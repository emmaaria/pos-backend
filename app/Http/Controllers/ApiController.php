<?php

namespace App\Http\Controllers;

use App\Models\AveragePurchasePrice;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;


class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom:api', ['except' => ['login']]);
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

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'mobile' => 'required',
                'company_id' => 'required',
                'password' => 'required',
            ],
            [
                'mobile.required' => 'Mobile number required',
                'company_id.required' => 'Company number required',
                'password.required' => 'Password required',
            ]
        );
        if ($validator->fails()) {
            $status = false;
            $errors = '';
            foreach ($validator->errors()->all() as $message) {
                $errors .= $message;
            }
            return response()->json(compact('status', 'errors'));
        }
        $credentials = array('email' => $request->mobile, 'password' => $request->password);
        $user = DB::table('users')->where('email', $request->mobile)->where('company_id', $request->company_id)->first();
        $company = DB::table('companies')->where('company_id', $request->company_id)->first();
        if ($user && $company) {
            if (!empty($user)) {
                $userData = array(
                    'company_id' => encrypt($user->company_id),
                );
            } else {
                $userData = null;
            }
            if (!$token = auth()->claims($userData)->attempt($credentials)) {
                $status = false;
                $errors = 'Credentials did not matched';
                return response()->json(compact('status', 'errors'));
            }
            $company = array(
                'company_name' => $company->name,
                'company_address' => $company->address,
                'company_mobile' => $company->mobile,
                'vat_number' => $company->vat_number,
                'mushok_number' => $company->mushok_number,
            );
            $status = true;
            return response()->json(compact('status', 'user', 'token', 'company'));
        } else {
            $status = false;
            $errors = 'Mobile, password and company id did not matched';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function profile()
    {
        return response()->json($this->guard()->user());
    }

    public function logout()
    {
        auth()->logout(true);
        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Report Start
    |--------------------------------------------------------------------------
    */
    public
    function getStock(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            if (empty($name)) {
                $products = DB::table('products')
                    ->selectRaw("products.name AS name, products.product_id, products.price,(select sum(quantity) from invoice_items where product_id= `products`.`product_id`) as 'sale',(select sum(quantity) from purchase_items where product_id= `products`.`product_id`) as 'purchase'")
                    ->where('products.company_id', $companyId)
                    ->leftJoin('invoice_items', 'invoice_items.product_id', '=', 'products.product_id')
                    ->leftJoin('purchase_items', 'purchase_items.product_id', '=', 'products.product_id')
                    ->groupBy('products.product_id', 'products.name')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'products'));
            } else {
                $products = DB::table('products')
                    ->selectRaw("products.name AS name, products.product_id, products.price,(select sum(quantity) from invoice_items where product_id= `products`.`product_id`) as 'sale',(select sum(quantity) from purchase_items where product_id= `products`.`product_id`) as 'purchase'")
                    ->where('products.company_id', $companyId)
                    ->where('products.name', 'like', '%' . $name . '%')
                    ->leftJoin('invoice_items', 'invoice_items.product_id', '=', 'products.product_id')
                    ->leftJoin('purchase_items', 'purchase_items.product_id', '=', 'products.product_id')
                    ->groupBy('products.product_id', 'products.name')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'products'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Report End
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Bank Start
    |--------------------------------------------------------------------------
    */
    public
    function getBanks(Request $request)
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

    public
    function getBank($id)
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

    public
    function storeBank(Request $request)
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

    public
    function updateBank(Request $request)
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

    public
    function deleteBank(Request $request)
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


    /*
    |--------------------------------------------------------------------------
    | Company Start
    |--------------------------------------------------------------------------
    */

    public
    function getCompany()
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $company = DB::table('companies')->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'company'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public
    function updateCompany(Request $request)
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
            if (!empty($request->logo)) {
                DB::table('companies')->where('company_id', $companyId)->update(
                    [
                        'name' => $request->name,
                        'address' => $request->address,
                        'email' => $request->email,
                        'mobile' => $request->mobile,
                        'logo' => $request->logo,
                        'vat_number' => $request->vat_number,
                        'mushok_number' => $request->mushok_number,
                        'discount_type' => $request->discount_type,
                    ]
                );
                $status = true;
                $message = 'Updated';
                return response()->json(compact('status', 'message'));
            } else {
                DB::table('companies')->where('company_id', $companyId)->update(
                    [
                        'name' => $request->name,
                        'address' => $request->address,
                        'email' => $request->email,
                        'mobile' => $request->mobile,
                        'vat_number' => $request->vat_number,
                        'mushok_number' => $request->mushok_number,
                        'discount_type' => $request->discount_type,
                    ]
                );
                $status = true;
                $message = 'Updated';
                return response()->json(compact('status', 'message'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Company End
    |--------------------------------------------------------------------------
    */
}
