<template>
    <header class="bg-white shadow-sm h-16 flex items-center px-6 justify-between border-b border-gray-200
                 fixed top-0 z-10" :style="{ left: '265px', width: 'calc(100% - 265px)' }">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
        </div>

        <div class="flex items-center space-x-4">

            <button
                class="p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                title="Tìm kiếm">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
            <button
                class="p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                title="Cài đặt">
                <font-awesome-icon icon="fa-solid fa-gear" />
            </button>

            <div class="relative">
                <button
                    class="relative p-2 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    @click="toggleNotifications" title="Thông báo">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                    </svg>
                    <span v-if="unreadNotificationsCount > 0"
                        class="absolute top-1 right-1 block h-2 w-2 rounded-full ring-2 ring-white bg-red-500"></span>
                </button>

                <Transition name="fade-slide">
                    <div v-if="showNotifications"
                        class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-md shadow-lg py-1 z-20">
                        <div class="px-4 py-2 font-semibold text-gray-800 border-b border-gray-200">Thông báo của bạn
                        </div>
                        <ul v-if="notifications.length > 0">
                            <li v-for="notification in latestNotifications" :key="notification.id"
                                class="px-4 py-2 hover:bg-gray-100 text-sm text-gray-700 cursor-pointer border-b border-gray-100 last:border-b-0">
                                <p class="font-medium">{{ notification.title }}</p>
                                <p class="text-xs text-gray-500">{{ notification.timeAgo }}</p>
                            </li>
                        </ul>
                        <div v-else class="px-4 py-2 text-sm text-gray-500">Không có thông báo mới.</div>
                        <div class="px-4 py-2 border-t border-gray-200 text-center">
                            <a href="#" class="text-sm text-indigo-600 hover:text-indigo-800">Xem tất cả thông báo</a>
                        </div>
                    </div>
                </Transition>
            </div>

            <div class="relative">
                <div class="flex items-center space-x-2 cursor-pointer group" @click="toggleUserMenu">
                    <img class="h-8 w-8 rounded-full object-cover"
                        src="https://via.placeholder.com/150/4A5568/FFFFFF?text=JD" alt="User Avatar" />
                    <span class="text-gray-700 font-medium hidden sm:block">John Doe</span>
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-gray-700 transition-colors"
                        :class="{ 'rotate-180': showUserMenu }" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>

                <Transition name="fade-slide">
                    <div v-if="showUserMenu"
                        class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg py-1 z-20">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Thông tin cá
                            nhân</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Cài đặt tài
                            khoản</a>
                        <div class="border-t border-gray-200 my-1"></div>
                        <a href="#" @click.prevent="logout"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-100 hover:text-red-700">Đăng
                            xuất</a>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

// --- State cho User Menu ---
const showUserMenu = ref(false);

const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
    // Đảm bảo đóng thông báo nếu đang mở
    showNotifications.value = false;
};

// --- State cho Notifications ---
const showNotifications = ref(false);
const notifications = ref([
    { id: 1, title: 'Đơn hàng #12345 đã được tạo.', time: new Date(Date.now() - 5 * 60 * 1000), read: false }, // 5 phút trước
    { id: 2, title: 'Sản phẩm "Nước hoa ABC" sắp hết hàng.', time: new Date(Date.now() - 30 * 60 * 1000), read: false }, // 30 phút trước
    { id: 3, title: 'Báo cáo doanh thu tháng 6 đã sẵn sàng.', time: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000), read: true }, // 2 ngày trước
    { id: 4, title: 'Khách hàng mới đã đăng ký.', time: new Date(Date.now() - 1 * 24 * 60 * 60 * 1000), read: false }, // 1 ngày trước
    { id: 5, title: 'Có cập nhật mới cho hệ thống.', time: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000), read: true }, // 7 ngày trước
]);

const toggleNotifications = () => {
    showNotifications.value = !showNotifications.value;
    // Đảm bảo đóng user menu nếu đang mở
    showUserMenu.value = false;
};

// Computed property để lấy số lượng thông báo chưa đọc
const unreadNotificationsCount = computed(() => {
    return notifications.value.filter(n => !n.read).length;
});

// Computed property để hiển thị 5 thông báo gần nhất (có thể tùy chỉnh)
const latestNotifications = computed(() => {
    return notifications.value
        .sort((a, b) => b.time.getTime() - a.time.getTime()) // Sắp xếp mới nhất lên đầu
        .slice(0, 5) // Lấy 5 thông báo gần nhất
        .map(n => ({
            ...n,
            timeAgo: formatTimeAgo(n.time) // Thêm định dạng thời gian "X phút trước"
        }));
});

// Hàm định dạng thời gian "X phút/giờ/ngày trước"
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

// --- Xử lý click ra ngoài để đóng menu/thông báo ---
const handleClickOutside = (event) => {
    // Đóng user menu nếu click ra ngoài
    if (showUserMenu.value && !event.target.closest('.group') && !event.target.closest('.absolute.right-0.mt-2.w-48')) {
        showUserMenu.value = false;
    }
    // Đóng notifications nếu click ra ngoài
    if (showNotifications.value && !event.target.closest('.relative') && !event.target.closest('.absolute.right-0.mt-2.w-80')) {
        showNotifications.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// --- Hàm Logout (ví dụ) ---
const logout = () => {
    alert('Đăng xuất...'); // Thay bằng logic đăng xuất thực tế của bạn
    showUserMenu.value = false; // Đóng menu sau khi đăng xuất
};
</script>

<style scoped>
/* Transition cho dropdown menu (Fade and Slide) */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-5px);
    /* Trượt nhẹ lên trên khi biến mất */
}
</style>