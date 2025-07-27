<template>
    <div class="container mx-auto px-4 py-8">
        <div class="page-inner">
            <div class="mb-6">
                <h3 class="text-3xl font-bold mb-3">{{ route.meta.title }}</h3>
                <ul class="flex items-center space-x-2 text-gray-600 text-sm">
                    <li class="nav-home">
                        <router-link :to="{ name: 'AdminDashboard' }" class="hover:text-blue-600">
                            <i class="fas fa-home"></i>
                        </router-link>
                    </li>
                    <li class="separator">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </li>
                    <li class="nav-item">
                        <router-link :to="{ name: 'products' }" class="hover:text-blue-600">Quản lý Sản phẩm</router-link>
                    </li>
                    <li class="separator">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="text-blue-600">{{ route.meta.title }}</a>
                    </li>
                </ul>
            </div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">{{ route.meta.title }}</h1>
                </div>
                <div class="card-body">
                    <div v-if="trashedProducts.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr class="text-center">
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ảnh</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày xóa</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành động</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr class="text-center" v-for="(product, index) in trashedProducts" :key="product.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ product.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <img :src="product.image_url" :alt="product.name"
                                                class="w-24 h-auto object-cover rounded-md mx-auto">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(product.deleted_at) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex flex-col space-y-2 items-center justify-center">
                                            <button @click="confirmRestoreProduct(product.id)"
                                                class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">Khôi phục</button>
                                            <button @click="confirmForceDeleteProduct(product.id)"
                                                class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">Xóa vĩnh viễn</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-else class="text-center py-4 text-gray-500 italic">
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