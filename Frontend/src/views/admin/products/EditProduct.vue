<template>
    <div class="container">
        <div class="page-inner">
            <div class="page-header">
                <h3 class="fw-bold mb-3">{{ route.meta.title }}</h3>
                <ul class="breadcrumbs mb-3">
                    <li class="nav-home">
                        <router-link :to="{ name: 'AdminDashboard' }">
                            <i class="icon-home"></i>
                        </router-link>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <router-link :to="{ name: 'products' }">Danh sách sản phẩm</router-link>
                    </li>
                    <li class="separator">
                        <i class="icon-arrow-right"></i>
                    </li>
                    <li class="nav-item">
                        <router-link :to="{ name: 'addProduct' }">{{ route.meta.title }}</router-link>
                    </li>
                </ul>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-flex justify-content-between align-items-center">
                        <div class="card-title">{{ route.meta.title }}</div>
                    </div>
                </div>
                <div class="card-body">
                    <form @submit.prevent="updateProduct">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="name">Tên sản phẩm</label>
                                    <input type="text" class="form-control" id="name" placeholder="Nhập tên sản phẩm"
                                        v-model="product.name" />
                                </div>
                                <div class="form-group">
                                    <label>Giới tính</label><br />
                                    <div class="d-flex">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="gender" id="male"
                                                value="male" v-model="product.gender" />
                                            <label class="form-check-label" for="male">Nam</label>
                                        </div>
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="radio" name="gender" id="female"
                                                value="female" v-model="product.gender" />
                                            <label class="form-check-label" for="female">Nữ</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="unisex"
                                                value="unisex" v-model="product.gender" />
                                            <label class="form-check-label" for="unisex">Unisex</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="price">Giá</label>
                                    <input type="number" class="form-control" id="price" placeholder="Nhập giá sản phẩm"
                                        step="0.01" inputmode="decimal" v-model.number="product.price" />
                                </div>
                                <div class="form-group">
                                    <label for="category_id">Danh mục</label>
                                    <select class="form-select" id="category_id" v-model="product.category_id">
                                        <option value="" disabled>Chọn danh mục</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control" id="slug" placeholder="Nhập tên slug"
                                        v-model="product.slug" />
                                </div>
                                <div class="form-group">
                                    <label for="brandSelect">Thương hiệu <span class="text-danger">*</span></label>
                                    <select class="form-select" id="brandSelect" v-model="product.brand_id"
                                        v-if="brands && brands.length > 0">
                                        <option value="">Chọn thương hiệu</option>
                                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                    <p v-else class="text-muted">Đang tải thương hiệu...</p>
                                    <small v-if="errors.brand_id" class="form-text text-danger">{{ errors.brand_id[0]
                                        }}</small>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="image">Hình ảnh</label>
                                    <input type="file" class="form-control mb-3" id="image" @change="onFileChange"
                                        accept="image/*" />
                                    <img v-if="currentImageUrl" :src="currentImageUrl" alt="Product Image"
                                        style="width: 150px;">
                                    <span v-else>Không có ảnh hiện tại</span>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="removeMainImage"
                                            v-model="removeMainImage">
                                        <label class="form-check-label" for="removeMainImage">Xóa ảnh chính</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="description">Mô tả</label>
                                        <textarea class="form-control" id="description" rows="5"
                                            v-model="product.description"></textarea>
                                    </div>
                                </div>
                            </div>
