<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth.custom:api', ['except' => ['login']]);
    }

    protected function guard()
    {
        return Auth::guard();
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required',
                'password' => 'required',
            ]
        );
        if ($validator->fails()) {
            $status = false;
            $errors = $validator->errors();
            return response()->json(compact('status', 'errors'));
        }
        $credentials = array('email' => $request->email, 'password' => $request->password);
        $user = DB::table('users')->where('email', $request->email)->first();
        if (!empty($user)){
            $userData = array(
                'user_id' => bcrypt($user->id),
            );
        }else{
            $userData = null;
        }
        if (!$token = auth()->claims($userData)->attempt($credentials)) {
            $status = false;
            $errors = 'Email and password did not matched';
            return response()->json(compact('status', 'errors'));
        }
        $status = true;
        $user = User::select('id', 'name', 'email', 'role')->where('email', $request->email)->first();
        return response()->json(compact('status', 'user', 'token'));
    }

    public function profile()
    {
        return response()->json($this->guard()->user());
    }

    public function logout()
    {
        $this->guard()->logout();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Category Start
    |--------------------------------------------------------------------------
    */
    public function getCategories(Request $request)
    {
        $name = $request->name;
        $all = $request->allData;
        if (empty($name) && empty($all)) {
            $categories = DB::table('categories')->select('id', 'name')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'categories'));
        } elseif (!empty($all)) {
            $categories = DB::table('categories')->get();
            $status = true;
            return response()->json(compact('status', 'categories'));
        } else {
            $categories = DB::table('categories')->select('id', 'name')->where('name', 'like', '%' . $name . '%')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'categories'));
        }
    }

    public function getCategory($id)
    {
        $category = DB::table('categories')->where('id', $id)->first();
        $status = true;
        return response()->json(compact('status', 'category'));
    }

    public function storeCategory(Request $request)
    {
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
        $categoty = DB::table('categories')->insert(['name' => $request->name]);
        if ($categoty) {
            $status = true;
            return response()->json(compact('status'));
        } else {
            $status = false;
            return response()->json(compact('status'));
        }
    }

    public function updateCategory(Request $request)
    {
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
        DB::table('categories')->where('id', $request->id)->update(['name' => $request->name]);
        $status = true;
        $message = 'Updated';
        return response()->json(compact('status', 'message'));
    }

    public function deleteCategory(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $deleted = DB::table('categories')->where('id', $id)->delete();
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
    }
    /*
    |--------------------------------------------------------------------------
    | Category End
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Unit Start
    |--------------------------------------------------------------------------
    */
    public function getUnits(Request $request)
    {
        $name = $request->name;
        $all = $request->allData;
        if (empty($name) && empty($all)) {
            $units = DB::table('units')->select('id', 'name')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'units'));
        } elseif (!empty($all)) {
            $units = DB::table('units')->get();
            $status = true;
            return response()->json(compact('status', 'units'));
        } else {
            $units = DB::table('units')->select('id', 'name')->where('name', 'like', '%' . $name . '%')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'units'));
        }
    }

    public function getUnit($id)
    {
        $unit = DB::table('units')->where('id', $id)->first();
        $status = true;
        return response()->json(compact('status', 'unit'));
    }

    public function storeUnit(Request $request)
    {
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
        $unit = DB::table('units')->insert(['name' => $request->name]);
        if ($unit) {
            $status = true;
            return response()->json(compact('status'));
        } else {
            $status = false;
            return response()->json(compact('status'));
        }
    }

    public function updateUnit(Request $request)
    {
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
        DB::table('units')->where('id', $request->id)->update(['name' => $request->name]);
        $status = true;
        $message = 'Updated';
        return response()->json(compact('status', 'message'));
    }

    public function deleteUnit(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $deleted = DB::table('units')->where('id', $id)->delete();
            if ($deleted) {
                $status = true;
                $message = 'Unit deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Unit not found';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $error = 'Unit not found';
            return response()->json(compact('status', 'error'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Unit End
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Customer Start
    |--------------------------------------------------------------------------
    */
    public function getCustomers(Request $request)
    {
        $name = $request->name;
        if (empty($name)) {
            $customers = DB::table('customers')
                ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'customers'));
        } else {
            $customers = DB::table('customers')
                ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                ->where('customers.name', 'like', '%' . $name . '%')
                ->orWhere('customers.mobile', 'like', '%' . $name . '%')
                ->orWhere('customers.address', 'like', '%' . $name . '%')
                ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'customers'));
        }
    }

    public function getCustomer($id)
    {
        $customer = DB::table('customers')->where('id', $id)->first();
        $status = true;
        return response()->json(compact('status', 'customer'));
    }

    public function storeCustomer(Request $request)
    {
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
        $customerId = DB::table('customers')->insertGetId(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address]);

        if ($customerId) {
            if (!empty($request->due)) {
                $txIdGenerator = new InvoiceNumberGeneratorService();
                $txId = $txIdGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('customer_transaction');
                DB::table('customer_ledgers')->insert(array(
                    'customer_id' => $customerId,
                    'transaction_id' => $txId,
                    'type' => 'due',
                    'due' => $request->due,
                    'deposit' => 0,
                    'date' => date('Y-m-d'),
                    'comment' => 'Previous Due'
                ));
                $txIdGenerator->setNextInvoiceNo();
            }
            $status = true;
            return response()->json(compact('status'));
        } else {
            $status = false;
            return response()->json(compact('status'));
        }
    }

    public function updateCustomer(Request $request)
    {
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
        DB::table('customers')->where('id', $request->id)->update(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address]);
        $status = true;
        $message = 'Updated';
        return response()->json(compact('status', 'message'));
    }

    public function deleteCustomer(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $deleted = DB::table('customers')->where('id', $id)->delete();
            DB::table('customer_ledgers')->where('customer_id', $id)->delete();
            if ($deleted) {
                $status = true;
                $message = 'Unit deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Unit not found';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $error = 'Unit not found';
            return response()->json(compact('status', 'error'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Customer End
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Supplier Start
    |--------------------------------------------------------------------------
    */
    public function getSuppliers(Request $request)
    {
        $name = $request->name;
        if (empty($name)) {
            $suppliers = DB::table('suppliers')
                ->select('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                ->leftJoin('supplier_ledgers', 'supplier_ledgers.supplier_id', '=', 'suppliers.id')
                ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address')
                ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'suppliers'));
        } else {
            $suppliers = DB::table('suppliers')
                ->select('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                ->leftJoin('supplier_ledgers', 'supplier_ledgers.supplier_id', '=', 'suppliers.id')
                ->where('suppliers.name', 'like', '%' . $name . '%')
                ->orWhere('suppliers.mobile', 'like', '%' . $name . '%')
                ->orWhere('suppliers.address', 'like', '%' . $name . '%')
                ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address')
                ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'suppliers'));
        }
    }

    public function getSupplier($id)
    {
        $supplier = DB::table('suppliers')->where('id', $id)->first();
        $status = true;
        return response()->json(compact('status', 'supplier'));
    }

    public function storeSupplier(Request $request)
    {
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
        $supplierId = DB::table('suppliers')->insertGetId(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address]);

        if ($supplierId) {
            if (!empty($request->due)) {
                $txIdGenerator = new InvoiceNumberGeneratorService();
                $txId = $txIdGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('supplier_transaction');
                DB::table('supplier_ledgers')->insert(array(
                    'supplier_id' => $supplierId,
                    'transaction_id' => $txId,
                    'type' => 'due',
                    'due' => $request->due,
                    'deposit' => 0,
                    'date' => date('Y-m-d'),
                    'comment' => 'Previous Due'
                ));
                $txIdGenerator->setNextInvoiceNo();
            }
            $status = true;
            return response()->json(compact('status'));
        } else {
            $status = false;
            return response()->json(compact('status'));
        }
    }

    public function updateSupplier(Request $request)
    {
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
        DB::table('suppliers')->where('id', $request->id)->update(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address]);
        $status = true;
        $message = 'Updated';
        return response()->json(compact('status', 'message'));
    }

    public function deleteSupplier(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $deleted = DB::table('suppliers')->where('id', $id)->delete();
            DB::table('supplier_ledgers')->where('supplier_id', $id)->delete();
            if ($deleted) {
                $status = true;
                $message = 'Supplier deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Supplier not found';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $error = 'Supplier not found';
            return response()->json(compact('status', 'error'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Supplier End
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Purchase Start
    |--------------------------------------------------------------------------
    */
    public function getPurchases(Request $request)
    {
        $name = $request->name;
        if (empty($name)) {
            $purchases = DB::table('purchases')
                ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->orderBy('id', 'desc')
                ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'purchases'));
        } else {
            $purchases = DB::table('purchases')
                ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id')
                ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->where('purchases.purchase_id', 'like', '%' . $name . '%')
                ->orWhere('suppliers.name', 'like', '%' . $name . '%')
                ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'purchases'));
        }
    }

    public function getPurchase($id)
    {
        $purchaseData = DB::table('purchases')
            ->select('suppliers.name AS supplier_name', 'suppliers.id AS supplier_id', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date', 'purchases.paid')
            ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchases.id', $id)
            ->first();
        $purchaseItems = DB::table('purchase_items')
            ->select('products.name', 'purchase_items.price', 'purchase_items.total', 'purchase_items.quantity', 'products.id')
            ->join('products', 'products.id', '=', 'purchase_items.product_id')
            ->where('purchase_items.purchase_id', $purchaseData->purchase_id)
            ->get();
        $purchase = array(
            'purchaseData' => $purchaseData,
            'purchaseItems' => $purchaseItems,
        );
        $status = true;
        return response()->json(compact('status', 'purchase'));
    }

    public function storePurchase(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'supplier_id' => 'required',
                'date' => 'required',
                'productIds' => 'required',
                'productQuantities' => 'required',
                'productPrices' => 'required',
                'total' => 'required',
            ]
        );
        if ($validator->fails()) {
            $status = false;
            $errors = $validator->errors();
            return response()->json(compact('status', 'errors'));
        }
        $products = $request->productIds;
        $quantities = $request->productQuantities;
        $prices = $request->productPrices;
        if (count($products) > 0) {
            $txGenerator = new InvoiceNumberGeneratorService();
            $purchaseId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('purchase');
            $txGenerator->setNextInvoiceNo();
            DB::table('purchases')->insertGetId(
                [
                    'supplier_id' => $request->supplier_id,
                    'amount' => $request->total,
                    'paid' => $request->paid,
                    'comment' => $request->comment,
                    'purchase_id' => $purchaseId,
                    'date' => $request->date,
                ]
            );
            for ($i = 0, $n = count($products); $i < $n; $i++) {
                $productID = $products[$i];
                $quantity = $quantities[$i];
                $price = $prices[$i];
                if ($quantity > 0) {
                    DB::table('purchase_items')->insert([
                        'purchase_id' => $purchaseId,
                        'product_id' => $productID,
                        'price' => $price,
                        'quantity' => $quantity,
                        'date' => $request->date,
                        'total' => $quantity * $price,
                    ]);
                }
            }
            $supplierDueTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('supplier_transaction');
            DB::table('supplier_ledgers')->insert(array(
                'supplier_id' => $request->supplier_id,
                'transaction_id' => $supplierDueTxId,
                'reference_no' => 'pur-'.$purchaseId,
                'type' => 'due',
                'due' => $request->total,
                'deposit' => 0,
                'date' => $request->date,
                'comment' => "Due for Purchase id ($purchaseId)"
            ));
            $txGenerator->setNextInvoiceNo();

            if (!empty($request->paid)) {
                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('supplier_transaction');
                DB::table('supplier_ledgers')->insert(array(
                    'supplier_id' => $request->supplier_id,
                    'reference_no' => 'pur-'.$purchaseId,
                    'transaction_id' => $supplierPaidTxId,
                    'type' => 'deposit',
                    'due' => 0,
                    'deposit' => $request->paid,
                    'date' => $request->date,
                    'comment' => "Deposit for Purchase id ($purchaseId)"
                ));
                $txGenerator->setNextInvoiceNo();

                $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('cash_transaction');
                DB::table('cash_books')->insert(array(
                    'transaction_id' => $cashTxId,
                    'reference_no' => 'pur-'.$purchaseId,
                    'type' => 'payment',
                    'payment' => $request->paid,
                    'date' => $request->date,
                    'comment' => "Paid for Purchase id ($purchaseId)"
                ));
                $txGenerator->setNextInvoiceNo();
            }
            $status = true;
            $message = 'Purchase saved';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $error = 'Please add at least one product';
            return response()->json(compact('status', 'error'));
        }
    }

    public function updatePurchase(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'purchase_id' => 'required',
                'supplier_id' => 'required',
                'date' => 'required',
                'productIds' => 'required',
                'productQuantities' => 'required',
                'productPrices' => 'required',
                'total' => 'required',
            ]
        );
        if ($validator->fails()) {
            $status = false;
            $errors = $validator->errors();
            return response()->json(compact('status', 'errors'));
        }
        $products = $request->productIds;
        $quantities = $request->productQuantities;
        $prices = $request->productPrices;
        if (count($products) > 0) {
            $purchase = DB::table('purchases')->where('id', $request->purchase_id)->first();
            DB::table('purchases')->where('id', $request->purchase_id)->update(
                [
                    'supplier_id' => $request->supplier_id,
                    'amount' => $request->total,
                    'paid' => $request->paid,
                    'comment' => $request->comment,
                    'date' => $request->date,
                ]
            );
            DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)->delete();
            for ($i = 0, $n = count($products); $i < $n; $i++) {
                $productID = $products[$i];
                $quantity = $quantities[$i];
                $price = $prices[$i];
                if ($quantity > 0) {
                    DB::table('purchase_items')->insert([
                        'purchase_id' => $purchase->purchase_id,
                        'product_id' => $productID,
                        'price' => $price,
                        'quantity' => $quantity,
                        'date' => $request->date,
                        'total' => $quantity * $price,
                    ]);
                }
            }
            DB::table('supplier_ledgers')
                ->where('reference_no', "pur-$purchase->purchase_id")
                ->where('type', 'due')
                ->update(array(
                    'supplier_id' => $request->supplier_id,
                    'due' => $request->total,
                    'deposit' => 0,
                    'date' => $request->date
                ));
            DB::table('supplier_ledgers')
                ->where('reference_no', "pur-$purchase->purchase_id")
                ->where('type', 'deposit')
                ->delete();
            DB::table('cash_books')
                ->where('reference_no', "pur-$purchase->purchase_id")
                ->where('type', 'payment')
                ->delete();

            if (!empty($request->paid)) {
                $txGenerator = new InvoiceNumberGeneratorService();
                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('supplier_transaction');
                DB::table('supplier_ledgers')->insert(array(
                    'supplier_id' => $request->supplier_id,
                    'reference_no' => "pur-$purchase->purchase_id",
                    'transaction_id' => $supplierPaidTxId,
                    'type' => 'deposit',
                    'due' => 0,
                    'deposit' => $request->paid,
                    'date' => $request->date,
                    'comment' => "Deposit for Purchase id ($purchase->purchase_id)"
                ));
                $txGenerator->setNextInvoiceNo();

                $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('cash_transaction');
                DB::table('cash_books')->insert(array(
                    'transaction_id' => $cashTxId,
                    'reference_no' => "pur-$purchase->purchase_id",
                    'type' => 'payment',
                    'payment' => $request->paid,
                    'date' => $request->date,
                    'comment' => "Paid for Purchase id ($purchase->purchase_id)"
                ));
                $txGenerator->setNextInvoiceNo();
            }
            $status = true;
            $message = 'Purchase saved';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $error = 'Please add at least one product';
            return response()->json(compact('status', 'error'));
        }
    }

    public function deletePurchase(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $purchase = DB::table('purchases')->where('id', $request->id)->first();
            $deleted = DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)->delete();
            $deleted = DB::table('supplier_ledgers')
                ->where('reference_no', "pur-$purchase->purchase_id")
                ->delete();
            $deleted = DB::table('cash_books')
                ->where('reference_no', "pur-$purchase->purchase_id")
                ->delete();
            $deleted = Db::table('purchases')->where('id', $id)->delete();
            if ($deleted) {
                $status = true;
                $message = 'Purchase deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Purchase not found';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $error = 'Purchase not found';
            return response()->json(compact('status', 'error'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Purchase End
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Product Start
    |--------------------------------------------------------------------------
    */
    public function getProducts(Request $request)
    {
        $name = $request->name;
        if (empty($name)) {
            $products = Product::select('*')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'products'));
        } else {
            $products = Product::select('*')->where('name', 'like', '%' . $name . '%')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'products'));
        }
    }

    public function getProduct($id)
    {
        $product = Product::where('id', $id)->first();
        $status = true;
        return response()->json(compact('status', 'product'));
    }

    public function getProductByBarcode(Request $request)
    {
        $product = Product::where('product_id', $request->id)->first();
        if (!empty($product)){
            $status = true;
            return response()->json(compact('status', 'product'));
        }else{
            $status = false;
            $message = 'No product fround';
            return response()->json(compact('status', 'message'));
        }
    }

    public function storeProduct(Request $request)
    {
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
        $productIdGenerator = new InvoiceNumberGeneratorService();
        $productId = $productIdGenerator->prefix('')->setCompanyId(1)->startAt(100000)->getInvoiceNumber('product');
        $product = Product::create(array(
            'name' => $request->name,
            'product_id' => $productId,
            'category' => $request->category,
            'unit' => $request->unit,
            'price' => $request->price,
            'purchase_price' => $request->purchase_price,
        ));
        if ($product) {
            $productIdGenerator->setNextInvoiceNo();
            $status = true;
            return response()->json(compact('status'));
        } else {
            $status = false;
            return response()->json(compact('status'));
        }
    }

    public function updateProduct(Request $request)
    {
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
        $product = Product::where('id', $request->id)->first();
        $product->name = $request->name;
        $product->category = $request->category;
        $product->unit = $request->unit;
        $product->price = $request->price;
        $product->purchase_price = $request->purchase_price;
        $product->save();
        $status = true;
        $message = 'Updated';
        return response()->json(compact('status', 'message'));
    }

    public function deleteProduct(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $deleted = Product::where('id', $id)->delete();
            if ($deleted) {
                $status = true;
                $message = 'Product deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Product not found';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $error = 'Product not found';
            return response()->json(compact('status', 'error'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Product End
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Invoice Start
    |--------------------------------------------------------------------------
    */
    public function getInvoices(Request $request)
    {
        $name = $request->name;
        if (empty($name)) {
            $purchases = DB::table('purchases')
                ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->orderBy('id', 'desc')
                ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'purchases'));
        } else {
            $purchases = DB::table('purchases')
                ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id')
                ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->where('purchases.purchase_id', 'like', '%' . $name . '%')
                ->orWhere('suppliers.name', 'like', '%' . $name . '%')
                ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'purchases'));
        }
    }

    public function getInvoice($id)
    {
        $purchaseData = DB::table('purchases')
            ->select('suppliers.name AS supplier_name', 'suppliers.id AS supplier_id', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date', 'purchases.paid')
            ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchases.id', $id)
            ->first();
        $purchaseItems = DB::table('purchase_items')
            ->select('products.name', 'purchase_items.price', 'purchase_items.total', 'purchase_items.quantity', 'products.id')
            ->join('products', 'products.id', '=', 'purchase_items.product_id')
            ->where('purchase_items.purchase_id', $purchaseData->purchase_id)
            ->get();
        $purchase = array(
            'purchaseData' => $purchaseData,
            'purchaseItems' => $purchaseItems,
        );
        $status = true;
        return response()->json(compact('status', 'purchase'));
    }

    public function storeInvoice(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'date' => 'required',
                'productIds' => 'required',
                'productQuantities' => 'required',
                'productPrices' => 'required',
            ]
        );
        if ($validator->fails()) {
            $status = false;
            $errors = $validator->errors();
            return response()->json(compact('status', 'errors'));
        }
        $products = $request->productIds;
        $quantities = $request->productQuantities;
        $prices = $request->productPrices;
        if (!empty($request->customer_id)){
            $customerId = $request->customer_id;
        }else{
            $customerId = DB::table('customers')->where('name', 'Walking Customer')->first()->id;
        }
        if (count($products) > 0) {
            $txGenerator = new InvoiceNumberGeneratorService();
            $invoiceId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
            $txGenerator->setNextInvoiceNo();
            $total = 0;
            for ($i = 0, $n = count($products); $i < $n; $i++) {
                $productID = $products[$i];
                $quantity = $quantities[$i];
                $price = $prices[$i];
                $total += $quantity * $price;
                if ($quantity > 0) {
                    DB::table('invoice_items')->insert([
                        'invoice_id' => $invoiceId,
                        'product_id' => $productID,
                        'price' => $price,
                        'quantity' => $quantity,
                        'date' => $request->date,
                        'total' => $quantity * $price,
                    ]);
                }
            }
            DB::table('invoices')->insert(
                [
                    'customer_id' => $customerId,
                    'invoice_id' => $invoiceId,
                    'comment' => $request->comment,
                    'date' => $request->date,
                    'discount' => $request->discount,
                    'discountAmount' => $request->discountAmount,
                    'discountType' => $request->discountType,
                    'total' => $total,
                ]
            );
            $customerDueTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('customer_transaction');
            DB::table('customer_ledgers')->insert(array(
                'customer_id' => $customerId,
                'transaction_id' => $customerDueTxId,
                'reference_no' => "inv-$invoiceId",
                'type' => 'due',
                'due' => $total - $request->discountAmount,
                'deposit' => 0,
                'date' => $request->date,
                'comment' => "Due for Invoice ID ($invoiceId)"
            ));
            $txGenerator->setNextInvoiceNo();

            $paid = $request->cash + $request->bcash + $request->nagad + $request->card;

            if ($paid > 0) {
                $customerPaidTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
                DB::table('customer_ledgers')->insert(array(
                    'customer_id' => $customerId,
                    'reference_no' => "inv-$invoiceId",
                    'transaction_id' => $customerPaidTxId,
                    'type' => 'deposit',
                    'due' => 0,
                    'deposit' => $request->paid,
                    'date' => $request->date,
                    'comment' => "Deposit for Invoice ID ($invoiceId)"
                ));
                $txGenerator->setNextInvoiceNo();

                if (!empty($request->cash) && $request->cash > 0){
                    $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('cash_transaction');
                    DB::table('cash_books')->insert(array(
                        'transaction_id' => $cashTxId,
                        'reference_no' => "inv-$invoiceId",
                        'type' => 'payment',
                        'payment' => $request->paid,
                        'date' => $request->date,
                        'comment' => "Paid for Purchase id ($invoiceId)"
                    ));
                    $txGenerator->setNextInvoiceNo();
                }

                if (!empty($request->bcash) && $request->bcash > 0){
                    $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('bcash_transaction');
                    DB::table('cash_books')->insert(array(
                        'transaction_id' => $cashTxId,
                        'reference_no' => "inv-$invoiceId",
                        'type' => 'payment',
                        'payment' => $request->paid,
                        'date' => $request->date,
                        'comment' => "Paid for Purchase id ($invoiceId)"
                    ));
                    $txGenerator->setNextInvoiceNo();
                }
            }
            $status = true;
            $message = 'Purchase saved';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $error = 'Please add at least one product';
            return response()->json(compact('status', 'error'));
        }
    }

    public function updateInvoice(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'purchase_id' => 'required',
                'supplier_id' => 'required',
                'date' => 'required',
                'productIds' => 'required',
                'productQuantities' => 'required',
                'productPrices' => 'required',
                'total' => 'required',
            ]
        );
        if ($validator->fails()) {
            $status = false;
            $errors = $validator->errors();
            return response()->json(compact('status', 'errors'));
        }
        $products = $request->productIds;
        $quantities = $request->productQuantities;
        $prices = $request->productPrices;
        if (count($products) > 0) {
            $purchase = DB::table('purchases')->where('id', $request->purchase_id)->first();
            DB::table('purchases')->where('id', $request->purchase_id)->update(
                [
                    'supplier_id' => $request->supplier_id,
                    'amount' => $request->total,
                    'paid' => $request->paid,
                    'comment' => $request->comment,
                    'date' => $request->date,
                ]
            );
            DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)->delete();
            for ($i = 0, $n = count($products); $i < $n; $i++) {
                $productID = $products[$i];
                $quantity = $quantities[$i];
                $price = $prices[$i];
                if ($quantity > 0) {
                    DB::table('purchase_items')->insert([
                        'purchase_id' => $purchase->purchase_id,
                        'product_id' => $productID,
                        'price' => $price,
                        'quantity' => $quantity,
                        'date' => $request->date,
                        'total' => $quantity * $price,
                    ]);
                }
            }
            DB::table('supplier_ledgers')
                ->where('reference_no', $request->id)
                ->where('type', 'due')
                ->update(array(
                    'supplier_id' => $request->supplier_id,
                    'due' => $request->total,
                    'deposit' => 0,
                    'date' => $request->date
                ));
            DB::table('supplier_ledgers')
                ->where('reference_no', $request->id)
                ->where('type', 'deposit')
                ->delete();
            DB::table('cash_books')
                ->where('reference_no', $request->id)
                ->where('type', 'payment')
                ->delete();

            if (!empty($request->paid)) {
                $txGenerator = new InvoiceNumberGeneratorService();
                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
                DB::table('supplier_ledgers')->insert(array(
                    'supplier_id' => $request->supplier_id,
                    'reference_no' => $request->id,
                    'transaction_id' => $supplierPaidTxId,
                    'type' => 'deposit',
                    'due' => 0,
                    'deposit' => $request->paid,
                    'date' => $request->date,
                    'comment' => "Deposit for Purchase id ($purchase->purchase_id)"
                ));
                $txGenerator->setNextInvoiceNo();

                $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
                DB::table('cash_books')->insert(array(
                    'transaction_id' => $cashTxId,
                    'reference_no' => $request->id,
                    'type' => 'payment',
                    'payment' => $request->paid,
                    'date' => $request->date,
                    'comment' => "Paid for Purchase id ($purchase->purchase_id)"
                ));
                $txGenerator->setNextInvoiceNo();
            }
            $status = true;
            $message = 'Purchase saved';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $error = 'Please add at least one product';
            return response()->json(compact('status', 'error'));
        }
    }

    public function deleteInvoice(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $purchase = DB::table('purchases')->where('id', $request->id)->first();
            $deleted = DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)->delete();
            $deleted = DB::table('supplier_ledgers')
                ->where('reference_no', $request->id)
                ->delete();
            $deleted = DB::table('cash_books')
                ->where('reference_no', $request->id)
                ->delete();
            $deleted = Db::table('purchases')->where('id', $id)->delete();
            if ($deleted) {
                $status = true;
                $message = 'Purchase deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Purchase not found';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $error = 'Purchase not found';
            return response()->json(compact('status', 'error'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Purchase End
    |--------------------------------------------------------------------------
    */
}
