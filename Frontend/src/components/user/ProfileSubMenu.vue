<template>
  <transition name="expand" @enter="enter" @after-enter="afterEnter" @leave="leave">
    <ul v-if="isVisible" class="pl-8 overflow-hidden">
      <li class="mb-2">
        <router-link to="ho-so" @click.prevent="emitSelectMenuItem('profile')"
          :class="['block p-2 rounded',
            activeMenuItem === 'profile' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">Hồ Sơ</router-link>
      </li>
      <li class="mb-2">
        <router-link to="ngan-hang" @click.prevent="emitSelectMenuItem('bank')"
          :class="['block p-2 rounded',
            activeMenuItem === 'bank' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">Ngân hàng</router-link>
      </li>
      <li class="mb-2">
        <router-link to="dia-chi" @click.prevent="emitSelectMenuItem('address')"
          :class="['block p-2 rounded',
            activeMenuItem === 'address' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">Địa chỉ</router-link>
      </li>
      <li class="mb-2">
        <a href="#" @click.prevent="emitSelectMenuItem('password')"
          :class="['block p-2 rounded',
            activeMenuItem === 'password' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">
          Đổi Mật Khẩu
        </a>
      </li>
      <li class="mb-2">
        <a href="#" @click.prevent="emitSelectMenuItem('notificationSetting')"
          :class="['block p-2 rounded',
            activeMenuItem === 'notificationSetting' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">
          Cài đặt thông báo
        </a>
      </li>
      <li class="mb-2">
        <a href="#" @click.prevent="emitSelectMenuItem('personalSetting')"
          :class="['block p-2 rounded',
            activeMenuItem === 'personalSetting' ? 'text-red-600 bg-red-50 font-semibold' : 'text-gray-700 hover:bg-red-50 hover:text-red-600']">
          Thiết lập riêng tư
        </a>
      </li>
    </ul>
  </transition>
</template>

<script setup>

  const props = defineProps({
    isVisible: {
      type: Boolean,
      default: false
    },
    activeMenuItem: {
      type: String,
      default: ''
    }
  });

  const emits = defineEmits(['selectMenuItem']);

  const emitSelectMenuItem = (menuItem) => {
    emits('selectMenuItem', menuItem);
  };

  // Vue Transition Hooks (giữ nguyên từ phần trước)
  const enter = (element) => {
    element.style.height = 'auto';
    const height = element.scrollHeight + 'px';
    element.style.height = '0';
    requestAnimationFrame(() => {
      element.style.height = height;
    });
  };

  const afterEnter = (element) => {
    element.style.height = 'auto';
  };

  const leave = (element) => {
    element.style.height = element.scrollHeight + 'px';
    requestAnimationFrame(() => {
      element.style.height = '0';
    });
  };
</script>

<style scoped>
  @import '@/assets/tailwind.css';

  .expand-enter-active,
  .expand-leave-active {
    transition: height 0.3s ease-in-out;
    /* Chỉ chuyển đổi thuộc tính height */
  }
</style>