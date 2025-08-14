<template>
  <div class="user-orders p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold mb-8 text-gray-800 text-center">Đơn Mua Của Tôi</h2>

    <div class="flex border-b border-gray-200 mb-6 bg-white rounded-t-lg shadow-sm overflow-x-auto whitespace-nowrap">
      <button v-for="tab in orderTabs" :key="tab.value" @click="activeTab = tab.value"
        :class="['flex-shrink-0 px-3 sm:px-6 py-3 text-base font-medium border-b-2 transition-colors duration-200 flex items-center justify-center',
          activeTab === tab.value ? 'border-red-600 text-red-600' : 'border-transparent text-gray-700 hover:text-red-600 hover:border-red-100']">
        <span>{{ tab.label }}</span>
        <span
          v-if="tab.count !== undefined && tab.count > 0 && !['all', 'delivered', 'cancelled', 'returns'].includes(tab.value)"
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

    <div v-else-if="!orders || orders.length === 0"
      class="text-center py-10 text-gray-500 text-lg bg-white rounded-lg shadow-sm">
      Không có đơn hàng nào trong mục này.
    </div>

    <div v-else class="order-list space-y-6">
      <div v-for="order in orders" :key="order.id"
        class="border border-gray-200 rounded-lg p-5 bg-white shadow-md hover:shadow-lg transition-shadow duration-200">

        <div class="flex justify-between items-center mb-4 text-sm border-b pb-3">
          <div>
            <router-link :to="{ name: 'OrderDetail', params: { idDonHang: order.id } }"
              class="text-blue-600 hover:underline font-semibold text-base">
              Mã Đơn hàng: #{{ order.id }}
            </router-link>
            <p class="text-xs text-gray-500 mt-1">
              Ngày đặt: {{ formatDate(order.created_at) }}
            </p>
          </div>
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
          <div class="flex flex-col ml-4">
            <button v-if="order.status === 'delivered' && !item.has_review" @click="reviewProduct(item)"
              class="px-4 py-2 text-sm bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-200 shadow-sm mb-2">
              Đánh Giá
            </button>
            <button v-else-if="order.status === 'delivered' && item.has_review" @click="viewReview(item)"
              class="px-4 py-2 text-sm bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 shadow-sm">
              Xem Đánh Giá
            </button>
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
            <router-link :to="{ name: 'OrderDetail', params: { idDonHang: order.id } }"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm text-center">
              Xem Chi Tiết
            </router-link>
          </template>
          <template v-else-if="order.status === 'delivered'">
            <button @click="reorder(order.id)"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Mua Lại
            </button>
            <router-link :to="{ name: 'OrderDetail', params: { idDonHang: order.id } }"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm text-center">
              Xem Chi Tiết
            </router-link>
          </template>
          <template v-else-if="order.status === 'cancelled'">
            <button @click="reorder(order.id)"
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
            <router-link :to="{ name: 'OrderDetail', params: { idDonHang: order.id } }"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm text-center">
              Xem Chi Tiết
            </router-link>
          </template>
          <template v-else-if="order.status === 'processing'">
            <button @click="cancelOrder(order.id)"
              class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors duration-200 shadow-sm">
              Hủy Đơn Hàng
            </button>
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

    <ReviewPopup :visible="showReviewPopup" :order-item-id="currentOrderItemId" :product-name="currentProductName"
      :product-image="currentProductImage" @close="showReviewPopup = false" @submitted="handleReviewSubmitted" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import ReviewPopup from '@/components/ReviewPopup.vue';

const api = axios;
const router = useRouter();

const showReviewPopup = ref(false);
const currentOrderItemId = ref(null);
const currentProductName = ref('');
const currentProductImage = ref('');

const orders = ref([]);
const isLoading = ref(true);
const error = ref(null);
const activeTab = ref('all');
const pagination = ref({});
const searchQuery = ref('');

