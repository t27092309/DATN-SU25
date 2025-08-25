<template>
    <div v-if="authStore.isAuthenticated" class="relative">
        <!-- User name display on red background -->
        <div @mouseenter="handleMouseEnter" @mouseleave="handleMouseLeave"
            class="flex items-center cursor-pointer text-white px-4 py-2 rounded-md transition-colors duration-200">
            <router-link to="/tai-khoan" class="font-semibold hover:underline">
                {{ authStore.userName }}
            </router-link>
            <svg class="ml-2 w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showDropdown }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>

        <!-- Dropdown menu -->
        <transition enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 transform scale-95 -translate-y-2"
            enter-to-class="opacity-100 transform scale-100 translate-y-0"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 transform scale-100 translate-y-0"
            leave-to-class="opacity-0 transform scale-95 -translate-y-2">
            <div v-show="showDropdown" @mouseenter="handleDropdownEnter" @mouseleave="handleDropdownLeave"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                <!-- Menu cho user thường -->
                <template v-if="authStore.user?.role === 'user'">
                    <router-link to="/tai-khoan"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                        Tài khoản của tôi
                    </router-link>
                    <router-link to="/tai-khoan/don-hang"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                        Đơn mua
                    </router-link>
                </template>
                
                <!-- Menu cho admin/staff -->
                <template v-if="authStore.user?.role === 'admin' || authStore.user?.role === 'staff'">
                    <router-link to="/admin/dashboard"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                        Quản lý Admin
                    </router-link>
                    <div class="px-4 py-2 text-xs text-gray-500">
                        Vai trò: {{ getRoleDisplayName(authStore.user?.role) }}
                    </div>
                </template>
                
                <hr class="my-1 border-gray-200">
                <button @click="handleLogout"
                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150">
                    Đăng xuất
                </button>
            </div>
        </transition>
    </div>
    <div v-else class="flex space-x-4">
        <router-link to="/dang-nhap" class="text-white font-semibold hover:text-blue-600">Đăng nhập</router-link>
        <span class="text-gray-400">|</span>
        <router-link to="/dang-ky" class="text-white font-semibold">Đăng ký</router-link>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const showDropdown = ref(false);
let hideTimeout = null;

const handleMouseEnter = () => {
    if (hideTimeout) {
        clearTimeout(hideTimeout);
        hideTimeout = null;
    }
    showDropdown.value = true;
};

const handleMouseLeave = () => {
    hideTimeout = setTimeout(() => {
        showDropdown.value = false;
    }, 150); // Delay 150ms before hiding
};

const handleDropdownEnter = () => {
    if (hideTimeout) {
        clearTimeout(hideTimeout);
        hideTimeout = null;
    }
    showDropdown.value = true;
};

const handleDropdownLeave = () => {
    hideTimeout = setTimeout(() => {
        showDropdown.value = false;
    }, 150); // Delay 150ms before hiding
};

const handleLogout = async () => {
    await authStore.logout();
    router.push('/');
};

const getRoleDisplayName = (role) => {
    const roleNames = {
        'admin': 'Administrator',
        'staff': 'Staff',
        'user': 'Customer'
    };
    return roleNames[role] || 'User';
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>