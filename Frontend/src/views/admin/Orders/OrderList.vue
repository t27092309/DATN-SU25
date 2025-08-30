<template>
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap items-center gap-4 mb-6">
            <div class="flex flex-row w-full items-center border-b border-gray-300 overflow-x-auto whitespace-nowrap">
                <button v-for="tab in orderTabs" :key="tab.value" @click="selectTab(tab.value)"
                    :class="['flex-grow px-4 py-3 text-center transition-all duration-300 ease-in-out',
                        filters.status === tab.value ? 'text-blue-600 border-b-2 border-blue-600 font-semibold' : 'text-gray-700 hover:text-blue-600']">
                    <span class="inline-block">{{ tab.label }}</span>
                    <span v-if="tab.count > 0" class="ml-2 font-bold">({{ tab.count }})</span>
                </button>
            </div>
            <div class="ml-auto flex items-center gap-3 w-full sm:w-auto">
                <label for="orderSearch" class="text-gray-700 font-medium whitespace-nowrap">Tìm kiếm</label>
                <input id="orderSearch" type="text" v-model="filters.search"
                    placeholder="Nhập ID, tên người dùng hoặc SĐT"
                    class="flex-grow px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
            </div>
        </div>

        <div v-if="loading" class="text-center py-10 text-lg text-gray-600">Đang tải đơn hàng...</div>
        <div v-else-if="!loading && orders && orders.length === 0"
            class="text-center py-10 text-lg text-gray-500 italic border border-dashed rounded-md">
            Không có đơn hàng nào.
        </div>
        <div v-else class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khách
                            hàng</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng
                            tiền</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày
                            tạo</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng
                            thái</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hành
                            động</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ order.id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ order.user ? order.user.name :
                            'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ order.total_price_formatted }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ order.display_created_at }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="flex items-center gap-2">
                                <span v-if="shouldDisableStatusChange(order.status)"
                                    :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusClass(order.status)]">
                                    {{ order.status_label || order.status }}
                                </span>
                                <span v-else-if="!order.isEditingStatus"
                                    :class="['px-2 py-1 rounded-full text-xs font-semibold cursor-pointer', getStatusClass(order.status)]"
                                    @click="startEditStatus(order)">
                                    {{ order.status_label || order.status }}
                                </span>
                                <select v-else v-model="order.status" @change="updateOrderStatus(order)"
                                    @blur="cancelEditStatus(order)" :disabled="order.isUpdatingStatus"
                                    class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    <option v-for="statusOpt in getStatusOptionsForOrder(order.originalStatus)"
                                        :key="statusOpt.value" :value="statusOpt.value">
                                        {{ statusOpt.label }}
                                    </option>
                                </select>
                                <span v-if="order.isUpdatingStatus" class="animate-spin text-blue-500 text-lg">🔄</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex gap-2 justify-end items-center">
                                <!-- <template v-if="order.status === 'return_requested'">
                                    <template v-if="order.return_request?.status === 'requested'">
                                        <button @click="approveReturn(order)"
                                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors duration-200">
                                            Duyệt trả hàng
                                        </button>
                                        <button @click="rejectReturn(order)"
                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors duration-200">
                                            Từ chối
                                        </button>
                                    </template>
<button v-if="order.return_request?.status === 'approved'" @click="markAsReturned(order)"
    class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md transition-colors duration-200">
    Đã nhận hàng hoàn
</button>

<button v-if="order.return_request?.status === 'returned'" @click="refundOrder(order)"
    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors duration-200">
    Hoàn tiền
