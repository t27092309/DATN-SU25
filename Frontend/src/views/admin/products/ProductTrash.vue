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
                        <router-link :to="{ name: 'products' }">Quản lý Sản phẩm</router-link>
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
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="trashedProducts.length > 0">
                        <table class="table table-bordered">
                            <thead>
                                <tr class="text-center">
                                    <th scope="col">STT</th>
                                    <th scope="col">Tên</th>
                                    <th scope="col">Ảnh</th>
                                    <th scope="col">Ngày xóa</th>
                                    <th scope="col">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-center" v-for="(product, index) in trashedProducts" :key="product.id">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ product.name }}</td>
                                    <td>
                                        <img :src="product.image_url" :alt="product.name"
                                            style="width: 100px; height: auto; object-fit: cover;">
                                    </td>
                                    <td>{{ formatDate(product.deleted_at) }}</td>
                                    <td class="">
                                        <button @click="confirmRestoreProduct(product.id)"
                                            class="btn btn-sm btn-outline-success mt-2">Khôi phục</button>
                                        <button @click="confirmForceDeleteProduct(product.id)"
                                            class="btn btn-sm btn-outline-danger mt-2">Xóa vĩnh viễn</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    <div v-else class="text-center py-4">
                        <p>Thùng rác trống.</p>
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
const trashedProducts = ref([]);

const fetchTrashedProducts = async () => {
    try {
        // Gọi API để lấy các sản phẩm đã xóa mềm
        const response = await axios.get('http://localhost:8000/api/admin/products/trashed');
        trashedProducts.value = response.data.data; // Giả sử API trả về data trong trường 'data'
    } catch (error) {
        console.error('Lỗi khi tải sản phẩm đã xóa:', error);
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: 'Không thể tải danh sách sản phẩm trong thùng rác. Vui lòng thử lại sau.',
        });
    }
};

const formatDate = (datetimeString) => {
    if (!datetimeString) return 'N/A';
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(datetimeString).toLocaleDateString('vi-VN', options);
};

const confirmRestoreProduct = async (id) => {
    const result = await Swal.fire({
        title: 'Bạn có chắc muốn khôi phục sản phẩm này?',
        text: 'Sản phẩm sẽ được đưa trở lại danh sách sản phẩm chính.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, khôi phục!',
        cancelButtonText: 'Hủy'
    });

    if (result.isConfirmed) {
        try {
            await axios.post(`http://localhost:8000/api/admin/products/${id}/restore`);
            await fetchTrashedProducts(); // Tải lại danh sách thùng rác
            Swal.fire({
                title: 'Khôi phục thành công!',
                text: 'Sản phẩm đã được khôi phục.',
                icon: 'success'
            });
        } catch (error) {
            console.error('Lỗi khi khôi phục sản phẩm:', error);
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: `Không thể khôi phục sản phẩm: ${error.response?.data?.message || error.message}`,
            });
        }
    }
};

const confirmForceDeleteProduct = async (id) => {
    const result = await Swal.fire({
        title: 'Bạn có chắc muốn XÓA VĨNH VIỄN sản phẩm này?',
        text: 'Hành động này không thể hoàn tác! Tất cả dữ liệu và ảnh liên quan sẽ bị xóa.',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Có, xóa vĩnh viễn!',
        cancelButtonText: 'Hủy'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`http://localhost:8000/api/admin/products/${id}/force-delete`);
            await fetchTrashedProducts(); // Tải lại danh sách thùng rác
            Swal.fire({
                title: 'Xóa vĩnh viễn thành công!',
                text: 'Sản phẩm đã bị xóa hoàn toàn khỏi hệ thống.',
                icon: 'success'
            });
        } catch (error) {
            console.error('Lỗi khi xóa vĩnh viễn sản phẩm:', error);
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: `Không thể xóa vĩnh viễn sản phẩm: ${error.response?.data?.message || error.message}`,
            });
        }
    }
};

onMounted(() => {
    fetchTrashedProducts();
});
</script>

<style scoped>
/* CSS cho ProductTrash.vue */
img {
    border-radius: 5px;
}
</style>