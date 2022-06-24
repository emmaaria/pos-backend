<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $user = User::select('id','name','email','role')->where('email',$request->email)->first();
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
    | Category Routes Start
    |--------------------------------------------------------------------------
    */
    public function getCategories()
    {
        $categories = Category::all();
        $status = true;
        return response()->json(compact('status', 'categories'));
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
        $categoty = Category::create(['name'=>$request->name]);
        if ($categoty){
            $status = true;
            return response()->json(compact('status'));
        }else{
            $status = false;
            return response()->json(compact('status'));
        }
    }
    /*
    |--------------------------------------------------------------------------
    | Category Routes End
    |--------------------------------------------------------------------------
    */
}
