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

            $data['totalPurchaseAmount'] = number_format(DB::table('purchases')
                ->where('company_id', $companyId)
                ->sum('amount'). 2);

            $totalSale = number_format(DB::table('invoices')
                ->where('company_id', $companyId)
                ->sum('grand_total'), 2);

            $totalReturn = DB::table('sale_returns')
                ->where('company_id', $companyId)
                ->sum('return_amount');

            $data['totalSaleAmount'] = $totalSale - $totalReturn;

            $data['totalReturn'] = DB::table('sale_returns')
                ->where('company_id', $companyId)
                ->count();

            $data['todayTotalInvoice'] = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->count();

            $data['todayTotalSaleAmount'] = number_format(DB::table('invoices')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum('grand_total'), 2);

            $data['todayTotalPurchase'] = DB::table('purchases')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->count();

            $data['todayTotalPurchaseAmount'] = DB::table('purchases')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum('amount');

            $data['todayTotalReturn'] = DB::table('sale_returns')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->count();

            $data['todayTotalReturnAmount'] = DB::table('sale_returns')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum('return_amount');

            $data['todayTotalCash'] = DB::table('cash_books')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('receive - payment'));

            $data['totalCash'] = DB::table('cash_books')
                ->where('company_id', $companyId)
                ->sum(DB::raw('receive - payment'));

            $data['todayTotalBkash'] = DB::table('bkash_transactions')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - withdraw'));

            $data['totalBkash'] = DB::table('bkash_transactions')
                ->where('company_id', $companyId)
                ->sum(DB::raw('deposit - withdraw'));

            $data['totalNagad'] = DB::table('nagad_transactions')
                ->where('company_id', $companyId)
                ->sum(DB::raw('deposit - withdraw'));

            $data['todayTotalNagad'] = DB::table('nagad_transactions')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - withdraw'));

            $data['todayTotalBank'] = DB::table('bank_ledgers')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - withdraw'));

            $data['todayTotalCard'] = DB::table('card_transactions')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - withdraw'));

            $data['totalBank'] = DB::table('bank_ledgers')
                ->where('company_id', $companyId)
                ->sum(DB::raw('deposit - withdraw'));

            $data['totalCard'] = DB::table('card_transactions')
                ->where('company_id', $companyId)
                ->sum(DB::raw('deposit - withdraw'));

            $data['todayTotalProfit'] = DB::table('profits')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - deduct'));

            $sales = DB::table('invoices')
                ->select(DB::raw("SUM(grand_total) as total"), DB::raw("MONTHNAME(date) as month"))
                ->whereYear('date', date('Y'))
                ->groupBy(DB::raw("month"))
                ->orderBy('date', 'ASC')
                ->where('company_id', $companyId)
                ->get();

            $returns = DB::table('sale_returns')
                ->select(DB::raw("SUM(return_amount) as total"), DB::raw("MONTHNAME(date) as month"))
                ->whereYear('date', date('Y'))
                ->groupBy(DB::raw("month"))
                ->orderBy('date', 'ASC')
                ->where('company_id', $companyId)
                ->get();

            $salesChart = [];

            foreach ($sales as $sale) {
                $month = $sale->month;
                $total = $sale->total;
                foreach ($returns as $return) {
                    if ($month == $return->month) {
                        $total = $sale->total - $return->total;
                    }
                }
                $salesChart[] = [
                    "month" => $month,
                    "sale" => $total,
                ];
            }

            $data['todayTotalProfit'] = DB::table('profits')
                ->where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->where('company_id', $companyId)
                ->sum(DB::raw('deposit - deduct'));

            $data["profitChart"] = DB::table('profits')
                ->select(DB::raw("ROUND(SUM(deposit - deduct), 2) as profit"), DB::raw("MONTHNAME(date) as month"))
                ->whereYear('date', date('Y'))
                ->groupBy(DB::raw("month"))
                ->where('company_id', $companyId)
                ->orderBy('date', 'ASC')
                ->get();

            $data["salesChart"] = $salesChart;

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
