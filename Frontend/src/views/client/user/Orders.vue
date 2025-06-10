<template>
  <div class="user-orders">
    <h2 class="text-xl font-semibold mb-6">Đơn Mua Của Tôi</h2>

    <div class="flex border-b border-gray-200 mb-6">
      <button v-for="tab in orderTabs" :key="tab.value" @click="activeTab = tab.value"
        :class="['px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200',
          activeTab === tab.value ? 'border-red-500 text-red-500' : 'border-transparent text-gray-700 hover:text-red-500 hover:border-red-50']">
        {{ tab.label }}
        <span v-if="tab.count > 0" class="ml-1 text-xs px-2 py-1 rounded-full bg-red-500 text-white">{{ tab.count
          }}</span>
      </button>
    </div>

    <div class="relative mb-6">
      <input type="text" placeholder="Bạn có thể tìm kiếm tên Shop, ID đơn hàng hoặc Tên Sản phẩm"
        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-red-500" />
      <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
    </div>

    <div class="order-list space-y-4">
      <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">

        <div class="flex justify-between items-center mb-4 text-sm">
          <p class="text-gray-600">
            [Quốc Tế Đơn hàng đã nhập kho quốc tế: Thâm Quyến]
            <i class="fas fa-question-circle ml-1 text-gray-400"></i>
          </p>
          <p class="text-red-500 font-semibold">CHỜ GIAO HÀNG</p>
        </div>

        <div class="flex items-center border-t border-b border-gray-100 py-4 mb-4">
          <img src="https://via.placeholder.com/80" alt="Product Image"
            class="w-20 h-20 object-cover border border-gray-200 rounded-sm mr-4" />
          <div class="flex-1">
            <p class="font-medium text-gray-800 mb-1">Ốp Điện Thoại Trong Suốt Làm Mắt Graphene Cho Vivo IQOO Z9 Turbo
              Plus Z9X Neo 9 8 9S Pro Plus Pro + 5G Vỏ Tản Nhiệt TPU Chống Sốc Vỏ Capa</p>
            <p class="text-sm text-gray-600">Phân loại hàng: Đen, Vivo IQOO Z9</p>
            <p class="text-sm text-gray-600">x1</p>
          </div>
          <div class="text-right flex items-center">
            <span class="text-gray-400 line-through mr-2">₫74.978</span>
            <span class="text-red-500 font-semibold">₫56.233</span>
          </div>
        </div>

        <div class="text-right mb-4">
          <span class="text-gray-700 mr-2">Thành tiền:</span>
          <span class="text-red-500 text-xl font-semibold">₫56.233</span>
        </div>

        <div class="flex justify-end items-center">
          <p class="text-sm text-gray-500 mr-4">
            Vui lòng chỉ nhấn "Đã Nhận Hàng" khi đơn hàng đã được giao đến bạn và sản phẩm nhận được không có vấn đề
            nào.
          </p>
          <button class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md mr-2 hover:bg-gray-300">
            Đã Nhận Hàng
          </button>
          <button class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
            Liên Hệ Người Bán
          </button>
        </div>
      </div>

            <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">

        <div class="flex justify-between items-center mb-4 text-sm">
          <p class="text-gray-600">
            [Quốc Tế Đơn hàng đã nhập kho quốc tế: Thâm Quyến]
            <i class="fas fa-question-circle ml-1 text-gray-400"></i>
          </p>
          <p class="text-red-500 font-semibold">CHỜ GIAO HÀNG</p>
        </div>

        <div class="flex items-center border-t border-b border-gray-100 py-4 mb-4">
          <img src="https://via.placeholder.com/80" alt="Product Image"
            class="w-20 h-20 object-cover border border-gray-200 rounded-sm mr-4" />
          <div class="flex-1">
            <p class="font-medium text-gray-800 mb-1">Ốp Điện Thoại Trong Suốt Làm Mắt Graphene Cho Vivo IQOO Z9 Turbo
              Plus Z9X Neo 9 8 9S Pro Plus Pro + 5G Vỏ Tản Nhiệt TPU Chống Sốc Vỏ Capa</p>
            <p class="text-sm text-gray-600">Phân loại hàng: Đen, Vivo IQOO Z9</p>
            <p class="text-sm text-gray-600">x1</p>
          </div>
          <div class="text-right flex items-center">
            <span class="text-gray-400 line-through mr-2">₫74.978</span>
            <span class="text-red-500 font-semibold">₫56.233</span>
          </div>
        </div>

        <div class="text-right mb-4">
          <span class="text-gray-700 mr-2">Thành tiền:</span>
          <span class="text-red-500 text-xl font-semibold">₫56.233</span>
        </div>

        <div class="flex justify-end items-center">
          <p class="text-sm text-gray-500 mr-4">
            Vui lòng chỉ nhấn "Đã Nhận Hàng" khi đơn hàng đã được giao đến bạn và sản phẩm nhận được không có vấn đề
            nào.
          </p>
          <button class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md mr-2 hover:bg-gray-300">
            Đã Nhận Hàng
          </button>
          <button class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
            Liên Hệ Người Bán
          </button>
        </div>
      </div>

            <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">

        <div class="flex justify-between items-center mb-4 text-sm">
          <p class="text-gray-600">
            [Quốc Tế Đơn hàng đã nhập kho quốc tế: Thâm Quyến]
            <i class="fas fa-question-circle ml-1 text-gray-400"></i>
          </p>
          <p class="text-red-500 font-semibold">CHỜ GIAO HÀNG</p>
        </div>

        <div class="flex items-center border-t border-b border-gray-100 py-4 mb-4">
          <img src="https://via.placeholder.com/80" alt="Product Image"
            class="w-20 h-20 object-cover border border-gray-200 rounded-sm mr-4" />
          <div class="flex-1">
            <p class="font-medium text-gray-800 mb-1">Ốp Điện Thoại Trong Suốt Làm Mắt Graphene Cho Vivo IQOO Z9 Turbo
              Plus Z9X Neo 9 8 9S Pro Plus Pro + 5G Vỏ Tản Nhiệt TPU Chống Sốc Vỏ Capa</p>
            <p class="text-sm text-gray-600">Phân loại hàng: Đen, Vivo IQOO Z9</p>
            <p class="text-sm text-gray-600">x1</p>
          </div>
          <div class="text-right flex items-center">
            <span class="text-gray-400 line-through mr-2">₫74.978</span>
            <span class="text-red-500 font-semibold">₫56.233</span>
          </div>
        </div>

        <div class="text-right mb-4">
          <span class="text-gray-700 mr-2">Thành tiền:</span>
          <span class="text-red-500 text-xl font-semibold">₫56.233</span>
        </div>

        <div class="flex justify-end items-center">
          <p class="text-sm text-gray-500 mr-4">
            Vui lòng chỉ nhấn "Đã Nhận Hàng" khi đơn hàng đã được giao đến bạn và sản phẩm nhận được không có vấn đề
            nào.
          </p>
          <button class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md mr-2 hover:bg-gray-300">
            Đã Nhận Hàng
          </button>
          <button class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
            Liên Hệ Người Bán
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

