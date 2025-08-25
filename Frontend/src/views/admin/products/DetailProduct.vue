<template>
    <div class="container mx-auto px-4 py-8">
        <div class="page-inner">
            <div class="mb-6">
                <ul class="flex items-center space-x-2 text-gray-600 text-sm">
                    <li>
                        <router-link :to="{ name: 'AdminDashboard' }" class="hover:text-blue-800">
                            <i class="fas fa-home"></i>
                        </router-link>
                    </li>
                    <li class="separator">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </li>
                    <li>
                        <router-link :to="{ name: 'products' }" class="hover:text-blue-800">Danh sách sản phẩm</router-link>
                    </li>
                    <li>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </li>
                    <li>
                        <span class="font-semibold">{{ route.meta.title }}</span>
                    </li>
                </ul>
            </div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="text-2xl font-semibold">{{ route.meta.title }}</div>
                </div>
                <div class="card-body">
                    <form v-if="product.id">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <div class="mb-4">
                                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Tên sản phẩm</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-not-allowed" id="name" placeholder="Nhập tên sản phẩm"
                                        disabled v-model="product.name" />
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Giới tính</label>
                                    <div class="flex space-x-4">
                                        <div class="flex items-center">
                                            <input class="form-radio h-4 w-4 text-blue-600 cursor-not-allowed" type="radio" name="gender" id="male"
                                                value="male" disabled v-model="product.gender" />
                                            <label class="ml-2 text-gray-700" for="male">Nam</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input class="form-radio h-4 w-4 text-blue-600 cursor-not-allowed" type="radio" name="gender" id="female"
                                                value="female" disabled v-model="product.gender" />
                                            <label class="ml-2 text-gray-700" for="female">Nữ</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input class="form-radio h-4 w-4 text-blue-600 cursor-not-allowed" type="radio" name="gender" id="unisex"
                                                value="unisex" disabled v-model="product.gender" />
                                            <label class="ml-2 text-gray-700" for="unisex">Unisex</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label for="price" class="block text-gray-700 text-sm font-bold mb-2">Giá</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-not-allowed" id="price" placeholder="Giá sản phẩm"
                                        disabled :value="formatCurrency(product.price)" />
                                </div>
                                <div class="mb-4">
                                    <label for="categorySelect" class="block text-gray-700 text-sm font-bold mb-2">Danh mục</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-not-allowed" id="categorySelect" disabled
                                        :value="getCategoryName(product.category_id)" />
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label for="slug" class="block text-gray-700 text-sm font-bold mb-2">Slug</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-not-allowed" id="slug" placeholder="Slug sản phẩm"
                                        disabled v-model="product.slug" />
                                </div>
                                <!-- <div class="mb-4">
                                    <label for="brandSelect" class="block text-gray-700 text-sm font-bold mb-2">Thương hiệu</label>
                                    <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-not-allowed" id="brandSelect" disabled
                                        :value="getBrandName(product.brand_id)" />
                                </div> -->

                                <div class="mb-4">
                                <label for="brandSelect" class="block text-gray-700 text-sm font-bold mb-2">Thương hiệu</label>
                                <input
                                    type="text"
                                    id="brandSelect"
                                    disabled
                                    :value="product.brand ? product.brand.name : 'Đang tải...'"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-not-allowed"
                                />
                                </div>

                            </div>

                            <div v-if="product.image" class="md:col-span-2 lg:col-span-1">
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Hình ảnh chính</label>
                                    <img :src="product.image" alt="Hình ảnh chính" class="max-w-xs rounded-md mt-2 shadow-md" />
                                </div>
                            </div>
                            <div v-if="product.images && product.images.length > 0" class="md:col-span-2 lg:col-span-2">
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Thư viện ảnh sản phẩm</label>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <img v-for="image in product.images" :key="image.id"
                                            :src="image.image_url" alt="Ảnh thư viện"
                                            class="w-24 h-24 object-cover rounded-md shadow-sm" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-gray-300" />

                        <div class="mb-6">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Mô tả</label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline bg-gray-100 cursor-not-allowed" id="description" rows="5" disabled
                                v-model="product.description"></textarea>
                        </div>

                        <hr class="my-6 border-gray-300" />

                        <div class="mb-6">
                            <h5 class="text-xl font-semibold mb-3">Nhóm hương & Mức độ hương</h5>
                            <div v-if="sortedScentProfiles.length > 0">
                                <div class="space-y-2">
                                    <div v-for="scent in sortedScentProfiles" :key="scent.scent_group_id"
                                        class="flex items-center">
                                        <span class="text-gray-700 font-medium mr-2" :style="{
                                                'min-width': '120px',
                                                'max-width': '120px',
                                                'white-space': 'nowrap',
                                                'overflow': 'hidden',
                                                'text-overflow': 'ellipsis',
                                            }">{{ scent.scent_group_name || 'Không xác định' }}:</span>
                                        <div class="flex-grow h-5 bg-gray-200 rounded-md overflow-hidden">
                                            <div class="h-full flex items-center justify-center text-sm font-bold text-white" role="progressbar"
                                                :style="{ width: scent.strength + '%', backgroundColor: scent.scent_group_color_code || '#cccccc' }"
                                                :aria-valuenow="scent.strength" aria-valuemin="0" aria-valuemax="100">
                                                <span :style="{ color: isDarkColor(scent.scent_group_color_code) ? 'white' : 'black' }">
                                                    {{ scent.strength }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-500 italic">Sản phẩm này chưa có thông tin nhóm hương.</p>
                        </div>

                        <hr class="my-6 border-gray-300" />

                        <div class="mb-6">
                            <h5 class="text-xl font-semibold mb-3">Thông tin sử dụng sản phẩm</h5>
                            <div v-if="product.usage_profile">
                                <div class="mb-4">
                                    <h6 class="text-lg font-medium mb-2">Mức độ phù hợp mùa</h6>
                                    <div class="space-y-2">
                                        <div v-for="(season, key) in seasons" :key="key" class="flex items-center">
                                            <label class="text-gray-700 capitalize mr-2" style="min-width: 80px;">{{ season.label }}:</label>
                                            <div class="flex-grow h-2.5 bg-gray-200 rounded-md overflow-hidden">
                                                <div class="h-full rounded-md"
                                                    :style="{ width: (product.usage_profile[key] || 0) + '%', backgroundColor: season.color }">
                                                </div>
                                            </div>
                                            <span class="font-bold text-sm ml-2" :style="{ color: season.color }">
                                                {{ product.usage_profile[key] || 0 }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="text-lg font-medium mb-2">Mức độ phù hợp ngày/đêm</h6>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <label class="text-gray-700 mr-2" style="min-width: 80px;">Ngày:</label>
                                            <div class="flex-grow h-2.5 bg-gray-200 rounded-md overflow-hidden">
                                                <div class="h-full rounded-md"
                                                    :style="{ width: (product.usage_profile.suitable_day || 0) + '%', backgroundColor: '#FFD700' }">
                                                </div>
                                            </div>
                                            <span class="font-bold text-sm ml-2" style="color: #FFD700;">
                                                {{ product.usage_profile.suitable_day || 0 }}%
                                            </span>
                                        </div>
                                        <div class="flex items-center">
                                            <label class="text-gray-700 mr-2" style="min-width: 80px;">Đêm:</label>
                                            <div class="flex-grow h-2.5 bg-gray-200 rounded-md overflow-hidden">
                                                <div class="h-full rounded-md"
                                                    :style="{ width: (product.usage_profile.suitable_night || 0) + '%', backgroundColor: '#4682B4' }">
                                                </div>
                                            </div>
                                            <span class="font-bold text-sm ml-2" style="color: #4682B4;">
                                                {{ product.usage_profile.suitable_night || 0 }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2 flex items-center">
                                    <label class="w-1/3 text-gray-700">Độ lưu hương:</label>
                                    <div class="w-2/3">
                                        <strong class="text-gray-900">{{ product.usage_profile.longevity_hours || 'N/A' }} giờ</strong>
                                    </div>
                                </div>
                                <div class="mb-2 flex items-center">
                                    <label class="w-1/3 text-gray-700">Độ tỏa hương:</label>
                                    <div class="w-2/3">
                                        <strong class="text-gray-900">{{ product.usage_profile.sillage_range_m || 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-500 italic">Sản phẩm này chưa có thông tin sử dụng.</p>
                        </div>

                        <hr class="my-6 border-gray-300" />

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Loại sản phẩm</label>
                            <div class="flex space-x-4">
                                <div class="flex items-center">
                                    <input class="form-radio h-4 w-4 text-blue-600 cursor-not-allowed" type="radio" id="noVariants" :value="false"
                                        v-model="product.has_variants_computed" disabled />
                                    <label class="ml-2 text-gray-700" for="noVariants">Sản phẩm đơn giản</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="form-radio h-4 w-4 text-blue-600 cursor-not-allowed" type="radio" id="hasVariants" :value="true"
                                        v-model="product.has_variants_computed" disabled />
                                    <label class="ml-2 text-gray-700" for="hasVariants">Sản phẩm có biến thể</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8" v-if="product.variants && product.variants.length > 0">
                            <h4 class="text-xl font-semibold mb-4">Các Biến thể Sản phẩm</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ảnh</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Giá</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tồn kho</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Đã bán</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                            <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã vạch</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mô tả</th> -->
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thuộc tính</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="variant in product.variants" :key="variant.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <img v-if="variant.image_url" :src="variant.image_url" alt="Variant Image" class="w-12 h-12 object-cover rounded-sm" />
                                                <span v-else class="text-gray-500">N/A</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ variant.sku }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ formatCurrency(variant.price) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ variant.stock }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ variant.sold }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ variant.status }}</td>
                                            <!-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ variant.barcode || 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ variant.description || 'N/A' }}</td> -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span v-if="variant.attributes && variant.attributes.length > 0">
                                                    <ul class="list-none p-0 m-0">
                                                        <li v-for="attr in variant.attributes" :key="attr.value_id">
                                                            <strong class="text-gray-700">{{ attr.attribute_name || 'Thuộc tính' }}:</strong> {{ attr.value_name || 'Giá trị' }}
                                                        </li>
                                                    </ul>
                                                </span>
                                                <span v-else class="text-gray-500">Không có thuộc tính</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-8" v-else-if="product.has_variants_computed">
                            <p class="text-blue-500 italic">Sản phẩm này được cấu hình có biến thể nhưng chưa có dữ liệu biến thể nào.</p>
                        </div>
                        <div class="mt-8" v-else>
                            <p class="text-blue-500 italic">Sản phẩm này là sản phẩm đơn giản (không có biến thể).</p>
                        </div>

                        <div class="mt-8 flex justify-end space-x-3">
                            <router-link :to="{ name: 'products' }" class="px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                                Quay lại
                            </router-link>
                            <router-link :to="{ name: 'editProduct', params: { id: product.id } }" class="px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400 transition ease-in-out duration-150">
                                Chỉnh sửa sản phẩm
                            </router-link>
                        </div>
                    </form>
                    <div v-else class="text-center py-10">
                        <div class="animate-spin inline-block w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full" role="status">
                            <span class="sr-only">Đang tải...</span>
                        </div>
                        <p class="mt-4 text-gray-600">Đang tải thông tin sản phẩm...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';

