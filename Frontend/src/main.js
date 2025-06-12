// import './assets/main.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'; // Import createPinia

import App from './App.vue'
import router from './router'
import axios from 'axios';
import { useAuthStore } from './stores/auth'; // Import auth store

import '@splidejs/splide/dist/css/splide.min.css'; // CSS của Splide
import { Splide, SplideSlide } from '@splidejs/vue-splide';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// 2. Import các icon bạn muốn sử dụng
// Ví dụ: icon solid
import { faUser, faCog, faHome, faShoppingCart } from '@fortawesome/free-solid-svg-icons'; // Thêm faShoppingCart
// Ví dụ: icon regular
import { faAddressBook } from '@fortawesome/free-regular-svg-icons';
// Ví dụ: icon brands
import { faFacebook, faTwitter } from '@fortawesome/free-brands-svg-icons';

// 3. Thêm các icon vào thư viện Font Awesome
library.add(faUser, faCog, faHome, faAddressBook, faFacebook, faTwitter, faShoppingCart);

const app = createApp(App)
const pinia = createPinia(); // Tạo instance Pinia

app.component('Splide', Splide);
app.component('SplideSlide', SplideSlide);

app.component('font-awesome-icon', FontAwesomeIcon);
// Cấu hình Axios toàn cục (nên làm đầu tiên)
axios.defaults.baseURL = 'http://localhost:8000/api';
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

app.use(pinia); // Gắn Pinia vào ứng dụng Vue
app.use(router)

// Sau khi Pinia được gắn, khởi tạo trạng thái xác thực
const authStore = useAuthStore();
authStore.initializeAuth(); // Gọi để đọc token/user từ localStorage khi app load

app.mount('#app')
