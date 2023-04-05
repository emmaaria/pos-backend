<?php

namespace App\Http\Controllers;

use App\Models\AveragePurchasePrice;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Skycoder\InvoiceNumberGenerator\InvoiceNumberGeneratorService;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductController extends Controller
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
    | Product Start
    |--------------------------------------------------------------------------
    */
    public
    function getProducts(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            $all = $request->all;
            if (empty($name) && empty($all)) {
                $products = DB::table('products')->select('*')->orderBy('id', 'desc')->where('company_id', $companyId)->paginate(50);
                $status = true;
                return response()->json(compact('status', 'products', 'companyId'));
            } elseif (!empty($all)) {
                $products = DB::table('products')->select('*')->where('company_id', $companyId)->orderBy('id', 'desc')->get();
                $status = true;
                return response()->json(compact('status', 'products'));
            } else {
                $products = DB::table('products')
                    ->select('*')
                    ->where('company_id', $companyId)
                    ->where('name', 'like', '%' . $name . '%')
                    ->orderBy('id', 'desc')
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

    public
    function getProduct($id)
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

    public
    function getProductByBarcode(Request $request)
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

    public
    function storeProduct(Request $request)
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
                    $prices = $request->customerPrices;
                    if (!empty($prices) && count($prices) > 0) {
                        foreach ($prices as $price) {
                            if ($price['customerId'] !== '' && $price['price'] !== '') {
                                DB::table('customer_products')->insert([
                                    'customer_id' => $price['customerId'],
                                    'product_id' => $productId,
                                    'price' => $price['price'],
                                    'company_id' => $companyId
                                ]);
                            }
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

    public
    function updateProduct(Request $request)
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

    public
    function deleteProduct(Request $request)
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

    public
    function getProductsWithStock(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            if (empty($name)) {
                $products = DB::table('products')
                    ->selectRaw("products.name AS name, products.product_id, products.price,(select sum(quantity) from invoice_items where product_id= `products`.`product_id`) as 'sale', (select sum(quantity) from sale_return_items where product_id= `products`.`product_id`) as 'return',(select sum(quantity) from purchase_items where product_id= `products`.`product_id`) as 'purchase'")
                    ->where('products.company_id', $companyId)
                    ->get();
                $status = true;
                return response()->json(compact('status', 'products'));
            } else {
                $products = DB::table('products')
                    ->selectRaw("products.name AS name, products.product_id, products.price,(select sum(quantity) from invoice_items where product_id= `products`.`product_id`) as 'sale', (select sum(quantity) from sale_return_items where product_id= `products`.`product_id`) as 'return',(select sum(quantity) from purchase_items where product_id= `products`.`product_id`) as 'purchase'")
                    ->where('products.company_id', $companyId)
                    ->where('products.name', 'like', '%' . $name . '%')
                    ->orWhere('products.product_id', 'like', '%' . $name . '%')
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

    public function stock(Request $request)
    {
        $companyId = $this->getCompanyId();
        if ($companyId) {
            $name = $request->name;
            if (empty($name)) {
                $products = DB::table('products')
                    ->selectRaw("products.name AS name, products.product_id, products.price,(select sum(quantity) from invoice_items where product_id= `products`.`product_id`) as 'sale', (select sum(quantity) from sale_return_items where product_id= `products`.`product_id`) as 'return',(select sum(quantity) from purchase_items where product_id= `products`.`product_id`) as 'purchase'")
                    ->where('products.company_id', $companyId)
                    ->paginate(50);
                $status = true;
                return response()->json(compact('status', 'products'));
            } else {
                $products = DB::table('products')
                    ->selectRaw("products.name AS name, products.product_id, products.price,(select sum(quantity) from invoice_items where product_id= `products`.`product_id`) - (select sum(quantity) from sale_return_items where product_id= `products`.`product_id`) as 'sale',(select sum(quantity) from purchase_items where product_id= `products`.`product_id`) as 'purchase'")
                    ->where('products.company_id', $companyId)
                    ->where('products.name', 'like', '%' . $name . '%')
                    ->orWhere('products.product_id', 'like', '%' . $name . '%')
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
    | Product End
    |--------------------------------------------------------------------------
    */
}
