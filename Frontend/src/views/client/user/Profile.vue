<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Hồ Sơ Của Tôi</h1>
    <p class="text-gray-500 mb-6">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>

    <div class="flex flex-col lg:flex-row">
      <div class="lg:w-2/3 lg:pr-8">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Tên đăng nhập</label>
          <input type="text" value="127092309" class="flex-1 border-b border-gray-300 py-2 focus:outline-none" readonly />
        </div>
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Tên</label>
          <input type="text" value="Đỗ Xuân Trường" class="flex-1 border-b border-gray-300 py-2 focus:outline-none" />
        </div>
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Email</label>
          <span class="flex-1 py-2 text-blue-500 cursor-pointer hover:underline">Thêm</span>
        </div>
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Số điện thoại</label>
          <input type="text" value="********20" class="flex-1 border-b border-gray-300 py-2 focus:outline-none" readonly />
          <span class="ml-0 sm:ml-4 py-2 text-blue-500 cursor-pointer hover:underline mt-1 sm:mt-0">Thay Đổi</span>
        </div>
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Giới tính</label>
          <div class="flex items-center space-x-4 flex-wrap">
            <label class="inline-flex items-center">
              <input type="radio" name="gender" value="male" class="form-radio text-red-500" checked />
              <span class="ml-2">Nam</span>
            </label>
            <label class="inline-flex items-center">
              <input type="radio" name="gender" value="female" class="form-radio text-red-500" />
              <span class="ml-2">Nữ</span>
            </label>
            <label class="inline-flex items-center">
              <input type="radio" name="gender" value="other" class="form-radio text-red-500" />
              <span class="ml-2">Khác</span>
            </label>
          </div>
        </div>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Ngày sinh</label>
          <input type="text" value="**/06/1994" class="flex-1 border-b border-gray-300 py-2 focus:outline-none" />
          <span class="ml-0 sm:ml-4 py-2 text-blue-500 cursor-pointer hover:underline mt-1 sm:mt-0">Thay Đổi</span>
        </div>

        <button class="bg-red-500 text-white px-8 py-3 rounded-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 w-full sm:w-auto">
          Lưu
        </button>
      </div>

      <div class="lg:w-1/3 flex flex-col items-center justify-center lg:border-l lg:border-gray-200 lg:pl-8 mt-8 lg:mt-0 pt-8 lg:pt-0 border-t lg:border-t-0 border-gray-200">
        <div class="w-32 h-32 rounded-full overflow-hidden mb-4 border border-gray-300 flex items-center justify-center">
          <img src="https://via.placeholder.com/128" alt="Profile Avatar" class="w-full h-full object-cover" />
        </div>
        <button class="border border-gray-300 text-gray-700 px-6 py-2 rounded-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50">
          Chọn Ảnh
        </button>
        <p class="text-sm text-gray-500 mt-2 text-center">
          Dung lượng file tối đa 1 MB <br /> Định dạng JPEG, PNG
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
// Script logic for profile details (e.g., data fetching, form handling) would go here
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Scoped styles specific to this component can go here */
</style>