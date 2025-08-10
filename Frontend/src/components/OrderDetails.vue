```vue
     <template>
       <div class="order-details-container bg-gray-100 min-h-screen py-8">
         <div class="container mx-auto px-4">
           <h1 class="text-3xl font-semibold text-gray-800 mb-6 text-center">
             Chi tiết đơn hàng #{{ order.order_id }}
           </h1>
           <div v-if="loading" class="text-center">
             <p class="text-gray-600">Đang tải dữ liệu...</p>
           </div>
           <div v-if="error" class="text-center text-red-500 mb-4">
             {{ error }}
           </div>
           <div v-if="order" class="bg-white shadow-md rounded-lg p-6">
             <p class="text-sm text-gray-600">Ngày đặt: {{ formatDate(order.created_at) }}</p>
             <p class="text-sm text-gray-600">Tổng tiền: {{ formatPrice(order.total_amount) }} VNĐ</p>
             <p class="text-sm text-gray-600">Trạng thái: {{ order.status }}</p>
             <!-- Thêm các chi tiết khác như danh sách sản phẩm, địa chỉ giao hàng, v.v. -->
           </div>
         </div>
       </div>
     </template>

     <script setup>
     import { ref, onMounted } from 'vue';
     import { useRoute } from 'vue-router';
     import axios from 'axios';
     import { useAuthStore } from '@/stores/auth';

     const route = useRoute();
     const authStore = useAuthStore();
     const order = ref(null);
     const loading = ref(false);
     const error = ref(null);

     const fetchOrderDetails = async () => {
       loading.value = true;
       error.value = null;

       try {
         const response = await axios.get(`http://localhost:8000/api/orders/${route.params.orderId}`, {
           headers: {
             Authorization: `Bearer ${authStore.token}`
           }
         });
         order.value = response.data.data;
       } catch (err) {
         error.value = 'Không thể tải chi tiết đơn hàng: ' + (err.message || 'Lỗi không xác định');
       } finally {
         loading.value = false;
       }
     };

     const formatDate = (dateString) => {
       const date = new Date(dateString);
       return date.toLocaleDateString('vi-VN', {
         day: '2-digit',
         month: '2-digit',
         year: 'numeric'
       });
     };

     const formatPrice = (amount) => {
       return new Intl.NumberFormat('vi-VN').format(amount);
     };

     onMounted(() => {
       authStore.initializeAuth();
       if (authStore.isAuthenticated) {
         fetchOrderDetails();
       } else {
         error.value = 'Vui lòng đăng nhập để xem chi tiết đơn hàng.';
       }
     });
     </script>

     <style scoped>
     @import '@/assets/tailwind.css';
     </style>
     ```