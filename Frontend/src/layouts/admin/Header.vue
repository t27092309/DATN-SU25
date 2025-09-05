<template>
    <header class="bg-white shadow-lg h-16 flex items-center px-6 justify-between 
                   border-b border-gray-200 fixed top-0 z-10" :style="{ left: '280px', width: 'calc(100% - 280px)' }">

        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-3">
                <div
                    class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-700 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-white text-sm"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-800">
                    {{ currentRouteTitle }}
                </h2>
            </div>
        </div>

        <div class="flex items-center space-x-3">

            <button class="group relative p-3 rounded-xl bg-gray-100 hover:bg-gray-200 
                       border border-gray-200 hover:border-gray-300 transition-all duration-300 
                       focus:outline-none focus:ring-2 focus:ring-blue-500/50 hover:shadow-md" title="Tìm kiếm">
                <svg class="w-5 h-5 text-gray-500 group-hover:text-blue-600 transition-colors duration-300" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>

            <button class="group relative p-3 rounded-xl bg-gray-100 hover:bg-gray-200 
                   border border-gray-200 hover:border-gray-300 transition-all duration-300 
                   focus:outline-none focus:ring-2 focus:ring-blue-500/50 hover:shadow-md" title="Cài đặt">
                <div class="transition-all duration-300 group-hover:rotate-90">
                    <i class="fas fa-cog w-5 h-5 text-gray-500 group-hover:text-blue-600 transition-colors"></i>
                </div>
            </button>

            <div class="relative">
                <button class="group relative p-3 rounded-xl bg-gray-100 hover:bg-gray-200 
                           border border-gray-200 hover:border-gray-300 transition-all duration-300 
                           focus:outline-none focus:ring-2 focus:ring-blue-500/50 hover:shadow-md"
                    @click="toggleNotifications" title="Thông báo">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-blue-600 transition-colors duration-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                    <span v-if="unreadNotificationsCount > 0" class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full 
                                 bg-red-500 text-xs font-bold text-white 
                                 animate-pulse shadow-lg">
                        {{ unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount }}
                    </span>
                </button>

                <Transition name="fade-slide">
                    <div v-if="showNotifications" class="absolute right-0 mt-3 w-80 bg-white 
                                border border-gray-200 rounded-xl shadow-2xl z-20 overflow-hidden">
                        <div class="px-4 py-3 font-semibold text-gray-800 border-b border-gray-200 
                                     bg-gray-50">
                            <div class="flex items-center justify-between">
                                <span>Thông báo của bạn</span>
                                <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full">
                                    {{ unreadNotificationsCount }} mới
                                </span>
                            </div>
                        </div>
                        <ul v-if="notifications.length > 0" class="max-h-80 overflow-y-auto custom-scrollbar">
                            <li v-for="notification in latestNotifications" :key="notification.id" class="px-4 py-3 hover:bg-gray-100 text-sm cursor-pointer border-b border-gray-200 
                                       last:border-b-0 transition-colors duration-200 group">
                                <div class="flex items-start space-x-3">
                                    <div class="w-2 h-2 rounded-full mt-2 flex-shrink-0"
                                        :class="notification.read ? 'bg-gray-400' : 'bg-blue-500 animate-pulse'"></div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-800 group-hover:text-black transition-colors">
                                            {{ notification.title }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">{{ notification.timeAgo }}</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                        <div v-else class="px-4 py-6 text-center text-gray-500">
                            <i class="fas fa-bell-slash text-2xl mb-2 opacity-50"></i>
                            <p>Không có thông báo mới</p>
                        </div>
                        <div class="px-4 py-3 border-t border-gray-200 text-center bg-gray-50">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-500 transition-colors font-medium">
                                Xem tất cả thông báo
                            </a>
                        </div>
                    </div>
                </Transition>
            </div>
            <div class="border-t border-gray-200">
                <a href="#" @click.prevent="logout" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:text-red-600 
                                      hover:bg-red-50 transition-all duration-200">
                    <i
                        class="fas fa-sign-out-alt w-4 h-4 mr-3 text-gray-400 group-hover:text-red-600 transition-colors"></i>
                    Đăng xuất
                </a>
            </div>

            <div class="relative">
                <div class="flex items-center space-x-3 cursor-pointer group p-2 rounded-xl 
                            hover:bg-gray-100 border border-transparent hover:border-gray-200 transition-all duration-300"
                    @click="toggleUserMenu">
                    <div class="relative">
                        <!-- <img class="h-9 w-9 rounded-full object-cover border-2 border-gray-300 group-hover:border-blue-500 transition-colors duration-300"
                            src="" alt="User Avatar" /> -->
                           <div class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-200 text-xl">
                                👤
                            </div>
                        <div
                            class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white">
                        </div>
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-gray-800 font-medium group-hover:text-black transition-colors">{{
                            authStore.user?.name || 'User' }}</span>
                        <p class="text-xs text-gray-500">{{ getRoleDisplayName(authStore.user?.role) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-blue-600 transition-all duration-300"
                        :class="{ 'rotate-180 text-blue-600': showUserMenu }" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <Transition name="fade-slide">
                    <div v-if="showUserMenu" class="absolute right-0 mt-3 w-56 bg-white 
                                border border-gray-200 rounded-xl shadow-2xl z-20 overflow-hidden">

                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <div class="flex items-center space-x-3">
                                <img class="h-10 w-10 rounded-full object-cover border border-gray-200" src=""
                                    alt="User Avatar" />
                                <div>
                                    <p class="font-medium text-gray-800">{{ authStore.user?.name || 'User' }}</p>
                                    <p class="text-xs text-gray-500">{{ authStore.user?.email || 'user@example.com' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="py-2">
                            <a href="#" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:text-black 
                                             hover:bg-gray-100 transition-colors duration-200">
                                <i
                                    class="fas fa-user w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-600 transition-colors"></i>
                                Thông tin cá nhân
                            </a>
                            <a href="#" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:text-black 
                                             hover:bg-gray-100 transition-colors duration-200">
                                <i
                                    class="fas fa-cog w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-600 transition-colors"></i>
                                Cài đặt tài khoản
                            </a>
                            <a href="#" class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:text-black 
                                             hover:bg-gray-100 transition-colors duration-200">
                                <i
                                    class="fas fa-moon w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-600 transition-colors"></i>
                                Chế độ tối
                            </a>
                        </div>

                        <div class="border-t border-gray-200">
                            <a href="#" @click.prevent="logout" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:text-red-600 
                                      hover:bg-red-50 transition-all duration-200">
                                <i
                                    class="fas fa-sign-out-alt w-4 h-4 mr-3 text-gray-400 group-hover:text-red-600 transition-colors"></i>
                                Đăng xuất
                            </a>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// --- State cho User Menu ---
const showUserMenu = ref(false);
const authStore = useAuthStore();
const router = useRouter();

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
    showNotifications.value = false;
};

const logout = () => {
    authStore.logout();
    router.push('/dang-nhap');
};

const getRoleDisplayName = (role) => {
    const roleNames = {
        'admin': 'Administrator',
        'staff': 'Staff',
        'user': 'Customer'
    };
    return roleNames[role] || 'User';
};

// --- State cho Notifications ---
const showNotifications = ref(false);
const notifications = ref([
    { id: 1, title: 'Đơn hàng #12345 đã được tạo.', time: new Date(Date.now() - 5 * 60 * 1000), read: false },
    { id: 2, title: 'Sản phẩm "Nước hoa ABC" sắp hết hàng.', time: new Date(Date.now() - 30 * 60 * 1000), read: false },
    { id: 3, title: 'Báo cáo doanh thu tháng 6 đã sẵn sàng.', time: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000), read: true },
    { id: 4, title: 'Khách hàng mới đã đăng ký.', time: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000), read: false },
    { id: 5, title: 'Có cập nhật mới cho hệ thống.', time: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000), read: true },
]);

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    showUserMenu.value = false;
};

