<template>
    <div class="container mx-auto px-4 py-8">
        <div class="space-y-6">
            <div class="mb-6">
                <h3 class="text-3xl font-bold text-gray-900 mb-3">{{ route.meta.title }}</h3>
                <ul class="flex items-center space-x-2 text-sm text-gray-600">
                    <li>
                        <router-link :to="{ name: 'AdminDashboard' }" class="text-blue-600 hover:text-blue-800">
                            <i class="fa fa-home"></i>
                        </router-link>
                    </li>
                    <li>
                        <i class="fa fa-chevron-right text-xs"></i>
                    </li>
                    <li>
                        <router-link :to="{ name: 'products' }" class="text-blue-600 hover:text-blue-800">Danh sách sản phẩm</router-link>
                    </li>
                    <li>
                        <i class="fa fa-chevron-right text-xs"></i>
                    </li>
                    <li>
                        <router-link :to="{ name: 'addProduct' }" class="text-gray-800">{{ route.meta.title }}</router-link>
                    </li>
                </ul>
            </div>

            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h4 class="text-lg font-semibold text-gray-800">{{ route.meta.title }}</h4>
                </div>
                <div class="p-6">
                    <form @submit.prevent="updateProduct" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tên sản phẩm</label>
                                <input type="text" id="name" placeholder="Nhập tên sản phẩm"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    v-model="product.name" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giới tính</label>
                                <div class="flex items-center space-x-6">
                                    <div class="flex items-center">
                                        <input type="radio" name="gender" id="male" value="male"
                                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                            v-model="product.gender" />
                                        <label for="male" class="ml-2 block text-sm text-gray-900">Nam</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="gender" id="female" value="female"
                                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                            v-model="product.gender" />
                                        <label for="female" class="ml-2 block text-sm text-gray-900">Nữ</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="radio" name="gender" id="unisex" value="unisex"
                                            class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                            v-model="product.gender" />
                                        <label for="unisex" class="ml-2 block text-sm text-gray-900">Unisex</label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Giá</label>
                                <input type="number" id="price" placeholder="Nhập giá sản phẩm"
                                    step="0.01" inputmode="decimal" v-model.number="product.price"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                            </div>

                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
                                <select id="category_id" v-model="product.category_id"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="" disabled>Chọn danh mục</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                                <input type="text" id="slug" placeholder="Nhập tên slug"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    v-model="product.slug" />
                            </div>

                            <div>
                                <label for="brandSelect" class="block text-sm font-medium text-gray-700 mb-1">Thương hiệu <span class="text-red-500">*</span></label>
                                <select id="brandSelect" v-model="product.brand_id"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    v-if="brands && brands.length > 0">
                                    <option value="">Chọn thương hiệu</option>
                                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                        {{ brand.name }}
                                    </option>
                                </select>
                                <p v-else class="text-gray-500 text-sm mt-1">Đang tải thương hiệu...</p>
                                <p v-if="errors.brand_id" class="text-red-500 text-xs mt-1">{{ errors.brand_id[0] }}</p>
                            </div>

                            <div class="lg:col-span-1">
                                <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Hình ảnh</label>
                                <input type="file" id="image" @change="onFileChange" accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-3" />
                                <div class="flex items-center space-x-4">
                                    <img v-if="currentImageUrl" :src="currentImageUrl" alt="Product Image"
                                        class="w-32 h-32 object-cover rounded-md border border-gray-300 shadow-sm" />
                                    <span v-else class="text-gray-500 text-sm">Không có ảnh hiện tại</span>
                                </div>
                                <div class="flex items-center mt-2">
                                    <input type="checkbox" id="removeMainImage"
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        v-model="removeMainImage">
                                    <label for="removeMainImage" class="ml-2 block text-sm text-gray-900">Xóa ảnh chính</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-span-full">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
                            <textarea id="description" rows="5"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                v-model="product.description"></textarea>
                        </div>

                        <div class="col-span-full">
                            <ScentGroupSelector
                                v-model:selectedScentGroupIds="product.scentGroups.selectedScentGroupIds"
                                v-model:scentGroupsData="product.scentGroups.scentGroupsData"
                                :allScentGroups="allScentGroups" />
                            <p v-if="errors.scent_groups" class="text-red-500 text-xs mt-1">{{ errors.scent_groups[0] }}</p>

                            <div v-if="sortedScentProfiles.length > 0" class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <h6 class="text-base font-semibold text-gray-800 mb-3">Mức độ hương:</h6>
                                <div class="space-y-3">
                                    <div v-for="scent in sortedScentProfiles" :key="scent.scent_group_id"
                                        class="flex items-center">
                                        <span class="text-sm font-medium text-gray-700 flex-shrink-0"
                                            :style="{
                                                'min-width': '120px',
                                                'max-width': '120px',
                                                'white-space': 'nowrap',
                                                'overflow': 'hidden',
                                                'text-overflow': 'ellipsis',
                                            }">{{ scent.scent_group_name }}:</span>
                                        <div class="relative flex-grow h-5 bg-gray-200 rounded-full overflow-hidden ml-4">
                                            <div class="absolute inset-0 rounded-full flex items-center justify-center transition-all duration-200 ease-in-out" role="progressbar"
                                                :style="{ width: scent.strength + '%', backgroundColor: scent.scent_group_color_code }"
                                                :aria-valuenow="scent.strength" aria-valuemin="0"
                                                aria-valuemax="100">
                                                <span class="text-xs font-semibold"
                                                    :style="{ color: isDarkColor(scent.scent_group_color_code) ? 'white' : 'black' }">
                                                    {{ scent.strength }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-6 border-gray-300" />

                        <div class="col-span-full">
                            <UsageProfile v-model:usageProfileData="product.usageProfile" />
                            <p v-if="errors.usage_profile" class="text-red-500 text-xs mt-1">{{ errors.usage_profile[0] }}</p>
                        </div>

                        <hr class="my-6 border-gray-300" />

                        <div class="col-span-full">
                            <h4 class="text-xl font-semibold text-gray-800 mb-4">Biến thể Sản phẩm</h4>
                            <button type="button" @click="addVariant"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 mb-4">
                                <i class="fa fa-plus mr-2"></i> Thêm biến thể
                            </button>

                            <div v-if="product.variants && product.variants.length > 0" class="space-y-6">
                                <div v-for="(variant, vIndex) in product.variants" :key="variant.id || vIndex"
                                    class="bg-white rounded-lg shadow-md border border-gray-200">
                                    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                        <h5 class="text-md font-medium text-gray-700">Biến thể #{{ vIndex + 1 }}</h5>
                                        <button type="button" @click="removeVariant(vIndex)"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Xóa biến thể
                                        </button>
                                    </div>
                                    <div class="p-4 space-y-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label :for="`sku-${vIndex}`" class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                                                <input type="text" :id="`sku-${vIndex}`" v-model="variant.sku" placeholder="SKU của biến thể"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <p v-if="errors[`variants.${vIndex}.sku`]" class="text-red-500 text-xs mt-1">{{ errors[`variants.${vIndex}.sku`][0] }}</p>
                                            </div>
                                            <div>
                                                <label :for="`variant-price-${vIndex}`" class="block text-sm font-medium text-gray-700 mb-1">Giá biến thể</label>
                                                <input type="number" :id="`variant-price-${vIndex}`" v-model.number="variant.price" step="0.01" placeholder="Giá của biến thể"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <p v-if="errors[`variants.${vIndex}.price`]" class="text-red-500 text-xs mt-1">{{ errors[`variants.${vIndex}.price`][0] }}</p>
                                            </div>
                                            <div>
                                                <label :for="`stock-${vIndex}`" class="block text-sm font-medium text-gray-700 mb-1">Tồn kho</label>
                                                <input type="number" :id="`stock-${vIndex}`" v-model.number="variant.stock" placeholder="Số lượng tồn kho"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                <p v-if="errors[`variants.${vIndex}.stock`]" class="text-red-500 text-xs mt-1">{{ errors[`variants.${vIndex}.stock`][0] }}</p>
                                            </div>
                                            <div>
                                                <label :for="`barcode-${vIndex}`" class="block text-sm font-medium text-gray-700 mb-1">Mã vạch</label>
                                                <input type="text" :id="`barcode-${vIndex}`" v-model="variant.barcode" placeholder="Mã vạch (nếu có)"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            </div>
                                            <div class="col-span-full">
                                                <label :for="`status-${vIndex}`" class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                                                <select :id="`status-${vIndex}`" v-model="variant.status"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                    <option value="available">Có sẵn</option>
                                                    <option value="discontinued">Ngừng sản xuất</option>
                                                    <option value="out_of_stock">Hết hàng</option>
                                                </select>
                                                <p v-if="errors[`variants.${vIndex}.status`]" class="text-red-500 text-xs mt-1">{{ errors[`variants.${vIndex}.status`][0] }}</p>
                                            </div>
                                            <div class="col-span-full">
                                                <label :for="`variant-description-${vIndex}`" class="block text-sm font-medium text-gray-700 mb-1">Mô tả biến thể (tùy chọn)</label>
                                                <textarea :id="`variant-description-${vIndex}`" rows="2" v-model="variant.description"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                            </div>
                                        </div>

                                        <h5 class="text-base font-semibold text-gray-800 mt-4 mb-2">Thuộc tính của biến thể</h5>
                                        <button type="button" @click="addAttributeToVariant(vIndex)"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400 mb-3">
                                            <i class="fa fa-plus mr-2"></i> Thêm thuộc tính
                                        </button>

                                        <div v-for="(attrVal, avIndex) in variant.attributes" :key="attrVal.value_id || avIndex"
                                            class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center mb-3">
                                            <div>
                                                <select v-model="attrVal.attribute_id" @change="onAttributeChange(vIndex, avIndex)"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                    <option value="" disabled>Chọn thuộc tính</option>
                                                    <option v-for="attr in attributes" :key="attr.id" :value="attr.id">
                                                        {{ attr.name }}
                                                    </option>
                                                </select>
                                                <p v-if="errors[`variants.${vIndex}.attributes.${avIndex}.attribute_id`]" class="text-red-500 text-xs mt-1">{{ errors[`variants.${vIndex}.attributes.${avIndex}.attribute_id`][0] }}</p>
                                            </div>
                                            <div>
                                                <select v-model="attrVal.value_id" :disabled="!attrVal.attribute_id"
                                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed">
                                                    <option value="" disabled>Chọn giá trị</option>
                                                    <option v-for="val in getAttributeValues(attrVal.attribute_id)" :key="val.id" :value="val.id">
                                                        {{ val.value }}
                                                    </option>
                                                </select>
                                                <p v-if="errors[`variants.${vIndex}.attributes.${avIndex}.value_id`]" class="text-red-500 text-xs mt-1">{{ errors[`variants.${vIndex}.attributes.${avIndex}.value_id`][0] }}</p>
                                            </div>
                                            <div>
                                                <button type="button" @click="removeAttributeFromVariant(vIndex, avIndex)"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    Xóa
                                                </button>
                                            </div>
                                        </div>
                                        <p v-if="!variant.attributes || variant.attributes.length === 0"
                                            class="text-gray-500 text-sm mt-3">Chưa có thuộc tính nào cho biến thể này.</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-md">
                                Sản phẩm này hiện không có biến thể nào. Thêm biến thể mới để quản lý.
                            </div>
                        </div>

                        <div class="flex justify-start space-x-4 mt-8">
                            <button type="submit"
                                class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Cập nhật Sản phẩm
                            </button>
                            <router-link :to="{ name: 'products' }"
                                class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Quay lại
                            </router-link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, watch, computed } from 'vue'; // Thêm 'computed' vào đây
