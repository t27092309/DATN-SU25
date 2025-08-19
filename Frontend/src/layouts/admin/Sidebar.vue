<template>
  <div
    class="bg-white text-gray-800 w-[280px] h-screen fixed top-0 left-0 flex flex-col shadow-xl border-r border-gray-200"
  >
    <!-- Header -->
    <div
      class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50"
    >
      <div class="flex items-center space-x-3">
        <div class="w-16 h-12 flex items-center justify-center shadow-md">
          <img class="" :src="logo" alt="" />
        </div>
        <div>
          <h1
            class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent"
          ></h1>
          <p class="text-xs text-gray-500 font-medium">Admin Panel</p>
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto custom-scrollbar p-2 bg-gray-50/50">
      <ul class="space-y-1">
        <li v-for="menuItem in menuItems" :key="menuItem.name" class="relative">
          <!-- Single Route Item -->
          <router-link
            v-if="!menuItem.children && menuItem.route"
            :to="menuItem.route"
            class="group flex items-center px-4 py-3 mx-2 rounded-xl transition-all duration-300 ease-out hover:scale-[1.02] active:scale-[0.98]"
            active-class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 shadow-md text-blue-700"
            exact-active-class="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 shadow-md text-blue-700"
          >
            <!-- Icon Container -->
            <div class="relative">
              <div
                class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center group-hover:bg-gradient-to-br group-hover:from-blue-500 group-hover:to-purple-600 group-[.router-link-exact-active]:bg-gradient-to-br group-[.router-link-exact-active]:from-blue-500 group-[.router-link-exact-active]:to-purple-600 transition-all duration-300 shadow-sm"
              >
                <i
                  v-if="menuItem.icon"
                  :class="[
                    menuItem.icon,
                    'text-gray-600 group-hover:text-white group-[.router-link-exact-active]:text-white transition-colors duration-300',
                  ]"
                ></i>
              </div>
              <!-- Active indicator dot -->
              <div
                class="absolute -top-1 -right-1 w-3 h-3 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full opacity-0 group-[.router-link-exact-active]:opacity-100 transition-opacity duration-300 shadow-md"
              ></div>
            </div>

            <span
              class="ml-4 font-medium text-gray-700 group-hover:text-gray-900 group-[.router-link-exact-active]:text-blue-700 transition-colors duration-300"
            >
              {{ menuItem.name }}
            </span>

            <!-- Hover effect -->
            <div
              class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-600/0 to-purple-600/0 group-hover:from-blue-600/5 group-hover:to-purple-600/5 transition-all duration-300 pointer-events-none"
            ></div>
          </router-link>

          <!-- Parent Item with Children -->
          <div v-else class="relative">
            <div
              class="group flex items-center justify-between px-4 py-3 mx-2 rounded-xl cursor-pointer transition-all duration-300 ease-out hover:scale-[1.02] active:scale-[0.98]"
              @click="toggleSubMenu(menuItem)"
              :class="{
                'bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 shadow-md text-blue-700':
                  menuItem.isOpen,
                'hover:bg-gray-100 text-gray-700 hover:text-gray-900':
                  !menuItem.isOpen,
              }"
            >
              <div class="flex items-center">
                <!-- Icon Container -->
                <div class="relative">
                  <div
                    class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center transition-all duration-300 shadow-sm"
                    :class="{
                      'bg-gradient-to-br from-blue-500 to-purple-600':
                        menuItem.isOpen,
                      'group-hover:bg-gray-200': !menuItem.isOpen,
                    }"
                  >
                    <i
                      v-if="menuItem.icon"
                      :class="[
                        menuItem.icon,
                        'transition-colors duration-300',
                        menuItem.isOpen
                          ? 'text-white'
                          : 'text-gray-600 group-hover:text-gray-800',
                      ]"
                    ></i>
                  </div>
                  <!-- Active indicator dot -->
                  <div
                    class="absolute -top-1 -right-1 w-3 h-3 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full transition-opacity duration-300 shadow-md"
                    :class="{
                      'opacity-100': menuItem.isOpen,
                      'opacity-0': !menuItem.isOpen,
                    }"
                  ></div>
                </div>

                <span class="ml-4 font-medium transition-colors duration-300">
                  {{ menuItem.name }}
                </span>
              </div>

              <!-- Dropdown Arrow -->
              <div class="relative">
                <svg
                  class="w-5 h-5 transition-all duration-300 text-gray-400"
                  :class="{
                    'rotate-180 text-blue-500': menuItem.isOpen,
                    'group-hover:text-gray-600': !menuItem.isOpen,
                  }"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  ></path>
                </svg>
              </div>

              <!-- Hover effect -->
              <div
                class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-600/0 to-purple-600/0 group-hover:from-blue-600/5 group-hover:to-purple-600/5 transition-all duration-300 pointer-events-none"
              ></div>
            </div>

            <!-- Submenu -->
            <Transition
              name="accordion"
              @before-enter="onBeforeEnter"
              @enter="onEnter"
              @after-enter="onAfterEnter"
              @before-leave="onBeforeLeave"
              @leave="onLeave"
              @after-leave="onAfterLeave"
            >
              <div
                v-if="menuItem.children && menuItem.isOpen"
                class="mt-2 mx-2 mb-2"
              >
                <div
                  class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden"
                >
                  <ul class="py-2">
                    <li
                      v-for="subItem in menuItem.children"
                      :key="subItem.name"
                    >
                      <router-link
                        :to="subItem.route"
                        class="group flex items-center px-4 py-2.5 mx-2 rounded-lg transition-all duration-200 relative overflow-hidden"
                        active-class="bg-blue-50 text-blue-600 border-l-3 border-blue-400"
                        exact-active-class="bg-blue-50 text-blue-600"
                      >
                        <!-- Connecting line -->
                        <div
                          class="w-6 h-px bg-gradient-to-r from-gray-300 to-transparent mr-2"
                        ></div>

                        <!-- Submenu indicator dot -->
                        <div
                          class="w-2 h-2 rounded-full bg-gray-400 group-hover:bg-blue-500 group-[.router-link-exact-active]:bg-blue-500 transition-colors duration-200 mr-3 flex-shrink-0"
                        ></div>

                        <span
                          class="text-sm text-gray-600 group-hover:text-gray-900 group-[.router-link-exact-active]:text-blue-600 transition-colors duration-200 font-medium"
                        >
                          {{ subItem.name }}
                        </span>

                        <!-- Hover background effect -->
                        <div
                          class="absolute inset-0 bg-gradient-to-r from-blue-600/0 to-purple-600/0 group-hover:from-blue-600/5 group-hover:to-purple-600/5 transition-all duration-200"
                        ></div>
                      </router-link>
                    </li>
                  </ul>
                </div>
              </div>
            </Transition>
          </div>
        </li>
      </ul>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t border-gray-200 bg-gray-50">
      <div class="flex items-center space-x-3 text-xs text-gray-500">
        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
        <span>System Online</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import logo from "../../assets/img/florea/Logo-bgremove.png";
