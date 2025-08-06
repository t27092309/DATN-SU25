<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <div class="mb-6">
        <ul class="flex items-center space-x-2 text-gray-600 text-sm">
          <li class="nav-home">
            <router-link :to="{ name: 'AdminDashboard' }" class="hover:text-blue-600">
              <i class="fas fa-home"></i>
            </router-link>
          </li>
          <li class="separator">
            <i class="fas fa-chevron-right text-xs"></i>
          </li>
          <li class="nav-item">
            <span class="font-semibold">{{ $route.meta.title }}</span>
          </li>
        </ul>
      </div>

      <div class="flex flex-wrap -mx-4">
        <div class="w-full px-4">
          <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-2xl font-semibold">Quản lý mã giảm giá</h1>
              <div class="flex gap-2">
                <router-link :to="{ name: 'CouponTrash' }"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  <i class="fas fa-trash mr-2"></i> Thùng rác
                </router-link>
              </div>
            </div>

            <div class="card-body">
              <div class="flex flex-wrap -mx-4">
                <div class="w-full px-4 mb-8">
                  <div class="overflow-x-auto">
                    <table id="add-row" class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã
                          </th>
                          <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại
                          </th>
                          <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá
                            trị
                          </th>
                          <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hết
                            hạn</th>
                          <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng
                            thái</th>
                          <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            style="width: 15%">Hành động</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                    <p v-if="listMessage" :class="listMessageClass" class="mt-4 text-sm">{{ listMessage }}</p>
                  </div>
                </div>

                <div class="w-full px-4 mx-auto">
                  <form @submit.prevent="isEditing ? updateCoupon() : addCoupon()"
                    class="mb-8 p-6 bg-gray-50 rounded-lg shadow-inner">
                    <div class="mb-4">
                      <h5 class="text-xl font-semibold text-gray-800 mb-4">
                        {{ isEditing ? 'Chỉnh sửa mã giảm giá' : 'Thêm mới mã giảm giá' }}
                      </h5>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                      <div>
                        <div class="form-group-item">
                          <label for="code" class="block text-gray-700 text-sm font-bold mb-2">Mã giảm giá</label>
                          <input type="text" v-model="coupon.code"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="code" placeholder="Nhập mã giảm giá" required />
                          <small class="text-gray-500 text-xs mt-1">Ví dụ: SALE2025</small>
                        </div>
                        <div class="form-group-item">
                          <label for="discount_type" class="block text-gray-700 text-sm font-bold mb-2">Loại giảm
                            giá</label>
                          <select v-model="coupon.discount_type"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="discount_type" required>
                            <option value="" disabled>Chọn loại giảm giá</option>
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Cố định (VNĐ)</option>
                          </select>
                          <small class="text-gray-500 text-xs mt-1">Chọn loại giảm giá: Phần trăm hoặc cố định.</small>
                        </div>
                        <div class="form-group-item">
                          <label for="discount_value" class="block text-gray-700 text-sm font-bold mb-2">Giá trị
                            giảm</label>
                          <input type="number" v-model="coupon.discount_value"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="discount_value" placeholder="Nhập giá trị giảm" required />
                          <small class="text-gray-500 text-xs mt-1">Ví dụ: 20 (cho 20%) hoặc 100000 (cho 100,000
                            VNĐ)</small>
                        </div>
                        <div class="form-group-item">
                          <label for="expires_at" class="block text-gray-700 text-sm font-bold mb-2">Ngày hết
                            hạn</label>
                          <input type="datetime-local" v-model="coupon.expires_at"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="expires_at" />
                          <small class="text-gray-500 text-xs mt-1">Để trống nếu không có ngày hết hạn</small>
                        </div>
                      </div>

                      <div>
                        <div class="form-group-item">
                          <label for="usage_limit" class="block text-gray-700 text-sm font-bold mb-2">Giới hạn sử dụng
                            (Tổng)</label>
                          <input type="number" v-model="coupon.usage_limit"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="usage_limit" placeholder="Số lần sử dụng tối đa" />
                          <small class="text-gray-500 text-xs mt-1">Để trống nếu không giới hạn</small>
                        </div>
                        <div class="form-group-item">
                          <label for="per_user_limit" class="block text-gray-700 text-sm font-bold mb-2">Giới hạn mỗi
                            người dùng</label>
                          <input type="number" v-model="coupon.per_user_limit"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="per_user_limit" placeholder="Số lần sử dụng mỗi người" required />
                          <small class="text-gray-500 text-xs mt-1">Số lần mã có thể được dùng bởi một người (mặc định
                            là 1)</small>
                        </div>
                        <div class="form-group-item">
                          <label for="min_order_amount" class="block text-gray-700 text-sm font-bold mb-2">Đơn hàng tối
                            thiểu (VNĐ)</label>
                          <input type="number" step="0.01" v-model="coupon.min_order_amount"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="min_order_amount" placeholder="Giá trị đơn hàng tối thiểu" />
                          <small class="text-gray-500 text-xs mt-1">Để trống nếu không yêu cầu giá trị đơn hàng tối
                            thiểu</small>
                        </div>
                        <div class="form-group-item">
                          <label for="max_discount" class="block text-gray-700 text-sm font-bold mb-2">Giảm tối đa
                            (VNĐ)</label>
                          <input type="number" step="0.01" v-model="coupon.max_discount"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="max_discount" placeholder="Giá trị giảm tối đa" />
                          <small class="text-gray-500 text-xs mt-1">Chỉ áp dụng cho giảm giá phần trăm. Để trống nếu
                            không giới hạn.</small>
                        </div>
                        <div class="form-group-item" v-if="isEditing">
                          <label for="used_count" class="block text-gray-700 text-sm font-bold mb-2">Số lần đã
                            dùng</label>
                          <input type="number" v-model="coupon.used_count"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 bg-gray-100 cursor-not-allowed leading-tight focus:outline-none focus:shadow-outline"
                            id="used_count" readonly />
                          <small class="text-gray-500 text-xs mt-1">Số lần mã đã được sử dụng (không thể sửa thủ
                            công)</small>
                        </div>
                        <div class="form-group-item form-group-checkbox">
                          <label for="is_active" class="block text-gray-700 text-sm font-bold mb-2">Trạng thái</label>
                          <div class="flex items-center">
                            <input type="checkbox" v-model="coupon.is_active"
                              class="form-checkbox h-5 w-5 text-blue-600" id="is_active" />
                            <span class="ml-2 text-gray-700">{{ coupon.is_active ? 'Hoạt động' : 'Không hoạt động'
                              }}</span>
                          </div>
                          <small class="text-gray-500 text-xs mt-1">Chuyển đổi trạng thái hoạt động của mã.</small>
                        </div>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2 mt-4">
                      <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ isEditing ? 'Cập nhật mã giảm giá' : 'Thêm mã giảm giá' }}
                      </button>
                      <button v-if="isEditing" @click="cancelEdit" type="button"
                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Hủy
                      </button>
                    </div>
                    <p v-if="addMessage" :class="addMessageClass" class="mt-4 text-sm">{{ addMessage }}</p>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showDetailModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50 p-4"
      @click.self="closeDetailModal">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-auto p-6">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
          <h2 class="text-2xl font-semibold text-gray-800">Chi tiết mã giảm giá</h2>
          <button @click="closeDetailModal" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        <div v-if="viewingCoupon" class="space-y-3 text-gray-700">
          <p><strong>Mã:</strong> {{ viewingCoupon.code }}</p>
          <p><strong>Loại giảm giá:</strong> {{ viewingCoupon.discount_type === 'percent' ? 'Phần trăm' : 'Cố định' }}
          </p>
          <p><strong>Giá trị giảm:</strong> {{ viewingCoupon.discount_value }} {{ viewingCoupon.discount_type ===
            'percent' ? '%' : 'VNĐ' }}</p>
          <p><strong>Ngày hết hạn:</strong> {{ viewingCoupon.expires_at ? new
            Date(viewingCoupon.expires_at).toLocaleString('vi-VN') : 'Không có' }}</p>
          <p><strong>Giới hạn sử dụng (Tổng):</strong> {{ viewingCoupon.usage_limit !== null ? viewingCoupon.usage_limit
            : 'Không giới hạn' }}</p>
          <p><strong>Giới hạn mỗi người dùng:</strong> {{ viewingCoupon.per_user_limit }}</p>
          <p><strong>Đã dùng:</strong> {{ viewingCoupon.used_count }}</p>
          <p><strong>Đơn hàng tối thiểu:</strong> {{ viewingCoupon.min_order_amount !== null ?
            formatCurrency(viewingCoupon.min_order_amount) : 'Không yêu cầu' }}</p>
          <p><strong>Giảm tối đa:</strong> {{ viewingCoupon.max_discount !== null ?
            formatCurrency(viewingCoupon.max_discount) : 'Không giới hạn' }}</p>
          <p>
            <strong>Trạng thái:</strong>
            <span
              :class="{ 'bg-green-100 text-green-800': viewingCoupon.is_active, 'bg-red-100 text-red-800': !viewingCoupon.is_active }"
              class="px-2 py-1 rounded-full text-sm font-semibold">
              {{ viewingCoupon.is_active ? 'Hoạt động' : 'Không hoạt động' }}
            </span>
          </p>
        </div>
        <div class="mt-6 flex justify-end">
          <button @click="closeDetailModal"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Đóng
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, nextTick, ref, watch } from "vue";
import axios from "axios";
import slugify from "slugify";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification"; // Import useToast

