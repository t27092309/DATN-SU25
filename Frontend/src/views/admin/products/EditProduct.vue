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
                                    <label for="brand_id">Brand</label>
                                    <select class="form-select" id="brand_id" v-model="product.brand_id">
                                        <option value="" disabled>Chọn thương hiệu</option>
                                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="image">Hình ảnh</label>
                                    <input type="file" class="form-control mb-3" id="image" @change="onFileChange"
                                        accept="image/*" />
                                    <img v-if="currentImageUrl" :src="currentImageUrl" alt="Product Image" style="width: 150px;">
                                    <span v-else>Không có ảnh hiện tại</span>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="removeMainImage" v-model="removeMainImage">
                                        <label class="form-check-label" for="removeMainImage">Xóa ảnh chính</label>
                                    </div>
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

                        <hr class="my-4">

                        <div class="row mt-4">
                            <div class="col-12">
                                <h4 class="mb-3">Biến thể Sản phẩm</h4>
                                <button type="button" class="btn btn-success btn-sm mb-3" @click="addVariant">
                                    <i class="fa fa-plus"></i> Thêm biến thể
                                </button>

                                <div v-if="product.variants && product.variants.length > 0">
                                    <div v-for="(variant, vIndex) in product.variants" :key="variant.id || vIndex" class="card mb-3 border">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            Biến thể #{{ vIndex + 1 }}
                                            <button type="button" class="btn btn-danger btn-sm" @click="removeVariant(vIndex)">
                                                Xóa biến thể
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label :for="`sku-${vIndex}`">SKU</label>
                                                        <input type="text" class="form-control" :id="`sku-${vIndex}`" v-model="variant.sku" placeholder="SKU của biến thể">
                                                    </div>
                                                    <div class="form-group">
                                                        <label :for="`variant-price-${vIndex}`">Giá biến thể</label>
                                                        <input type="number" class="form-control" :id="`variant-price-${vIndex}`" v-model.number="variant.price" step="0.01" placeholder="Giá của biến thể">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label :for="`stock-${vIndex}`">Tồn kho</label>
                                                        <input type="number" class="form-control" :id="`stock-${vIndex}`" v-model.number="variant.stock" placeholder="Số lượng tồn kho">
                                                    </div>
                                                    <div class="form-group">
                                                        <label :for="`barcode-${vIndex}`">Mã vạch</label>
                                                        <input type="text" class="form-control" :id="`barcode-${vIndex}`" v-model="variant.barcode" placeholder="Mã vạch (nếu có)">
                                                    </div>
                                                    <div class="form-group">
                                                        <label :for="`status-${vIndex}`">Trạng thái</label>
                                                        <select class="form-select" :id="`status-${vIndex}`" v-model="variant.status">
                                                            <option value="available">Có sẵn</option>
                                                            <option value="discontinued">Ngừng sản xuất</option>
                                                            <option value="out_of_stock">Hết hàng</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label :for="`variant-description-${vIndex}`">Mô tả biến thể (tùy chọn)</label>
                                                        <textarea class="form-control" :id="`variant-description-${vIndex}`" rows="2" v-model="variant.description"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <h5 class="mt-3">Thuộc tính của biến thể</h5>
                                            <button type="button" class="btn btn-info btn-sm mb-2" @click="addAttributeToVariant(vIndex)">
                                                <i class="fa fa-plus"></i> Thêm thuộc tính
                                            </button>

                                            <div v-for="(attrVal, avIndex) in variant.attributes" :key="attrVal.value_id || avIndex" class="row mb-2 align-items-center">
                                                <div class="col-md-4">
                                                    <select class="form-select" v-model="attrVal.attribute_id" @change="onAttributeChange(vIndex, avIndex)">
                                                        <option value="" disabled>Chọn thuộc tính</option>
                                                        <option v-for="attr in attributes" :key="attr.id" :value="attr.id">
                                                            {{ attr.name }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select" v-model="attrVal.value_id" :disabled="!attrVal.attribute_id">
                                                        <option value="" disabled>Chọn giá trị</option>
                                                        <option v-for="val in getAttributeValues(attrVal.attribute_id)" :key="val.id" :value="val.id">
                                                            {{ val.value }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="button" class="btn btn-sm btn-danger" @click="removeAttributeFromVariant(vIndex, avIndex)">
                                                        Xóa
                                                    </button>
                                                </div>
                                            </div>
                                            <p v-if="!variant.attributes || variant.attributes.length === 0" class="text-muted">Chưa có thuộc tính nào cho biến thể này.</p>
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
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import router from '@/router';
import Swal from 'sweetalert2';

const route = useRoute();

const categories = ref([]);
const brands = ref([]);
const attributes = ref([]); // To store all attributes and their values
const product = ref({
    name: "",
    slug: "",
    image: "", // This will hold the original image path from the backend
    description: "",
    gender: "",
    price: null, // Initialize as null for number inputs
    category_id: "",
    brand_id: "",
    variants: [], // Initialize variants as an empty array
});

const { params } = useRoute();
const imageFile = ref(null); // Holds the new image file to be uploaded
const currentImageUrl = ref(''); // Stores the URL for the image preview (either old or new)
const removeMainImage = ref(false); // Checkbox state for removing the main image

// --- Image Handling ---
const getImageUrl = (imagePath) => {
    // If imagePath is a full URL (e.g., from URL.createObjectURL), return it directly
    if (imagePath && imagePath.startsWith('blob:')) {
        return imagePath;
    }
    // Otherwise, construct the URL from storage path
    return imagePath ? `http://localhost:8000/storage/${imagePath}` : null;
};

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        imageFile.value = file;
        currentImageUrl.value = URL.createObjectURL(file); // Show preview of new image
        removeMainImage.value = false; // If a new image is selected, don't remove the old one
    } else {
        imageFile.value = null;
        // If file input is cleared, revert preview to existing image or null
        currentImageUrl.value = product.value.image ? getImageUrl(product.value.image) : null;
    }
};