import { useRoute } from 'vue-router';
import axios from 'axios';
import router from '@/router';
import Swal from 'sweetalert2';

import ScentGroupSelector from '@/components/admin/product/ScentGroupSelector.vue';
import UsageProfile from '@/components/admin/product/UsageProfile.vue';

const route = useRoute();

// Reactive state variables
const categories = ref([]);
const brands = ref([]);
const attributes = ref([]); // Stores all attributes with their nested values
// --- NEW STATE FOR GALLERY IMAGES ---
const newGalleryImages = ref([]); // Lưu trữ các File object của ảnh mới upload
const galleryImagesToDelete = ref([]); // Lưu trữ IDs của các ảnh cũ muốn xóa
// Dữ liệu cho ScentGroupSelector
const allScentGroups = ref([]); // Tất cả nhóm hương có sẵn từ API

// Product data structure with default values
const product = ref({
    name: "",
    slug: "",
    image: null,
    description: "",
    gender: "",
    price: null,
    category_id: "",
    brand_id: "",
    // Các trường mới cho scentGroup và usageProfile
    // ScentGroup: Sẽ chứa selectedScentGroupIds và scentGroupsData
    // usageProfile: Sẽ chứa tất cả các thuộc tính của hồ sơ sử dụng
    scentGroups: {
        selectedScentGroupIds: [],
        scentGroupsData: {} // e.g., { 'scentId': { strength: 50 } }
    },
    usageProfile: {
        spring_percent: 0,
        summer_percent: 0,
        autumn_percent: 0,
        winter_percent: 0,
        suitable_day: 0,
        suitable_night: 0,
        longevity_hours: 0.0,
        sillage_range_m: '',
    },
    variants: [],
    gallery_images: [] // Sẽ chứa các ảnh gallery hiện có từ API
});

