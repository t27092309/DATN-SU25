<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import AddressSelectionModal from '@/components/checkout/AddressSelectionModal.vue';
import VoucherSelectionModal from '@/components/checkout/VoucherSelectionModal.vue';
import PaymentMethodSelectionModal from '@/components/checkout/PaymentMethodSelectionModal.vue';
// NEW: Import the new ShippingMethodSelectionModal
import ShippingMethodSelectionModal from '@/components/checkout/ShippingMethodSelectionModal.vue';

const api = axios;

const route = useRoute();
const router = useRouter();

// --- State variables ---
const checkoutItems = ref([]);
const message = ref('');
const usePoints = ref(false);
const userPoints = ref(0);
const isLoading = ref(false);
const loading = ref(false);
const error = ref(null);
const errorMessage = ref(null);

const loadingAddress = ref(true);
const addressError = ref(null);

const userAddresses = ref([]);
const selectedAddressId = ref(null);
const newAddressDetails = ref({
  recipient_name: '',
  phone_number: '',
  address_line: '',
  ward: '',
  district: '',
  province: '',
});

const useNewAddressForm = ref(false);
const showAddressModal = ref(false);

const selectedCouponCode = ref(null);
const voucherDiscount = ref(0);
const showVoucherModal = ref(false);
const availableCoupons = ref([]);
const voucherErrorMessage = ref(null);
const isCheckingVoucher = ref(null);

// --- Shipping Method State (UPDATED for modal integration) ---
const shippingMethods = ref([]); // Store all available methods (fetched once or when modal opens)
const selectedShippingMethodId = ref(null); // The ID of the currently chosen method
const showShippingMethodModal = ref(false); // Controls modal visibility
const loadingShippingMethods = ref(false); // To show loading state
const shippingMethodsError = ref(null); // To show error state

// --- Payment Method State ---
const showPaymentMethodModal = ref(false);
const selectedPaymentMethod = ref('cash');

const paymentMethods = ref([
  { code: 'cash', name: 'Thanh toán khi nhận hàng (COD)' },
  { code: 'momo', name: 'Momo' },
  { code: 'vnpay', name: 'VNPay' },
]);

// --- Utility Functions ---
const formatCurrency = (amount) => {
  if (amount === null || amount === undefined || isNaN(amount)) {
    return '₫0';
  }
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(amount);
};

const formatCurrencyWithoutSymbol = (amount) => {
  if (amount === null || amount === undefined || isNaN(amount)) {
    return '0';
  }
  return new Intl.NumberFormat('vi-VN').format(amount);
};

const getPaymentMethodName = (code) => {
  const method = paymentMethods.value.find(m => m.code === code);
  return method ? method.name : 'Không xác định';
};

// --- Computed Properties ---
const totalItemsCount = computed(() => {
  if (!Array.isArray(checkoutItems.value)) {
    return 0;
  }
  return checkoutItems.value.reduce((sum, item) => sum + item.quantity, 0);
});

const totalAmountBeforeShippingAndVoucher = computed(() => {
  if (!Array.isArray(checkoutItems.value)) {
    return 0;
  }
  return checkoutItems.value.reduce((sum, item) => sum + (parseFloat(item.price) * item.quantity), 0);
});

// UPDATED: Dynamic shipping fee based on selected method (using 'price' from API)
const shippingFee = computed(() => {
  const selectedMethod = shippingMethods.value.find(method => method.id === selectedShippingMethodId.value);
  return selectedMethod ? parseFloat(selectedMethod.price) : 0; // Use 'price'
});

const usedPointsAmount = computed(() => {
  const maxUsablePoints = totalAmountBeforeShippingAndVoucher.value + shippingFee.value - voucherDiscount.value;
  return usePoints.value ? Math.min(userPoints.value, Math.max(0, maxUsablePoints)) : 0;
});

const finalTotalAmount = computed(() => {
  let total = totalAmountBeforeShippingAndVoucher.value + shippingFee.value - voucherDiscount.value;
  if (usePoints.value) {
    total -= usedPointsAmount.value;
  }
  return Math.max(0, total);
});

const selectedAddress = computed(() => {
  if (!useNewAddressForm.value && selectedAddressId.value) {
    return userAddresses.value.find(addr => addr.id === selectedAddressId.value);
  }
  return null;
});

const hasNewAddressDetails = computed(() => {
  const details = newAddressDetails.value;
  return details.recipient_name && details.phone_number && details.address_line &&
    details.ward && details.district && details.province;
});

