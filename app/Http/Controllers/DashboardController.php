<?php

namespace App\Http\Controllers;

use App\Models\BankLedger;
use App\Models\BkashTransaction;
use App\Models\CardTransaction;
use App\Models\CashBook;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\NagadTransaction;
use App\Models\Product;
use App\Models\Profit;
use App\Models\Purchase;
use App\Models\SaleReturn;
use App\Models\Supplier;
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

            $data['totalProduct'] = Product::where('company_id', $companyId)
                ->count();

            $data['totalCustomer'] = Customer::where('company_id', $companyId)
                ->count();

            $data['totalSupplier'] = Supplier::where('company_id', $companyId)
                ->count();

            $data['totalPurchase'] = Purchase::where('company_id', $companyId)
                ->count();

            $data['totalInvoice'] = Invoice::where('company_id', $companyId)
                ->count();

            $data['totalPurchaseAmount'] = Purchase::where('company_id', $companyId)
                ->sum('amount');

            $totalSale = Invoice::where('company_id', $companyId)
                ->sum('grand_total');

            $totalReturn = SaleReturn::where('company_id', $companyId)
                ->sum('return_amount');

            $data['totalSaleAmount'] = $totalSale - $totalReturn;

            $data['totalReturn'] = SaleReturn::where('company_id', $companyId)
                ->count();

            $data['todayTotalInvoice'] = Invoice::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->count();

            $data['todayTotalSaleAmount'] = Invoice::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum('grand_total');

            $data['todayTotalPurchase'] = Purchase::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->count();

            $data['todayTotalPurchaseAmount'] = Purchase::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum('amount');

            $data['todayTotalReturn'] = SaleReturn::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->count();

            $data['todayTotalReturnAmount'] = SaleReturn::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum('return_amount');

            $data['todayTotalCash'] = CashBook::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('receive - payment'));

            $data['totalCash'] = CashBook::where('company_id', $companyId)
                ->sum(DB::raw('receive - payment'));

            $data['todayTotalBkash'] = BkashTransaction::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - withdraw'));

            $data['totalBkash'] = BkashTransaction::where('company_id', $companyId)
                ->sum(DB::raw('deposit - withdraw'));

            $data['totalNagad'] = NagadTransaction::where('company_id', $companyId)
                ->sum(DB::raw('deposit - withdraw'));

            $data['todayTotalNagad'] = NagadTransaction::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - withdraw'));

            $data['todayTotalBank'] = BankLedger::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - withdraw'));

            $data['todayTotalCard'] = CardTransaction::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - withdraw'));

            $data['totalBank'] = BankLedger::where('company_id', $companyId)
                ->sum(DB::raw('deposit - withdraw'));

            $data['totalCard'] = CardTransaction::where('company_id', $companyId)
                ->sum(DB::raw('deposit - withdraw'));

            $data['todayTotalProfit'] = Profit::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->sum(DB::raw('deposit - deduct'));

            $sales = Invoice::select(DB::raw("SUM(grand_total) as total"), DB::raw("MONTHNAME(date) as month"))
                ->whereYear('date', date('Y'))
                ->groupBy(DB::raw("month"))
                ->orderBy('date', 'ASC')
                ->where('company_id', $companyId)
                ->get();

            $returns = SaleReturn::select(DB::raw("SUM(return_amount) as total"), DB::raw("MONTHNAME(date) as month"))
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

            $data['todayTotalProfit'] = Profit::where('company_id', $companyId)
                ->where('date', date('Y-m-d'))
                ->where('company_id', $companyId)
                ->sum(DB::raw('deposit - deduct'));

            $data["profitChart"] = Profit::select(DB::raw("ROUND(SUM(deposit - deduct), 2) as profit"), DB::raw("MONTHNAME(date) as month"))
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