// Form-related state
const imageFile = ref(null);
const currentImageUrl = ref('');
const removeMainImage = ref(false);
const errors = ref({});

// Watcher for product.name to auto-generate slug
watch(() => product.value.name, (newName) => {
    if (!product.value.slug) {
        product.value.slug = generateSlug(newName);
    }
});

// --- Utility Functions ---

const getImageUrl = (imagePath) => {
    if (!imagePath) return null; // Handle null or empty paths

    if (imagePath instanceof File) {
        return URL.createObjectURL(imagePath);
    }

    // Check if the imagePath is already a full HTTP/HTTPS URL.
    // This is the ideal scenario for your current backend output.
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath; // Use the provided URL directly
    }

    // Fallback for relative paths, though your backend might not send these for main images now.
    // Keep this if you anticipate receiving relative paths like 'products/image.jpg' from other APIs or contexts.
    return `http://localhost:8000/storage/${imagePath}`;
};

const generateSlug = (text) => {
    if (!text) return '';
    return text
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '-')
        .replace(/[^\w-]+/g, '')
        .replace(/--+/g, '-');
};

// --- Image Handling ---

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        imageFile.value = file;
        currentImageUrl.value = URL.createObjectURL(file);
        removeMainImage.value = false;
    } else {
        imageFile.value = null;
        currentImageUrl.value = product.value.image ? getImageUrl(product.value.image) : null;
    }
};

