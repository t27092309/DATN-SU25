<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'payment_method' => $this->payment_method,
            'amount'         => $this->amount,
            'status'         => $this->payment_status,
            'paid_at'        => $this->paid_at?->toDateTimeString(),
            'transaction_id' => $this->transaction_id,
            'details'        => $this->payment_details,
        ];
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
}