// --- Data Fetching ---
const fetchProduct = async () => {
    try {
        const { data } = await axios.get(`http://localhost:8000/api/admin/products/${params.id}`);
        product.value = {
            ...data.data,
            price: data.data.price !== null ? parseFloat(data.data.price) : null,
        };

        // Map existing variant attributes into the expected frontend format
        if (product.value.variants && Array.isArray(product.value.variants)) {
            product.value.variants = product.value.variants.map(variant => ({
                ...variant,
                // Ensure price and stock are numbers from backend data
                price: variant.price !== null ? parseFloat(variant.price) : null,
                stock: variant.stock !== null ? parseInt(variant.stock) : null,
                // --- ĐẢM BẢO STATUS LÀ CHUỖI TỪ DB ---
                // Giữ nguyên giá trị status từ backend (available, discontinued, stock)
                status: variant.status || 'available', // Mặc định 'available' nếu không có giá trị
                attributes: variant.attributes ? variant.attributes.map(av => ({
                    attribute_id: av.attribute_id,
                    value_id: av.value_id 
                })) : []
            }));
        }

        // Set initial image URL for display
        currentImageUrl.value = getImageUrl(product.value.image);
    } catch (error) {
        console.error('Lỗi khi lấy sản phẩm:', error);
        Swal.fire({
            title: 'Lỗi!',
            text: 'Lỗi xảy ra khi lấy sản phẩm: ' + error.message,
            icon: 'error',
            confirmButtonText: 'Đóng',
        });
    }
};

const fetchCategory = async () => {
    try {
        const { data } = await axios.get(`http://localhost:8000/api/admin/categories`);
        categories.value = data.data;
    } catch (error) {
        Swal.fire({
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi lấy danh mục: ' + error.message,
            icon: 'error',
            confirmButtonText: 'Đóng',
        });
    }
};

const fetchBrand = async () => {
    try {
        const { data } = await axios.get('http://localhost:8000/api/admin/brands');
        brands.value = data.data;
    } catch (error) {
        Swal.fire({
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi lấy danh sách thương hiệu: ' + error.message,
            icon: 'error',
            confirmButtonText: 'Đóng',
        });
        brands.value = [];
    }
};

const fetchAttributes = async () => {
    try {
        // Assuming your API endpoint returns attributes with their nested attribute_values
        const { data } = await axios.get('http://localhost:8000/api/admin/attributes?with_values=true');
        attributes.value = data.data;
    } catch (error) {
        console.error('Lỗi khi lấy thuộc tính:', error);
        Swal.fire({
            title: 'Lỗi!',
            text: 'Có lỗi xảy ra khi lấy danh sách thuộc tính: ' + error.message,
            icon: 'error',
            confirmButtonText: 'Đóng',
        });
    }
};

// --- Variant Management ---
const addVariant = () => {
    product.value.variants.push({
        id: null, // Null for new variants, backend will assign
        sku: '',
        price: null, // Initialize as null for number input
        stock: null, // Initialize as null for number input
        sold: 0, // Default for new variants
        status: 'available', // <-- Khởi tạo trạng thái mặc định là 'available'
        barcode: '',
        description: '',
        attributes: [], // Array to hold attribute-value pairs for this variant
    });
};

const removeVariant = (index) => {
    Swal.fire({
        title: 'Bạn có chắc chắn?',
        text: "Biến thể này sẽ bị xóa!",
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
                'Biến thể của bạn đã được xóa.',
                'success'
            );
        }
    });
};

// --- Attribute Management for Variants ---
const addAttributeToVariant = (variantIndex) => {
    // Check for duplicate attributes before adding
    const existingAttrIds = product.value.variants[variantIndex].attributes.map(a => a.attribute_id);
    const availableAttributes = attributes.value.filter(attr => !existingAttrIds.includes(attr.id));

    if (availableAttributes.length > 0) {
        // Add the first available attribute by default
        product.value.variants[variantIndex].attributes.push({
            attribute_id: availableAttributes[0].id, // Default to the first available attribute
            value_id: '',
        });
    } else {
        Swal.fire('Thông báo', 'Không còn thuộc tính nào để thêm.', 'info');
    }
};

