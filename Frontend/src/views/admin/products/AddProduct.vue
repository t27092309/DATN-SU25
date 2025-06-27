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
                    <form @submit.prevent="addProduct">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="name">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" placeholder="Nhập tên sản phẩm"
                                        v-model="product.name" />
                                    <small v-if="errors.name" class="form-text text-danger">{{ errors.name[0] }}</small>
                                </div>
                                <div class="form-group">
                                    <label>Giới tính <span class="text-danger">*</span></label><br />
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="male"
                                                value="male" v-model="product.gender" />
                                            <label class="form-check-label" for="male">Nam</label>
                                        </div>
                                        <div class="form-check">
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
                                    <small v-if="errors.gender" class="form-text text-danger">{{ errors.gender[0]
                                        }}</small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="categorySelect">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select" id="categorySelect" v-model="product.category_id">
                                        <option value="">Chọn danh mục</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <small v-if="errors.category_id" class="form-text text-danger">{{
                                        errors.category_id[0] }}</small>
                                </div>
                                <div class="form-group">
                                    <label for="brandSelect">Thương hiệu <span class="text-danger">*</span></label>
                                    <select class="form-select" id="brandSelect" v-model="product.brand_id">
                                        <option value="">Chọn thương hiệu</option>
                                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                            {{ brand.name }}
                                        </option>
                                    </select>
                                    <small v-if="errors.brand_id" class="form-text text-danger">{{ errors.brand_id[0]
                                        }}</small>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="slug">Slug (Tự động tạo)</label>
                                    <input type="text" class="form-control" id="slug" placeholder="Slug sản phẩm"
                                        v-model="product.slug" disabled />
                                </div>
                                <div class="form-group">
                                    <label for="image">Hình ảnh chính</label>
                                    <input type="file" class="form-control" id="image" @change="onFileChangeMainImage"
                                        accept="image/*" />
                                    <small v-if="errors.image" class="form-text text-danger">{{ errors.image[0]
                                        }}</small>
                                </div>
                                <div v-if="imageUrlPreview" class="mt-2">
                                    <label>Ảnh xem trước:</label><br />
                                    <img :src="imageUrlPreview" alt="Image Preview"
                                        style="max-width: 200px; border-radius: 5px;" />
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="description">Mô tả</label>
                                    <textarea class="form-control" id="description" rows="5"
                                        v-model="product.description"></textarea>
                                    <small v-if="errors.description" class="form-text text-danger">{{
                                        errors.description[0] }}</small>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Loại sản phẩm <span class="text-danger">*</span></label><br />
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="noVariants" :value="false"
                                            v-model="product.has_variants" />
                                        <label class="form-check-label" for="noVariants">Sản phẩm đơn giản</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" id="hasVariants" :value="true"
                                            v-model="product.has_variants" />
                                        <label class="form-check-label" for="hasVariants">Sản phẩm có biến thể</label>
                                    </div>
                                    <small v-if="errors.has_variants" class="form-text text-danger">{{
                                        errors.has_variants[0] }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row" v-if="product.has_variants === false">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="simplePrice">Giá sản phẩm <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="simplePrice"
                                        placeholder="Nhập giá sản phẩm" v-model="product.price" min="0" />
                                    <small v-if="errors.price" class="form-text text-danger">{{ errors.price[0]
                                        }}</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="simpleStock">Số lượng tồn kho <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="simpleStock"
                                        placeholder="Nhập số lượng tồn kho" v-model="product.stock" min="0" />
                                    <small v-if="errors.stock" class="form-text text-danger">{{ errors.stock[0]
                                        }}</small>
                                </div>
                            </div>
                        </div>

                        <div v-if="product.has_variants === true">
                            <h4 class="mt-4">Chọn thuộc tính và giá trị</h4>
                            <div class="row">
                                <div class="col-12">
                                    <small v-if="errors.variants" class="form-text text-danger mb-3 d-block">{{
                                        errors.variants[0] }}</small>
                                    <div v-for="attribute in attributes" :key="attribute.id"
                                        class="form-group mb-3 p-3 border rounded">
                                        <h6>{{ attribute.name }}</h6>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div v-for="value in attribute.attribute_values" :key="value.id"
                                                class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox"
                                                    :id="`attr-${attribute.id}-val-${value.id}`"
                                                    :value="{ attributeId: attribute.id, valueId: value.id, valueName: value.value, attributeName: attribute.name }"
                                                    v-model="selectedAttributeValues[attribute.id]"
                                                    @change="generateVariants" />
                                                <label class="form-check-label"
                                                    :for="`attr-${attribute.id}-val-${value.id}`">
                                                    {{ value.value }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h4 class="mt-4">Các biến thể đã tạo</h4>
                            <div v-if="product.variants.length > 0" class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tên biến thể</th>
                                            <th>SKU <span class="text-danger">*</span></th>
                                            <th>Giá <span class="text-danger">*</span></th>
                                            <th>Tồn kho <span class="text-danger">*</span></th>
                                            <th>Ảnh biến thể</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(variant, index) in product.variants" :key="variant.tempId">
                                            <td>{{ variant.name }}</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm"
                                                    v-model="variant.sku" :id="'variantSku' + variant.tempId" />
                                                <small v-if="errors[`variants.${index}.sku`]"
                                                    class="text-danger d-block">
                                                    {{ errors[`variants.${index}.sku`][0] }}
                                                </small>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm"
                                                    v-model="variant.price" min="0"
                                                    :id="'variantPrice' + variant.tempId" />
                                                <small v-if="errors[`variants.${index}.price`]"
                                                    class="text-danger d-block">
                                                    {{ errors[`variants.${index}.price`][0] }}
                                                </small>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm"
                                                    v-model="variant.stock" min="0"
                                                    :id="'variantStock' + variant.tempId" />
                                                <small v-if="errors[`variants.${index}.stock`]"
                                                    class="text-danger d-block">
                                                    {{ errors[`variants.${index}.stock`][0] }}
                                                </small>
                                            </td>
                                            <td>
                                                <input type="file" class="form-control form-control-sm"
                                                    @change="e => onFileChangeVariantImage(e, index)"
                                                    accept="image/*" />
                                                <div v-if="variant.imageUrlPreview" class="mt-1">
                                                    <img :src="variant.imageUrlPreview" alt="Preview"
                                                        style="max-width: 50px; border-radius: 3px;" />
                                                </div>
                                                <small v-if="errors[`variants.${index}.image`]"
                                                    class="text-danger d-block">
                                                    {{ errors[`variants.${index}.image`][0] }}
                                                </small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    @click="removeSpecificVariant(index)">
                                                    Xóa
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="text-muted">Chưa có biến thể nào được tạo. Vui lòng chọn thuộc tính và giá
                                trị ở trên.</p>
                        </div>

                        <div class="card-action mt-4">
                            <button type="submit" class="btn btn-success me-2">Thêm sản phẩm</button>
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
import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import router from '@/router';
import Swal from 'sweetalert2';

// Hàm generateSlug có thể nằm trong một file utils/slugUtils.js và được import.
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
const attributes = ref([]); // Danh sách tất cả thuộc tính và giá trị từ API
const selectedAttributeValues = ref({}); // Đối tượng lưu trữ các giá trị thuộc tính ĐÃ CHỌN, ví dụ: { 1: [{id:101, value:'Đỏ'}], 2: [{id:201, value:'S'}] }

const product = ref({
    name: '',
    slug: '',
    description: '',
    gender: 'male',
    category_id: '',
    brand_id: '',
    has_variants: false,
    price: '', // Cho sản phẩm đơn giản
    stock: '', // Cho sản phẩm đơn giản
    variants: [], // Mảng các biến thể được tạo tự động và nhập dữ liệu
});

const mainImageFile = ref(null);
const imageUrlPreview = ref(null);
const errors = ref({});

// Watch for changes in product.name to auto-generate slug
watch(() => product.value.name, (newName) => {
    product.value.slug = generateSlug(newName);
});

// Watch for changes in has_variants to clear/reset fields
watch(() => product.value.has_variants, (newVal) => {
    if (newVal === true) { // Chuyển sang có biến thể
        product.value.price = '';
        product.value.stock = '';
        // Không tự động thêm biến thể mặc định nữa, chờ người dùng chọn thuộc tính
        // Thay vào đó, nếu đã có selectedAttributeValues, tạo lại biến thể
        generateVariants();
    } else { // Chuyển sang không có biến thể
        product.value.variants = [];
        // Clear all selected attribute values
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
        const { data } = await axios.get(`http://localhost:8000/api/admin/brands`);
        brands.value = data.data;
    } catch (error) {
        console.error('Lỗi khi tải thương hiệu:', error);
        Swal.fire('Lỗi!', 'Không thể tải danh sách thương hiệu.', 'error');
    }
};

const fetchAttributes = async () => {
    try {
        const { data } = await axios.get(`http://localhost:8000/api/admin/attributes`);
        attributes.value = data.data;
        // Khởi tạo selectedAttributeValues cho mỗi thuộc tính
        attributes.value.forEach(attr => {
            selectedAttributeValues.value[attr.id] = [];
        });
    } catch (error) {
        console.error('Lỗi khi tải thuộc tính:', error);
        Swal.fire('Lỗi!', 'Không thể tải danh sách thuộc tính.', 'error');
    }
};

onMounted(() => {
    fetchCategory();
    fetchBrand();
    fetchAttributes();
});

// Hàm xử lý khi chọn file ảnh chính
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

// Hàm xử lý khi chọn file ảnh cho biến thể
const onFileChangeVariantImage = (e, index) => {
    const file = e.target.files[0];
    if (file) {
        product.value.variants[index].imageFile = file; // Lưu File object
        product.value.variants[index].imageUrlPreview = URL.createObjectURL(file); // Tạo URL xem trước
    } else {
        product.value.variants[index].imageFile = null;
        // Revoke previous URL if any
        if (product.value.variants[index].imageUrlPreview) {
            URL.revokeObjectURL(product.value.variants[index].imageUrlPreview);
        }
        product.value.variants[index].imageUrlPreview = null;
    }
};

// Hàm chính để tự động tạo biến thể
const generateVariants = () => {
    // Lấy tất cả các giá trị thuộc tính đã chọn mà không rỗng
    const activeAttributeValueGroups = Object.values(selectedAttributeValues.value)
        .filter(group => group.length > 0);

    if (activeAttributeValueGroups.length === 0) {
        // Nếu không có nhóm thuộc tính nào được chọn, xóa tất cả biến thể
        product.value.variants.forEach(variant => {
            if (variant.imageUrlPreview) URL.revokeObjectURL(variant.imageUrlPreview);
        });
        product.value.variants = [];
        return;
    }

    // Tạo tất cả các tổ hợp
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

    // Chuyển đổi combinations thành cấu trúc biến thể mong muốn
    const newVariants = combinations.map(combination => {
        // Tạo tên biến thể (ví dụ: "Đỏ / S")
        const name = combination.map(val => val.valueName).join(' / ');
        // Lấy mảng các ID giá trị thuộc tính
        const attribute_values_ids = combination.map(val => val.valueId);

        // Tìm biến thể hiện có nếu có cùng các giá trị thuộc tính để giữ lại dữ liệu nhập
        const existingVariant = product.value.variants.find(v =>
            JSON.stringify(v.attribute_values.sort()) === JSON.stringify(attribute_values_ids.sort())
        );

        return {
            tempId: existingVariant ? existingVariant.tempId : Date.now() + Math.random(), // Giữ tempId nếu có
            name: name,
            sku: existingVariant ? existingVariant.sku : '',
            price: existingVariant ? existingVariant.price : null,
            stock: existingVariant ? existingVariant.stock : null,
            imageFile: existingVariant ? existingVariant.imageFile : null,
            imageUrlPreview: existingVariant ? existingVariant.imageUrlPreview : null,
            attribute_values: attribute_values_ids,
        };
    });

    // Xóa các URL preview của biến thể cũ không còn tồn tại
    product.value.variants.forEach(oldVariant => {
        const stillExists = newVariants.some(newVariant => newVariant.tempId === oldVariant.tempId);
        if (!stillExists && oldVariant.imageUrlPreview) {
            URL.revokeObjectURL(oldVariant.imageUrlPreview);
        }
    });

    product.value.variants = newVariants;
};

// Hàm xóa một biến thể cụ thể khỏi bảng (có thể xóa thủ công)
const removeSpecificVariant = (index) => {
    if (product.value.variants[index].imageUrlPreview) {
        URL.revokeObjectURL(product.value.variants[index].imageUrlPreview);
    }
    product.value.variants.splice(index, 1);
};

const addProduct = async () => {
    errors.value = {}; // Reset lỗi mỗi khi gửi form
    try {
        const formData = new FormData();

        // Append các trường thông tin sản phẩm chính
        for (const key in product.value) {
            if (key !== 'slug' && key !== 'variants' && product.value[key] !== null && product.value[key] !== '') {
                for (const key in product.value) {
                    if (key === 'has_variants') {
                        // Chuyển đổi boolean thành 0 hoặc 1
                        formData.append('has_variants', product.value.has_variants ? 1 : 0);
                    } else if (key !== 'slug' && key !== 'variants' && product.value[key] !== null && product.value[key] !== '') {
                        formData.append(key, product.value[key]);
                    }
                }
            }
        }

        // Append ảnh chính nếu có
        if (mainImageFile.value) {
            formData.append('image', mainImageFile.value);
        }

        // Append biến thể nếu product.has_variants là true
        if (product.value.has_variants) {
            if (product.value.variants.length === 0) {
                Swal.fire('Lỗi!', 'Bạn phải tạo ít nhất một biến thể cho sản phẩm có biến thể.', 'error');
                errors.value.variants = ['Bạn phải tạo ít nhất một biến thể.']; // Thêm lỗi vào object errors
                return; // Ngăn không cho form gửi đi
            }
            product.value.variants.forEach((variant, index) => {
                // Cần đảm bảo các trường này không null hoặc rỗng khi gửi đi
                formData.append(`variants[${index}][sku]`, variant.sku || '');
                formData.append(`variants[${index}][price]`, variant.price || '');
                formData.append(`variants[${index}][stock]`, variant.stock || '');

                if (variant.imageFile) {
                    formData.append(`variants[${index}][image]`, variant.imageFile);
                }

                // Append các giá trị thuộc tính đã chọn
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
</style>