const toast = useToast(); // Khởi tạo instance của toast

// Reactive variables
const coupon = ref({
  id: null,
  code: "",
  discount_type: "percent",
  discount_value: "",
  expires_at: "",
  usage_limit: null,
  per_user_limit: 1,
  used_count: 0,
  is_active: true,
  min_order_amount: null,
  max_discount: null,
});
const coupons = ref([]);
const addMessage = ref("");
const addMessageClass = ref("");
const listMessage = ref("");
const listMessageClass = ref("");
const isEditing = ref(false);

// State for detail modal
const showDetailModal = ref(false);
const viewingCoupon = ref(null);

// Helper function for formatting currency
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};


// Watch for code changes to slugify (only for new entries or when manually typing)
watch(
  () => coupon.value.code,
  (newCode) => {
    // Only slugify if not in editing mode OR if the new code actually needs slugifying
    // This prevents slugify from running on already slugified codes when editing
    if (!isEditing.value || (newCode && slugify(newCode, { lower: true, strict: true, locale: 'vi' }) !== newCode)) {
      coupon.value.code = slugify(newCode, {
        strict: true,
        locale: "vi",
      });
    }
  }
);

// Fetch active coupons
const fetchCoupons = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/coupons");
    coupons.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    if (!coupons.value.length) {
      listMessage.value = "Không có mã giảm giá nào.";
      listMessageClass.value = "text-blue-500";
    } else {
      listMessage.value = "";
    }
    await destroyAndReinitializeDataTable();
  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách mã giảm giá!";
    listMessageClass.value = "text-red-500";
    console.error("Lỗi khi tải danh sách:", error);
    toast.error("Lỗi khi tải danh sách mã giảm giá. Vui lòng thử lại!"); // Toast error
    await destroyAndReinitializeDataTable();
  }
};

