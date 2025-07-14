<template>
  <div class="order-list-container">
    <h2>Danh sách Đơn hàng</h2>

    <div class="filters-and-search">
      <div class="order-tabs">
        <button v-for="tab in orderTabs" :key="tab.value"
          :class="{ 'tab-button': true, 'active': filters.status === tab.value }" @click="selectTab(tab.value)">
          {{ tab.label }}
        </button>
      </div>

      <div class="search-box">
        <label for="orderSearch">Tìm kiếm (ID đơn hàng / Tên người dùng / SĐT):</label>
        <input id="orderSearch" type="text" v-model="filters.search" placeholder="Nhập ID, tên người dùng hoặc SĐT" />
      </div>
    </div>

    <div v-if="loading" class="loading-indicator">Đang tải đơn hàng...</div>
    <div v-else-if="!loading && orders && orders.length === 0" class="no-orders">Không có đơn hàng nào.</div>
    <div v-else>
      <table class="order-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Khách hàng</th>
            <th>Tổng tiền</th>
            <th>Ngày tạo</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td>{{ order.id }}</td>
            <td>{{ order.user ? order.user.name : 'N/A' }}</td>
            <td>{{ order.total_price_formatted }}</td>
            <td>{{ order.display_created_at }}</td>
            <td>
              <div class="status-cell">
                <span v-if="!order.isEditingStatus" :class="getStatusClass(order.status)"
                  @click="startEditStatus(order)">
                  {{ order.status_label || order.status }}
                </span>
                <select v-else v-model="order.status" @change="updateOrderStatus(order)" @blur="cancelEditStatus(order)"
                  :disabled="order.isUpdatingStatus">
                  <option v-for="statusOpt in availableStatusOptions" :key="statusOpt.value" :value="statusOpt.value">
                    {{ statusOpt.label }}
                  </option>
                </select>
                <span v-if="order.isUpdatingStatus" class="status-spinner">🔄</span>
              </div>
            </td>
            <td>
              <button @click="viewOrderDetails(order.id)" class="btn-view">Xem chi tiết</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination">
        <button @click="fetchOrders(pagination.current_page - 1)" :disabled="!pagination.prev_page_url">
          Trước
        </button>
        <span>Trang {{ pagination.current_page }} / {{ pagination.last_page }}</span>
        <button @click="fetchOrders(pagination.current_page + 1)" :disabled="!pagination.next_page_url">
          Sau
        </button>
      </div>
    </div>

    <div v-if="showDetailsModal" class="modal-overlay" @click.self="closeDetailsModal">
      <div class="modal-content">
        <button class="close-button" @click="closeDetailsModal">&times;</button>
        <h3>Chi tiết đơn hàng #{{ selectedOrder.id }}</h3>
        <div v-if="loadingDetails">Đang tải chi tiết...</div>
        <div v-else-if="selectedOrder">
          <p><strong>Khách hàng:</strong> {{ selectedOrder.user ? selectedOrder.user.name : 'N/A' }}</p>
          <p><strong>Email:</strong> {{ selectedOrder.user ? selectedOrder.user.email : 'N/A' }}</p>
          <p><strong>Trạng thái:</strong> <span :class="getStatusClass(selectedOrder.status)">{{
            selectedOrder.status_label || selectedOrder.status }}</span></p>
          <p><strong>Tổng tiền:</strong> {{ selectedOrder.total_price_formatted }}</p>
          <p><strong>Phí vận chuyển:</strong> {{ formatCurrency(selectedOrder.shipping_fee) }}</p>
          <p><strong>Ngày tạo:</strong> {{ selectedOrder.display_created_at }}</p>
          <p><strong>Ghi chú:</strong> {{ selectedOrder.notes || 'Không có' }}</p>

          <h4>Địa chỉ giao hàng:</h4>
          <p v-if="selectedOrder.address">
            <strong>Người nhận:</strong> {{ selectedOrder.address.recipient_name }}<br>
            <strong>Điện thoại:</strong> {{ selectedOrder.address.phone_number }}<br>
            <strong>Địa chỉ:</strong> {{ selectedOrder.address.address_line }}, {{ selectedOrder.address.ward }}, {{
              selectedOrder.address.district }}, {{ selectedOrder.address.province }}
          </p>
          <p v-else>Không có địa chỉ giao hàng.</p>

          <h4>Sản phẩm:</h4>
          <ul v-if="selectedOrder.items && selectedOrder.items.length">
            <li v-for="item in selectedOrder.items" :key="item.id">
              {{ item.variant_name || 'Sản phẩm không xác định' }} ({{ item.quantity }} x {{
                formatCurrency(item.price_each) }})
            </li>
          </ul>
          <p v-else>Không có sản phẩm trong đơn hàng.</p>

          <h4>Thanh toán:</h4>
          <ul v-if="selectedOrder.payments && selectedOrder.payments.length">
            <li v-for="payment in selectedOrder.payments" :key="payment.id">
              {{ formatCurrency(payment.amount) }} - {{ payment.payment_method }} (Trạng thái: {{ payment.status }})
              <span v-if="payment.paid_at"> - Ngày thanh toán: {{ formatOrderCreatedAt(payment.paid_at) }}</span>
            </li>
          </ul>
          <p v-else>Chưa có thanh toán nào.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