</button>
</template> -->

                                <template
                                    v-if="['return_requested', 'return_approved', 'return_received', 'rejected'].includes(order.status)">
                                    <template v-if="order.return_request?.status === 'requested'">
                                        <button @click="approveReturn(order)"
                                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors duration-200">
                                            Duyệt trả hàng
                                        </button>
                                        <button @click="rejectReturn(order)"
                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors duration-200">
                                            Từ chối
                                        </button>
                                    </template>

                                    <template v-else-if="order.return_request?.status === 'rejected'">
                                        <span
                                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md inline-block font-semibold">
                                            Yêu cầu trả hàng đã bị từ chối
                                        </span>
                                    </template>

                                    <button v-if="order.return_request?.status === 'approved'"
                                        @click="markAsReturned(order)"
                                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-md transition-colors duration-200">
                                        Đã nhận hàng hoàn
                                    </button>

                                    <button v-if="order.return_request?.status === 'returned'"
                                        @click="refundOrder(order)"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors duration-200">
                                        Hoàn tiền
                                    </button>
                                </template>
                                <button @click="viewOrderDetails(order.id)"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Xem chi tiết
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="flex justify-center items-center gap-4 py-4 px-6 bg-gray-50 border-t border-gray-200">
                <button @click="fetchOrders(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    Trước
                </button>
                <span class="text-gray-700 font-semibold">Trang {{ pagination.current_page }} / {{ pagination.last_page
                }}</span>
                <button @click="fetchOrders(pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    Sau
                </button>
            </div>
        </div>

        <div v-if="showDetailsModal"
            class="fixed inset-0 bg-black bg-opacity-60 flex justify-center items-center z-[1000]"
            @click.self="closeDetailsModal">
            <div class="bg-white p-8 rounded-lg shadow-xl w-11/12 max-w-2xl relative max-h-[90vh] overflow-y-auto">
                <button class="absolute top-3 right-5 text-gray-500 hover:text-gray-800 text-3xl leading-none"
                    @click="closeDetailsModal">&times;</button>
                <h3 class="text-2xl font-semibold text-gray-800 mb-4 border-b pb-2">Chi tiết đơn hàng #{{
                    selectedOrder?.id }}
                </h3>

                <div v-if="loadingDetails" class="text-center py-5 text-gray-600">Đang tải chi tiết...</div>
                <div v-else-if="selectedOrder">
                    <p class="mb-2"><strong>Khách hàng:</strong> {{ selectedOrder.user ? selectedOrder.user.name : 'N/A'
                    }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ selectedOrder.user ? selectedOrder.user.email : 'N/A' }}
                    </p>
                    <h4 class="text-lg font-semibold text-gray-700 mb-2 mt-4">Trạng thái:</h4>
                    <div class="flex items-center gap-2 mb-4">
                        <span v-if="shouldDisableStatusChange(selectedOrder.status)"
                            :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusClass(selectedOrder.status)]">
                            {{ statusLabelMap[selectedOrder.status] || selectedOrder.status }}
                        </span>
                        <select v-else v-model="selectedOrder.status" @change="updateOrderStatusFromDetails"
                            :class="getStatusClass(selectedOrder.status)" class="p-1 rounded-md cursor-pointer">
                            <option v-for="status in getStatusOptionsForOrder(selectedOrder.originalStatus)"
                                :key="status.value" :value="status.value">
                                {{ status.label }}
                            </option>
                        </select>
                    </div>

                    <p class="mb-2"><strong>Tổng tiền:</strong> {{ selectedOrder.total_price_formatted }}</p>
                    <p class="mb-2"><strong>Phí vận chuyển:</strong> {{ formatCurrency(selectedOrder.shipping_fee) }}
                    </p>
                    <p class="mb-2"><strong>Ngày tạo:</strong> {{ selectedOrder.display_created_at }}</p>
                    <p class="mb-4"><strong>Ghi chú:</strong> {{ selectedOrder.notes || 'Không có' }}</p>
                    <h4 class="text-lg font-semibold text-gray-700 mb-2 mt-4">Địa chỉ giao hàng:</h4>
                    <p v-if="selectedOrder.address" class="ml-4 mb-4 text-gray-700">
                        <strong>Người nhận:</strong> {{ selectedOrder.address.recipient_name }}<br>
                        <strong>Điện thoại:</strong> {{ selectedOrder.address.phone_number }}<br>
                        <strong>Địa chỉ:</strong> {{ selectedOrder.address.address_line }}, {{
                            selectedOrder.address.ward }}, {{ selectedOrder.address.district }}, {{
                            selectedOrder.address.province }}
                    </p>
                    <p v-else class="ml-4 mb-4 text-gray-500 italic">Không có địa chỉ giao hàng.</p>

                    <h4 class="text-lg font-semibold text-gray-700 mb-2 mt-4">Sản phẩm:</h4>
                    <ul v-if="selectedOrder.orderItems && selectedOrder.orderItems.length" class="list-disc pl-8 mb-4">
                        <li v-for="item in selectedOrder.orderItems" :key="item.id" class="mb-1 text-gray-700">
                            {{ item.product_name || item.variant_name || 'Sản phẩm không xác định' }} ({{ item.quantity
                            }} x {{
                                formatCurrency(item.price_each) }})
                        </li>
                    </ul>
                    <p v-else class="ml-4 mb-4 text-gray-500 italic">Không có sản phẩm trong đơn hàng.</p>

                    <h4 class="text-lg font-semibold text-gray-700 mb-2 mt-4">Thanh toán:</h4>
                    <ul v-if="selectedOrder.payments && selectedOrder.payments.length" class="list-disc pl-8">
                        <li v-for="payment in selectedOrder.payments" :key="payment.id" class="mb-1 text-gray-700">
                            {{ formatCurrency(payment.amount) }} - {{ payment.payment_method }} (Trạng thái: {{
                                payment.status }})
                            <span v-if="payment.paid_at" class="text-gray-600"> - Ngày thanh toán: {{
                                formatOrderCreatedAt(payment.paid_at) }}</span>
                        </li>
                    </ul>
                    <p v-else class="ml-4 text-gray-500 italic">Chưa có thanh toán nào.</p>

                    <div v-if="selectedOrder.return_request">
                        <h4 class="text-lg font-semibold text-red-700 mb-2 mt-4 border-t pt-4">Thông tin hoàn trả:</h4>
                        <div class="ml-4 p-4 bg-red-50 rounded-md border border-red-200">
                            <p class="mb-2">
                                <strong>Trạng thái:</strong>
                                <span
                                    :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusClass(selectedOrder.return_request.status)]">
                                    {{ statusLabelMap[selectedOrder.return_request.status] ||
                                        selectedOrder.return_request.status }}
                                </span>
                            </p>
                            <p v-if="selectedOrder.return_request.processed_by" class="mb-2">
                                <strong>Người duyệt:</strong> {{ selectedOrder.return_request.processor_name ||
                                    'Đang cập nhật' }}
                            </p>
                            <p class="mb-2">
                                <strong>Lý do hoàn hàng:</strong> {{ selectedOrder.return_request.reason || 'Không có'
                                }}
                            </p>
                            <p class="mb-2">
                                <strong>Ghi chú (nếu có):</strong> {{ selectedOrder.return_request.notes || 'Không có'
                                }}
                            </p>
                            <p class="mb-2">
                                <strong>Ngày yêu cầu:</strong> {{
                                    formatOrderCreatedAt(selectedOrder.return_request.created_at)
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import moment from 'moment';

const orders = ref([]);
const pagination = ref({});
const loading = ref(false);
const loadingDetails = ref(false);
const showDetailsModal = ref(false);
const selectedOrder = ref(null);
const filters = ref({
    status: 'all',
    search: ''
});

const orderTabs = ref([
    { label: 'Tất cả', value: 'all', count: 0 },
    { label: 'Chờ xác nhận', value: 'pending', count: 0 },
    { label: 'Đang xử lý', value: 'processing', count: 0 },
    { label: 'Đang giao hàng', value: 'shipped', count: 0 },
    { label: 'Đã giao hàng', value: 'delivered', count: 0 },
    { label: 'Đã hủy', value: 'cancelled', count: 0 },
    { label: 'Yêu cầu trả hàng', value: 'return_requested', count: 0 },
    { label: 'Đã hoàn tiền', value: 'refunded', count: 0 },
]);

const statusFlow = {
    pending: 'processing',
    processing: 'shipped',
    shipped: 'delivered',
};

const availableStatusOptions = ref([
    { label: 'Chờ xác nhận', value: 'pending' },
    { label: 'Đang xử lý', value: 'processing' },
    { label: 'Đang giao hàng', value: 'shipped' },
    { label: 'Đã giao hàng', value: 'delivered' },
    { label: 'Đã hủy', value: 'cancelled' },
    { label: 'Yêu cầu trả hàng', value: 'return_requested' },
    { label: 'Đã hoàn tiền', value: 'refunded' },
]);

const statusLabelMap = {
    pending: 'Chờ xác nhận',
    processing: 'Đang xử lý',
    shipped: 'Đang giao hàng',
    delivered: 'Đã giao hàng',
    cancelled: 'Đã hủy',
    return_requested: 'Yêu cầu trả hàng',
    refunded: 'Đã hoàn tiền',
    requested: 'Đang yêu cầu',
    approved: 'Đã duyệt',
    returned: 'Đã nhận hàng hoàn',
    rejected: 'Đã từ chối',
};

const finalOrReturnStatuses = [
    'delivered',
    'cancelled',
    'return_requested',
    'refunded',
];

const shouldDisableStatusChange = (status) => {
    return finalOrReturnStatuses.includes(status);
};

let searchTimeout = null;

const showAuthError = (message) => {
    Swal.fire({
        icon: 'error',
        title: 'Lỗi xác thực!',
        text: message,
        confirmButtonText: 'Đăng nhập lại'
    });
};

async function fetchOrders(page = 1) {
    loading.value = true;
    try {
        const params = {
            page: page,
            status: filters.value.status === 'all' ? '' : filters.value.status,
            search: filters.value.search
        };
        const response = await axios.get('http://localhost:8000/api/admin/orders', { params });
        orders.value = response.data.data.map(order => {
            return {
                ...order,
                total_price: parseFloat(order.total_price) || 0,
                shipping_fee: parseFloat(order.shipping_fee) || 0,
                total_price_formatted: formatCurrency(parseFloat(order.total_price) || 0),
                display_created_at: formatOrderCreatedAt(order.created_at),
                isEditingStatus: false,
                isUpdatingStatus: false,
                originalStatus: order.status,
                status_label: statusLabelMap[order.status] || order.status
            };
        });
        pagination.value = response.data.meta;
        if (response.data.counts) {
            const counts = response.data.counts;
            orderTabs.value.forEach(tab => {
                tab.count = counts[tab.value] || 0;
            });
            orderTabs.value.find(tab => tab.value === 'all').count = counts.all || 0;
        }
    } catch (error) {
        console.error("Lỗi khi tải đơn hàng:", error);
        if (error.response && error.response.status === 401) {
            showAuthError('Phiên làm việc của bạn đã hết hạn hoặc không có quyền truy cập. Vui lòng đăng nhập lại.');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: "Không thể tải danh sách đơn hàng. Vui lòng thử lại.",
            });
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
        selectedOrder.value = {
            ...orderData,
            total_price: parseFloat(orderData.total_price) || 0,
            shipping_fee: parseFloat(orderData.shipping_fee) || 0,
            total_price_formatted: formatCurrency(parseFloat(orderData.total_price) || 0),
            display_created_at: formatOrderCreatedAt(orderData.created_at),
            orderItems: orderData.items ? orderData.items.map(item => ({
                ...item,
                price_each: parseFloat(item.price_each) || 0
            })) : [],
            payments: orderData.payments ? orderData.payments.map(payment => ({
                ...payment,
                amount: parseFloat(payment.amount) || 0,
                status_label: payment.status
            })) : [],
            status_label: statusLabelMap[orderData.status] || orderData.status,
            originalStatus: orderData.status,
            return_request: orderData.return_request || null
        };
    } catch (error) {
        console.error("Lỗi khi tải chi tiết đơn hàng:", error);
        if (error.response && error.response.status === 401) {
            showAuthError('Phiên làm việc của bạn đã hết hạn hoặc không có quyền truy cập. Vui lòng đăng nhập lại.');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: "Không thể tải chi tiết đơn hàng này.",
            });
        }
        closeDetailsModal();
    } finally {
        loadingDetails.value = false;
    }
}