// Handle adding a new coupon
const addCoupon = async () => {
  if (!coupon.value.code || !coupon.value.discount_type || coupon.value.discount_value === "") { // Check for empty string on discount_value
    addMessage.value = "Vui lòng nhập mã, loại giảm giá và giá trị giảm giá!";
    addMessageClass.value = "text-red-500";
    toast.error("Vui lòng điền đầy đủ thông tin bắt buộc!"); // Toast error
    return;
  }
  try {
    const response = await axios.post(
      "http://localhost:8000/api/admin/coupons",
      {
        code: coupon.value.code,
        discount_type: coupon.value.discount_type,
        discount_value: parseFloat(coupon.value.discount_value),
        expires_at: coupon.value.expires_at || null,
        usage_limit: coupon.value.usage_limit || null,
        per_user_limit: coupon.value.per_user_limit,
        is_active: coupon.value.is_active,
        min_order_amount: coupon.value.min_order_amount || null,
        max_discount: coupon.value.max_discount || null,
      },
      { validateStatus: (status) => status >= 200 && status < 300 }
    );
    addMessage.value = response.data.message || "Thêm mã giảm giá thành công!";
    addMessageClass.value = "text-green-500";
    toast.success("Thêm mã giảm giá thành công!"); // Toast success
    resetForm();
    await fetchCoupons();
  } catch (error) {
    console.error("Lỗi từ API:", error.response);
    const errors = error.response?.data?.errors;
    let errorMessageText = error.response?.data?.message || "Có lỗi khi thêm mã giảm giá!";
    if (errors) {
      errorMessageText = Object.values(errors).flat().join(" ");
    }
    addMessage.value = errorMessageText;
    addMessageClass.value = "text-red-500";
    toast.error(errorMessageText); // Toast error
  }
};

