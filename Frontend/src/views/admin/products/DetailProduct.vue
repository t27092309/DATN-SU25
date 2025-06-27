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
                    <form>
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="name">Tên sản phẩm</label>
                                    <input type="text" class="form-control" id="name" placeholder="Nhập tên sản phẩm"
                                        disabled v-model="product.name" />
                                </div>
                                <div class="form-group">
                                    <label>Giới tính</label><br />
                                    <div class="d-flex">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="male"
                                                value="male" disabled v-model="product.gender" checked />
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
                                    <input type="number" class="form-control" id="price" placeholder="Nhập giá sản phẩm"
                                        step="0.01" inputmode="decimal" disabled v-model="product.price" />
                                </div>
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Danh mục</label>
                                    <select class="form-select" id="exampleFormControlSelect1" disabled
                                        v-model="product.category_id">
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ getCategoryName(product.category_id) }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="slug">Slug</label>
                                    <input type="text" class="form-control" id="slug" placeholder="Nhập tên slug"
                                        disabled v-model="product.slug" />
                                </div>
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Brand</label>
                                    <select class="form-select" id="exampleFormControlSelect1" disabled
                                        v-model="product.brand_id">
                                        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                            {{ getBrandName(product.brand_id) }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md">
                                <div class="form-group">
                                    <label for="image">Hình ảnh</label>
                                    <input type="text" class="form-control" id="image" placeholder="Nhập link hình ảnh"
                                        disabled v-model="product.image" />
                                    <img :src="product.image" alt="" width="170px" class="mt-2">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="comment">Mô tả</label>
                                    <textarea class="form-control" id="comment" rows="5" disabled
                                        v-model="product.description"></textarea>
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
                                                <th>SKU</th>
                                                <th>Giá</th>
                                                <th>Tồn kho</th>
                                                <th>Đã bán</th>
                                                <th>Trạng thái</th>
                                                <th>Mã vạch</th>
                                                <th>Mô tả biến thể</th>
                                                <th>Thuộc tính</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="variant in product.variants" :key="variant.id">
                                                <td>{{ variant.sku }}</td>
                                                <td>{{ variant.price }}</td>
                                                <td>{{ variant.stock }}</td>
                                                <td>{{ variant.sold }}</td>
                                                <td>{{ variant.status }}</td>
                                                <td>{{ variant.barcode }}</td>
                                                <td>{{ variant.description }}</td>
                                                <td>
                                                    <span v-if="variant.attributes && variant.attributes.length > 0">
                                                        <ul class="list-unstyled mb-0">
                                                            <li v-for="attr in variant.attributes" :key="attr.value_id">
                                                                <strong>{{ attr.attribute_name }}:</strong> {{ attr.value_name }}
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
                        <div class="row mt-4" v-else>
                            <div class="col-12">
                                <p class="text-info">Sản phẩm này hiện không có biến thể nào.</p>
                            </div>
                        </div>
                        <div class="card-action">
                            <router-link to="/admin/products" class="btn btn-primary">
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
    import { onMounted, ref, reactive } from 'vue';
    import { useRoute } from 'vue-router';
    import axios from 'axios'
    // import router from '@/router'; // Không cần thiết nếu bạn chỉ dùng useRoute để lấy params

    const route = useRoute();
    const categories = ref([]);
    const brands = ref([]);
    const product = ref({
        name: "",
        slug: "",
        image: "",
        description: "",
        gender: "",
        price: "",
        category_id: "",
        brand_id: "",
        variants: [], // Khởi tạo mảng variants
    });
    const { params } = useRoute();

    const fetchProduct = async () => {
        try {
            // Đảm bảo URL là đúng cho API admin để lấy chi tiết sản phẩm
            const { data } = await axios.get(`http://localhost:8000/api/admin/products/${params.id}`);
            product.value = data.data; // data.data vì resource trả về trong key 'data'
        } catch (error) {
            console.error('Không lấy được sản phẩm:', error);
            // Xử lý lỗi, ví dụ: chuyển hướng hoặc hiển thị thông báo lỗi
        }
    };

    const fetchCategory = async () => {
        try {
            const { data } = await axios.get(`http://localhost:8000/api/admin/categories`)
            categories.value = data.data
        } catch (error) {
            alert('Co loi xay ra: ' + error.message)
        }
    }

    const fetchBrand = async () => {
        try {
            const { data } = await axios.get('http://localhost:8000/api/admin/brands');
            brands.value = data.data;
        } catch (error) {
            alert('Có lỗi xảy ra khi lấy danh sách thương hiệu: ' + error.message);
            brands.value = [];
        }
    };

    const getCategoryName = (categoryId) => {
        if (!Array.isArray(categories.value)) return 'Đang load...';
        const category = categories.value.find(c => c.id === categoryId);
        return category ? category.name : 'Đang load...';
    };

    const getBrandName = (brandId) => {
        if (!Array.isArray(brands.value)) return 'Không rõ thương hiệu';
        const brand = brands.value.find(b => b.id === brandId);
        return brand ? brand.name : 'Đang load...';
    };

    onMounted(() => {
        fetchProduct();
        fetchCategory();
        fetchBrand();
    });
</script>

<style scoped>
    .custom-hover-link:hover {
        color: white !important;
    }
</style>