<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderReturnResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'total_price' => $this->total_price,
            'shipping_fee' => $this->shipping_fee,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'orderAddress' => $this->whenLoaded('orderAddress'),
            'orderItems' => $this->whenLoaded('orderItems'),
            'payments' => $this->whenLoaded('payments'),
            'returnRequest' => $this->whenLoaded('returnRequest', function () {
                return [
                    'id' => $this->returnRequest->id,
                    'reason' => $this->returnRequest->reason,
                    'notes' => $this->returnRequest->notes,
                    'status' => $this->returnRequest->status,
                    'created_at' => $this->returnRequest->created_at,
                    'processed_by' => $this->returnRequest->processed_by,
                    // Trả về tên người duyệt nếu mối quan hệ 'processor' đã được tải
                    'processor_name' => $this->returnRequest->processor->name ?? 'N/A',
                ];
            }),
        ];
    }
}
