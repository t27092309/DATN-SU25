<template>
    <div class="container mx-auto px-4 py-8">
        <div class="page-inner">
            <div class="mb-6">
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
                        <span class="font-semibold">{{ route.meta.title }}</span>
                    </li>
                </ul>
            </div>
            <div class="bg-white shadow-md rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">{{ route.meta.title }}</h1>
                    <div class="flex space-x-2">
                        <router-link :to="{ name: 'trashedProducts' }"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
                            <i class="fas fa-trash mr-2"></i> Thùng rác
                        </router-link>

                        <router-link :to="{ name: 'addProduct' }"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150">
                            Thêm sản phẩm
                        </router-link>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="products.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            STT</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tên</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ảnh</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Giá</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Danh mục</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Giới tính</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Thương hiệu</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Hành động</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(product, index) in products" :key="product.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                            {{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ product.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <img :src="product.image" :alt="product.name"
                                                class="w-24 h-auto object-cover rounded-md mx-auto">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                                            {{ formatCurrency(product.price) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ product.category_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ getGenderDisplay(product.gender) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ product.brand }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <router-link :to="{ name: 'detailProduct', params: { id: product.id } }"
                                                class="text-blue-600 hover:text-blue-900 mr-2">Xem</router-link>
                                            <router-link :to="{ name: 'editProduct', params: { id: product.id } }"
                                                class="text-yellow-600 hover:text-yellow-900 mr-2">Sửa</router-link>
                                            <button @click="confirmDeleteProduct(product.id)"
                                                class="text-red-600 hover:text-red-900">Xóa</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <nav class="mt-4">
                            <ul class="flex justify-center items-center space-x-2">
                                <li :class="{ 'opacity-50 cursor-not-allowed': !pagination.prev_page_url }">
                                    <button @click="fetchProducts(pagination.current_page - 1)"
                                        :disabled="!pagination.prev_page_url"
                                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                        Trước
                                    </button>
                                </li>

                                <li
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-gray-100">
                                    Trang {{ pagination.current_page }} / {{ pagination.last_page }}
                                </li>

                                <li :class="{ 'opacity-50 cursor-not-allowed': !pagination.next_page_url }">
                                    <button @click="fetchProducts(pagination.current_page + 1)"
                                        :disabled="!pagination.next_page_url"
                                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                        Sau
                                    </button>
                                </li>
                            </ul>
                        </nav>
                    </div>
                    <div v-else class="text-center py-8 text-gray-600">
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
img {
    border-radius: 0.375rem;
    /* rounded-md in Tailwind */
}
</style>