<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Unit;
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
        if (empty($name)) {
            $categories = Category::select('id', 'name')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'categories'));
        } else {
            $categories = Category::select('id', 'name')->where('name', 'like', '%' . $name . '%')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'categories'));
        }
    }

    public function getCategory($id)
    {
        $category = Category::where('id', $id)->first();
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
        $categoty = Category::create(['name' => $request->name]);
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
        $category = Category::where('id', $request->id)->first();
        $category->name = $request->name;
        $category->save();
        $status = true;
        $message = 'Updated';
        return response()->json(compact('status', 'message'));
    }

    public function deleteCategory(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $deleted = Category::where('id', $id)->delete();
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
        if (empty($name)) {
            $units = Unit::select('id', 'name')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'units'));
        } else {
            $units = Unit::select('id', 'name')->where('name', 'like', '%' . $name . '%')->paginate(50);
            $status = true;
            return response()->json(compact('status', 'units'));
        }
    }

    public function getUnit($id)
    {
        $unit = Unit::where('id', $id)->first();
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
        $unit = Unit::create(['name' => $request->name]);
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
        $unit = Unit::where('id', $request->id)->first();
        $unit->name = $request->name;
        $unit->save();
        $status = true;
        $message = 'Updated';
        return response()->json(compact('status', 'message'));
    }

    public function deleteUnit(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $deleted = Unit::where('id', $id)->delete();
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
                        ->select('customers.id', 'customers.name', 'customers.mobile' , 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                        ->join('customer_ledgers', 'customer_ledgers.customer_id', '=' , 'customers.id')
                        ->groupBy('customers.id', 'customers.name', 'customers.mobile' , 'customers.address')
                        ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'customers'));
        } else {
            $customers = DB::table('customers')
                        ->select('customers.id', 'customers.name', 'customers.mobile' , 'customers.address', DB::raw('SUM(due) as due'), DB::raw('SUM(deposit) as deposit'), DB::raw('SUM(due - deposit) as balance'))
                        ->where('customers.name', 'like', '%' . $name . '%')
                        ->orWhere('customers.mobile', 'like', '%' . $name . '%')
                        ->orWhere('customers.address', 'like', '%' . $name . '%')
                        ->groupBy('customers.id', 'customers.name', 'customers.mobile' , 'customers.address')
                        ->paginate(50);
            $status = true;
            return response()->json(compact('status', 'customers'));
        }
    }

    public function getCustomer($id)
    {
        $customer = Customer::where('id', $id)->first();
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
        $customerId = Customer::insertGetId(['name' => $request->name, 'mobile' => $request->mobile, 'address' => $request->address]);

        if ($customerId) {
            if (!empty($request->due)) {
                $txIdGenerator = new InvoiceNumberGeneratorService();
                $txId = $txIdGenerator->currentYear()->prefix('')->setCompanyId(1)->startAt(1)->getInvoiceNumber('Due');
                CustomerLedger::create(array(
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
        $unit = Unit::where('id', $request->id)->first();
        $unit->name = $request->name;
        $unit->save();
        $status = true;
        $message = 'Updated';
        return response()->json(compact('status', 'message'));
    }

    public function deleteCustomer(Request $request)
    {
        $id = $request->id;
        if (!empty($id)) {
            $deleted = Unit::where('id', $id)->delete();
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
}