<div class="col-12">
    <ScentGroupSelector
        v-model:selectedScentGroupIds="product.scentGroups.selectedScentGroupIds"
        v-model:scentGroupsData="product.scentGroups.scentGroupsData"
        :allScentGroups="allScentGroups"
    />
    <small v-if="errors.scent_groups" class="form-text text-danger">{{
        errors.scent_groups[0] }}</small>

    <div v-if="sortedScentProfiles.length > 0" class="mt-3">
        <h6>Mức độ hương:</h6>
        <div class="scent-strength-bars">
            <div v-for="scent in sortedScentProfiles" :key="scent.scent_group_id"
                class="scent-bar-item mb-2 d-flex align-items-center">
                <span class="scent-name me-2" :style="{
                    'min-width': '120px',
                    'max-width': '120px',
                    'white-space': 'nowrap',
                    'overflow': 'hidden',
                    'text-overflow': 'ellipsis',
                }">{{ scent.scent_group_name }}:</span>
                <div class="progress flex-grow-1" style="height: 20px;">
                    <div class="progress-bar" role="progressbar"
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
    </div>
                        </div>


                        <hr class="my-4">

                        <div class="row">
                            <div class="col-12">
                                <UsageProfile v-model:usageProfileData="product.usageProfile" />
                                <small v-if="errors.usage_profile" class="form-text text-danger">{{
                                    errors.usage_profile[0] }}</small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mt-4">
                            <div class="col-12">
                                <h4 class="mb-3">Biến thể Sản phẩm</h4>
                                <button type="button" class="btn btn-success btn-sm mb-3" @click="addVariant">
                                    <i class="fa fa-plus"></i> Thêm biến thể
                                </button>

                                <div v-if="product.variants && product.variants.length > 0">
                                    <div v-for="(variant, vIndex) in product.variants" :key="variant.id || vIndex"
                                        class="card mb-3 border">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            Biến thể #{{ vIndex + 1 }}
                                            <button type="button" class="btn btn-danger btn-sm"
                                                @click="removeVariant(vIndex)">
                                                Xóa biến thể
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label :for="`sku-${vIndex}`">SKU</label>
                                                        <input type="text" class="form-control" :id="`sku-${vIndex}`"
                                                            v-model="variant.sku" placeholder="SKU của biến thể">
                                                        <small v-if="errors[`variants.${vIndex}.sku`]"
                                                            class="form-text text-danger">{{
                                                                errors[`variants.${vIndex}.sku`][0] }}</small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label :for="`variant-price-${vIndex}`">Giá biến thể</label>
                                                        <input type="number" class="form-control"
                                                            :id="`variant-price-${vIndex}`"
                                                            v-model.number="variant.price" step="0.01"
                                                            placeholder="Giá của biến thể">
                                                        <small v-if="errors[`variants.${vIndex}.price`]"
                                                            class="form-text text-danger">{{
                                                                errors[`variants.${vIndex}.price`][0] }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label :for="`stock-${vIndex}`">Tồn kho</label>
                                                        <input type="number" class="form-control"
                                                            :id="`stock-${vIndex}`" v-model.number="variant.stock"
                                                            placeholder="Số lượng tồn kho">
                                                        <small v-if="errors[`variants.${vIndex}.stock`]"
                                                            class="form-text text-danger">{{
                                                                errors[`variants.${vIndex}.stock`][0] }}</small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label :for="`barcode-${vIndex}`">Mã vạch</label>
                                                        <input type="text" class="form-control"
                                                            :id="`barcode-${vIndex}`" v-model="variant.barcode"
                                                            placeholder="Mã vạch (nếu có)">
                                                    </div>
                                                    <div class="form-group">
                                                        <label :for="`status-${vIndex}`">Trạng thái</label>
                                                        <select class="form-select" :id="`status-${vIndex}`"
                                                            v-model="variant.status">
                                                            <option value="available">Có sẵn</option>
                                                            <option value="discontinued">Ngừng sản xuất</option>
                                                            <option value="out_of_stock">Hết hàng</option>
                                                        </select>
                                                        <small v-if="errors[`variants.${vIndex}.status`]"
                                                            class="form-text text-danger">{{
                                                                errors[`variants.${vIndex}.status`][0] }}</small>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label :for="`variant-description-${vIndex}`">Mô tả biến thể
                                                            (tùy chọn)</label>
                                                        <textarea class="form-control"
                                                            :id="`variant-description-${vIndex}`" rows="2"
                                                            v-model="variant.description"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <h5 class="mt-3">Thuộc tính của biến thể</h5>
                                            <button type="button" class="btn btn-info btn-sm mb-2"
                                                @click="addAttributeToVariant(vIndex)">
                                                <i class="fa fa-plus"></i> Thêm thuộc tính
                                            </button>

                                            <div v-for="(attrVal, avIndex) in variant.attributes"
                                                :key="attrVal.value_id || avIndex" class="row mb-2 align-items-center">
                                                <div class="col-md-4">
                                                    <select class="form-select" v-model="attrVal.attribute_id"
                                                        @change="onAttributeChange(vIndex, avIndex)">
                                                        <option value="" disabled>Chọn thuộc tính</option>
                                                        <option v-for="attr in attributes" :key="attr.id"
                                                            :value="attr.id">
                                                            {{ attr.name }}
                                                        </option>
                                                    </select>
                                                    <small
                                                        v-if="errors[`variants.${vIndex}.attributes.${avIndex}.attribute_id`]"
                                                        class="form-text text-danger">{{
                                                            errors[`variants.${vIndex}.attributes.${avIndex}.attribute_id`][0]
                                                        }}</small>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select" v-model="attrVal.value_id"
                                                        :disabled="!attrVal.attribute_id">
                                                        <option value="" disabled>Chọn giá trị</option>
                                                        <option v-for="val in getAttributeValues(attrVal.attribute_id)"
                                                            :key="val.id" :value="val.id">
                                                            {{ val.value }}
                                                        </option>
                                                    </select>
                                                    <small
                                                        v-if="errors[`variants.${vIndex}.attributes.${avIndex}.value_id`]"
                                                        class="form-text text-danger">{{
                                                            errors[`variants.${vIndex}.attributes.${avIndex}.value_id`][0]
                                                        }}</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        @click="removeAttributeFromVariant(vIndex, avIndex)">
                                                        Xóa
                                                    </button>
                                                </div>
                                            </div>
                                            <p v-if="!variant.attributes || variant.attributes.length === 0"
                                                class="text-muted">Chưa có thuộc tính nào cho biến thể này.</p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="alert alert-info">
                                    Sản phẩm này hiện không có biến thể nào. Thêm biến thể mới để quản lý.
                                </div>
                            </div>
                        </div>

                        <div class="card-action mt-4">
                            <button type="submit" class="btn btn-success me-2">
                                Cập nhật Sản phẩm
                            </button>
                            <router-link :to="{ name: 'products' }" class="btn btn-primary">
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
    if (imagePath instanceof File) {
        return URL.createObjectURL(imagePath);
    }
    return imagePath ? `http://localhost:8000/storage/${imagePath}` : null;
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
            })) : []
        };

        currentImageUrl.value = getImageUrl(product.value.image);
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
        const { data } = await axios.get('http://localhost:8000/api/admin/brands');
        brands.value = data.data;
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
        // --- Bắt đầu sửa đổi cho Usage Profile ---
        // Duyệt qua từng trường của usageProfile và thêm vào FormData
        for (const key in product.value.usageProfile) {
            const value = product.value.usageProfile[key];
            // Thêm tiền tố 'usage_profile' để Laravel dễ dàng nhận diện và validate
            formData.append(`usage_profile[${key}]`, value === null || value === undefined ? '' : value);
        }
        // --- Kết thúc sửa đổi cho Usage Profile ---

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