// Fill the form with coupon data for editing
const editCoupon = (id) => {
  const selectedCoupon = coupons.value.find(c => c.id === id);
  if (selectedCoupon) {
    coupon.value.id = selectedCoupon.id;
    coupon.value.code = selectedCoupon.code;
    coupon.value.discount_type = selectedCoupon.discount_type;
    coupon.value.discount_value = selectedCoupon.discount_value;
    // Format date for datetime-local input
    coupon.value.expires_at = selectedCoupon.expires_at ? new Date(selectedCoupon.expires_at).toISOString().slice(0, 16) : '';
    coupon.value.usage_limit = selectedCoupon.usage_limit;
    coupon.value.per_user_limit = selectedCoupon.per_user_limit;
    coupon.value.used_count = selectedCoupon.used_count;
    coupon.value.is_active = selectedCoupon.is_active;
    coupon.value.min_order_amount = selectedCoupon.min_order_amount;
    coupon.value.max_discount = selectedCoupon.max_discount;

    isEditing.value = true;
    addMessage.value = "";
    toast.info(`Đang chỉnh sửa mã: ${selectedCoupon.code}`); // Toast info
  }
};

// Handle updating an existing coupon
const updateCoupon = async () => {
  if (!coupon.value.id || !coupon.value.code || !coupon.value.discount_type || coupon.value.discount_value === "") {
    addMessage.value = "Vui lòng nhập đầy đủ thông tin để cập nhật!";
    addMessageClass.value = "text-red-500";
    toast.error("Vui lòng điền đầy đủ thông tin bắt buộc để cập nhật!"); // Toast error
    return;
  }
  try {
    const response = await axios.put(
      `http://localhost:8000/api/admin/coupons/${coupon.value.id}`,
      {
        code: coupon.value.code,
        discount_type: coupon.value.discount_type,
        discount_value: parseFloat(coupon.value.discount_value),
        expires_at: coupon.value.expires_at || null,
        usage_limit: coupon.value.usage_limit || null,
        per_user_limit: coupon.value.per_user_limit,
        is_active: coupon.value.is_active,
        min_order_amount: coupon.value.min_order_amount || null,
        max_discount: coupon.value.max_discount || null,
      },
      { validateStatus: (status) => status >= 200 && status < 300 }
    );
    addMessage.value = response.data.message || "Cập nhật mã giảm giá thành công!";
    addMessageClass.value = "text-green-500";
    toast.success("Cập nhật mã giảm giá thành công!"); // Toast success
    resetForm();
    await fetchCoupons();
  } catch (error) {
    console.error("Lỗi từ API khi cập nhật:", error.response);
    const errors = error.response?.data?.errors;
    let errorMessageText = error.response?.data?.message || "Có lỗi khi cập nhật mã giảm giá!";
    if (errors) {
      errorMessageText = Object.values(errors).flat().join(" ");
    }
    addMessage.value = errorMessageText;
    addMessageClass.value = "text-red-500";
    toast.error(errorMessageText); // Toast error
  }
};

// Cancel editing and reset the form
const cancelEdit = () => {
  resetForm();
  addMessage.value = "";
  toast.info("Đã hủy chỉnh sửa mã giảm giá."); // Toast info
};

// Reset form fields and editing state
const resetForm = () => {
  coupon.value = {
    id: null,
    code: "",
    discount_type: "percent",
    discount_value: "",
    expires_at: "",
    usage_limit: null,
    per_user_limit: 1,
    used_count: 0,
    is_active: true,
    min_order_amount: null,
    max_discount: null,
  };
  isEditing.value = false;
};

// Open detail modal
const viewCouponDetails = (id) => {
  const selectedCoupon = coupons.value.find(c => c.id === id);
  if (selectedCoupon) {
    viewingCoupon.value = { ...selectedCoupon }; // Copy the object to avoid direct mutation
    showDetailModal.value = true;
  }
};