const seasons = ref({
    spring_percent: { label: 'Xuân', color: '#8BC34A' },
    summer_percent: { label: 'Hạ', color: '#FFEB3B' },
    autumn_percent: { label: 'Thu', color: '#FF9800' },
    winter_percent: { label: 'Đông', color: '#2196F3' }
});

const route = useRoute();
const categories = ref([]);
const brands = ref([]);

const product = ref({
    id: null,
    name: "",
    slug: "",
    image: null, // Main product image URL
    images: [], // Gallery images (will be an array of objects with id, image_url)
    description: null,
    gender: "",
    price: null,
    view: 0, // Changed from 'view' to 'views' to match JSON
    brand_id: null,
    brand_name: "",
    brand_slug: "",
    category_id: null,
    category_name: "",
    category_slug: "",
    usage_profile: null, // Directly the usage_profile object
    scent_profiles: [], // Directly the array of scent profile objects
    variants: [],
});

// A computed property to infer if the product has variants
const has_variants_computed = computed(() => {
    return product.value.variants && product.value.variants.length > 0;
});


const formatCurrency = (value) => {
    // Ensure value is a number, even if it comes as a string like "200000.00"
    const numericValue = parseFloat(value);
    if (numericValue === null || isNaN(numericValue)) return 'N/A';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(numericValue);
};

