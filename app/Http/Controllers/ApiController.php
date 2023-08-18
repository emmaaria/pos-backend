<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom:api', ['except' => ['login', 'makeUser']]);
    }

    public function makeUser()
    {
        DB::table('users')->insert([
            'company_id' => 101,
            'name' => 'admin',
            'email' => '01748254814',
            'role' => 'admin',
            'password' => Hash::make('87654321'),
        ]);
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
                $userData = [];
            }
            if (!$token = auth()->claims($userData)->attempt($credentials)) {
                $status = false;
                $errors = 'Credentials did not matched';
                return response()->json(compact('status', 'errors'));
            }
            $company = array(
                'company_name' => $company->name,
                'discount_type' => $company->discount_type,
                'customer_based_price' => $company->customer_based_price,
                'company_address' => $company->address,
                'company_mobile' => $company->mobile,
                'vat_number' => $company->vat_number,
                'mushok_number' => $company->mushok_number,
                'stock_over_selling' => $company->stock_over_selling,
                'paddingLeft' => $company->paddingLeft,
                'paddingRight' => $company->paddingRight,
                'paddingTop' => $company->paddingTop,
                'perRow' => $company->perRow,
            );
            $status = true;
            return response()->json(compact('status', 'user', 'token', 'company'));
        } else {
            $status = false;
            $errors = 'Mobile, password and company id did not matched';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function logout()
    {
        Auth::logout();
        $status = true;
        $message = 'Successfully logged out';
        return response()->json(compact('status', $message));
    }

    public function profile()
    {
        return response()->json($this->guard()->user());
    }

    /*
    |--------------------------------------------------------------------------
    | Company Start
    |--------------------------------------------------------------------------
    */

    public function getCompany()
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

    public function updateCompany(Request $request)
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
                        'stock_over_selling' => $request->stock_over_selling,
                        'paddingLeft' => $request->paddingLeft,
                        'paddingRight' => $request->paddingRight,
                        'paddingTop' => $request->paddingTop,
                        'perRow' => $request->perRow,
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
                        'stock_over_selling' => $request->stock_over_selling,
                        'paddingLeft' => $request->paddingLeft,
                        'paddingRight' => $request->paddingRight,
                        'paddingTop' => $request->paddingTop,
                        'perRow' => $request->perRow,
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

    public function updateSoftware(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            DB::table('companies')->where('company_id', $companyId)->update(
                [
                    'discount_type' => $request->discount_type,
                    'customer_based_price' => $request->customer_based_price,
                    'stock_over_selling' => $request->stock_over_selling,
                    'paddingLeft' => $request->paddingLeft,
                    'paddingRight' => $request->paddingRight,
                    'paddingTop' => $request->paddingTop,
                    'perRow' => $request->perRow,
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

    /*
    |--------------------------------------------------------------------------
    | Company End
    |--------------------------------------------------------------------------
    */


}
