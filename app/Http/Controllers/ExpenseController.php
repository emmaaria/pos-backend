<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class ExpenseController extends Controller
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
    | Expense Category Start
    |--------------------------------------------------------------------------
    */

    public function categoryIndex(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $categories = DB::table('expense_categories')->select('id', 'name')->where('company_id', $companyId)->orderBy('id', 'desc')->paginate(50);
                $status = true;
                return response()->json(compact('status', 'categories'));
            } elseif (!empty($all)) {
                $categories = DB::table('expense_categories')->where('company_id', $companyId)->orderBy('id', 'desc')->get();
                $status = true;
                return response()->json(compact('status', 'categories'));
            } else {
                $categories = DB::table('expense_categories')->select('id', 'name')->orderBy('id', 'desc')->where('name', 'like', '%' . $name . '%')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'categories'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function singleCategory($id)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $category = DB::table('expense_categories')->where('id', $id)->where('company_id', $companyId)->first();
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
            $unit = DB::table('expense_categories')->insert(['name' => $request->name, 'company_id' => $companyId]);
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
            DB::table('expense_categories')->where('id', $request->id)->where('company_id', $companyId)->update(['name' => $request->name]);
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
                $deleted = DB::table('expense_categories')->where('id', $id)->where('company_id', $companyId)->delete();
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
    | Expense Category End
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Expense Start
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->allData;
            if (empty($name) && empty($all)) {
                $expenses = DB::table('expenses')
                            ->select('expenses.expense_id', 'expenses.note', 'expenses.amount', 'expense_categories.name as title')
                            ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.category')
                            ->where('company_id', $companyId)
                            ->orderBy('id', 'desc')
                            ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'expenses'));
            } elseif (!empty($all)) {
                $expenses = DB::table('expenses')
                    ->where('company_id', $companyId)
                    ->select('expenses.expense_id', 'expenses.note', 'expenses.amount', 'expense_categories.name as title')
                    ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.category')
                    ->orderBy('id', 'desc')
                    ->get();
                $status = true;
                return response()->json(compact('status', 'expenses'));
            } else {
                $expenses = DB::table('expenses')
                    ->select('expenses.expense_id', 'expenses.note', 'expenses.amount', 'expense_categories.name as title')
                    ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.category')
                    ->where('expenses.expense_id', 'like', '%' . $name . '%')
                    ->orWhere('expense_categories.name', 'like', '%' . $name . '%')
                    ->orderBy('id', 'desc')
                    ->where('name', 'like', '%' . $name . '%')
                    ->where('company_id', $companyId)
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'expenses'));
            }
        } else {
            $status = false;
            $errors = 'You are not authorized';
            return response()->json(compact('status', 'errors'));
        }
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $validator = Validator::make($request->all(),
                [
                    'category' => 'required',
                    'account' => 'required',
                    'amount' => 'required|min:1',
                    'date' => 'required',
                    'bankId' => 'required_if:account,==,bank',
                ]
            );
            if ($validator->fails()) {
                $status = false;
                $errors = $validator->errors();
                return response()->json(compact('status', 'errors'));
            }
            try {
                DB::transaction(function () use ($request, $companyId) {
                    $txGenerator = new InvoiceNumberGeneratorService();
                    $expenseId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('expense');
                    $txGenerator->setNextInvoiceNo();

                    DB::table('expenses')->insert([
                        'expense_id' => $expenseId,
                        'category' => $request->category,
                        'note' => $request->note,
                        'company_id' => $companyId,
                        'account' => $request->account,
                        'amount' => $request->amount,
                        'date' => $request->date,
                    ]);

                    $categoryName = DB::table('expense_categories')->select('name')->where('company_id', $companyId)->where('id', $request->category)->first();

                    if ($request->account == 'cash'){
                        $cashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('customer_transaction');
                        DB::table('cash_books')->insert(array(
                            'transaction_id' => $cashTxId,
                            'company_id' => $companyId,
                            'reference_no' => "exp-$expenseId",
                            'type' => 'payment',
                            'payment' => $request->amount,
                            'date' => $request->date,
                            'comment' => $request->note !== '' ? $request->note : "Cash deduct for $categoryName->name (Expense ID : $expenseId"
                        ));
                    }

                    if ($request->account == 'bkash') {
                        $bkashTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('bkash_transaction');
                        DB::table('bkash_transactions')->insert(array(
                            'transaction_id' => $bkashTxId,
                            'company_id' => $companyId,
                            'reference_no' => "exp-$expenseId",
                            'type' => 'withdraw',
                            'withdraw' => $request->amount,
                            'date' => $request->date,
                            'comment' => $request->note !== '' ? $request->note : "Deduct for $categoryName->name (Expense ID : $expenseId"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }

                    if ($request->account == 'nagad') {
                        $nagadTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(10000)->getInvoiceNumber('nagad_transaction');
                        DB::table('nagad_transactions')->insert(array(
                            'transaction_id' => $nagadTxId,
                            'company_id' => $companyId,
                            'reference_no' => "exp-$expenseId",
                            'type' => 'withdraw',
                            'withdraw' => $request->amount,
                            'date' => $request->date,
                            'comment' => $request->note !== '' ? $request->note : "Deduct for $categoryName->name (Expense ID : $expenseId"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }

                    if ($request->account == 'bank' && !empty($request->bankId)) {
                        $bankTxId = $txGenerator->prefix('')->setCompanyId($companyId)->startAt(1000)->getInvoiceNumber('bank_transaction');
                        DB::table('bank_ledgers')->insert(array(
                            'transaction_id' => $bankTxId,
                            'reference_no' => 'exp-' . $expenseId,
                            'type' => 'withdraw',
                            'withdraw' => $request->amount,
                            'bank_id' => $request->bankId,
                            'date' => $request->date,
                            'company_id' => $companyId,
                            'comment' => $request->note !== '' ? $request->note : "Deduct for $categoryName->name (Expense ID : $expenseId"
                        ));
                        $txGenerator->setNextInvoiceNo();
                    }
                });
                $status = true;
                $message = 'Expense saved';
                return response()->json(compact('status', 'message'));
            }catch (Exception $e){
                $status = false;
                $errors = $e;
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
    | Expense End
    |--------------------------------------------------------------------------
    */
}
