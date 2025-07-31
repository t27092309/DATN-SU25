<template>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="mb-6">
                <h3 class="text-3xl font-bold mb-3">{{ route.meta.title }}</h3>
                <ul class="flex items-center space-x-2 text-gray-600 text-sm">
                    <li>
                        <router-link :to="{ name: 'AdminDashboard' }" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-home"></i>
                        </router-link>
                    </li>
                    <li>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </li>
                    <li>
                        <router-link :to="{ name: 'products' }" class="text-blue-600 hover:text-blue-800">Danh sách sản phẩm</router-link>
                    </li>
                    <li>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </li>
                    <li>
                        <span class="text-blue-600 font-semibold">{{ route.meta.title }}</span>
                    </li>
                </ul>
            </div>
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-xl font-semibold">{{ route.meta.title }}</div>
                    </div>
                </div>
                <div class="p-6">
                    <form @submit.prevent="addProduct">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-6">
                            <div>
                                <div class="mb-4">
                                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Tên sản phẩm <span class="text-red-500">*</span></label>
                                    <input type="text" class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="name" placeholder="Nhập tên sản phẩm"
                                        v-model="product.name" />
                                    <p v-if="errors.name" class="text-red-500 text-xs italic mt-1">{{ errors.name[0] }}</p>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Giới tính <span class="text-red-500">*</span></label>
                                    <div class="flex gap-6 mt-1">
                                        <div class="flex items-center">
                                            <input class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out" type="radio" name="gender" id="male"
                                                value="male" v-model="product.gender" />
                                            <label class="ml-2 text-gray-700" for="male">Nam</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out" type="radio" name="gender" id="female"
                                                value="female" v-model="product.gender" />
                                            <label class="ml-2 text-gray-700" for="female">Nữ</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out" type="radio" name="gender" id="unisex"
                                                value="unisex" v-model="product.gender" />
                                            <label class="ml-2 text-gray-700" for="unisex">Unisex</label>
                                        </div>
                                    </div>
                                    <p v-if="errors.gender" class="text-red-500 text-xs italic mt-1">{{ errors.gender[0]
                                    }}</p>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label for="categorySelect" class="block text-gray-700 text-sm font-bold mb-2">Danh mục <span class="text-red-500">*</span></label>
                                    <select class="block appearance-none w-full bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 pr-8 rounded-md shadow-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="categorySelect" v-model="product.category_id">
                                        <option value="">Chọn danh mục</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <p v-if="errors.category_id" class="text-red-500 text-xs italic mt-1">{{
                                        errors.category_id[0] }}</p>
                                </div>
                                <div class="mb-4">
                                    <label for="brandSelect" class="block text-gray-700 text-sm font-bold mb-2">Thương hiệu <span class="text-red-500">*</span></label>
                                    <select class="block appearance-none w-full bg-white border border-gray-300 hover:border-gray-400 px-4 py-2 pr-8 rounded-md shadow-sm leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="brandSelect" v-model="product.brand_id"
                                        v-if="brands.length > 0">
                                        <option value="">Chọn thương hiệu</option>
                                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                    <p v-else class="text-gray-500 text-sm italic mt-1">Đang tải thương hiệu...</p>
                                    <p v-if="errors.brand_id" class="text-red-500 text-xs italic mt-1">{{ errors.brand_id[0]
                                    }}</p>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label for="slug" class="block text-gray-700 text-sm font-bold mb-2">Slug (Tự động tạo)</label>
                                    <input type="text" class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 bg-gray-100 leading-tight cursor-not-allowed" id="slug" placeholder="Slug sản phẩm"
                                        v-model="product.slug" disabled />
                                </div>
                                <div class="mb-4">
                                    <label for="image" class="block text-gray-700 text-sm font-bold mb-2">Hình ảnh chính</label>
                                    <input type="file" class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-md file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100 file:cursor-pointer" id="image" @change="onFileChangeMainImage"
                                        accept="image/*" />
                                    <p v-if="errors.image" class="text-red-500 text-xs italic mt-1">{{ errors.image[0]
                                    }}</p>
                                </div>
                                <div v-if="imageUrlPreview" class="mt-2">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Ảnh xem trước:</label>
                                    <img :src="imageUrlPreview" alt="Image Preview"
                                        class="max-w-xs h-32 w-32 object-cover rounded-md shadow-md border border-gray-200" />
                                </div>
                            </div>
                        </div>

                        <hr class="my-8 border-gray-300" />

                        <div class="mb-6">
                            <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Mô tả</label>
                            <textarea class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="description" rows="5"
                                v-model="product.description" placeholder="Nhập mô tả sản phẩm"></textarea>
                            <p v-if="errors.description" class="text-red-500 text-xs italic mt-1">{{
                                errors.description[0] }}</p>
                        </div>

                        <hr class="my-8 border-gray-300" />

                        <div class="mb-6">
                            <label for="productGallery" class="block text-gray-700 text-sm font-bold mb-2">Thư viện ảnh sản phẩm</label>
                            <input type="file" class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100 file:cursor-pointer" id="productGallery" multiple
                                @change="onFileChangeGalleryImages" accept="image/*" />
                            <p v-if="errors.gallery_images" class="text-red-500 text-xs italic mt-1">{{
                                errors.gallery_images[0] }}</p>
                        </div>
                        <div v-if="galleryImagePreviews.length > 0" class="mt-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Ảnh thư viện xem trước:</label>
                            <div class="flex flex-wrap gap-3">
                                <div v-for="(image, index) in galleryImagePreviews" :key="index"
                                    class="relative group">
                                    <img :src="image" alt="Gallery Image Preview"
                                        class="max-w-[100px] h-[100px] object-cover rounded-md shadow-md border border-gray-200 transition-all duration-200 group-hover:scale-105" />
                                    <button type="button"
                                        class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full h-6 w-6 flex items-center justify-center text-xs font-bold opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                        @click="removeGalleryImage(index)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="my-8 border-gray-300" />

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nhóm hương</label>
                            <ScentGroupSelector
                                v-model:selected-scent-group-ids="product.selected_scent_group_ids"
                                v-model:scent-groups-data="product.scent_groups_data"
                                :all-scent-groups="allScentGroupsForDisplay" />
                            <p v-if="errors.scent_groups" class="text-red-500 text-xs italic mt-1">{{
                                errors.scent_groups[0] }}</p>
                        </div>
                        <div v-if="sortedScentProfiles.length > 0" class="mt-8">
                            <h6 class="text-lg font-semibold mb-4 text-gray-800">Mức độ hương:</h6>
                            <div class="space-y-4">
                                <div v-for="scent in sortedScentProfiles" :key="scent.scent_group_id"
                                    class="flex items-center">
                                    <span class="scent-name mr-4 text-gray-700 font-medium w-32 truncate" :title="scent.scent_group_name">{{ scent.scent_group_name }}:</span>
                                    <div class="flex-grow bg-gray-200 rounded-full h-6 overflow-hidden">
                                        <div class="h-full flex items-center justify-center text-sm font-bold transition-all duration-300 ease-out px-2" role="progressbar"
                                            :style="{ width: scent.strength + '%', backgroundColor: scent.scent_group_color_code }"
                                            :aria-valuenow="scent.strength" aria-valuemin="0"
                                            aria-valuemax="100">
                                            <span
                                                :style="{ color: isDarkColor(scent.scent_group_color_code) ? 'white' : 'black' }">
                                                {{ scent.strength }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-8 border-gray-300" />

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Thông tin sử dụng sản phẩm</label>
                            <UsageProfile v-model:usage-profile-data="product.usage_profile" />
                            <p v-if="errors['usage_profile.spring_percent']" class="text-red-500 text-xs italic mt-1">
                                {{ errors['usage_profile.spring_percent'][0] }}
                            </p>
                        </div>

                        <hr class="my-8 border-gray-300" />

                        <div class="mb-8">
                            <label class="block text-gray-700 text-sm font-bold mb-3">Loại sản phẩm <span class="text-red-500">*</span></label>
                            <div class="flex items-center space-x-6">
                                <div class="flex items-center">
                                    <input class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out" type="radio" id="noVariants" :value="false"
                                        v-model="product.has_variants" />
                                    <label class="ml-2 text-gray-700" for="noVariants">Sản phẩm đơn giản</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="form-radio h-4 w-4 text-blue-600 transition duration-150 ease-in-out" type="radio" id="hasVariants" :value="true"
                                        v-model="product.has_variants" />
                                    <label class="ml-2 text-gray-700" for="hasVariants">Sản phẩm có biến thể</label>
                                </div>
                            </div>
                            <p v-if="errors.has_variants" class="text-red-500 text-xs italic mt-1">{{
                                errors.has_variants[0] }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6" v-if="product.has_variants === false">
                            <div>
                                <div class="mb-4">
                                    <label for="simplePrice" class="block text-gray-700 text-sm font-bold mb-2">Giá sản phẩm <span class="text-red-500">*</span></label>
                                    <input type="number" class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="simplePrice"
                                        placeholder="Nhập giá sản phẩm" v-model="product.price" min="0" />
                                    <p v-if="errors.price" class="text-red-500 text-xs italic mt-1">{{ errors.price[0]
                                    }}</p>
                                </div>
                            </div>
                            <div>
                                <div class="mb-4">
                                    <label for="simpleStock" class="block text-gray-700 text-sm font-bold mb-2">Số lượng tồn kho <span class="text-red-500">*</span></label>
                                    <input type="number" class="shadow-sm appearance-none border border-gray-300 rounded-md w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" id="simpleStock"
                                        placeholder="Nhập số lượng tồn kho" v-model="product.stock" min="0" />
                                    <p v-if="errors.stock" class="text-red-500 text-xs italic mt-1">{{ errors.stock[0]
                                    }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="product.has_variants === true">
                            <h4 class="text-xl font-semibold mt-8 mb-6 text-gray-800">Chọn thuộc tính và giá trị</h4>
                            <div>
                                <p v-if="errors.variants" class="text-red-500 text-xs italic mb-4">{{
                                    errors.variants[0] }}</p>
                                <div v-for="attribute in attributes" :key="attribute.id"
                                    class="mb-6 p-5 border border-gray-200 rounded-lg shadow-sm bg-gray-50">
                                    <h6 class="text-lg font-semibold mb-4 text-gray-800">{{ attribute.name }}</h6>
                                    <div class="flex flex-wrap gap-x-6 gap-y-3">
                                        <div v-for="value in attribute.attribute_values" :key="value.id"
                                            class="flex items-center">
                                            <input class="form-checkbox h-4 w-4 text-blue-600 rounded focus:ring-blue-500 transition duration-150 ease-in-out" type="checkbox"
                                                :id="`attr-${attribute.id}-val-${value.id}`"
                                                :value="{ attributeId: attribute.id, valueId: value.id, valueName: value.value, attributeName: attribute.name }"
                                                v-model="selectedAttributeValues[attribute.id]"
                                                @change="generateVariants" />
                                            <label class="ml-2 text-gray-700 select-none cursor-pointer"
                                                :for="`attr-${attribute.id}-val-${value.id}`">
                                                {{ value.value }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h4 class="text-xl font-semibold mt-10 mb-6 text-gray-800">Các biến thể đã tạo</h4>
                            <div v-if="product.variants.length > 0" class="overflow-x-auto rounded-lg shadow-md border border-gray-200">
                                <table class="min-w-full bg-white divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên biến thể</th>
                                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU <span class="text-red-500">*</span></th>
                                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá <span class="text-red-500">*</span></th>
                                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tồn kho <span class="text-red-500">*</span></th>
                                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ảnh biến thể</th>
                                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(variant, index) in product.variants" :key="variant.tempId" class="hover:bg-gray-50">
                                            <td class="py-3 px-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ variant.name }}</td>
                                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">
                                                <input type="text" class="w-full text-sm py-1 px-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                    v-model="variant.sku" :id="'variantSku' + variant.tempId" />
                                                <p v-if="errors[`variants.${index}.sku`]"
                                                    class="text-red-500 text-xs italic mt-1">
                                                    {{ errors[`variants.${index}.sku`][0] }}
                                                </p>
                                            </td>
                                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">
                                                <input type="number" class="w-full text-sm py-1 px-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                    v-model="variant.price" min="0"
                                                    :id="'variantPrice' + variant.tempId" />
                                                <p v-if="errors[`variants.${index}.price`]"
                                                    class="text-red-500 text-xs italic mt-1">
                                                    {{ errors[`variants.${index}.price`][0] }}
                                                </p>
                                            </td>
                                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">
                                                <input type="number" class="w-full text-sm py-1 px-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                                                    v-model="variant.stock" min="0"
                                                    :id="'variantStock' + variant.tempId" />
                                                <p v-if="errors[`variants.${index}.stock`]"
                                                    class="text-red-500 text-xs italic mt-1">
                                                    {{ errors[`variants.${index}.stock`][0] }}
                                                </p>
                                            </td>
                                            <td class="py-3 px-4 text-sm text-gray-700">
                                                <input type="file" class="block w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer"
                                                    @change="e => onFileChangeVariantImage(e, index)"
                                                    accept="image/*" />
                                                <div v-if="variant.imageUrlPreview" class="mt-2">
                                                    <img :src="variant.imageUrlPreview" alt="Preview"
                                                        class="max-w-[60px] h-16 object-cover rounded-md shadow-sm border border-gray-200" />
                                                </div>
                                                <p v-if="errors[`variants.${index}.image`]"
                                                    class="text-red-500 text-xs italic mt-1">
                                                    {{ errors[`variants.${index}.image`][0] }}
                                                </p>
                                            </td>
                                            <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-700">
                                                <button type="button" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                                    @click="removeSpecificVariant(index)">
                                                    Xóa
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="text-gray-500 text-base italic mt-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-md">
                                <i class="fas fa-info-circle mr-2"></i> Chưa có biến thể nào được tạo. Vui lòng chọn thuộc tính và giá
                                trị ở trên để tạo biến thể.
                            </p>
                        </div>

                        <div class="mt-10 pt-6 border-t border-gray-200 flex justify-start space-x-4">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">Thêm sản phẩm</button>
                            <router-link :to="{ name: 'products' }" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
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
import { onMounted, ref, watch, computed } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import router from '@/router';
import Swal from 'sweetalert2';

import ScentGroupSelector from '@/components/admin/product/ScentGroupSelector.vue';
import UsageProfile from '@/components/admin/product/UsageProfile.vue';

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

const route = useRoute();
const categories = ref([]);
const brands = ref([]);
const attributes = ref([]);
const selectedAttributeValues = ref({});

const product = ref({
    name: '',
    slug: '',
    description: '',
    gender: 'male',
    category_id: '',
    brand_id: '',
    has_variants: false,
    price: '',
    stock: '',
    variants: [],

    selected_scent_group_ids: [],
    scent_groups_data: {},
    usage_profile: {
        spring_percent: 0,
        summer_percent: 0,
        autumn_percent: 0,
        winter_percent: 0,
        suitable_day: 0,
        suitable_night: 0,
        longevity_hours: 0.0,
        sillage_range_m: '',
    },
});

const mainImageFile = ref(null);
const imageUrlPreview = ref(null);

const galleryImageFiles = ref([]);
const galleryImagePreviews = ref([]);

const errors = ref({});

const allScentGroupsForDisplay = ref([]);

const isDarkColor = (hexColor) => {
    if (!hexColor || hexColor.length < 7) return true;
    const r = parseInt(hexColor.substring(1, 3), 16);
    const g = parseInt(hexColor.substring(3, 5), 16);
    const b = parseInt(hexColor.substring(5, 7), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance <= 0.5;
};

// Cập nhật computed property cho độ mạnh từ 1-100
const sortedScentProfiles = computed(() => {
    const scentProfilesArray = Object.keys(product.value.scent_groups_data)
        .map(scentGroupId => {
            const strengthObj = product.value.scent_groups_data[scentGroupId];
            const strength = strengthObj ? strengthObj.strength : 0; // Giá trị strength giờ là từ 1-100

            const scentGroupInfo = allScentGroupsForDisplay.value.find(sg => sg.id == scentGroupId);

            return {
                scent_group_id: scentGroupId,
                scent_group_name: scentGroupInfo ? scentGroupInfo.name : `ID: ${scentGroupId}`,
                scent_group_color_code: scentGroupInfo ? scentGroupInfo.color_code : '#cccccc',
                strength: strength, // Giữ nguyên giá trị strength (1-100)
            };
        })
        .filter(scent => scent.strength > 0);

    return scentProfilesArray.sort((a, b) => b.strength - a.strength);
});

watch(() => product.value.name, (newName) => {
    product.value.slug = generateSlug(newName);
});

watch(() => product.value.has_variants, (newVal) => {
    if (newVal === true) {
        product.value.price = '';
        product.value.stock = '';
        generateVariants();
    } else {
        product.value.variants.forEach(variant => {
            if (variant.imageUrlPreview) URL.revokeObjectURL(variant.imageUrlPreview);
        });
        product.value.variants = [];
        for (const attrId in selectedAttributeValues.value) {
            selectedAttributeValues.value[attrId] = [];
        }
    }
});

const fetchCategory = async () => {
    try {
        const { data } = await axios.get(`http://localhost:8000/api/admin/categories`);
        categories.value = data.data;
    } catch (error) {
        console.error('Lỗi khi tải danh mục:', error);
        Swal.fire('Lỗi!', 'Không thể tải danh sách danh mục.', 'error');
    }
};

const fetchBrand = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/admin/brands');
        brands.value = response.data;
        console.log('Brands loaded successfully:', brands.value);
    } catch (error) {
        console.error('Lỗi khi tải thương hiệu:', error);
        let errorMessage = 'Không thể tải danh sách thương hiệu.';

        if (error.response) {
            if (error.response.data && error.response.data.message) {
                errorMessage = error.response.data.message;
            } else {
                errorMessage = `Lỗi máy chủ: ${error.response.status}`;
            }
        } else if (error.request) {
            errorMessage = 'Không có phản hồi từ máy chủ. Vui lòng kiểm tra kết nối mạng của bạn.';
        } else {
            errorMessage = `Lỗi yêu cầu: ${error.message}`;
        }

        Swal.fire('Lỗi!', errorMessage, 'error');
    }
};

const fetchAttributes = async () => {
    try {
        const { data } = await axios.get(`http://localhost:8000/api/admin/attributes`);
        attributes.value = data.data;
        attributes.value.forEach(attr => {
            selectedAttributeValues.value[attr.id] = [];
        });
    } catch (error) {
        console.error('Lỗi khi tải thuộc tính:', error);
        Swal.fire('Lỗi!', 'Không thể tải danh sách thuộc tính.', 'error');
    }
};

const fetchAllScentGroupsForDisplay = async () => {
    try {
        const { data } = await axios.get(`http://localhost:8000/api/admin/scent-groups?all=true`);
        allScentGroupsForDisplay.value = data.data || data;
    } catch (error) {
        console.error('Lỗi khi tải tất cả nhóm hương để hiển thị demo:', error);
        Swal.fire('Lỗi!', 'Không thể tải danh sách nhóm hương để hiển thị.', 'error');
    }
};

onMounted(() => {
    fetchCategory();
    fetchBrand();
    fetchAttributes();
    fetchAllScentGroupsForDisplay();
});

const onFileChangeMainImage = (e) => {
    const file = e.target.files[0];
    if (file) {
        mainImageFile.value = file;
        imageUrlPreview.value = URL.createObjectURL(file);
    } else {
        mainImageFile.value = null;
        imageUrlPreview.value = null;
    }
};

const onFileChangeGalleryImages = (e) => {
    const files = Array.from(e.target.files);
    galleryImageFiles.value.forEach(file => URL.revokeObjectURL(file));
    galleryImagePreviews.value.forEach(url => URL.revokeObjectURL(url));

    galleryImageFiles.value = [];
    galleryImagePreviews.value = [];

    files.forEach(file => {
        galleryImageFiles.value.push(file);
        galleryImagePreviews.value.push(URL.createObjectURL(file));
    });
};

const removeGalleryImage = (index) => {
    URL.revokeObjectURL(galleryImagePreviews.value[index]);
    galleryImageFiles.value.splice(index, 1);
    galleryImagePreviews.value.splice(index, 1);
};

const onFileChangeVariantImage = (e, index) => {
    const file = e.target.files[0];
    if (file) {
        if (product.value.variants[index].imageUrlPreview) {
            URL.revokeObjectURL(product.value.variants[index].imageUrlPreview);
        }
        product.value.variants[index].imageFile = file;
        product.value.variants[index].imageUrlPreview = URL.createObjectURL(file);
    } else {
        product.value.variants[index].imageFile = null;
        if (product.value.variants[index].imageUrlPreview) {
            URL.revokeObjectURL(product.value.variants[index].imageUrlPreview);
        }
        product.value.variants[index].imageUrlPreview = null;
    }
};

const generateVariants = () => {
    const activeAttributeValueGroups = Object.values(selectedAttributeValues.value)
        .filter(group => group.length > 0);

    if (activeAttributeValueGroups.length === 0) {
        product.value.variants.forEach(variant => {
            if (variant.imageUrlPreview) URL.revokeObjectURL(variant.imageUrlPreview);
        });
        product.value.variants = [];
        return;
    }

    const combinations = activeAttributeValueGroups.reduce((acc, currentGroup) => {
        if (acc.length === 0) return currentGroup.map(val => [val]);

        const newCombinations = [];
        acc.forEach(prevCombination => {
            currentGroup.forEach(currentVal => {
                newCombinations.push([...prevCombination, currentVal]);
            });
        });
        return newCombinations;
    }, []);

    const newVariants = combinations.map(combination => {
        const name = combination.map(val => val.valueName).join(' / ');
        const attribute_values_ids = combination.map(val => val.valueId);

        const existingVariant = product.value.variants.find(v =>
            JSON.stringify(v.attribute_values.sort()) === JSON.stringify(attribute_values_ids.sort())
        );

        return {
            tempId: existingVariant ? existingVariant.tempId : Date.now() + Math.random(),
            name: name,
            sku: existingVariant ? existingVariant.sku : '',
            price: existingVariant ? existingVariant.price : null,
            stock: existingVariant ? existingVariant.stock : null,
            imageFile: existingVariant ? existingVariant.imageFile : null,
            imageUrlPreview: existingVariant ? existingVariant.imageUrlPreview : null,
            attribute_values: attribute_values_ids,
        };
    });

    product.value.variants.forEach(oldVariant => {
        const stillExists = newVariants.some(newVariant => newVariant.tempId === oldVariant.tempId);
        if (!stillExists && oldVariant.imageUrlPreview) {
            URL.revokeObjectURL(oldVariant.imageUrlPreview);
        }
    });

    product.value.variants = newVariants;
};

const removeSpecificVariant = (index) => {
    if (product.value.variants[index].imageUrlPreview) {
        URL.revokeObjectURL(product.value.variants[index].imageUrlPreview);
    }
    product.value.variants.splice(index, 1);
};

const addProduct = async () => {
    errors.value = {};
    try {
        const formData = new FormData();

        for (const key in product.value) {
            if (key === 'has_variants') {
                formData.append('has_variants', product.value.has_variants ? 1 : 0);
            } else if (key === 'scent_groups_data') {
                // Đảm bảo scent_groups_data được gửi dưới dạng JSON string
                if (Object.keys(product.value.scent_groups_data).length > 0) {
                    formData.append('scent_groups', JSON.stringify(product.value.scent_groups_data));
                }
            } else if (key === 'usage_profile') {
                // --- BỔ SUNG: Thêm các trường usage_profile vào FormData ---
                for (const upKey in product.value.usage_profile) {
                    // Đảm bảo giá trị là số và không null/undefined trước khi append
                    if (product.value.usage_profile[upKey] !== null && product.value.usage_profile[upKey] !== undefined) {
                        formData.append(`usage_profile[${upKey}]`, product.value.usage_profile[upKey]);
                    }
                }
                // --- KẾT THÚC BỔ SUNG ---
            }
            else if (key !== 'slug' && key !== 'variants' && key !== 'selected_scent_group_ids' && product.value[key] !== null && product.value[key] !== '') {
                formData.append(key, product.value[key]);
            }
        }

        if (mainImageFile.value) {
            formData.append('image', mainImageFile.value);
        }


        galleryImageFiles.value.forEach((file, index) => {
            formData.append(`gallery_images[${index}]`, file);
        });


        if (product.value.has_variants) {
            if (product.value.variants.length === 0) {
                Swal.fire('Lỗi!', 'Bạn phải tạo ít nhất một biến thể cho sản phẩm có biến thể.', 'error');
                errors.value.variants = ['Bạn phải tạo ít nhất một biến thể.'];
                return;
            }
            product.value.variants.forEach((variant, index) => {
                formData.append(`variants[${index}][sku]`, variant.sku || '');
                formData.append(`variants[${index}][price]`, variant.price || '');
                formData.append(`variants[${index}][stock]`, variant.stock || '');

                if (variant.imageFile) {
                    formData.append(`variants[${index}][image]`, variant.imageFile);
                }

                variant.attribute_values.forEach((attrValueId, attrIndex) => {
                    formData.append(`variants[${index}][attribute_values][${attrIndex}]`, attrValueId);
                });
            });
        }

        const response = await axios.post('http://localhost:8000/api/admin/products', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });

        const successMessage = response.data.message || 'Sản phẩm đã được thêm thành công!';

        const result = await Swal.fire({
            title: 'Thành công!',
            text: successMessage,
            icon: 'success',
            confirmButtonText: 'Tuyệt vời!'
        });

        if (result.isConfirmed) {
            router.push('/admin/products');
        }
    } catch (error) {
        console.error('Lỗi khi thêm sản phẩm:', error);
        if (error.response) {
            if (error.response.status === 422) {
                errors.value = error.response.data.errors;
                let errorMessages = Object.values(errors.value).flat();
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi Validation!',
                    html: 'Vui lòng kiểm tra lại các trường dữ liệu:<br><ul>' +
                        errorMessages.map(msg => `<li>${msg}</li>`).join('') +
                        '</ul>',
                    confirmButtonText: 'Đã hiểu'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi Server!',
                    text: `Có lỗi xảy ra từ máy chủ: ${error.response.data.message || 'Vui lòng thử lại sau.'}`,
                    confirmButtonText: 'Đã hiểu'
                });
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Không thể kết nối tới máy chủ. Vui lòng kiểm tra kết nối mạng của bạn.',
                confirmButtonText: 'Đã hiểu'
            });
        }
    }
};
</script>

<style scoped>
.form-check {
    margin-right: 15px;
}

.custom-hover-link:hover {
    color: white !important;
}

.gap-3>.form-check {
    margin-right: 1rem;
    /* Adjust as needed for better spacing */
}

.form-check-inline {
    margin-right: 1rem;
}

/* Style cho bảng biến thể */
.table-responsive {
    margin-top: 1rem;
}

.table td,
.table th {
    vertical-align: middle;
    padding: 0.5rem;
}

.table input[type="text"],
.table input[type="number"],
.table input[type="file"] {
    max-width: 150px;
    /* Điều chỉnh độ rộng input trong bảng */
}

/* Thêm style cho phần thư viện ảnh */
.position-relative {
    position: relative;
}

.position-absolute {
    position: absolute;
}

.top-0 {
    top: 0;
}

.end-0 {
    right: 0;
}
</style>