const canPlaceOrder = computed(() => {
  return (useNewAddressForm.value && hasNewAddressDetails.value || (!useNewAddressForm.value && selectedAddressId.value !== null)) &&
           checkoutItems.value.length > 0 &&
           selectedShippingMethodId.value !== null; // Ensure a shipping method is selected
});

// UPDATED: Estimated delivery dates based on selected shipping method (using new fields from API)
const estimatedDeliveryDate = computed(() => {
    const selectedMethod = shippingMethods.value.find(method => method.id === selectedShippingMethodId.value);
    if (selectedMethod && selectedMethod.delivery_time_min !== undefined && selectedMethod.delivery_time_max !== undefined && selectedMethod.delivery_time_unit) {
        const today = new Date();
        const formatter = new Intl.DateTimeFormat('vi-VN', { day: 'numeric', month: 'numeric' });

        if (selectedMethod.delivery_time_unit === 'hours') {
            // For hourly deliveries, provide a direct range in hours
            return `Trong vòng ${selectedMethod.delivery_time_min} - ${selectedMethod.delivery_time_max} giờ`;
        } else if (selectedMethod.delivery_time_unit === 'days') {
            const deliveryStartDate = new Date(today);
            deliveryStartDate.setDate(today.getDate() + selectedMethod.delivery_time_min);
            const deliveryEndDate = new Date(today);
            deliveryEndDate.setDate(today.getDate() + selectedMethod.delivery_time_max);
            return `${formatter.format(deliveryStartDate)} - ${formatter.format(deliveryEndDate)}`;
        }
    }
    return 'Chưa xác định';
});

// UPDATED: Delivery Guarantee Date (using new fields from API)
const deliveryGuaranteeDate = computed(() => {
    const selectedMethod = shippingMethods.value.find(method => method.id === selectedShippingMethodId.value);
    if (selectedMethod && selectedMethod.delivery_time_max !== undefined && selectedMethod.delivery_time_unit) {
        const today = new Date();
        let guaranteeDaysOffset = 0;

        if (selectedMethod.delivery_time_unit === 'days') {
            guaranteeDaysOffset = selectedMethod.delivery_time_max + 1; // E.g., if max is 3 days, guarantee by 4th day
        } else if (selectedMethod.delivery_time_unit === 'hours') {
            // For hourly, a common approach is end of next day for guarantee
            // Adjust this logic if your guarantee is different (e.g., specific hour)
            guaranteeDaysOffset = 1; // Guarantee by end of next day
        }

        const guaranteeDate = new Date(today);
        guaranteeDate.setDate(today.getDate() + guaranteeDaysOffset);

        const formatter = new Intl.DateTimeFormat('vi-VN', { day: 'numeric', month: 'numeric', year: 'numeric' });
        return formatter.format(guaranteeDate);
    }
    return 'Chưa xác định';
});


// --- Data Fetching Functions ---
const fetchAvailableCoupons = async () => {
  try {
    const response = await axios.get('coupons/available');
    availableCoupons.value = response.data;
  } catch (err) {
    console.error('Error fetching available coupons:', err);
  }
};

const openVoucherSelectionModal = () => {
  fetchAvailableCoupons();
  showVoucherModal.value = true;
};

const handleCouponSelection = async (coupon) => {
  voucherErrorMessage.value = null;
  voucherDiscount.value = 0;
  selectedCouponCode.value = null;

  if (coupon) {
    if (!coupon.isEligible) {
      voucherErrorMessage.value = coupon.ineligibilityReason || 'Voucher không đủ điều kiện sử dụng.';
      selectedCouponCode.value = null;
      voucherDiscount.value = 0;
      return;
    }

    isCheckingVoucher.value = true;
    try {
      const payload = {
        coupon_code: coupon.code,
        total_amount: totalAmountBeforeShippingAndVoucher.value,
        order_items: checkoutItems.value.map(item => ({
          product_id: item.product.id,
          product_variant_id: item.variant.id,
          quantity: item.quantity,
          price: item.price,
        })),
      };

      const response = await axios.post('check-coupon', payload);

      if (response.data && response.data.coupon && response.data.coupon.discount_amount !== undefined) {
        selectedCouponCode.value = response.data.coupon.code;
        voucherDiscount.value = response.data.coupon.discount_amount;
      } else {
        voucherErrorMessage.value = response.data.message || 'Voucher không hợp lệ hoặc không áp dụng được.';
        selectedCouponCode.value = null;
        voucherDiscount.value = 0;
      }
    } catch (err) {
      console.error('Lỗi khi kiểm tra voucher với backend:', err);
      if (err.response && err.response.data && err.response.data.message) {
        voucherErrorMessage.value = err.response.data.message;
      } else {
        voucherErrorMessage.value = 'Không thể kiểm tra voucher. Vui lòng thử lại.';
      }
      selectedCouponCode.value = null;
      voucherDiscount.value = 0;
    } finally {
      isCheckingVoucher.value = false;
      showVoucherModal.value = false;
    }
  } else {
    selectedCouponCode.value = null;
    voucherDiscount.value = 0;
    voucherErrorMessage.value = null;
    showVoucherModal.value = false;
  }
};

