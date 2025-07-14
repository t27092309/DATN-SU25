<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'user'          => new UserResource($this->whenLoaded('user')),
            'status'        => $this->status,
            'status_label'  => $this->statusLabel ?? null,
            'total_price'   => $this->total_price,
            'shipping_fee'  => $this->shipping_fee,
            'notes'         => $this->notes,
            'created_at'    => $this->created_at?->toDateTimeString(),
            'created_at_human' => $this->created_at?->diffForHumans(), // Thời gian tương đối (ví dụ: "5 phút trước")
            'address'       => new OrderAddressResource($this->whenLoaded('orderAddress')),
            'items'         => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'payments'      => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
}
