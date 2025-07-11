<template>
  <div v-if="isVisible"
       class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center p-4 z-50"
       @click.self="cancelSelectionAndClose">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto relative">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-800">Chọn Voucher</h3>
        <button @click="cancelSelectionAndClose" class="text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div class="p-6 max-h-[70vh] overflow-y-auto">
        <div v-if="processedCoupons.length === 0" class="text-center text-gray-600 py-8">
          Không có voucher nào khả dụng lúc này.
        </div>
        <div v-else class="space-y-4">
          <div v-for="coupon in processedCoupons" :key="coupon.id"
               @click="selectOrDeselectCoupon(coupon)"
               :class="[
                 'p-4 border rounded-lg transition-all duration-200',
                 'flex items-center justify-between',
                 // Highlight khi được chọn
                 selectedCouponInternal && selectedCouponInternal.id === coupon.id
                   ? 'border-blue-500 ring-2 ring-blue-200 bg-blue-50'
                   : 'border-gray-200 hover:border-blue-300',
                 // Làm mờ và vô hiệu hóa nếu không đủ điều kiện
                 !coupon.isEligible ? 'opacity-50 cursor-not-allowed bg-gray-100' : 'cursor-pointer'
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
                Đơn tối thiểu:
                <span :class="{'text-red-600 font-bold': totalAmount < coupon.min_order_amount}">
                  {{ formatCurrency(coupon.min_order_amount) }}
                </span>
              </p>
              <p v-if="coupon.end_date || coupon.expires_at" class="text-xs text-gray-500 mt-1">
                Hạn sử dụng: {{ formatExpiryDate(coupon.end_date, coupon.expires_at) }}
              </p>
              <p v-if="!coupon.isEligible" class="text-red-600 text-xs font-medium italic mt-1">
                {{ coupon.ineligibilityReason }}
              </p>
            </div>
            <div
              :class="[
                'w-5 h-5 rounded-full border-2 flex items-center justify-center',
                selectedCouponInternal && selectedCouponInternal.id === coupon.id
                  ? 'bg-blue-600 border-blue-600'
                  : 'border-gray-400',
                // Tùy chọn để làm mờ radio button nếu không đủ điều kiện
                !coupon.isEligible ? 'bg-gray-300 border-gray-300' : ''
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
  },
  totalAmount: { // Thêm prop này để truyền tổng tiền từ Checkout.vue
    type: Number,
    default: 0,
  },
  // Thêm prop này để truyền chi tiết các item trong giỏ hàng/đặt hàng
  checkoutItems: {
    type: Array,
    default: () => [],
  }
});

const emit = defineEmits(['update:isVisible', 'couponSelected']);

const selectedCouponInternal = ref(null); // Lựa chọn hiện tại trong modal
let initialSelectedCouponCode = null; // Biến tạm để lưu trạng thái khi mở modal

// --- Watchers ---
watch(() => props.isVisible, (newVal) => {
  if (newVal) {
    initialSelectedCouponCode = props.currentSelectedCouponCode;
    selectedCouponInternal.value = props.availableCoupons.find(
      c => c.code === initialSelectedCouponCode
    ) || null;
  }
});

watch(() => props.availableCoupons, (newCoupons) => {
  if (newCoupons && newCoupons.length > 0) {
    if (selectedCouponInternal.value) {
      selectedCouponInternal.value = newCoupons.find(c => c.id === selectedCouponInternal.value.id) || null;
    }
    if (initialSelectedCouponCode) {
      initialSelectedCouponCode = newCoupons.find(c => c.code === initialSelectedCouponCode) ? initialSelectedCouponCode : null;
    }
  } else {
    selectedCouponInternal.value = null;
    initialSelectedCouponCode = null;
  }
}, { immediate: true, deep: true });

// --- Computed Property for button state ---
const isApplyButtonEnabled = computed(() => {
  const selectedCode = selectedCouponInternal.value ? selectedCouponInternal.value.code : null;

  // Nếu có voucher được chọn và voucher đó không đủ điều kiện, nút Áp dụng sẽ bị vô hiệu hóa
  if (selectedCouponInternal.value && !selectedCouponInternal.value.isEligible) {
    return false;
  }
  
  // Nút áp dụng được bật nếu có sự thay đổi so với lựa chọn ban đầu
  return selectedCode !== initialSelectedCouponCode;
});