async function fetchCheckoutItemsFromCart(itemIds) {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await api.post('checkout/order-items', { cart_item_ids: itemIds });

    if (!response.data || !Array.isArray(response.data.items)) {
      throw new Error('Cấu trúc dữ liệu không hợp lệ từ server.');
    }

    checkoutItems.value = response.data.items.map(item => {
      return {
        id: item.id,
        product: {
            id: item.product_id,
            name: item.product_name,
            thumbnail_url: item.thumbnail_url
        },
        quantity: item.quantity,
        variant: {
          id: item.variant.id,
          name: item.variant.name,
          sku: item.variant.sku,
        },
        price: item.price,
        subtotal: item.subtotal,
      };
    });
  } catch (err) {
    console.error('Lỗi khi lấy chi tiết giỏ hàng:', err);
    if (err.response && err.response.data && err.response.data.message) {
      error.value = err.response.data.message;
    } else {
      error.value = 'Không thể tải chi tiết giỏ hàng. Vui lòng thử lại.';
    }
  } finally {
    isLoading.value = false;
  }
}

async function fetchCheckoutItemForBuyNow(variantId, quantity) {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await api.get(`product-variants/${variantId}`);
    const variant = response.data.data;

    if (!variant) {
      console.warn('Không tìm thấy biến thể cho ID:', variantId);
      error.value = 'Không tìm thấy thông tin biến thể.';
      isLoading.value = false;
      return;
    }

    const variantNameParts = variant.attribute_values.map(attrValue => {
      const attrName = attrValue.attribute?.name;
      const attrValueName = attrValue.value;

      if (attrName && attrValueName) {
        return `${attrName}: ${attrValueName}`;
      } else if (attrValueName) {
        return attrValueName;
      }
      return '';
    }).filter(Boolean);
    const variantName = variantNameParts.length > 0 ? variantNameParts.join(' / ') : 'Mặc định';

    checkoutItems.value = [{
      product: {
        id: variant.product?.id,
        name: variant.product?.name,
        image: variant.product?.image,
      },
      quantity: quantity,
      variant: {
        id: variant.id,
        name: variantName,
      },
      price: variant.price,
      subtotal: variant.price * quantity,
      selected: true,
    }];
  } catch (err) {
    console.error('Lỗi khi lấy chi tiết sản phẩm mua ngay:', err);
    error.value = 'Không thể tải chi tiết sản phẩm. Vui lòng thử lại.';
  } finally {
    isLoading.value = false;
  }
}

async function fetchUserAddresses() {
  loadingAddress.value = true;
  addressError.value = null;
  try {
    const response = await axios.get('user/addresses');
    userAddresses.value = response.data;
    const defaultAddr = userAddresses.value.find(addr => addr.is_default === 1);
    if (defaultAddr) {
      selectedAddressId.value = defaultAddr.id;
      useNewAddressForm.value = false;
    } else if (userAddresses.value.length > 0) {
      selectedAddressId.value = userAddresses.value[0].id;
      useNewAddressForm.value = false;
    } else {
      useNewAddressForm.value = true;
    }
  } catch (err) {
    console.error('Error fetching user addresses:', err);
    addressError.value = 'Không thể tải địa chỉ của bạn. Vui lòng thử lại.';
    userAddresses.value = [];
    useNewAddressForm.value = true;
  } finally {
    loadingAddress.value = false;
  }
}

async function fetchUserPoints() {
  try {
    const response = await axios.get('/api/user/points');
    userPoints.value = response.data.points || 0;
  } catch (err) {
    console.error('Lỗi khi tải điểm của người dùng:', err);
    userPoints.value = 0;
  }
}

