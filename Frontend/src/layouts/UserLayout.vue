<template>
  <div class="flex flex-col md:flex-row max-w-7xl mx-auto min-h-screen bg-gray-100">
    <main class="flex-1 p-4 md:p-8 order-2 md:order-1">
      <div class="bg-white shadow-md rounded-lg p-6 h-full">
        <router-view></router-view>
      </div>
    </main>

    <aside class="w-full md:w-64 p-4 order-1 md:order-2 flex-shrink-0">
      <div class="flex items-center mb-6">
        <template v-if="isLoggedIn">
          <div class="w-10 h-10 rounded-full overflow-hidden mr-3">
            <img src="https://via.placeholder.com/40" alt="Profile" class="w-full h-full object-cover" />
          </div>
          <div>
            <p class="font-semibold text-gray-800">{{ userName }}</p>
            <a href="#" class="text-sm text-blue-500 hover:underline">Sửa Hồ Sơ</a>
            <button @click="logout" class="ml-2 text-sm text-red-500 hover:underline">Đăng Xuất</button>
          </div>
        </template>
        <template v-else>
          <div>
            <button @click="login"
              class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
              Đăng Nhập
            </button>
          </div>
        </template>
      </div>

      <nav>
        <ul>
          <li class="mb-2">
            <a href="#" @click.prevent="selectMenuItem('notifications')"
              :class="['flex items-center p-2 rounded',
                activeMenuItem === 'notifications' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">
              <i class="fas fa-bell w-5 mr-3"
                :class="activeMenuItem === 'notifications' ? 'text-red-500' : 'text-gray-500'"></i>
              Thông Báo
            </a>
          </li>

          <li class="mb-2">
            <a href="#" @click.prevent="toggleAccountSubMenu" :class="['flex items-center p-2 rounded',
              'text-gray-700 hover:bg-red-50 hover:text-red-600']">
              <i class="fas fa-user-circle w-5 mr-3 text-blue-500"></i>
              Tài Khoản Của Tôi
              <i
                :class="['fas ml-auto transition-transform duration-300', isAccountSubMenuOpen ? 'fa-chevron-up' : 'fa-chevron-down']"></i>
            </a>
          </li>

          <li>
            <ProfileSubMenu :isVisible="isAccountSubMenuOpen" :activeMenuItem="activeMenuItem"
              @selectMenuItem="selectMenuItem" />
          </li>

          <li class="mb-2">
            <router-link to="don-hang" @click.prevent="selectMenuItem('orders')"
              :class="['flex items-center p-2 rounded',
                activeMenuItem === 'orders' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">
              <i class="fas fa-file-invoice w-5 mr-3"
                :class="activeMenuItem === 'orders' ? 'text-red-600' : 'text-orange-500'"></i>
                Đơn mua
            </router-link>
          </li>
          <li class="mb-2">
            <a href="#" @click.prevent="selectMenuItem('vouchers')"
              :class="['flex items-center p-2 rounded',
                activeMenuItem === 'vouchers' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">
              <i class="fas fa-ticket-alt w-5 mr-3"
                :class="activeMenuItem === 'vouchers' ? 'text-red-600' : 'text-orange-500'"></i>
              Kho Voucher
            </a>
          </li>
          <li class="mb-2">
            <a href="#" @click.prevent="selectMenuItem('shopee-coins')"
              :class="['flex items-center p-2 rounded',
                activeMenuItem === 'shopee-coins' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">
              <i class="fas fa-coins w-5 mr-3"
                :class="activeMenuItem === 'shopee-coins' ? 'text-red-600' : 'text-orange-500'"></i>
              Xu của tôi
            </a>
          </li>
        </ul>
      </nav>
    </aside>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import ProfileSubMenu from '@/components/user/ProfileSubMenu.vue';

// Trạng thái đăng nhập
const isLoggedIn = ref(false); // Mặc định là chưa đăng nhập
const userName = ref('Guest'); // Tên người dùng

// Hàm giả định để đăng nhập
const login = () => {
  // Thực tế sẽ gọi API đăng nhập, xử lý token, vv.
  isLoggedIn.value = true;
  userName.value = '127092309'; // Đặt tên người dùng sau khi đăng nhập thành công
  activeMenuItem.value = 'profile'; // Tự động chọn hồ sơ sau khi đăng nhập
  isAccountSubMenuOpen.value = true; // Đảm bảo submenu mở sau khi đăng nhập
  console.log('Đã đăng nhập!');
};

// Hàm giả định để đăng xuất
const logout = () => {
  // Thực tế sẽ xóa token, reset trạng thái người dùng, vv.
  isLoggedIn.value = false;
  userName.value = 'Guest';
  activeMenuItem.value = 'notifications'; // Có thể chuyển về trang thông báo hoặc trang chủ
  isAccountSubMenuOpen.value = true; // Có thể đóng submenu lại khi logout
  console.log('Đã đăng xuất!');
};


const isAccountSubMenuOpen = ref(true);
const activeMenuItem = ref('profile');

// Danh sách các mục con của "Tài Khoản Của Tôi"
const accountSubMenuItems = ['profile', 'bank', 'address', 'password', 'notificationSetting', 'personalSetting'];


const toggleAccountSubMenu = () => {
  // Kiểm tra xem activeMenuItem hiện tại có phải là một trong các mục con này không
  const isAnySubMenuItemActive = accountSubMenuItems.includes(activeMenuItem.value);

  // LOGIC 1: Khi một mục con đang active, click vào menu cha không đóng submenu
  if (isAnySubMenuItemActive) {
    isAccountSubMenuOpen.value = true; // Luôn giữ mở nếu có mục con active
  } else {
    isAccountSubMenuOpen.value = !isAccountSubMenuOpen.value; // Toggle bình thường
  }
};

const selectMenuItem = (menuItem) => {
  activeMenuItem.value = menuItem;

  // LOGIC 2: Tự động đóng submenu khi chọn vào menu khác
  if (accountSubMenuItems.includes(menuItem)) {
    // Nếu mục được chọn nằm trong submenu, đảm bảo submenu đang mở
    isAccountSubMenuOpen.value = true;
  } else {
    // Nếu mục được chọn KHÔNG nằm trong submenu (là một mục "bên ngoài"),
    // thì đóng submenu lại
    isAccountSubMenuOpen.value = false;
  }
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css'); */
</style>