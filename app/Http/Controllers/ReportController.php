<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            if (!empty($request->supplier)){
                $data = DB::table('purchases')
                    ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date')
                    ->where('purchases.company_id', $companyId)
                    ->where('purchases.supplier_id', $request->supplier)
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                    ->where('purchases.date', '>=', $request->startDate)
                    ->where('purchases.date', '<=', $request->endDate)
                    ->orderBy('purchases.id', 'desc')
                    ->get();
            }else{
                $data = DB::table('purchases')
                    ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date')
                    ->where('purchases.company_id', $companyId)
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                    ->where('purchases.date', '>=', $request->startDate)
                    ->where('purchases.date', '<=', $request->endDate)
                    ->orderBy('purchases.id', 'desc')
                    ->get();
            }

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
            $query = DB::table('customer_ledgers')
                ->select('customers.name', 'customer_ledgers.transaction_id', 'customer_ledgers.date', 'customer_ledgers.type', 'customer_ledgers.due', 'customer_ledgers.deposit', 'customer_ledgers.reference_no', 'customer_ledgers.comment')
                ->where('customer_ledgers.customer_id', $request->customer)
                ->where('customer_ledgers.company_id', $companyId)
                ->leftJoin('customers', 'customers.id', '=', 'customer_ledgers.customer_id')
                ->orderBy('customer_ledgers.date', 'desc');
            if (!empty($request->startDate)) {
                $query->where('customer_ledgers.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
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

    public function supplierLedger(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'supplier' => 'required'
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            $query = DB::table('supplier_ledgers')
                ->select('suppliers.name', 'supplier_ledgers.transaction_id', 'supplier_ledgers.date', 'supplier_ledgers.type', 'supplier_ledgers.due', 'supplier_ledgers.deposit', 'supplier_ledgers.reference_no', 'supplier_ledgers.comment')
                ->where('supplier_ledgers.supplier_id', $request->supplier)
                ->where('supplier_ledgers.company_id', $companyId)
                ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_ledgers.supplier_id')
                ->orderBy('supplier_ledgers.date', 'desc');
            if (!empty($request->startDate)) {
                $query->where('supplier_ledgers.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $query->where('supplier_ledgers.date', '<=', $request->endDate);
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

    public function salesByProduct(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $query = DB::table('invoice_items')
                ->select('customers.name AS customer_name', 'invoice_items.invoice_id', 'invoice_items.date', 'invoice_items.quantity', 'invoice_items.grand_total', 'products.weight')
                ->where('invoice_items.company_id', $companyId)
                ->where('invoices.company_id', $companyId)
                ->where('products.company_id', $companyId)
                ->where('customers.company_id', $companyId)
                ->where('invoice_items.product_id', $request->productId)
                ->leftJoin('invoices', 'invoices.invoice_id', '=', 'invoice_items.invoice_id')
                ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                ->leftJoin('products', 'products.product_id', '=', 'invoice_items.product_id')
                ->orderBy('invoice_items.date', 'desc');
            if (!empty($request->startDate)) {
                $query->where('invoice_items.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $query->where('invoice_items.date', '<=', $request->endDate);
            }
            $data = $query->get();
            $totalQuantity = 0;
            $totalAmount = 0;
            $totalWeight = 0;
            foreach ($data as $row) {
                $totalQuantity += $row->quantity;
                $totalAmount += $row->grand_total;
                $totalWeight += $row->weight;
            }
            $status = true;
            return response()->json(compact('status', 'data', 'totalQuantity', 'totalAmount', 'totalWeight'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function salesByCategory(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $query = DB::table('products')
                ->select(
                    'products.name',
                    'products.product_id',
                    'products.weight',
                    'invoice_items.date',
                    'invoice_items.grand_total',
                    'products.weight',
                    DB::raw('SUM(quantity) as qty'),
                    DB::raw('SUM(weight) as weight'),
                )
                ->where('products.company_id', $companyId)
                ->where('products.category', $request->category)
                ->where('invoice_items.company_id', $companyId)
                ->leftJoin('invoice_items', 'invoice_items.product_id', '=', 'products.product_id')
                ->orderBy('invoice_items.date', 'desc')
                ->groupBy('products.product_id');
            if (!empty($request->startDate)) {
                $query->where('invoice_items.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $query->where('invoice_items.date', '<=', $request->endDate);
            }

            $data = $query->get();
            $totalQuantity = 0;
            $totalAmount = 0;
            $totalWeight = 0;
            foreach ($data as $row) {
                $totalQuantity += $row->qty;
                $totalAmount += $row->grand_total;
                $totalWeight += $row->weight;
            }
            $status = true;
            return response()->json(compact('status', 'data', 'totalQuantity', 'totalAmount', 'totalWeight'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function purchaseByProduct(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $data = DB::table('purchase_items')
                ->select('suppliers.name AS supplierName', 'purchase_items.purchase_id', 'purchase_items.date', 'purchase_items.quantity', 'purchase_items.total', 'products.weight')
                ->where('purchase_items.company_id', $companyId)
                ->where('purchases.company_id', $companyId)
                ->where('products.company_id', $companyId)
                ->where('suppliers.company_id', $companyId)
                ->where('purchase_items.product_id', $request->productId)
                ->where('purchase_items.date', '>=', $request->startDate)
                ->where('purchase_items.date', '<=', $request->endDate)
                ->leftJoin('purchases', 'purchases.purchase_id', '=', 'purchase_items.purchase_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->leftJoin('products', 'products.product_id', '=', 'purchase_items.product_id')
                ->orderBy('purchase_items.date', 'desc')
                ->get();
            $totalQuantity = 0;
            $totalAmount = 0;
            $totalWeight = 0;
            foreach ($data as $row) {
                $totalQuantity += $row->quantity;
                $totalAmount += $row->total;
                $totalWeight += $row->weight;
            }
            $status = true;
            return response()->json(compact('status', 'data', 'totalQuantity', 'totalAmount', 'totalWeight'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function purchaseByCategory(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $query = DB::table('products')
                ->select(
                    'products.name',
                    'products.product_id',
                    'products.weight',
                    'purchase_items.date',
                    'purchase_items.total',
                    'products.weight',
                    DB::raw('SUM(purchase_items.quantity) as qty'),
                )
                ->where('products.company_id', $companyId)
                ->where('products.category', $request->category)
                ->where('purchase_items.company_id', $companyId)
                ->leftJoin('purchase_items', 'purchase_items.product_id', '=', 'products.product_id')
                ->orderBy('purchase_items.date', 'desc')
                ->groupBy('products.product_id');
            if (!empty($request->startDate)) {
                $query->where('purchase_items.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $query->where('purchase_items.date', '<=', $request->endDate);
            }
            $data = $query->get();
            $totalQuantity = 0;
            $totalAmount = 0;
            $totalWeight = 0;
            foreach ($data as $row) {
                $totalQuantity += $row->qty;
                $totalAmount += $row->total;
                $totalWeight += $row->weight * $row->qty;
            }
            $status = true;
            return response()->json(compact('status', 'data', 'totalQuantity', 'totalAmount', 'totalWeight'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function purchaseBySupplier(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $query = DB::table('supplier_products')
                ->select(
                    'products.name',
                    'products.product_id',
                    'products.weight',
                    'purchase_items.date',
                    'purchase_items.total',
                    'products.weight',
                    DB::raw('SUM(purchase_items.quantity) as qty'),
                )
                ->where('supplier_products.company_id', $companyId)
                ->where('supplier_products.supplier_id', $request->supplier)
                ->where('purchase_items.company_id', $companyId)
                ->leftJoin('purchase_items', 'purchase_items.product_id', '=', 'supplier_products.product_id')
                ->leftJoin('products', 'products.product_id', '=', 'supplier_products.product_id')
                ->orderBy('purchase_items.date', 'desc')
                ->groupBy('purchase_items.product_id');
            if (!empty($request->startDate)) {
                $query->where('purchase_items.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $query->where('purchase_items.date', '<=', $request->endDate);
            }
            $data = $query->get();
            $totalQuantity = 0;
            $totalAmount = 0;
            $totalWeight = 0;
            foreach ($data as $row) {
                $totalQuantity += $row->qty;
                $totalAmount += $row->total;
                $totalWeight += $row->weight * $row->qty;
            }
            $status = true;
            return response()->json(compact('status', 'data', 'totalQuantity', 'totalAmount', 'totalWeight'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function salesByCustomer(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'customer' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }


            // Query to calculate total sold quantity
            $soldQuery = DB::table('invoices')
                ->select(
                    'invoice_items.product_id',
                    'products.name',
                    'products.weight',
                    DB::raw('SUM(invoice_items.quantity) as sold_qty'),
                    DB::raw('SUM(invoice_items.grand_total) as sold_amount'),
                )
                ->where('invoices.company_id', $companyId)
                ->where('invoices.customer_id', $request->customer)
                ->leftJoin('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.invoice_id')
                ->leftJoin('products', 'products.product_id', '=', 'invoice_items.product_id')
                ->groupBy('invoice_items.product_id');

            // Add additional filters if needed
            if (!empty($request->startDate)) {
                $soldQuery->where('invoices.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $soldQuery->where('invoices.date', '<=', $request->endDate);
            }

            if (!empty($request->supplier)) {
                $soldQuery->join('supplier_products', 'supplier_products.product_id', '=', 'invoice_items.product_id')
                    ->where('supplier_products.supplier_id', '=', $request->supplier);
            }

            if (!empty($request->category) && empty($request->product)) {
                $soldQuery->where('products.category', $request->category);
            }
            if (!empty($request->product)) {
                $soldQuery->where('invoice_items.product_id', $request->product);
            }

            // Get the results of total sold quantity
            $soldResults = $soldQuery->get();

            // Query to calculate total returned quantity
            $returnedQuery = DB::table('sale_return_items')
                ->select(
                    'product_id',
                    DB::raw('SUM(quantity) as returned_qty'),
                    DB::raw('SUM(total) as returned_amt')
                )
                ->where('customer_id', $request->customer);

            // Add additional filters if needed
            if (!empty($request->startDate)) {
                $returnedQuery->where('date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $returnedQuery->where('date', '<=', $request->endDate);
            }

            $returnedResults = $returnedQuery->groupBy('product_id')->get();

            $totalAmount = 0;
            $totalNetQuantity = 0;
            $totalWeight = 0;
            $totalSaleQty = 0;
            $totalReturnQty = 0;

            $data = collect($soldResults)->map(function ($item) use ($returnedResults, &$totalAmount, &$totalNetQuantity, &$totalWeight, &$totalSaleQty, &$totalReturnQty) {
                $returnedItem = $returnedResults->where('product_id', $item->product_id)->first();
                $returnedQty = $returnedItem ? $returnedItem->returned_qty : 0;
                $returnedAmt = $returnedItem ? $returnedItem->returned_amt : 0;

                $finalQty = $item->sold_qty - $returnedQty;
                $finalAmount = $item->sold_amount - $returnedAmt;

                $totalAmount += $finalAmount;
                $totalNetQuantity += $finalQty;
                $totalSaleQty += $item->sold_qty;
                $totalReturnQty += $returnedQty;
                $totalWeight += $item->weight * $finalQty;

                return [
                    'name' => $item->name,
                    'weight' => $item->weight,
                    'product_id' => $item->product_id,
                    'sold_qty' => $item->sold_qty,
                    'returned_qty' => $returnedQty,
                    'final_qty' => $finalQty,
                    'final_sale_amount' => $finalAmount,
                ];
            });


            $status = true;
            return response()->json(compact('status', 'data', 'totalAmount', 'totalNetQuantity', 'totalWeight', 'totalSaleQty', 'totalReturnQty'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function salesBySupplier(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'supplier' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            $query = DB::table('supplier_products')
                ->select(
                    'invoice_items.grand_total',
                    'invoice_items.product_id',
                    'products.name',
                    'products.weight',
                    DB::raw('SUM(invoice_items.quantity) as qty'),
                    DB::raw('SUM(sale_return_items.quantity) as returnQty'),
                    DB::raw('SUM(sale_return_items.total) as returnAmount'),
                )
                ->where('supplier_products.company_id', $companyId)
                ->where('supplier_products.supplier_id', $request->supplier)
                ->where('invoice_items.quantity', '>', 0)
                ->leftJoin('invoice_items', 'invoice_items.product_id', '=', 'supplier_products.product_id')
                ->leftJoin('products', 'products.product_id', '=', 'supplier_products.product_id')
                ->leftJoin('sale_return_items', 'sale_return_items.product_id', '=', 'supplier_products.product_id')
                ->orderBy('invoice_items.date', 'desc')
                ->groupBy('invoice_items.product_id');
            if (!empty($request->startDate)) {
                $query->where('invoice_items.date', '>=', $request->startDate);
            }
            if (!empty($request->endDate)) {
                $query->where('invoice_items.date', '<=', $request->endDate);
            }
            if (!empty($request->category) && empty($request->product)) {
                $query->where('products.category', $request->category);
            }
            if (!empty($request->product)) {
                $query->where('supplier_products.product_id', $request->product);
            }
            $data = $query->get();
            $totalAmount = 0;
            $totalQuantity = 0;
            $totalWeight = 0;
            foreach ($data as $row) {
                $totalAmount += $row->grand_total - $row->returnAmount;
                $totalQuantity += $row->qty - $row->returnQty;
                if (!empty($row->weight)) {
                    $totalWeight += (int)$row->qty * (int)$row->weight;
                }
            }
            $status = true;
            return response()->json(compact('status', 'data', 'totalQuantity', 'totalAmount', 'totalWeight'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function generateExpenseReport(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'startDate' => 'required',
                    'endDate' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            $query = DB::table('expenses')
                ->select('expenses.amount', 'expense_categories.name AS category_name', DB::raw('COALESCE(SUM(expenses.amount), 0) as total'))
                ->where('expenses.company_id', $companyId)
                ->where('expenses.date', '>=', $request->startDate)
                ->where('expenses.date', '<=', $request->endDate)
                ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.category');
            if ($request->expenseCategory) {
                $query = $query->where('expenses.category', $request->expenseCategory);
            }
            $data = $query->orderBy('expenses.date', 'desc')
                ->groupBy('expense_categories.id')
                ->get();
            $totalAmount = 0;
            foreach ($data as $row) {
                $totalAmount += $row->total;
            }
            $status = true;
            return response()->json(compact('status', 'data', 'totalAmount'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }
}
