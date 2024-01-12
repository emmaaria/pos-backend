<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomerController extends Controller
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
    | Customer Start
    |--------------------------------------------------------------------------
    */
    public function getCustomers(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $customers = DB::table('customers')
                    ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                    ->where('customers.company_id', $companyId)
                    ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'customers'));
            } elseif ($all) {
                $customers = DB::table('customers')
                    ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                    ->where('customers.company_id', $companyId)
                    ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                    ->get();
                $status = true;
                return response()->json(compact('status', 'customers'));
            } else {
                $customers = DB::table('customers')
                    ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                    ->where('customers.company_id', $companyId)
                    ->where('customers.name', 'like', '%' . $name . '%')
                    ->orWhere('customers.mobile', 'like', '%' . $name . '%')
                    ->orWhere('customers.address', 'like', '%' . $name . '%')
                    ->where('customers.company_id', $companyId)
                    ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'customers'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getOldCustomers(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            $cacheKey = 'customers_without_invoices_' . $companyId;
            if (empty($name) && empty($all)) {
                $customers = Cache::remember($cacheKey, 60, function () use ($companyId) {
                    return DB::table('customers')
                        ->select(
                            'customers.id',
                            'customers.name',
                            'customers.mobile',
                            'customers.address',
                            DB::raw('SUM(due) as due'),
                            DB::raw('SUM(deposit) as deposit'),
                            DB::raw('SUM(due - deposit) as balance')
                        )
                        ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                        ->leftJoin('invoices', function ($join) {
                            $join->on('customers.id', '=', 'invoices.customer_id')
                                ->where('invoices.date', '>=', now()->subDays(90)->toDateString());
                        })
                        ->where('customers.company_id', $companyId)
                        ->whereNull('invoices.id')
                        ->orWhere('invoices.date', '<', now()->subDays(90)->toDateString())
                        ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                        ->paginate(50);
                });
                $status = true;
                return response()->json(compact('status', 'customers'));
            } elseif ($all) {
                $customers = Cache::remember($cacheKey, 60, function () use ($companyId) {
                    return DB::table('customers')
                        ->select(
                            'customers.id',
                            'customers.name',
                            'customers.mobile',
                            'customers.address',
                            DB::raw('SUM(due) as due'),
                            DB::raw('SUM(deposit) as deposit'),
                            DB::raw('SUM(due - deposit) as balance')
                        )
                        ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                        ->leftJoin('invoices', function ($join) {
                            $join->on('customers.id', '=', 'invoices.customer_id')
                                ->where('invoices.date', '>=', now()->subDays(90)->toDateString());
                        })
                        ->where('customers.company_id', $companyId)
                        ->whereNull('invoices.id')
                        ->orWhere('invoices.date', '<', now()->subDays(90)->toDateString())
                        ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                        ->get(50);
                });
                $status = true;
                return response()->json(compact('status', 'customers'));
            } else {
                $customers = DB::table('customers')
                    ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                    ->where('customers.company_id', $companyId)
                    ->where('customers.name', 'like', '%' . $name . '%')
                    ->orWhere('customers.mobile', 'like', '%' . $name . '%')
                    ->orWhere('customers.address', 'like', '%' . $name . '%')
                    ->where('customers.company_id', $companyId)
                    ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'customers'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getCustomer($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $customer = DB::table('customers')->where('id', $id)->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'customer'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeCustomer(Request $request)
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
                $customer = DB::transaction(function () use ($companyId, $request) {
                    $customerId = DB::table('customers')->insertGetId([
                        'name' => $request->name,
                        'mobile' => $request->mobile,
                        'address' => $request->address,
                        'company_id' => $companyId,
                        'additionalInfo' => $request->additionalInfo,
                    ]);
                    if (!empty($request->due)) {
                        $txIdGenerator = new InvoiceNumberGeneratorService();
                        $txId = $txIdGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('customer_transaction');
                        DB::table('customer_ledgers')->insert(array(
                            'customer_id' => $customerId,
                            'transaction_id' => $txId,
                            'user_id' => Auth::id(),
                            'company_id' => $companyId,
                            'type' => 'due',
                            'due' => $request->due,
                            'deposit' => 0,
                            'date' => date('Y-m-d'),
                            'comment' => 'Previous Due'
                        ));
                        $txIdGenerator->setNextInvoiceNo();
                    }
                    $customer = DB::table('customers')
                        ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                        ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                        ->where('customers.company_id', $companyId)
                        ->where('customers.id', $customerId)
                        ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                        ->first();
                    return $customer;
                });
                $status = true;
                $message = 'Successfully saved';
                return response()->json(compact('status', 'message', 'customer'));
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

    public function updateCustomer(Request $request)
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
            DB::table('customers')->where('id', $request->id)->where('company_id', $companyId)->update([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'address' => $request->address,
                'additionalInfo' => $request->additionalInfo
            ]);
            $status = true;
            $message = 'Updated';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteCustomer(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                try {
                    DB::transaction(function () use ($companyId, $id) {
                        DB::table('customers')->where('id', $id)->where('company_id', $companyId)->delete();
                        DB::table('customer_ledgers')->where('company_id', $companyId)->where('customer_id', $id)->delete();
                        $invoices = DB::table('invoices')->where('customer_id', $id)->where('company_id', $companyId)->get();
                        foreach ($invoices as $invoice) {
                            DB::table('invoice_items')->where('invoice_id', $invoice->invoice_id)->where('company_id', $companyId)->delete();
                            DB::table('bkash_transactions')->where('reference_no', 'inv-' . $invoice->invoice_id)->where('company_id', $companyId)->delete();
                            DB::table('card_transactions')->where('reference_no', 'inv-' . $invoice->invoice_id)->where('company_id', $companyId)->delete();
                            DB::table('cash_books')->where('reference_no', 'inv-' . $invoice->invoice_id)->where('company_id', $companyId)->delete();
                            DB::table('nagad_transactions')->where('reference_no', 'inv-' . $invoice->invoice_id)->where('company_id', $companyId)->delete();
                        }
                    });
                } catch (Exception $e) {
                    $status = false;
                    $errors = 'Something went wrong';
                    return response()->json(compact('status', 'errors'));
                }

                $status = true;
                $message = 'Customer deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Customer not found';
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
                    'customer' => 'required',
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
                    $paidId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('customer_transaction');
                    $txGenerator->setNextInvoiceNo();
                    DB::table('customer_ledgers')->insert(array(
                        'customer_id' => $request->customer,
                        'transaction_id' => $paidId,
                        'reference_no' => "c-rec-$paidId",
                        'type' => 'deposit',
                        'company_id' => $companyId,
                        'due' => 0,
                        'deposit' => $request->amount,
                        'user_id' => Auth::id(),
                        'date' => $request->date,
                        'comment' => $request->note !== '' ? $request->note . " (Paid ID : $paidId)" : "Due paid (Paid ID : $paidId)"
                    ));

                    if ($request->account == 'cash') {
                        $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('cash_transaction');
                        DB::table('cash_books')->insert(array(
                            'transaction_id' => $cashTxId,
                            'company_id' => $companyId,
                            'reference_no' => "c-rec-$paidId",
                            'type' => 'receive',
                            'user_id' => Auth::id(),
                            'receive' => $request->amount,
                            'date' => $request->date,
                            'comment' => $request->note !== '' ? $request->note . " (Paid ID : $paidId)" : "Due paid (Paid ID : $paidId)"
                        ));
                    }

                    if ($request->account == 'bkash') {
                        $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('bkash_transaction');
                        DB::table('bkash_transactions')->insert(array(
                            'transaction_id' => $bkashTxId,
                            'company_id' => $companyId,
                            'reference_no' => "c-rec-$paidId",
                            'type' => 'deposit',
                            'user_id' => Auth::id(),
                            'deposit' => $request->amount,
                            'date' => $request->date,
                            'comment' => $request->note !== '' ? $request->note . " (Paid ID : $paidId)" : "Due paid (Paid ID : $paidId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }

                    if ($request->account == 'nagad') {
                        $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('nagad_transaction');
                        DB::table('nagad_transactions')->insert(array(
                            'transaction_id' => $nagadTxId,
                            'company_id' => $companyId,
                            'reference_no' => "c-rec-$paidId",
                            'type' => 'deposit',
                            'user_id' => Auth::id(),
                            'deposit' => $request->amount,
                            'date' => $request->date,
                            'comment' => $request->note !== '' ? $request->note . " (Paid ID : $paidId)" : "Due paid (Paid ID : $paidId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }

                    if ($request->account == 'bank' && !empty($request->bankId)) {
                        $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                        DB::table('bank_ledgers')->insert(array(
                            'transaction_id' => $bankTxId,
                            'reference_no' => "c-rec-$paidId",
                            'type' => 'deposit',
                            'user_id' => Auth::id(),
                            'deposit' => $request->deposit,
                            'bank_id' => $request->bankId,
                            'date' => $request->date,
                            'company_id' => $companyId,
                            'comment' => $request->note !== '' ? $request->note . " (Paid ID : $paidId)" : "Due paid (Paid ID : $paidId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }
                });
                $status = true;
                $message = 'Customer payment saved';
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

    public function deletePayment(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                DB::table('customer_ledgers')->where('reference_no', "c-rec-$id")->where('company_id', $companyId)->delete();
                DB::table('nagad_transactions')->where('reference_no', "c-rec-$id")->where('company_id', $companyId)->delete();
                DB::table('bkash_transactions')->where('reference_no', "c-rec-$id")->where('company_id', $companyId)->delete();
                DB::table('cash_books')->where('reference_no', "c-rec-$id")->where('company_id', $companyId)->delete();
                DB::table('bank_ledgers')->where('reference_no', "c-rec-$id")->where('company_id', $companyId)->delete();
                $status = true;
                $message = 'Customer payment deleted';
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

    public function customerPaymentList(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $data = DB::table('customer_ledgers')
                ->select('customer_ledgers.transaction_id', 'customer_ledgers.deposit', 'customer_ledgers.date', 'customer_ledgers.comment', 'customers.name')
                ->where('customer_ledgers.company_id', $companyId)
                ->leftJoin('customers', 'customers.id', '=', 'customer_ledgers.customer_id')
                ->where('customer_ledgers.type', 'deposit')
                ->where('customer_ledgers.reference_no', 'like', "c-rec%");
            if (!empty($request->customer)) {
                $data = $data->where('customer_ledgers.customer_id', $request->customer);
            }
            if (!empty($request->startDate)) {
                $data = $data->where('customer_ledgers.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $data = $data->where('customer_ledgers.date', '<=', $request->endDate);
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

    public function customerDueList(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $data = DB::table('customer_ledgers')
                ->select('customer_ledgers.transaction_id', 'customer_ledgers.due', 'customer_ledgers.date', 'customer_ledgers.comment', 'customers.name')
                ->where('customer_ledgers.company_id', $companyId)
                ->leftJoin('customers', 'customers.id', '=', 'customer_ledgers.customer_id')
                ->where('customer_ledgers.type', 'due')
                ->where('customer_ledgers.reference_no', 'like', "m-due%");
            if (!empty($request->customer)) {
                $data = $data->where('customer_ledgers.customer_id', $request->customer);
            }
            if (!empty($request->startDate)) {
                $data = $data->where('customer_ledgers.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $data = $data->where('customer_ledgers.date', '<=', $request->endDate);
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

    public function storeDue(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'customer' => 'required',
                    'date' => 'required',
                    'amount' => 'required',
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
                    $paidId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('customer_transaction');
                    $txGenerator->setNextInvoiceNo();
                    DB::table('customer_ledgers')->insert(array(
                        'customer_id' => $request->customer,
                        'transaction_id' => $paidId,
                        'reference_no' => "m-due-$paidId",
                        'type' => 'due',
                        'company_id' => $companyId,
                        'due' => $request->amount,
                        'deposit' => 0,
                        'user_id' => Auth::id(),
                        'date' => $request->date,
                        'comment' => $request->note !== '' ? $request->note . " (Due ID : $paidId)" : "Manual due (Due ID : $paidId)"
                    ));

                });
                $status = true;
                $message = 'Customer due saved';
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

    public function deleteDue(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                DB::table('customer_ledgers')->where('reference_no', "m-due-$id")->where('company_id', $companyId)->delete();
                $status = true;
                $message = 'Customer due deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Customer due not found';
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
    | Customer End
    |--------------------------------------------------------------------------
    */
}