// Close detail modal
const closeDetailModal = () => {
  showDetailModal.value = false;
  viewingCoupon.value = null;
};

// Handle soft delete with SweetAlert2
const confirmSoftDeleteWithSwal = async (id) => {
  try {
    const result = await Swal.fire({
      title: "Bạn có chắc muốn xóa mềm mã giảm giá này?",
      text: "Hành động này sẽ chuyển mã giảm giá vào thùng rác, bạn có thể khôi phục nó sau này. Bạn vẫn muốn tiếp tục?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Có, xóa mềm!",
      cancelButtonText: "Hủy",
    });

    if (result.isConfirmed) {
      const response = await axios.delete(`http://localhost:8000/api/admin/coupons/${id}`);
      await fetchCoupons();
      toast.success(response.data.message || "Xóa mềm mã giảm giá thành công!"); // Toast success
    } else {
      toast.info("Đã hủy thao tác xóa mềm mã giảm giá."); // Toast info for cancellation
    }
  } catch (error) {
    console.error("Lỗi khi xóa mềm mã giảm giá:", error);
    const errorMessage =
      error.response?.data?.message || "Không kết nối được tới server. Vui lòng kiểm tra mạng của bạn.";
    toast.error(`Xảy ra lỗi khi xóa mềm: ${errorMessage}`); // Toast error
  }
};

// Function to destroy and reinitialize DataTables
let dataTableInstance = null;
const destroyAndReinitializeDataTable = async () => {
  if (dataTableInstance) {
    dataTableInstance.destroy();
    dataTableInstance = null;
  }
  await nextTick(); // Ensure DOM is updated before reinitializing
  if (typeof jQuery !== "undefined" && jQuery.fn.DataTable) {
    dataTableInstance = jQuery("#add-row").DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      searching: true,
      paging: true,
      info: true,
      responsive: true,
      language: {
        lengthMenu: "Hiển thị _MENU_ mục",
        search: "Tìm kiếm:",
        info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
        paginate: {
          previous: "Trước",
          next: "Tiếp",
        },
        emptyTable: "Không có mã giảm giá nào",
      },
      columns: [
        { data: "code", defaultContent: "Không có" },
        {
          data: "discount_type",
          render: (data) => (data === "percent" ? "Phần trăm" : "Cố định"),
        },
        {
          data: "discount_value",
          render: (data, type, row) => `${data} ${row.discount_type === "percent" ? "%" : "VNĐ"}`,
        },
        {
          data: "expires_at",
          render: (data) => (data ? new Date(data).toLocaleString("vi-VN") : "Không có"),
        },
        {
          data: "is_active",
          render: (data) =>
            `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${data ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
              ${data ? 'Hoạt động' : 'Không hoạt động'}
            </span>`,
        },
        {
          data: null,
          render: (data, type, row) => `
            <div class="flex space-x-2 justify-center">
              <button type="button" title="Xem chi tiết" class="text-gray-600 hover:text-gray-900" data-action="view" data-id="${row.id}">
                <i class="fa fa-eye"></i>
              </button>
              <button type="button" title="Chỉnh sửa mã giảm giá" class="text-blue-600 hover:text-blue-900" data-action="edit" data-id="${row.id}">
                <i class="fa fa-edit"></i>
              </button>
              <button type="button" title="Xóa mềm" class="text-red-600 hover:text-red-900" data-action="delete" data-id="${row.id}">
                <i class="fa fa-times"></i>
              </button>
            </div>
          `,
        },
      ],
      data: coupons.value,
      drawCallback: () => {
        // Event listeners for action buttons
        jQuery("#add-row").off("click", "button[data-action]"); // Remove previous listeners
        jQuery("#add-row").on("click", "button[data-action]", (e) => {
          const id = jQuery(e.currentTarget).data("id");
          const action = jQuery(e.currentTarget).data("action");

          if (action === "view") {
            viewCouponDetails(id);
          } else if (action === "edit") {
            editCoupon(id);
          } else if (action === "delete") {
            confirmSoftDeleteWithSwal(id);
          }
        });
      },
    });
  } else {
    console.error("DataTables is not loaded correctly or jQuery is missing.");
    listMessage.value = "Không thể khởi tạo bảng!";
    listMessageClass.value = "text-red-500";
    toast.error("Không thể tải hoặc khởi tạo bảng dữ liệu. Vui lòng kiểm tra console!"); // Toast error
  }
};