// ==============================================
// 1. STATE REACTIVE
// ==============================================
const orders = ref([]);
const pagination = ref({});
const loading = ref(false);
const loadingDetails = ref(false);
const showDetailsModal = ref(false);
const selectedOrder = ref(null);
const filters = ref({
  status: 'all', // Đặt mặc định là 'all' để tab 'Tất cả' được chọn
  search: ''
});

// Định nghĩa lại các trạng thái để phù hợp với trạng thái backend
const orderTabs = ref([
  { label: 'Tất cả', value: 'all', count: 0 },
  { label: 'Chờ xác nhận', value: 'pending', count: 0 },
  { label: 'Đang xử lý', value: 'processing', count: 0 },
  { label: 'Đang giao hàng', value: 'shipped', count: 0 },
  { label: 'Đã giao hàng', value: 'completed', count: 0 },
  { label: 'Đã hủy', value: 'cancelled', count: 0 },
]);

// Các trạng thái có thể chọn trong dropdown
const availableStatusOptions = ref([
  { label: 'Chờ xác nhận', value: 'pending' },
  { label: 'Đang xử lý', value: 'processing' },
  { label: 'Đang giao hàng', value: 'shipped' },
  { label: 'Đã giao hàng', value: 'completed' },
  { label: 'Đã hủy', value: 'cancelled' },
]);

let searchTimeout = null;

// ==============================================
// 2. LOGIC CHUNG (Xử lý lỗi xác thực)
// ==============================================

const showAuthError = (message) => {
  alert(message);
  // window.location.href = '/login';
};

// ==============================================
// 3. LOGIC CHO DANH SÁCH ĐƠN HÀNG VÀ CHI TIẾT
// ==============================================

async function fetchOrders(page = 1) {
  loading.value = true;

  try {
    const params = {
      page: page,
      status: filters.value.status === 'all' ? '' : filters.value.status, // Gửi rỗng nếu là 'all'
      search: filters.value.search
    };
    const response = await axios.get('http://localhost:8000/api/admin/orders', { params });

    orders.value = response.data.data.map(order => {
      const totalPrice = parseFloat(order.total_price) || 0;
      const shippingFee = parseFloat(order.shipping_fee) || 0;
      return {
        ...order,
        total_price: totalPrice,
        shipping_fee: shippingFee,
        total_price_formatted: formatCurrency(totalPrice),
        display_created_at: formatOrderCreatedAt(order.created_at),
        isEditingStatus: false,
        isUpdatingStatus: false,
        originalStatus: order.status,
        // Đảm bảo status_label được thiết lập khi fetch để hiển thị đúng
        status_label: availableStatusOptions.value.find(opt => opt.value === order.status)?.label || order.status
      };
    });
    pagination.value = response.data.meta;

    // Cập nhật số lượng đơn hàng cho từng tab từ dữ liệu 'counts' của API
    if (response.data.counts) {
      orderTabs.value.forEach(tab => {
        tab.count = response.data.counts[tab.value] || 0;
      });
    }

  } catch (error) {
    console.error("Lỗi khi tải đơn hàng:", error);
    if (error.response && error.response.status === 401) {
      showAuthError('Phiên làm việc của bạn đã hết hạn hoặc không có quyền truy cập. Vui lòng đăng nhập lại.');
    } else {
      alert("Không thể tải danh sách đơn hàng. Vui lòng thử lại.");
    }
  } finally {
    loading.value = false;
  }
}

