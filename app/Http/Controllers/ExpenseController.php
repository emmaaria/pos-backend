<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExpenseController extends Controller
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
    | Expense Category Start
    |--------------------------------------------------------------------------
    */

    public function categoryIndex(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $categories = DB::table('expense_categories')->select('id', 'name')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'categories'));
            } elseif (!empty($all)) {
                $categories = DB::table('expense_categories')->where('company_id', $companyId)->get();
                $status = true;
                return response()->json(compact('status', 'categories'));
            } else {
                $categories = DB::table('expense_categories')->select('id', 'name')->where('name', 'like', '%' . $name . '%')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'categories'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Expense Category End
    |--------------------------------------------------------------------------
    */
}
