<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    
   public $timestamps = false;

    protected $fillable = ['code', 'name', 'province_code'];

    public function wards()
    {
        return $this->hasMany(Ward::class, 'district_code', 'code');
    }
}