function closeDetailsModal() {
    showDetailsModal.value = false;
    selectedOrder.value = null;
    fetchOrders(pagination.value.current_page);
}

function selectTab(statusValue) {
    filters.value.status = statusValue;
    fetchOrders(1);
}

function getStatusOptionsForOrder(currentStatus) {
    const options = [availableStatusOptions.value.find(opt => opt.value === currentStatus)].filter(Boolean);
    if (statusFlow[currentStatus]) {
        options.push(availableStatusOptions.value.find(opt => opt.value === statusFlow[currentStatus]));
    }
    if (currentStatus !== 'delivered' && currentStatus !== 'cancelled' && currentStatus !== 'return_requested' && currentStatus !== 'refunded') {
        options.push(availableStatusOptions.value.find(opt => opt.value === 'cancelled'));
    }
    if (currentStatus === 'return_requested') {
        const refundedStatus = availableStatusOptions.value.find(opt => opt.value === 'refunded');
        const cancelledStatus = availableStatusOptions.value.find(opt => opt.value === 'cancelled');
        if (refundedStatus && !options.some(opt => opt.value === 'refunded')) {
            options.push(refundedStatus);
        }
        if (cancelledStatus && !options.some(opt => opt.value === 'cancelled')) {
            options.push(cancelledStatus);
        }
    }
    return options.filter(Boolean);
}

