// main.js
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
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

// --- Cấu hình Axios toàn cục ---
// Base URL cho tất cả các API requests
axios.defaults.baseURL = 'http://localhost:8000/api';
// Cho phép gửi credentials (cookies, session) qua CORS requests (quan trọng cho Sanctum)
axios.defaults.withCredentials = true;
// Header này giúp Laravel nhận diện request là AJAX
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = localStorage.getItem('authToken'); // Giả sử bạn lưu token với key 'api_token'

if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}
// --- Sử dụng Pinia và Vue Router ---
app.use(pinia);
app.use(router);

// --- Khởi tạo trạng thái xác thực từ Pinia Store ---
// Sau khi Pinia được gắn vào ứng dụng, chúng ta có thể sử dụng store.
// Việc gọi initializeAuth() ở đây là rất quan trọng để đọc token từ localStorage
// và thiết lập header Authorization cho Axios NGAY KHI ứng dụng khởi động.
const authStore = useAuthStore();
authStore.initializeAuth();

// --- Mount ứng dụng Vue ---
app.mount('#app');