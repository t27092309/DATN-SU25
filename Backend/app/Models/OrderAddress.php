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
    
    public function order(){
        
        return $this -> belongsTo(Order::class);
    }
    

}
