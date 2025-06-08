<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function manageUsers()
{
    return response()->json(['message' => 'Quản trị viên đang quản lý người dùng']);
}

public function manageProducts()
{
    return response()->json(['message' => 'Quản trị viên đang quản lý sản phẩm']);
}
}
