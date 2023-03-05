<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardController extends Controller
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
    | Dashboard Start
    |--------------------------------------------------------------------------
    */
    public function data(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $data = [];
            $data['totalProduct'] = DB::table('products')
                ->where('company_id', $companyId)
                ->count();
            $data['totalCustomer'] = DB::table('customers')
                ->where('company_id', $companyId)
                ->count();
            $data['totalSupplier'] = DB::table('suppliers')
                ->where('company_id', $companyId)
                ->count();
            $data['totalPurchase'] = DB::table('purchases')
                ->where('company_id', $companyId)
                ->count();
            $data['totalInvoice'] = DB::table('invoices')
                ->where('company_id', $companyId)
                ->count();
            $data['totalReturn'] = DB::table('sale_returns')
                ->where('company_id', $companyId)
                ->count();
            $data['totalTodayInvoice'] = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->count();
            $data['todayTotalSaleAmount'] = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum('grand_total');
            $status = true;
            return response()->json(compact('status', 'data'));
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