// NEW: Fetch all shipping methods and set a default if available
async function fetchAndSetDefaultShippingMethods() {
    loadingShippingMethods.value = true;
    shippingMethodsError.value = null;
    try {
        // Fetch from the new API endpoint
        const response = await axios.get('/shipping-methods');
        // UPDATED: Adjust based on your API response structure (direct array under 'shipping_methods')
        shippingMethods.value = response.data.shipping_methods;

        // Set a default selected method if none is chosen and methods are available
        if (shippingMethods.value.length > 0 && !selectedShippingMethodId.value) {
            selectedShippingMethodId.value = shippingMethods.value[0].id;
        }
    } catch (err) {
        console.error('Error fetching shipping methods:', err);
        shippingMethodsError.value = 'Không thể tải các phương thức vận chuyển.';
    } finally {
        loadingShippingMethods.value = false;
    }
}

// Handler for when a shipping method is selected in the modal
const handleShippingMethodSelected = (methodId) => {
  selectedShippingMethodId.value = methodId;
  showShippingMethodModal.value = false; // Close the modal
};


// --- Address Selection Handler from Modal ---
const handleAddressSelection = (payload) => {
  if (payload.type === 'existing') {
    selectedAddressId.value = payload.id;
    useNewAddressForm.value = false;
    newAddressDetails.value = {
      recipient_name: '', phone_number: '', address_line: '', ward: '', district: '', province: '',
    };
  } else if (payload.type === 'new') {
    newAddressDetails.value = payload.details;
    useNewAddressForm.value = true;
    selectedAddressId.value = null;
  }
  showAddressModal.value = false;
};

// --- Payment Method Selection Handler ---
const handlePaymentMethodSelection = (methodCode) => {
  selectedPaymentMethod.value = methodCode;
  showPaymentMethodModal.value = false;
};


// --- Order Placement ---
const placeOrder = async () => {
  errorMessage.value = null;

  if (checkoutItems.value.length === 0) {
    errorMessage.value = 'Vui lòng chọn sản phẩm để thanh toán.';
    return;
  }

  let addressPayload = {};
  let addressChosenSuccessfully = false;

  if (useNewAddressForm.value) {
    const { recipient_name, phone_number, address_line, ward, district, province } = newAddressDetails.value;
    if (!recipient_name || !phone_number || !address_line || !ward || !district || !province) {
      errorMessage.value = 'Vui lòng điền đầy đủ thông tin địa chỉ mới.';
      return;
    }
    addressPayload = {
      recipient_name,
      phone_number,
      address_line,
      ward,
      district,
      province,
    };
    addressChosenSuccessfully = true;
  } else {
    if (!selectedAddressId.value) {
      errorMessage.value = 'Vui lòng chọn một địa chỉ có sẵn hoặc nhập địa chỉ mới.';
      return;
    }
    addressPayload = { address_id: selectedAddressId.value };
    addressChosenSuccessfully = true;
  }

  if (!addressChosenSuccessfully) {
    errorMessage.value = 'Vui lòng chọn hoặc nhập địa chỉ nhận hàng.';
    return;
  }

  // NEW: Validate shipping method
  if (!selectedShippingMethodId.value) {
    errorMessage.value = 'Vui lòng chọn phương thức vận chuyển.';
    return;
  }

  if (!confirm('Bạn có chắc chắn muốn đặt hàng không?')) {
    return;
  }

  loading.value = true;

  try {
    const isBuyNow = route.query.buy_now === 'true';

    let orderData = {
      ...addressPayload,
      shipping_method_id: selectedShippingMethodId.value, // Send selected shipping method ID
      notes: message.value,
      coupon_code: selectedCouponCode.value,
      payment_method: selectedPaymentMethod.value,
    };

    let response;
    if (isBuyNow) {
      const singleItem = checkoutItems.value[0];
      if (!singleItem || !singleItem.variant || !singleItem.variant.id) {
        errorMessage.value = 'Thông tin sản phẩm để mua ngay không hợp lệ.';
        loading.value = false;
        return;
      }
      orderData.product_variant_id = singleItem.variant.id;
      orderData.quantity = singleItem.quantity;

      response = await axios.post('checkout/buy-now', orderData);
    } else {
      response = await axios.post('checkout/place-order', orderData);
    }

    const paymentInfo = response.data.payment_info;

    if (paymentInfo && paymentInfo.status === 'redirect' && paymentInfo.redirect_url) {
      window.location.href = paymentInfo.redirect_url;
    } else {
      alert(`${response.data.message} ${paymentInfo?.message || ''}`);
      router.push({ name: 'DatHangThanhCong', params: { ma_don_hang: response.data.order_id } });
    }

  } catch (err) {
    console.error('Lỗi khi đặt hàng:', err);
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage.value = `Lỗi đặt hàng: ${err.response.data.message}`;
      if (err.response.data.errors) {
        for (const key in err.response.data.errors) {
          errorMessage.value += `\n- ${err.response.data.errors[key].join(', ')}`;
        }
      }
    } else {
      errorMessage.value = 'Không thể đặt hàng. Vui lòng thử lại.';
    }
  } finally {
    loading.value = false;
  }
};