// Computed properties
const unreadNotificationsCount = computed(() => {
    return notifications.value.filter(n => !n.read).length;
});

const latestNotifications = computed(() => {
    return notifications.value
        .sort((a, b) => b.time.getTime() - a.time.getTime())
        .slice(0, 5)
        .map(n => ({
            ...n,
            timeAgo: formatTimeAgo(n.time)
        }));
});

// Utility functions
function formatTimeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);

    let interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " năm trước";
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " tháng trước";
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " ngày trước";
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " giờ trước";
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " phút trước";
    return Math.floor(seconds) + " giây trước";
}

// Click outside handler
const handleClickOutside = (event) => {
    const userMenuButton = document.querySelector('.relative > .group');
    const userMenuDropdown = document.querySelector('.relative > .absolute.w-56');
    const notificationButton = document.querySelector('.relative:nth-child(3) > .group');
    const notificationDropdown = document.querySelector('.relative:nth-child(3) > .absolute.w-80');

    if (showUserMenu.value && userMenuDropdown && !userMenuDropdown.contains(event.target) && userMenuButton && !userMenuButton.contains(event.target)) {
        showUserMenu.value = false;
    }
    if (showNotifications.value && notificationDropdown && !notificationDropdown.contains(event.target) && notificationButton && !notificationButton.contains(event.target)) {
        showNotifications.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});


const route = useRoute();
const currentRouteTitle = computed(() => route.meta.title || 'Dashboard');
</script>

<style scoped>

/* Transition animations */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.95);
}

/* Custom scrollbar for notifications */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #2563eb, #7c3aed);
}
</style>