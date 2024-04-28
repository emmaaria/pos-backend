<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;

class PurchaseReturnController extends Controller
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
    | Return Start
    |--------------------------------------------------------------------------
    */

    public function getReturns(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->keyword;
            if (empty($name)) {
                $returns = DB::table('purchase_returns')
                    ->select('purchase_returns.*', 'suppliers.name')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_returns.supplier_id')
                    ->where('purchase_returns.company_id', $companyId)
                    ->orderBy('id', 'desc')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'returns'));
            } else {
                $returns = DB::table('purchase_returns')
                    ->select('purchase_returns.*', 'suppliers.name')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_returns.supplier_id')
                    ->where('purchase_returns.company_id', $companyId)
                    ->where('purchase_returns.return_id', 'like', '%' . $name . '%')
                    ->orWhere('suppliers.name', 'like', '%' . $name . '%')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'returns'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getReturn($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $return = DB::table('purchase_returns')
                ->select('purchase_returns.*', 'suppliers.name AS supplierName')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_returns.supplier_id')
                ->where('purchase_returns.company_id', $companyId)
                ->where('purchase_returns.return_id', $id)
                ->orderBy('id', 'desc')
                ->first();
            $returnItems = DB::table('purchase_return_items')
                ->select('purchase_return_items.*', 'products.name')
                ->leftJoin('products', 'products.product_id', '=', 'sale_return_items.product_id')
                ->where('sale_return_items.return_id', $id)
                ->get();
            $status = true;
            return response()->json(compact('status', 'return', 'returnItems'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeReturn(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'date' => 'required',
                    'productIds' => 'required',
                    'productQuantities' => 'required',
                    'productPrices' => 'required',
                    'total' => 'required',
                    'supplierId' => 'required',
                    'account' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            if ($request->account == 'bank') {
                if (empty($request->bankId)) {
                    $status = false;
                    $errors = 'Please select bank account';
                    return response()->json(compact('status', 'errors'));
                }
            }
            $supplierId = $request->supplierId;
            $products = $request->productIds;
            $quantities = $request->productQuantities;
            $prices = $request->productPrices;
            if (count($products) > 0) {
                try {
                    DB::transaction(function () use ($request, $companyId, $products, $quantities, $prices, $supplierId) {
                        $profit = 0;
                        $txGenerator = new InvoiceNumberGeneratorService();
                        $returnId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('return');
                        $txGenerator->setNextInvoiceNo();
                        for ($i = 0, $n = count($products); $i < $n; $i++) {
                            $productID = $products[$i];
                            $quantity = $quantities[$i];
                            $price = $prices[$i];

                            if ($quantity > 0) {
                                DB::table('purchase_return_items')->insert([
                                    'return_id' => $returnId,
                                    'product_id' => $productID,
                                    'price' => $price,
                                    'user_id' => Auth::id(),
                                    'company_id' => $companyId,
                                    'quantity' => $quantity,
                                    'date' => $request->date,
                                    'supplier_id' => $supplierId,
                                    'total' => $quantity * $price,
                                ]);
                            }
                        }
                        DB::table('purchase_returns')->insert(
                            [
                                'return_id' => $returnId,
                                'return_amount' => $request->total,
                                'note' => $request->note,
                                'user_id' => Auth::id(),
                                'date' => $request->date,
                                'account' => $request->account,
                                'supplier_id' => $supplierId,
                                'type' => 'dsr',
                                'company_id' => $companyId,
                            ]
                        );
                        if ($request->account == 'cash' && !empty($request->total)) {
                            $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('cash_transaction');
                            DB::table('cash_books')->insert(array(
                                'transaction_id' => $cashTxId,
                                'company_id' => $companyId,
                                'user_id' => Auth::id(),
                                'reference_no' => "ret-$returnId",
                                'type' => 'receive',
                                'receive' => $request->total,
                                'date' => $request->date,
                                'comment' => "Cash Deposit for Return No ($returnId)"
                            ));
                        }

                        if ($request->account == 'bkash' && !empty($request->total)) {
                            $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('bkash_transaction');
                            DB::table('bkash_transactions')->insert(array(
                                'transaction_id' => $bkashTxId,
                                'company_id' => $companyId,
                                'reference_no' => "ret-$returnId",
                                'type' => 'deposit',
                                'user_id' => Auth::id(),
                                'deposit' => $request->total,
                                'date' => $request->date,
                                'comment' => "Deposit for Return No ($returnId)"
                            ));
                            $txGenerator->setNextInvoiceNo();
                        }

                        if ($request->account == 'nagad' && !empty($request->total)) {
                            $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('nagad_transaction');
                            DB::table('nagad_transactions')->insert(array(
                                'transaction_id' => $nagadTxId,
                                'company_id' => $companyId,
                                'reference_no' => "ret-$returnId",
                                'type' => 'deposit',
                                'user_id' => Auth::id(),
                                'deposit' => $request->total,
                                'date' => $request->date,
                                'comment' => "Deposit for Return No ($returnId)"
                            ));
                            $txGenerator->setNextInvoiceNo();
                        }

                        if ($request->account == 'bank' && !empty($request->bankId) && !empty($request->total)) {
                            $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                            DB::table('bank_ledgers')->insert(array(
                                'transaction_id' => $bankTxId,
                                'reference_no' => 'ret-' . $returnId,
                                'type' => 'deposit',
                                'user_id' => Auth::id(),
                                'deposit' => $request->total,
                                'bank_id' => $request->bankId,
                                'date' => $request->date,
                                'company_id' => $companyId,
                                'comment' => "Deposit for Return No ($returnId)"
                            ));
                            $txGenerator->setNextInvoiceNo();
                        }

                        if ($request->account == 'supplier' && !empty($request->total)) {
                            $customerTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('supplier_transaction');
                            DB::table('supplier_ledgers')->insert(array(
                                'supplier_id' => $supplierId,
                                'transaction_id' => $customerTxId,
                                'reference_no' => "ret-$returnId",
                                'type' => 'deposit',
                                'user_id' => Auth::id(),
                                'company_id' => $companyId,
                                'deposit' => $request->total,
                                'date' => $request->date,
                                'comment' => "Due Deduct for Return No ($returnId)"
                            ));
                            $txGenerator->setNextInvoiceNo();
                        }
                    });
                    $status = true;
                    $message = 'Return saved';
                    return response()->json(compact('status', 'message'));
                } catch (Exception $e) {
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

    public function delete(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                DB::table('purchase_returns')->where('return_id', $id)->where('company_id', $companyId)->delete();
                DB::table('purchase_return_items')->where('return_id', $id)->where('company_id', $companyId)->delete();
                DB::table('supplier_ledgers')->where('reference_no', "ret-$id")->where('company_id', $companyId)->delete();
                DB::table('nagad_transactions')->where('reference_no', "ret-$id")->where('company_id', $companyId)->delete();
                DB::table('bkash_transactions')->where('reference_no', "ret-$id")->where('company_id', $companyId)->delete();
                DB::table('cash_books')->where('reference_no', "ret-$id")->where('company_id', $companyId)->delete();
                DB::table('bank_ledgers')->where('reference_no', "ret-$id")->where('company_id', $companyId)->delete();
                $status = true;
                $message = 'Return deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Return not found';
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
    | Return End
    |--------------------------------------------------------------------------
    */
}
