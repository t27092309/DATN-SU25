<template>
  <div v-if="isVisible"
       class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center p-4 z-50"
       @click.self="cancelSelectionAndClose"> <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto relative">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-800">Chọn Voucher</h3>
        <button @click="cancelSelectionAndClose" class="text-gray-500 hover:text-gray-700"> <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="p-6 max-h-[70vh] overflow-y-auto">
        <div v-if="availableCoupons.length === 0" class="text-center text-gray-600 py-8">
          Không có voucher nào khả dụng lúc này.
        </div>
        <div v-else class="space-y-4">
          <div v-for="coupon in availableCoupons" :key="coupon.id"
            @click="selectCoupon(coupon)"
            :class="[
              'p-4 border rounded-lg cursor-pointer transition-all duration-200',
              'flex items-center justify-between',
              selectedCouponInternal && selectedCouponInternal.id === coupon.id
                ? 'border-blue-500 ring-2 ring-blue-200 bg-blue-50'
                : 'border-gray-200 hover:border-blue-300'
            ]">
            <div>
              <p class="font-semibold text-lg text-gray-800">{{ coupon.code }}</p>
              <p class="text-sm text-gray-600 mt-1">
                Giảm
                <span class="font-medium text-red-600">
                  {{ formatDiscount(coupon.discount_type, coupon.discount_value) }}
                </span>
                <span v-if="coupon.max_discount"> (tối đa {{ formatCurrency(coupon.max_discount) }})</span>
              </p>
              <p v-if="coupon.min_order_amount" class="text-xs text-gray-500 mt-1">
                Đơn tối thiểu: {{ formatCurrency(coupon.min_order_amount) }}
              </p>
              <p v-if="coupon.end_date || coupon.expires_at" class="text-xs text-gray-500 mt-1">
                Hạn sử dụng: {{ formatExpiryDate(coupon.end_date, coupon.expires_at) }}
              </p>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                selectedCouponInternal && selectedCouponInternal.id === coupon.id
                  ? 'bg-blue-600 border-blue-600'
                  : 'border-gray-400'
              ]">
              <svg v-if="selectedCouponInternal && selectedCouponInternal.id === coupon.id"
                class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
        <button @click="cancelSelectionAndClose"
          class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition">
          Hủy
        </button>
        <button @click="applyCoupon"
          :disabled="!isApplyButtonEnabled"
          :class="[
            'px-5 py-2 text-sm font-medium text-white rounded-md transition',
            isApplyButtonEnabled ? 'bg-blue-600 hover:bg-blue-700' : 'bg-blue-300 cursor-not-allowed'
          ]">
          Áp dụng Voucher
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  availableCoupons: {
    type: Array,
    default: () => [],
  },
  currentSelectedCouponCode: { // Mã voucher đang áp dụng từ component cha
    type: String,
    default: null,
  }
});

const emit = defineEmits(['update:isVisible', 'couponSelected']);

const selectedCouponInternal = ref(null); // Lựa chọn hiện tại trong modal
let initialSelectedCouponCode = null; // Biến tạm để lưu trạng thái khi mở modal

// --- Watchers ---
// Khi modal hiển thị, đồng bộ selectedCouponInternal với currentSelectedCouponCode
watch(() => props.isVisible, (newVal) => {
  if (newVal) {
    // Khi mở modal, lưu trữ mã voucher hiện tại
    initialSelectedCouponCode = props.currentSelectedCouponCode;
    // Và thiết lập selectedCouponInternal để hiển thị lựa chọn hiện tại
    selectedCouponInternal.value = props.availableCoupons.find(
      c => c.code === initialSelectedCouponCode
    ) || null;
  }
});