function startEditStatus(order) {
    if (shouldDisableStatusChange(order.status)) {
        Swal.fire({
            icon: 'warning',
            title: 'Không thể thay đổi!',
            text: 'Trạng thái đơn hàng này không thể thay đổi được nữa.',
            confirmButtonText: 'Đã hiểu'
        });
        return;
    }
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
    const isFlowValid = (newStatus === statusFlow[oldStatus]) ||
        (newStatus === 'cancelled' && oldStatus !== 'delivered' && oldStatus !== 'shipped' && oldStatus !== 'return_requested') ||
        (newStatus === 'refunded' && oldStatus === 'return_requested');
    if (!isFlowValid) {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: `Bạn không thể chuyển trạng thái từ "${statusLabelMap[oldStatus]}" sang "${statusLabelMap[newStatus]}". Vui lòng chọn trạng thái tiếp theo hợp lệ.`,
        });
        order.status = oldStatus;
        order.isEditingStatus = false;
        return;
    }
    const result = await Swal.fire({
        title: 'Xác nhận thay đổi trạng thái?',
        html: `Bạn có chắc chắn muốn thay đổi trạng thái đơn hàng **#${order.id}** từ "<span class="font-bold">${statusLabelMap[oldStatus]}</span>" sang "<span class="font-bold">${statusLabelMap[newStatus]}</span>"?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, thay đổi!',
        cancelButtonText: 'Hủy bỏ'
    });
    if (!result.isConfirmed) {
        order.status = oldStatus;
        order.isEditingStatus = false;
        return;
    }
    order.isUpdatingStatus = true;
    try {
        const response = await axios.patch(`http://localhost:8000/api/admin/orders/${order.id}/status`, {
            status: newStatus
        });
        if (response.data.success) {
            const updatedOrderData = response.data.data || {};
            const orderIndex = orders.value.findIndex(o => o.id === order.id);
            if (orderIndex !== -1) {
                orders.value[orderIndex].status = updatedOrderData.status || newStatus;
                orders.value[orderIndex].status_label = statusLabelMap[orders.value[orderIndex].status] || orders.value[orderIndex].status;
                orders.value[orderIndex].originalStatus = orders.value[orderIndex].status;
            }
            Swal.fire({
                icon: 'success',
                title: 'Thành công!',
                text: `Cập nhật trạng thái đơn hàng #${order.id} thành công!`,
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false,
            });
            fetchCountsOnly();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Thất bại!',
                text: `Cập nhật trạng thái đơn hàng #${order.id} thất bại: ` + (response.data.message || 'Lỗi không xác định từ server.'),
            });
            order.status = oldStatus;
        }
    } catch (error) {
        console.error("Lỗi khi cập nhật trạng thái đơn hàng:", error);
        if (axios.isAxiosError(error)) {
            if (error.response) {
                if (error.response.status === 401) {
                    showAuthError('Phiên làm việc của bạn đã hết hạn hoặc không có quyền truy cập. Vui lòng đăng nhập lại.');
                } else if (error.response.data && error.response.data.message) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: `Cập nhật trạng thái đơn hàng #${order.id} thất bại: ` + error.response.data.message,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: `Cập nhật trạng thái đơn hàng #${order.id} thất bại: Lỗi ${error.response.status} từ server.`,
                    });
                }
            } else if (error.request) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: "Không thể kết nối đến server. Vui lòng kiểm tra kết nối mạng hoặc server.",
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: "Đã xảy ra lỗi không mong muốn. Vui lòng thử lại.",
                });
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: "Đã xảy ra lỗi không mong muốn. Vui lòng thử lại.",
            });
        }
        order.status = oldStatus;
    } finally {
        order.isEditingStatus = false;
        order.isUpdatingStatus = false;
    }
}