watch(removeMainImage, (newValue) => {
    if (newValue) {
        imageFile.value = null;
        currentImageUrl.value = null;
    } else {
        if (product.value.image && !imageFile.value) {
            currentImageUrl.value = getImageUrl(product.value.image);
        }
    }
});

// --- NEW: Gallery Image Handling ---

// Khi chọn ảnh mới từ input
const onGalleryFilesChange = (e) => {
    const files = e.target.files;
    for (let i = 0; i < files.length; i++) {
        // Thêm ảnh mới vào newGalleryImages
        newGalleryImages.value.push(files[i]);
        // Đồng thời thêm vào product.gallery_images để hiển thị ngay lập tức
        // Gán một ID tạm thời (âm) để phân biệt với ảnh cũ từ DB
        product.value.gallery_images.push({
            id: -Date.now() - i, // ID âm duy nhất
            path: files[i], // Lưu trữ File object trực tiếp để preview
            isNew: true, // Đánh dấu đây là ảnh mới
            order: product.value.gallery_images.length // Gán thứ tự ban đầu
        });
    }
    // Xóa giá trị của input file để có thể chọn lại cùng file
    e.target.value = '';
};

// Xóa ảnh cũ hoặc ảnh mới trong preview
const removeGalleryImage = (imageToRemove) => {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Ảnh này sẽ bị xóa khỏi thư viện!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, xóa nó đi!',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            product.value.gallery_images = product.value.gallery_images.filter(img => {
                if (img.id === imageToRemove.id) {
                    // Nếu là ảnh cũ từ DB, thêm ID vào danh sách xóa
                    if (!img.isNew) {
                        galleryImagesToDelete.value.push(img.id);
                    }
                    return false; // Loại bỏ ảnh này khỏi mảng hiển thị
                }
                return true;
            });
            // Cập nhật lại thứ tự
            updateGalleryImageOrder();
            Swal.fire(
                'Đã xóa!',
                'Ảnh đã được đánh dấu để xóa khi lưu sản phẩm.',
                'success'
            );
        }
    });
};

// Cập nhật thứ tự sau khi kéo thả
const updateGalleryImageOrder = () => {
    product.value.gallery_images.forEach((image, index) => {
        image.order = index + 1; // Đặt thứ tự dựa trên vị trí trong mảng
    });
};


// --- Data Fetching ---

/**
 * Fetches product data from the API based on the route parameter ID.
 */
