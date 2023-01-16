<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom:api', ['except' => []]);
    }
    protected function guard()
    {
        return Auth::guard();
    }

    public function getCompanyId()
    {
        try {
            $token = JWTAuth::getToken();
            $token = JWTAuth::getPayload($token)->toArray();
            if ($token['company_id']) {
                try {
                    $companyId = decrypt($token['company_id']);
                    return $companyId;
                } catch (Exception $e) {
                    return null;
                }
            } else {
                return null;
            }

        } catch (Exception $e) {
            return null;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Purchase Start
    |--------------------------------------------------------------------------
    */
    public function getPurchases(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            if (empty($name)) {
                $purchases = DB::table('purchases')
                    ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date')
                    ->where('purchases.company_id', $companyId)
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                    ->orderBy('id', 'desc')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'purchases'));
            } else {
                $purchases = DB::table('purchases')
                    ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date')
                    ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                    ->where('purchases.company_id', $companyId)
                    ->where('purchases.purchase_id', 'like', '%' . $name . '%')
                    ->orWhere('suppliers.name', 'like', '%' . $name . '%')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'purchases'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getPurchase($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $purchaseData = DB::table('purchases')
                ->select('suppliers.name AS supplier_name', 'suppliers.id AS supplier_id', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date', 'purchases.paid', 'purchases.opening', 'purchases.payment_method')
                ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->where('purchases.company_id', $companyId)
                ->where('purchases.id', $id)
                ->first();
            $paymentData = [];
            $cash = DB::table('cash_books')->where('company_id', $companyId)->where('reference_no', 'pur-' . $purchaseData->purchase_id)->first();
            if ($cash) {
                $paymentData['cash'] = $cash->payment;
            }
            $baksh = DB::table('bkash_transactions')->where('company_id', $companyId)->where('reference_no', 'pur-' . $purchaseData->purchase_id)->first();
            if ($baksh) {
                $paymentData['bkash'] = $baksh->withdraw;
            }
            $nagad = DB::table('nagad_transactions')->where('company_id', $companyId)->where('reference_no', 'pur-' . $purchaseData->purchase_id)->first();
            if ($nagad) {
                $paymentData['nagad'] = $nagad->withdraw;
            }
            $bank = DB::table('bank_ledgers')->where('company_id', $companyId)->where('reference_no', 'pur-' . $purchaseData->purchase_id)->first();
            if ($bank) {
                $paymentData['bank'] = $bank;
            }
            $purchaseItems = DB::table('purchase_items')
                ->select('products.name', 'purchase_items.price as purchase_price', 'purchase_items.total', 'purchase_items.quantity', 'products.product_id', 'products.price')
                ->where('purchase_items.company_id', $companyId)
                ->where('products.company_id', $companyId)
                ->join('products', 'products.product_id', '=', 'purchase_items.product_id')
                ->where('purchase_items.purchase_id', $purchaseData->purchase_id)
                ->get();
            $purchase = array(
                'purchaseData' => $purchaseData,
                'purchaseItems' => $purchaseItems,
                'paymentData' => $paymentData
            );
            $status = true;
            return response()->json(compact('status', 'purchase'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storePurchase(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'supplier_id' => 'required',
                    'date' => 'required',
                    'productIds' => 'required',
                    'productQuantities' => 'required',
                    'productPrices' => 'required',
                    'total' => 'required',
                    'paymentMethod' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            if (count($request->productIds) > 0) {
                try {
                    DB::transaction(function () use ($request, $companyId) {
                        $products = $request->productIds;
                        $quantities = $request->productQuantities;
                        $prices = $request->productPrices;
                        $txGenerator = new InvoiceNumberGeneratorService();
                        $purchaseId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('purchase');
                        $txGenerator->setNextInvoiceNo();
                        DB::table('purchases')->insertGetId(
                            [
                                'supplier_id' => $request->supplier_id,
                                'amount' => $request->total,
                                'paid' => $request->cash + $request->bkash + $request->nagad + $request->bank,
                                'comment' => $request->comment,
                                'opening' => $request->openingStock ? $request->openingStock : 0,
                                'purchase_id' => $purchaseId,
                                'date' => $request->date,
                                'company_id' => $companyId,
                                'payment_method' => $request->paymentMethod,
                            ]
                        );
                        for ($i = 0, $n = count($products); $i < $n; $i++) {
                            $productID = $products[$i];
                            $quantity = $quantities[$i];
                            $price = $prices[$i];
                            if ($quantity > 0) {
                                DB::table('purchase_items')->insert([
                                    'purchase_id' => $purchaseId,
                                    'product_id' => $productID,
                                    'price' => $price,
                                    'quantity' => $quantity,
                                    'date' => $request->date,
                                    'total' => $quantity * $price,
                                    'company_id' => $companyId,
                                ]);
                                $averagePrice = DB::table('purchase_items')
                                    ->select(
                                        DB::raw('SUM(quantity) as totalQuantity'),
                                        DB::raw('SUM(total) as totalPrice')
                                    )->where('product_id', $productID)->where('company_id', $companyId)->first();
                                DB::table('average_purchase_prices')->where('product_id', $productID)
                                    ->where('company_id', $companyId)
                                    ->update(['price' => number_format($averagePrice->totalPrice / $averagePrice->totalQuantity, 2, '.', '')]);
                            }
                        }
                        if (empty($request->openingStock) || $request->openingStock == 0) {
                            $supplierDueTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                            DB::table('supplier_ledgers')->insert(array(
                                'supplier_id' => $request->supplier_id,
                                'transaction_id' => $supplierDueTxId,
                                'reference_no' => 'pur-' . $purchaseId,
                                'type' => 'due',
                                'due' => $request->total,
                                'deposit' => 0,
                                'date' => $request->date,
                                'company_id' => $companyId,
                                'comment' => "Due for Purchase id ($purchaseId)"
                            ));
                            $txGenerator->setNextInvoiceNo();
                            $paymentMethod = $request->paymentMethod;
                            if ($paymentMethod == 'cash' || $paymentMethod == 'multiple') {
                                if (!empty($request->cash) && $request->cash > 0) {
                                    $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                    DB::table('supplier_ledgers')->insert(array(
                                        'supplier_id' => $request->supplier_id,
                                        'reference_no' => 'pur-' . $purchaseId,
                                        'transaction_id' => $supplierPaidTxId,
                                        'type' => 'deposit',
                                        'due' => 0,
                                        'deposit' => $request->cash,
                                        'date' => $request->date,
                                        'company_id' => $companyId,
                                        'comment' => "Deposit for Purchase id ($purchaseId)"
                                    ));
                                    $txGenerator->setNextInvoiceNo();
                                    $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('cash_transaction');
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'reference_no' => 'pur-' . $purchaseId,
                                        'type' => 'payment',
                                        'payment' => $request->cash,
                                        'date' => $request->date,
                                        'company_id' => $companyId,
                                        'comment' => "Paid for Purchase id ($purchaseId)"
                                    ));
                                    $txGenerator->setNextInvoiceNo();
                                }
                            } elseif ($paymentMethod == 'bkash' || $paymentMethod == 'multiple') {
                                if (!empty($request->bkash) && $request->bkash > 0) {
                                    $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                    DB::table('supplier_ledgers')->insert(array(
                                        'supplier_id' => $request->supplier_id,
                                        'reference_no' => 'pur-' . $purchaseId,
                                        'transaction_id' => $supplierPaidTxId,
                                        'type' => 'deposit',
                                        'due' => 0,
                                        'deposit' => $request->bkash,
                                        'date' => $request->date,
                                        'company_id' => $companyId,
                                        'comment' => "Deposit for Purchase id ($purchaseId)"
                                    ));
                                    $txGenerator->setNextInvoiceNo();
                                    $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bkash_transaction');
                                    DB::table('bkash_transactions')->insert(array(
                                        'transaction_id' => $bkashTxId,
                                        'reference_no' => 'pur-' . $purchaseId,
                                        'type' => 'withdraw',
                                        'withdraw' => $request->bkash,
                                        'deposit' => 0,
                                        'date' => $request->date,
                                        'company_id' => $companyId,
                                        'comment' => "Paid for Purchase id ($purchaseId)"
                                    ));
                                    $txGenerator->setNextInvoiceNo();
                                }
                            } elseif ($paymentMethod == 'nagad' || $paymentMethod == 'multiple') {
                                if (!empty($request->nagad) && $request->nagad > 0) {
                                    $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                    DB::table('supplier_ledgers')->insert(array(
                                        'supplier_id' => $request->supplier_id,
                                        'reference_no' => 'pur-' . $purchaseId,
                                        'transaction_id' => $supplierPaidTxId,
                                        'type' => 'deposit',
                                        'due' => 0,
                                        'deposit' => $request->nagad,
                                        'date' => $request->date,
                                        'company_id' => $companyId,
                                        'comment' => "Deposit for Purchase id ($purchaseId)"
                                    ));
                                    $txGenerator->setNextInvoiceNo();
                                    $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('nagad_transaction');
                                    DB::table('nagad_transactions')->insert(array(
                                        'transaction_id' => $nagadTxId,
                                        'reference_no' => 'pur-' . $purchaseId,
                                        'type' => 'withdraw',
                                        'withdraw' => $request->nagad,
                                        'deposit' => 0,
                                        'date' => $request->date,
                                        'company_id' => $companyId,
                                        'comment' => "Paid for Purchase id ($purchaseId)"
                                    ));
                                    $txGenerator->setNextInvoiceNo();
                                }
                            } elseif ($paymentMethod == 'bank' || $paymentMethod == 'multiple') {
                                if (!empty($request->bank) && $request->bank > 0 && !empty($request->bankId)) {
                                    $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                    DB::table('supplier_ledgers')->insert(array(
                                        'supplier_id' => $request->supplier_id,
                                        'reference_no' => 'pur-' . $purchaseId,
                                        'transaction_id' => $supplierPaidTxId,
                                        'type' => 'deposit',
                                        'due' => 0,
                                        'deposit' => $request->bank,
                                        'date' => $request->date,
                                        'company_id' => $companyId,
                                        'comment' => "Deposit for Purchase id ($purchaseId)"
                                    ));
                                    $txGenerator->setNextInvoiceNo();
                                    $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                                    DB::table('bank_ledgers')->insert(array(
                                        'transaction_id' => $bankTxId,
                                        'reference_no' => 'pur-' . $purchaseId,
                                        'type' => 'withdraw',
                                        'withdraw' => $request->bank,
                                        'bank_id' => $request->bankId,
                                        'date' => $request->date,
                                        'company_id' => $companyId,
                                        'comment' => "Paid for Purchase id ($purchaseId)"
                                    ));
                                    $txGenerator->setNextInvoiceNo();
                                }
                            }
                        }
                    });
                } catch (Exception $e) {
                    $status = false;
                    $errors = $e;
                    return response()->json(compact('status', 'errors'));
                }
                $status = true;
                $message = 'Purchase saved';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Please add at least one product';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updatePurchase(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'purchase_id' => 'required',
                    'supplier_id' => 'required',
                    'date' => 'required',
                    'productIds' => 'required',
                    'productQuantities' => 'required',
                    'productPrices' => 'required',
                    'total' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            $products = $request->productIds;
            $quantities = $request->productQuantities;
            $prices = $request->productPrices;
            if (count($products) > 0) {
                try {
                    DB::transaction(function () use ($prices, $quantities, $products, $request, $companyId) {
                        $txGenerator = new InvoiceNumberGeneratorService();
                        $purchase = DB::table('purchases')->where('id', $request->purchase_id)->where('company_id', $companyId)->first();

                        DB::table('purchases')->where('id', $request->purchase_id)->where('company_id', $companyId)->update(
                            [
                                'supplier_id' => $request->supplier_id,
                                'amount' => $request->total,
                                'payment_method' => $request->paymentMethod,
                                'paid' => $request->cash + $request->bkash + $request->nagad + $request->bank,
                                'opening' => $request->openingStock ? $request->openingStock : 0,
                                'comment' => $request->comment,
                                'date' => $request->date,
                            ]
                        );

                        DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)
                            ->where('company_id', $companyId)
                            ->delete();

                        for ($i = 0, $n = count($products); $i < $n; $i++) {
                            $productID = $products[$i];
                            $quantity = $quantities[$i];
                            $price = $prices[$i];
                            if ($quantity > 0) {
                                DB::table('purchase_items')->insert([
                                    'purchase_id' => $purchase->purchase_id,
                                    'product_id' => $productID,
                                    'price' => $price,
                                    'quantity' => $quantity,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'total' => $quantity * $price,
                                ]);
                                $averagePrice = DB::table('purchase_items')
                                    ->select(
                                        DB::raw('SUM(quantity) as totalQuantity'),
                                        DB::raw('SUM(total) as totalPrice')
                                    )->where('product_id', $productID)->where('company_id', $companyId)->first();

                                DB::table('average_purchase_prices')->where('product_id', $productID)
                                    ->where('company_id', $companyId)
                                    ->update(['price' => number_format($averagePrice->totalPrice / $averagePrice->totalQuantity, 2, '.', '')]);

                                if (empty($request->openingStock) || $request->openingStock == 0) {
                                    DB::table('supplier_ledgers')
                                        ->where('reference_no', "pur-$purchase->purchase_id")
                                        ->where('type', 'due')
                                        ->where('company_id', $companyId)
                                        ->update(array(
                                            'supplier_id' => $request->supplier_id,
                                            'due' => $request->total,
                                            'deposit' => 0,
                                            'date' => $request->date
                                        ));
                                } else {
                                    DB::table('supplier_ledgers')
                                        ->where('reference_no', "pur-$purchase->purchase_id")->where('company_id', $companyId)->delete();
                                }

                                DB::table('supplier_ledgers')
                                    ->where('reference_no', "pur-$purchase->purchase_id")
                                    ->where('type', 'deposit')
                                    ->where('company_id', $companyId)
                                    ->delete();

                                DB::table('cash_books')
                                    ->where('reference_no', "pur-$purchase->purchase_id")
                                    ->where('type', 'payment')
                                    ->where('company_id', $companyId)
                                    ->delete();

                                DB::table('bkash_transactions')
                                    ->where('reference_no', "pur-$purchase->purchase_id")
                                    ->where('type', 'withdraw')
                                    ->where('company_id', $companyId)
                                    ->delete();

                                DB::table('nagad_transactions')
                                    ->where('reference_no', "pur-$purchase->purchase_id")
                                    ->where('type', 'withdraw')
                                    ->where('company_id', $companyId)
                                    ->delete();

                                DB::table('bank_ledgers')
                                    ->where('reference_no', "pur-$purchase->purchase_id")
                                    ->where('type', 'withdraw')
                                    ->where('company_id', $companyId)
                                    ->delete();

                                if (empty($request->openingStock) || $request->openingStock == 0) {
                                    $paymentMethod = $request->paymentMethod;
                                    if ($paymentMethod == 'cash' || $paymentMethod == 'multiple') {
                                        if (!empty($request->cash) && $request->cash > 0) {
                                            $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                            DB::table('supplier_ledgers')->insert(array(
                                                'supplier_id' => $request->supplier_id,
                                                'reference_no' => 'pur-' . $purchase->purchase_id,
                                                'transaction_id' => $supplierPaidTxId,
                                                'type' => 'deposit',
                                                'due' => 0,
                                                'deposit' => $request->cash,
                                                'date' => $request->date,
                                                'company_id' => $companyId,
                                                'comment' => "Deposit for Purchase id ($purchase->purchase_id)"
                                            ));
                                            $txGenerator->setNextInvoiceNo();
                                            $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('cash_transaction');
                                            DB::table('cash_books')->insert(array(
                                                'transaction_id' => $cashTxId,
                                                'reference_no' => 'pur-' . $purchase->purchase_id,
                                                'type' => 'payment',
                                                'payment' => $request->cash,
                                                'date' => $request->date,
                                                'company_id' => $companyId,
                                                'comment' => "Paid for Purchase id ($purchase->purchase_id)"
                                            ));
                                            $txGenerator->setNextInvoiceNo();
                                        }
                                    } elseif ($paymentMethod == 'bkash' || $paymentMethod == 'multiple') {
                                        if (!empty($request->bkash) && $request->bkash > 0) {
                                            $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                            DB::table('supplier_ledgers')->insert(array(
                                                'supplier_id' => $request->supplier_id,
                                                'reference_no' => 'pur-' . $purchase->purchase_id,
                                                'transaction_id' => $supplierPaidTxId,
                                                'type' => 'deposit',
                                                'due' => 0,
                                                'deposit' => $request->bkash,
                                                'date' => $request->date,
                                                'company_id' => $companyId,
                                                'comment' => "Deposit for Purchase id ($purchase->purchase_id)"
                                            ));
                                            $txGenerator->setNextInvoiceNo();
                                            $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bkash_transaction');
                                            DB::table('bkash_transactions')->insert(array(
                                                'transaction_id' => $bkashTxId,
                                                'reference_no' => 'pur-' . $purchase->purchase_id,
                                                'type' => 'withdraw',
                                                'withdraw' => $request->bkash,
                                                'deposit' => 0,
                                                'date' => $request->date,
                                                'company_id' => $companyId,
                                                'comment' => "Paid for Purchase id ($purchase->purchase_id)"
                                            ));
                                            $txGenerator->setNextInvoiceNo();
                                        }
                                    } elseif ($paymentMethod == 'nagad' || $paymentMethod == 'multiple') {
                                        if (!empty($request->nagad) && $request->nagad > 0) {
                                            $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                            DB::table('supplier_ledgers')->insert(array(
                                                'supplier_id' => $request->supplier_id,
                                                'reference_no' => 'pur-' . $purchase->purchase_id,
                                                'transaction_id' => $supplierPaidTxId,
                                                'type' => 'deposit',
                                                'due' => 0,
                                                'deposit' => $request->nagad,
                                                'date' => $request->date,
                                                'company_id' => $companyId,
                                                'comment' => "Deposit for Purchase id ($purchase->purchase_id)"
                                            ));
                                            $txGenerator->setNextInvoiceNo();
                                            $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('nagad_transaction');
                                            DB::table('nagad_transactions')->insert(array(
                                                'transaction_id' => $nagadTxId,
                                                'reference_no' => 'pur-' . $purchase->purchase_id,
                                                'type' => 'withdraw',
                                                'withdraw' => $request->nagad,
                                                'deposit' => 0,
                                                'date' => $request->date,
                                                'company_id' => $companyId,
                                                'comment' => "Paid for Purchase id ($purchase->purchase_id)"
                                            ));
                                            $txGenerator->setNextInvoiceNo();
                                        }
                                    } elseif ($paymentMethod == 'bank' || $paymentMethod == 'multiple') {
                                        if (!empty($request->bank) && $request->bank > 0 && !empty($request->bankId)) {
                                            $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                            DB::table('supplier_ledgers')->insert(array(
                                                'supplier_id' => $request->supplier_id,
                                                'reference_no' => 'pur-' . $purchase->purchase_id,
                                                'transaction_id' => $supplierPaidTxId,
                                                'type' => 'deposit',
                                                'due' => 0,
                                                'deposit' => $request->bank,
                                                'date' => $request->date,
                                                'company_id' => $companyId,
                                                'comment' => "Deposit for Purchase id ($purchase->purchase_id)"
                                            ));
                                            $txGenerator->setNextInvoiceNo();
                                            $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                                            DB::table('bank_ledgers')->insert(array(
                                                'transaction_id' => $bankTxId,
                                                'reference_no' => 'pur-' . $purchase->purchase_id,
                                                'type' => 'withdraw',
                                                'withdraw' => $request->bank,
                                                'bank_id' => $request->bankId,
                                                'date' => $request->date,
                                                'company_id' => $companyId,
                                                'comment' => "Paid for Purchase id ($purchase->purchase_id)"
                                            ));
                                            $txGenerator->setNextInvoiceNo();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }catch (Exception $e) {
                    $status = false;
                    $errors = $e;
                    return response()->json(compact('status', 'errors'));
                }
                $status = true;
                $message = 'Purchase updated';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Please add at least one product';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deletePurchase(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                $purchase = DB::table('purchases')->where('id', $request->id)->where('company_id', $companyId)->first();
                try {
                    DB::transaction(function () use ($request, $purchase, $companyId, $id) {
                        DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)->where('company_id', $companyId)->delete();
                        DB::table('supplier_ledgers')
                            ->where('reference_no', "pur-$purchase->purchase_id")
                            ->where('company_id', $companyId)
                            ->delete();
                        DB::table('cash_books')
                            ->where('reference_no', "pur-$purchase->purchase_id")
                            ->where('company_id', $companyId)
                            ->delete();
                        DB::table('bkash_transactions')
                            ->where('reference_no', "pur-$purchase->purchase_id")
                            ->where('company_id', $companyId)
                            ->delete();
                        DB::table('nagad_transactions')
                            ->where('reference_no', "pur-$purchase->purchase_id")
                            ->where('company_id', $companyId)
                            ->delete();
                        DB::table('bank_ledgers')
                            ->where('reference_no', "pur-$purchase->purchase_id")
                            ->where('company_id', $companyId)
                            ->delete();
                        DB::table('purchases')->where('id', $id)->where('company_id', $companyId)->delete();
                    });
                } catch (Exception $e) {
                    $status = false;
                    $errors = $e;
                    return response()->json(compact('status', 'errors'));
                }
                $status = true;
                $message = 'Purchase deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Purchase not found';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Purchase End
    |--------------------------------------------------------------------------
    */
}