async function updateOrderStatusFromDetails() {
    await updateOrderStatus(selectedOrder.value);
}

function cancelEditStatus(order) {
    if (!order.isUpdatingStatus) {
        order.status = order.originalStatus;
        order.isEditingStatus = false;
    }
}

async function fetchCountsOnly() {
    try {
        const response = await axios.get('http://localhost:8000/api/admin/orders', { params: { page: 1, status: '', per_page: 1 } });
        if (response.data.counts) {
            const counts = response.data.counts;
            const tabValues = orderTabs.value.map(tab => tab.value);
            for (const key in counts) {
                if (tabValues.includes(key)) {
                    const tab = orderTabs.value.find(tab => tab.value === key);
                    if (tab) {
                        tab.count = counts[key] || 0;
                    }
                }
            }
        }
    } catch (error) {
        console.error("Lỗi khi tải số lượng đơn hàng:", error);
    }
}

async function approveReturn(order) {
    const result = await Swal.fire({
        title: 'Duyệt yêu cầu hoàn trả?',
        text: `Bạn có chắc chắn muốn duyệt yêu cầu hoàn trả cho đơn hàng #${order.id} này không?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, duyệt!',
        cancelButtonText: 'Hủy bỏ'
    });

    if (result.isConfirmed) {
        try {
            const response = await axios.post(`http://localhost:8000/api/admin/orders/${order.id}/returns/approve`);
            Swal.fire('Thành công!', response.data.message, 'success');
            Object.assign(order, response.data.data.order);
            order.status_label = statusLabelMap[order.status] || order.status;
            order.originalStatus = order.status;
        } catch (error) {
            console.error("Lỗi khi duyệt yêu cầu:", error);
            const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi duyệt yêu cầu.';
            Swal.fire('Lỗi!', errorMessage, 'error');
        }
    }
}

