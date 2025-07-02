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
          <span class="font-medium">Nhanh</span>
          <button class="ml-2 text-blue-500 hover:underline">Thay Đổi</button>
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
      <div class="flex items-center mb-4">
        <button class="flex items-center text-blue-500 hover:underline text-sm">
          Hoặc chọn Hóa Tốc để
          <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M10 2a8 8 0 100 16 8 8 0 000-16zM8.707 9.293a1 1 0 00-1.414 1.414L10 13.414l3.707-3.707a1 1 0 00-1.414-1.414L10 10.586l-1.293-1.293z">
            </path>
          </svg>
          <span class="font-semibold ml-1">Cam kết nhận hàng trong Hôm nay</span>
        </button>
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
        <button @click="openVoucherSelectionModal" class="text-blue-500 hover:underline">Chọn Voucher</button>
      </div>

      <VoucherSelectionModal :is-visible="showVoucherModal" :available-coupons="availableCoupons"
        :current-selected-coupon-code="selectedCouponCode" @update:is-visible="showVoucherModal = $event"
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
          <span class="mr-2">Thanh toán khi nhận hàng</span>
          <button class="text-blue-500 hover:underline">THAY ĐỔI</button>
        </div>
      </div>

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

      <div class="mt-8 pt-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center">
        <p class="text-sm text-gray-500 mb-4 sm:mb-0 sm:mr-4">
          Nhấn "Đặt hàng" đồng nghĩa với việc bạn đồng ý tuân theo
          <a href="#" class="text-blue-500 hover:underline">Điều khoản Floréa</a>
        </p>
        <button
          class="bg-red-500 text-white px-8 py-3 rounded-md font-semibold hover:bg-red-600 transition duration-200"
          :disabled="loading || !canPlaceOrder" @click="placeOrder">
          Đặt hàng
        </button>
      </div>
    </div>

    <AddressSelectionModal :is-visible="showAddressModal" :addresses="userAddresses"
      :selected-address-id="selectedAddressId" :new-address-details="newAddressDetails"
      :use-new-address-form="useNewAddressForm" @update:is-visible="showAddressModal = $event"
      @addressSelected="handleAddressSelection" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import AddressSelectionModal from '@/components/checkout/AddressSelectionModal.vue';
import VoucherSelectionModal from '@/components/checkout/VoucherSelectionModal.vue';

const api = axios;

const route = useRoute();
const router = useRouter();

// --- State variables ---
const checkoutItems = ref([]);
const message = ref('');
const usePoints = ref(false);
const userPoints = ref(0);
const isLoading = ref(false);
const loading = ref(true);
const error = ref(null);

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
const shippingFee = ref(22200);
const voucherDiscount = ref(0);
const showVoucherModal = ref(false);
const availableCoupons = ref([]);

// --- Utility Functions (giữ nguyên) ---
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

// --- Computed Properties (giữ nguyên, trừ phần totalItemsCount có thể được tính lại nếu cần) ---
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
  return (useNewAddressForm.value && hasNewAddressDetails.value) || (!useNewAddressForm.value && selectedAddressId.value !== null);
});

const estimatedDeliveryDate = computed(() => {
  const today = new Date();
  const deliveryDate = new Date(today);
  deliveryDate.setDate(today.getDate() + 2);
  const endDate = new Date(today);
  endDate.setDate(today.getDate() + 4);
  return `${deliveryDate.getDate()} Tháng ${deliveryDate.getMonth() + 1} - ${endDate.getDate()} Tháng ${endDate.getMonth() + 1}`;
});

const deliveryGuaranteeDate = computed(() => {
  const today = new Date();
  const guaranteeDate = new Date(today);
  guaranteeDate.setDate(today.getDate() + 4);
  return `${guaranteeDate.getDate()} Tháng ${guaranteeDate.getMonth() + 1} ${guaranteeDate.getFullYear()}`;
});


// --- Data Fetching Functions ---

// Hàm fetch cho luồng thanh toán từ giỏ hàng (nhiều sản phẩm)
// Ví dụ sửa trong hàm fetchCheckoutItemsFromCart:
// (Áp dụng tương tự cho fetchCheckoutItemForBuyNow)
const fetchAvailableCoupons = async () => {
  try {
    const response = await axios.get('coupons/available'); // Use your apiService here
    availableCoupons.value = response.data;
  } catch (err) {
    console.error('Error fetching available coupons:', err);
    // Handle error, e.g., show a message to the user
  }
};

const openVoucherSelectionModal = () => {
  fetchAvailableCoupons(); // Fetch coupons every time the modal is opened
  showVoucherModal.value = true;
};