const fetchProduct = async () => {
    try {
        const { data } = await axios.get(`http://localhost:8000/api/admin/products/${route.params.id}`);

        // Gán dữ liệu sản phẩm chính
        product.value = {
            ...data.data,
            price: data.data.price !== null ? parseFloat(data.data.price) : null,
            // Xử lý dữ liệu scentGroups
            scentGroups: {
                selectedScentGroupIds: data.data.scent_profiles ? data.data.scent_profiles.map(sp => sp.scent_group_id) : [],
                scentGroupsData: data.data.scent_profiles ? data.data.scent_profiles.reduce((acc, sp) => {
                    acc[sp.scent_group_id] = { strength: sp.strength || 50 };
                    return acc;
                }, {}) : {}
            },
            // Xử lý dữ liệu usageProfile
            usageProfile: {
                spring_percent: data.data.usage_profile?.spring_percent || 0,
                summer_percent: data.data.usage_profile?.summer_percent || 0,
                autumn_percent: data.data.usage_profile?.autumn_percent || 0,
                winter_percent: data.data.usage_profile?.winter_percent || 0,
                suitable_day: data.data.usage_profile?.suitable_day || 0,
                suitable_night: data.data.usage_profile?.suitable_night || 0,
                longevity_hours: data.data.usage_profile?.longevity_hours || 0.0,
                sillage_range_m: data.data.usage_profile?.sillage_range_m || '',
            },
            // Deep copy variants và đảm bảo các trường số được phân tích
            variants: data.data.variants ? data.data.variants.map(variant => ({
                ...variant,
                price: variant.price !== null ? parseFloat(variant.price) : null,
                stock: variant.stock !== null ? parseInt(variant.stock) : null,
                status: variant.status || 'available',
                attributes: variant.attributes ? variant.attributes.map(av => ({
                    attribute_id: av.pivot.attribute_id,
                    value_id: av.pivot.attribute_value_id
                })) : []
            })) : [],
            gallery_images: data.data.images ? data.data.images.map((path, index) => ({
                id: data.data.image_ids ? data.data.image_ids[index] : index, // Giả định có mảng image_ids
                path: path,
                isNew: false,
                order: index + 1
            })) : []
        };

        currentImageUrl.value = getImageUrl(product.value.image);
        newGalleryImages.value = [];
        galleryImagesToDelete.value = [];
    } catch (error) {
        console.error('Lỗi khi lấy sản phẩm:', error);
        Swal.fire({
            title: 'Lỗi!',
            text: 'Lỗi xảy ra khi lấy sản phẩm: ' + (error.response?.data?.message || error.message),
            icon: 'error',
            confirmButtonText: 'Đóng',
        });
    }
};

/**
 * Fetches all scent groups from the API for the selector.
 */
const fetchAllScentGroups = async () => {
    try {
        const { data } = await axios.get('http://localhost:8000/api/admin/scent-groups'); // API endpoint của bạn

        console.log('EditProduct: Raw data from API for scent-groups:', data);

        // SỬA DÒNG NÀY:
        allScentGroups.value = data; // Gán trực tiếp 'data' (là mảng) vào allScentGroups.value

        console.log('EditProduct: allScentGroups.value after assignment:', allScentGroups.value);
    } catch (error) {
        console.error('Lỗi khi lấy tất cả nhóm hương:', error);
        Swal.fire({
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi lấy danh sách nhóm hương: ' + (error.response?.data?.message || error.message),
            icon: 'error',
            confirmButtonText: 'Đóng',
        });
    }
};

/**
 * Fetches categories from the API.
 */
const fetchCategory = async () => {
    try {
        const { data } = await axios.get(`http://localhost:8000/api/admin/categories`);
        categories.value = data.data;
    } catch (error) {
        console.error('Lỗi khi lấy danh mục:', error);
        Swal.fire({
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi lấy danh mục: ' + (error.response?.data?.message || error.message),
            icon: 'error',
            confirmButtonText: 'Đóng',
        });
    }
};

/**
 * Fetches brands from the API.
 */
