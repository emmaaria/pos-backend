<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class BulkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom:api', ['except' => []]);
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

    public function productBulkData(){
        $companyId = $this->getCompanyId();
        if ($companyId) {

            $categories = DB::table('categories')->where('company_id', $companyId)->get();

            $units = DB::table('units')->where('company_id', $companyId)->get();

            $suppliers = DB::table('suppliers')->select('name', 'id')->where('company_id', $companyId)->get();

            $customers = DB::table('customers')->select('name', 'id', 'address')->where('company_id', $companyId)->get();

            $status = true;
            return response()->json(compact('status', 'categories', 'units', 'suppliers', 'customers'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }
}
