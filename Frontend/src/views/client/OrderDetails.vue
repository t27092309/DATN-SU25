```vue
<template>
  <div class="order-details-container bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
      <h1 class="text-3xl font-semibold text-gray-800 mb-6 text-center">
        Chi tiết đơn hàng #{{ order?.order_code || '...' }}
      </h1>
      <div v-if="loading" class="text-center">
        <p class="text-gray-600">Đang tải dữ liệu...</p>
      </div>
      <div v-if="error" class="text-center text-red-500 mb-4">
        {{ error }}
      </div>
      <div v-if="order" class="bg-white shadow-md rounded-lg p-6">
        <p class="text-sm text-gray-600">Mã đơn hàng: {{ order.order_code }}</p>
        <p class="text-sm text-gray-600">Người nhận: {{ order.customer_name }}</p>
        <p class="text-sm text-gray-600">Số điện thoại: {{ order.customer_phone }}</p>
        <p class="text-sm text-gray-600">Tổng tiền: {{ formatPrice(order.total_amount) }} VNĐ</p>
        <p class="text-sm text-gray-600">Trạng thái đơn hàng: {{ order.order_status }}</p>
        <p class="text-sm text-gray-600">Trạng thái thanh toán: {{ order.payment_status }}</p>
        <p class="text-sm text-gray-600">Ngày đặt: {{ formatDate(order.created_at) }}</p>
        <div v-if="order.items && order.items.length > 0" class="mt-4">
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Sản phẩm</h3>
          <ul class="space-y-2">
            <li v-for="item in order.items" :key="item.product_name" class="text-sm text-gray-600">
              {{ item.product_name }} ({{ item.variant?.name || 'Không có biến thể' }}) - 
              Số lượng: {{ item.quantity }} - 
              Giá: {{ formatPrice(item.price) }} VNĐ
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const order = ref(null);
const loading = ref(false);
const error = ref(null);

const fetchOrderDetails = async () => {
  loading.value = true;
  error.value = null;

  try {
    // Lấy dữ liệu từ localStorage
    const storedOrder = JSON.parse(localStorage.getItem('selected_order') || '{}');
    if (storedOrder.id === parseInt(route.params.orderId)) {
      order.value = storedOrder;
    } else {
      error.value = 'Không tìm thấy đơn hàng hoặc dữ liệu không hợp lệ. Vui lòng tra cứu lại.';
    }
  } catch (err) {
    error.value = 'Không thể tải chi tiết đơn hàng: ' + err.message;
  } finally {
    loading.value = false;
  }
};

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatPrice = (amount) => {
  return new Intl.NumberFormat('vi-VN').format(amount);
};

onMounted(() => {
  fetchOrderDetails();
});
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>
```