const fetchBrand = async () => {
    try {
        // 'data' here will directly be the array from your Postman result
        const { data } = await axios.get('http://localhost:8000/api/admin/brands');

        // Assign the 'data' (which is the array) directly to brands.value
        brands.value = data; // FIX IS HERE

        console.log('Brands loaded successfully:', brands.value);
    } catch (error) {
        console.error('Lỗi khi tải thương hiệu:', error);
        let errorMessage = 'Không thể tải danh sách thương hiệu.';
        if (error.response) {
            errorMessage = `Lỗi máy chủ: ${error.response.status} - ${error.response.data?.message || 'Không có thông báo lỗi'}`;
        } else if (error.request) {
            errorMessage = 'Không có phản hồi từ máy chủ. Vui lòng kiểm tra kết nối mạng của bạn.';
        } else {
            errorMessage = `Lỗi yêu cầu: ${error.message}`;
        }
        Swal.fire('Lỗi!', errorMessage, 'error');
    }
};

/**
 * Fetches attributes with their values from the API.
 */
const fetchAttributes = async () => {
    try {
        const { data } = await axios.get('http://localhost:8000/api/admin/attributes?with_values=true');
        attributes.value = data.data;
    } catch (error) {
        console.error('Lỗi khi lấy thuộc tính:', error);
        Swal.fire({
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi lấy danh sách thuộc tính: ' + (error.response?.data?.message || error.message),
            icon: 'error',
            confirmButtonText: 'Đóng',
        });
    }
};

// --- Variant Management ---

const addVariant = () => {
    product.value.variants.push({
        id: null,
        sku: '',
        price: null,
        stock: null,
        sold: 0,
        status: 'available',
        barcode: '',
        description: '',
        attributes: [],
    });
};

const removeVariant = (index) => {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Biến thể này sẽ bị xóa! (Nếu là biến thể đã lưu, nó sẽ bị xóa khỏi cơ sở dữ liệu)",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, xóa nó đi!',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            product.value.variants.splice(index, 1);
            Swal.fire(
                'Đã xóa!',
                'Biến thể của bạn đã được đánh dấu để xóa khi lưu sản phẩm.',
                'success'
            );
        }
    });
};

// --- Attribute Management for Variants ---

const addAttributeToVariant = (variantIndex) => {
    const variantAttributes = product.value.variants[variantIndex].attributes;
    const existingAttrIds = variantAttributes.map(a => a.attribute_id);

    const availableAttributes = attributes.value.filter(attr => !existingAttrIds.includes(attr.id));

    if (availableAttributes.length > 0) {
        variantAttributes.push({
            attribute_id: availableAttributes[0].id,
            value_id: '',
        });
    } else {
        Swal.fire('Thông báo', 'Không còn thuộc tính nào để thêm cho biến thể này.', 'info');
    }
};

const removeAttributeFromVariant = (variantIndex, attributeValueIndex) => {
    product.value.variants[variantIndex].attributes.splice(attributeValueIndex, 1);
};

const getAttributeValues = (attributeId) => {
    const attribute = attributes.value.find(attr => attr.id === attributeId);
    return attribute ? attribute.attribute_values : [];
};

const onAttributeChange = (variantIndex, attributeValueIndex) => {
    product.value.variants[variantIndex].attributes[attributeValueIndex].value_id = '';
};

// --- Form Submission ---