onMounted(async () => {
  const scripts = [
    "https://code.jquery.com/jquery-3.7.1.min.js",
    "https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js",
  ];

  const loadScript = (src) =>
    new Promise((resolve, reject) => {
      const script = document.createElement("script");
      script.src = src;
      script.async = true;
      script.onload = () => {
        console.log(`Loaded: ${src}`);
        resolve();
      };
      script.onerror = () => {
        console.error(`Failed to load: ${src}`);
        reject(new Error(`Không thể tải script: ${src}`));
      };
      document.head.appendChild(script);
    });

  try {
    for (const src of scripts) {
      await loadScript(src);
    }
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = "https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css";
    document.head.appendChild(link);

    const fontAwesomeLink = document.createElement("link");
    fontAwesomeLink.rel = "stylesheet";
    fontAwesomeLink.href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css";
    document.head.appendChild(fontAwesomeLink);

    await fetchCoupons();
  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error.message, error.stack);
    listMessage.value = "Có lỗi khi tải bảng mã giảm giá!";
    listMessageClass.value = "text-red-500";
    toast.error("Có lỗi nghiêm trọng khi tải tài nguyên. Vui lòng kiểm tra kết nối mạng và thử lại!"); // Toast error
  }
});
</script>

<style scoped>
/* Custom CSS for form alignment */
/* Ensure all form-group-item have consistent height,
    especially if they contain different types of inputs (e.g., text, select, datetime-local)
    or varied small text. */
.form-group-item {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  /* Align content to the top within each item */
  margin-bottom: 1rem;
  /* Ensure consistent spacing between form groups */
}

/* For the checkbox group, use flexbox to align the checkbox and text */
.form-group-checkbox {
  display: flex;
  flex-direction: column;
  /* Keep label and the rest stacked */
  justify-content: flex-start;
}

.form-group-checkbox>div {
  display: flex;
  align-items: center;
  /* Vertically align checkbox and span */
}

/* Make sure all input fields fill the available width */
.form-group-item input[type="text"],
.form-group-item input[type="number"],
.form-group-item input[type="datetime-local"],
.form-group-item select {
  width: 100%;
  /* Ensure they take full width of their container */
  box-sizing: border-box;
  /* Include padding and border in the element's total width and height */
}

/* Specific styling for the checkbox itself if needed, though Tailwind's form-checkbox is usually good */
.form-checkbox {
  /* Tailwind's default h-5 w-5 usually makes it square */
  -webkit-appearance: none;
  /* Remove default browser styling for consistency */
  -moz-appearance: none;
  appearance: none;
  display: inline-block;
  vertical-align: middle;
  background-origin: border-box;
  user-select: none;
  flex-shrink: 0;
  /* Prevent it from shrinking */
  height: 1.25rem;
  /* h-5 */
  width: 1.25rem;
  /* w-5 */
  border-width: 1px;
  border-color: #d1d5db;
  /* gray-300 */
  border-radius: 0.25rem;
  /* rounded */
  cursor: pointer;
  position: relative;
  /* Needed for custom checkmark positioning */
}

.form-checkbox:checked {
  background-color: #3b82f6;
  /* blue-600 */
  border-color: #3b82f6;
  /* blue-600 */
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
  background-size: 100% 100%;
  background-position: center;
  background-repeat: no-repeat;
}

/* DataTables specific adjustments */
.dataTables_wrapper .dataTables_length {
  margin-bottom: 0.5rem;
  /* Add some space below the length dropdown */
}

/* Prevent DataTables elements from overlapping */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
  display: inline-block;
  /* Ensure they are on the same line if space allows */
  vertical-align: middle;
  /* Align them vertically */
}

/* Add margin to the select element within DataTables length control */
.dataTables_wrapper .dataTables_length select {
  margin-right: 0.5em;
  /* Add space between the select and the text "mục" */
  display: inline-block;
  /* Ensure it respects margin */
  width: auto;
  /* Allow width to be determined by content or browser default */
  min-width: unset;
  /* Remove any restrictive min-width */
}
</style>