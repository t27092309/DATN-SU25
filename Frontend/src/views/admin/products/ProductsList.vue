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
                                    <img :src="getImageUrl(product.image)" alt="" style="width: 150px;">
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
                            <li class="page-item">
                                <button class="page-link" :disabled="!pagination.prev_page_url"
                                    @click="fetchProducts(pagination.current_page - 1)">Trước</button>
                            </li>

                            <span>Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>

                            <li class="page-item">
                                <button class="page-link" :disabled="!pagination.next_page_url"
                                    @click="fetchProducts(pagination.current_page + 1)">Sau</button>
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
    import Swal from 'sweetalert2';

    const route = useRoute()

    const products = ref([]);
    const pagination = ref({});
    const categories = ref([]);
    const brands = ref([]);

    const getImageUrl = (imagePath) => {
        return `http://localhost:8000/storage/${imagePath}`;
    };

    const fetchProducts = async (page = 1) => {
        try {
            const { data } = await axios.get(`http://localhost:8000/api/admin/products?page=${page}`);
            console.log(data);

            products.value = data.data; // data là mảng sản phẩm
            pagination.value = data;

            window.scrollTo(0, 0);
        } catch (error) {
            console.error('Không lấy được sản phẩm:', error);
            products.value = [];
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
            brands.value = data.data || [];
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
        if (!Array.isArray(brands.value)) return 'Đang load...';
        const brand = brands.value.find(b => b.id === brandId);
        return brand ? brand.name : 'Đang load...';
    };


    const deleteProduct = async (id) => {
        try {
            const confirmDelete = await Swal.fire({
                title: 'Bạn có chắc muốn xóa ?',
                text: 'Bạn sẽ không thể hoàn tác hành động này!',
                icon: 'warning', // Dùng icon 'warning' cho hành động xóa sẽ hợp lý hơn
                showCancelButton: true, // Hiển thị nút "Hủy"
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Vâng, xóa đi!',
                cancelButtonText: 'Hủy' // Thêm text cho nút hủy
            });
            if (confirmDelete.isConfirmed) {
                await axios.delete(`http://localhost:8000/api/admin/products/${id}`)
                fetchProducts();
                Swal.fire({
                    title: 'Xóa thành công!',
                    text: 'Chúc mừng, bạn đã xóa thành công!',
                    icon: 'success', // 'success', 'error', 'warning', 'info', 'question'
                    confirmButtonText: 'Tuyệt vời!'
                });
            }
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