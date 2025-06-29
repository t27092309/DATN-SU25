<template>
    <div v-if="authStore.isAuthenticated" class="flex items-center space-x-4 p-2 bg-gray-100 rounded-md">
        <span class="text-gray-700">Xin chào, </span>

        <router-link to="/tai-khoan"><strong class="font-semibold text-gray-700">{{ authStore.userName
                }}</strong></router-link>
        <button @click="handleLogout"
            class="ml-4 px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
            Đăng xuất
        </button>
    </div>
    <div v-else class="flex space-x-4">
        <router-link to="/dang-nhap" class="text-white font-semibold  hover:text-blue-600">Đăng
            nhập</router-link>
        <span class="text-gray-400">|</span>
        <router-link to="/dang-ky" class="text-white font-semibold ">Đăng ký</router-link>
    </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth'; // Import auth store

const router = useRouter();
const authStore = useAuthStore(); // Sử dụng store

const handleLogout = async () => {
    await authStore.logout(); // Gọi action logout từ store
    router.push('/'); // Chuyển hướng về trang chủ
};
</script>
<style scoped>
@import '@/assets/tailwind.css';
</style>