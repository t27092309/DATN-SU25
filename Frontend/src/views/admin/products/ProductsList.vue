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
                        <a href="#">{{ route.meta.title }}</a>
                    </li>
                </ul>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-flex justify-content-between align-items-center">
                        <h1>{{ route.meta.title }}</h1>
                        <router-link :to="{ name: 'addProduct' }"
                            class="btn btn-sm btn-outline-success custom-hover-link">
                            Thêm sản phẩm
                        </router-link>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr class="text-center">
                                <th scope="col">STT</th>
                                <th scope="col">Name</th>
                                <th scope="col">Image</th>
                                <th scope="col">Price</th>
                                <th scope="col">Category</th>
                                <th scope="col">Gender</th>
                                <th scope="col">Slug</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-center" v-for="(product, index) in products" :key="product.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ product.name }}</td>
                                <td>
                                    <img :src="product.image" alt="" style="width: 150px;">
                                </td>
                                <td>{{ product.price }}</td>
                                <td>{{ getCategoryName(product.category_id) }}</td>
                                <td>{{ product.gender }}</td>
                                <td>{{ product.slug }}</td>
                                <td>{{ getBrandName(product.brand_id) }}</td>
                                <td class="">
                                    <router-link :to="{ name: 'detailProduct', params: { id: product.id } }"
                                        class="btn btn-sm btn-outline-info mt-2">Xem</router-link>
                                    <router-link :to="{ name: 'editProduct', params: { id: product.id } }"
                                        class="btn btn-sm btn-outline-warning mt-2">Sửa</router-link>
                                    <button @click="deleteProduct(product.id)"
                                        class="btn btn-sm btn-outline-danger mt-2">Xóa</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <nav>
                        <ul class="pagination justify-content-center">
                            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                <button class="page-link" @click="fetchProducts(currentPage - 1)">Trước</button>
                            </li>

                            <li class="page-item" v-for="page in totalPages" :key="page"
                                :class="{ active: page === currentPage }">
                                <button class="page-link" @click="fetchProducts(page)">{{ page }}</button>
                            </li>

                            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                <button class="page-link" @click="fetchProducts(currentPage + 1)">Sau</button>
                            </li>
                        </ul>
                    </nav>

                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
    import { onMounted, ref } from 'vue';
    import { useRoute } from 'vue-router';
    import axios from 'axios'

    const route = useRoute()

    const products = ref([]);
    const categories = ref([]);
    const brands = ref([]);

    const fetchProducts = async () => {
        try {
            const { data } = await axios.get(`http://localhost:8000/api/products`)
            products.value = data
        } catch (error) {
            alert('Co loi xay ra: ' + error.message)
        }
    }

    const fetchCategory = async () => {
        try {
            const { data } = await axios.get(`http://localhost:8000/api/categories`)
            categories.value = data
        } catch (error) {
            alert('Co loi xay ra: ' + error.message)
        }
    }
    const fetchBrand = async () => {
        try {
            const { data } = await axios.get(`http://localhost:8000/api/brands`)
            brands.value = data
        } catch (error) {
            alert('Co loi xay ra: ' + error.message)
        }
    }

    const getCategoryName = (categoryId) => {
        const category = categories.value.find(c => c.id === categoryId);
        return category ? category.name : 'Không rõ danh mục';
    }

    const getBrandName = (brandId) => {
        const brand = brands.value.find(c => c.id === brandId);
        return brand ? brand.name : 'Không rõ brand';
    }

    const deleteProduct = async (id) => {
        try {
            const confirmDelete = confirm('Bạn có chắc muốn xóa sản phẩm này ?')
            if (!confirmDelete) return

            await axios.delete(`http://localhost:8000/api/products/${id}`)
            fetchProducts();
            alert('Xóa thành công!')
        } catch (error) {
            if (error.response) {
                console.log('Lỗi chi tiết:', error.response.data)
                alert('❌ Server báo lỗi: ' + JSON.stringify(error.response.data))
            } else {
                alert('❌ Không kết nối được tới server')
            }
        }
    }

    onMounted(() => {
        fetchProducts()
        fetchCategory()
        fetchBrand()
    })



</script>

<style scoped>

    .custom-hover-link {
        color: #198754;
    }

    .custom-hover-link:hover {
        color: white !important;
    }
</style>