<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    use HasFactory;

    protected $table = 'order_addresses';

    protected $fillable = [
        'order_id',
        'recipient_name',
        'phone_number',
        'address_line',
        'ward',
        'district',
        'province'
    ];

    public function order()
    {

        return $this->belongsTo(Order::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id');
    }
}
