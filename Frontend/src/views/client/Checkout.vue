<template>
  <div class="container mx-auto p-4 bg-gray-50 max-w-[1200px]">
    <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
      <div class="flex items-center text-red-500 mb-2">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 0110.038 0l1.929 1.929a1 1 0 11-1.414 1.414L13.5 5.5l-1.929 1.929a1 1 0 11-1.414-1.414l1.929-1.929a7 7 0 010-10.038zM10 18a8 8 0 100-16 8 8 0 000 16z" clip-rule="evenodd"></path>
        </svg>
        <h2 class="font-semibold text-lg">Địa Chỉ Nhận Hàng</h2>
      </div>
      <div class="text-gray-700">
        <span class="font-medium">Đỗ Xuân Trường (+84) 374729420</span>
        Ngõ 36 Thiết Ứng, Xã Vân Hà, Huyện Đông Anh, Hà Nội
        <button class="ml-2 text-blue-500 hover:underline">Mặc Định</button>
        <button class="ml-2 text-blue-500 hover:underline">Thay Đổi</button>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
      <div class="grid grid-cols-5 text-gray-500 pb-2 border-b border-gray-200">
        <div class="col-span-2">Sản phẩm</div>
        <div class="text-center">Đơn giá</div>
        <div class="text-center">Số lượng</div>
        <div class="text-right">Thành tiền</div>
      </div>

      <div class="py-4 border-b border-gray-200 last:border-b-0">
        <div class="flex items-center text-sm mb-2">
          <button class="ml-auto flex items-center text-blue-500 hover:underline">
          </button>
        </div>
        <div class="grid grid-cols-5 items-center">
          <div class="col-span-2 flex items-center">
            <img src="" alt="Miếng tản nhiệt" class="w-16 h-16 mr-3 border rounded">
            <div>
              <p class="text-gray-800">Miếng tản nhiệt cao cấp Thermal Pad SNOWM...</p>
              <p class="text-gray-500 text-sm">Loại: SNOWMAN 13.8 WI...</p>
            </div>
          </div>
          <div class="text-center text-gray-700">99,000₫</div>
          <div class="text-center text-gray-700">1</div>
          <div class="text-right text-red-500 font-semibold">99,000₫</div>
        </div>
      </div>
      </div>

    <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
      <div class="grid grid-cols-4 items-center">
        <div class="col-span-1 text-gray-500">Phương thức vận chuyển:</div>
        <div class="col-span-2">
          <span class="font-medium">Nhanh</span>
          <button class="ml-2 text-blue-500 hover:underline">Thay Đổi</button>
          <p class="text-xs text-gray-500 mt-1">Đảm bảo nhận hàng từ 21 Tháng 6 - 23 Tháng 6</p>
          <p class="text-xs text-gray-500 mt-1">
            <span class="text-red-500">Voucher trị giá ₫15.000</span> nếu đơn hàng được giao đến bạn sau ngày 23 Tháng 6 2025
            <span class="ml-1 cursor-pointer text-blue-400" title="Chi tiết về voucher">
              <svg class="w-3 h-3 inline-block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 11-2 0 1 1 0 012 0zm-2 5a1 1 0 00-1 1v2a1 1 0 102 0v-2a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
            </span>
          </p>
        </div>
        <div class="col-span-1 text-right text-gray-700">₫22.200</div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm">
      <div class="flex items-center mb-4">
        <span class="text-gray-500 mr-4">Lời nhắn:</span>
        <input type="text" placeholder="Lưu ý cho chúng tôi" class="flex-grow border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
      </div>
      <div class="flex items-center mb-4">
        <button class="flex items-center text-blue-500 hover:underline text-sm">
          Hoặc chọn Hóa Tốc để
          <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM8.707 9.293a1 1 0 00-1.414 1.414L10 13.414l3.707-3.707a1 1 0 00-1.414-1.414L10 10.586l-1.293-1.293z"></path></svg>
          <span class="font-semibold ml-1">Cam kết nhận hàng trong Hôm nay</span>
        </button>
      </div>

      <div class="flex items-center mb-4 text-sm text-gray-500">
        Được đồng kiểm.
        <span class="ml-1 cursor-pointer text-blue-400" title="Chi tiết về đồng kiểm">
          <svg class="w-3 h-3 inline-block" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 11-2 0 1 1 0 012 0zm-2 5a1 1 0 00-1 1v2a1 1 0 102 0v-2a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
        </span>
      </div>

      <div class="text-right text-lg font-bold">
        Tổng số tiền (1 sản phẩm): <span class="text-red-500">₫121.200</span>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm mb-4 mt-4">
      <div class="flex justify-between items-center mb-4">
        <div class="flex items-center text-red-500">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M5.05 4.05a7 7 0 0110.038 0l1.929 1.929a1 1 0 11-1.414 1.414L13.5 5.5l-1.929 1.929a1 1 0 11-1.414-1.414l1.929-1.929a7 7 0 010-10.038zM10 18a8 8 0 100-16 8 8 0 000 16z" clip-rule="evenodd"></path></svg>
          <span class="font-semibold">Voucher</span>
        </div>
        <button class="text-blue-500 hover:underline">Chọn Voucher</button>
      </div>

      <div class="flex justify-between items-center border-t border-gray-200 pt-4">
        <div class="flex items-center">
          <span class="text-orange-500 text-xl mr-2">Floréa Xu</span> <span class="text-gray-700">Xu <span class="text-gray-500 text-sm">Dùng 2600 Xu</span></span>
        </div>
        <div class="flex items-center">
          <span class="text-gray-500 mr-2">[-₫2.600]</span>
          <input type="checkbox" class="form-checkbox h-5 w-5 text-red-600 rounded" />
        </div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm mt-4">
      <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-200">
        <h3 class="font-semibold text-lg text-gray-800">Phương thức thanh toán</h3>
        <div class="flex items-center text-gray-700">
          <span class="mr-2">Thanh toán khi nhận hàng</span>
          <button class="text-blue-500 hover:underline">THAY ĐỔI</button>
        </div>
      </div>

      <div class="text-right text-gray-700 mt-4">
        <div class="flex justify-end items-center mb-2">
          <span class="mr-4 text-gray-500">Tổng tiền hàng</span>
          <span class="w-24">₫99.000</span>
        </div>
        <div class="flex justify-end items-center mb-2">
          <span class="mr-4 text-gray-500">Tổng tiền phí vận chuyển</span>
          <span class="w-24">₫22.200</span>
        </div>
        <div class="flex justify-end items-center text-lg font-bold mt-4">
          <span class="mr-4">Tổng thanh toán</span>
          <span class="text-red-500 w-24">₫121.200</span>
        </div>
      </div>

      <div class="mt-8 pt-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center">
        <p class="text-sm text-gray-500 mb-4 sm:mb-0 sm:mr-4">
          Nhấn "Đặt hàng" đồng nghĩa với việc bạn đồng ý tuân theo
          <a href="#" class="text-blue-500 hover:underline">Điều khoản Floréa</a>
        </p>
        <button class="bg-red-500 text-white px-8 py-3 rounded-md font-semibold hover:bg-red-600 transition duration-200">
          Đặt hàng
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// No specific script logic needed for just the UI representation.
// Data for products, addresses, etc., would typically come from props or a store.
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* No custom CSS needed if using only Tailwind utility classes */
</style>