<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'total_price' => (float) $this->total_price,
            'status'      => $this->status,
            'created_at'  => $this->created_at?->toDateTimeString(),
            'items'       => $this->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'name'       => $item->product->name,
                    'price'      => (float) $item->price,
                    'quantity'   => $item->quantity,
                    'subtotal'   => $item->quantity * $item->price,
                ];
            }),
        ];
    }
}
