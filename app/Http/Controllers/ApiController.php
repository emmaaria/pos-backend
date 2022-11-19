<?php

namespace App\Http\Controllers;

use App\Models\AveragePurchasePrice;
use App\Models\Product;
use App\Models\User;
use Exception;
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

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'mobile' => 'required',
                'company_id' => 'required',
                'password' => 'required',
            ],
            [
                'mobile.required' => 'Mobile number required',
                'company_id.required' => 'Company number required',
                'password.required' => 'Password required',
            ]
        );
        if ($validator->fails()) {
            $status = false;
            $errors = '';
            foreach ($validator->errors()->all() as $message) {
                $errors .= $message;
            }
            return response()->json(compact('status', 'errors'));
        }
        $credentials = array('email' => $request->mobile, 'password' => $request->password);
        $user = DB::table('users')->where('email', $request->mobile)->where('company_id', $request->company_id)->first();
        $company = DB::table('companies')->where('company_id', $request->company_id)->first();
        if ($user && $company) {
            if (!empty($user)) {
                $userData = array(
                    'company_id' => encrypt($user->company_id),
                );
            } else {
                $userData = null;
            }
            if (!$token = auth()->claims($userData)->attempt($credentials)) {
                $status = false;
                $errors = 'Credentials did not matched';
                return response()->json(compact('status', 'errors'));
            }
            $company = array(
                'company_name' => $company->name,
                'company_address' => $company->address,
                'company_mobile' => $company->mobile,
                'vat_number' => $company->vat_number,
                'mushok_number' => $company->mushok_number,
            );
            $status = true;
            return response()->json(compact('status', 'user', 'token', 'company'));
        } else {
            $status = false;
            $errors = 'Mobile, password and company id did not matched';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function profile()
    {
        return response()->json($this->guard()->user());
    }

    public function logout()
    {
        auth()->logout(true);
        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    /*
    |--------------------------------------------------------------------------
    | Category Start
    |--------------------------------------------------------------------------
    */
    public function getCategories(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $categories = DB::table('categories')->select('id', 'name')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'categories'));
            } elseif (!empty($all)) {
                $categories = DB::table('categories')->where('company_id', $companyId)->get();
                $status = true;
                return response()->json(compact('status', 'categories'));
            } else {
                $categories = DB::table('categories')->select('id', 'name')->where('name', 'like', '%' . $name . '%')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'categories'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getCategory($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $category = DB::table('categories')->where('id', $id)->where('company_id', $companyId)->first();
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
            $categoty = DB::table('categories')->insert(['name' => $request->name, 'company_id' => $companyId]);
            if ($categoty) {
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
            DB::table('categories')->where('id', $request->id)->where('company_id', $companyId)->update(['name' => $request->name]);
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
                $deleted = DB::table('categories')->where('id', $id)->where('company_id', $companyId)->delete();
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
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $units = DB::table('units')->select('id', 'name')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'units'));
            } elseif (!empty($all)) {
                $units = DB::table('units')->where('company_id', $companyId)->get();
                $status = true;
                return response()->json(compact('status', 'units'));
            } else {
                $units = DB::table('units')->select('id', 'name')->where('name', 'like', '%' . $name . '%')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'units'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getUnit($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $unit = DB::table('units')->where('id', $id)->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'unit'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeUnit(Request $request)
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
            $unit = DB::table('units')->insert(['name' => $request->name, 'company_id' => $companyId]);
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

    public function updateUnit(Request $request)
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
            DB::table('units')->where('id', $request->id)->where('company_id', $companyId)->update(['name' => $request->name]);
            $status = true;
            $message = 'Updated';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteUnit(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                $deleted = DB::table('units')->where('id', $id)->where('company_id', $companyId)->delete();
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
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
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
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            if (empty($name)) {
                $customers = DB::table('customers')
                    ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                    ->where('customers.company_id', $companyId)
                    ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'customers'));
            } else {
                $customers = DB::table('customers')
                    ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                    ->where('customers.company_id', $companyId)
                    ->where('customers.name', 'like', '%' . $name . '%')
                    ->orWhere('customers.mobile', 'like', '%' . $name . '%')
                    ->orWhere('customers.address', 'like', '%' . $name . '%')
                    ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'customers'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getCustomer($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $customer = DB::table('customers')->where('id', $id)->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'customer'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeCustomer(Request $request)
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
            try {
                DB::transaction(function () use ($companyId, $request) {
                    $customerId = DB::table('customers')->insertGetId(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address, 'company_id' => $companyId]);
                    if (!empty($request->due)) {
                        $txIdGenerator = new InvoiceNumberGeneratorService();
                        $txId = $txIdGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('customer_transaction');
                        DB::table('customer_ledgers')->insert(array(
                            'customer_id' => $customerId,
                            'transaction_id' => $txId,
                            'company_id' => $companyId,
                            'type' => 'due',
                            'due' => $request->due,
                            'deposit' => 0,
                            'date' => date('Y-m-d'),
                            'comment' => 'Previous Due'
                        ));
                        $txIdGenerator->setNextInvoiceNo();
                    }
                    $customer = DB::table('customers')
                        ->select('customers.id', 'customers.name', 'customers.mobile', 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                        ->leftJoin('customer_ledgers', 'customer_ledgers.customer_id', '=', 'customers.id')
                        ->where('customers.company_id', $companyId)
                        ->where('customers.id', $customerId)
                        ->groupBy('customers.id', 'customers.name', 'customers.mobile', 'customers.address')
                        ->first();
                    $status = true;
                    $message = 'Successfully saved';
                    return response()->json(compact('status', 'message', 'customer'));
                });
            } catch (Exception $e) {
                $status = false;
                $errors = 'Something went wrong';
                return response()->json(compact('status', 'errors'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updateCustomer(Request $request)
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
            DB::table('customers')->where('id', $request->id)->where('company_id', $companyId)->update(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address]);
            $status = true;
            $message = 'Updated';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteCustomer(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                try {
                    DB::transaction(function () use ($companyId, $id) {
                        DB::table('customers')->where('id', $id)->where('company_id', $companyId)->delete();
                        DB::table('customer_ledgers')->where('company_id', $companyId)->where('customer_id', $id)->delete();
                        $invoices = DB::table('invoices')->where('customer_id', $id)->where('company_id', $companyId)->get();
                        foreach ($invoices as $invoice) {
                            DB::table('invoice_items')->where('invoice_id', $invoice->invoice_id)->where('company_id', $companyId)->delete();
                            DB::table('bkash_transactions')->where('reference_no', 'inv-' . $invoice->invoice_id)->where('company_id', $companyId)->delete();
                            DB::table('card_transactions')->where('reference_no', 'inv-' . $invoice->invoice_id)->where('company_id', $companyId)->delete();
                            DB::table('cash_books')->where('reference_no', 'inv-' . $invoice->invoice_id)->where('company_id', $companyId)->delete();
                            DB::table('nagad_transactions')->where('reference_no', 'inv-' . $invoice->invoice_id)->where('company_id', $companyId)->delete();
                        }
                    });
                } catch (Exception $e) {
                    $status = false;
                    $errors = 'Something went wrong';
                    return response()->json(compact('status', 'errors'));
                }

                $status = true;
                $message = 'Customer deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Customer not found';
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
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $suppliers = DB::table('suppliers')
                    ->select('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('supplier_ledgers', 'supplier_ledgers.supplier_id', '=', 'suppliers.id')
                    ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'suppliers'));
            } elseif (!empty($all)) {
                $suppliers = DB::table('suppliers')
                    ->select('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('supplier_ledgers', 'supplier_ledgers.supplier_id', '=', 'suppliers.id')
                    ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address')
                    ->get();
                $status = true;
                return response()->json(compact('status', 'suppliers'));
            } else {
                $suppliers = DB::table('suppliers')
                    ->select('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('supplier_ledgers', 'supplier_ledgers.supplier_id', '=', 'suppliers.id')
                    ->where('suppliers.name', 'like', '%' . $name . '%')
                    ->orWhere('suppliers.mobile', 'like', '%' . $name . '%')
                    ->orWhere('suppliers.address', 'like', '%' . $name . '%')
                    ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.mobile', 'suppliers.address')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'suppliers'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getSupplier($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $supplier = DB::table('suppliers')->where('id', $id)->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'supplier'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeSupplier(Request $request)
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
            try {
                DB::transaction(function () use ($companyId, $request) {
                    $supplierId = DB::table('suppliers')->insertGetId(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address, 'company_id' => $companyId]);
                    if (!empty($request->due)) {
                        $txIdGenerator = new InvoiceNumberGeneratorService();
                        $txId = $txIdGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                        DB::table('supplier_ledgers')->insert(array(
                            'supplier_id' => $supplierId,
                            'transaction_id' => $txId,
                            'type' => 'due',
                            'due' => $request->due,
                            'company_id' => $companyId,
                            'deposit' => 0,
                            'date' => date('Y-m-d'),
                            'comment' => 'Previous Due'
                        ));
                        $txIdGenerator->setNextInvoiceNo();
                    }
                });
            } catch (Exception $e) {
                $status = false;
                $errors = 'Something went wrong';
                return response()->json(compact('status', 'errors'));
            }
            $status = true;
            return response()->json(compact('status'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updateSupplier(Request $request)
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
            DB::table('suppliers')->where('id', $request->id)->where('company_id', $companyId)->update(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address]);
            $status = true;
            $message = 'Updated';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteSupplier(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                try {
                    DB::transaction(function () use ($companyId, $id) {
                        DB::table('suppliers')->where('id', $id)->where('company_id', $companyId)->delete();
                        DB::table('supplier_ledgers')->where('company_id', $companyId)->where('customer_id', $id)->delete();
                        $purchases = DB::table('purchase')->where('supplier_id', $id)->where('company_id', $companyId)->get();
                        DB::table('supplier_products')->where('supplier_id', $id)->where('company_id', $companyId)->delete();
                        foreach ($purchases as $purchase) {
                            DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)->where('company_id', $companyId)->delete();
                            DB::table('bkash_transactions')->where('reference_no', 'pur-' . $purchase->purchase_id)->where('company_id', $companyId)->delete();
                            DB::table('card_transactions')->where('reference_no', 'pur-' . $purchase->purchase_id)->where('company_id', $companyId)->delete();
                            DB::table('cash_books')->where('reference_no', 'pur-' . $purchase->purchase_id)->where('company_id', $companyId)->delete();
                            DB::table('nagad_transactions')->where('reference_no', 'pur-' . $purchase->purchase_id)->where('company_id', $companyId)->delete();
                        }
                    });
                } catch (Exception $e) {
                    $status = false;
                    $errors = 'Something went wrong';
                    return response()->json(compact('status', 'errors'));
                }

                $status = true;
                $message = 'Customer deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Supplier not found';
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
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            if (empty($name)) {
                $purchases = DB::table('purchases')
                    ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date')
                    ->where('purchases.company_id', $companyId)
                    ->where('suppliers.company_id', $companyId)
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                    ->orderBy('id', 'desc')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'purchases'));
            } else {
                $purchases = DB::table('purchases')
                    ->select('suppliers.name AS supplier_name', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date')
                    ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                    ->where('purchases.company_id', $companyId)
                    ->where('purchases.purchase_id', 'like', '%' . $name . '%')
                    ->orWhere('suppliers.name', 'like', '%' . $name . '%')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'purchases'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getPurchase($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $purchaseData = DB::table('purchases')
                ->select('suppliers.name AS supplier_name', 'suppliers.id AS supplier_id', 'purchases.purchase_id', 'purchases.amount', 'purchases.comment', 'purchases.id', 'purchases.date', 'purchases.paid')
                ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->where('purchases.company_id', $companyId)
                ->where('purchases.id', $id)
                ->first();
            $purchaseItems = DB::table('purchase_items')
                ->select('products.name', 'purchase_items.price as purchase_price', 'purchase_items.total', 'purchase_items.quantity', 'products.product_id')
                ->where('purchase_items.company_id', $companyId)
                ->where('products.company_id', $companyId)
                ->join('products', 'products.product_id', '=', 'purchase_items.product_id')
                ->where('purchase_items.purchase_id', $purchaseData->purchase_id)
                ->get();
            $purchase = array(
                'purchaseData' => $purchaseData,
                'purchaseItems' => $purchaseItems,
            );
            $status = true;
            return response()->json(compact('status', 'purchase'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storePurchase(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'supplier_id' => 'required',
                    'date' => 'required',
                    'productIds' => 'required',
                    'productQuantities' => 'required',
                    'productPrices' => 'required',
                    'total' => 'required',
                    'paymentMethod' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            if (count($request->productIds) > 0) {
                try {
                    DB::transaction(function () use ($request, $companyId) {
                        $products = $request->productIds;
                        $quantities = $request->productQuantities;
                        $prices = $request->productPrices;
                        $txGenerator = new InvoiceNumberGeneratorService();
                        $purchaseId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('purchase');
                        $txGenerator->setNextInvoiceNo();
                        DB::table('purchases')->insertGetId(
                            [
                                'supplier_id' => $request->supplier_id,
                                'amount' => $request->total,
                                'paid' => $request->cash + $request->bkash + $request->nagad + $request->bank,
                                'comment' => $request->comment,
                                'purchase_id' => $purchaseId,
                                'date' => $request->date,
                                'company_id' => $companyId,
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
                                    'company_id' => $companyId,
                                ]);
                                $averagePrice = DB::table('purchase_items')
                                    ->select(
                                        DB::raw('SUM(quantity) as totalQuantity'),
                                        DB::raw('SUM(total) as totalPrice')
                                    )->where('product_id', $productID)->where('company_id', $companyId)->first();
                                DB::table('average_purchase_prices')->where('product_id', $productID)
                                    ->where('company_id', $companyId)
                                    ->update(['price' => number_format($averagePrice->totalPrice / $averagePrice->totalQuantity, 2, '.', '')]);
                            }
                        }
                        $supplierDueTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                        DB::table('supplier_ledgers')->insert(array(
                            'supplier_id' => $request->supplier_id,
                            'transaction_id' => $supplierDueTxId,
                            'reference_no' => 'pur-' . $purchaseId,
                            'type' => 'due',
                            'due' => $request->total,
                            'deposit' => 0,
                            'date' => $request->date,
                            'company_id' => $companyId,
                            'comment' => "Due for Purchase id ($purchaseId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                        $paymentMethod = $request->paymentMethod;
                        if ($paymentMethod == 'cash') {
                            if (!empty($request->cash) && $request->cash > 0) {
                                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $request->supplier_id,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'transaction_id' => $supplierPaidTxId,
                                    'type' => 'deposit',
                                    'due' => 0,
                                    'deposit' => $request->cash,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Deposit for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                                $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('cash_transaction');
                                DB::table('cash_books')->insert(array(
                                    'transaction_id' => $cashTxId,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'type' => 'payment',
                                    'payment' => $request->cash,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }
                        } elseif ($paymentMethod == 'bkash') {
                            if (!empty($request->bkash) && $request->bkash > 0) {
                                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $request->supplier_id,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'transaction_id' => $supplierPaidTxId,
                                    'type' => 'deposit',
                                    'due' => 0,
                                    'deposit' => $request->bkash,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Deposit for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                                $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bkash_transaction');
                                DB::table('bkash_transaction')->insert(array(
                                    'transaction_id' => $bkashTxId,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'type' => 'payment',
                                    'payment' => $request->bkash,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }
                        } elseif ($paymentMethod == 'nagad') {
                            if (!empty($request->nagad) && $request->nagad > 0) {
                                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $request->supplier_id,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'transaction_id' => $supplierPaidTxId,
                                    'type' => 'deposit',
                                    'due' => 0,
                                    'deposit' => $request->nagad,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Deposit for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                                $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('nagad_transaction');
                                DB::table('nagad_transaction')->insert(array(
                                    'transaction_id' => $nagadTxId,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'type' => 'payment',
                                    'payment' => $request->nagad,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }
                        } elseif ($paymentMethod == 'bank') {
                            if (!empty($request->bank) && $request->bank > 0) {
                                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $request->supplier_id,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'transaction_id' => $supplierPaidTxId,
                                    'type' => 'deposit',
                                    'due' => 0,
                                    'deposit' => $request->bank,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Deposit for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                                $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                                DB::table('bank_ledgers')->insert(array(
                                    'transaction_id' => $bankTxId,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'type' => 'withdraw',
                                    'withdraw' => $request->bank,
                                    'bank_id' => $request->bankId,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }
                        } elseif ($paymentMethod == 'multiple') {
                            if (!empty($request->cash) && $request->cash > 0) {
                                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $request->supplier_id,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'transaction_id' => $supplierPaidTxId,
                                    'type' => 'deposit',
                                    'due' => 0,
                                    'deposit' => $request->cash,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Deposit for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                                $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('cash_transaction');
                                DB::table('cash_books')->insert(array(
                                    'transaction_id' => $cashTxId,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'type' => 'payment',
                                    'payment' => $request->cash,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->bkash) && $request->bkash > 0) {
                                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $request->supplier_id,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'transaction_id' => $supplierPaidTxId,
                                    'type' => 'deposit',
                                    'due' => 0,
                                    'deposit' => $request->bkash,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Deposit for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                                $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bkash_transaction');
                                DB::table('bkash_transaction')->insert(array(
                                    'transaction_id' => $bkashTxId,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'type' => 'payment',
                                    'payment' => $request->bkash,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->nagad) && $request->nagad > 0) {
                                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $request->supplier_id,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'transaction_id' => $supplierPaidTxId,
                                    'type' => 'deposit',
                                    'due' => 0,
                                    'deposit' => $request->nagad,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Deposit for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                                $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('nagad_transaction');
                                DB::table('nagad_transaction')->insert(array(
                                    'transaction_id' => $nagadTxId,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'type' => 'payment',
                                    'payment' => $request->nagad,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->bank) && $request->bank > 0) {
                                $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('supplier_transaction');
                                DB::table('supplier_ledgers')->insert(array(
                                    'supplier_id' => $request->supplier_id,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'transaction_id' => $supplierPaidTxId,
                                    'type' => 'deposit',
                                    'due' => 0,
                                    'deposit' => $request->bank,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Deposit for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                                $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                                DB::table('bank_ledgers')->insert(array(
                                    'transaction_id' => $bankTxId,
                                    'reference_no' => 'pur-' . $purchaseId,
                                    'type' => 'withdraw',
                                    'withdraw' => $request->bank,
                                    'bank_id' => $request->bankId,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Purchase id ($purchaseId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }
                        }
                    });
                } catch (Exception $e) {
                    $status = false;
                    $errors = $e;
                    return response()->json(compact('status', 'errors'));
                }
                $status = true;
                $message = 'Purchase saved';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Please add at least one product';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updatePurchase(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
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
                $purchase = DB::table('purchases')->where('id', $request->purchase_id)->where('company_id', $companyId)->first();
                DB::table('purchases')->where('id', $request->purchase_id)->where('company_id', $companyId)->update(
                    [
                        'supplier_id' => $request->supplier_id,
                        'amount' => $request->total,
                        'paid' => $request->paid,
                        'comment' => $request->comment,
                        'date' => $request->date,
                    ]
                );
                DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)
                    ->where('company_id', $companyId)
                    ->delete();
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
                            'company_id' => $companyId,
                            'total' => $quantity * $price,
                        ]);
                        $averagePrice = DB::table('purchase_items')
                            ->select(
                                DB::raw('SUM(quantity) as totalQuantity'),
                                DB::raw('SUM(total) as totalPrice')
                            )->where('product_id', $productID)->where('company_id', $companyId)->first();
                        DB::table('average_purchase_prices')->where('product_id', $productID)
                            ->where('company_id', $companyId)
                            ->update(['price' => number_format($averagePrice->totalPrice / $averagePrice->totalQuantity, 2, '.', '')]);
                    }
                }
                DB::table('supplier_ledgers')
                    ->where('reference_no', "pur-$purchase->purchase_id")
                    ->where('type', 'due')
                    ->where('company_id', $companyId)
                    ->update(array(
                        'supplier_id' => $request->supplier_id,
                        'due' => $request->total,
                        'deposit' => 0,
                        'date' => $request->date
                    ));
                DB::table('supplier_ledgers')
                    ->where('reference_no', "pur-$purchase->purchase_id")
                    ->where('type', 'deposit')
                    ->where('company_id', $companyId)
                    ->delete();
                DB::table('cash_books')
                    ->where('reference_no', "pur-$purchase->purchase_id")
                    ->where('type', 'payment')
                    ->where('company_id', $companyId)
                    ->delete();

                if (!empty($request->paid)) {
                    $txGenerator = new InvoiceNumberGeneratorService();
                    $supplierPaidTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('supplier_transaction');
                    DB::table('supplier_ledgers')->insert(array(
                        'supplier_id' => $request->supplier_id,
                        'reference_no' => "pur-$purchase->purchase_id",
                        'transaction_id' => $supplierPaidTxId,
                        'type' => 'deposit',
                        'company_id' => $companyId,
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
                        'company_id' => $companyId,
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
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deletePurchase(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                $purchase = DB::table('purchases')->where('id', $request->id)->where('company_id', $companyId)->first();
                $deleted = DB::table('purchase_items')->where('purchase_id', $purchase->purchase_id)->where('company_id', $companyId)->delete();
                $deleted = DB::table('supplier_ledgers')
                    ->where('reference_no', "pur-$purchase->purchase_id")
                    ->where('company_id', $companyId)
                    ->delete();
                $deleted = DB::table('cash_books')
                    ->where('reference_no', "pur-$purchase->purchase_id")
                    ->where('company_id', $companyId)
                    ->delete();
                $deleted = Db::table('purchases')->where('id', $id)->where('company_id', $companyId)->delete();
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
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
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
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->all;
            if (empty($name) && empty($all)) {
                $products = DB::table('products')->select('*')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'products', 'companyId'));
            } elseif (!empty($all)) {
                $products = DB::table('products')->select('*')->where('company_id', $companyId)->get();
                $status = true;
                return response()->json(compact('status', 'products'));
            } else {
                $products = DB::table('products')
                    ->select('*')
                    ->where('company_id', $companyId)
                    ->where('name', 'like', '%' . $name . '%')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'products'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getProduct($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $product = Product::where('id', $id)->where('company_id', $companyId)->first();
            $suppliers = DB::table('supplier_products')
                ->select('suppliers.id', 'suppliers.name')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_products.supplier_id')
                ->where('supplier_products.product_id', $product->product_id)
                ->where('supplier_products.company_id', $companyId)
                ->get();
            $status = true;
            return response()->json(compact('status', 'product', 'suppliers'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getProductByBarcode(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $product = Product::where('product_id', $request->id)->where('company_id', $companyId)->first();
            if (!empty($product)) {
                $status = true;
                return response()->json(compact('status', 'product'));
            } else {
                $status = false;
                $message = 'No product fround';
                return response()->json(compact('status', 'message'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeProduct(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'name' => 'required',
                ],
                [
                    'name.required' => 'Product name is required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            if (!empty($request->product_id)) {
                $productId = $request->product_id;
            } else {
                $productIdGenerator = new InvoiceNumberGeneratorService();
                $productId = $productIdGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('product');
                $productIdGenerator->setNextInvoiceNo();
            }
            try {
                DB::transaction(function () use ($request, $companyId, $productId) {
                    Product::create(array(
                        'name' => $request->name,
                        'product_id' => $productId,
                        'category' => $request->category,
                        'company_id' => $companyId,
                        'unit' => $request->unit,
                        'price' => $request->price,
                        'purchase_price' => $request->purchase_price,
                        'weight' => $request->weight ?: 0,
                    ));
                    AveragePurchasePrice::create(array(
                        'product_id' => $productId,
                        'price' => $request->purchase_price,
                        'company_id' => $companyId,
                    ));
                    $suppliers = $request->suppliers;
                    if (!empty($suppliers) && count($suppliers) > 0) {
                        foreach ($suppliers as $supplier) {
                            DB::table('supplier_products')->insert(['supplier_id' => $supplier['id'], 'product_id' => $productId, 'company_id' => $companyId]);
                        }
                    }
                });
            } catch (Exception $e) {
                $status = false;
                $errors = 'Something went wrong';
                return response()->json(compact('status', 'errors'));
            }
            $status = true;
            $message = "Successfully Saved";
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updateProduct(Request $request)
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
            try {
                DB::transaction(function () use ($companyId, $request) {
                    $product = Product::where('id', $request->id)->where('company_id', $companyId)->first();
                    $product->name = $request->name;
                    $product->category = $request->category;
                    $product->unit = $request->unit;
                    $product->price = $request->price;
                    $product->purchase_price = $request->purchase_price;
                    $product->weight = $request->weight;
                    $product->save();
                    DB::table('supplier_products')->where('product_id', $product->product_id)->where('company_id', $companyId)->delete();
                    $suppliers = $request->suppliers;
                    if (!empty($suppliers) && count($suppliers) > 0) {
                        foreach ($suppliers as $supplier) {
                            DB::table('supplier_products')->insert(['supplier_id' => $supplier['id'], 'product_id' => $product->product_id, 'company_id' => $companyId]);
                        }
                    }
                });
            } catch (Exception $e) {
                $status = false;
                $errors = 'Something went wrong';
                return response()->json(compact('status', 'errors'));
            }
            $status = true;
            $message = 'Updated';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteProduct(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                try {
                    DB::transaction(function () use ($companyId, $id) {
                        $product = Product::where('id', $id)->where('company_id', $companyId)->first();
                        AveragePurchasePrice::where('product_id', $product->product_id)->where('company_id', $companyId)->delete();
                        DB::table('supplier_products')->where('product_id', $product->product_id)->where('company_id', $companyId)->delete();
                        $product->delete();
                        return true;
                    });
                } catch (Exception $e) {
                    $status = false;
                    $errors = 'Something went wrong';
                    return response()->json(compact('status', 'errors'));
                }
                $status = true;
                $message = 'Product deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $errors = 'Product not found';
                return response()->json(compact('status', 'errors'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
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
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->keyword;
            if (empty($name)) {
                $invoices = DB::table('invoices')
                    ->select('customers.name AS customer_name', 'invoices.invoice_id', 'invoices.total', 'invoices.discountAmount', 'invoices.comment', 'invoices.id', 'invoices.date')
                    ->where('invoices.company_id', $companyId)
                    ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                    ->orderBy('id', 'desc')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'invoices'));
            } else {
                $invoices = DB::table('invoices')
                    ->select('customers.name AS customer_name', 'invoices.invoice_id', 'invoices.total', 'invoices.discountAmount', 'invoices.comment', 'invoices.id', 'invoices.date')
                    ->where('invoices.company_id', $companyId)
                    ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                    ->where('invoices.invoice_id', 'like', '%' . $name . '%')
                    ->orWhere('customers.name', 'like', '%' . $name . '%')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'invoices'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getInvoice($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $purchaseData = DB::table('invoices')
                ->where('invoices.company_id', $companyId)
                ->select('customers.name AS customer_name', 'invoices.*')
                ->leftJoin('customers', 'customers.id', '=', 'invoices.customer_id')
                ->where('invoices.invoice_id', $id)
                ->first();
            $purchaseItems = DB::table('invoice_items')
                ->select('products.name', 'invoice_items.*')
                ->where('invoice_items.company_id', $companyId)
                ->join('products', 'products.product_id', '=', 'invoice_items.product_id')
                ->where('invoice_items.invoice_id', $id)
                ->get();

            $cash = DB::table('cash_books')
                ->where('reference_no', "inv-$id")
                ->where('company_id', $companyId)
                ->where('type', 'receive')
                ->first();
            $bkash = DB::table('bkash_transactions')
                ->where('reference_no', "inv-$id")
                ->where('company_id', $companyId)
                ->where('type', 'deposit')
                ->first();
            $nagad = DB::table('nagad_transactions')
                ->where('reference_no', "inv-$id")
                ->where('company_id', $companyId)
                ->where('type', 'deposit')
                ->first();
            $card = DB::table('card_transactions')
                ->where('reference_no', "inv-$id")
                ->where('type', 'deposit')
                ->where('company_id', $companyId)
                ->first();

            $payments = array(
                'cash' => $cash->receive,
                'bkash' => $bkash ? $bkash->deposit : 0,
                'nagad' => $nagad ? $nagad->deposit : 0,
                'card' => $card ? $card->deposit : 0,
            );
            $invoice = array(
                'invoiceData' => $purchaseData,
                'invoiceItems' => $purchaseItems,
                'payments' => $payments
            );
            $status = true;
            return response()->json(compact('status', 'invoice'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeInvoice(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
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
            $customerName = '';

            if (!empty($request->customer_id)) {
                $customerId = $request->customer_id;
                $customer = DB::table('customers')->where('id', $request->customer_id)->where('company_id', $companyId)->first();
                $customerName = $customer->name;
            } else {
                $customer = DB::table('customers')->where('name', 'Walking Customer')->where('company_id', $companyId)->first();
                if ($customer){
                    $customerId = $customer->id;
                    $customerName = $customer->name;
                }else{
                    $status = false;
                    $errors = 'No customer selected. Please select a customer or add Walking Customer.';
                    return response()->json(compact('status', 'errors'));
                }
            }
            if (!empty($request->bank)) {
                if (empty($request->bankId)) {
                    $status = false;
                    $errors = 'Please select bank account';
                    return response()->json(compact('status', 'errors'));
                }
            }
            if (count($products) > 0) {
                try {
                    $invoice = DB::transaction(function () use ($request, $companyId, $products, $quantities, $prices, $customerId, $customerName) {
                        $invoice = array();
                        $invoice['customer_name'] = $customerName;
                        $txGenerator = new InvoiceNumberGeneratorService();
                        $invoiceId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
                        $txGenerator->setNextInvoiceNo();
                        $invoice['invoice_id'] = $invoiceId;
                        $invoice['date'] = $request->date;
                        $total = 0;
                        $profit = 0;
                        for ($i = 0, $n = count($products); $i < $n; $i++) {
                            $productID = $products[$i];
                            $product = DB::table('products')->select('name')->where('product_id', $productID)->first();
                            $quantity = $quantities[$i];
                            $price = $prices[$i];
                            $total += $quantity * $price;
                            if ($quantity > 0) {
                                $invoiceItemData = array(
                                    'name' => $product->name,
                                    'invoice_id' => $invoiceId,
                                    'product_id' => $productID,
                                    'price' => $price,
                                    'company_id' => $companyId,
                                    'quantity' => $quantity,
                                    'date' => $request->date,
                                    'total' => $quantity * $price,
                                );
                                $invoice['items'][] = $invoiceItemData;
                                DB::table('invoice_items')->insert([
                                    'invoice_id' => $invoiceId,
                                    'product_id' => $productID,
                                    'price' => $price,
                                    'company_id' => $companyId,
                                    'quantity' => $quantity,
                                    'date' => $request->date,
                                    'total' => $quantity * $price,
                                ]);
                                $purchasePrice = DB::table('average_purchase_prices')->where('product_id', $productID)->where('company_id', $companyId)->first();
                                $profit += ($quantity * $price) - ($quantity * $purchasePrice->price);
                            }
                        }
                        $invoice['cash'] = $request->cash;
                        $invoice['bkash'] = $request->bkash;
                        $invoice['nagad'] = $request->nagad;
                        $invoice['card'] = $request->card;
                        $invoice['bank'] = $request->bank;
                        $invoice['discountType'] = $request->discountType;
                        $invoice['discount'] = $request->discount;
                        $invoice['discountAmount'] = $request->discountAmount;
                        $invoice['subtotal'] = $total;
                        $invoice['grandTotal'] = $total - $request->discountAmount;
                        $paid = $request->cash + $request->bkash + $request->nagad + $request->card + $request->bank;
                        $invoice['due'] = $total - $paid;
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
                                'paid_amount' => $paid,
                                'company_id' => $companyId,
                                'profit' => $profit - $request->discountAmount,
                            ]
                        );
                        $customerDueTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('customer_transaction');
                        $grandTotal = $total - $request->discountAmount;
                        DB::table('customer_ledgers')->insert(array(
                            'customer_id' => $customerId,
                            'transaction_id' => $customerDueTxId,
                            'reference_no' => "inv-$invoiceId",
                            'type' => 'due',
                            'company_id' => $companyId,
                            'due' => $grandTotal,
                            'deposit' => 0,
                            'date' => $request->date,
                            'comment' => "Due for Invoice ID ($invoiceId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                        if ($paid > $grandTotal) {
                            $paid = $grandTotal;
                        }
                        if ($paid > 0) {
                            $customerPaidTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
                            DB::table('customer_ledgers')->insert(array(
                                'customer_id' => $customerId,
                                'reference_no' => "inv-$invoiceId",
                                'transaction_id' => $customerPaidTxId,
                                'type' => 'deposit',
                                'company_id' => $companyId,
                                'due' => 0,
                                'deposit' => $paid,
                                'date' => $request->date,
                                'comment' => "Deposit for Invoice ID ($invoiceId)"
                            ));
                            $txGenerator->setNextInvoiceNo();

                            if (!empty($request->cash) && $request->cash > 0) {
                                $onlinePayments = $request->bkash + $request->nagad + $request->card + $request->bank;
                                $dueAfterOnlinePayment = $grandTotal - $onlinePayments;
                                $cashPaid = $request->cash;
                                $change = $grandTotal - ($dueAfterOnlinePayment - $cashPaid);
                                $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('cash_transaction');

                                if ($change > 0) {
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'company_id' => $companyId,
                                        'reference_no' => "inv-$invoiceId",
                                        'type' => 'receive',
                                        'receive' => $dueAfterOnlinePayment,
                                        'date' => $request->date,
                                        'comment' => "Cash receive for Invoice No ($invoiceId)"
                                    ));
                                } elseif ($change < 0) {
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'company_id' => $companyId,
                                        'reference_no' => "inv-$invoiceId",
                                        'type' => 'receive',
                                        'receive' => $cashPaid,
                                        'date' => $request->date,
                                        'comment' => "Cash receive for Invoice No ($invoiceId)"
                                    ));
                                } else {
                                    DB::table('cash_books')->insert(array(
                                        'transaction_id' => $cashTxId,
                                        'company_id' => $companyId,
                                        'reference_no' => "inv-$invoiceId",
                                        'type' => 'receive',
                                        'receive' => $cashPaid,
                                        'date' => $request->date,
                                        'comment' => "Cash receive for Invoice No ($invoiceId)"
                                    ));
                                }

                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->bkash) && $request->bkash > 0) {
                                $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('bkash_transaction');
                                DB::table('bkash_transactions')->insert(array(
                                    'transaction_id' => $cashTxId,
                                    'company_id' => $companyId,
                                    'reference_no' => "inv-$invoiceId",
                                    'type' => 'deposit',
                                    'deposit' => $request->bkash,
                                    'date' => $request->date,
                                    'comment' => "Cash receive for Invoice No ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->nagad) && $request->nagad > 0) {
                                $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('nagad_transaction');
                                DB::table('nagad_transactions')->insert(array(
                                    'transaction_id' => $cashTxId,
                                    'reference_no' => "inv-$invoiceId",
                                    'company_id' => $companyId,
                                    'type' => 'deposit',
                                    'deposit' => $request->nagad,
                                    'date' => $request->date,
                                    'comment' => "Cash receive for Invoice No ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->card) && $request->card > 0) {
                                $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('card_transaction');
                                DB::table('card_transactions')->insert(array(
                                    'transaction_id' => $cashTxId,
                                    'company_id' => $companyId,
                                    'reference_no' => "inv-$invoiceId",
                                    'type' => 'deposit',
                                    'deposit' => $request->card,
                                    'date' => $request->date,
                                    'comment' => "Cash receive for Invoice No ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }

                            if (!empty($request->bank) && $request->bank > 0) {
                                $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                                DB::table('bank_ledgers')->insert(array(
                                    'transaction_id' => $bankTxId,
                                    'reference_no' => 'inv-' . $invoiceId,
                                    'type' => 'deposit',
                                    'deposit' => $request->bank,
                                    'bank_id' => $request->bankId,
                                    'date' => $request->date,
                                    'company_id' => $companyId,
                                    'comment' => "Paid for Invoice id ($invoiceId)"
                                ));
                                $txGenerator->setNextInvoiceNo();
                            }
                        }
                        return $invoice;
                    });
                    $status = true;
                    $message = 'Invoice saved';
                    return response()->json(compact('status', 'message', 'invoice'));
                } catch (Exception $e) {
                    $status = false;
                    $errors = $e;
                    return response()->json(compact('status', 'errors'));
                }
            } else {
                $status = false;
                $error = 'Please add at least one product';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updateInvoice(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'invoice_id' => 'required',
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
            if (!empty($request->customer_id)) {
                $customerId = $request->customer_id;
            } else {
                $customerId = DB::table('customers')->where('name', 'Walking Customer')->where('company_id', $companyId)->first()->id;
            }
            if (count($products) > 0) {
                $txGenerator = new InvoiceNumberGeneratorService();
                $invoiceId = $request->invoice_id;
                $total = 0;
                $profit = 0;
                DB::table('invoice_items')->where('invoice_id', $invoiceId)->where('company_id', $companyId)->delete();
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
                            'company_id' => $companyId,
                            'quantity' => $quantity,
                            'date' => $request->date,
                            'total' => $quantity * $price,
                        ]);
                        $purchasePrice = DB::table('average_purchase_prices')->where('product_id', $productID)->where('company_id', $companyId)->first();
                        $profit += ($quantity * $price) - ($quantity * $purchasePrice->price);
                    }
                }
                $paid = $request->cash + $request->bkash + $request->nagad + $request->card;
                DB::table('invoices')->where('invoice_id', $invoiceId)->where('company_id', $companyId)->update(
                    [
                        'customer_id' => $customerId,
                        'comment' => $request->comment,
                        'date' => $request->date,
                        'discount' => $request->discount,
                        'discountAmount' => $request->discountAmount,
                        'discountType' => $request->discountType,
                        'total' => $total,
                        'paid_amount' => $paid,
                        'company_id' => $companyId,
                        'profit' => $profit - $request->discountAmount,
                    ]
                );
                DB::table('customer_ledgers')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();

                $customerDueTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('customer_transaction');
                $grandTotal = $total - $request->discountAmount;
                DB::table('customer_ledgers')->insert(array(
                    'customer_id' => $customerId,
                    'transaction_id' => $customerDueTxId,
                    'reference_no' => "inv-$invoiceId",
                    'type' => 'due',
                    'company_id' => $companyId,
                    'due' => $grandTotal,
                    'deposit' => 0,
                    'date' => $request->date,
                    'comment' => "Due for Invoice ID ($invoiceId)"
                ));
                $txGenerator->setNextInvoiceNo();

                if ($paid > $grandTotal) {
                    $paid = $grandTotal;
                }

                if ($paid > 0) {
                    $customerPaidTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('transaction');
                    DB::table('customer_ledgers')->insert(array(
                        'customer_id' => $customerId,
                        'reference_no' => "inv-$invoiceId",
                        'transaction_id' => $customerPaidTxId,
                        'type' => 'deposit',
                        'company_id' => $companyId,
                        'due' => 0,
                        'deposit' => $paid,
                        'date' => $request->date,
                        'comment' => "Deposit for Invoice ID ($invoiceId)"
                    ));
                    $txGenerator->setNextInvoiceNo();

                    DB::table('cash_books')->where('reference_no', "inv-$invoiceId")->delete();

                    if (!empty($request->cash) && $request->cash > 0) {
                        $onlinePayments = $request->bkash + $request->nagad + $request->card;
                        $dueAfterOnlinePayment = $grandTotal - $onlinePayments;
                        $cashPaid = $request->cash;
                        $change = $grandTotal - ($dueAfterOnlinePayment - $cashPaid);
                        $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('cash_transaction');
                        if ($change > 0) {
                            DB::table('cash_books')->insert(array(
                                'transaction_id' => $cashTxId,
                                'company_id' => $companyId,
                                'reference_no' => "inv-$invoiceId",
                                'type' => 'receive',
                                'receive' => $dueAfterOnlinePayment,
                                'date' => $request->date,
                                'comment' => "Cash receive for Invoice No ($invoiceId)"
                            ));
                        } elseif ($change < 0) {
                            DB::table('cash_books')->insert(array(
                                'transaction_id' => $cashTxId,
                                'company_id' => $companyId,
                                'reference_no' => "inv-$invoiceId",
                                'type' => 'receive',
                                'receive' => $cashPaid,
                                'date' => $request->date,
                                'comment' => "Cash receive for Invoice No ($invoiceId)"
                            ));
                        } else {
                            DB::table('cash_books')->insert(array(
                                'transaction_id' => $cashTxId,
                                'company_id' => $companyId,
                                'reference_no' => "inv-$invoiceId",
                                'type' => 'receive',
                                'receive' => $cashPaid,
                                'date' => $request->date,
                                'comment' => "Cash receive for Invoice No ($invoiceId)"
                            ));
                        }
                        $txGenerator->setNextInvoiceNo();
                    }
                    DB::table('bkash_transactions')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();

                    if (!empty($request->bkash) && $request->bkash > 0) {
                        $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('bkash_transaction');
                        DB::table('bkash_transactions')->insert(array(
                            'transaction_id' => $cashTxId,
                            'reference_no' => "inv-$invoiceId",
                            'type' => 'deposit',
                            'company_id' => $companyId,
                            'deposit' => $request->bkash,
                            'date' => $request->date,
                            'comment' => "Cash receive for Invoice No ($invoiceId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }
                    DB::table('nagad_transactions')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();
                    if (!empty($request->nagad) && $request->nagad > 0) {
                        $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('nagad_transaction');
                        DB::table('nagad_transactions')->insert(array(
                            'transaction_id' => $cashTxId,
                            'reference_no' => "inv-$invoiceId",
                            'type' => 'deposit',
                            'company_id' => $companyId,
                            'deposit' => $request->nagad,
                            'date' => $request->date,
                            'comment' => "Cash receive for Invoice No ($invoiceId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }
                    DB::table('card_transactions')->where('reference_no', "inv-$invoiceId")->where('company_id', $companyId)->delete();
                    if (!empty($request->card) && $request->card > 0) {
                        $cashTxId = $txGenerator->prefix('')->setCompanyId('1')->startAt(10000)->getInvoiceNumber('card_transaction');
                        DB::table('card_transactions')->insert(array(
                            'transaction_id' => $cashTxId,
                            'reference_no' => "inv-$invoiceId",
                            'type' => 'deposit',
                            'company_id' => $companyId,
                            'deposit' => $request->card,
                            'date' => $request->date,
                            'comment' => "Cash receive for Invoice No ($invoiceId)"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }
                }
                $status = true;
                $message = 'Invoice updated';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Please add at least one product';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteInvoice(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                DB::table('invoices')->where('invoice_id', $id)->where('company_id', $companyId)->delete();
                DB::table('invoice_items')->where('invoice_id', $id)->where('company_id', $companyId)->delete();
                DB::table('customer_ledgers')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('card_transactions')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('nagad_transactions')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('bkash_transactions')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                DB::table('cash_books')->where('reference_no', "inv-$id")->where('company_id', $companyId)->delete();
                $status = true;
                $message = 'Invoice deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Invoice not found';
                return response()->json(compact('status', 'error'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getProductsWithStock(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            if (empty($name)) {
                $products = DB::table('products')
                    ->select('products.name AS name products.product_id AS product_id products.price AS price SUM(purchase_items.quantity) as purchase invoice_items.quantity as sell')
                    ->where('products.company_id', $companyId)
                    ->leftJoin('invoice_items', 'invoice_items.product_id', '=', 'products.product_id')
                    ->leftJoin('purchase_items', 'purchase_items.product_id', '=', 'products.product_id')
                    ->groupBy('products.product_id')
                    ->get();
                $status = true;
                return response()->json(compact('status', 'products'));
            } else {
                $products = DB::table('products')
                    ->select('products.name AS name', 'products.product_id AS product_id', 'products.price AS price', DB::raw('SUM(purchase_items.quantity) as purchase'), DB::raw('SUM(invoice_items.quantity) as sell'))
                    ->where('products.company_id', $companyId)
                    ->where('products.name', 'like', '%' . $name . '%')
                    ->leftJoin('invoice_items', 'invoice_items.product_id', '=', 'products.product_id')
                    ->leftJoin('purchase_items', 'purchase_items.product_id', '=', 'products.product_id')
                    ->groupBy('products.product_id')
                    ->get();
                $status = true;
                return response()->json(compact('status', 'products'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Invoice End
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Report Start
    |--------------------------------------------------------------------------
    */
    public function getStock(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            if (empty($name)) {
                $products = DB::table('products')
                    ->select('products.name AS product_name', 'products.product_id AS product_id', DB::raw('SUM(purchase_items.quantity) as totalPurchaseQuantity'), DB::raw('SUM(invoice_items.quantity) as totalSaleQuantity'))
                    ->where('products.company_id', $companyId)
                    ->leftJoin('invoice_items', 'invoice_items.product_id', '=', 'products.product_id')
                    ->leftJoin('purchase_items', 'purchase_items.product_id', '=', 'products.product_id')
                    ->groupBy('products.product_id', 'products.name')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'products'));
            } else {
                $products = DB::table('products')
                    ->select('products.name AS product_name', 'products.product_id AS product_id', DB::raw('SUM(purchase_items.quantity) as totalPurchaseQuantity'), DB::raw('SUM(invoice_items.quantity) as totalSaleQuantity'))
                    ->where('products.company_id', $companyId)
                    ->where('products.name', 'like', '%' . $name . '%')
                    ->leftJoin('invoice_items', 'invoice_items.product_id', '=', 'products.product_id')
                    ->leftJoin('purchase_items', 'purchase_items.product_id', '=', 'products.product_id')
                    ->groupBy('products.product_id', 'products.name')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'products'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Report End
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Bank Start
    |--------------------------------------------------------------------------
    */
    public function getBanks(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $banks = DB::table('banks')
                    ->select('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type', DB::raw('SUM(withdraw) as withdraw'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(deposit - withdraw) as balance'))
                    ->leftJoin('bank_ledgers', 'bank_ledgers.bank_id', '=', 'banks.id')
                    ->where('banks.company_id', $companyId)
                    ->groupBy('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'banks'));
            } elseif (!empty($all)) {
                $banks = DB::table('banks')
                    ->select('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type', DB::raw('SUM(withdraw) as withdraw'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(deposit - withdraw) as balance'))
                    ->leftJoin('bank_ledgers', 'bank_ledgers.bank_id', '=', 'banks.id')
                    ->where('banks.company_id', $companyId)
                    ->groupBy('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type')
                    ->get();
                $status = true;
                return response()->json(compact('status', 'banks'));
            } else {
                $banks = DB::table('banks')
                    ->select('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type', DB::raw('SUM(withdraw) as withdraw'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(deposit - withdraw) as balance'))
                    ->leftJoin('bank_ledgers', 'bank_ledgers.bank_id', '=', 'banks.id')
                    ->where('banks.company_id', $companyId)
                    ->where('banks.name', 'like', '%' . $name . '%')
                    ->orWhere('banks.account_name', 'like', '%' . $name . '%')
                    ->orWhere('banks.account_no', 'like', '%' . $name . '%')
                    ->groupBy('banks.id', 'banks.name', 'banks.account_name', 'banks.account_no', 'banks.branch', 'banks.bank_type')
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'banks'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function getBank($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $bank = DB::table('banks')->where('id', $id)->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'bank'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function storeBank(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'account_name' => 'required',
                    'bank_type' => 'required',
                    'account_no' => 'required',
                    'branch' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            try {
                DB::transaction(function () use ($companyId, $request) {
                    $bankId = DB::table('banks')->insertGetId([
                        'name' => $request->name,
                        'account_name' => $request->account_name,
                        'account_no' => $request->account_no,
                        'branch' => $request->branch,
                        'company_id' => $companyId,
                        'bank_type' => $request->bank_type
                    ]);
                    if (!empty($request->balance)) {
                        $txIdGenerator = new InvoiceNumberGeneratorService();
                        $txId = $txIdGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                        if ($request->bank_type == 'saving') {
                            DB::table('bank_ledgers')->insert(array(
                                'bank_id' => $bankId,
                                'transaction_id' => $txId,
                                'comment' => "Previous balance",
                                'type' => 'deposit',
                                'withdraw' => 0,
                                'deposit' => $request->balance,
                                'company_id' => $companyId,
                                'date' => date('Y-m-d')
                            ));
                        } else {
                            DB::table('bank_ledgers')->insert(array(
                                'bank_id' => $bankId,
                                'transaction_id' => $txId,
                                'comment' => "Previous balance",
                                'type' => 'withdraw',
                                'withdraw' => $request->balance,
                                'deposit' => 0,
                                'company_id' => $companyId,
                                'date' => date('Y-m-d')
                            ));
                        }
                        $txIdGenerator->setNextInvoiceNo();
                    }
                });
            } catch (Exception $e) {
                $status = false;
                $errors = 'Something went wrong';
                return response()->json(compact('status', 'errors'));
            }
            $status = true;
            $message = 'Successfully saved';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updateBank(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'account_name' => 'required',
                    'bank_type' => 'required',
                    'account_no' => 'required',
                    'branch' => 'required',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            DB::table('banks')->where('id', $request->id)->where('company_id', $companyId)->update(
                [
                    'name' => $request->name,
                    'account_name' => $request->account_name,
                    'account_no' => $request->account_no,
                    'branch' => $request->branch,
                    'bank_type' => $request->bank_type
                ]
            );
            $status = true;
            $message = 'Updated';
            return response()->json(compact('status', 'message'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function deleteBank(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $id = $request->id;
            if (!empty($id)) {
                $check = DB::table('bank_ledgers')
                    ->where('company_id', $companyId)
                    ->where('bank_id', $id)
                    ->whereNotNull('reference_no')
                    ->get();
                if (count($check) > 0) {
                    $status = false;
                    $errors = 'You can not delete this bank as it already used for calculation';
                    return response()->json(compact('status', 'errors'));
                }
                try {
                    DB::transaction(function () use ($companyId, $id) {
                        DB::table('banks')->where('id', $id)->where('company_id', $companyId)->delete();
                        DB::table('bank_ledgers')->where('company_id', $companyId)->where('bank_id', $id)->delete();
                    });
                } catch (Exception $e) {
                    $status = false;
                    $errors = 'Something went wrong';
                    return response()->json(compact('status', 'errors'));
                }

                $status = true;
                $message = 'Customer deleted';
                return response()->json(compact('status', 'message'));
            } else {
                $status = false;
                $error = 'Customer not found';
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
    | Bank End
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | Company Start
    |--------------------------------------------------------------------------
    */

    public function getCompany()
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $company = DB::table('companies')->where('company_id', $companyId)->first();
            $status = true;
            return response()->json(compact('status', 'company'));
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function updateCompany(Request $request)
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
            if (!empty($request->logo)) {
                DB::table('companies')->where('company_id', $companyId)->update(
                    [
                        'name' => $request->name,
                        'address' => $request->address,
                        'email' => $request->email,
                        'mobile' => $request->mobile,
                        'logo' => $request->logo,
                        'vat_number' => $request->vat_number,
                        'mushok_number' => $request->mushok_number,
                    ]
                );
                $status = true;
                $message = 'Updated';
                return response()->json(compact('status', 'message'));
            } else {
                DB::table('companies')->where('company_id', $companyId)->update(
                    [
                        'name' => $request->name,
                        'address' => $request->address,
                        'email' => $request->email,
                        'mobile' => $request->mobile,
                        'vat_number' => $request->vat_number,
                        'mushok_number' => $request->mushok_number,
                    ]
                );
                $status = true;
                $message = 'Updated';
                return response()->json(compact('status', 'message'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Company End
    |--------------------------------------------------------------------------
    */
}
