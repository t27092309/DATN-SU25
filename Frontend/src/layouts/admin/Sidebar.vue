<template>
  <div
    class="bg-gradient-to-br from-gray-900 to-gray-800 text-gray-200 w-[265px] h-screen fixed top-0 left-0 flex flex-col shadow-lg">
    <div class="p-4 text-xl font-bold border-b border-gray-700">
      Florea admin panel
    </div>
    <nav class="flex-1 overflow-y-auto custom-scrollbar">
      <ul>
        <li v-for="menuItem in menuItems" :key="menuItem.name">
          <router-link v-if="!menuItem.children && menuItem.route" :to="menuItem.route"
            class="px-4 py-3 cursor-pointer flex items-center transition-colors duration-200 block group"
            active-class="bg-gray-700 text-white" exact-active-class="bg-gray-700 text-white">
            <i v-if="menuItem.icon"
              :class="[menuItem.icon, 'w-5 h-5 mr-3 text-gray-400 group-[.router-link-exact-active]:text-white group-hover:text-white']"></i>
            <span>{{ menuItem.name }}</span>
          </router-link>

          <div v-else
            class="px-4 py-3 cursor-pointer flex items-center justify-between transition-colors duration-200 group"
            @click="toggleSubMenu(menuItem)" :class="{
              'bg-gray-700 text-white': menuItem.isOpen, /* Active: Nền xám đậm hơn, chữ trắng */
              'hover:bg-gray-800 hover:text-white': !menuItem.isOpen /* Hover: Nền xám đậm hơn, chữ trắng (khi chưa active) */
            }">
            <div class="flex items-center">
              <i v-if="menuItem.icon"
                :class="[menuItem.icon, 'w-5 h-5 mr-3 text-gray-400 group-[.bg-gray-700]:text-white group-hover:text-white']"></i>
              <span>{{ menuItem.name }}</span>
            </div>
            <svg v-if="menuItem.children && menuItem.children.length > 0"
              class="w-4 h-4 transition-transform duration-200 text-gray-400"
              :class="{ 'rotate-180 text-white': menuItem.isOpen }" fill="none" stroke="currentColor"
              viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>

          <Transition name="accordion" @before-enter="onBeforeEnter" @enter="onEnter" @after-enter="onAfterEnter"
            @before-leave="onBeforeLeave" @leave="onLeave" @after-leave="onAfterLeave">
            <ul v-if="menuItem.children && menuItem.isOpen" class="bg-gray-800">
              <li v-for="subItem in menuItem.children" :key="subItem.name">
                <router-link :to="subItem.route"
                  class="px-4 py-2 pl-8 cursor-pointer transition-colors duration-200 block text-gray-400 group"
                  active-class="bg-gray-700 text-white" exact-active-class="bg-gray-700 text-white">
                  <span>{{ subItem.name }}</span>
                </router-link>
              </li>
            </ul>
          </Transition>
        </li>
      </ul>
    </nav>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import logormbg from '@/assets/img/florea/Logo.png';

const menuItems = ref([
  // Giữ nguyên dữ liệu menuItems như trước
  {
    name: 'Dashboard',
    route: '/dashboard',
    isOpen: false,
  },
  {
    name: 'Sản phẩm',
    route: '/admin/products',
    isOpen: false,
    children: [
      { name: 'Tất cả sản phẩm', route: '/admin/products' },
      { name: 'Thêm sản phẩm mới', route: '/admin/add-product' },
    ],
  },
  {
    name: 'Danh mục',
    route: '/admin/categories',
    isOpen: false,
    children: [
      { name: 'Tất cả danh mục', route: '/admin/categories' },
    ],
  },
  {
    name: 'Thương hiệu',
    route: '/admin/brands',
    isOpen: false,
    children: [
      { name: 'Tất cả thương hiệu', route: '/admin/brands' },
    ],
  },
  {
    name: 'Mã giảm giá',
    route: '/admin/coupons',
    isOpen: false,
    children: [
      { name: 'Tất cả mã giảm giá', route: '/admin/coupons' },
    ],
  },
  {
    name: 'Nhóm hương',
    route: '/admin/scent-groups',
    isOpen: false,
    children: [
      { name: 'Tất cả nhóm hương', route: '/admin/scent-groups' },
    ],
  },
  {
    name: 'Biến thể',
    route: '/admin/attributes',
    isOpen: false,
  },
  {
    name: 'Đơn hàng',
    route: '/admin/orders',
    isOpen: false,
  },
  {
    name: 'Phương thức vận chuyển',
    route: '/admin/shipping-methods',
    isOpen: false,
  },

]);

const toggleSubMenu = (item) => {
  menuItems.value.forEach(menu => {
    if (menu !== item && menu.isOpen) {
      menu.isOpen = false;
    }
  });

  if (item.children) {
    item.isOpen = !item.isOpen;
  }
};

// --- Transition Hooks (giữ nguyên vì chúng hoạt động tốt) ---
const onBeforeEnter = (el) => {
  el.style.height = '0';
  el.style.opacity = '0';
  el.style.overflow = 'hidden';
};

const onEnter = (el, done) => {
  el.offsetHeight;
  el.style.height = el.scrollHeight + 'px';
  el.style.opacity = '1';
  el.addEventListener('transitionend', done, { once: true });
};

const onAfterEnter = (el) => {
  el.style.height = '';
  el.style.overflow = '';
};

const onBeforeLeave = (el) => {
  el.style.height = el.scrollHeight + 'px';
  el.style.opacity = '1';
  el.style.overflow = 'hidden';
};

const onLeave = (el, done) => {
  el.offsetHeight;
  el.style.height = '0';
  el.style.opacity = '0';
  el.addEventListener('transitionend', done, { once: true });
};

const onAfterLeave = (el) => {
  el.style.height = '';
  el.style.overflow = '';
};
</script>

<style scoped>
/* CSS cho Transition vẫn giữ nguyên */
.accordion-enter-active,
.accordion-leave-active {
  transition: height 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

/* Tùy chỉnh thanh cuộn cho đẹp hơn (chỉ hoạt động trên Webkit browsers như Chrome) */
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
  /* Chiều rộng thanh cuộn */
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #374151;
  /* Màu nền của rãnh cuộn */
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #6B7280;
  /* Màu của thanh cuộn */
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #9CA3AF;
  /* Màu của thanh cuộn khi hover */
}
</style>