const removeAttributeFromVariant = (variantIndex, attributeValueIndex) => {
    product.value.variants[variantIndex].attributes.splice(attributeValueIndex, 1);
};

// Get attribute values for a specific attribute ID
const getAttributeValues = (attributeId) => {
    const attribute = attributes.value.find(attr => attr.id === attributeId);
    return attribute ? attribute.attribute_values : [];
};

// Reset value_id when attribute_id changes
const onAttributeChange = (variantIndex, attributeValueIndex) => {
    product.value.variants[variantIndex].attributes[attributeValueIndex].value_id = '';
};

// --- Form Submission ---
const updateProduct = async () => {
    try {
        const formData = new FormData();

        // Append main product fields
        for (const key in product.value) {
            // Exclude 'variants' and 'image' (if handled separately)
            if (key !== 'variants' && key !== 'image') {
                 // For numeric fields like price, send null as empty string if null
                 if (key === 'price') {
                    formData.append(key, product.value[key] !== null ? product.value[key] : '');
                 } else {
                    formData.append(key, product.value[key] === null ? '' : product.value[key]);
                 }
            }
        }

        // Append file if a new one is selected
        if (imageFile.value) {
            formData.append('image', imageFile.value);
        } else if (removeMainImage.value) {
            // If checkbox to remove image is checked, send a flag
            formData.append('remove_main_image', true);
            formData.append('image', ''); // Explicitly send empty string for image field
        } else if (product.value.image) {
            // If no new file and not removing, keep existing image path (backend will handle this)
            // No need to append product.value.image if it's already on the server,
            // as Laravel's update logic can retain it by default unless a new file/remove flag is sent.
        }


        // Handle variants data
        // Convert variants array to a JSON string and append.
        const variantsData = product.value.variants.map(variant => {
            const variantCopy = { ...variant }; // Create a shallow copy

            // Explicitly handle numeric fields for variants, sending null as empty string
            variantCopy.price = variantCopy.price !== null ? variantCopy.price : '';
            variantCopy.stock = variantCopy.stock !== null ? variantCopy.stock : '';
            // --- GỬI STATUS DƯỚI DẠNG CHUỖI NGUYÊN GỐC (available, discontinued, stock) ---
            variantCopy.status = variantCopy.status || 'available'; // Đảm bảo có giá trị hoặc mặc định

            // Extract only the attribute_value_ids for the backend sync
            variantCopy.attribute_value_ids = (variantCopy.attributes || [])
                .filter(av => av.value_id) // Only include selected values
                .map(av => av.value_id);

            delete variantCopy.attributes; // Remove the full attributes array to avoid sending redundant data

            return variantCopy;
        });

        formData.append('variants', JSON.stringify(variantsData));

        // Important: Laravel's PUT/PATCH methods don't natively support FormData with nested arrays well
        // when mixed with file uploads. Using _method=PUT is a common workaround.
        formData.append('_method', 'PUT');

        await axios.post(`http://localhost:8000/api/admin/products/${params.id}`, formData, {
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
            let errorMessage = "Có lỗi xảy ra khi cập nhật:\n";
            for (const field in error.response.data.errors) {
                if (field === 'variants') {
                    errorMessage += `- Dữ liệu biến thể không hợp lệ. Vui lòng kiểm tra lại các trường biến thể.\n`;
                } else if (field.startsWith('variants.')) {
                    // Specific validation errors for nested variant fields
                    const variantIndex = parseInt(field.split('.')[1]) + 1; // +1 for user-friendly numbering
                    const errorField = field.split('.')[2]; // e.g., 'price'
                    let fieldName = '';
                    switch (errorField) {
                        case 'sku': fieldName = 'SKU'; break;
                        case 'price': fieldName = 'Giá biến thể'; break;
                        case 'stock': fieldName = 'Tồn kho'; break;
                        case 'barcode': fieldName = 'Mã vạch'; break;
                        case 'description': fieldName = 'Mô tả biến thể'; break;
                        case 'status': fieldName = 'Trạng thái'; break; // Cập nhật tên trường
                        default: fieldName = errorField; break;
                    }
                    errorMessage += `- Biến thể ${variantIndex} (${fieldName}): ${error.response.data.errors[field].join(', ')}\n`;
                } else {
                    errorMessage += `- ${error.response.data.errors[field].join(', ')}\n`;
                }
            }
            Swal.fire({
                title: 'Lỗi Cập nhật!',
                html: `<pre style="text-align: left; white-space: pre-wrap; word-break: break-word;">${errorMessage}</pre>`, // Use html to preserve newlines
                icon: 'error',
                confirmButtonText: 'Đóng',
            });
        } else {
            console.error("❌ Lỗi khác:", error.message);
            Swal.fire({
                title: 'Lỗi!',
                text: 'Có lỗi không xác định xảy ra: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Đóng',
            });
        }
    }
};

onMounted(() => {
    fetchProduct();
    fetchCategory();
    fetchBrand();
    fetchAttributes();
});
</script>

<style scoped>
.custom-hover-link:hover {
    color: white !important;
}
.form-check {
    margin-right: 1.5rem; /* Add some space between radio buttons */
}
</style>