<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\CartAddRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController extends Controller
{

    public function index(Request $request)
    {
        $items = $request->user()
            ->cartItems()
            ->with('product')
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Cart items retrieved successfully',
            'data'    => CartResource::collection($items),
        ]);
    }


    public function add(CartAddRequest $request)
    {
        $user   = $request->user();
        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json([
                'status'  => false,
                'message' => 'Insufficient stock available',
                'data'    => (object)[],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cart) {
            $newQuantity = min($product->stock, $cart->quantity + $request->quantity);
            $cart->update(['quantity' => $newQuantity]);
        } else {
            $cart = Cart::create([
                'user_id'    => $user->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Product added to cart successfully',
            'data'    => new CartResource($cart->load('product')),
        ]);
    }


    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($cart->user_id !== $request->user()->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized action',
                'data'    => (object)[],
            ], Response::HTTP_FORBIDDEN);
        }

        if ($cart->product->stock < $request->quantity) {
            return response()->json([
                'status'  => false,
                'message' => 'Not enough stock available',
                'data'    => (object)[],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json([
            'status'  => true,
            'message' => 'Cart quantity updated',
            'data'    => new CartResource($cart->load('product')),
        ]);
    }


    public function remove(Request $request, $id)
    {
        $cart = Cart::where('user_id', $request->user()->id)->find($id);

        if (! $cart) {
            return response()->json([
                'status'  => false,
                'message' => 'Item not found in cart',
                'data'    => (object)[],
            ], Response::HTTP_NOT_FOUND);
        }

        $cart->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Item removed from cart',
            'data'    => (object)[],
        ]);
    }


    public function clear(Request $request)
    {
        $request->user()->cartItems()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Cart cleared successfully',
            'data'    => (object)[],
        ]);
    }
}
