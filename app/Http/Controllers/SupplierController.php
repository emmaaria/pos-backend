<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;

class SupplierController extends Controller
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
    | Supplier Start
    |--------------------------------------------------------------------------
    */
    public function getSuppliers(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $suppliers = DB::table('suppliers')
                    ->select('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('supplier_ledgers', 'supplier_ledgers.supplier_id', '=', 'suppliers.id')
                    ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address')
                    ->orderBy('suppliers.id', 'desc')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'suppliers'));
            } elseif (!empty($all)) {
                $suppliers = DB::table('suppliers')
                    ->select('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('supplier_ledgers', 'supplier_ledgers.supplier_id', '=', 'suppliers.id')
                    ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address')
                    ->orderBy('suppliers.id', 'desc')
                    ->get();
                $status = true;
                return response()->json(compact('status', 'suppliers'));
            } else {
                $suppliers = DB::table('suppliers')
                    ->select('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('supplier_ledgers', 'supplier_ledgers.supplier_id', '=', 'suppliers.id')
                    ->where('suppliers.name', 'like', '%' . $name . '%')
                    ->orWhere('suppliers.mobile', 'like', '%' . $name . '%')
                    ->orWhere('suppliers.address', 'like', '%' . $name . '%')
                    ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'suppliers'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getSupplier($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $supplier = DB::table('suppliers')->where('id', $id)->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'supplier'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeSupplier(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            try {
                DB::transaction(function () use ($companyId, $request) {
                    $supplierId = DB::table('suppliers')->insertGetId(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address, 'company_id' => $companyId]);
                    if (!empty($request->balanceType)) {
                        if ($request->balanceType == 'due') {
                            if (!empty($request->balance)) {
                                $txIdGenerator = new InvoiceNumberGeneratorService();
                                $txId = $txIdGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $supplierId,
                                    'transaction_id' => $txId,
                                    'type' => 'due',
                                    'user_id' => Auth::id(),
                                    'due' => $request->balance,
                                    'company_id' => $companyId,
                                    'deposit' => 0,
                                    'date' => date('Y-m-d'),
                                    'comment' => 'Previous Due'
                                ));
                                $txIdGenerator->setNextInvoiceNo();
                            }
                        }
                        if ($request->balanceType == 'advance') {
                            if (!empty($request->balance)) {
                                $txIdGenerator = new InvoiceNumberGeneratorService();
                                $txId = $txIdGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $supplierId,
                                    'transaction_id' => $txId,
                                    'type' => 'deposit',
                                    'user_id' => Auth::id(),
                                    'deposit' => $request->balance,
                                    'company_id' => $companyId,
                                    'due' => 0,
                                    'date' => date('Y-m-d'),
                                    'comment' => 'Previous Advance Balance'
                                ));
                                $txIdGenerator->setNextInvoiceNo();
                            }
                        }
                    }
                });
                $status = true;
                return response()->json(compact('status'));
            } catch (Exception $e) {
                $status = false;
                $errors = 'Something went wrong';
                return response()->json(compact('status', 'errors'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updateSupplier(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'id' => 'required',
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            DB::table('suppliers')->where('id', $request->id)->where('company_id', $companyId)->update(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address]);
            $status = true;
            $message = 'Updated';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteSupplier(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                try {
                    DB::transaction(function () use ($companyId, $id) {
                        DB::table('suppliers')->where('id', $id)->where('company_id', $companyId)->delete();
                        DB::table('supplier_ledgers')->where('company_id', $companyId)->where('supplier_id', $id)->delete();
                        $purchases = DB::table('purchases')->where('supplier_id', $id)->where('company_id', $companyId)->get();
                        DB::table('supplier_products')->where('supplier_id', $id)->where('company_id', $companyId)->delete();
                        if ($purchases) {
                            foreach ($purchases as $purchase) {
                                DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)->where('company_id', $companyId)->delete();
                                DB::table('bkash_transactions')->where('reference_no', 'pur-' . $purchase->purchase_id)->where('company_id', $companyId)->delete();
                                DB::table('card_transactions')->where('reference_no', 'pur-' . $purchase->purchase_id)->where('company_id', $companyId)->delete();
                                DB::table('cash_books')->where('reference_no', 'pur-' . $purchase->purchase_id)->where('company_id', $companyId)->delete();
                                DB::table('nagad_transactions')->where('reference_no', 'pur-' . $purchase->purchase_id)->where('company_id', $companyId)->delete();
                                DB::table('purchases')->where('supplier_id', $id)->where('company_id', $companyId)->where('id', $purchase->id)->delete();
                            }
                        }
                    });
                } catch (Exception $e) {
                    $status = false;
                    $errors = 'Something went wrong';
                    return response()->json(compact('status', 'errors'));
                }

                $status = true;
                $message = 'Supplier deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Supplier not found';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }
    public function storePayment(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'supplier' => 'required',
                    'date' => 'required',
                    'amount' => 'required',
                    'account' => 'required',
                    'bankId' => 'required_if:account,==,bank',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            try {
                DB::transaction(function () use ($request, $companyId) {
                    $txGenerator = new InvoiceNumberGeneratorService();
                    $paidId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('supplier_transaction');
                    $txGenerator->setNextInvoiceNo();
                    DB::table('supplier_ledgers')->insert(array(
                        'supplier_id' => $request->supplier,
                        'transaction_id' => $paidId,
                        'reference_no' => "s-pay-$paidId",
                        'type' => 'deposit',
                        'company_id' => $companyId,
                        'due' => 0,
                        'deposit' => $request->amount,
                        'date' => $request->date,
                        'comment' => $request->note !== '' ? $request->note." (Payment ID : $paidId)" : "Due payment (Payment ID : $paidId)"
                    ));

                    if ($request->account == 'cash') {
                        $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('cash_transaction');
                        DB::table('cash_books')->insert(array(
                            'transaction_id' => $cashTxId,
                            'company_id' => $companyId,
                            'reference_no' => "s-pay-$paidId",
                            'type' => 'payment',
                            'payment' => $request->amount,
                            'date' => $request->date,
                            'comment' => $request->note !== '' ? $request->note." (Payment ID : $paidId)" : "Due payment (Payment ID : $paidId)"
                        ));
                    }

                    if ($request->account == 'bkash') {
                        $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('bkash_transaction');
                        DB::table('bkash_transactions')->insert(array(
                            'transaction_id' => $bkashTxId,
                            'company_id' => $companyId,
                            'reference_no' => "s-pay-$paidId",
                            'type' => 'withdraw',
                            'withdraw' => $request->amount,
                            'date' => $request->date,
                            'comment' => $request->note !== '' ? $request->note." (Payment ID : $paidId)" : "Due payment (Payment ID : $paidId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }

                    if ($request->account == 'nagad') {
                        $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('nagad_transaction');
                        DB::table('nagad_transactions')->insert(array(
                            'transaction_id' => $nagadTxId,
                            'company_id' => $companyId,
                            'reference_no' => "s-pay-$paidId",
                            'type' => 'withdraw',
                            'withdraw' => $request->amount,
                            'date' => $request->date,
                            'comment' => $request->note !== '' ? $request->note." (Payment ID : $paidId)" : "Due payment (Payment ID : $paidId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }

                    if ($request->account == 'bank' && !empty($request->bankId)) {
                        $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                        DB::table('bank_ledgers')->insert(array(
                            'transaction_id' => $bankTxId,
                            'reference_no' => "s-pay-$paidId",
                            'type' => 'withdraw',
                            'withdraw' => $request->deposit,
                            'bank_id' => $request->bankId,
                            'date' => $request->date,
                            'company_id' => $companyId,
                            'comment' => $request->note !== '' ? $request->note." (Payment ID : $paidId)" : "Due payment (Payment ID : $paidId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }
                });
                $status = true;
                $message = 'Supplier payment saved';
                return response()->json(compact('status', 'message'));
            } catch (Exception $e) {
                $status = false;
                $errors = $e;
                return response()->json(compact('status', 'errors'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function paymentList(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $data = DB::table('supplier_ledgers')
                ->select('supplier_ledgers.transaction_id', 'supplier_ledgers.deposit', 'supplier_ledgers.date', 'supplier_ledgers.comment', 'suppliers.name')
                ->where('supplier_ledgers.company_id', $companyId)
                ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_ledgers.supplier_id')
                ->where('supplier_ledgers.type', 'deposit')
                ->where('supplier_ledgers.reference_no', 'like', "s-pay%");
            if (!empty($request->supplier)) {
                $data = $data->where('supplier_ledgers.supplier_id', $request->supplier);
            }
            if (!empty($request->startDate)) {
                $data = $data->where('supplier_ledgers.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $data = $data->where('supplier_ledgers.date', '<=', $request->endDate);
            }
            $data = $data->paginate(50);
            $status = true;
            return response()->json(compact('status', 'data'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deletePayment(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                DB::table('supplier_ledgers')->where('reference_no', "s-pay-$id")->where('company_id', $companyId)->delete();
                DB::table('nagad_transactions')->where('reference_no', "s-pay-$id")->where('company_id', $companyId)->delete();
                DB::table('bkash_transactions')->where('reference_no', "s-pay-$id")->where('company_id', $companyId)->delete();
                DB::table('cash_books')->where('reference_no', "s-pay-$id")->where('company_id', $companyId)->delete();
                DB::table('bank_ledgers')->where('reference_no', "s-pay-$id")->where('company_id', $companyId)->delete();
                $status = true;
                $message = 'Supplier payment deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Customer payment not found';
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
    | Supplier End
    |--------------------------------------------------------------------------
    */
}