// Trạng thái tab hiện tại
const activeTab = ref('all'); // Mặc định là 'all'

// Danh sách các tab và số lượng đơn hàng (có thể động từ API)
const orderTabs = ref([
  { label: 'Tất cả', value: 'all', count: 0 },
  { label: 'Chờ thanh toán', value: 'pending_payment', count: 0 },
  { label: 'Vận chuyển', value: 'shipping', count: 0 },
  { label: 'Chờ giao hàng', value: 'pending_delivery', count: 1 }, // Có 1 đơn hàng chờ giao
  { label: 'Hoàn thành', value: 'completed', count: 0 },
  { label: 'Đã hủy', value: 'cancelled', count: 0 },
  { label: 'Trả hàng/Hoàn tiền', value: 'return_refund', count: 0 },
]);

// Danh sách đơn hàng (dữ liệu giả định)
const orders = ref([]);

// Hàm để tải đơn hàng dựa trên activeTab
const fetchOrders = async (status) => {
  console.log(`Đang tải đơn hàng với trạng thái: ${status}`);
  // Trong thực tế, bạn sẽ gọi API ở đây để fetch dữ liệu đơn hàng
  // dựa trên 'status'.
  // Ví dụ:
  // try {
  //   const response = await fetch(`/api/orders?status=${status}`);
  //   const data = await response.json();
  //   orders.value = data;
  // } catch (error) {
  //   console.error('Lỗi khi tải đơn hàng:', error);
  // }

  // Dữ liệu giả định cho mục đích hiển thị
  if (status === 'pending_delivery') {
    orders.value = [
      {
        id: 'ORDER12345',
        shopName: 'yangxinyil.cn',
        isPreferred: true,
        status: 'CHỜ GIAO HÀNG',
        shippingInfo: '[Quốc Tế Đơn hàng đã nhập kho quốc tế: Thâm Quyến]',
        product: {
          name: 'Ốp Điện Thoại Trong Suốt Làm Mắt Graphene Cho Vivo IQOO Z9 Turbo Plus Z9X Neo 9 8 9S Pro Plus Pro + 5G Vỏ Tản Nhiệt TPU Chống Sốc Vỏ Capa',
          variant: 'Đen, Vivo IQOO Z9',
          quantity: 1,
          imageUrl: 'https://via.placeholder.com/80',
          originalPrice: 74978,
          currentPrice: 56233,
        },
        totalAmount: 56233,
        note: 'Vui lòng chỉ nhấn "Đã Nhận Hàng" khi đơn hàng đã được giao đến bạn và sản phẩm nhận được không có vấn đề nào.',
      },
      // Thêm các đơn hàng khác nếu có
    ];
  } else {
    orders.value = []; // Giả định không có đơn hàng cho các tab khác
  }
};

// Theo dõi sự thay đổi của activeTab để tải lại đơn hàng
watch(activeTab, (newTab) => {
  fetchOrders(newTab);
});

// Tải đơn hàng lần đầu khi component được mount
onMounted(() => {
  fetchOrders(activeTab.value);
});

// Các hàm xử lý hành động trên đơn hàng
const markAsReceived = (orderId) => {
  console.log(`Đã nhận hàng cho đơn: ${orderId}`);
  // Gọi API để cập nhật trạng thái đơn hàng
};

const contactSeller = (shopName) => {
  console.log(`Liên hệ người bán: ${shopName}`);
  // Logic để mở cửa sổ chat hoặc chuyển đến trang chat
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Không cần style đặc biệt ngoài Tailwind CSS */
</style>