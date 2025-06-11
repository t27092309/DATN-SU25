<template>
  <div class="user-address">
    <h2 class="text-xl font-semibold mb-6">Địa chỉ của tôi</h2>

    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-medium">Địa chỉ</h3>
      <button class="px-4 py-2 bg-red-500 text-white rounded-md flex items-center hover:bg-red-600">
        <i class="fas fa-plus mr-2"></i> Thêm địa chỉ mới
      </button>
    </div>

    <div class="space-y-4">
      <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
        <div class="flex justify-between items-start mb-2">
          <div>
            <p class="font-semibold text-gray-800">Đỗ Xuân Trường | <span class="text-gray-600">(+84) 374 729 420</span></p>
            <p class="text-gray-700">Ngõ 36 Thiết Ưng</p>
            <p class="text-gray-700">Xã Vân Hà, Huyện Đông Anh, Hà Nội</p>
          </div>
          <div class="flex space-x-2 text-blue-500 text-sm">
            <button class="hover:underline">Cập nhật</button>
            <button class="hover:underline">Xóa</button>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs font-semibold rounded">Mặc định</span>
          <span class="px-2 py-0.5 border border-red-500 text-red-500 text-xs font-semibold rounded">Địa chỉ trả hàng</span>
          <button class="px-3 py-1 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50">Thiết lập mặc định</button>
        </div>
      </div>

      <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
        <div class="flex justify-between items-start mb-2">
          <div>
            <p class="font-semibold text-gray-800">Đỗ Xuân Trường | <span class="text-gray-600">(+84) 865 248 426</span></p>
            <p class="text-gray-700">Bưu Điện xã Vân Hà</p>
            <p class="text-gray-700">Xã Vân Hà, Huyện Đông Anh, Hà Nội</p>
          </div>
          <div class="flex space-x-2 text-blue-500 text-sm">
            <button class="hover:underline">Cập nhật</button>
            <button class="hover:underline">Xóa</button>
          </div>
        </div>
        <div class="flex items-center space-x-2 mb-2">
          <span class="px-2 py-0.5 border border-gray-300 text-gray-700 text-xs font-semibold rounded">Địa chỉ lấy hàng</span>
          <button class="px-3 py-1 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50">Thiết lập mặc định</button>
        </div>
        <p class="text-orange-600 text-sm italic">
          Một vài thông tin đã cũ, vui lòng giúp chúng tôi cập nhật địa chỉ này.
        </p>
      </div>

    </div>
  </div>
</template>

<script setup>
// Trong một ứng dụng thực tế, bạn sẽ có các props để nhận dữ liệu địa chỉ
// và các hàm để xử lý sự kiện (thêm, cập nhật, xóa, thiết lập mặc định).

// import { ref, onMounted } from 'vue';

// const addresses = ref([]); // Ví dụ: dữ liệu địa chỉ sẽ được lưu ở đây

// onMounted(() => {
//   // Load dữ liệu địa chỉ từ API khi component được mount
//   // Ví dụ:
//   // fetchAddresses().then(data => {
//   //   addresses.value = data;
//   // });
// });

// // Các hàm xử lý sự kiện (ví dụ)
// const addNewAddress = () => {
//   console.log('Thêm địa chỉ mới');
//   // Logic để hiển thị form thêm địa chỉ
// };

// const updateAddress = (addressId) => {
//   console.log('Cập nhật địa chỉ:', addressId);
//   // Logic để hiển thị form cập nhật địa chỉ
// };

// const deleteAddress = (addressId) => {
//   console.log('Xóa địa chỉ:', addressId);
//   // Logic gọi API xóa và cập nhật lại danh sách
// };

// const setDefaultAddress = (addressId) => {
//   console.log('Thiết lập mặc định địa chỉ:', addressId);
//   // Logic gọi API thiết lập mặc định và cập nhật lại danh sách
// };
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Các style riêng cho component này */
</style>