const handleCouponSelection = (coupon) => {
  if (coupon) {
    // Voucher được chọn hoặc giữ nguyên
    selectedCouponCode.value = coupon.code;
    let discount = 0;
    if (coupon.discount_type === 'percent') {
      discount = (totalAmountBeforeShippingAndVoucher.value * coupon.discount_value / 100);
    } else {
      discount = coupon.discount_value;
    }
    if (coupon.max_discount && discount > coupon.max_discount) {
      discount = coupon.max_discount;
    }
    voucherDiscount.value = Math.min(discount, totalAmountBeforeShippingAndVoucher.value);
  } else {
    // Voucher bị hủy hoặc không có voucher nào được chọn
    selectedCouponCode.value = null;
    voucherDiscount.value = 0;
  }
};

async function fetchCheckoutItemsFromCart(itemIds) {
  isLoading.value = true;
  try {
    const response = await api.post('checkout/order-items', { cart_item_ids: itemIds });
    checkoutItems.value = response.data.items.map(item => {
      // Đảm bảo item.product_variant tồn tại và có attribute_values
      if (!item.product_variant || !item.product_variant.attribute_values) {
        console.warn('Dữ liệu biến thể hoặc thuộc tính không hợp lệ cho item:', item);
        return {
          // Trả về một đối tượng mặc định hoặc xử lý lỗi phù hợp
          id: item.id,
          product: item.product || { name: 'Sản phẩm không rõ' },
          quantity: item.quantity,
          variant: {
            id: item.product_variant?.id,
            name: 'Mặc định', // Fallback nếu dữ liệu không có
          },
          subtotal: item.subtotal,
        };
      }

      const variantNameParts = item.product_variant.attribute_values.map(attrValue => {
        // Sử dụng 'attribute.name' và 'value' như trong JSON response
        const attrName = attrValue.attribute?.name; // Dùng optional chaining cho an toàn
        const attrValueName = attrValue.value;      // Giá trị nằm ở trường 'value'

        if (attrName && attrValueName) {
          return `${attrName}: ${attrValueName}`;
        } else if (attrValueName) {
          return attrValueName; // Chỉ trả về giá trị nếu tên thuộc tính không có
        }
        return ''; // Trả về chuỗi rỗng nếu không có gì
      }).filter(Boolean); // Lọc bỏ các chuỗi rỗng

      const variantName = variantNameParts.length > 0 ? variantNameParts.join(' / ') : 'Mặc định';

      return {
        id: item.id,
        product: item.product, // Giữ nguyên thông tin sản phẩm
        quantity: item.quantity,
        variant: {
          id: item.product_variant.id,
          name: variantName, // <-- Chuỗi tên biến thể đã được xây dựng đúng
        },
        price: item.price,
        subtotal: item.subtotal,
        selected: item.selected,
      };
    });
  } catch (error) {
    console.error('Lỗi khi lấy chi tiết giỏ hàng:', error);
    // Xử lý lỗi (ví dụ: hiển thị thông báo cho người dùng)
  } finally {
    isLoading.value = false;
  }
}

// Ví dụ sửa trong hàm fetchCheckoutItemForBuyNow:
async function fetchCheckoutItemForBuyNow(variantId, quantity) {
  isLoading.value = true;
  error.value = null;
  try {
    // Gọi API để lấy chi tiết biến thể. API này cần trả về cả thông tin product liên quan.
    const response = await api.get(`product-variants/${variantId}`);
    const variant = response.data.data; // Đây là đối tượng biến thể đầy đủ từ backend

    if (!variant) {
      console.warn('Không tìm thấy biến thể cho ID:', variantId);
      error.value = 'Không tìm thấy thông tin biến thể.';
      isLoading.value = false;
      return;
    }

    // Xây dựng chuỗi phân loại (variant name)
    const variantNameParts = variant.attribute_values.map(attrValue => {
      const attrName = attrValue.attribute?.name;
      const attrValueName = attrValue.value; // Dùng 'value' theo cấu trúc API của bạn

      if (attrName && attrValueName) {
        return `${attrName}: ${attrValueName}`;
      } else if (attrValueName) {
        return attrValueName;
      }
      return '';
    }).filter(Boolean); // Lọc bỏ các chuỗi rỗng
    const variantName = variantNameParts.length > 0 ? variantNameParts.join(' / ') : 'Mặc định';

    // Gán dữ liệu vào checkoutItems
    checkoutItems.value = [{
      // Lấy thông tin sản phẩm (tên, hình ảnh) từ variant.product được trả về từ API
      product: {
        id: variant.product?.id,
        name: variant.product?.name,
        image: variant.product?.image, // Đảm bảo thuộc tính 'image' tồn tại trong variant.product
      },
      quantity: quantity,
      variant: {
        id: variant.id,
        name: variantName,
      },
      price: variant.price, // Giá của biến thể
      subtotal: variant.price * quantity,
      selected: true,
    }];

    console.log('Dữ liệu checkoutItems (Buy Now) sau khi gán:', checkoutItems.value);

  } catch (err) {
    console.error('Lỗi khi lấy chi tiết sản phẩm mua ngay:', err);
    error.value = 'Không thể tải chi tiết sản phẩm. Vui lòng thử lại.';
  } finally {
    isLoading.value = false;
  }
}

