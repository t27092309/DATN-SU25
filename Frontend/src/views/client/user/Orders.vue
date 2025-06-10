<template>
  <div class="user-orders p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold mb-8 text-gray-800">Đơn Mua Của Tôi</h2>

    <div class="flex border-b border-gray-200 mb-6 bg-white rounded-t-lg shadow-sm overflow-x-auto whitespace-nowrap">
      <button v-for="tab in orderTabs" :key="tab.value" @click="activeTab = tab.value"
        :class="['flex-shrink-0 px-3 sm:px-6 py-3 text-base font-medium border-b-2 transition-colors duration-200 flex items-center justify-center',
          activeTab === tab.value ? 'border-red-600 text-red-600' : 'border-transparent text-gray-700 hover:text-red-600 hover:border-red-100']">
        <span>{{ tab.label }}</span>
        <span v-if="tab.count > 0" class="ml-2 text-xs px-2 py-1 rounded-full bg-red-500 text-white font-bold">{{ tab.count
          }}</span>
      </button>
    </div>

    <div class="relative mb-6">
      <input type="text" placeholder="Bạn có thể tìm kiếm Tên sản phẩm hoặc ID đơn hàng"
        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 shadow-sm" />
      <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
    </div>

    <div class="order-list space-y-6">
      <div v-if="orders.length === 0"
        class="text-center py-10 text-gray-500 text-lg bg-white rounded-lg shadow-sm">
        Không có đơn hàng nào trong mục này.
      </div>

      <div v-for="order in orders" :key="order.id"
        class="border border-gray-200 rounded-lg p-5 bg-white shadow-md hover:shadow-lg transition-shadow duration-200">

        <div class="flex justify-between items-center mb-4 text-sm">
          <p class="text-gray-600">
            {{ order.shippingInfo }}
            <i class="fas fa-question-circle ml-1 text-gray-400 cursor-pointer hover:text-gray-600"
              title="Thông tin vận chuyển"></i>
          </p>
          <p :class="['font-bold text-base', order.statusColor || 'text-red-600']">{{ order.status }}</p>
        </div>

        <div class="flex items-center border-t border-b border-gray-100 py-4 mb-4">
          <img :src="order.product.imageUrl" :alt="order.product.name"
            class="w-24 h-24 object-cover border border-gray-200 rounded-md mr-4 flex-shrink-0" />
          <div class="flex-1">
            <p class="font-semibold text-gray-800 mb-1 text-base">{{ order.product.name }}</p>
            <p class="text-sm text-gray-600">Phân loại hàng: {{ order.product.variant }}</p>
            <p class="text-sm text-gray-600">x{{ order.product.quantity }}</p>
          </div>
          <div class="text-right flex flex-col items-end min-w-[120px]">
            <span v-if="order.product.originalPrice" class="text-gray-400 line-through text-sm">
              ₫{{ order.product.originalPrice.toLocaleString('vi-VN') }}
            </span>
            <span class="text-red-600 font-bold text-lg">
              ₫{{ order.product.currentPrice.toLocaleString('vi-VN') }}
            </span>
          </div>
        </div>

        <div class="text-right mb-4">
          <span class="text-gray-700 mr-2 text-base">Thành tiền:</span>
          <span class="text-red-600 text-2xl font-bold">
            ₫{{ order.totalAmount.toLocaleString('vi-VN') }}
          </span>
        </div>

        <div class="flex flex-col sm:flex-row justify-end items-end sm:items-center space-y-3 sm:space-y-0 sm:space-x-3">
          <p class="text-xs text-gray-500 text-right sm:text-left flex-1 leading-relaxed">
            {{ order.note }}
          </p>
          <template v-if="order.status === 'CHỜ GIAO HÀNG'">
            <button @click="markAsReceived(order.id)"
              class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors duration-200 shadow-sm">
              Đã Nhận Hàng
            </button>
            <button @click="contactSeller(order.shopName)"
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Liên Hệ Người Bán
            </button>
          </template>
          <template v-else-if="order.status === 'HOÀN THÀNH'">
            <button
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Mua Lại
            </button>
            <button
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Xem Chi Tiết
            </button>
          </template>
          <template v-else-if="order.status === 'ĐÃ HỦY'">
            <button
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Đặt Lại
            </button>
            <button
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Xem Lý Do Hủy
            </button>
          </template>
          <template v-else-if="order.status === 'CHỜ THANH TOÁN'">
            <button
              class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors duration-200 shadow-sm">
              Thanh Toán Ngay
            </button>
            <button
              class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors duration-200 shadow-sm">
              Hủy Đơn Hàng
            </button>
          </template>
          </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const activeTab = ref('all');