const updateProduct = async () => {
    errors.value = {};
    try {
        const formData = new FormData();

        // Append main product fields
        for (const key in product.value) {
            if (key !== 'variants' && key !== 'image' && key !== 'scentGroups' && key !== 'usageProfile') {
                const value = product.value[key];
                formData.append(key, value === null || value === undefined ? '' : value);
            }
        }

        // Handle main image
        if (imageFile.value) {
            formData.append('image', imageFile.value);
        } else if (removeMainImage.value) {
            formData.append('remove_main_image', true);
            formData.append('image', '');
        }

        // Append Scent Groups data
        const formattedScentGroups = product.value.scentGroups.selectedScentGroupIds
            .filter(id => id && id > 0) // Lọc bỏ các giá trị null, undefined, 0, hoặc không phải số
            .map(id => ({
                id: id,
                strength: product.value.scentGroups.scentGroupsData[id]?.strength || 50
            }));
        console.log('Sending scent_groups:', formattedScentGroups);
        formData.append('scent_groups', JSON.stringify(formattedScentGroups));

        // Duyệt qua từng trường của usageProfile và thêm vào FormData
        for (const key in product.value.usageProfile) {
            const value = product.value.usageProfile[key];
            // Thêm tiền tố 'usage_profile' để Laravel dễ dàng nhận diện và validate
            formData.append(`usage_profile[${key}]`, value === null || value === undefined ? '' : value);
        }

        // --- Append Gallery Images Data ---
        // 1. Ảnh mới (File objects)
        newGalleryImages.value.forEach((file, index) => {
            formData.append(`new_gallery_images[${index}]`, file);
        });

        // 2. ID của các ảnh cũ cần xóa
        if (galleryImagesToDelete.value.length > 0) {
            formData.append('deleted_gallery_image_ids', JSON.stringify(galleryImagesToDelete.value));
        }

        // 3. Thứ tự và ID của các ảnh gallery hiện có (cả cũ và mới đã được hiển thị)
        // Đây là cách bạn gửi thông tin thứ tự về backend
        const existingGalleryImagesData = product.value.gallery_images
            .filter(img => !img.isNew) // Chỉ gửi thông tin của ảnh cũ (để cập nhật thứ tự)
            .map(img => ({
                id: img.id,
                order: img.order
            }));
        // Các ảnh mới sẽ được xử lý riêng thông qua new_gallery_images và sẽ được gán ID và thứ tự mới ở backend.

        if (existingGalleryImagesData.length > 0) {
            formData.append('existing_gallery_images_order', JSON.stringify(existingGalleryImagesData));
        }
        // Prepare and append variants data
        const variantsData = product.value.variants.map(variant => {
            const variantCopy = { ...variant };

            variantCopy.price = variantCopy.price !== null ? variantCopy.price : '';
            variantCopy.stock = variantCopy.stock !== null ? variantCopy.stock : '';
            variantCopy.status = variantCopy.status || 'available';

            variantCopy.attribute_value_ids = (variantCopy.attributes || [])
                .filter(av => av.attribute_id && av.value_id)
                .map(av => av.value_id);

            delete variantCopy.attributes;

            return variantCopy;
        });

        formData.append('variants', JSON.stringify(variantsData));

        formData.append('_method', 'PUT'); // For Laravel's PATCH/PUT emulation

        await axios.post(`http://localhost:8000/api/admin/products/${route.params.id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        const result = await Swal.fire({
            title: 'Cập nhật thành công!',
            text: 'Sản phẩm đã được cập nhật.',
            icon: 'success',
            confirmButtonText: 'Tuyệt vời!',
        });

        if (result.isConfirmed) {
            router.push('/admin/products');
        }
    } catch (error) {
        if (error.response && error.response.status === 422) {
            console.error("💥 Lỗi validation từ Laravel:", error.response.data.errors);
            errors.value = error.response.data.errors;

            let errorMessage = "Có lỗi xảy ra khi cập nhật:\n";
            for (const field in errors.value) {
                let displayFieldName = field;
                switch (field) {
                    case 'name': displayFieldName = 'Tên sản phẩm'; break;
                    case 'price': displayFieldName = 'Giá'; break;
                    case 'slug': displayFieldName = 'Slug'; break;
                    case 'gender': displayFieldName = 'Giới tính'; break;
                    case 'category_id': displayFieldName = 'Danh mục'; break;
                    case 'brand_id': displayFieldName = 'Thương hiệu'; break;
                    case 'image': displayFieldName = 'Hình ảnh'; break;
                    case 'description': displayFieldName = 'Mô tả'; break;
                    case 'scent_groups': displayFieldName = 'Cấu hình nhóm hương'; break;
                    case 'usage_profile': displayFieldName = 'Hồ sơ sử dụng'; break; // Giữ nguyên, Laravel sẽ tự xử lý
                    case 'usage_profile.spring_percent': displayFieldName = 'Mùa Xuân'; break;
                    case 'usage_profile.summer_percent': displayFieldName = 'Mùa Hạ'; break;
                    case 'usage_profile.autumn_percent': displayFieldName = 'Mùa Thu'; break;
                    case 'usage_profile.winter_percent': displayFieldName = 'Mùa Đông'; break;
                    case 'usage_profile.suitable_day': displayFieldName = 'Sử dụng ban ngày'; break;
                    case 'usage_profile.suitable_night': displayFieldName = 'Sử dụng ban đêm'; break;
                    case 'usage_profile.longevity_hours': displayFieldName = 'Độ lưu hương (giờ)'; break;
                    case 'usage_profile.sillage_range_m': displayFieldName = 'Độ tỏa hương (mét)'; break;
                    default: break;
                }
                errorMessage += `- ${displayFieldName}: ${errors.value[field].join(', ')}\n`;
            }

            for (const field in errors.value) {
                if (field.startsWith('variants.')) {
                    const parts = field.split('.');
                    const variantIndex = parseInt(parts[1]) + 1;
                    let errorField = parts[2];

                    let fieldName = '';
                    if (errorField === 'attributes' && parts.length > 3) {
                        const attributeIndex = parseInt(parts[3]) + 1;
                        const attributeSubField = parts[4];
                        fieldName = `Thuộc tính #${attributeIndex} (`;
                        if (attributeSubField === 'attribute_id') {
                            fieldName += 'ID thuộc tính';
                        } else if (attributeSubField === 'value_id') {
                            fieldName += 'ID giá trị';
                        }
                        fieldName += ')';
                    } else {
                        switch (errorField) {
                            case 'sku': fieldName = 'SKU'; break;
                            case 'price': fieldName = 'Giá'; break;
                            case 'stock': fieldName = 'Tồn kho'; break;
                            case 'barcode': fieldName = 'Mã vạch'; break;
                            case 'description': fieldName = 'Mô tả'; break;
                            case 'status': fieldName = 'Trạng thái'; break;
                            case 'attribute_value_ids': fieldName = 'Thuộc tính'; break;
                            default: fieldName = errorField; break;
                        }
                    }
                    errorMessage += `- Biến thể ${variantIndex} (${fieldName}): ${errors.value[field].join(', ')}\n`;
                }
            }

            Swal.fire({
                title: 'Lỗi Cập nhật!',
                html: `<pre style="text-align: left; white-space: pre-wrap; word-break: break-word;">${errorMessage}</pre>`,
                icon: 'error',
                confirmButtonText: 'Đóng',
            });
        } else {
            console.error("❌ Lỗi khác:", error);
            Swal.fire({
                title: 'Lỗi!',
                text: 'Có lỗi không xác định xảy ra: ' + (error.response?.data?.message || error.message),
                icon: 'error',
                confirmButtonText: 'Đóng',
            });
        }
    }
};

