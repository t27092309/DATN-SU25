<template>
  <div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6 text-center">Chi tiết đơn hàng #{{ idDonHang }}</h1>

    <div v-if="isLoading" class="text-center py-8">
      <p class="text-gray-600">Đang tải chi tiết đơn hàng...</p>
      </div>

    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Lỗi!</strong>
      <span class="block sm:inline"> {{ error }}</span>
      <div class="mt-4 text-center">
        <router-link :to="{ name: 'Orders' }" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
          Quay lại danh sách đơn hàng
        </router-link>
      </div>
    </div>

    <div v-else-if="!order" class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative text-center">
      <p>Không tìm thấy đơn hàng này.</p>
      <div class="mt-4 text-center">
        <router-link :to="{ name: 'Orders' }" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
          Quay lại danh sách đơn hàng
        </router-link>
      </div>
    </div>

    <div v-else class="bg-white shadow-lg rounded-lg p-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-b pb-6">
        <div>
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Thông tin chung</h2>
          <p class="text-gray-700 mb-2"><span class="font-medium">Mã đơn hàng:</span> #{{ order.id }}</p>
          <p class="text-gray-700 mb-2"><span class="font-medium">Ngày đặt:</span> {{ formatDate(order.created_at) }}</p>
          <p class="text-gray-700 mb-2"><span class="font-medium">Trạng thái:</span>
            <span :class="getStatusClass(order.status)" class="px-3 py-1 rounded-full text-sm font-medium ml-2">
              {{ getStatusText(order.status) }}
            </span>
          </p>
          <p class="text-gray-700 mb-2"><span class="font-medium">Tổng tiền:</span> <span class="font-bold text-green-600 text-xl">{{ formatCurrency(order.total_price) }}</span></p>
          <p class="text-gray-700 mb-2"><span class="font-medium">Phí vận chuyển:</span> {{ formatCurrency(order.shipping_fee) }}</p>
          <p class="text-gray-700 mb-2"><span class="font-medium">Phương thức thanh toán:</span> {{ getPaymentMethodText(order.payment_method) }}</p>
          <p v-if="order.notes" class="text-gray-700 mb-2"><span class="font-medium">Ghi chú:</span> {{ order.notes }}</p>
        </div>

        <div>
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Địa chỉ giao hàng</h2>
          <div v-if="order.order_address">
            <p class="text-gray-700 mb-1"><span class="font-medium">Người nhận:</span> {{ order.order_address.recipient_name }}</p>
            <p class="text-gray-700 mb-1"><span class="font-medium">Số điện thoại:</span> {{ order.order_address.phone_number }}</p>
            <p class="text-gray-700 mb-1"><span class="font-medium">Địa chỉ:</span> {{ order.order_address.full_address }}</p>
          </div>
          <p v-else class="text-gray-600 italic">Không có thông tin địa chỉ.</p>

          <h2 class="text-2xl font-semibold text-gray-800 mb-4 mt-6">Thông tin thanh toán</h2>
          <div v-if="order.payment_info">
            <p class="text-gray-700 mb-1"><span class="font-medium">Trạng thái thanh toán:</span>
              <span :class="getPaymentStatusClass(order.payment_info.payment_status)" class="px-3 py-1 rounded-full text-sm font-medium ml-2">
                {{ getPaymentStatusText(order.payment_info.payment_status) }}
              </span>
            </p>
            <p class="text-gray-700 mb-1"><span class="font-medium">Số tiền đã thanh toán:</span> {{ formatCurrency(order.payment_info.amount) }}</p>
            <p v-if="order.payment_info.paid_at" class="text-gray-700 mb-1"><span class="font-medium">Thời gian thanh toán:</span> {{ formatDate(order.payment_info.paid_at) }}</p>
          </div>
          <p v-else class="text-gray-600 italic">Chưa có thông tin thanh toán.</p>
        </div>
      </div>

      <div>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Sản phẩm trong đơn hàng</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead>
              <tr class="bg-gray-100 text-left text-gray-600 uppercase text-sm leading-normal">
                <th class="py-3 px-6 text-left">Sản phẩm</th>
                <th class="py-3 px-6 text-left">Biến thể</th>
                <th class="py-3 px-6 text-center">Số lượng</th>
                <th class="py-3 px-6 text-right">Giá / SP</th>
                <th class="py-3 px-6 text-right">Tổng</th>
              </tr>
            </thead>
            <tbody class="text-gray-700 text-sm font-light">
              <tr v-for="item in order.items" :key="item.id" class="border-b border-gray-200 hover:bg-gray-50">
                <td class="py-3 px-6 text-left whitespace-nowrap">
                  <div class="flex items-center">
                    <img :src="item.product_image" alt="Product Image" class="w-10 h-10 object-cover rounded-md mr-3">
                    <span>{{ item.product_name }}</span>
                  </div>
                </td>
                <td class="py-3 px-6 text-left">{{ item.variant_name }}</td>
                <td class="py-3 px-6 text-center">{{ item.quantity }}</td>
                <td class="py-3 px-6 text-right">{{ formatCurrency(item.price_each) }}</td>
                <td class="py-3 px-6 text-right font-medium">{{ formatCurrency(item.subtotal) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mt-8 text-center">
        <router-link :to="{ name: 'Orders' }" class="inline-block bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 transition-colors duration-300">
          Quay lại danh sách đơn hàng
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const api = axios;
const route = useRoute();
const router = useRouter();

const props = defineProps({
  idDonHang: {
    type: [String, Number],
    required: true
  }
});

const order = ref(null);
const isLoading = ref(true);
const error = ref(null);

const fetchOrderDetails = async (id) => {
  isLoading.value = true;
  error.value = null;
  order.value = null; // Reset order data
  try {
    const response = await api.get(`orders/${id}`);
    order.value = response.data.order;
  } catch (err) {
    console.error('Error fetching order details:', err);
    if (err.response && err.response.status === 404) {
      error.value = 'Đơn hàng không tồn tại hoặc bạn không có quyền truy cập.';
    } else if (err.response && err.response.status === 401) {
      router.push({ name: 'login' }); // Chuyển hướng nếu không xác thực
    } else {
      error.value = 'Không thể tải chi tiết đơn hàng. Vui lòng thử lại sau.';
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  if (props.idDonHang) {
    fetchOrderDetails(props.idDonHang);
  } else {
    error.value = 'Không tìm thấy ID đơn hàng.';
    isLoading.value = false;
  }
});

// Watch for changes in idDonHang prop (if navigating between details pages)
watch(() => props.idDonHang, (newidDonHang) => {
  if (newidDonHang) {
    fetchOrderDetails(newidDonHang);
  }
});

// Helper functions for display (same as MyOrdersPage.vue)
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '0₫';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString('vi-VN', options);
};

const getStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-200 text-yellow-800';
    case 'processing': return 'bg-blue-200 text-blue-800';
    case 'shipped': return 'bg-purple-200 text-purple-800';
    case 'completed': return 'bg-green-200 text-green-800';
    case 'cancelled': return 'bg-red-200 text-red-800';
    default: return 'bg-gray-200 text-gray-800';
  }
};

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

const getPaymentMethodText = (method) => {
  switch (method) {
    case 'cod': return 'Thanh toán khi nhận hàng (COD)';
    case 'vnpay': return 'VNPay';
    case 'momo': return 'Momo';
    default: return 'Không rõ';
  }
};

const getPaymentStatusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-200 text-yellow-800';
    case 'paid': return 'bg-green-200 text-green-800';
    case 'failed': return 'bg-red-200 text-red-800';
    default: return 'bg-gray-200 text-gray-800';
  }
};

const getPaymentStatusText = (status) => {
  switch (status) {
    case 'pending': return 'Chờ thanh toán';
    case 'paid': return 'Đã thanh toán';
    case 'failed': return 'Thất bại';
    default: return 'Không rõ';
  }
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>