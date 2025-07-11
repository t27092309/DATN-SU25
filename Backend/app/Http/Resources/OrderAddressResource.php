<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderAddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'recipient_name' => $this->recipient_name,
            'phone_number'   => $this->phone_number,
            'address_line'   => $this->address_line,
            'ward'           => $this->ward,
            'district'       => $this->district,
            'province'       => $this->province,
        ];
    }
}
