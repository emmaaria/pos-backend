<?php

namespace App\Http\Controllers;

use App\Models\AveragePurchasePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvoiceController extends Controller
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

        } catch (\Exception $e) {
            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Invoice Start
    |--------------------------------------------------------------------------
    */
    public function getInvoices(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->keyword;
            if (empty($name)) {
                $invoices = DB::table('invoices')
                    ->select('customers.name AS customer_name', 'invoices.invoice_id', 'invoices.grand_total', 'invoices.discountAmount', 'invoices.comment', 'invoices.id', 'invoices.date')
                    ->where('invoices.company_id', $companyId)
                    ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                    ->orderBy('date', 'desc')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'invoices'));
            } else {
                $invoices = DB::table('invoices')
                    ->select('customers.name AS customer_name', 'invoices.invoice_id', 'invoices.grand_total', 'invoices.discountAmount', 'invoices.comment', 'invoices.id', 'invoices.date')
                    ->where('invoices.company_id', $companyId)
                    ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                    ->where('invoices.invoice_id', 'like', '%' . $name . '%')
                    ->orWhere('customers.name', 'like', '%' . $name . '%')
                    ->orderBy('date', 'desc')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'invoices'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getTodayInvoices(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->keyword;
            if (empty($name)) {
                $invoices = DB::table('invoices')
                    ->select('customers.name AS customer_name', 'invoices.invoice_id', 'invoices.total', 'invoices.discountAmount', 'invoices.comment', 'invoices.id', 'invoices.date')
                    ->where('invoices.company_id', $companyId)
                    ->where('invoices.date', date('Y-m-d'))
                    ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                    ->orderBy('id', 'desc')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'invoices'));
            } else {
                $invoices = DB::table('invoices')
                    ->select('customers.name AS customer_name', 'invoices.invoice_id', 'invoices.total', 'invoices.discountAmount', 'invoices.comment', 'invoices.id', 'invoices.date')
                    ->where('invoices.company_id', $companyId)
                    ->where('invoices.date', date('Y-m-d'))
                    ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                    ->where('invoices.invoice_id', 'like', '%' . $name . '%')
                    ->orWhere('customers.name', 'like', '%' . $name . '%')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'invoices'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getInvoice($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $invoiceData = DB::table('invoices')
                ->where('invoices.company_id', $companyId)
                ->select('customers.name AS customer_name', 'invoices.*')
                ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                ->where('invoices.invoice_id', $id)
                ->first();
            $invoiceItems = DB::table('invoice_items')
                ->select('products.name', 'invoice_items.*')
                ->where('invoice_items.company_id', $companyId)
                ->join('products', 'products.product_id', '=', 'invoice_items.product_id')
                ->where('invoice_items.invoice_id', $id)
                ->get();

            $cash = DB::table('cash_books')
                ->where('reference_no', "inv-$id")
                ->where('company_id', $companyId)
                ->where('type', 'receive')
                ->first();
            $bkash = DB::table('bkash_transactions')
                ->where('reference_no', "inv-$id")
                ->where('company_id', $companyId)
                ->where('type', 'deposit')
                ->first();
            $nagad = DB::table('nagad_transactions')
                ->where('reference_no', "inv-$id")
                ->where('company_id', $companyId)
                ->where('type', 'deposit')
                ->first();
            $card = DB::table('card_transactions')
                ->where('reference_no', "inv-$id")
                ->where('type', 'deposit')
                ->where('company_id', $companyId)
                ->first();

            $bank = DB::table('bank_ledgers')
                ->where('reference_no', "inv-$id")
                ->where('type', 'deposit')
                ->where('company_id', $companyId)
                ->first();

            $payments = array(
                'cash' => $cash ? $cash->receive : null,
                'bkash' => $bkash ? $bkash->deposit : null,
                'nagad' => $nagad ? $nagad->deposit : null,
                'card' => $card ? $card->deposit : null,
                'bankId' => $bank ? $bank->bank_id : null,
                'bank' => $bank ? $bank->deposit : null,
            );
            $invoice = array(
                'invoiceData' => $invoiceData,
                'invoiceItems' => $invoiceItems,
                'payments' => $payments
            );
            $status = true;
            return response()->json(compact('status', 'invoice'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeInvoice(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'date' => 'required',
                    'productIds' => 'required',
                    'productQuantities' => 'required',
                    'productPrices' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            $customerName = '';

            if (!empty($request->customer_id)) {
                $customerId = $request->customer_id;
                $customer = DB::table('customers')->where('id', $request->customer_id)->where('company_id', $companyId)->first();
                $customerName = $customer->name;
            } else {
                $customer = DB::table('customers')->where('name', 'Walking Customer')->where('company_id', $companyId)->first();
                if ($customer) {
                    $customerId = $customer->id;
                    $customerName = $customer->name;
                } else {
                    $status = false;
                    $errors = 'No customer selected. Please select a customer or add Walking Customer.';
                    return response()->json(compact('status', 'errors'));
                }
            }
            if (!empty($request->bank)) {
                if (empty($request->bankId)) {
                    $status = false;
                    $errors = 'Please select bank account';
                    return response()->json(compact('status', 'errors'));
                }
            }
            $products = $request->productIds;
            $quantities = $request->productQuantities;
            $prices = $request->productPrices;
            $discountTypes = $request->productDiscountTypes;
            $productDiscounts = $request->productDiscounts;
            $productDiscountedAmounts = $request->productDiscountedAmounts;
            if (count($products) > 0) {
                try {
                    $invoice = DB::transaction(function () use ($productDiscountedAmounts, $request, $companyId, $products, $quantities, $prices, $customerId, $customerName, $productDiscounts, $discountTypes) {
                        $invoice = array();
                        $invoice['customer_name'] = $customerName;
                        $txGenerator = new InvoiceNumberGeneratorService();
                        $invoiceId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('invoice');
                        $txGenerator->setNextInvoiceNo();
                        $invoice['invoice_id'] = $invoiceId;
                        $invoice['date'] = $request->date;
                        $total = 0;
                        $profit = 0;
                        for ($i = 0, $n = count($products); $i < $n; $i++) {
                            $productID = $products[$i];
                            $product = DB::table('products')->select('name')->where('product_id', $productID)->first();
                            $quantity = $quantities[$i];
                            $price = $prices[$i];
                            $total += $quantity * $price;
                            $prDisType = '';
                            $prDis = '';
                            $prDisAmount = 0;
                            if (isset($discountTypes[$i])) {
                                $prDisType = $discountTypes[$i];
                            }
                            if (isset($productDiscounts[$i]) && $productDiscounts[$i] !== '') {
                                $prDis = $productDiscounts[$i];
                            }
                            if (isset($productDiscountedAmounts[$i]) && $productDiscountedAmounts[$i] !== '') {
                                $prDisAmount = $productDiscountedAmounts[$i];
                            }

                            if ($quantity > 0) {
                                $ttl = $quantity * $price;
                                $invoiceItemData = array(
                                    'name' => $product->name,
                                    'price' => $price,
                                    'quantity' => $quantity,
                                    'total' => $quantity * $price,
                                );
                                $invoice['items'][] = $invoiceItemData;
                                DB::table('invoice_items')->insert([
                                    'invoice_id' => $invoiceId,
                                    'product_id' => $productID,
                                    'user_id' => Auth::id(),
                                    'price' => $price,
                                    'company_id' => $companyId,
                                    'quantity' => $quantity,
                                    'date' => $request->date,
                                    'discount_type' => $prDisType,
                                    'discount_amount' => $prDisAmount,
                                    'discount' => $prDis,
                                    'total' => $quantity * $price,
                                    'grand_total' => $ttl - $prDisAmount,
                                ]);
                                $purchasePrice = DB::table('average_purchase_prices')->select('price')->where('product_id', $productID)->where('company_id', $companyId)->first();
                                if (!empty($purchasePrice)){
                                    $profit += ($quantity * $price) - ($quantity * $purchasePrice->price) - $prDisAmount;
                                }else{
                                    AveragePurchasePrice::create(array(
                                        'product_id' => $productID,
                                        'price' => $product->purchase_price,
                                        'company_id' => $companyId,
                                    ));
                                    $profit += ($quantity * $price) - ($quantity * $product->purchase_price) - $prDisAmount;
                                }

                            }
                        }
                        $invoice['cash'] = $request->cash;
                        $invoice['bkash'] = $request->bkash;
                        $invoice['nagad'] = $request->nagad;
                        $invoice['card'] = $request->card;
                        $invoice['bank'] = $request->bank;
                        $invoice['discountType'] = $request->discountType;
                        $invoice['discount'] = $request->discount;
                        $invoice['discountAmount'] = $request->discountAmount;
                        $invoice['subtotal'] = $total;
                        $invoice['grandTotal'] = $total - $request->discountAmount;
                        $paid = $request->cash + $request->bkash + $request->nagad + $request->card + $request->bank;
                        $invoice['paid'] = $paid;
                        $invoice['due'] = ($total - $request->discountAmount) - ($request->cash + $request->bkash + $request->nagad + $request->card + $request->bank);
                        $setting = DB::table('companies')->where('company_id', $companyId)->first();
                        DB::table('invoices')->insert(
                            [
                                'customer_id' => $customerId,
                                'user_id' => Auth::id(),
                                'payment_method' => $request->payment_method,
                                'invoice_id' => $invoiceId,
                                'comment' => $request->comment,
                                'discount_setting' => $setting->discount_type,
                                'date' => $request->date,
                                'discount' => $request->discount,
                                'discountAmount' => $request->discountAmount,
                                'discountType' => $request->discountType,
                                'total' => $total,
                                'grand_total' => $total - $request->discountAmount,
                                'paid_amount' => $paid,
                                'company_id' => $companyId,
                            ]
                        );
                        DB::table('profits')->insert(
                            [
                                'date' => $request->date,
                                'deposit' => $profit - $request->discountAmount,
                                'company_id' => $companyId,
                                'reference_no' => "inv-$invoiceId",
                            ]
                        );
                        $customerDueTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('customer_transaction');
                        $grandTotal = $total - $request->discountAmount;
                        DB::table('customer_ledgers')->insert(array(
                            'customer_id' => $customerId,
                            'user_id' => Auth::id(),
                            'transaction_id' => $customerDueTxId,
                            'reference_no' => "inv-$invoiceId",
                            'type' => 'due',
                            'company_id' => $companyId,
                            'due' => $grandTotal,
                            'deposit' => 0,
                            'date' => $request->date,
                            'comment' => "Due for Invoice ID ($invoiceId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                        if ($paid > $grandTotal) {
                            $paid = $grandTotal;
                        }
                        if ($paid > 0) {
                            $customerPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('customer_transaction');
                            DB::table('customer_ledgers')->insert(array(
                                'customer_id' => $customerId,
                                'user_id' => Auth::id(),
                                'reference_no' => "inv-$invoiceId",
                                'transaction_id' => $customerPaidTxId,
                                'type' => 'deposit',
                                'company_id' => $companyId,
                                'due' => 0,
                                'deposit' => $paid,
                                'date' => $request->date,
                                'comment' => "Deposit for Invoice ID ($invoiceId)"
                            ));
                            $txGenerator->setNextInvoiceNo();

                            if (!empty($request->cash) && $request->cash > 0) {
                                $onlinePayments = $request->bkash + $request->nagad + $request->card + $request->bank;
                                $dueAfterOnlinePayment = $grandTotal - $onlinePayments;
                                $cashPaid = $request->cash;
                                $change = $grandTotal - ($dueAfterOnlinePayment - $cashPaid);
                                $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('cash_transaction');

                                if ($change > 0) {
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'user_id' => Auth::id(),
                                        'company_id' => $companyId,
                                        'reference_no' => "inv-$invoiceId",
                                        'type' => 'receive',
                                        'receive' => $dueAfterOnlinePayment,
                                        'date' => $request->date,
                                        'comment' => "Cash receive for Invoice No ($invoiceId)"
                                    ));
                                } elseif ($change < 0) {
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'user_id' => Auth::id(),
                                        'company_id' => $companyId,
                                        'reference_no' => "inv-$invoiceId",
                                        'type' => 'receive',
                                        'receive' => $cashPaid,
                                        'date' => $request->date,
                                        'comment' => "Cash receive for Invoice No ($invoiceId)"
                                    ));
                                } else {
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'company_id' => $companyId,
                                        'user_id' => Auth::id(),
                                        'reference_no' => "inv-$invoiceId",
                                        'type' => 'receive',
                                        'receive' => $cashPaid,
                                        'date' => $request->date,
                                        'comment' => "Cash receive for Invoice No ($invoiceId)"
                                    ));
                                }

                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->bkash) && $request->bkash > 0) {
                                $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('bkash_transaction');
                                DB::table('bkash_transactions')->insert(array(
                                    'transaction_id' => $bkashTxId,
                                    'company_id' => $companyId,
                                    'user_id' => Auth::id(),
                                    'reference_no' => "inv-$invoiceId",
                                    'type' => 'deposit',
                                    'deposit' => $request->bkash,
                                    'date' => $request->date,
                                    'comment' => "Cash receive for Invoice No ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->nagad) && $request->nagad > 0) {
                                $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('nagad_transaction');
                                DB::table('nagad_transactions')->insert(array(
                                    'transaction_id' => $nagadTxId,
                                    'user_id' => Auth::id(),
                                    'reference_no' => "inv-$invoiceId",
                                    'company_id' => $companyId,
                                    'type' => 'deposit',
                                    'deposit' => $request->nagad,
                                    'date' => $request->date,
                                    'comment' => "Cash receive for Invoice No ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->card) && $request->card > 0) {
                                $cardTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('card_transaction');
                                DB::table('card_transactions')->insert(array(
                                    'transaction_id' => $cardTxId,
                                    'user_id' => Auth::id(),
                                    'company_id' => $companyId,
                                    'reference_no' => "inv-$invoiceId",
                                    'type' => 'deposit',
                                    'deposit' => $request->card,
                                    'date' => $request->date,
                                    'comment' => "Cash receive for Invoice No ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->bank) && $request->bank > 0) {
                                $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                                DB::table('bank_ledgers')->insert(array(
                                    'transaction_id' => $bankTxId,
                                    'user_id' => Auth::id(),
                                    'reference_no' => 'inv-' . $invoiceId,
                                    'type' => 'deposit',
                                    'deposit' => $request->bank,
                                    'bank_id' => $request->bankId,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Invoice id ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }
                        }
                        return $invoice;
                    });
                    $status = true;
                    $message = 'Invoice saved';
                    return response()->json(compact('status', 'message', 'invoice'));
                } catch (\Exception $e) {
                    $status = false;
                    $errors = $e;
                    return response()->json(compact('status', 'errors'));
                }
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

    public function updateInvoice(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'invoice_id' => 'required',
                    'date' => 'required',
                    'productIds' => 'required',
                    'productQuantities' => 'required',
                    'productPrices' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            $customerName = '';

            if (!empty($request->customer_id)) {
                $customerId = $request->customer_id;
                $customer = DB::table('customers')->where('id', $request->customer_id)->where('company_id', $companyId)->first();
                $customerName = $customer->name;
            } else {
                $customer = DB::table('customers')->where('name', 'Walking Customer')->where('company_id', $companyId)->first();
                if ($customer) {
                    $customerId = $customer->id;
                    $customerName = $customer->name;
                } else {
                    $status = false;
                    $errors = 'No customer selected. Please select a customer or add Walking Customer.';
                    return response()->json(compact('status', 'errors'));
                }
            }
            if (!empty($request->bank)) {
                if (empty($request->bankId)) {
                    $status = false;
                    $errors = 'Please select bank account';
                    return response()->json(compact('status', 'errors'));
                }
            }
            $invoiceId = $request->invoice_id;
            $products = $request->productIds;
            $quantities = $request->productQuantities;
            $prices = $request->productPrices;
            $discountTypes = $request->productDiscountTypes;
            $productDiscounts = $request->productDiscounts;
            $productDiscountedAmounts = $request->productDiscountedAmounts;
            if (count($products) > 0) {
                try {
                    $invoice = DB::transaction(function () use ($invoiceId, $productDiscountedAmounts, $request, $companyId, $products, $quantities, $prices, $customerId, $customerName, $productDiscounts, $discountTypes) {
                        $invoice = array();
                        $invoice['customer_name'] = $customerName;
                        $txGenerator = new InvoiceNumberGeneratorService();
                        $invoice['invoice_id'] = $invoiceId;
                        $invoice['date'] = $request->date;
                        $total = 0;
                        $profit = 0;
                        DB::table('invoice_items')->where('invoice_id', $invoiceId)->where('company_id', $companyId)->delete();
                        for ($i = 0, $n = count($products); $i < $n; $i++) {
                            $productID = $products[$i];
                            $product = DB::table('products')->select('name')->where('product_id', $productID)->first();
                            $quantity = $quantities[$i];
                            $price = $prices[$i];
                            $total += $quantity * $price;
                            $prDisType = '';
                            $prDis = '';
                            $prDisAmount = 0;
                            if (isset($discountTypes[$i])) {
                                $prDisType = $discountTypes[$i];
                            }
                            if (isset($productDiscounts[$i]) && $productDiscounts[$i] !== '') {
                                $prDis = $productDiscounts[$i];
                            }
                            if (isset($productDiscountedAmounts[$i]) && $productDiscountedAmounts[$i] !== '') {
                                $prDisAmount = $productDiscountedAmounts[$i];
                            }

                            if ($quantity > 0) {
                                $ttl = $quantity * $price;
                                $invoiceItemData = array(
                                    'name' => $product->name,
                                    'price' => $price,
                                    'quantity' => $quantity,
                                    'total' => $quantity * $price,
                                );
                                $invoice['items'][] = $invoiceItemData;
                                DB::table('invoice_items')->insert([
                                    'invoice_id' => $invoiceId,
                                    'user_id' => Auth::id(),
                                    'product_id' => $productID,
                                    'price' => $price,
                                    'company_id' => $companyId,
                                    'quantity' => $quantity,
                                    'date' => $request->date,
                                    'discount_type' => $prDisType,
                                    'discount_amount' => $prDisAmount,
                                    'discount' => $prDis,
                                    'total' => $quantity * $price,
                                    'grand_total' => $ttl - $prDisAmount,
                                ]);
                                $purchasePrice = DB::table('average_purchase_prices')->select('price')->where('product_id', $productID)->where('company_id', $companyId)->first();
                                $profit += ($quantity * $price) - ($quantity * $purchasePrice->price) - $prDisAmount;
                            }
                        }
                        $invoice['cash'] = $request->cash;
                        $invoice['bkash'] = $request->bkash;
                        $invoice['nagad'] = $request->nagad;
                        $invoice['card'] = $request->card;
                        $invoice['bank'] = $request->bank;
                        $invoice['discountType'] = $request->discountType;
                        $invoice['discount'] = $request->discount;
                        $invoice['discountAmount'] = $request->discountAmount;
                        $invoice['subtotal'] = $total;
                        $invoice['grandTotal'] = $total - $request->discountAmount;
                        $paid = $request->cash + $request->bkash + $request->nagad + $request->card + $request->bank;
                        $invoice['paid'] = $paid;
                        $invoice['due'] = ($total - $request->discountAmount) - ($request->cash + $request->bkash + $request->nagad + $request->card + $request->bank);
                        $setting = DB::table('companies')->where('company_id', $companyId)->first();
                        DB::table('invoices')->where('invoice_id', $invoiceId)->where('company_id', $companyId)->update(
                            [
                                'customer_id' => $customerId,
                                'user_id' => Auth::id(),
                                'payment_method' => $request->payment_method,
                                'comment' => $request->comment,
                                'discount_setting' => $setting->discount_type,
                                'date' => $request->date,
                                'discount' => $request->discount,
                                'discountAmount' => $request->discountAmount,
                                'discountType' => $request->discountType,
                                'total' => $total,
                                'grand_total' => $total - $request->discountAmount,
                                'paid_amount' => $paid,
                                'company_id' => $companyId,
                            ]
                        );
                        DB::table('profits')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->updateOrInsert(
                            [
                                'reference_no' => "inv-$invoiceId",
                                'date' => $request->date,
                                'deposit' => $profit - $request->discountAmount,
                                'company_id' => $companyId,
                            ]
                        );
                        DB::table('customer_ledgers')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();
                        $customerDueTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('customer_transaction');
                        $grandTotal = $total - $request->discountAmount;
                        DB::table('customer_ledgers')->insert(array(
                            'customer_id' => $customerId,
                            'user_id' => Auth::id(),
                            'transaction_id' => $customerDueTxId,
                            'reference_no' => "inv-$invoiceId",
                            'type' => 'due',
                            'company_id' => $companyId,
                            'due' => $grandTotal,
                            'deposit' => 0,
                            'date' => $request->date,
                            'comment' => "Due for Invoice ID ($invoiceId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                        if ($paid > $grandTotal) {
                            $paid = $grandTotal;
                        }
                        DB::table('bank_ledgers')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();

                        DB::table('bkash_transactions')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();

                        DB::table('card_transactions')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();

                        DB::table('cash_books')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();

                        DB::table('nagad_transactions')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();

                        if ($paid > 0) {
                            $customerPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('customer_transaction');
                            DB::table('customer_ledgers')->insert(array(
                                'customer_id' => $customerId,
                                'reference_no' => "inv-$invoiceId",
                                'transaction_id' => $customerPaidTxId,
                                'type' => 'deposit',
                                'user_id' => Auth::id(),
                                'company_id' => $companyId,
                                'due' => 0,
                                'deposit' => $paid,
                                'date' => $request->date,
                                'comment' => "Deposit for Invoice ID ($invoiceId)"
                            ));
                            $txGenerator->setNextInvoiceNo();

                            if (!empty($request->cash) && $request->cash > 0) {
                                $onlinePayments = $request->bkash + $request->nagad + $request->card + $request->bank;
                                $dueAfterOnlinePayment = $grandTotal - $onlinePayments;
                                $cashPaid = $request->cash;
                                $change = $grandTotal - ($dueAfterOnlinePayment - $cashPaid);
                                $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('cash_transaction');

                                if ($change > 0) {
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'company_id' => $companyId,
                                        'reference_no' => "inv-$invoiceId",
                                        'type' => 'receive',
                                        'user_id' => Auth::id(),
                                        'receive' => $dueAfterOnlinePayment,
                                        'date' => $request->date,
                                        'comment' => "Cash receive for Invoice No ($invoiceId)"
                                    ));
                                } elseif ($change < 0) {
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'company_id' => $companyId,
                                        'reference_no' => "inv-$invoiceId",
                                        'type' => 'receive',
                                        'user_id' => Auth::id(),
                                        'receive' => $cashPaid,
                                        'date' => $request->date,
                                        'comment' => "Cash receive for Invoice No ($invoiceId)"
                                    ));
                                } else {
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'company_id' => $companyId,
                                        'reference_no' => "inv-$invoiceId",
                                        'type' => 'receive',
                                        'user_id' => Auth::id(),
                                        'receive' => $cashPaid,
                                        'date' => $request->date,
                                        'comment' => "Cash receive for Invoice No ($invoiceId)"
                                    ));
                                }

                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->bkash) && $request->bkash > 0) {
                                $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('bkash_transaction');
                                DB::table('bkash_transactions')->insert(array(
                                    'transaction_id' => $bkashTxId,
                                    'company_id' => $companyId,
                                    'reference_no' => "inv-$invoiceId",
                                    'type' => 'deposit',
                                    'user_id' => Auth::id(),
                                    'deposit' => $request->bkash,
                                    'date' => $request->date,
                                    'comment' => "Cash receive for Invoice No ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->nagad) && $request->nagad > 0) {
                                $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('nagad_transaction');
                                DB::table('nagad_transactions')->insert(array(
                                    'transaction_id' => $nagadTxId,
                                    'reference_no' => "inv-$invoiceId",
                                    'company_id' => $companyId,
                                    'type' => 'deposit',
                                    'user_id' => Auth::id(),
                                    'deposit' => $request->nagad,
                                    'date' => $request->date,
                                    'comment' => "Cash receive for Invoice No ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->card) && $request->card > 0) {
                                $cardTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('card_transaction');
                                DB::table('card_transactions')->insert(array(
                                    'transaction_id' => $cardTxId,
                                    'company_id' => $companyId,
                                    'reference_no' => "inv-$invoiceId",
                                    'type' => 'deposit',
                                    'user_id' => Auth::id(),
                                    'deposit' => $request->card,
                                    'date' => $request->date,
                                    'comment' => "Cash receive for Invoice No ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->bank) && $request->bank > 0) {
                                $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                                DB::table('bank_ledgers')->insert(array(
                                    'transaction_id' => $bankTxId,
                                    'reference_no' => 'inv-' . $invoiceId,
                                    'type' => 'deposit',
                                    'user_id' => Auth::id(),
                                    'deposit' => $request->bank,
                                    'bank_id' => $request->bankId,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Invoice id ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }
                        }
                        return $invoice;
                    });
                    $status = true;
                    $message = 'Invoice updated';
                    return response()->json(compact('status', 'message', 'invoice'));
                } catch (\Exception $e) {
                    $status = false;
                    $errors = $e;
                    Log::error($e);
                    return response()->json(compact('status', 'errors'));
                }
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

    public function deleteInvoice(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                DB::table('invoices')->where('invoice_id', $id)->where('company_id', $companyId)->delete();
                DB::table('invoice_items')->where('invoice_id', $id)->where('company_id', $companyId)->delete();
                DB::table('customer_ledgers')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('profits')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('card_transactions')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('nagad_transactions')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('bkash_transactions')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('cash_books')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('bank_ledgers')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                $status = true;
                $message = 'Invoice deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Invoice not found';
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
    | Invoice End
    |--------------------------------------------------------------------------
    */
}
