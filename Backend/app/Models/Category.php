<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

    // Cho phép gán hàng loạt
    protected $fillable = [
        'name',
        'slug',
    ];

    // Nếu cần xử lý các trường thời gian (bao gồm deleted_at)
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    // Sử dụng slug làm khóa chính trong route model binding
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Quan hệ 1-n với sản phẩm
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
