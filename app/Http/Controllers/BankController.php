<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;

class BankController extends Controller
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
    | Bank Start
    |--------------------------------------------------------------------------
    */
    public function getBanks(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $banks = DB::table('banks')
                    ->select('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type', DB::raw('SUM(withdraw) as withdraw'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(deposit - withdraw) as balance'))
                    ->leftJoin('bank_ledgers', 'bank_ledgers.bank_id', '=', 'banks.id')
                    ->where('banks.company_id', $companyId)
                    ->groupBy('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'banks'));
            } elseif (!empty($all)) {
                $banks = DB::table('banks')
                    ->select('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type', DB::raw('SUM(withdraw) as withdraw'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(deposit - withdraw) as balance'))
                    ->leftJoin('bank_ledgers', 'bank_ledgers.bank_id', '=', 'banks.id')
                    ->where('banks.company_id', $companyId)
                    ->groupBy('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type')
                    ->get();
                $status = true;
                return response()->json(compact('status', 'banks'));
            } else {
                $banks = DB::table('banks')
                    ->select('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type', DB::raw('SUM(withdraw) as withdraw'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(deposit - withdraw) as balance'))
                    ->leftJoin('bank_ledgers', 'bank_ledgers.bank_id', '=', 'banks.id')
                    ->where('banks.company_id', $companyId)
                    ->where('banks.name', 'like', '%' . $name . '%')
                    ->orWhere('banks.account_name', 'like', '%' . $name . '%')
                    ->orWhere('banks.account_no', 'like', '%' . $name . '%')
                    ->groupBy('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'banks'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getBank($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $bank = DB::table('banks')->where('id', $id)->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'bank'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeBank(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'account_name' => 'required',
                    'bank_type' => 'required',
                    'account_no' => 'required',
                    'branch' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            try {
                DB::transaction(function () use ($companyId, $request) {
                    $bankId = DB::table('banks')->insertGetId([
                        'name' => $request->name,
                        'account_name' => $request->account_name,
                        'account_no' => $request->account_no,
                        'branch' => $request->branch,
                        'company_id' => $companyId,
                        'bank_type' => $request->bank_type
                    ]);
                    if (!empty($request->balance)) {
                        $txIdGenerator = new InvoiceNumberGeneratorService();
                        $txId = $txIdGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                        if ($request->bank_type == 'saving') {
                            DB::table('bank_ledgers')->insert(array(
                                'bank_id' => $bankId,
                                'transaction_id' => $txId,
                                'user_id' => Auth::id(),
                                'comment' => "Previous balance",
                                'type' => 'deposit',
                                'withdraw' => 0,
                                'deposit' => $request->balance,
                                'company_id' => $companyId,
                                'date' => date('Y-m-d')
                            ));
                        } else {
                            DB::table('bank_ledgers')->insert(array(
                                'bank_id' => $bankId,
                                'transaction_id' => $txId,
                                'user_id' => Auth::id(),
                                'comment' => "Previous balance",
                                'type' => 'withdraw',
                                'withdraw' => $request->balance,
                                'deposit' => 0,
                                'company_id' => $companyId,
                                'date' => date('Y-m-d')
                            ));
                        }
                        $txIdGenerator->setNextInvoiceNo();
                    }
                });
            } catch (Exception $e) {
                $status = false;
                $errors = 'Something went wrong';
                return response()->json(compact('status', 'errors'));
            }
            $status = true;
            $message = 'Successfully saved';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updateBank(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'account_name' => 'required',
                    'bank_type' => 'required',
                    'account_no' => 'required',
                    'branch' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            DB::table('banks')->where('id', $request->id)->where('company_id', $companyId)->update(
                [
                    'name' => $request->name,
                    'account_name' => $request->account_name,
                    'account_no' => $request->account_no,
                    'branch' => $request->branch,
                    'bank_type' => $request->bank_type
                ]
            );
            $status = true;
            $message = 'Updated';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteBank(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                $check = DB::table('bank_ledgers')
                    ->where('company_id', $companyId)
                    ->where('bank_id', $id)
                    ->whereNotNull('reference_no')
                    ->get();
                if (count($check) > 0) {
                    $status = false;
                    $errors = 'You can not delete this bank as it already used for calculation';
                    return response()->json(compact('status', 'errors'));
                }
                try {
                    DB::transaction(function () use ($companyId, $id) {
                        DB::table('banks')->where('id', $id)->where('company_id', $companyId)->delete();
                        DB::table('bank_ledgers')->where('company_id', $companyId)->where('bank_id', $id)->delete();
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

    /*
    |--------------------------------------------------------------------------
    | Bank End
    |--------------------------------------------------------------------------
    */
}
