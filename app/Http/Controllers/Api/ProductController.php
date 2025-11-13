<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        $query = Product::query();

        
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

     
        $sortBy = $request->query('sort', 'id');
        $direction = $request->query('direction', 'desc');
        $query->orderBy($sortBy, $direction);

     
        $perPage = (int) $request->query('per_page', 10);
        $products = $query->paginate($perPage);

        return response()->json([
            'status'  => true,
            'message' => 'Product list retrieved successfully',
            'data'    => ProductResource::collection($products),
            'meta'    => [
                'current_page' => $products->currentPage(),
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
            ],
        ]);
    }

  
    public function store(ProductStoreRequest $request)
    {
        $product = Product::create($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Product created successfully',
            'data'    => new ProductResource($product),
        ], Response::HTTP_CREATED);
    }

   
    public function show(Product $product)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Product details',
            'data'    => new ProductResource($product),
        ]);
    }

   
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Product updated successfully',
            'data'    => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Product deleted successfully',
            'data'    => (object)[],
        ]);
    }
}
