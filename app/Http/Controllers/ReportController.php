<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class ReportController extends Controller
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

    public function sales(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $data = DB::table('invoices')
                ->select('customers.name AS customer_name', 'invoices.invoice_id', 'invoices.grand_total', 'invoices.discountAmount', 'invoices.comment', 'invoices.id', 'invoices.date')
                ->where('invoices.company_id', $companyId)
                ->where('invoices.date', '>=', $request->startDate)
                ->where('invoices.date', '<=', $request->endDate)
                ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                ->orderBy('id', 'desc')
                ->get();
            $status = true;
            return response()->json(compact('status', 'data'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function purchase(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $data = DB::table('purchases')
                ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date')
                ->where('purchases.company_id', $companyId)
                ->where('suppliers.company_id', $companyId)
                ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->where('purchases.date', '>=', $request->startDate)
                ->where('purchases.date', '<=', $request->endDate)
                ->orderBy('purchases.id', 'desc')
                ->get();
            $status = true;
            return response()->json(compact('status', 'data'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function customerLedger(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'customer' => 'required'
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            $query = DB::table('customers')
                ->select('customers.name', 'customer_ledgers.transaction_id', 'customer_ledgers.date', 'customer_ledgers.type', 'customer_ledgers.due', 'customer_ledgers.deposit', 'customer_ledgers.reference_no')
                ->where('customers.id', $request->customer_id)
                ->where('customers.company_id', $companyId)
                ->where('customer_ledgers.company_id', $companyId)
                ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                ->orderBy('customer_ledgers.date', 'desc');
            if (!empty($request->startDate)){
                $query->where('customer_ledgers.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)){
                $query->where('customer_ledgers.date', '<=', $request->endDate);
            }
            $data = $query->get();
            $status = true;
            return response()->json(compact('status', 'data'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }
}
