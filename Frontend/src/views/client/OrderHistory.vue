```vue
<template>
  <div class="order-history-container bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto px-4">
      <!-- Tiêu đề -->
      <h1 class="text-3xl font-semibold text-gray-800 mb-6 text-center">
        Tra cứu lịch sử mua hàng
      </h1>
      <p class="text-gray-600 text-center mb-8">
        Vui lòng nhập số điện thoại để tra cứu lịch sử đơn hàng của bạn.
      </p>

      <!-- Form tìm kiếm -->
      <form @submit.prevent="handleOrderSearch" class="max-w-lg mx-auto mb-8">
        <div class="relative">
          <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-500" aria-hidden="true" fill="none" viewBox="0 0 20 20">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
            </svg>
          </div>
          <input v-model="phone" type="tel" id="phone-search"
            class="block w-full h-10 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-blue-500 focus:border-blue-500"
            placeholder="Nhập số điện thoại (VD: 0901234567)" required pattern="[0-9]{10}" />
          <button type="submit"
            class="absolute end-1 bottom-1.5 h-7 px-4 text-sm text-black bg-gray-200 hover:bg-gray-300 rounded-md flex items-center justify-center">
            Tra cứu
          </button>
        </div>
      </form>

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
                  Mã đơn hàng: {{ order.order_code }}
                </p>
                <p class="text-sm text-gray-600">
                  Người nhận: {{ order.customer_name }}
                </p>
                <p class="text-sm text-gray-600">
                  Ngày đặt: {{ formatDate(order.created_at) }}
                </p>
                <p class="text-sm text-gray-600">
                  Tổng tiền: {{ formatPrice(order.total_amount) }} VNĐ
                </p>
                <p class="text-sm text-gray-600">
                  Trạng thái đơn hàng: {{ order.order_status }}
                </p>
                <p class="text-sm text-gray-600">
                  Trạng thái thanh toán: {{ order.payment_status }}
                </p>
              </div>
              <div class="mt-4 md:mt-0">
                <router-link :to="`/order-details/${order.id}`" @click="storeOrder(order)"
                  class="text-sm text-blue-600 hover:underline">
                  Xem chi tiết
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Không tìm thấy đơn hàng -->
      <div v-if="!loading && orders.length === 0 && phone" class="text-center">
        <p class="text-gray-600">Không tìm thấy đơn hàng nào cho số điện thoại này.</p>
      </div>

      <!-- Phân trang -->
      <div v-if="pagination.total > pagination.per_page" class="mt-6 flex justify-center space-x-2">
        <button v-for="page in pagination.last_page" :key="page"
          @click="changePage(page)"
          :class="['px-4 py-2 rounded-md', pagination.current_page === page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300']">
          {{ page }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const phone = ref('');
const orders = ref([]);
const loading = ref(false);
const error = ref(null);
const pagination = ref({
  total: 0,
  per_page: 10,
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0
});

// Hàm xử lý tìm kiếm đơn hàng
const handleOrderSearch = async (page = 1) => {
  if (!phone.value.trim() || !/^[0-9]{10}$/.test(phone.value)) {
    error.value = 'Vui lòng nhập số điện thoại hợp lệ (10 chữ số).';
    return;
  }

  loading.value = true;
  error.value = null;
  orders.value = [];

  try {
    const response = await axios.post('http://localhost:8000/api/orders/lookup', {
      phone: phone.value,
      page
    });

    console.log('API Response:', response.data); // In phản hồi để kiểm tra

    if (response.data.data && Array.isArray(response.data.data)) {
      orders.value = response.data.data;
      pagination.value = response.data.pagination;
    } else {
      error.value = response.data.message || 'Dữ liệu từ API không hợp lệ';
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Không thể tra cứu đơn hàng: ' + err.message;
    if (err.response?.status === 500) {
      error.value = 'Lỗi server, vui lòng thử lại sau.';
    }
  } finally {
    loading.value = false;
  }
};

// Hàm thay đổi trang
const changePage = (page) => {
  if (page !== pagination.value.current_page) {
    handleOrderSearch(page);
  }
};

// Lưu đơn hàng vào localStorage
const storeOrder = (order) => {
  localStorage.setItem('selected_order', JSON.stringify(order));
};

// Định dạng ngày
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

// Định dạng giá tiền
const formatPrice = (amount) => {
  return new Intl.NumberFormat('vi-VN').format(amount);
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>
```