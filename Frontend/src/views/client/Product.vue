<style scoped>
@import '@/assets/tailwind.css';
</style>
<template>
    <div class="container mx-auto p-4 max-w-[1230px]">
        <div class="flex flex-col lg:flex-row justify-between gap-8">
            <div class="w-full lg:max-w-[920px] lg:flex-shrink-0">
                <div class="flex flex-col lg:flex-row gap-8">
                    <ProductCarousel :productImages="myProductImages" />
                    <div class="flex-grow">
                        <h1 class="text-3xl font-semibold mb-2">Narciso Rodriguez For Her EDP</h1>
                        <div class="flex items-center mb-4">
                            <div class="flex text-yellow-400 mr-2">
                                <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9734;</span>
                            </div>
                            <span class="text-sm text-gray-600">(9 đánh giá)</span>
                            <span class="mx-2 text-gray-300">|</span>
                            <span class="text-sm text-gray-600">Đã bán 2.5K</span>
                        </div>
                        <p class="text-gray-700 mb-6">Tỏa sáng trong mọi buổi tiệc, sự kiện, hay buổi hẹn hò với hương
                            hoa cỏ, gỗ và xạ hương quyến rũ, thanh lịch, giúp bạn tự tin và nổi bật.</p>
                        <div class="mb-4">
                            <span class="font-semibold text-gray-600">Thương hiệu:</span>
                            <a href="#" class="text-blue-600 hover:underline ml-2">Narciso Rodriguez</a>
                        </div>
                        <div class="mb-4">
                            <span class="font-semibold text-gray-600">Loại sản phẩm:</span>
                            <a href="#" class="text-blue-600 hover:underline ml-2">Top Nước Hoa Nữ Được Yêu Thích Nhất
                                Mọi Thời Đại</a>,
                            <a href="#" class="text-blue-600 hover:underline ml-1">Top Nước Hoa Được Yêu Thích Nhất Mọi
                                Thời Đại</a>,
                            <a href="#" class="text-blue-600 hover:underline ml-1">Nước Hoa Nữ</a>
                        </div>
                        <div class="mb-6">
                            <span class="font-semibold text-gray-600">Tình trạng:</span>
                            <span class="text-green-600 font-bold ml-2">Còn hàng</span>
                        </div>
                        <div class="mb-6">
                            <span class="font-semibold text-gray-600">Mã vạch:</span>
                            <img src="https://i.imgur.com/barcode-placeholder.png" alt="Barcode"
                                class="inline-block ml-2 w-24 h-auto" />
                        </div>
                        <div class="text-4xl font-bold text-red-500 mb-2">2.680.000 ₫</div>
                        <p class="text-sm text-gray-500 mb-6">Giá cho Khách hàng thân thiết <i
                                class="fas fa-chevron-down ml-1"></i></p>
                        <div class="mb-6">
                            <h3 class="font-semibold text-gray-700 mb-3">Bạn đang xem For Her</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <button
                                    class="flex flex-col items-center p-2 border border-gray-300 rounded-lg hover:border-pink-500">
                                    <img src="https://i.imgur.com/forher-chiet-placeholder.png" alt="For Her (chiết)"
                                        class="w-16 h-16 object-contain mb-1" />
                                    <span class="text-sm">For Her <br>(chiết)</span>
                                </button>
                                <button
                                    class="flex flex-col items-center p-2 border border-gray-300 rounded-lg hover:border-pink-500">
                                    <img src="https://i.imgur.com/forher-mini-placeholder.png" alt="For Her (mini)"
                                        class="w-16 h-16 object-contain mb-1" />
                                    <span class="text-sm">For Her <br>(mini)</span>
                                </button>
                                <button
                                    class="flex flex-col items-center p-2 border border-pink-500 bg-pink-50 rounded-lg">
                                    <img src="https://i.imgur.com/forher-selected-placeholder.png" alt="For Her"
                                        class="w-16 h-16 object-contain mb-1" />
                                    <span class="text-sm font-semibold text-pink-700">For Her</span>
                                </button>
                                <button
                                    class="flex flex-col items-center p-2 border border-gray-300 rounded-lg hover:border-pink-500">
                                    <img src="https://i.imgur.com/puremusc-placeholder.png" alt="Pure Musc"
                                        class="w-16 h-16 object-contain mb-1" />
                                    <span class="text-sm">Pure Musc</span>
                                </button>
                                <button
                                    class="flex flex-col items-center p-2 border border-gray-300 rounded-lg hover:border-pink-500">
                                    <img src="https://i.imgur.com/muscnoir-placeholder.png" alt="Musc Noir"
                                        class="w-16 h-16 object-contain mb-1" />
                                    <span class="text-sm">Musc Noir</span>
                                </button>
                                <button
                                    class="flex flex-col items-center p-2 border border-gray-300 rounded-lg hover:border-pink-500">
                                    <img src="https://i.imgur.com/fleurmusc-placeholder.png" alt="Fleur Musc"
                                        class="w-16 h-16 object-contain mb-1" />
                                    <span class="text-sm">Fleur Musc</span>
                                </button>
                            </div>
                        </div>
                        <div class="mb-8">
                            <h3 class="font-semibold text-gray-700 mb-3">Lựa chọn : 150 ML <span
                                    class="text-gray-500">(17.867 ₫/mL - 1500 lần xịt trong 18 tháng)</span></h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <button class="p-3 border border-gray-300 rounded-lg text-center hover:border-pink-500">
                                    <div class="font-semibold">30 ML</div>
                                    <div class="text-sm text-gray-600">1.290.000 ₫</div>
                                </button>
                                <button class="p-3 border border-gray-300 rounded-lg text-center hover:border-pink-500">
                                    <div class="font-semibold">50 ML</div>
                                    <div class="text-sm text-gray-600">1.580.000 ₫</div>
                                </button>
                                <button class="p-3 border border-gray-300 rounded-lg text-center hover:border-pink-500">
                                    <div class="font-semibold">100 ML</div>
                                    <div class="text-sm text-gray-600">2.080.000 ₫</div>
                                </button>
                                <button class="p-3 border border-pink-500 bg-pink-50 rounded-lg text-center col-span-1">
                                    <div class="font-semibold text-pink-700">150 ML</div>
                                    <div class="text-sm text-pink-700">2.680.000 ₫</div>
                                </button>
                            </div>
                        </div>
                        <div
                            class="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded-lg flex items-center mb-6">
                            <i class="fas fa-gift mr-2"></i>
                            <span>Giảm <span class="font-bold">100K</span> khi thanh toán qua Fundiin (<a href="#"
                                    class="underline">xem thêm</a>)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-[310px] lg:flex-shrink-0">
                <div class="p-4 border border-gray-200 rounded-lg shadow-sm">
                    <button class="w-full py-2 px-4 border border-gray-300 rounded-lg text-gray-700 font-semibold mb-6">
                        narciso rodriguez
                    </button>
                    <h3 class="text-center font-bold text-gray-800 mb-4">MÙI HƯƠNG CHÍNH (ACCORDS)</h3>
                    <p class="text-center text-gray-500 text-xs italic mb-6">
                        (*click tên nhóm hương để tìm hiểu chi tiết)
                    </p>
                    <div class="space-y-3 mb-8">
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-purple-200 text-purple-800 text-xs font-medium flex items-center pl-3"
                                style="width: 80%;">
                                xạ hương
                            </div>
                        </div>
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-pink-500 text-white text-xs font-medium flex items-center pl-3"
                                style="width: 95%;">
                                hoa hồng
                            </div>
                        </div>
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-pink-300 text-pink-800 text-xs font-medium flex items-center pl-3"
                                style="width: 75%;">
                                hương phấn
                            </div>
                        </div>
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-amber-700 text-white text-xs font-medium flex items-center pl-3"
                                style="width: 60%;">
                                gỗ thơm
                            </div>
                        </div>
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-green-700 text-white text-xs font-medium flex items-center pl-3"
                                style="width: 55%;">
                                hoắc hương
                            </div>
                        </div>
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-orange-600 text-white text-xs font-medium flex items-center pl-3"
                                style="width: 45%;">
                                cay ấm
                            </div>
                        </div>
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-orange-400 text-orange-900 text-xs font-medium flex items-center pl-3"
                                style="width: 40%;">
                                trái cây
                            </div>
                        </div>
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-pink-200 text-pink-800 text-xs font-medium flex items-center pl-3"
                                style="width: 35%;">
                                hổ phách
                            </div>
                        </div>
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-pink-300 text-pink-800 text-xs font-medium flex items-center pl-3"
                                style="width: 30%;">
                                hoa cỏ
                            </div>
                        </div>
                        <div class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full bg-pink-200 text-pink-800 text-xs font-medium flex items-center pl-3"
                                style="width: 25%;">
                                ngọt ngào
                            </div>
                        </div>
                    </div>
                    <div class="text-center mb-6">
                        <img src="https://i.imgur.com/fragrantica-logo-placeholder.png" alt="Fragrantica Logo"
                            class="mx-auto h-8 mb-2" />
                        <span class="font-semibold text-gray-700 text-lg">FRAGRANTICA</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#10052;</div>
                            <div class="text-sm font-semibold text-gray-700">Đông</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-gray-500 rounded-full" style="width: 40%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#127807;</div>
                            <div class="text-sm font-semibold text-gray-700">Xuân</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-green-500 rounded-full" style="width: 60%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#9728;</div>
                            <div class="text-sm font-semibold text-gray-700">Hè</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-red-500 rounded-full" style="width: 80%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#127809;</div>
                            <div class="text-sm font-semibold text-gray-700">Thu</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-orange-500 rounded-full" style="width: 70%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#9728;</div>
                            <div class="text-sm font-semibold text-gray-700">Ngày</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-yellow-500 rounded-full" style="width: 90%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#127769;</div>
                            <div class="text-sm font-semibold text-gray-700">Đêm</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-gray-500 rounded-full" style="width: 50%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#128337;</div>
                            <div class="text-sm font-semibold text-gray-700">Lưu <br> đến 12h</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-green-500 rounded-full" style="width: 85%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#128100;</div>
                            <div class="text-sm font-semibold text-gray-700">Toả <br> ~ 1 mét</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-red-500 rounded-full" style="width: 70%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ProductDescription/>
        <ProductReview/>
        <RelatedProduct/>
    </div>
</template>

<script setup>
import ProductReview from '@/components/ProductReview.vue'
import ProductDescription from '@/components/ProductDescription.vue';
import ProductCarousel from '@/components/ProductCarousel.vue';
import RelatedProduct from '@/components/RelatedProduct.vue';
import { ref } from 'vue';
const props = defineProps({
    productImages: {
        type: Array,
        required: true,
        default: () => []
    }
});
// Dữ liệu hình ảnh cho carousel
const myProductImages = ref([
    'https://placehold.co/600x400/EEE/31343C',
    'https://www.svgrepo.com/show/508699/landscape-placeholder.svg',
    'https://via.placeholder.com/600x400/3357FF/FFFFFF?text=Product+Image+C',
    'https://via.placeholder.com/600x400/FF33A1/FFFFFF?text=Product+Image+D'
]);

</script>

<style>
/* Không cần CSS đặc biệt cho carousel track ngoài các class Tailwind đã áp dụng */
/* .carousel-track đã có transition-transform duration-500 ease-in-out */
</style>