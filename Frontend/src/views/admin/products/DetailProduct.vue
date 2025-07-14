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
                        <span class="text-primary">{{ route.meta.title }}</span>
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
                    <form v-if="product.id">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="name">Tên sản phẩm</label>
                                    <input type="text" class="form-control" id="name" placeholder="Nhập tên sản phẩm"
                                        disabled v-model="product.name" />
                                </div>
                                <div class="form-group">
                                    <label>Giới tính</label><br />
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="male"
                                                value="male" disabled v-model="product.gender" />
                                            <label class="form-check-label" for="male">Nam</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="female"
                                                value="female" disabled v-model="product.gender" />
                                            <label class="form-check-label" for="female">Nữ</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="unisex"
                                                value="unisex" disabled v-model="product.gender" />
                                            <label class="form-check-label" for="unisex">Unisex</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="price">Giá</label>
                                    <input type="text" class="form-control" id="price" placeholder="Giá sản phẩm"
                                        disabled :value="formatCurrency(product.price)" />
                                </div>
                                <div class="form-group">
                                    <label for="categorySelect">Danh mục</label>
                                    <input type="text" class="form-control" id="categorySelect" disabled
                                        :value="getCategoryName(product.category_id)" />
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control" id="slug" placeholder="Slug sản phẩm"
                                        disabled v-model="product.slug" />
                                </div>
                                <div class="form-group">
                                    <label for="brandSelect">Thương hiệu</label>
                                    <input type="text" class="form-control" id="brandSelect" disabled
                                        :value="getBrandName(product.brand_id)" />
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4" v-if="product.image">
                                <div class="form-group">
                                    <label>Hình ảnh chính</label><br />
                                    <img :src="product.image" alt="Hình ảnh chính" style="max-width: 200px; border-radius: 5px;" class="mt-2" />
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-8" v-if="product.images && product.images.length > 0">
                                <div class="form-group">
                                    <label>Thư viện ảnh sản phẩm</label><br />
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <img v-for="image in product.images" :key="image.id"
                                            :src="image.image_url" alt="Ảnh thư viện"
                                            style="max-width: 100px; height: 100px; object-fit: cover; border-radius: 5px;" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="description">Mô tả</label>
                                    <textarea class="form-control" id="description" rows="5" disabled
                                        v-model="product.description"></textarea>
                                </div>
                            </div>
                        </div>
                        <hr />

                        <div class="row mt-3">
                            <div class="col-12">
                                <h5 class="mb-3">Nhóm hương & Mức độ hương</h5>
                                <div v-if="sortedScentProfiles.length > 0">
                                    <div class="scent-strength-bars">
                                        <div v-for="scent in sortedScentProfiles" :key="scent.scent_group_id"
                                            class="scent-bar-item mb-2 d-flex align-items-center">
                                            <span class="scent-name me-2" :style="{
                                                'min-width': '120px',
                                                'max-width': '120px',
                                                'white-space': 'nowrap',
                                                'overflow': 'hidden',
                                                'text-overflow': 'ellipsis',
                                            }">{{ scent.scent_group_name || 'Không xác định' }}:</span>
                                            <div class="progress flex-grow-1" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar"
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
                                <p v-else class="text-muted">Sản phẩm này chưa có thông tin nhóm hương.</p>
                            </div>
                        </div>
                        <hr />

                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Thông tin sử dụng sản phẩm</h5>
                                <div v-if="product.usage_profile">
                                    <div class="usage-profile-section mb-3">
                                        <h6>Mức độ phù hợp mùa</h6>
                                        <div class="level-item mb-2" v-for="(season, key) in seasons" :key="key">
                                            <div class="d-flex align-items-center">
                                                <label class="form-label mb-0 me-2 text-capitalize" style="min-width: 80px;">{{ season.label }}:</label>
                                                <div class="level-bar-container flex-grow-1">
                                                    <div class="level-bar"
                                                        :style="{ width: (product.usage_profile[key] || 0) + '%', backgroundColor: season.color }">
                                                    </div>
                                                </div>
                                                <span class="percentage-display ms-2" :style="{ color: season.color }">
                                                    {{ product.usage_profile[key] || 0 }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="usage-profile-section mb-3">
                                        <h6>Mức độ phù hợp ngày/đêm</h6>
                                        <div class="level-item mb-2">
                                            <div class="d-flex align-items-center">
                                                <label class="form-label mb-0 me-2" style="min-width: 80px;">Ngày:</label>
                                                <div class="level-bar-container flex-grow-1">
                                                    <div class="level-bar"
                                                        :style="{ width: (product.usage_profile.suitable_day || 0) + '%', backgroundColor: '#FFD700' }">
                                                    </div>
                                                </div>
                                                <span class="percentage-display ms-2" style="color: #FFD700;">
                                                    {{ product.usage_profile.suitable_day || 0 }}%
                                                </span>
                                            </div>
                                        </div>
                                        <div class="level-item mb-2">
                                            <div class="d-flex align-items-center">
                                                <label class="form-label mb-0 me-2" style="min-width: 80px;">Đêm:</label>
                                                <div class="level-bar-container flex-grow-1">
                                                    <div class="level-bar"
                                                        :style="{ width: (product.usage_profile.suitable_night || 0) + '%', backgroundColor: '#4682B4' }">
                                                    </div>
                                                </div>
                                                <span class="percentage-display ms-2" style="color: #4682B4;">
                                                    {{ product.usage_profile.suitable_night || 0 }}%
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <label class="col-sm-4 col-form-label">Độ lưu hương:</label>
                                        <div class="col-sm-8 col-form-label">
                                            <strong>{{ product.usage_profile.longevity_hours || 'N/A' }} giờ</strong>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <label class="col-sm-4 col-form-label">Độ tỏa hương:</label>
                                        <div class="col-sm-8 col-form-label">
                                            <strong>{{ product.usage_profile.sillage_range_m || 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-muted">Sản phẩm này chưa có thông tin sử dụng.</p>
                            </div>
                        </div>
                        <hr />

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Loại sản phẩm</label><br />
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="noVariants" :value="false"
                                            v-model="product.has_variants_computed" disabled />
                                        <label class="form-check-label" for="noVariants">Sản phẩm đơn giản</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="hasVariants" :value="true"
                                            v-model="product.has_variants_computed" disabled />
                                        <label class="form-check-label" for="hasVariants">Sản phẩm có biến thể</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4" v-if="product.variants && product.variants.length > 0">
                            <div class="col-12">
                                <h4 class="mb-3">Các Biến thể Sản phẩm</h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Ảnh</th>
                                                <th>SKU</th>
                                                <th>Giá</th>
                                                <th>Tồn kho</th>
                                                <th>Đã bán</th>
                                                <th>Trạng thái</th>
                                                <th>Mã vạch</th>
                                                <th>Mô tả</th>
                                                <th>Thuộc tính</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="variant in product.variants" :key="variant.id">
                                                <td>
                                                    <img v-if="variant.image_url" :src="variant.image_url" alt="Variant Image" style="max-width: 50px; border-radius: 3px;" />
                                                    <span v-else>N/A</span>
                                                </td>
                                                <td>{{ variant.sku }}</td>
                                                <td>{{ formatCurrency(variant.price) }}</td>
                                                <td>{{ variant.stock }}</td>
                                                <td>{{ variant.sold }}</td>
                                                <td>{{ variant.status }}</td>
                                                <td>{{ variant.barcode || 'N/A' }}</td>
                                                <td>{{ variant.description || 'N/A' }}</td>
                                                <td>
                                                    <span v-if="variant.attributes && variant.attributes.length > 0">
                                                        <ul class="list-unstyled mb-0">
                                                            <li v-for="attr in variant.attributes" :key="attr.value_id">
                                                                <strong>{{ attr.attribute_name || 'Thuộc tính' }}:</strong> {{ attr.value_name || 'Giá trị' }}
                                                            </li>
                                                        </ul>
                                                    </span>
                                                    <span v-else>Không có thuộc tính</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-4" v-else-if="product.has_variants_computed">
                             <div class="col-12">
                                <p class="text-info">Sản phẩm này được cấu hình có biến thể nhưng chưa có dữ liệu biến thể nào.</p>
                            </div>
                        </div>
                         <div class="row mt-4" v-else>
                            <div class="col-12">
                                <p class="text-info">Sản phẩm này là sản phẩm đơn giản (không có biến thể).</p>
                            </div>
                        </div>

                        <div class="card-action">
                            <router-link :to="{ name: 'products' }" class="btn btn-primary">
                                Quay lại
                            </router-link>
                             <router-link :to="{ name: 'editProduct', params: { id: product.id } }" class="btn btn-warning ms-2">
                                Chỉnh sửa sản phẩm
                            </router-link>
                        </div>
                    </form>
                    <div v-else class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                        <p class="mt-3">Đang tải thông tin sản phẩm...</p>
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

<style scoped>
.custom-hover-link:hover {
    color: white !important;
}

/* Styles cho thanh mức độ trong UsageProfile */
.level-bar-container {
    flex-shrink: 0;
    width: 150px;
    height: 10px;
    background-color: #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
}

.level-bar {
    height: 100%;
    transition: width 0.2s ease-in-out;
    border-radius: 5px;
}

.percentage-display {
    font-weight: bold;
    font-size: 0.95rem;
    min-width: 45px;
    text-align: right;
}

/* Styles cho progress bar của nhóm hương */
.scent-strength-bars .progress {
    height: 20px;
    border-radius: 0.25rem;
    background-color: #e9ecef;
}

.scent-strength-bars .progress-bar {
    transition: width 0.4s ease-in-out;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
    color: white;
}
</style>