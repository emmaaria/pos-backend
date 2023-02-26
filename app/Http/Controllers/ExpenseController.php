<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
                $categories = DB::table('expense_categories')->select('id', 'name')->where('company_id', $companyId)->orderBy('id', 'desc')->paginate(50);
                $status = true;
                return response()->json(compact('status', 'categories'));
            } elseif (!empty($all)) {
                $categories = DB::table('expense_categories')->where('company_id', $companyId)->orderBy('id', 'desc')->get();
                $status = true;
                return response()->json(compact('status', 'categories'));
            } else {
                $categories = DB::table('expense_categories')->select('id', 'name')->orderBy('id', 'desc')->where('name', 'like', '%' . $name . '%')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'categories'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function singleCategory($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $category = DB::table('expense_categories')->where('id', $id)->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'category'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeCategory(Request $request)
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
            $unit = DB::table('expense_categories')->insert(['name' => $request->name, 'company_id' => $companyId]);
            if ($unit) {
                $status = true;
                return response()->json(compact('status'));
            } else {
                $status = false;
                return response()->json(compact('status'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updateCategory(Request $request)
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
            DB::table('expense_categories')->where('id', $request->id)->where('company_id', $companyId)->update(['name' => $request->name]);
            $status = true;
            $message = 'Updated';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteCategory(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                $deleted = DB::table('expense_categories')->where('id', $id)->where('company_id', $companyId)->delete();
                if ($deleted) {
                    $status = true;
                    $message = 'Category deleted';
                    return response()->json(compact('status', 'message'));
                } else {
                    $status = false;
                    $error = 'Category not found';
                    return response()->json(compact('status', 'error'));
                }
            } else {
                $status = false;
                $error = 'Category not found';
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
    | Expense Category End
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Expense Start
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $categories = DB::table('expenses')->select('id', 'name')->where('company_id', $companyId)->orderBy('id', 'desc')->paginate(50);
                $status = true;
                return response()->json(compact('status', 'categories'));
            } elseif (!empty($all)) {
                $categories = DB::table('expenses')->where('company_id', $companyId)->orderBy('id', 'desc')->get();
                $status = true;
                return response()->json(compact('status', 'categories'));
            } else {
                $categories = DB::table('expenses')->select('id', 'name')->orderBy('id', 'desc')->where('name', 'like', '%' . $name . '%')->where('company_id', $companyId)->paginate(50);
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
    | Expense End
    |--------------------------------------------------------------------------
    */
}
