<?php

namespace App\Http\Controllers\API\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAddress;

class UserAddressController extends Controller
{
    // Lấy danh sách địa chỉ của user
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->get();
        return response()->json($addresses);
    }

    // Thêm địa chỉ mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_line' => 'required|string|max:255',
            'ward' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'province' => 'required|string|max:100',
        ], [
            'recipient_name.required' => 'Tên người nhận không được để trống.',
            'recipient_name.max' => 'Tên người nhận không được vượt quá 255 ký tự.',

            'phone_number.required' => 'Số điện thoại không được để trống.',
            'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự.',

            'address_line.required' => 'Địa chỉ chi tiết không được để trống.',
            'address_line.max' => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',

            'ward.required' => 'Phường/xã không được để trống.',
            'ward.max' => 'Phường/xã không được vượt quá 100 ký tự.',

            'district.required' => 'Quận/huyện không được để trống.',
            'district.max' => 'Quận/huyện không được vượt quá 100 ký tự.',

            'province.required' => 'Tỉnh/thành phố không được để trống.',
            'province.max' => 'Tỉnh/thành phố không được vượt quá 100 ký tự.',
        ]);

        $user = $request->user();

        $address = new UserAddress($validated);
        $address->user_id = $user->id;
        $address->save();

        return response()->json(['message' => 'Thêm địa chỉ thành công', 'address' => $address]);
    }

    // Cập nhật địa chỉ
    public function update(Request $request, $id)
    {
        $address = UserAddress::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'recipient_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'address_line' => 'required|string|max:255',
            'ward' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'province' => 'required|string|max:100',
        ], [
            'recipient_name.required' => 'Tên người nhận không được để trống.',
            'recipient_name.max' => 'Tên người nhận không được vượt quá 255 ký tự.',

            'phone_number.required' => 'Số điện thoại không được để trống.',
            'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự.',

            'address_line.required' => 'Địa chỉ chi tiết không được để trống.',
            'address_line.max' => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',

            'ward.required' => 'Phường/xã không được để trống.',
            'ward.max' => 'Phường/xã không được vượt quá 100 ký tự.',

            'district.required' => 'Quận/huyện không được để trống.',
            'district.max' => 'Quận/huyện không được vượt quá 100 ký tự.',

            'province.required' => 'Tỉnh/thành phố không được để trống.',
            'province.max' => 'Tỉnh/thành phố không được vượt quá 100 ký tự.',
        ]);

        $address->update($validated);

        return response()->json(['message' => 'Cập nhật địa chỉ thành công', 'address' => $address]);
    }


    // Xoá địa chỉ
    public function destroy(Request $request, $id)
    {
        $address = UserAddress::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $address->delete();

        return response()->json(['message' => 'Đã xoá địa chỉ']);
    }

    // Đặt địa chỉ mặc định
    public function setDefault(Request $request, $id)
    {
        $user = $request->user();

        $address = UserAddress::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        // Reset tất cả về false
        UserAddress::where('user_id', $user->id)->update(['is_default' => false]);

        // Set địa chỉ này
        $address->update(['is_default' => true]);

        return response()->json(['message' => 'Đã đặt địa chỉ mặc định']);
    }
}