async function viewOrderDetails(orderId) {
  loadingDetails.value = true;
  selectedOrder.value = null;
  showDetailsModal.value = true;

  try {
    const response = await axios.get(`http://localhost:8000/api/admin/orders/${orderId}`);
    const orderData = response.data.data;

    const totalPrice = parseFloat(orderData.total_price) || 0;
    const shippingFee = parseFloat(orderData.shipping_fee) || 0;

    selectedOrder.value = {
      ...orderData,
      total_price: totalPrice,
      shipping_fee: shippingFee,
      total_price_formatted: formatCurrency(totalPrice),
      display_created_at: formatOrderCreatedAt(orderData.created_at),
      items: orderData.items ? orderData.items.map(item => ({
        ...item,
        price_each: parseFloat(item.price_each) || 0
      })) : [],
      payments: orderData.payments ? orderData.payments.map(payment => ({
        ...payment,
        amount: parseFloat(payment.amount) || 0,
        status_label: payment.status
      })) : [],
      status_label: availableStatusOptions.value.find(opt => opt.value === orderData.status)?.label || orderData.status
    };
  } catch (error) {
    console.error("Lỗi khi tải chi tiết đơn hàng:", error);
    if (error.response && error.response.status === 401) {
      showAuthError('Phiên làm việc của bạn đã hết hạn hoặc không có quyền truy cập. Vui lòng đăng nhập lại.');
    } else {
      alert("Không thể tải chi tiết đơn hàng này.");
    }
    closeDetailsModal();
  } finally {
    loadingDetails.value = false;
  }
}

function closeDetailsModal() {
  showDetailsModal.value = false;
  selectedOrder.value = null;
}

// ==============================================
// 4. LOGIC CHO TABS VÀ CẬP NHẬT TRẠNG THÁI
// ==============================================

function selectTab(statusValue) {
  filters.value.status = statusValue;
  fetchOrders(1); // Tải lại danh sách đơn hàng khi đổi tab
}

function startEditStatus(order) {
  order.originalStatus = order.status;
  order.isEditingStatus = true;
}

