<template>
  <div class="user-orders p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold mb-8 text-gray-800 text-center">Đơn Mua Của Tôi</h2>

    <div class="flex border-b border-gray-200 mb-6 bg-white rounded-t-lg shadow-sm overflow-x-auto whitespace-nowrap">
      <button v-for="tab in orderTabs" :key="tab.value" @click="activeTab = tab.value"
        :class="['flex-shrink-0 px-3 sm:px-6 py-3 text-base font-medium border-b-2 transition-colors duration-200 flex items-center justify-center',
          activeTab === tab.value ? 'border-red-600 text-red-600' : 'border-transparent text-gray-700 hover:text-red-600 hover:border-red-100']">
        <span>{{ tab.label }}</span>
        <span v-if="tab.count !== undefined && tab.count > 0"
          class="ml-2 text-xs px-2 py-1 rounded-full bg-red-500 text-white font-bold">{{ tab.count }}</span>
      </button>
    </div>

    <div class="relative mb-6">
      <input type="text" placeholder="Bạn có thể tìm kiếm Tên sản phẩm hoặc ID đơn hàng" v-model="searchQuery"
        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 shadow-sm" />
      <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
    </div>

    <div v-if="isLoading" class="text-center py-10 bg-white rounded-lg shadow-sm">
      <p class="text-gray-600 flex items-center justify-center">
        <i class="fas fa-spinner fa-spin mr-2"></i> Đang tải đơn hàng...
      </p>
    </div>

    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
      role="alert">
      <strong class="font-bold">Lỗi!</strong>
      <span class="block sm:inline"> {{ error }}</span>
    </div>

    <div v-else-if="orders.length === 0" class="text-center py-10 text-gray-500 text-lg bg-white rounded-lg shadow-sm">
      Không có đơn hàng nào trong mục này.
    </div>

    <div v-else class="order-list space-y-6">
      <div v-for="order in orders" :key="order.id"
        class="border border-gray-200 rounded-lg p-5 bg-white shadow-md hover:shadow-lg transition-shadow duration-200">

        <div class="flex justify-between items-center mb-4 text-sm border-b pb-3">
          <router-link :to="{ name: 'OrderDetail', params: { idDonHang: order.id } }"
            class="text-blue-600 hover:underline font-semibold text-base">
            Mã Đơn hàng: #{{ order.id }}
          </router-link>
          <p :class="['font-bold text-base', getStatusClass(order.status)]">{{ getStatusText(order.status) }}</p>
        </div>

        <div v-for="item in order.items" :key="item.id"
          class="flex items-center border-t border-b border-gray-100 py-4 mb-4">
          <img :src="item.product_image" :alt="item.product_name"
            class="w-24 h-24 object-cover border border-gray-200 rounded-md mr-4 flex-shrink-0" />
          <div class="flex-1">
            <p class="font-semibold text-gray-800 mb-1 text-base">{{ item.product_name }}</p>
            <p class="text-sm text-gray-600">Phân loại hàng: {{ item.variant_name }}</p>
            <p class="text-sm text-gray-600">x{{ item.quantity }}</p>
          </div>
          <div class="text-right flex flex-col items-end min-w-[120px]">
            <span class="text-red-600 font-bold text-lg">
              {{ formatCurrency(item.price_each) }}
            </span>
          </div>
        </div>

        <div class="text-right mb-4">
          <span class="text-gray-700 mr-2 text-base">Thành tiền:</span>
          <span class="text-red-600 text-2xl font-bold">
            {{ formatCurrency(order.total_price) }}
          </span>
        </div>

        <div
          class="flex flex-col sm:flex-row justify-end items-end sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
          <p class="text-xs text-gray-500 text-right sm:text-left flex-1 leading-relaxed">
            Ghi chú: {{ order.notes || 'Không có ghi chú.' }}
          </p>
          <template v-if="order.status === 'shipped'">
            <button @click="markAsDelivered(order.id)"
              class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors duration-200 shadow-sm">
              Đã Nhận Hàng
            </button>
            <button @click="contactSeller(order.id)"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Liên Hệ Người Bán
            </button>
          </template>
          <template v-else-if="order.status === 'completed'">
            <button
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Mua Lại
            </button>
            <router-link :to="{ name: 'OrderDetail', params: { idDonHang: order.id } }"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm text-center">
              Xem Chi Tiết
            </router-link>
          </template>
          <template v-else-if="order.status === 'cancelled'">
            <button
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Đặt Lại
            </button>
            <router-link :to="{ name: 'OrderDetail', params: { idDonHang: order.id } }"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm text-center">
              Xem Chi Tiết
            </router-link>
          </template>
          <template v-else-if="order.status === 'pending'">
            <button
              class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors duration-200 shadow-sm">
              Thanh Toán Ngay
            </button>
            <button @click="cancelOrder(order.id)"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Hủy Đơn Hàng
            </button>
          </template>
          <template v-else-if="order.status === 'processing'">
            <router-link :to="{ name: 'OrderDetail', params: { idDonHang: order.id } }"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm text-center">
              Xem Chi Tiết
            </router-link>
          </template>
          <template v-else>
            <router-link :to="{ name: 'OrderDetail', params: { idDonHang: order.id } }"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm text-center">
              Xem Chi Tiết
            </router-link>
          </template>
        </div>
      </div>
      <div v-if="pagination.last_page > 1" class="flex justify-center mt-8 space-x-2">
        <button @click="fetchOrders(activeTab, pagination.current_page - 1, searchQuery)"
          :disabled="pagination.current_page === 1"
          class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
          Trước
        </button>
        <button v-for="page in pagination.last_page" :key="page" @click="fetchOrders(activeTab, page, searchQuery)"
          :class="{ 'bg-red-500 text-white': page === pagination.current_page, 'bg-gray-200 text-gray-700': page !== pagination.current_page }"
          class="px-4 py-2 rounded-md hover:bg-red-400 hover:text-white transition-colors duration-200">
          {{ page }}
        </button>
        <button @click="fetchOrders(activeTab, pagination.current_page + 1, searchQuery)"
          :disabled="pagination.current_page === pagination.last_page"
          class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed">
          Sau
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const api = axios;
const router = useRouter();

