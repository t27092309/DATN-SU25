<template>
    <div class="container mx-auto p-4 max-w-[1200px]">
        <div v-if="loadingBrand" class="text-center text-gray-500 text-lg">Đang tải thông tin thương hiệu...</div>
        <div v-else-if="error" class="text-center text-red-500 text-lg">{{ error }}</div>

        <div v-else-if="brand">
            <p class="font-bold text-3xl text-black">Nước hoa {{ brand.name }}</p>
            <nav class="text-sm text-gray-500 mb-6">
                <ul class="flex items-center space-x-1">
                    <li class="flex items-center">
                        <router-link to="/" class="text-base hover:text-gray-700 transition-colors duration-200">Trang
                            chủ</router-link>
                    </li>
                    <li class="flex items-center">
                        <span class="mx-2 text-gray-400">/</span>
                        <router-link to="nuoc-hoa"
                            class="text-base hover:text-gray-700 transition-colors duration-200">Nước hoa</router-link>
                    </li>
                    <li class="flex items-center">
                        <span class="mx-2 text-gray-400">/</span>
                        <router-link to="/thuong-hieu"
                            class="text-base hover:text-gray-700 transition-colors duration-200">Thương hiệu nước
                            hoa</router-link>
                    </li>
                    <li class="flex items-center">
                        <span class="mx-2 text-gray-400">/</span>
                        <span class="text-gray-900 font-bold text-base">Nước hoa {{ brand.name }}</span>
                    </li>
                </ul>
            </nav>

            <div class="flex flex-col gap-8 mt-5">
                <div class="mb-8">
                    <ProductFilters :priceRanges="priceRanges" :selectedPriceRange="selectedPriceRange"
                        :aromaOptions="aromaOptions" :selectedAromas="selectedAromas"
                        @select-price-range="handleSelectPriceRange" @select-aroma="handleSelectAroma" />
                </div>
                <div class="p-6 rounded-lg min-h-[300px]">
                    <p v-if="selectedPriceRange" class="mb-2">Phạm vi giá đã chọn: <strong>{{ selectedPriceRange
                            }}</strong></p>
                    <div v-if="selectedAromas.length > 0" class="mb-2">
                        <p class="font-medium">Nhóm Hương:</p>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <span v-for="aroma in selectedAromas" :key="aroma"
                                class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">
                                {{ aroma }}
                            </span>
                        </div>
                    </div>
                    <div v-if="loadingProducts" class="text-center py-4">Đang tải sản phẩm...</div>
                    <div v-else-if="errorProducts" class="error text-red-600 text-center py-4">{{ errorProducts }}</div>

                    <div v-else-if="filteredProducts.length"
                        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
                        <router-link v-for="product in filteredProducts" :key="product.slug || product.id"
                            :to="{ name: 'ProductDetail', params: { slug: product.slug || product.id } }"
                            class="block p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
                            <img :src="product.image" :alt="product.name" class="w-full h-48 object-cover rounded-t-lg">
                            <h5 class="text-md font-semibold mt-2">{{ product.name }}</h5>
                            <p class="text-gray-700">{{ product.brand ? product.brand.name : 'N/A' }}</p>
                            <p class="text-lg font-bold text-red-600">{{ new Intl.NumberFormat('vi-VN').format(product.price) }} VNĐ
                            </p>
                        </router-link>
                    </div>
                    <p v-else class="text-center py-4">Không có sản phẩm nào phù hợp với bộ lọc.</p>
                </div>
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
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import ProductFilters from '@/components/ProductFilter.vue';

const route = useRoute();
const brand = ref(null); // Để lưu thông tin thương hiệu
const products = ref([]); // Để lưu danh sách sản phẩm
const loadingBrand = ref(true); // Biến riêng cho trạng thái tải brand
const loadingProducts = ref(true); // Biến riêng cho trạng thái tải products
const error = ref(null);
const errorProducts = ref(null);

const priceRanges = ref([
    { label: 'Dưới 2 Triệu', value: 'under_2' },
    { label: '2 - 4 Triệu', value: '2_4' },
    { label: 'Trên 4 Triệu', value: 'over_4' },
]);
const aromaOptions = ref([
    'Hương hoa cỏ', 'Hương gỗ', 'Hương phương Đông', 'Hương trái cây', 'Hương cam chanh',
    'Hương gia vị', 'Hương da thuộc', 'Hương biển', 'Hương Fougere',
]);
const selectedPriceRange = ref(null);
const selectedAromas = ref([]);

const getPriceRange = (range) => {
    switch (range) {
        case 'under_2': return [0, 2000000];
        case '2_4': return [2000000, 4000000];
        case 'over_4': return [4000000, Infinity];
        default: return [null, null];
    }
};

const filteredProducts = computed(() => {
    // 1. Declare 'result' at the beginning to ensure it's always defined.
    let result = [];

    // 2. Add a safety check to make sure products.value is an array before iterating.
    if (Array.isArray(products.value)) {
        result = [...products.value];
    } else {
        return []; // Return an empty array if products.value is not valid.
    }

    if (selectedPriceRange.value) {
        const [min, max] = getPriceRange(selectedPriceRange.value);
        result = result.filter(product => {
            const price = parseFloat(product.price);
            return !isNaN(price) && (min === null || price >= min) && (max === null || price <= max);
        });
    }

    if (selectedAromas.value.length > 0) {
        result = result.filter(product => {
            if (!product.scent_profiles || product.scent_profiles.length === 0) {
                return false;
            }
            const productAromas = product.scent_profiles.map(
                (profile) => profile.scent_group_name
            );
            return selectedAromas.value.some(aroma => productAromas.includes(aroma));
        });
    }

    // 3. Return the final filtered result.
    return result;
});

const fetchBrandDetail = async () => {
    loadingBrand.value = true;
    error.value = null;
    const slug = route.params.slug;

    try {
        const brandResponse = await axios.get(`brands/${slug}`);
        brand.value = brandResponse.data;
    } catch (err) {
        console.error("Error fetching brand details:", err);
        error.value = "Không thể tìm thấy thông tin thương hiệu.";
    } finally {
        loadingBrand.value = false;
    }
};

const fetchProductsByBrand = async () => {
    loadingProducts.value = true;
    errorProducts.value = null;
    const slug = route.params.slug;

    try {
        const productsResponse = await axios.get(`brands/${slug}/products`);
        // Sửa dòng này để lấy dữ liệu từ trường 'data'
        products.value = productsResponse.data.data;
    } catch (err) {
        console.error("Error fetching products:", err);
        errorProducts.value = "Không có sản phẩm nào cho thương hiệu này.";
        products.value = []; // Đảm bảo products.value là một mảng rỗng khi có lỗi
    } finally {
        loadingProducts.value = false;
    }
};

const handleSelectPriceRange = (range) => {
    selectedPriceRange.value = range;
};

const handleSelectAroma = (aromas) => {
    selectedAromas.value = aromas;
};

onMounted(() => {
    fetchBrandDetail();
    fetchProductsByBrand();
});
</script>