async function updateOrderStatus(order) {
  const oldStatus = order.originalStatus;
  const newStatus = order.status;

  if (oldStatus === newStatus) {
    order.isEditingStatus = false;
    return;
  }

  // Cập nhật thông báo xác nhận để hiển thị label thay vì value
  if (!confirm(`Bạn có chắc chắn muốn thay đổi trạng thái đơn hàng #${order.id} từ "${order.status_label || oldStatus}" sang "${availableStatusOptions.value.find(opt => opt.value === newStatus)?.label || newStatus}"?`)) {
    order.status = oldStatus;
    order.isEditingStatus = false;
    return;
  }

  order.isUpdatingStatus = true;

  try {
    const response = await axios.patch(`http://localhost:8000/api/admin/orders/${order.id}/status`, {
      status: newStatus
    });

    // --- LOGIC ĐÃ CẬP NHẬT: XỬ LÝ PHẢN HỒI VÀ CẬP NHẬT GIAO DIỆN ---
    if (response.data.success) { // Kiểm tra thuộc tính 'success' từ backend
      // Lấy dữ liệu đơn hàng đã cập nhật từ phản hồi (nếu backend có gửi về)
      // Nếu backend chỉ gửi { success: true, message: '...', data: { id, status, status_label } },
      // chúng ta sẽ lấy trực tiếp từ response.data.data
      const updatedOrderData = response.data.data || {}; // Đảm bảo không bị lỗi nếu data không tồn tại

      // Tìm và cập nhật trực tiếp đối tượng order trong mảng orders.value
      // để giao diện cập nhật ngay lập tức
      const orderIndex = orders.value.findIndex(o => o.id === order.id); // Dùng order.id của đối tượng gốc
      if (orderIndex !== -1) {
        // Cập nhật các thuộc tính cần thiết
        orders.value[orderIndex].status = updatedOrderData.status || newStatus; // Ưu tiên data từ backend
        // Lấy status_label mới từ backend nếu có, hoặc từ availableStatusOptions
        orders.value[orderIndex].status_label = updatedOrderData.status_label || availableStatusOptions.value.find(opt => opt.value === orders.value[orderIndex].status)?.label || orders.value[orderIndex].status;
        orders.value[orderIndex].originalStatus = orders.value[orderIndex].status; // Cập nhật originalStatus
      }

      alert(`Cập nhật trạng thái đơn hàng #${order.id} thành công!`);

      // Quan trọng: Tải lại số lượng đơn hàng cho các tab
      // Điều này đảm bảo số đếm trên các tab được cập nhật chính xác
      fetchCountsOnly();

    } else {
      // Backend báo thất bại (success: false)
      alert(`Cập nhật trạng thái đơn hàng #${order.id} thất bại: ` + (response.data.message || 'Lỗi không xác định từ server.'));
      order.status = oldStatus; // Khôi phục trạng thái cũ trên UI
    }
  } catch (error) {
    console.error("Lỗi khi cập nhật trạng thái đơn hàng:", error);
    // Xử lý lỗi Axios (Network Error, 4xx, 5xx)
    if (axios.isAxiosError(error)) { // Kiểm tra xem đây có phải lỗi Axios không
      if (error.response) {
        // Lỗi từ server (status code không phải 2xx)
        if (error.response.status === 401) {
          showAuthError('Phiên làm việc của bạn đã hết hạn hoặc không có quyền truy cập. Vui lòng đăng nhập lại.');
        } else if (error.response.data && error.response.data.message) {
          alert(`Cập nhật trạng thái đơn hàng #${order.id} thất bại: ` + error.response.data.message);
        } else {
          alert(`Cập nhật trạng thái đơn hàng #${order.id} thất bại: Lỗi ${error.response.status} từ server.`);
        }
      } else if (error.request) {
        // Yêu cầu đã được gửi nhưng không nhận được phản hồi (Network Error)
        alert("Không thể kết nối đến server. Vui lòng kiểm tra kết nối mạng hoặc server.");
      } else {
        // Lỗi khác khi thiết lập request
        alert("Lỗi khi gửi yêu cầu cập nhật trạng thái. Vui lòng thử lại.");
      }
    } else {
      // Lỗi không phải Axios
      alert("Đã xảy ra lỗi không mong muốn. Vui lòng thử lại.");
    }
    order.status = oldStatus; // Khôi phục trạng thái cũ trên UI trong mọi trường hợp lỗi
  } finally {
    order.isEditingStatus = false;
    order.isUpdatingStatus = false;
  }
}

function cancelEditStatus(order) {
  if (!order.isUpdatingStatus) { // Chỉ revert nếu không đang trong quá trình cập nhật
    order.status = order.originalStatus;
    order.isEditingStatus = false;
  }
}

// Hàm mới để chỉ fetch số lượng đơn hàng cho các tab
async function fetchCountsOnly() {
  try {
    const response = await axios.get('http://localhost:8000/api/admin/orders', { params: { page: 1, status: '' } }); // Gửi request chung để lấy tất cả counts
    if (response.data.counts) {
      orderTabs.value.forEach(tab => {
        tab.count = response.data.counts[tab.value] || 0;
      });
    }
  } catch (error) {
    console.error("Lỗi khi tải số lượng đơn hàng:", error);
  }
}

