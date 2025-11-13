<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json([
            'status'  => true,
            'message' => 'Orders retrieved successfully',
            'data'    => OrderResource::collection($orders),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'Your cart is empty',
                'data'    => (object)[],
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();

        try {
            $total = 0;

            foreach ($cartItems as $item) {
                if ($item->product->stock < $item->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'status'  => false,
                        'message' => "Insufficient stock for {$item->product->name}",
                        'data'    => (object)[],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $total += $item->product->price * $item->quantity;
            }

            $order = Order::create([
                'user_id'     => $user->id,
                'total_price' => $total,
                'status'      => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);


                $item->product->decrement('stock', $item->quantity);
            }


            $user->cartItems()->delete();

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Order placed successfully',
                'data'    => new OrderResource($order->load('items.product')),
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to place order',
                'error'   => $e->getMessage(),
                'data'    => (object)[],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $order->update(['status' => $request->status]);

        return response()->json([
            'status'  => true,
            'message' => 'Order status updated successfully',
            'data'    => new OrderResource($order->load('items.product')),
        ]);
    }
}