// --- NEW: Computed property for sortedScentProfiles ---
const sortedScentProfiles = computed(() => {
    // Lấy các ID nhóm hương đã chọn từ product.scentGroups.selectedScentGroupIds
    const selectedIds = product.value.scentGroups.selectedScentGroupIds;
    const scentData = product.value.scentGroups.scentGroupsData;

    // Tạo một mảng các đối tượng scent profile với đầy đủ thông tin
    const profiles = selectedIds
        .map(id => {
            // Tìm thông tin chi tiết của nhóm hương từ allScentGroups
            const group = allScentGroups.value.find(sg => sg.id === id);
            // Lấy độ mạnh từ scentGroupsData, mặc định là 0 nếu không có
            const strength = scentData[id]?.strength || 0;

            if (group) {
                return {
                    scent_group_id: group.id,
                    scent_group_name: group.name,
                    scent_group_color_code: group.color_code,
                    strength: strength,
                };
            }
            return null; // Bỏ qua nếu không tìm thấy nhóm hương
        })
        .filter(profile => profile !== null); // Loại bỏ các nhóm hương không tìm thấy

    // Sắp xếp các profile theo độ mạnh giảm dần
    return profiles.sort((a, b) => b.strength - a.strength);
});

// --- NEW: Function to check if a color is dark ---
const isDarkColor = (hexColor) => {
    if (!hexColor || hexColor.length < 7) { // Đảm bảo hexColor đúng định dạng (vd: #RRGGBB)
        return true; // Mặc định là tối nếu không hợp lệ để chữ trắng dễ đọc
    }
    const r = parseInt(hexColor.substring(1, 3), 16);
    const g = parseInt(hexColor.substring(3, 5), 16);
    const b = parseInt(hexColor.substring(5, 7), 16);
    // Tính độ sáng tương đối (perceived brightness)
    // Công thức này phổ biến, cho kết quả tốt trên nhiều loại màu
    const brightness = (r * 299 + g * 587 + b * 114) / 1000;
    return brightness < 128; // Trả về true nếu màu tối, false nếu màu sáng (ngưỡng 128)
};

// --- Lifecycle Hook ---
onMounted(() => {
    fetchProduct();
    fetchCategory();
    fetchBrand();
    fetchAllScentGroups(); // Đảm bảo hàm này được gọi
    fetchAttributes();
});
</script>

<style scoped>
.custom-hover-link:hover {
    color: white !important;
}

.form-check {
    margin-right: 1.5rem;
    /* Add some space between radio buttons */
}
</style>