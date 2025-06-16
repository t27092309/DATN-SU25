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
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="male"
                                                value="male" v-model="product.gender" checked />
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
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-group">
                                    <label for="price">Giá</label>
                                    <input type="number" class="form-control" id="price" placeholder="Nhập giá sản phẩm"
                                        step="0.01" inputmode="decimal" v-model="product.price" />
                                </div>
                                <div class="form-group">
                                    <label for="exampleFormControlSelect1">Danh mục</label>
                                    <select class="form-select" id="exampleFormControlSelect1"
                                        v-model="product.category_id">
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ getBrandName(product.category_id) }}
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
                                    <label for="exampleFormControlSelect1">Brand</label>
                                    <select class="form-select" id="exampleFormControlSelect1"
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
                                        v-model="product.image" />
                                    <img :src="product.image" alt="" width="170px" class="mt-2">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="comment">Mô tả</label>
                                    <textarea class="form-control" id="comment" rows="5"
                                        v-model="product.description"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-action">
                            <button type="submit" class="btn btn-success me-2">
                                Submit
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
    import axios from 'axios'
    import router from '@/router';

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
    });
    const { params } = useRoute();

    const fetchProduct = async () => {
        try {
            const { data } = await axios.get(`http://localhost:8000/api/admin/products/${params.id}`);
            product.value = data.data;
            console.log(product.value); return

        } catch (error) {
            alert('Loi xay ra: ' + error.message)
        }
    }

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

    const updateProduct = async () => {
        try {
            await axios.put(`http://localhost:8000/api/admin/products/${params.id}`, product.value);
            alert('Sua thanh cong!')
            router.push("/admin/products");
        } catch (error) {
            if (error.response && error.response.status === 422) {
                console.log("💥 Lỗi từ Laravel:", error.response.data.errors);
                alert(
                    "❌ Lỗi: " +
                    JSON.stringify(error.response.data.errors, null, 2)
                );
            } else {
                console.log("❌ Lỗi khác:", error.message);
            }
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
