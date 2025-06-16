<template>
    <div v-for="categoryData in categoriesData" :key="categoryData.category_slug"
        class="box-products-1 mx-auto max-w-[1200px] px-4 mb-8">
        <div class="py-4 px-4 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">{{ categoryData.category_name }}</h2>
            <a :href="`/category/${categoryData.category_slug}`"
                class="text-blue-700 text-base hover:text-blue-400 flex items-center">
                Xem thêm {{ categoryData.category_name.toLowerCase() }}
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            <div v-for="product in categoryData.products" :key="product.id"
                class="border border-gray-200 rounded-lg p-4 flex flex-col items-center text-center bg-white relative hover:shadow-md transition-shadow duration-300">
                <span v-if="product.discount_percentage"
                    class="absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                    -{{ product.discount_percentage }}%
                </span>
                <img src="https://via.placeholder.com/150" alt="NARCISO RODRIGUEZ For Her EDP"
                    class="w-28 h-28 object-contain mb-2">
                <!-- <img :src="product.images[0]?.url || 'https://via.placeholder.com/150'" :alt="product.name"
             class="w-28 h-28 object-contain mb-2"> -->
                <p class="text-xs text-gray-500 mb-1">
                    <!-- <span v-for="(variant, index) in product.variants" :key="variant.id">
            {{ variant.size }} <span v-if="index < product.variants.length - 1"> </span>
          </span> -->
                </p>
                <p class="text-sm font-semibold mb-1 truncate w-full">{{ product.brand.name }}</p>
                <p class="text-xs font-medium mb-1 truncate w-full">{{ product.name }}</p>

                <p v-if="product.old_price" class="text-xs text-gray-400 line-through mt-2">
                    {{ new Intl.NumberFormat('vi-VN').format(product.old_price) }} ₫
                </p>
                <p class="text-md font-bold text-red-600">
                    {{ new Intl.NumberFormat('vi-VN').format(product.price) }} ₫
                </p>
            </div>
        </div>
    </div>

    <div v-if="loading" class="text-center py-8">Đang tải sản phẩm...</div>
    <div v-if="error" class="text-center py-8 text-red-600">Đã xảy ra lỗi khi tải dữ liệu: {{ error }}</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const categoriesData = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchProducts = async () => {
    try {
        const response = await fetch('http://localhost:8000/api/most-viewed-products-by-categories'); // Đảm bảo URL API của bạn đúng
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        categoriesData.value = await response.json();
    } catch (err) {
        error.value = err.message;
        console.error("Lỗi khi tải dữ liệu sản phẩm:", err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchProducts();
});
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Bạn có thể thêm các style tùy chỉnh nếu cần, nhưng Tailwind CSS đã xử lý phần lớn */
</style>