const orders = ref([]);
const isLoading = ref(true);
const error = ref(null);
const activeTab = ref('all'); // Mặc định hiển thị tất cả
const pagination = ref({}); // Dữ liệu phân trang từ API
const searchQuery = ref(''); // Biến cho input tìm kiếm

// Định nghĩa lại các tab để phù hợp với trạng thái backend
const orderTabs = ref([
  { label: 'Tất cả', value: 'all', count: 0 },
  { label: 'Chờ xác nhận', value: 'pending', count: 0 },
  { label: 'Đang xử lý', value: 'processing', count: 0 },
  { label: 'Đang giao hàng', value: 'shipped', count: 0 },
  { label: 'Đã giao hàng', value: 'completed', count: 0 },
  { label: 'Đã hủy', value: 'cancelled', count: 0 },
]);

const showSuccess = (message) => {
  Swal.fire({
    icon: 'success',
    title: 'Thành công!',
    text: message,
    showConfirmButton: false,
    timer: 1500
  });
};

const showError = (message) => {
  Swal.fire({
    icon: 'error',
    title: 'Lỗi!',
    text: message,
    confirmButtonText: 'Đóng'
  });
};

const fetchOrders = async (status = 'all', page = 1, search = '') => {
  isLoading.value = true;
  error.value = null;
  orders.value = []; // Clear current orders before fetching
  try {
    let url = `orders?page=${page}`;
    if (status !== 'all') {
      url += `&status=${status}`;
    }
    if (search) {
      url += `&search=${search}`; // Thêm query tìm kiếm
    }

    const response = await api.get(url);
    orders.value = response.data.orders;
    pagination.value = response.data.pagination;
  } catch (err) {
    console.error('Lỗi khi tải đơn hàng:', err);
    if (err.response && err.response.status === 401) {
      error.value = 'Bạn chưa đăng nhập hoặc phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.';
      router.push({ name: 'login' });
    } else {
      error.value = 'Không thể tải danh sách đơn hàng. Vui lòng thử lại sau.';
    }
  } finally {
    isLoading.value = false;
  }
};

