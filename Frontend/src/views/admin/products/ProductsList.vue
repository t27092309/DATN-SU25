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
                        <div class="d-flex gap-2">
                            <router-link :to="{ name: 'trashedProducts' }"
                                class="btn btn-sm btn-outline-secondary custom-hover-secondary">
                                <i class="fas fa-trash"></i> Thùng rác
                            </router-link>

                            <router-link :to="{ name: 'addProduct' }"
                                class="btn btn-sm btn-outline-success custom-hover-link">
                                Thêm sản phẩm
                            </router-link>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="products.length > 0">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="text-center">
                                    <th scope="col">STT</th>
                                    <th scope="col">Tên</th>
                                    <th scope="col">Ảnh</th>
                                    <th scope="col">Giá</th>
                                    <th scope="col">Danh mục</th>
                                    <th scope="col">Giới tính</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col">Thương hiệu</th>
                                    <th scope="col">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center" v-for="(product, index) in products" :key="product.id">
                                    <td>{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
                                    <td>{{ product.name }}</td>
                                    <td>
                                        <img :src="product.image"
                                            style="width: 150px; height: auto; object-fit: cover;">
                                    </td>
                                    <td>{{ formatCurrency(product.price) }}</td>
                                    <td>{{ product.category_name }}</td>
                                    <td>{{ getGenderDisplay(product.gender) }}</td>
                                    <td>{{ product.slug }}</td>
                                    <td>{{ product.brand }}</td>
                                    <td class="">
                                        <router-link :to="{ name: 'detailProduct', params: { id: product.id } }"
                                            class="btn btn-sm btn-outline-info mt-2">Xem</router-link>
                                        <router-link :to="{ name: 'editProduct', params: { id: product.id } }"
                                            class="btn btn-sm btn-outline-warning mt-2">Sửa</router-link>
                                        <button @click="confirmDeleteProduct(product.id)"
                                            class="btn btn-sm btn-outline-danger mt-2">Xóa</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <nav>
                            <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ 'disabled': !pagination.prev_page_url }">
                                    <button class="page-link" @click="fetchProducts(pagination.current_page - 1)"
                                        :disabled="!pagination.prev_page_url">Trước</button>
                                </li>

                                <li class="page-item disabled">
                                    <span class="page-link">Trang {{ pagination.current_page }} / {{
                                        pagination.last_page }}</span>
                                </li>

                                <li class="page-item" :class="{ 'disabled': !pagination.next_page_url }">
                                    <button class="page-link" @click="fetchProducts(pagination.current_page + 1)"
                                        :disabled="!pagination.next_page_url">Sau</button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div v-else class="text-center py-4">
                        <p>Không có sản phẩm nào để hiển thị.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { onMounted, ref } from 'vue';
    import { useRoute } from 'vue-router';
    import axios from 'axios';
    import Swal from 'sweetalert2';

    const route = useRoute();

    const products = ref([]);
    const pagination = ref({});

    const fetchProducts = async (page = 1) => {
        try {
            const response = await axios.get(`http://localhost:8000/api/admin/products?page=${page}`);

            products.value = response.data.data;
            console.log(products.value);
    
            pagination.value = {
                current_page: response.data.meta.current_page,
                last_page: response.data.meta.last_page,
                from: response.data.meta.from,
                to: response.data.meta.to,
                per_page: response.data.meta.per_page,
                total: response.data.meta.total,
                prev_page_url: response.data.links.prev,
                next_page_url: response.data.links.next,
            };

            window.scrollTo(0, 0);
        } catch (error) {
            console.error('Lỗi khi tải sản phẩm:', error);
            products.value = [];
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Không thể tải danh sách sản phẩm. Vui lòng thử lại sau.',
            });
        }
    };

    const getGenderDisplay = (gender) => {
        switch (gender) {
            case 'male':
                return 'Nam';
            case 'female':
                return 'Nữ';
            case 'unisex':
                return 'Unisex';
            default:
                return 'Không xác định';
        }
    };

    const formatCurrency = (amount) => {
        if (amount === null || amount === undefined) {
            return '0 VNĐ';
        }
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND',
        }).format(amount);
    };

    const confirmDeleteProduct = async (id) => {
        try {
            const result = await Swal.fire({
                title: 'Bạn có chắc muốn xóa sản phẩm này?',
                text: 'Hành động này sẽ xóa sản phẩm, bạn có thể khôi phục nó sau này. Bạn vẫn muốn tiếp tục?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Có!',
                cancelButtonText: 'Hủy'
            });

            if (result.isConfirmed) {
                await axios.delete(`http://localhost:8000/api/admin/products/${id}`);
                fetchProducts(pagination.value.current_page);
                Swal.fire({
                    title: 'Xóa thành công!',
                    text: 'Sản phẩm đã được đánh dấu là đã xóa.',
                    icon: 'success',
                    confirmButtonText: 'Đã hiểu!'
                });
            }
        } catch (error) {
            console.error('Lỗi khi xóa sản phẩm:', error);
            const errorMessage = error.response?.data?.message || 'Không kết nối được tới server. Vui lòng kiểm tra mạng của bạn.';
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: `Xảy ra lỗi khi xóa sản phẩm: ${errorMessage}`,
            });
        }
    };

    onMounted(() => {
        fetchProducts();
    });
</script>

<style scoped>
    .custom-hover-link {
        color: #198754;
    }

    .custom-hover-link:hover {
        color: white !important;
    }

    .custom-hover-secondary {
        color: #6c757d;
    }

    .custom-hover-secondary:hover {
        color: white !important;
        background-color: #6c757d;
    }

    img {
        border-radius: 5px;
    }
</style>