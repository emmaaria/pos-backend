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
            $query = DB::table('invoices')
                ->select(
                    'invoice_items.product_id',
                    'products.name',
                    'products.weight',
                    DB::raw('SUM(invoice_items.quantity) as qty'),
                    DB::raw('COALESCE(SUM(sale_return_items.quantity), 0) as returnQty'),
                    DB::raw('COALESCE(SUM(sale_return_items.total), 0) as returnTotal'),
                    DB::raw('SUM(invoice_items.grand_total) as grand_total')
                )
                ->where('invoices.company_id', $companyId)
                ->where('invoices.customer_id', $request->customer)
                ->leftJoin('invoice_items', 'invoice_items.invoice_id', '=', 'invoices.invoice_id')
                ->leftJoin('sale_return_items', function($join) use ($request) {
                    $join->on('sale_return_items.product_id', '=', 'invoice_items.product_id')
                        ->where('sale_return_items.customer_id', $request->customer);
                    if (!empty($request->startDate)) {
                        $join->where('sale_return_items.date', '>=', $request->startDate);
                    }
                    if (!empty($request->endDate)) {
                        $join->where('sale_return_items.date', '<=', $request->endDate);
                    }
                })
                ->leftJoin('products', 'products.product_id', '=', 'invoice_items.product_id')
                ->orderBy('invoices.date', 'desc')
                ->groupBy('invoice_items.product_id');
            if (!empty($request->startDate)) {
                $query->where('invoices.date', '>=', $request->startDate);
            }
            if (!empty($request->supplier)) {
                $query->join('supplier_products', 'supplier_products.product_id', '=', 'invoice_items.product_id')
                    ->where('supplier_products.supplier_id', '=', $request->supplier);
            }
            if (!empty($request->endDate)) {
                $query->where('invoices.date', '<=', $request->endDate);
            }
            if (!empty($request->category) && empty($request->product)) {
                $query->where('products.category', $request->category);
            }
            if (!empty($request->product)) {
                $query->where('invoice_items.product_id', $request->product);
            }
            $data = $query->get();
            $totalAmount = 0;
            $totalQuantity = 0;
            $totalWeight = 0;
            foreach ($data as $row) {
                $total = $row->grand_total - $row->returnTotal;
                $totalAmount += $total;
                $qty = $row->qty - $row->returnQty;
                $totalQuantity += $qty;
                if (!empty($row->weight)) {
                    $totalWeight += (int)$qty * (int)$row->weight;
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
}
