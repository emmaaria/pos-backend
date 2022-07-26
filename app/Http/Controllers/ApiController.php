<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;

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
        if (!$token = $this->guard()->attempt($validator->validated())) {
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
                $txId = $txIdGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
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
                $txId = $txIdGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
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
            ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date')
            ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->where('purchases.id', $id)
            ->first();
        $purchaseItems = DB::table('purchase_items')
            ->select('products.name AS product_name', 'purchase_items.price', 'purchase_items.total', 'purchase_items.quantity')
            ->join('products', 'products.id', '=', 'purchase_items.product_id')
            ->where('purchase_items.purchase_id', $purchaseData->purchase_id)
            ->get();
        $purchase = [$purchaseData, $purchaseItems];
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
            $txId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
            $txGenerator->setNextInvoiceNo();
            $pId = DB::table('purchases')->insertGetId(
                [
                    'supplier_id' => $request->supplier_id,
                    'amount' => $request->total,
                    'paid' => $request->paid,
                    'comment' => $request->comment,
                    'purchase_id' => $txId,
                    'date' => $request->date,
                ]
            );
            for ($i = 0, $n = count($products); $i < $n; $i++) {
                $productID = $products[$i];
                $quantity = $quantities[$i];
                $price = $prices[$i];
                if ($quantity > 0) {
                    DB::table('purchase_items')->insert([
                        'purchase_id' => $txId,
                        'product_id' => $productID,
                        'price' => $price,
                        'quantity' => $quantity,
                        'date' => $request->date,
                        'total' => $quantity * $price,
                    ]);
                }
            }
            $supplierDueTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
            DB::table('supplier_ledgers')->insert(array(
                'supplier_id' => $request->supplier_id,
                'transaction_id' => $supplierDueTxId,
                'reference_no' => $pId,
                'type' => 'due',
                'due' => $request->total,
                'deposit' => 0,
                'date' => $request->date,
                'comment' => "Due for Purchase id ($txId)"
            ));
            $txGenerator->setNextInvoiceNo();

            if (!empty($request->paid)) {
                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
                DB::table('supplier_ledgers')->insert(array(
                    'supplier_id' => $request->supplier_id,
                    'reference_no' => $pId,
                    'transaction_id' => $supplierPaidTxId,
                    'type' => 'deposit',
                    'due' => 0,
                    'deposit' => $request->paid,
                    'date' => $request->date,
                    'comment' => "Deposit for Purchase id ($txId)"
                ));
                $txGenerator->setNextInvoiceNo();

                $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
                DB::table('cash_books')->insert(array(
                    'transaction_id' => $cashTxId,
                    'reference_no' => $pId,
                    'type' => 'payment',
                    'payment' => $request->paid,
                    'date' => $request->date,
                    'comment' => "Paid for Purchase id ($txId)"
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
                'id' => 'required',
                'name' => 'required',
            ]
        );
        if ($validator->fails()) {
            $status = false;
            $errors = $validator->errors();
            return response()->json(compact('status', 'errors'));
        }
        $supplier = Supplier::where('id', $request->id)->first();
        $supplier->name = $request->name;
        $supplier->mobile = $request->mobile;
        $supplier->address = $request->address;
        $supplier->save();
        $status = true;
        $message = 'Updated';
        return response()->json(compact('status', 'message'));
    }

    public function deletePurchase(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $deleted = Supplier::where('id', $id)->delete();
            SupplierLedger::where('supplier_id', $id)->delete();
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
}