const orderTabs = ref([
  { label: 'Tất cả', value: 'all', count: 0 },
  { label: 'Chờ xác nhận', value: 'pending', count: 0 },
  { label: 'Đang xử lý', value: 'processing', count: 0 },
  { label: 'Đang giao hàng', value: 'shipped', count: 0 },
  { label: 'Đã giao hàng', value: 'delivered', count: 0 },
  { label: 'Trả hàng', value: 'return_requested', count: 0 },
  { label: 'Hoàn tiền', value: 'refunded', count: 0 },
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

const reviewProduct = (item) => {
  currentOrderItemId.value = item.id;
  currentProductName.value = item.product_name;
  currentProductImage.value = item.product_image;
  showReviewPopup.value = true;
};

const handleReviewSubmitted = () => {
  showReviewPopup.value = false;

  // 1. Tìm và cập nhật OrderItem trong danh sách orders
  const order = orders.value.find(o => o.items.some(item => item.id === currentOrderItemId.value));
  if (order) {
    const item = order.items.find(i => i.id === currentOrderItemId.value);
    if (item) {
      // Cập nhật trạng thái has_review của sản phẩm đó
      item.has_review = true;
    }
  }

  // 2. (Tùy chọn) Gọi lại API nếu cần thiết để đảm bảo dữ liệu luôn mới nhất
  // Bỏ dòng này nếu bạn muốn tối ưu hiệu suất và chỉ cập nhật frontend
  // fetchOrders(activeTab.value, pagination.value.current_page, searchQuery.value);

  showSuccess('Đánh giá của bạn đã được gửi thành công!');
};
const viewReview = async (item) => {
  // Lấy ID của đánh giá từ order item
  // Giả định bạn có một route để xem đánh giá theo order_item_id
  router.push({ name: 'ProductDetail', params: { slug: item.slug } });

  // Hoặc bạn có thể dùng popup để hiển thị đánh giá
  // try {
  //   const response = await api.get(`/reviews/order-item/${item.id}`);
  //   const review = response.data;
  //   Swal.fire({
  //     title: 'Đánh giá của bạn',
  //     html: `
  //       <p>Rating: ${review.rating} sao</p>
  //       <p>Bình luận: ${review.comment}</p>
  //     `,
  //     icon: 'info'
  //   });
  // } catch (error) {
  //   showError('Không thể tải đánh giá. Vui lòng thử lại.');
  // }
};

const fetchOrders = async (status = 'all', page = 1, search = '') => {
  isLoading.value = true;
  error.value = null;
  orders.value = [];
  pagination.value = {};

  try {
    let url = `orders?page=${page}`;

    if (status !== 'all') {
      if (status === 'returns') {
        url += `&status=return_requested,refunded`;
      } else {
        url += `&status=${status}`;
      }
    }

    if (search) {
      url += `&search=${search}`;
    }

    const response = await api.get(url);

    // Lô gic sửa lỗi: kiểm tra dữ liệu trước khi gán
    if (response.data) {
      // Giả sử API trả về định dạng { orders: [...], pagination: {...} }
      orders.value = Array.isArray(response.data.orders) ? response.data.orders : [];
      pagination.value = response.data.pagination || {};
    } else {
      orders.value = [];
      pagination.value = {};
    }

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

const fetchOrderCounts = async () => {
  try {
    const response = await api.get('orders/counts');
    const counts = response.data.counts;
    orderTabs.value.forEach(tab => {
      if (tab.value === 'returns') {
        tab.count = (counts['return_requested'] || 0) + (counts['refunded'] || 0);
      } else if (counts[tab.value] !== undefined) {
        tab.count = counts[tab.value];
      }
    });
  } catch (err) {
    console.error('Lỗi khi tải số lượng đơn hàng:', err);
  }
};

watch(activeTab, (newTab) => {
  fetchOrders(newTab, 1, searchQuery.value);
});

let searchTimeout = null;
watch(searchQuery, (newSearch) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchOrders(activeTab.value, 1, newSearch);
  }, 500);
});

onMounted(() => {
  fetchOrders(activeTab.value);
  fetchOrderCounts();
});

// Helper Functions
const formatCurrency = (value) => {
  if (value === null || value === undefined || isNaN(value)) return '0₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (datetimeString) => {
  if (!datetimeString) return 'N/A';
  const options = { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric' };
  return new Date(datetimeString).toLocaleDateString('vi-VN', options);
};

const getStatusText = (status) => {
  switch (status) {
    case 'pending': return 'Chờ xác nhận';
    case 'processing': return 'Đang xử lý';
    case 'shipped': return 'Đang giao hàng';
    case 'delivered': return 'Đã giao hàng';
    case 'cancelled': return 'Đã hủy';
    case 'return_requested': return 'Yêu cầu trả hàng';
    case 'refunded': return 'Đã hoàn tiền';
    default: return 'Không rõ';
  }
};

const getStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'text-yellow-600';
    case 'processing': return 'text-blue-600';
    case 'shipped': return 'text-purple-600';
    case 'delivered': return 'text-green-600';
    case 'cancelled': return 'text-gray-500';
    case 'return_requested': return 'text-orange-500';
    case 'refunded': return 'text-red-500';
    default: return 'text-gray-800';
  }
};

// Action Handlers
const markAsDelivered = async (orderId) => {
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
        const response = await api.post(`orders/${orderId}/mark-delivered`);
        showSuccess(response.data.message);
        fetchOrders(activeTab.value, pagination.value.current_page, searchQuery.value);
        fetchOrderCounts();
      } catch (err) {
        console.error('Lỗi khi đánh dấu đã nhận hàng:', err);
        showError(err.response?.data?.message || 'Không thể đánh dấu đã nhận hàng. Vui lòng thử lại.');
      }
    }
  });
};

const cancelOrder = async (orderId) => {
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
        const response = await api.post(`orders/${orderId}/cancel`);
        showSuccess(response.data.message);
        fetchOrders(activeTab.value, pagination.value.current_page, searchQuery.value);
        fetchOrderCounts();
      } catch (err) {
        console.error('Lỗi khi hủy đơn hàng:', err);
        showError(err.response?.data?.message || 'Không thể hủy đơn hàng. Vui lòng thử lại.');
      }
    }
  });
};

const reorder = async (orderId) => {
  Swal.fire({
    title: 'Mua lại đơn hàng?',
    text: 'Các sản phẩm trong đơn hàng này sẽ được thêm vào giỏ hàng của bạn.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Đồng ý',
    cancelButtonText: 'Hủy'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        const response = await api.post(`orders/${orderId}/reorder`);
        showSuccess(response.data.message);
        router.push({ name: 'GioHang' });
      } catch (err) {
        console.error('Lỗi khi mua lại đơn hàng:', err);
        showError(err.response?.data?.message || 'Không thể mua lại đơn hàng. Vui lòng thử lại.');
      }
    }
  });
};
</script>

<style scoped></style>