// --- Computed Property to process coupons with eligibility ---
const processedCoupons = computed(() => {
  if (!props.availableCoupons || props.availableCoupons.length === 0) {
    return [];
  }

  return props.availableCoupons.map(coupon => {
    let isEligible = true;
    let ineligibilityReason = '';

    // 1. Check expiration date (backend should filter, but good to double-check)
    if (coupon.expires_at) {
        const expiryDate = new Date(coupon.expires_at);
        if (new Date() > expiryDate) {
            isEligible = false;
            ineligibilityReason = 'Voucher đã hết hạn.';
        }
    } else if (coupon.end_date) { // In case you use `end_date` as well
        const endDate = new Date(coupon.end_date);
        if (new Date() > endDate) {
            isEligible = false;
            ineligibilityReason = 'Voucher đã hết hạn.';
        }
    }


    // 2. Check minimum order amount (min_order_amount)
    if (isEligible && coupon.min_order_amount && props.totalAmount < coupon.min_order_amount) {
      isEligible = false;
      ineligibilityReason = `Đơn hàng cần tối thiểu ${formatCurrency(coupon.min_order_amount)}.`;
    }

    // 3. Check user usage limit (if applicable and backend provides `user_used_count` or similar)
    // Assuming coupon object has `usage_limit_per_user` and `user_used_count`
    if (isEligible && coupon.usage_limit_per_user && coupon.user_used_count >= coupon.usage_limit_per_user) {
        isEligible = false;
        ineligibilityReason = 'Bạn đã dùng hết lượt voucher này.';
    }

    // 4. Check product/category applicability (requires `checkoutItems` prop)
    // Assuming coupon object has `applies_to_products` (array of product IDs)
    // and `applies_to_categories` (array of category IDs)
    if (isEligible && (coupon.applies_to_products?.length > 0 || coupon.applies_to_categories?.length > 0)) {
        let hasApplicableItem = false;
        for (const item of props.checkoutItems) {
            // Check by product ID
            if (coupon.applies_to_products?.includes(item.product?.id)) {
                hasApplicableItem = true;
                break;
            }
            // Check by category ID (assuming product has categories array)
            if (item.product?.categories && coupon.applies_to_categories?.some(catId => item.product.categories.includes(catId))) {
                hasApplicableItem = true;
                break;
            }
        }
        if (!hasApplicableItem) {
            isEligible = false;
            ineligibilityReason = 'Voucher không áp dụng cho các sản phẩm đã chọn.';
        }
    }


    return {
      ...coupon,
      isEligible,
      ineligibilityReason,
    };
  }).sort((a, b) => {
    // Ưu tiên các voucher đủ điều kiện lên trên
    if (a.isEligible && !b.isEligible) return -1;
    if (!a.isEligible && b.isEligible) return 1;
    return 0; // Giữ nguyên thứ tự nếu cả hai đều đủ hoặc không đủ
  });
});

// --- Methods ---
const closeModal = () => {
  emit('update:isVisible', false);
};

// Phương thức để xử lý khi người dùng nhấn "Hủy", nút đóng X, hoặc click ra ngoài
const cancelSelectionAndClose = () => {
  // Khi hủy, chúng ta muốn trả về voucher ban đầu được chọn (trước khi mở modal)
  const couponToEmit = props.availableCoupons.find(
    c => c.code === initialSelectedCouponCode
  ) || null;
  emit('couponSelected', couponToEmit);
  closeModal();
};

const selectOrDeselectCoupon = (coupon) => {
  // Chỉ cho phép chọn/bỏ chọn nếu voucher đủ điều kiện
  if (!coupon.isEligible) {
    return;
  }

  if (selectedCouponInternal.value && selectedCouponInternal.value.id === coupon.id) {
    // Bỏ chọn nếu click lại vào chính voucher đó
    selectedCouponInternal.value = null;
  } else {
    // Chọn voucher mới
    selectedCouponInternal.value = coupon;
  }
};

const applyCoupon = () => {
  // Đảm bảo voucher được chọn đủ điều kiện trước khi emit
  if (selectedCouponInternal.value && !selectedCouponInternal.value.isEligible) {
    // Điều này không nên xảy ra nếu nút bị disabled, nhưng là một kiểm tra an toàn
    console.warn("Attempted to apply an ineligible coupon.");
    return;
  }
  emit('couponSelected', selectedCouponInternal.value);
  closeModal();
};

// --- Formatters ---
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