// ==============================================
// 5. CÁC HÀM TIỆN ÍCH KHÁC
// ==============================================

function formatCurrency(value) {
  const numericValue = parseFloat(value);
  if (isNaN(numericValue)) {
    console.warn("formatCurrency nhận giá trị không phải số:", value);
    return '0 VNĐ';
  }
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(numericValue);
}

function getStatusClass(status) {
  switch (status) {
    case 'pending':
      return 'status-pending';
    case 'processing':
      return 'status-processing';
    case 'shipped':
      return 'status-shipped';
    case 'completed':
      return 'status-completed';
    case 'cancelled':
      return 'status-cancelled';
    default:
      return '';
  }
}

function formatOrderCreatedAt(timestampString) {
  if (!timestampString) return 'N/A';

  const date = new Date(timestampString);
  const now = new Date();

  const diffMs = now.getTime() - date.getTime();
  const diffHours = diffMs / (1000 * 60 * 60);

  const formattedMinutes = String(date.getMinutes()).padStart(2, '0');
  const formattedHours = String(date.getHours()).padStart(2, '0');
  const formattedDay = String(date.getDate()).padStart(2, '0');
  const formattedMonth = String(date.getMonth() + 1).padStart(2, '0');

  const exactDateTime = `${formattedDay}/${formattedMonth}/${date.getFullYear()}, ${formattedHours}:${formattedMinutes}`;

  if (diffHours < 24) {
    return `${exactDateTime} (${Math.round(diffHours)} tiếng trước)`;
  } else {
    return exactDateTime;
  }
}

// ==============================================
// 6. WATCHERS & LIFECYCLE HOOKS
// ==============================================

watch(() => filters.value.search, (newValue, oldValue) => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  searchTimeout = setTimeout(() => {
    fetchOrders(1);
  }, 300);
});

onMounted(() => {
  fetchOrders(); // Khởi tạo lần đầu
});
</script>

<style scoped>
/* Các styles đã có (giữ nguyên) */
.order-list-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: sans-serif;
}

h2 {
  text-align: center;
  color: #333;
  margin-bottom: 25px;
}

.filters-and-search {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.order-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 10px;
}

.tab-button {
  padding: 10px 15px;
  border: 1px solid #ddd;
  background-color: #f0f0f0;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: bold;
  color: #555;
}

.tab-button:hover {
  background-color: #e0e0e0;
  border-color: #ccc;
}

.tab-button.active {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
  box-shadow: 0 2px 5px rgba(0, 123, 255, 0.2);
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-status select,
.search-box input[type="text"] {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.loading-indicator,
.no-orders {
  text-align: center;
  padding: 20px;
  color: #666;
}

.order-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  background-color: #fff;
}

.order-table th,
.order-table td {
  border: 1px solid #eee;
  padding: 12px 15px;
  text-align: left;
}

.order-table th {
  background-color: #f8f8f8;
  font-weight: bold;
  color: #333;
}

.order-table tbody tr:nth-child(even) {
  background-color: #f9f9f9;
}

.order-table tbody tr:hover {
  background-color: #f1f1f1;
}

.btn-view {
  padding: 6px 12px;
  background-color: #28a745;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.btn-view:hover {
  background-color: #218838;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
  gap: 10px;
}

.pagination button {
  padding: 8px 15px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.pagination button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
}

.pagination button:hover:not(:disabled) {
  background-color: #0056b3;
}

.pagination span {
  font-weight: bold;
  color: #333;
}

.status-cell {
  display: flex;
  align-items: center;
  gap: 5px;
}

/* Styles cho phần tử select trong cột trạng thái */
.status-cell select {
  padding: 5px 8px;
  /* Tăng padding để dễ nhìn */
  border: 1px solid #ccc;
  /* Viền màu xám nhạt */
  border-radius: 3px;
  /* Bo tròn góc */
  background-color: white;
  /* Nền trắng */
  font-size: 0.9em;
  /* Kích thước chữ nhỏ hơn một chút so với mặc định */
  min-width: 120px;
  /* Đảm bảo chiều rộng tối thiểu */
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.075);
  /* Đổ bóng nhẹ bên trong */
  appearance: none;
  /* Xóa style mặc định của trình duyệt cho select */
  -webkit-appearance: none;
  /* Dành cho WebKit browsers */
  -moz-appearance: none;
  /* Dành cho Firefox */
  background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="gray"><polygon points="0,0 10,0 5,10"/></svg>');
  /* Icon mũi tên tùy chỉnh */
  background-repeat: no-repeat;
  background-position: right 8px center;
  background-size: 8px;
}

