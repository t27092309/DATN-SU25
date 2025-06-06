// import './assets/main.css'
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

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

app.component('Splide', Splide);
app.component('SplideSlide', SplideSlide);

app.component('font-awesome-icon', FontAwesomeIcon);


app.use(router)

app.mount('#app')