async function rejectReturn(order) {
    const { value: notes } = await Swal.fire({
        title: 'Từ chối yêu cầu hoàn trả?',
        html: `Bạn có chắc chắn muốn **từ chối** yêu cầu hoàn trả cho đơn hàng #${order.id} này không? <br>
                Vui lòng nhập lý do từ chối (Không bắt buộc):`,
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Lý do từ chối...',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, từ chối!',
        cancelButtonText: 'Hủy bỏ',
        reverseButtons: true
    });

    if (notes !== undefined) {
        try {
            //     const response = await axios.post(`http://localhost:8000/api/admin/orders/${order.id}/returns/reject`, { notes });
            //     Swal.fire('Thành công!', response.data.message, 'success');
            //     Object.assign(order, response.data.data.order);

            //      const updatedOrder = response.data.data.order;
            //     updatedOrder.return_request = updatedOrder.returnRequest;

            //     // Cập nhật reactive order
            //     // Object.assign(order, updatedOrder);

            //     delete updatedOrder.returnRequest;

            // // Cập nhật reactive order đúng cách
            // for (const key in updatedOrder) {
            //     // Gán từng thuộc tính, đảm bảo Vue detect được thay đổi
            //     order[key] = updatedOrder[key];
            // }

            //     order.status_label = statusLabelMap[order.status] || order.status;
            //     order.originalStatus = order.status;
            const response = await axios.post(`http://localhost:8000/api/admin/orders/${order.id}/returns/reject`, { notes });

            Swal.fire('Thành công!', response.data.message, 'success');

            // Lấy dữ liệu đơn hàng đã cập nhật từ phản hồi
            const updatedOrderData = response.data.data.order;

            // Đảm bảo thuộc tính 'return_request' tồn tại và có giá trị
            // Sử dụng updatedOrderData.returnRequest từ API để gán cho return_request
            updatedOrderData.return_request = updatedOrderData.returnRequest;

            // Xóa thuộc tính 'returnRequest' cũ nếu có để tránh trùng lặp
            delete updatedOrderData.returnRequest;

            // Cập nhật tất cả các thuộc tính của đối tượng `order` bằng dữ liệu mới.
            // Vue sẽ nhận biết được sự thay đổi này và tự động cập nhật giao diện.
            Object.assign(order, updatedOrderData);

            // Cập nhật lại các thuộc tính bổ sung nếu cần
            order.status_label = statusLabelMap[order.status] || order.status;
            order.originalStatus = order.status;
        } catch (error) {
            console.error("Lỗi khi từ chối yêu cầu:", error);
            const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi từ chối yêu cầu.';
            Swal.fire('Lỗi!', errorMessage, 'error');
        }
    }
}