.status-cell select:focus {
  border-color: #007bff;
  /* Viền xanh khi focus */
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  /* Đổ bóng focus */
  outline: none;
  /* Xóa outline mặc định */
}

/* Tùy chọn: Định kiểu cho các option bên trong select */
/* Lưu ý: Việc định kiểu option có thể bị giới hạn tùy thuộc vào trình duyệt */
.status-cell select option {
  padding: 8px 12px;
  background-color: white;
  color: #333;
}

.status-cell select option:checked {
  background-color: #007bff;
  /* Nền xanh khi chọn */
  color: white;
  /* Chữ trắng khi chọn */
}

/* Bạn cũng có thể áp dụng màu nền cho từng option dựa trên trạng thái, nếu muốn */
.status-cell select option[value="pending"] {
  background-color: #fff3cd;
  color: #856404;
}

.status-cell select option[value="processing"] {
  background-color: #d1ecf1;
  color: #0c5460;
}

.status-cell select option[value="shipped"] {
  background-color: #cce5ff;
  color: #004085;
}

.status-cell select option[value="completed"] {
  background-color: #d4edda;
  color: #155724;
}

.status-cell select option[value="cancelled"] {
  background-color: #f8d7da;
  color: #721c24;
}

.status-cell span {
  cursor: pointer;
  padding: 5px 8px;
  border-radius: 3px;
  display: inline-block;
}

.status-cell select {
  padding: 5px 8px;
  border: 1px solid #ccc;
  border-radius: 3px;
  background-color: white;
  font-size: 0.9em;
  min-width: 120px;
}

.status-spinner {
  animation: spin 1s linear infinite;
  display: inline-block;
  margin-left: 5px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.status-pending {
  background-color: #fff3cd;
  color: #856404;
  font-weight: bold;
  border: 1px solid #ffeeba;
}

.status-processing {
  background-color: #d1ecf1;
  color: #0c5460;
  font-weight: bold;
  border: 1px solid #bee5eb;
}

.status-shipped {
  background-color: #cce5ff;
  color: #004085;
  font-weight: bold;
  border: 1px solid #b8daff;
}

.status-completed {
  background-color: #d4edda;
  color: #155724;
  font-weight: bold;
  border: 1px solid #c3e6cb;
}

.status-cancelled {
  background-color: #f8d7da;
  color: #721c24;
  font-weight: bold;
  border: 1px solid #f5c6cb;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 30px;
  border-radius: 8px;
  width: 90%;
  max-width: 700px;
  position: relative;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.modal-content h3 {
  margin-top: 0;
  color: #333;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
  margin-bottom: 20px;
}

.modal-content p {
  margin-bottom: 10px;
  line-height: 1.6;
}

.modal-content h4 {
  margin-top: 20px;
  margin-bottom: 10px;
  color: #555;
}

.modal-content ul {
  list-style-type: disc;
  padding-left: 20px;
  margin-bottom: 10px;
}

.modal-content li {
  margin-bottom: 5px;
}

.close-button {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 24px;
  background: none;
  border: none;
  cursor: pointer;
  color: #666;
}

.close-button:hover {
  color: #333;
}
</style>