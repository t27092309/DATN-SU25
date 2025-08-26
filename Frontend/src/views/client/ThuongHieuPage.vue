<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const brands = ref([]);
const loading = ref(true);
const error = ref(null);

const router = useRouter();

const goToBrandDetail = (brand) => {
    router.push({
        name: 'BrandDetail',
        params: { slug: brand.slug }
    });
};

const fetchBrands = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('brands');
        brands.value = response.data;
    } catch (err) {
        // --- Thêm dòng này để in ra toàn bộ đối tượng lỗi ---
        console.error("Lỗi khi lấy dữ liệu thương hiệu:", err);

        error.value = "Không thể tải danh sách thương hiệu. Vui lòng thử lại sau.";
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchBrands();
});
</script>

<template>
    <div class="container mx-auto p-4 max-w-[1200px]">
        <p class="font-bold text-3xl text-black">Thương hiệu</p>
        <nav class="text-sm text-gray-500 mb-6">
            <ul class="flex items-center space-x-1">
                <li class="flex items-center">
                    <router-link to="/" class="text-base hover:text-gray-700 transition-colors duration-200">Trang
                        chủ</router-link>
                </li>
                <li class="flex items-center">
                    <span class="mx-2 text-gray-400">/</span>
                    <router-link to="nuoc-hoa" class="text-base hover:text-gray-700 transition-colors duration-200">Nước
                        hoa</router-link>
                </li>
                <li class="flex items-center">
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-900 font-bold text-base">Thương hiệu nước hoa</span>
                </li>
            </ul>
        </nav>
        <h2 class="text-3xl font-bold text-center mb-10">Thương hiệu Nước hoa Nổi bật</h2>

        <div v-if="loading" class="text-center text-gray-500 text-lg">
            Đang tải thương hiệu...
        </div>

        <div v-else-if="error" class="text-center text-red-500 text-lg">
            {{ error }}
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-5">
            <div v-for="brand in brands" :key="brand.slug"
                class="flex flex-col items-center p-2 bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 cursor-pointer"
                @click="goToBrandDetail(brand)">
                <!-- Khung chứa ảnh -->
                <div class="w-full flex items-center justify-center">
                    <img :src="brand.logo" :alt="brand.name" class="max-h-full object-contain">
                </div>
            </div>

        </div>
    </div>
</template>