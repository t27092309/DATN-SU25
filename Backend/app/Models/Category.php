<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Đặt tên bảng nếu không tuân theo quy ước Laravel
    protected $table = 'categories';

    // Các cột mà bạn có thể mass assign
    protected $fillable = [
        'name',
        'slug',
    ];

    // Các cột không thể mass assign
    protected $guarded = [];

    // Nếu cần xử lý các trường thời gian
    protected $dates = ['created_at', 'updated_at'];

    // Nếu bạn muốn sử dụng route model binding với slug
    public function getRouteKeyName()
    {
        return 'slug'; // Dùng slug thay vì id làm khóa route
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