async function markAsReturned(order) {
    const result = await Swal.fire({
        title: 'Xác nhận đã nhận hàng hoàn trả?',
        text: `Bạn có chắc chắn đã nhận được hàng từ đơn hàng #${order.id} và muốn cập nhật tồn kho không?`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, đã nhận!',
        cancelButtonText: 'Hủy bỏ'
    });

    if (result.isConfirmed) {
        try {
            const response = await axios.post(`http://localhost:8000/api/admin/orders/${order.id}/returns/received`);
            Swal.fire('Thành công!', response.data.message, 'success');
            Object.assign(order, response.data.data.order);
            order.status_label = statusLabelMap[order.status] || order.status;
            order.originalStatus = order.status;
        } catch (error) {
            console.error("Lỗi khi xác nhận nhận hàng:", error);
            const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi xác nhận nhận hàng.';
            Swal.fire('Lỗi!', errorMessage, 'error');
        }
    }
}

async function refundOrder(order) {
    const result = await Swal.fire({
        title: 'Xác nhận hoàn tiền?',
        text: `Bạn có chắc chắn muốn hoàn tiền cho đơn hàng #${order.id} này không? Thao tác này không thể hoàn tác.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, hoàn tiền!',
        cancelButtonText: 'Hủy bỏ'
    });

    if (result.isConfirmed) {
        try {
            const response = await axios.post(`http://localhost:8000/api/admin/orders/${order.id}/returns/refund`);
            Swal.fire('Thành công!', response.data.message, 'success');
            Object.assign(order, response.data.data.order);
            order.status_label = statusLabelMap[order.status] || order.status;
            order.originalStatus = order.status;
        } catch (error) {
            console.error("Lỗi khi hoàn tiền:", error);
            const errorMessage = error.response?.data?.message || 'Có lỗi xảy ra khi hoàn tiền.';
            Swal.fire('Lỗi!', errorMessage, 'error');
        }
    }
}

function formatCurrency(value) {
    const numericValue = parseFloat(value);
    if (isNaN(numericValue)) {
        return '0 VNĐ';
    }
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(numericValue);
}

function getStatusClass(status) {
    switch (status) {
        case 'pending':
        case 'requested':
            return 'bg-yellow-100 text-yellow-800';
        case 'processing':
            return 'bg-blue-100 text-blue-800';
        case 'shipped':
            return 'bg-indigo-100 text-indigo-800';
        case 'delivered':
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'return_requested':
            return 'bg-purple-100 text-purple-800';
        case 'refunded':
        case 'returned':
            return 'bg-pink-100 text-pink-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function formatOrderCreatedAt(timestampString) {
    if (!timestampString) return 'N/A';
    return moment(timestampString).format('HH:mm DD/MM/YYYY');
}

watch(() => filters.value.search, (newValue, oldValue) => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        fetchOrders(1);
    }, 300);
});

onMounted(() => {
    fetchOrders();
});
</script>

<style scoped>
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
}
</style>