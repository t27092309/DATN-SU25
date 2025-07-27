
<template>
  <div class="order-history-container bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
      <!-- Tiêu đề -->
      <h1 class="text-3xl font-semibold text-gray-800 mb-6 text-center">
        Tra cứu lịch sử mua hàng
      </h1>
      <p class="text-gray-600 text-center mb-8">
        Xem danh sách các đơn hàng bạn đã đặt.
      </p>

      <!-- Trạng thái đang tải -->
      <div v-if="loading" class="text-center">
        <p class="text-gray-600">Đang tải dữ liệu...</p>
      </div>

      <!-- Thông báo lỗi -->
      <div v-if="error" class="text-center text-red-500 mb-4">
        {{ error }}
      </div>

      <!-- Danh sách đơn hàng -->
      <div v-if="orders.length > 0" class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="grid grid-cols-1 gap-4 p-4">
          <div v-for="order in orders" :key="order.id"
            class="border-b last:border-b-0 p-4 hover:bg-gray-50 transition duration-200">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
              <div>
                <p class="text-sm font-semibold text-gray-800">
                  Mã đơn hàng: {{ order.order_id }}
                </p>
                <p class="text-sm text-gray-600">
                  Ngày đặt: {{ formatDate(order.created_at) }}
                </p>
                <p class="text-sm text-gray-600">
                  Tổng tiền: {{ formatPrice(order.total_amount) }} VNĐ
                </p>
                <p class="text-sm text-gray-600">
                  Trạng thái: {{ order.status }}
                </p>
              </div>
              <div class="mt-4 md:mt-0 flex space-x-4">
                <router-link :to="`/order-details/${order.id}`"
                  class="text-sm text-blue-600 hover:underline">
                  Xem chi tiết
                </router-link>
                <button v-if="order.status === 'pending'"
                  @click="cancelOrder(order.id)"
                  class="text-sm text-red-600 hover:underline">
                  Hủy đơn
                </button>
                <button v-if="order.status === 'shipped'"
                  @click="markAsDelivered(order.id)"
                  class="text-sm text-green-600 hover:underline">
                  Đã nhận hàng
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Không có đơn hàng -->
      <div v-if="!loading && orders.length === 0" class="text-center">
        <p class="text-gray-600">Bạn chưa có đơn hàng nào.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const orders = ref([]);
const loading = ref(false);
const error = ref(null);

// Lấy danh sách đơn hàng khi component được mount
const fetchOrders = async () => {
  loading.value = true;
  error.value = null;
  orders.value = [];

  try {
    const response = await axios.get('http://localhost:8000/api/orders', {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    });

    if (response.data && Array.isArray(response.data.data)) {
      orders.value = response.data.data;
    } else {
      error.value = 'Dữ liệu từ API không hợp lệ';
    }
  } catch (err) {
    error.value = 'Không thể tải lịch sử đơn hàng: ' + (err.message || 'Lỗi không xác định');
  } finally {
    loading.value = false;
  }
};

// Hủy đơn hàng
const cancelOrder = async (orderId) => {
  if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) return;

  try {
    await axios.post(`http://localhost:8000/api/orders/${orderId}/cancel`, null, {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    });
    fetchOrders(); // Làm mới danh sách đơn hàng
  } catch (err) {
    error.value = 'Không thể hủy đơn hàng: ' + (err.message || 'Lỗi không xác định');
  }
};

// Đánh dấu đơn hàng đã nhận
const markAsDelivered = async (orderId) => {
  if (!confirm('Bạn có chắc đã nhận được đơn hàng này?')) return;

  try {
    await axios.post(`http://localhost:8000/api/orders/${orderId}/mark-delivered`, null, {
      headers: {
        Authorization: `Bearer ${authStore.token}`
      }
    });
    fetchOrders(); // Làm mới danh sách đơn hàng
  } catch (err) {
    error.value = 'Không thể cập nhật trạng thái đơn hàng: ' + (err.message || 'Lỗi không xác định');
  }
};

// Định dạng ngày
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

// Định dạng giá tiền
const formatPrice = (amount) => {
  return new Intl.NumberFormat('vi-VN').format(amount);
};

// Khởi tạo khi component được mount
onMounted(() => {
  authStore.initializeAuth();
  if (authStore.isAuthenticated) {
    fetchOrders();
  } else {
    error.value = 'Vui lòng đăng nhập để xem lịch sử đơn hàng.';
  }
});
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>