const fetchUserAddresses = async () => {
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
};

const fetchUserPoints = async () => {
  try {
    const response = await axios.get('/api/user/points');
    userPoints.value = response.data.points || 0;
  } catch (err) {
    console.error('Lỗi khi tải điểm của người dùng:', err);
    userPoints.value = 0;
  }
};

// --- Address Selection Handler from Modal (giữ nguyên) ---
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
};

// --- Order Placement ---
const placeOrder = async () => {
  // Clear any previous error messages
  errorMessage.value = null;

  if (checkoutItems.value.length === 0) {
    errorMessage.value = 'Vui lòng chọn sản phẩm để thanh toán.';
    return;
  }

  let addressPayload = {};
  let addressChosenSuccessfully = false;

  // Validate and set address payload
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

  // Confirmation dialog
  if (!confirm('Bạn có chắc chắn muốn đặt hàng không?')) {
    return;
  }

  // Set loading state to true to disable button and show feedback
  loading.value = true;

  try {
    const isBuyNow = route.query.buy_now === 'true';

    let orderData = {
      shipping_method: 'Nhanh',
      shipping_fee: shippingFee.value,
      message: message.value, // This maps to 'notes' on backend
      use_points: usePoints.value,
      coupon_code: selectedCouponCode.value, // Add the selected coupon code here
      ...addressPayload,
    };

    let response;
    if (isBuyNow) {
      // Logic for "Buy Now" flow
      const singleItem = checkoutItems.value[0];
      if (!singleItem || !singleItem.variant || !singleItem.variant.id) {
        errorMessage.value = 'Thông tin sản phẩm để mua ngay không hợp lệ.';
        loading.value = false;
        return;
      }
      orderData.items = [{
        product_variant_id: singleItem.variant.id,
        quantity: singleItem.quantity,
        price_at_order: singleItem.price // Record price at time of order
      }];
      response = await axios.post('/api/checkout/buy-now', orderData);
    } else {
      // Logic for standard cart checkout flow
      orderData.cart_item_ids = checkoutItems.value.map(item => item.id); // Send cart item IDs
      response = await axios.post('/api/checkout/place-order', orderData);

      // Important: Clear selected cart items after successful order
      await axios.delete('/api/cart-items/clear-selected', { data: { cart_item_ids: orderData.cart_item_ids } });
    }

    // Show success message and redirect
    alert('Đặt hàng thành công! Mã đơn hàng: ' + response.data.order_id);
    router.push({ name: 'order-success', params: { orderId: response.data.order_id } });

  } catch (err) {
    console.error('Lỗi khi đặt hàng:', err);
    if (err.response && err.response.data && err.response.data.message) {
      errorMessage.value = `Lỗi đặt hàng: ${err.response.data.message}`;
    } else {
      errorMessage.value = 'Không thể đặt hàng. Vui lòng thử lại.';
    }
  } finally {
    loading.value = false; // Always set loading to false after the request
  }
};


// --- Lifecycle Hook ---
onMounted(async () => {
  const isBuyNow = route.query.buy_now === 'true';
  const variantId = route.query.variant_id;
  const quantity = parseInt(route.query.qty);

  if (isBuyNow && variantId && quantity) {
    // Luồng "Mua ngay"
    await fetchCheckoutItemForBuyNow(variantId, quantity);
  } else {
    // Luồng thanh toán từ giỏ hàng
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

  // Luôn tải địa chỉ và điểm của người dùng
  await Promise.all([
    fetchUserAddresses(),
    fetchUserPoints()
  ]);
});

// Watcher to handle initial state if no addresses are fetched
watch(userAddresses, (newAddresses) => {
  if (newAddresses.length === 0 && !selectedAddressId.value) {
    useNewAddressForm.value = true;
  }
}, { immediate: true });
</script>

<style scoped>
/* Ensure your Tailwind CSS is correctly imported/configured */
@import '@/assets/tailwind.css';

input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>