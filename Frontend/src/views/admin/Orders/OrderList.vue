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
                                <span v-if="!order.isEditingStatus"
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
                            <button @click="viewOrderDetails(order.id)"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Xem chi tiết
                            </button>
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
                    <p class="mb-2"><strong>Trạng thái:</strong> <span
                            :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusClass(selectedOrder.status)]">{{
                                selectedOrder.status_label || selectedOrder.status }}</span></p>
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
                            selectedOrder.address.ward }}, {{
                            selectedOrder.address.district }}, {{ selectedOrder.address.province }}
                    </p>
                    <p v-else class="ml-4 mb-4 text-gray-500 italic">Không có địa chỉ giao hàng.</p>

                    <h4 class="text-lg font-semibold text-gray-700 mb-2 mt-4">Sản phẩm:</h4>
                    <ul v-if="selectedOrder.items && selectedOrder.items.length" class="list-disc pl-8 mb-4">
                        <li v-for="item in selectedOrder.items" :key="item.id" class="mb-1 text-gray-700">
                            {{ item.variant_name || 'Sản phẩm không xác định' }} ({{ item.quantity }} x {{
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
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

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
    { label: 'Trả hàng/Hoàn tiền', value: 'refund', count: 0 },
]);

// Định nghĩa luồng chuyển trạng thái hợp lệ, loại bỏ 'delivered' là một lựa chọn tiếp theo.
const statusFlow = {
    pending: 'processing',
    processing: 'shipped',
    shipped: null // Không có trạng thái tiếp theo sau 'shipped'
};

const availableStatusOptions = ref([
    { label: 'Chờ xác nhận', value: 'pending' },
    { label: 'Đang xử lý', value: 'processing' },
    { label: 'Đang giao hàng', value: 'shipped' },
    { label: 'Đã giao hàng', value: 'delivered' },
    { label: 'Đã hủy', value: 'cancelled' },
    { label: 'Trả hàng/Hoàn tiền', value: 'refund', count: 0 },
]);

const statusLabelMap = availableStatusOptions.value.reduce((map, status) => {
    map[status.value] = status.label;
    return map;
}, {});

let searchTimeout = null;

// ==============================================
// 2. LOGIC CHUNG (Xử lý lỗi xác thực)
// ==============================================

const showAuthError = (message) => {
    Swal.fire({
        icon: 'error',
        title: 'Lỗi xác thực!',
        text: message,
        confirmButtonText: 'Đăng nhập lại'
    });
};

// ==============================================
// 3. LOGIC CHO DANH SÁCH ĐƠN HÀNG VÀ CHI TIẾT
// ==============================================

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
                status_label: statusLabelMap[order.status] || order.status
            };
        });
        pagination.value = response.data.meta;

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
            status_label: statusLabelMap[orderData.status] || orderData.status
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
}

// ==============================================
// 4. LOGIC CHO TABS VÀ CẬP NHẬT TRẠNG THÁI
// ==============================================

function selectTab(statusValue) {
    filters.value.status = statusValue;
    fetchOrders(1);
}

function getStatusOptionsForOrder(currentStatus) {
    const nextStatus = statusFlow[currentStatus];

    // Chỉ cho phép chỉnh sửa nếu trạng thái hiện tại KHÔNG phải là 'shipped', 'delivered', hoặc 'cancelled'
    if (currentStatus === 'delivered' || currentStatus === 'cancelled') {
        return [];
    }

    const options = [];
    if (nextStatus) {
        options.push(availableStatusOptions.value.find(opt => opt.value === nextStatus));
    }

    options.unshift(availableStatusOptions.value.find(opt => opt.value === currentStatus));

    // Thêm trạng thái "Đã hủy" vào danh sách tùy chọn, trừ khi đơn hàng đã giao hoặc đã hủy
    if (currentStatus !== 'delivered' && currentStatus !== 'cancelled') {
        options.push(availableStatusOptions.value.find(opt => opt.value === 'cancelled'));
    }

    return options.filter(Boolean);
}

function startEditStatus(order) {
    // Ngăn chặn chỉnh sửa nếu trạng thái đã là 'delivered' hoặc 'cancelled'
    if (order.status === 'delivered' || order.status === 'cancelled') {
        Swal.fire({
            icon: 'warning',
            title: 'Không thể thay đổi!',
            text: 'Đơn hàng đã không thể thay đổi trạng thái.',
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

    const nextValidStatus = statusFlow[oldStatus];
    // Thay đổi ở đây: không cho phép chuyển trạng thái thành 'delivered' hoặc một trạng thái không hợp lệ khác
    if (newStatus !== nextValidStatus && newStatus !== 'cancelled') {
        Swal.fire({
            icon: 'error',
            title: 'Lỗi!',
            text: `Bạn không thể chuyển trạng thái từ "${statusLabelMap[oldStatus]}" sang "${statusLabelMap[newStatus]}". Vui lòng chọn trạng thái tiếp theo hợp lệ.`,
        });
        order.status = oldStatus;
        order.isEditingStatus = false;
        return;
    }

    // Thêm xác thực để chặn chuyển sang 'delivered'
    if (newStatus === 'delivered') {
        Swal.fire({
            icon: 'error',
            title: 'Thất bại!',
            text: `Bạn không có quyền chuyển trạng thái đơn hàng #${order.id} sang "Đã giao hàng".`,
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
            return 'bg-yellow-100 text-yellow-800';
        case 'processing':
            return 'bg-blue-100 text-blue-800';
        case 'shipped':
            return 'bg-indigo-100 text-indigo-800';
        case 'delivered':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
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
        const diffMinutes = Math.round(diffMs / (1000 * 60));
        if (diffMinutes < 60) {
            return `${exactDateTime} (${diffMinutes} phút trước)`;
        }
        const roundedHours = Math.round(diffHours);
        return `${exactDateTime} (${roundedHours} tiếng trước)`;
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
    fetchOrders();
});
</script>

<style scoped>
select {
    -webkit-appearance: none;
    /* Chrome, Safari, Edge */
    -moz-appearance: none;
    /* Firefox */
    appearance: none;
    /* Standard */
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1rem;
}
</style>