<template>

    <div>
        <div class="header">
            <div class="upper-header">
                <nav class="dark:bg-red-500 w-full border-b">
                    <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto py-2 px-4">
                        <p class="text-white font-semibold text-xs">NƯỚC HOA & MỸ PHẨM CHÍNH HÃNG TỪ 2004</p>

                        <!-- Ô tìm kiếm -->
                        <form class="max-w-md mx-auto flex-grow px-4">
                            <label for="default-search" class="sr-only">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" fill="none"
                                        viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                    </svg>
                                </div>
                                <input type="search" id="default-search"
                                    class="block w-full h-10 ps-10 pe-28 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-white dark:text-black"
                                    placeholder="Tìm kiếm nước hoa của bạn" required />
                                <button type="submit"
                                    class="absolute end-1 bottom-1.5 h-7 px-4 text-sm text-black bg-gray-200 hover:bg-gray-300 rounded-md flex items-center justify-center">
                                    Search
                                </button>
                            </div>
                        </form>

                        <!-- Các nút bên phải -->
                        <div class="flex items-center space-x-2">
                            <a href="">
                                <span class="text-white font-semibold text-xs">Giới thiệu về chúng tôi</span>
                            </a>
                            <a href="">
                                <span class="text-white font-semibold text-xs ml-10">Tra cứu lịch sử mua hàng</span>
                            </a>
                            <button type="button"
                                class="inline-flex items-center p-2 justify-center text-sm text-gray-500 rounded-lg md:hidden hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
                                aria-controls="navbar-sticky" aria-expanded="false">
                                <span class="sr-only">Open main menu</span>
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 17 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="M1 1h15M1 7h15M1 13h15" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </nav>
            </div>
            <div class="lower-header font-sans antialiased text-gray-800">
                <nav class="bg-white shadow-md sticky top-0 z-30">
                    <div class="container mx-auto flex items-center justify-center px-4 py-2">
                        <div class="flex items-center space-x-6">
                            <router-link to="/">
                                <img :src="logormbg" alt="Florea" class="h-16 mr-0 pb-1.5">
                            </router-link>

                            <div class="relative group inline-block z-50">
                                <router-link to="/nuoc-hoa"
                                    class="cursor-pointer text-sm font-semibold  hover:text-red-600 text-gray-900 flex items-center py-2">
                                    Nước Hoa
                                    <svg class="w-4 h-4 ml-1 transform group-hover:rotate-180 transition-transform duration-200"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </router-link>
                                <div class="absolute left-14 -translate-x-1/4 top-6 mt-2 bg-white rounded-b-lg shadow-xl p-8 w-[1200px] z-50 hidden group-hover:block"
                                    style="max-height: calc(100vh - 70px); overflow-y: auto;">
                                    <div class="mx-auto px-0">
                                        <div
                                            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-12">
                                            <div
                                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                                <div class="relative w-full h-72 overflow-hidden">
                                                    <img src="../assets/images/categories/category-nuoc-hoa-nam.webp"
                                                        alt="Men's Perfume"
                                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                                <div class="relative w-full h-72 overflow-hidden">
                                                    <img src="../assets/images/categories/category-nuoc-hoa-nu-600x720.webp"
                                                        alt="Women's Perfume"
                                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                                <div class="relative w-full h-72 overflow-hidden">
                                                    <img src="../assets/images/categories/category-nuoc-hoa-unisex-600x720.webp"
                                                        alt="Unisex Perfume"
                                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                                <div class="relative w-full h-72 overflow-hidden">
                                                    <img src="../assets/images/categories/category-nuoc-hoa-niche-600x720.webp"
                                                        alt="Niche Perfume"
                                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden">
                                                <div class="relative w-full h-72 overflow-hidden">
                                                    <img src="../assets/images/categories/category-nuoc-hoa-mini-600x720.webp"
                                                        alt="Mini Perfume"
                                                        class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-y-8 gap-x-4">
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-bvlgary.webp"
                                                    alt="BVLGARI Logo" class=" h-16 max-w-full object-contain" /></div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/04/logo-brand-calvin-klein.webp"
                                                    alt="Parfums Logo" class=" h-16 max-w-full object-contain" /></div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-chanel.webp"
                                                    alt="CHANEL Logo" class=" h-16 max-w-full object-contain" /></div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-dior.webp"
                                                    alt="DIOR Logo" class=" h-16 max-w-full object-contain" /></div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/04/logo-brand-giorgio-armani.webp"
                                                    alt="GIORGIO ARMANI Logo" class=" h-16 max-w-full object-contain" />
                                            </div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-gucci.webp"
                                                    alt="GUCCI Logo" class=" h-16 max-w-full object-contain" /></div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jean-paul-gaultier.webp"
                                                    alt="Jean Paul GAULTIER Logo"
                                                    class=" h-16 max-w-full object-contain" />
                                            </div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jo-malone.webp"
                                                    alt="Jo Malone Logo" class=" h-16 max-w-full object-contain" />
                                            </div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-kilian.webp"
                                                    alt="Kilian Logo" class=" h-16 max-w-full object-contain" /></div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-lancome.webp"
                                                    alt="LANCÔME Logo" class=" h-16 max-w-full object-contain" /></div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-montblanc.webp"
                                                    alt="MONTBLANC Logo" class=" h-16 max-w-full object-contain" />
                                            </div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/04/logo-brand-narciso-rodriguez.webp"
                                                    alt="narciso rodriguez Logo"
                                                    class=" h-16 max-w-full object-contain" />
                                            </div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-parfums-de-marly.webp"
                                                    alt="PARFUMS DE MARLY Logo"
                                                    class=" h-16 max-w-full object-contain" />
                                            </div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-tom-ford.webp"
                                                    alt="TOM FORD Logo" class=" h-16 max-w-full object-contain" /></div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-versace.webp"
                                                    alt="VERSACE Logo" class=" h-16 max-w-full object-contain" /></div>
                                            <div class="flex justify-center items-center"><img
                                                    src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-ysl.webp"
                                                    alt="Yves Saint Laurent Logo"
                                                    class=" h-16 max-w-full object-contain" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <router-link to="/nuoc-hoa-nam"
                                class="text-sm font-semibold text-gray-900 hover:text-red-600 transition duration-200">Nước
                                Hoa Nam</router-link>
                            <router-link to="/nuoc-hoa-nu"
                                class="text-sm font-semibold text-gray-900 hover:text-red-600 transition duration-200">Nước
                                Hoa Nữ</router-link>
                            <router-link to="/thuong-hieu"
                                class="text-sm font-semibold text-gray-900 hover:text-red-600 transition duration-200">Thương
                                hiệu</router-link>
                            <router-link to="/nuoc-hoa-mini"
                                class="text-sm font-semibold text-gray-900 hover:text-red-600 transition duration-200">Nước
                                Hoa Mini</router-link>
                            <router-link to="/nuoc-hoa-chiet"
                                class="text-sm font-semibold text-gray-900 hover:text-red-600 transition duration-200">Nước
                                Hoa Chiết</router-link>
                            <router-link to="/nuoc-hoa-unisex"
                                class="text-sm font-semibold text-gray-900 hover:text-red-600 transition duration-200">Nước
                                Hoa Unisex</router-link>
                            <router-link to="/nuoc-hoa-niche"
                                class="text-sm font-semibold text-gray-900 hover:text-red-600 transition duration-200">Nước
                                Hoa Niche</router-link>
                            <a href="#"
                                class="text-sm font-semibold text-gray-900 hover:text-red-600 transition duration-200">
                                Kiến Thức
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <router-view></router-view>

        <footer class="bg-gray-800 text-gray-300 py-10">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4">VỀ CHÚNG TÔI</h3>
                        <p class="text-sm leading-relaxed">
                            Ra đời vào ngày 14/8/2004, hiện tại là nơi cung cấp hơn 2000 nhãn hiệu nước hoa cấp
                            tới thế giới. Là website nước hoa đầu tiên tại
                            Việt Nam. Định hướng của chúng tôi trở thành
                            nhà cung cấp nước hoa số 1 tại VN, mang lại sự
                            lựa chọn đa dạng, sự thuận tiện, tiết kiệm tiền &
                            thời gian, đồng thời mang đến sự yên tâm & hài
                            lòng đến với khách hàng.
                        </p>
                    </div>

                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4">LIÊN HỆ</h3>
                        <p class="text-sm mb-2">Giờ hoạt động: 08h - 22h hàng ngày, tất cả CN</p>
                        <p class="text-sm mb-2">TỔNG ĐÀI TƯ VẤN - 028 999 999 42</p>
                        <p class="text-sm mb-4">TỔNG ĐÀI TƯ VẤN 2 - 0977 591 562</p>
                        <p class="text-sm">Email liên hệ: <a href="mailto:cskh@orchard.vn"
                                class="text-blue-400 hover:underline">cskh@florea.vn</a></p>
                    </div>

                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4">HỖ TRỢ</h3>
                        <ul class="text-sm space-y-2">
                            <li><a href="#" class="hover:underline">Khách hàng thân thiết (Membership)</a></li>
                            <li><a href="#" class="hover:underline">Hướng dẫn đặt hàng & thanh toán</a></li>
                            <li><a href="#" class="hover:underline">Điều khoản sử dụng website</a></li>
                            <li><a href="#" class="hover:underline">Chính sách bảo mật</a></li>
                            <li><a href="#" class="hover:underline">Chính sách bảo hành & đổi trả</a></li>
                            <li><a href="#" class="hover:underline">Tuyển dụng</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4">ĐĂNG KÝ NHẬN THÔNG TIN</h3>
                        <p class="text-sm mb-4">Nhận ngay những thông tin khuyến mãi, chương trình Flash Sale mới nhất
                            từ
                            Florea.vn.</p>
                        <a href="#"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/be/Facebook_Messenger_logo_2020.svg/1200px-Facebook_Messenger_logo_2020.svg.png"
                                alt="Messenger Icon" class="w-5 h-5 mr-2">
                            Đăng ký nhận tin Messenger
                        </a>
                    </div>
                </div>

                <hr class="my-8 border-gray-700">

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4">NƯỚC HOA</h3>
                        <ul class="text-sm space-y-2">
                            <li><a href="#" class="hover:underline">Nước hoa Nam</a></li>
                            <li><a href="#" class="hover:underline">Nước hoa Nữ</a></li>
                            <li><a href="#" class="hover:underline">Nước hoa Niche</a></li>
                            <li><a href="#" class="hover:underline">Nước hoa Mini</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4">BODY CARE & MỸ PHẨM</h3>
                        <ul class="text-sm space-y-2">
                            <li><a href="#" class="hover:underline">Son môi</a></li>
                            <li><a href="#" class="hover:underline">Makeup</a></li>
                            <li><a href="#" class="hover:underline">Skincare</a></li>
                            <li><a href="#" class="hover:underline">Lăn khử mùi</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4">KẾT NỐI CÙNG FLORÉA</h3>
                        <div class="flex space-x-4">
                            <a href="#" class="text-gray-400 hover:text-white"><img
                                    src="https://www.facebook.com/images/fb_icon_325x325.png" alt="Facebook"
                                    class="w-8 h-8 rounded-full"></a>
                            <a href="#" class="text-gray-400 hover:text-white"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Instagram_logo_2016.svg/768px-Instagram_logo_2016.svg.png"
                                    alt="Instagram" class="w-8 h-8 rounded-full"></a>
                            <a href="#" class="text-gray-400 hover:text-white"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/09/YouTube_full-color_icon_%282017%29.svg/1024px-YouTube_full-color_icon_%282017%29.svg.png"
                                    alt="YouTube" class="w-8 h-8 rounded-full"></a>
                            <a href="#" class="text-gray-400 hover:text-white"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Icon_of_Zalo.svg/50px-Icon_of_Zalo.svg.png"
                                    alt="Zalo" class="w-8 h-8 rounded-full"></a>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-white text-lg font-semibold mb-4">THANH TOÁN</h3>
                        <div class="flex flex-wrap gap-2">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/960px-Visa_Inc._logo.svg.png?20170118154621"
                                alt="Visa" class="h-8">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png"
                                alt="Mastercard" class="h-8">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/40/JCB_logo.svg/300px-JCB_logo.svg.png"
                                alt="JCB" class="h-8">
                            <img src="../assets/images/categories/momo_circle_pinkbg.svg" alt="Momo" class="h-8">
                            <img src="https://cdn.brandfetch.io/idV02t6WJs/theme/dark/logo.svg?c=1bxid64Mup7aczewSAYMX&t=1742660347836"
                                alt="VNPay" class="h-8">
                        </div>
                    </div>
                </div>

                <hr class="my-8 border-gray-700">

                <div class="flex flex-col md:flex-row justify-between items-center text-sm">
                    <p>&copy; Bản quyền nội dung thuộc về Floréa &copy; 2025</p>
                    <p>Trích dẫn "florea.vn" khi sử dụng thông tin từ website này.</p>
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR0L1Y4x7f-kY8Q8Q0Q4x4Q4Q4Q4Q4Q4Q4Q4Q4Q4Q4Q4Q4Q4Q4Q&s"
                        alt="DMCA Badge" class="h-10 mt-4 md:mt-0">
                </div>
            </div>
        </footer>

    </div>
</template>
<script setup>
import { onMounted } from 'vue';
import logormbg from '@/assets/images/categories/Logo-removebg.png'


</script>
<style></style>