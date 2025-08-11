<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'status' => $this->status,
            'status_label' => $this->status, // Hoặc một trường khác nếu có logic ánh xạ
            'total_price' => number_format($this->total_price, 2, '.', ''),
            'shipping_fee' => number_format($this->shipping_fee, 2, '.', ''),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'created_at_human' => $this->created_at->diffForHumans(),
            'updated_at' => $this->updated_at,
            'address' => $this->whenLoaded('orderAddress', function () {
                return [
                    'recipient_name' => $this->orderAddress->recipient_name,
                    'phone_number' => $this->orderAddress->phone_number,
                    'address_line' => $this->orderAddress->address_line,
                    'ward' => $this->orderAddress->ward,
                    'district' => $this->orderAddress->district,
                    'province' => $this->orderAddress->province,
                ];
            }),
            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'return_request' => $this->whenLoaded('returnRequest', function () {
                return [
                    'id' => $this->returnRequest->id,
                    'reason' => $this->returnRequest->reason,
                    'notes' => $this->returnRequest->notes,
                    'status' => $this->returnRequest->status,
                    'processed_by' => $this->returnRequest->processed_by,
                    'processor_name' => $this->returnRequest->processor->name ?? 'N/A',
                    'created_at' => $this->returnRequest->created_at,
                    'updated_at' => $this->returnRequest->updated_at,
                ];
            }),
        ];
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
}
