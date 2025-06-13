<template>
  <div class="flex flex-col md:flex-row max-w-7xl mx-auto min-h-screen bg-gray-100">
    <main class="flex-1 p-4 md:p-8 order-2 md:order-1">
      <div class="bg-white shadow-md rounded-lg p-6 h-full">
        <router-view></router-view>
      </div>
    </main>

    <aside class="w-full md:w-64 p-4 order-1 md:order-2 flex-shrink-0">
      <div class="flex items-center mb-6">
        <div>
          <p class="font-semibold text-gray-800">{{ userName }}</p>
        </div>

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
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth'; // Import auth store
import ProfileSubMenu from '@/components/user/ProfileSubMenu.vue';

// Trạng thái đăng nhập
const isLoggedIn = ref(false); // Mặc định là chưa đăng nhập
const userName = ref('Guest'); // Tên người dùng



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