// --- Lifecycle Hook ---
onMounted(async () => {
  const isBuyNow = route.query.buy_now === 'true';
  const variantId = route.query.variant_id;
  const quantity = parseInt(route.query.qty);

  if (isBuyNow && variantId && quantity) {
    await fetchCheckoutItemForBuyNow(variantId, quantity);
  } else {
    const cartItemIdsString = route.query.cart_item_ids;
    if (cartItemIdsString) {
      const cartItemIds = cartItemIdsString.split(',').map(Number);
      if (cartItemIds.length > 0) {
        await fetchCheckoutItemsFromCart(cartItemIds);
      } else {
        error.value = 'Không có sản phẩm nào được chọn để thanh toán.';
        loading.value = false;
      }
    } else {
      error.value = 'Không có sản phẩm nào được chọn để thanh toán.';
      loading.value = false;
    }
  }

  await Promise.all([
    fetchUserAddresses(),
    fetchUserPoints(),
    fetchAndSetDefaultShippingMethods() // NEW: Fetch and set default shipping methods
  ]);
});

watch(userAddresses, (newAddresses) => {
  if (newAddresses.length === 0 && !selectedAddressId.value) {
    useNewAddressForm.value = true;
  }
}, { immediate: true });
</script>

<template>
  <div class="container mx-auto p-4 bg-gray-50 max-w-[1200px]">
    <div class="bg-white p-4 rounded-lg shadow-sm">
      <h2 class="text-xl font-semibold mb-4">Địa chỉ giao hàng</h2>

      <div class="mb-4 p-3 border rounded-md bg-gray-50">
        <div v-if="selectedAddress">
          <p class="font-medium text-gray-800">{{ selectedAddress.recipient_name }} - {{ selectedAddress.phone_number }}
          </p>
          <p class="text-sm text-gray-600">
            {{ selectedAddress.address_line }}, {{ selectedAddress.ward }}, {{ selectedAddress.district }}, {{
              selectedAddress.province }}
          </p>
          <span v-if="selectedAddress.is_default"
            class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full mt-1">Mặc định</span>
        </div>
        <div v-else-if="useNewAddressForm && hasNewAddressDetails">
          <p class="font-medium text-gray-800">{{ newAddressDetails.recipient_name }} - {{
            newAddressDetails.phone_number }}</p>
          <p class="text-sm text-gray-600">
            {{ newAddressDetails.address_line }}, {{ newAddressDetails.ward }}, {{ newAddressDetails.district }}, {{
              newAddressDetails.province }}
          </p>
        </div>
        <div v-else class="text-gray-600">
          Chưa có địa chỉ nào được chọn.
        </div>

        <button @click="showAddressModal = true"
          class="mt-3 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
          {{ selectedAddress || (useNewAddressForm && hasNewAddressDetails) ? 'Thay đổi địa chỉ' : 'Chọn địa chỉ' }}
        </button>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm mb-4 mt-4">
      <h2 class="text-xl font-semibold mb-4">Sản phẩm đã chọn</h2>
      <div class="grid grid-cols-5 text-gray-500 pb-2 border-b border-gray-200">
        <div class="col-span-2">Sản phẩm</div>
        <div class="text-center">Đơn giá</div>
        <div class="text-center">Số lượng</div>
        <div class="text-right">Thành tiền</div>
      </div>

      <div v-if="isLoading" class="text-center py-4 text-gray-600">Đang tải sản phẩm...</div>
      <div v-else-if="error" class="text-center py-4 text-red-600">{{ error }}</div>
      <div v-else-if="!checkoutItems || checkoutItems.length === 0" class="text-center py-4 text-gray-600">
        Không có sản phẩm nào được chọn để thanh toán.
      </div>
      <div v-else>
        <div v-for="item in checkoutItems" :key="item.variant.id || item.id"
          class="py-4 border-b border-gray-200 last:border-b-0">
          <div class="grid grid-cols-5 items-center">
            <div class="col-span-2 flex items-center">
              <img :src="item.product?.image || 'https://via.placeholder.com/64'"
                :alt="item.product?.name || 'Sản phẩm'" class="w-16 h-16 mr-3 border rounded object-cover">
              <div>
                <p class="text-gray-800">{{ item.product?.name || 'Sản phẩm không rõ tên' }}</p>
                <p v-if="item.variant && item.variant.name" class="text-gray-500 text-sm">
                  Phân loại: {{ item.variant.name }}
                </p>
              </div>
            </div>
            <div class="text-center text-gray-700">{{ formatCurrency(item.price) }}</div>
            <div class="text-center text-gray-700">{{ item.quantity }}</div>
            <div class="text-right text-red-500 font-semibold">{{ formatCurrency(item.price * item.quantity) }}</div>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
      <div class="grid grid-cols-4 items-center">
        <div class="col-span-1 text-gray-500">Phương thức vận chuyển:</div>
        <div class="col-span-2">
          <span class="font-medium">
            {{ shippingMethods.find(m => m.id === selectedShippingMethodId)?.name || 'Chưa chọn' }}
          </span>
          <button @click="showShippingMethodModal = true" class="ml-2 text-blue-500 hover:underline">Thay Đổi</button>
          <p class="text-xs text-gray-500 mt-1">Dự kiến nhận hàng: {{ estimatedDeliveryDate }}</p>
          <p class="text-xs text-gray-500 mt-1">
            <span class="text-red-500">Voucher trị giá ₫15.000</span> nếu đơn hàng được giao đến bạn sau ngày {{
              deliveryGuaranteeDate }}
            <span class="ml-1 cursor-pointer text-blue-400" title="Chi tiết về voucher">
              <svg class="w-3 h-3 inline-block" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-2 5a1 1 0 00-1 1v2a1 1 0 102 0v-2a1 1 0 00-1-1z"
                  clip-rule="evenodd"></path>
              </svg>
            </span>
          </p>
        </div>
        <div class="col-span-1 text-right text-gray-700">{{ formatCurrency(shippingFee) }}</div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm">
      <div class="flex items-center mb-4">
        <span class="text-gray-500 mr-4">Lời nhắn:</span>
        <input type="text" v-model="message" placeholder="Lưu ý cho chúng tôi"
          class="flex-grow border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
      </div>
      <div class="flex items-center mb-4 text-sm text-gray-500">
        Được đồng kiểm.
        <span class="ml-1 cursor-pointer text-blue-400" title="Chi tiết về đồng kiểm">
          <svg class="w-3 h-3 inline-block" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-2 5a1 1 0 00-1 1v2a1 1 0 102 0v-2a1 1 0 00-1-1z"
              clip-rule="evenodd"></path>
          </svg>
        </span>
      </div>

      <div class="text-right text-lg font-bold">
        Tổng số tiền ({{ totalItemsCount }} sản phẩm): <span class="text-red-500">{{
          formatCurrency(totalAmountBeforeShippingAndVoucher) }}</span>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm mb-4 mt-4">
      <div class="flex justify-between items-center mb-4">
        <div class="flex items-center text-red-500">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M5.05 4.05a7 7 0 0110.038 0l1.929 1.929a1 1 0 11-1.414 1.414L13.5 5.5l-1.929 1.929a1 1 0 11-1.414-1.414l1.929-1.929a7 7 0 010-10.038zM10 18a8 8 0 100-16 8 8 0 000 16z"
              clip-rule="evenodd"></path>
          </svg>
          <span class="font-semibold">Voucher</span>
        </div>
        <button @click="openVoucherSelectionModal" class="text-blue-500 hover:underline">
          {{ selectedCouponCode ? `Đổi Voucher (${selectedCouponCode})` : 'Chọn Voucher' }}
        </button>
      </div>

      <VoucherSelectionModal :is-visible="showVoucherModal" :available-coupons="availableCoupons"
        :current-selected-coupon-code="selectedCouponCode" :total-amount="totalAmountBeforeShippingAndVoucher"
        :checkout-items="checkoutItems" @update:is-visible="showVoucherModal = $event"
        @couponSelected="handleCouponSelection" />

      <div class="flex justify-between items-center border-t border-gray-200 pt-4">
        <div class="flex items-center">
          <span class="text-orange-500 text-xl mr-2">Floréa Xu</span> <span class="text-gray-700">Xu <span
              class="text-gray-500 text-sm">Dùng {{ formatCurrencyWithoutSymbol(userPoints) }} Xu</span></span>
        </div>
        <div class="flex items-center">
          <span class="text-gray-500 mr-2">[-{{ formatCurrency(usedPointsAmount) }}]</span>
          <input type="checkbox" class="form-checkbox h-5 w-5 text-red-600 rounded" v-model="usePoints" />
        </div>
      </div>
    </div>

    <div class="bg-white p-4 rounded-lg shadow-sm mt-4">
      <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-200">
        <h3 class="font-semibold text-lg text-gray-800">Phương thức thanh toán</h3>
        <div class="flex items-center text-gray-700">
          <span class="mr-2">{{ getPaymentMethodName(selectedPaymentMethod) }}</span>
          <button @click="showPaymentMethodModal = true" class="text-blue-500 hover:underline">THAY ĐỔI</button>
        </div>
      </div>

      <PaymentMethodSelectionModal :is-visible="showPaymentMethodModal" :current-selected-method="selectedPaymentMethod"
        @update:is-visible="showPaymentMethodModal = $event" @methodSelected="handlePaymentMethodSelection" />

      <div class="text-right text-gray-700 mt-4">
        <div class="flex justify-end items-center mb-2">
          <span class="mr-4 text-gray-500">Tổng tiền hàng</span>
          <span class="w-24">{{ formatCurrency(totalAmountBeforeShippingAndVoucher) }}</span>
        </div>
        <div class="flex justify-end items-center mb-2">
          <span class="mr-4 text-gray-500">Tổng tiền phí vận chuyển</span>
          <span class="w-24">{{ formatCurrency(shippingFee) }}</span>
        </div>
        <div class="flex justify-end items-center mb-2">
          <span class="mr-4 text-gray-500">Voucher giảm giá</span>
          <span class="w-24">-{{ formatCurrency(voucherDiscount) }}</span>
        </div>
        <div class="flex justify-end items-center mb-2">
          <span class="mr-4 text-gray-500">Giảm giá từ xu</span>
          <span class="w-24">-{{ formatCurrency(usedPointsAmount) }}</span>
        </div>
        <div class="flex justify-end items-center text-lg font-bold mt-4">
          <span class="mr-4">Tổng thanh toán</span>
          <span class="text-red-500 w-24">{{ formatCurrency(finalTotalAmount) }}</span>
        </div>
      </div>

      <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
        role="alert">
        <span class="block sm:inline">{{ errorMessage }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="errorMessage = null">
          <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20">
            <title>Close</title>
            <path
              d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z" />
          </svg>
        </span>
      </div>

      <div class="mt-8 pt-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center">
        <p class="text-sm text-gray-500 mb-4 sm:mb-0 sm:mr-4">
          Nhấn "Đặt hàng" đồng nghĩa với việc bạn đồng ý tuân theo
          <a href="#" class="text-blue-500 hover:underline">Điều khoản Floréa</a>
        </p>
        <button
          class="bg-red-500 text-white px-8 py-3 rounded-md font-semibold hover:bg-red-600 transition duration-200"
          :disabled="loading || !canPlaceOrder" @click="placeOrder">
          <span v-if="loading">Đang đặt hàng...</span>
          <span v-else>Đặt hàng</span>
        </button>
      </div>
    </div>

    <AddressSelectionModal :is-visible="showAddressModal" :addresses="userAddresses"
      :selected-address-id="selectedAddressId" :new-address-details="newAddressDetails"
      :use-new-address-form="useNewAddressForm" @update:is-visible="showAddressModal = $event"
      @addressSelected="handleAddressSelection" />

    <ShippingMethodSelectionModal
      :is-visible="showShippingMethodModal"
      :current-selected-method-id="selectedShippingMethodId"
      @update:is-visible="showShippingMethodModal = $event"
      @methodSelected="handleShippingMethodSelected"
    />
  </div>
</template>

<style scoped>
/* Ensure your Tailwind CSS is correctly imported/configured */
@import '@/assets/tailwind.css';

input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>