<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;

class SaleReturnController extends Controller
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
                $returns = DB::table('sale_returns')
                    ->where('sale_returns.company_id', $companyId)
                    ->orderBy('id', 'desc')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'returns'));
            } else {
                $returns = DB::table('sale_returns')
                    ->where('sale_returns.company_id', $companyId)
                    ->where('sale_returns.return_id', 'like', '%' . $name . '%')
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
            $customerId = $request->customerId;
            $products = $request->productIds;
            $quantities = $request->productQuantities;
            $prices = $request->productPrices;
            if (count($products) > 0) {
                try {
                    DB::transaction(function () use ($request, $companyId, $products, $quantities, $prices, $customerId) {
                        $txGenerator = new InvoiceNumberGeneratorService();
                        $returnId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('return');
                        $txGenerator->setNextInvoiceNo();
                        for ($i = 0, $n = count($products); $i < $n; $i++) {
                            $productID = $products[$i];
                            $quantity = $quantities[$i];
                            $price = $prices[$i];

                            if ($quantity > 0) {
                                DB::table('sale_return_items')->insert([
                                    'return_id' => $returnId,
                                    'product_id' => $productID,
                                    'price' => $price,
                                    'user_id' => Auth::id(),
                                    'company_id' => $companyId,
                                    'quantity' => $quantity,
                                    'date' => $request->date,
                                    'total' => $quantity * $price,
                                ]);
                                $purchasePrice = DB::table('average_purchase_prices')->where('product_id', $productID)->where('company_id', $companyId)->first();

                                $profit += ($quantity * $price) - ($quantity * $purchasePrice->price);
                            }
                        }
                        DB::table('profits')->insert(
                            [
                                'date' => $request->date,
                                'deduct' => $profit,
                                'company_id' => $companyId,
                                'reference_no' => "ret-$returnId",
                            ]
                        );
                        DB::table('sale_returns')->insert(
                            [
                                'return_id' => $returnId,
                                'return_amount' => $request->total,
                                'note' => $request->note,
                                'user_id' => Auth::id(),
                                'date' => $request->date,
                                'account' => $request->account,
                                'type' => 'dsr',
                                'company_id' => $companyId,
                            ]
                        );
                        if ($request->account == 'cash' && !empty($request->total)){
                            $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('customer_transaction');
                            DB::table('cash_books')->insert(array(
                                'transaction_id' => $cashTxId,
                                'company_id' => $companyId,
                                'user_id' => Auth::id(),
                                'reference_no' => "ret-$returnId",
                                'type' => 'payment',
                                'payment' => $request->total,
                                'date' => $request->date,
                                'comment' => "Cash deduct for Return No ($returnId)"
                            ));
                        }

                        if ($request->account == 'bkash' && !empty($request->total)) {
                            $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('bkash_transaction');
                            DB::table('bkash_transactions')->insert(array(
                                'transaction_id' => $bkashTxId,
                                'company_id' => $companyId,
                                'reference_no' => "ret-$returnId",
                                'type' => 'withdraw',
                                'user_id' => Auth::id(),
                                'withdraw' => $request->total,
                                'date' => $request->date,
                                'comment' => "Deduct for Return No ($returnId)"
                            ));
                            $txGenerator->setNextInvoiceNo();
                        }

                        if ($request->account == 'nagad' && !empty($request->total)) {
                            $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('nagad_transaction');
                            DB::table('nagad_transactions')->insert(array(
                                'transaction_id' => $nagadTxId,
                                'company_id' => $companyId,
                                'reference_no' => "ret-$returnId",
                                'type' => 'withdraw',
                                'user_id' => Auth::id(),
                                'withdraw' => $request->total,
                                'date' => $request->date,
                                'comment' => "Deduct for Return No ($returnId)"
                            ));
                            $txGenerator->setNextInvoiceNo();
                        }

                        if ($request->account == 'bank' && !empty($request->bankId) && !empty($request->total)) {
                            $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                            DB::table('bank_ledgers')->insert(array(
                                'transaction_id' => $bankTxId,
                                'reference_no' => 'ret-' . $returnId,
                                'type' => 'withdraw',
                                'user_id' => Auth::id(),
                                'withdraw' => $request->total,
                                'bank_id' => $request->bankId,
                                'date' => $request->date,
                                'company_id' => $companyId,
                                'comment' => "Deduct for Return No ($returnId)"
                            ));
                            $txGenerator->setNextInvoiceNo();
                        }

                        if ($request->account == 'customer' && !empty($request->total)) {
                            $customerTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('customer_transaction');
                            DB::table('customer_ledgers')->insert(array(
                                'customer_id' => $customerId,
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
                DB::table('sale_returns')->where('return_id', $id)->where('company_id', $companyId)->delete();
                DB::table('profits')->where('reference_no', "ret-$id")->where('company_id', $companyId)->delete();
                DB::table('sale_return_items')->where('return_id', $id)->where('company_id', $companyId)->delete();
                DB::table('customer_ledgers')->where('reference_no', "ret-$id")->where('company_id', $companyId)->delete();
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