// MỚI: Hàm để lấy số lượng đơn hàng cho từng trạng thái
const fetchOrderCounts = async () => {
  try {
    const response = await api.get('orders/counts'); // Gọi API mới
    const counts = response.data.counts;
    orderTabs.value.forEach(tab => {
      if (counts[tab.value] !== undefined) {
        tab.count = counts[tab.value];
      }
    });
  } catch (err) {
    console.error('Lỗi khi tải số lượng đơn hàng:', err);
  }
};



// Watch activeTab changes to refetch orders
watch(activeTab, (newTab) => {
  fetchOrders(newTab, 1, searchQuery.value); // Reset về trang 1 khi đổi tab
});

// Watch searchQuery changes to refetch orders (debounce for better performance)
let searchTimeout = null;
watch(searchQuery, (newSearch) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchOrders(activeTab.value, 1, newSearch); // Reset về trang 1 khi tìm kiếm
  }, 500); // Debounce 500ms
});


onMounted(() => {
  fetchOrders(activeTab.value);
  fetchOrderCounts(); // Gọi khi component được mount
});

// --- Helper Functions ---
const formatCurrency = (value) => {
  if (value === null || value === undefined || isNaN(value)) return '0₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// Ánh xạ trạng thái từ backend sang hiển thị và màu sắc
const getStatusText = (status) => {
  switch (status) {
    case 'pending': return 'Chờ xác nhận';
    case 'processing': return 'Đang xử lý';
    case 'shipped': return 'Đang giao hàng';
    case 'completed': return 'Đã giao hàng';
    case 'cancelled': return 'Đã hủy';
    default: return 'Không rõ';
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'text-yellow-600'; // Chờ xác nhận
    case 'processing': return 'text-blue-600'; // Đang xử lý
    case 'shipped': return 'text-purple-600'; // Đang giao hàng
    case 'completed': return 'text-green-600'; // Đã giao hàng
    case 'cancelled': return 'text-gray-500'; // Đã hủy
    default: return 'text-gray-800';
  }
};

// --- Action Handlers ---
const markAsDelivered = async (orderId) => { // Đổi tên biến từ idDonHang thành orderId cho rõ ràng
  Swal.fire({
    title: 'Xác nhận đã nhận hàng?',
    text: 'Bạn có chắc chắn muốn xác nhận đã nhận hàng cho đơn này không?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Có, đã nhận!',
    cancelButtonText: 'Hủy'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const response = await api.post(`orders/${orderId}/mark-delivered`); // Đảm bảo URL chính xác
        showSuccess(response.data.message); // Sử dụng SweetAlert2
        // Tải lại danh sách đơn hàng và cập nhật số lượng
        fetchOrders(activeTab.value, pagination.value.current_page, searchQuery.value);
        fetchOrderCounts();
      } catch (err) {
        console.error('Lỗi khi đánh dấu đã nhận hàng:', err);
        showError(err.response?.data?.message || 'Không thể đánh dấu đã nhận hàng. Vui lòng thử lại.'); // Lấy lỗi từ API
      }
    }
  });
};

const cancelOrder = async (orderId) => { // Đổi tên biến từ idDonHang thành orderId cho rõ ràng
  Swal.fire({
    title: 'Hủy đơn hàng?',
    text: 'Bạn có chắc chắn muốn hủy đơn hàng này không? Hành động này không thể hoàn tác.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Có, hủy!',
    cancelButtonText: 'Không'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const response = await api.post(`orders/${orderId}/cancel`); // Đảm bảo URL chính xác
        showSuccess(response.data.message); // Sử dụng SweetAlert2
        // Tải lại danh sách đơn hàng và cập nhật số lượng
        fetchOrders(activeTab.value, pagination.value.current_page, searchQuery.value);
        fetchOrderCounts();
      } catch (err) {
        console.error('Lỗi khi hủy đơn hàng:', err);
        showError(err.response?.data?.message || 'Không thể hủy đơn hàng. Vui lòng thử lại.'); // Lấy lỗi từ API
      }
    }
  });
};

const contactSeller = (idDonHang) => {
  alert(`Đang liên hệ với người bán cho đơn hàng ${idDonHang}. (Logic chuyển hướng/chat sẽ được gọi ở đây)`);
  // Logic để mở cửa sổ chat hoặc chuyển đến trang chat
};

// Bạn có thể thêm các hàm cho "Mua Lại", "Đặt Lại", "Xem Lý Do Hủy", "Thanh Toán Ngay" tương tự
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>