const getCategoryName = (categoryId) => {
    if (!Array.isArray(categories.value) || categories.value.length === 0) return 'Đang tải...';
    const category = categories.value.find(c => c.id === categoryId);
    return category ? category.name : 'Không tìm thấy danh mục';
};

const getBrandName = (brandId) => {
    if (!Array.isArray(brands.value) || brands.value.length === 0) return 'Đang tải...';
    const brand = brands.value.find(b => b.id === brandId);
    return brand ? brand.name : 'Không tìm thấy thương hiệu';
};

const fetchCategory = async () => {
    try {
        const { data } = await axios.get(`http://localhost:8000/api/admin/categories`);
        categories.value = data.data;
    } catch (error) {
        console.error('Lỗi khi tải danh mục:', error);
    }
};

const fetchBrand = async () => {
    try {
        const { data } = await axios.get('http://localhost:8000/api/admin/brands');
        brands.value = data.data;
    } catch (error) {
        console.error('Lỗi khi tải thương hiệu:', error);
    }
};

const fetchProduct = async () => {
    try {
        const response = await axios.get(`http://localhost:8000/api/admin/products/${route.params.id}`);
        const fetchedProductData = response.data.data; // Access the 'data' key as per your JSON structure

        // Directly assign all properties from fetchedProductData
        product.value = {
            ...product.value, // Keep defaults for properties not in API response (e.g., if null)
            ...fetchedProductData, // Overwrite with API data
            // Ensure numeric values are parsed correctly if they come as strings
            price: parseFloat(fetchedProductData.price),
            view: parseInt(fetchedProductData.view),
            // Ensure usage_profile and scent_profiles are objects/arrays, even if null from API
            usage_profile: fetchedProductData.usage_profile || {
                spring_percent: 0, summer_percent: 0, autumn_percent: 0, winter_percent: 0,
                suitable_day: 0, suitable_night: 0, longevity_hours: 0.0, sillage_range_m: '',
            },
            scent_profiles: fetchedProductData.scent_profiles || [],
            images: fetchedProductData.images || [], // Initialize images array
            variants: fetchedProductData.variants || [], // Initialize variants array
        };

    } catch (error) {
        console.error('Không lấy được sản phẩm:', error);
        Swal.fire('Lỗi!', 'Không thể tải thông tin sản phẩm.', 'error');
    }
};

const sortedScentProfiles = computed(() => {
    if (!Array.isArray(product.value.scent_profiles) || product.value.scent_profiles.length === 0) return [];

    // The data already contains scent_group_name, color_code, and strength directly
    const scentProfilesArray = product.value.scent_profiles
        .map(sp => ({
            scent_group_id: sp.scent_group_id,
            scent_group_name: sp.scent_group_name,
            scent_group_color_code: sp.scent_group_color_code,
            strength: sp.strength,
        }))
        .filter(scent => scent.strength > 0);

    return scentProfilesArray.sort((a, b) => b.strength - a.strength);
});

const isDarkColor = (hexColor) => {
    if (!hexColor || hexColor.length < 7) return true;
    const r = parseInt(hexColor.substring(1, 3), 16);
    const g = parseInt(hexColor.substring(3, 5), 16);
    const b = parseInt(hexColor.substring(5, 7), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance <= 0.5;
};

onMounted(async () => {
    await Promise.all([
        fetchCategory(),
        fetchBrand(),
    ]);
    await fetchProduct();
});
</script>