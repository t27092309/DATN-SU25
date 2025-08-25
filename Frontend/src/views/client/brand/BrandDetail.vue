<template>
    <div class="container mx-auto p-4 max-w-[1200px]">
        <div v-if="loading" class="text-center text-gray-500 text-lg">Đang tải thông tin thương hiệu...</div>
        <div v-else-if="error" class="text-center text-red-500 text-lg">{{ error }}</div>
        
        <div v-else-if="brand">
            <h1 class="text-4xl font-bold mb-4">{{ brand.name }}</h1>
            <img :src="brand.logo" :alt="brand.name" class="w-64 h-64 object-contain my-4">

            <div class="mb-8">
                <h2 class="text-2xl font-semibold mb-4">Sản phẩm nổi bật</h2>
                <div v-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div v-for="product in products" :key="product.id" class="overflow-hidden shadow-lg">
                        <img :src="product.image" :alt="product.name" class="w-full h-48 object-cover">
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2">{{ product.name }}</h3>
                            <p class="text-gray-700">{{ product.price }} VNĐ</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-gray-500 italic">Không có sản phẩm nào cho thương hiệu này.</div>
            </div>

            <div v-if="brand.description" class="text-gray-700 mt-4" v-html="brand.description"></div>
            <p v-else class="text-gray-500 italic">Không có mô tả cho thương hiệu này.</p>
        </div>
        
        <div v-else>
            Không tìm thấy thương hiệu.
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const brand = ref(null);
const products = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchBrandDetail = async () => {
    loading.value = true;
    error.value = null;
    try {
        const slug = route.params.slug;
        
        // 1. Gọi API để lấy thông tin chi tiết của thương hiệu
        const brandResponse = await axios.get(`client/brands/slug/${slug}`);
        brand.value = brandResponse.data;

        // 2. Gọi API để lấy danh sách sản phẩm của thương hiệu đó
        const productsResponse = await axios.get(`client/brands/${slug}/products`);
        products.value = productsResponse.data;

    } catch (err) {
        console.error("Lỗi khi tải dữ liệu:", err);
        error.value = "Không thể tìm thấy thông tin thương hiệu hoặc sản phẩm.";
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchBrandDetail();
});
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>