const orderTabs = ref([
  { label: 'Tất cả', value: 'all', count: 2 }, // Cập nhật số lượng ví dụ
  { label: 'Chờ thanh toán', value: 'pending_payment', count: 1 },
  { label: 'Vận chuyển', value: 'shipping', count: 0 },
  { label: 'Chờ giao hàng', value: 'pending_delivery', count: 1 },
  { label: 'Hoàn thành', value: 'completed', count: 1 },
  { label: 'Đã hủy', value: 'cancelled', count: 1 },
  { label: 'Trả hàng/Hoàn tiền', value: 'return_refund', count: 0 },
]);

const orders = ref([]);

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
  switch (status) {
    case 'all':
      orders.value = [
        {
          id: 'ORDER12345',
          shopName: 'yangxinyil.cn',
          isPreferred: true,
          status: 'CHỜ GIAO HÀNG',
          statusColor: 'text-orange-500', // Thêm màu sắc cho trạng thái
          shippingInfo: '[Quốc Tế Đơn hàng đã nhập kho quốc tế: Thâm Quyến]',
          product: {
            name: 'Ốp Điện Thoại Trong Suốt Làm Mắt Graphene Cho Vivo IQOO Z9 Turbo Plus Z9X Neo 9 8 9S Pro Plus Pro + 5G Vỏ Tản Nhiệt TPU Chống Sốc Vỏ Capa',
            variant: 'Đen, Vivo IQOO Z9',
            quantity: 1,
            imageUrl: 'https://via.placeholder.com/80?text=SP1',
            originalPrice: 74978,
            currentPrice: 56233,
          },
          totalAmount: 56233,
          note: 'Vui lòng chỉ nhấn "Đã Nhận Hàng" khi đơn hàng đã được giao đến bạn và sản phẩm nhận được không có vấn đề nào.',
        },
        {
          id: 'ORDER12346',
          shopName: 'mobile-accessories.vn',
          isPreferred: false,
          status: 'CHỜ THANH TOÁN',
          statusColor: 'text-blue-500',
          shippingInfo: '[Nội Địa Đơn hàng chưa thanh toán]',
          product: {
            name: 'Tai nghe Bluetooth không dây chất lượng cao, âm thanh sống động, pin trâu',
            variant: 'Trắng, Phiên bản 2024',
            quantity: 2,
            imageUrl: 'https://via.placeholder.com/80?text=SP2',
            originalPrice: 150000,
            currentPrice: 120000,
          },
          totalAmount: 240000,
          note: 'Vui lòng thanh toán để đơn hàng được xử lý.',
        },
        {
          id: 'ORDER12347',
          shopName: 'thoitrangnu.com',
          isPreferred: false,
          status: 'HOÀN THÀNH',
          statusColor: 'text-green-600',
          shippingInfo: '[Đã Giao Hàng Thành Công]',
          product: {
            name: 'Áo thun nữ cotton basic, màu sắc thời trang, dễ phối đồ',
            variant: 'Đen, Size M',
            quantity: 1,
            imageUrl: 'https://via.placeholder.com/80?text=SP3',
            originalPrice: 199000,
            currentPrice: 99000,
          },
          totalAmount: 99000,
          note: 'Cảm ơn bạn đã mua hàng!',
        },
        {
          id: 'ORDER12348',
          shopName: 'sachonline.vn',
          isPreferred: false,
          status: 'ĐÃ HỦY',
          statusColor: 'text-gray-500',
          shippingInfo: '[Đơn hàng đã được hủy]',
          product: {
            name: 'Sách "Đắc Nhân Tâm" - Tái bản 2023',
            variant: 'Bản bìa cứng',
            quantity: 1,
            imageUrl: 'https://via.placeholder.com/80?text=SP4',
            originalPrice: 120000,
            currentPrice: 120000,
          },
          totalAmount: 120000,
          note: 'Đơn hàng đã bị hủy theo yêu cầu của bạn.',
        },
      ];
      break;
    case 'pending_payment':
      orders.value = [
        {
          id: 'ORDER12346',
          shopName: 'mobile-accessories.vn',
          isPreferred: false,
          status: 'CHỜ THANH TOÁN',
          statusColor: 'text-blue-500',
          shippingInfo: '[Nội Địa Đơn hàng chưa thanh toán]',
          product: {
            name: 'Tai nghe Bluetooth không dây chất lượng cao, âm thanh sống động, pin trâu',
            variant: 'Trắng, Phiên bản 2024',
            quantity: 2,
            imageUrl: 'https://via.placeholder.com/80?text=SP2',
            originalPrice: 150000,
            currentPrice: 120000,
          },
          totalAmount: 240000,
          note: 'Vui lòng thanh toán để đơn hàng được xử lý.',
        },
      ];
      break;
    case 'shipping':
      orders.value = [
        // Thêm dữ liệu giả định cho trạng thái 'Vận chuyển'
        // { ... }
      ];
      break;
    case 'pending_delivery':
      orders.value = [
        {
          id: 'ORDER12345',
          shopName: 'yangxinyil.cn',
          isPreferred: true,
          status: 'CHỜ GIAO HÀNG',
          statusColor: 'text-orange-500',
          shippingInfo: '[Quốc Tế Đơn hàng đã nhập kho quốc tế: Thâm Quyến]',
          product: {
            name: 'Ốp Điện Thoại Trong Suốt Làm Mắt Graphene Cho Vivo IQOO Z9 Turbo Plus Z9X Neo 9 8 9S Pro Plus Pro + 5G Vỏ Tản Nhiệt TPU Chống Sốc Vỏ Capa',
            variant: 'Đen, Vivo IQOO Z9',
            quantity: 1,
            imageUrl: 'https://via.placeholder.com/80?text=SP1',
            originalPrice: 74978,
            currentPrice: 56233,
          },
          totalAmount: 56233,
          note: 'Vui lòng chỉ nhấn "Đã Nhận Hàng" khi đơn hàng đã được giao đến bạn và sản phẩm nhận được không có vấn đề nào.',
        },
      ];
      break;
    case 'completed':
      orders.value = [
        {
          id: 'ORDER12347',
          shopName: 'thoitrangnu.com',
          isPreferred: false,
          status: 'HOÀN THÀNH',
          statusColor: 'text-green-600',
          shippingInfo: '[Đã Giao Hàng Thành Công]',
          product: {
            name: 'Áo thun nữ cotton basic, màu sắc thời trang, dễ phối đồ',
            variant: 'Đen, Size M',
            quantity: 1,
            imageUrl: 'https://via.placeholder.com/80?text=SP3',
            originalPrice: 199000,
            currentPrice: 99000,
          },
          totalAmount: 99000,
          note: 'Cảm ơn bạn đã mua hàng!',
        },
      ];
      break;
    case 'cancelled':
      orders.value = [
        {
          id: 'ORDER12348',
          shopName: 'sachonline.vn',
          isPreferred: false,
          status: 'ĐÃ HỦY',
          statusColor: 'text-gray-500',
          shippingInfo: '[Đơn hàng đã được hủy]',
          product: {
            name: 'Sách "Đắc Nhân Tâm" - Tái bản 2023',
            variant: 'Bản bìa cứng',
            quantity: 1,
            imageUrl: 'https://via.placeholder.com/80?text=SP4',
            originalPrice: 120000,
            currentPrice: 120000,
          },
          totalAmount: 120000,
          note: 'Đơn hàng đã bị hủy theo yêu cầu của bạn.',
        },
      ];
      break;
    case 'return_refund':
      orders.value = [
        // Thêm dữ liệu giả định cho trạng thái 'Trả hàng/Hoàn tiền'
        // { ... }
      ];
      break;
    default:
      orders.value = [];
  }
};

watch(activeTab, (newTab) => {
  fetchOrders(newTab);
});

onMounted(() => {
  fetchOrders(activeTab.value);
});

const markAsReceived = (orderId) => {
  alert(`Đã nhận hàng cho đơn: ${orderId}. (Logic cập nhật trạng thái sẽ được gọi ở đây)`);
  console.log(`Đã nhận hàng cho đơn: ${orderId}`);
  // Gọi API để cập nhật trạng thái đơn hàng
};

const contactSeller = (shopName) => {
  alert(`Đang liên hệ với người bán: ${shopName}. (Logic chuyển hướng/chat sẽ được gọi ở đây)`);
  console.log(`Liên hệ người bán: ${shopName}`);
  // Logic để mở cửa sổ chat hoặc chuyển đến trang chat
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Không cần style đặc biệt ngoài Tailwind CSS */
</style>