// main.js
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import './assets/tailwind.css';
import Toast, { POSITION } from 'vue-toastification';
import 'vue-toastification/dist/index.css';
import router from './router';
import tinymce from 'tinymce/tinymce';
import axios from 'axios'; // Import Axios
import { useAuthStore } from './stores/auth'; // Import auth store

import '@splidejs/splide/dist/css/splide.min.css'; // CSS của Splide
import { Splide, SplideSlide } from '@splidejs/vue-splide';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faUser, faCog, faHome, faShoppingCart } from '@fortawesome/free-solid-svg-icons';
import { faAddressBook } from '@fortawesome/free-regular-svg-icons';
import { faFacebook, faTwitter } from '@fortawesome/free-brands-svg-icons';

// 3. Thêm các icon vào thư viện Font Awesome
library.add(faUser, faCog, faHome, faAddressBook, faFacebook, faTwitter, faShoppingCart);

// Cấu hình TinyMCE sử dụng file cục bộ
window.tinymce = tinymce;
tinymce.baseURL = '/tinymce'; // Đặt đường dẫn gốc
tinymce.suffix = '.min'; // Sử dụng file nén

// --- Khởi tạo Vue App và Pinia ---
const app = createApp(App);
const pinia = createPinia();

// --- Đăng ký Components toàn cục ---
app.component('Splide', Splide);
app.component('SplideSlide', SplideSlide);
app.component('font-awesome-icon', FontAwesomeIcon);

// Cấu hình Toastification
const options = {
    position: POSITION.TOP_RIGHT, // Vị trí hiển thị toast
    timeout: 3000, // Thời gian hiển thị (ms)
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: 'button',
    icon: true,
    rtl: false
};

// --- Cấu hình Axios toàn cục ---
axios.defaults.baseURL = 'http://localhost:8000/api/admin';
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = localStorage.getItem('authToken');

if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// --- Hàm quản lý Tawk.to ---
let tawkToScriptLoaded = false; // Biến cờ để kiểm soát việc tải script

function loadTawkToScript() {
    // Tránh tải script nhiều lần
    if (tawkToScriptLoaded) {
        if (window.Tawk_API && window.Tawk_API.showWidget) {
            window.Tawk_API.showWidget(); // Đảm bảo hiển thị nếu trước đó đã ẩn
        }
        return;
    }

    // Tạo và thêm script Tawk.to
    var Tawk_API = window.Tawk_API || {}, Tawk_LoadStart = new Date();
    (function () {
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/6843f4447bacdc190ef68830/1it4n4b3b';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();

    tawkToScriptLoaded = true; // Đặt cờ là đã tải
}

function unloadTawkToScript() {
    // Nếu Tawk_API tồn tại, ẩn widget
    if (window.Tawk_API && window.Tawk_API.hideWidget) {
        window.Tawk_API.hideWidget();
    }

    // Tùy chọn: Loại bỏ iframe của Tawk.to khỏi DOM để sạch hơn
    // Cần đảm bảo selector là chính xác với Tawk.to iframe của bạn
    const tawkToWidget = document.querySelector('iframe[title="tawk.to chat iframe"]') || document.getElementById('tawkchat-iframe');
    if (tawkToWidget && tawkToWidget.parentNode) {
        tawkToWidget.parentNode.removeChild(tawkToWidget);
    }
    tawkToScriptLoaded = false; // Đặt lại cờ
}

// --- Global Navigation Guard của Vue Router ---
router.beforeEach((to, from, next) => {
    const isAdminRoute = to.matched.some(record => record.meta && record.meta.requiresAdmin);

    if (isAdminRoute) {
        unloadTawkToScript();
    } else {
        loadTawkToScript();
    }

    next(); // Luôn gọi next() để cho phép điều hướng tiếp tục
});


// --- Xử lý Tawk.to ngay khi ứng dụng khởi động (trước khi mount) ---
// Bước này quan trọng để xử lý trường hợp tải trang admin trực tiếp
const currentRoute = router.resolve(window.location.pathname);
const isInitialAdminRoute = currentRoute.matched.some(record => record.meta && record.meta.requiresAdmin);

if (isInitialAdminRoute) {
    // Nếu là trang admin khi khởi động, đảm bảo không tải Tawk.to
    // Đồng thời, nếu Tawk.to bằng cách nào đó đã được nhúng (ví dụ, do code cũ hoặc cache)
    // thì cố gắng loại bỏ nó ngay lập tức.
    unloadTawkToScript();
} else {
    // Nếu không phải trang admin khi khởi động, tải Tawk.to
    loadTawkToScript();
}


// --- Sử dụng Pinia và Vue Router ---
app.use(pinia);
app.use(router);
app.use(Toast, options);

// --- Khởi tạo trạng thái xác thực từ Pinia Store ---
const authStore = useAuthStore();
authStore.initializeAuth();

// --- Mount ứng dụng Vue ---
app.mount('#app');