// Khi danh sách coupons thay đổi (ví dụ: tải lại), cập nhật lại lựa chọn nội bộ
watch(() => props.availableCoupons, (newCoupons) => {
  if (newCoupons && newCoupons.length > 0) {
    // Nếu có lựa chọn hiện tại trong modal, cố gắng tìm lại nó trong danh sách mới
    if (selectedCouponInternal.value) {
      selectedCouponInternal.value = newCoupons.find(c => c.id === selectedCouponInternal.value.id) || null;
    }
    // Cập nhật lại initialSelectedCouponCode nếu coupon đó vẫn tồn tại trong list mới
    if (initialSelectedCouponCode) {
      initialSelectedCouponCode = newCoupons.find(c => c.code === initialSelectedCouponCode) ? initialSelectedCouponCode : null;
    }
  } else {
    // Nếu không có coupons nào, reset tất cả lựa chọn
    selectedCouponInternal.value = null;
    initialSelectedCouponCode = null;
  }
}, { immediate: true, deep: true });

// --- Computed Property for button state ---
const isApplyButtonEnabled = computed(() => {
  // Nút áp dụng được bật nếu:
  // 1. Có một voucher được chọn trong modal, VÀ
  // 2. Voucher được chọn đó khác với voucher ban đầu (hoặc ban đầu không có voucher)
  // HOẶC
  // 3. Không có voucher nào được chọn trong modal, NHƯNG ban đầu có một voucher được chọn (tức là người dùng muốn hủy)
  // => Đã bao gồm cả trường hợp bỏ chọn rồi áp dụng để hủy
  const selectedCode = selectedCouponInternal.value ? selectedCouponInternal.value.code : null;
  return selectedCode !== initialSelectedCouponCode;
});


// --- Methods ---
const closeModal = () => {
  emit('update:isVisible', false);
};

// Phương thức để xử lý khi người dùng nhấn "Hủy", nút đóng X, hoặc click ra ngoài
const cancelSelectionAndClose = () => {
  // So sánh lựa chọn hiện tại trong modal với lựa chọn ban đầu
  const selectedCodeInModal = selectedCouponInternal.value ? selectedCouponInternal.value.code : null;

  if (selectedCodeInModal !== initialSelectedCouponCode) {
    // Nếu có sự thay đổi (đã bỏ chọn hoặc chọn voucher khác)
    // thì gửi về lựa chọn hiện tại trong modal (có thể là null nếu đã bỏ chọn)
    emit('couponSelected', selectedCouponInternal.value);
  } else {
    // Nếu không có sự thay đổi so với lựa chọn ban đầu
    // thì gửi về voucher ban đầu (để giữ nguyên)
    const couponToEmit = props.availableCoupons.find(
      c => c.code === initialSelectedCouponCode
    ) || null;
    emit('couponSelected', couponToEmit);
  }
  closeModal();
};


const selectCoupon = (coupon) => {
  if (selectedCouponInternal.value && selectedCouponInternal.value.id === coupon.id) {
    // Bỏ chọn nếu click lại vào chính voucher đó
    selectedCouponInternal.value = null;
  } else {
    // Chọn voucher mới
    selectedCouponInternal.value = coupon;
  }
};

const applyCoupon = () => {
  // Gửi voucher được chọn hiện tại (có thể là null nếu người dùng bỏ chọn tất cả)
  emit('couponSelected', selectedCouponInternal.value);
  closeModal();
};

// --- Formatters (giữ nguyên) ---
const formatCurrency = (amount) => {
  if (amount === null || amount === undefined || isNaN(amount)) {
    return '₫0';
  }
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(amount);
};

const formatDiscount = (type, value) => {
  if (type === 'percent') {
    return `${value}%`;
  }
  return formatCurrency(value);
};

const formatExpiryDate = (endDate, expiresAt) => {
  if (endDate) {
    const date = new Date(endDate);
    return new Intl.DateTimeFormat('vi-VN', { day: 'numeric', month: 'numeric', year: 'numeric' }).format(date);
  }
  if (expiresAt) {
    const date = new Date(expiresAt);
    return new Intl.DateTimeFormat('vi-VN', { day: 'numeric', month: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric' }).format(date);
  }
  return 'Không giới hạn';
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>