const menuItems = ref([
  {
    name: "Dashboard",
    route: "/admin/dashboard",
    isOpen: false,
    icon: "fas fa-chart-line",
  },
  {
    name: "Sản phẩm",
    route: "/admin/products",
    icon: "fas fa-box",
    isOpen: false,
    children: [
      { name: "Tất cả sản phẩm", route: "/admin/products" },
      { name: "Thêm sản phẩm mới", route: "/admin/add-product" },
    ],
  },
  {
    name: "Danh mục",
    route: "/admin/categories",
    icon: "fas fa-tags",
    isOpen: false,
    children: [{ name: "Tất cả danh mục", route: "/admin/categories" }],
  },
  {
    name: "Thương hiệu",
    route: "/admin/brands",
    icon: "fas fa-star",
    isOpen: false,
    children: [{ name: "Tất cả thương hiệu", route: "/admin/brands" }],
  },
  {
    name: "Mã giảm giá",
    route: "/admin/coupons",
    icon: "fas fa-percent",
    isOpen: false,
    children: [{ name: "Tất cả mã giảm giá", route: "/admin/coupons" }],
  },
  {
    name: "Nhóm hương",
    route: "/admin/scent-groups",
    icon: "fas fa-leaf",
    isOpen: false,
    children: [{ name: "Tất cả nhóm hương", route: "/admin/scent-groups" }],
  },
  {
    name: "Biến thể",
    route: "/admin/attributes",
    icon: "fas fa-sliders-h",
    isOpen: false,
  },
  {
    name: "Đơn hàng",
    route: "/admin/orders",
    icon: "fas fa-shopping-cart",
    isOpen: false,
  },
  {
    name: "Phương thức vận chuyển",
    route: "/admin/shipping-methods",
    icon: "fas fa-truck",
    isOpen: false,
  },
  {
    name: "Banner",
    route: "/admin/banners",
    icon: "fas fa-images",
    isOpen: false,
  },
]);

const toggleSubMenu = (item) => {
  menuItems.value.forEach((menu) => {
    if (menu !== item && menu.isOpen) {
      menu.isOpen = false;
    }
  });

  if (item.children) {
    item.isOpen = !item.isOpen;
  }
};

// Transition hooks
const onBeforeEnter = (el) => {
  el.style.height = "0";
  el.style.opacity = "0";
  el.style.overflow = "hidden";
};

const onEnter = (el, done) => {
  el.offsetHeight;
  el.style.height = el.scrollHeight + "px";
  el.style.opacity = "1";
  el.addEventListener("transitionend", done, { once: true });
};

const onAfterEnter = (el) => {
  el.style.height = "";
  el.style.overflow = "";
};

const onBeforeLeave = (el) => {
  el.style.height = el.scrollHeight + "px";
  el.style.opacity = "1";
  el.style.overflow = "hidden";
};

const onLeave = (el, done) => {
  el.offsetHeight;
  el.style.height = "0";
  el.style.opacity = "0";
  el.addEventListener("transitionend", done, { once: true });
};

const onAfterLeave = (el) => {
  el.style.height = "";
  el.style.overflow = "";
};
</script>

<style scoped>
/* Accordion transitions */
.accordion-enter-active,
.accordion-leave-active {
  transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
}

/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(229, 231, 235, 0.5);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}

/* Active glow effect */
@keyframes glow {
  0% {
    box-shadow: 0 0 5px rgba(59, 130, 246, 0.2);
  }
  50% {
    box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
  }
  100% {
    box-shadow: 0 0 5px rgba(59, 130, 246, 0.2);
  }
}

.router-link-exact-active {
  animation: glow 2s ease-in-out infinite;
}
</style>
