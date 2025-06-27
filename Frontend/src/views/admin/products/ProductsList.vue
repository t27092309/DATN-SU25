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
                                        <img :src="product.image_url" :alt="product.name"
                                            style="width: 150px; height: auto; object-fit: cover;">
                                    </td>
                                    <td>{{ formatCurrency(product.price) }}</td>
                                    <td>{{ product.category_name }}</td>
                                    <td>{{ getGenderDisplay(product.gender) }}</td>
                                    <td>{{ product.slug }}</td>
                                    <td>{{ product.brand_name }}</td>
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
                                <li class="page-item" :class="{ 'disabled': !pagination.prev_page_url }">
                                    <button class="page-link" @click="fetchProducts(pagination.current_page - 1)">Trước</button>
                                </li>

                                <li class="page-item disabled">
                                    <span class="page-link">Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
                                </li>

                                <li class="page-item" :class="{ 'disabled': !pagination.next_page_url }">
                                    <button class="page-link" @click="fetchProducts(pagination.current_page + 1)">Sau</button>
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
    import Swal from 'sweetalert2'; // Đảm bảo SweetAlert2 được cài đặt

    const route = useRoute();

    const products = ref([]);
    const pagination = ref({});
    // Không cần categories và brands refs nữa vì chúng ta đã có tên trực tiếp từ ProductResource
    // const categories = ref([]);
    // const brands = ref([]);

    // Loại bỏ getImageUrl vì ProductResource trả về URL đầy đủ
    // const getImageUrl = (imagePath) => {
    //     return `http://localhost:8000/storage/${imagePath}`;
    // };

    const fetchProducts = async (page = 1) => {
        try {
            // Thay đổi URL API để phù hợp với endpoint của bạn
            const response = await axios.get(`http://localhost:8000/api/admin/products?page=${page}`);
            
            // Dữ liệu từ Laravel API Resource sẽ nằm trong 'data' của response.data
            products.value = response.data.data; 

            // Cập nhật thông tin phân trang từ response.data (ngoài phần 'data' của resource)
            // Laravel Pagination Resource trả về các meta data trực tiếp
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
            console.error('Không lấy được sản phẩm:', error);
            products.value = [];
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Không thể tải danh sách sản phẩm. Vui lòng thử lại sau.',
            });
        }
    };

    // Loại bỏ fetchCategory và fetchBrand vì chúng ta đã có tên trực tiếp từ ProductResource
    // const fetchCategory = async () => { ... }
    // const fetchBrand = async () => { ... }

    // Loại bỏ getCategoryName và getBrandName vì chúng ta đã có tên trực tiếp từ ProductResource
    // const getCategoryName = (categoryId) => { ... }
    // const getBrandName = (brandId) => { ... }

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

    const deleteProduct = async (id) => {
        try {
            const confirmDelete = await Swal.fire({
                title: 'Bạn có chắc muốn xóa?',
                text: 'Bạn sẽ không thể hoàn tác hành động này!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Vâng, xóa đi!',
                cancelButtonText: 'Hủy'
            });

            if (confirmDelete.isConfirmed) {
                await axios.delete(`http://localhost:8000/api/admin/products/${id}`);
                fetchProducts(pagination.value.current_page); // Tải lại trang hiện tại sau khi xóa
                Swal.fire({
                    title: 'Xóa thành công!',
                    text: 'Sản phẩm đã được xóa khỏi hệ thống.',
                    icon: 'success',
                    confirmButtonText: 'Tuyệt vời!'
                });
            }
        } catch (error) {
            console.error('Lỗi khi xóa sản phẩm:', error);
            if (error.response) {
                console.log('Lỗi chi tiết:', error.response.data);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: `Server báo lỗi: ${error.response.data.message || error.message}`,
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Không kết nối được tới server. Vui lòng kiểm tra mạng của bạn.',
                });
            }
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
    img {
        border-radius: 5px;
    }
</style>