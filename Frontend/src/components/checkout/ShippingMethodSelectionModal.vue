<template>
  <div v-if="isVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-600 bg-opacity-75"
       @click.self="closeModalAndApplyTemporary"> <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="flex justify-between items-center mb-4 border-b pb-2">
        <h3 class="text-xl font-semibold text-gray-800">Chọn Phương Thức Vận Chuyển</h3>
        <button @click="closeModalAndApplyTemporary" class="text-gray-500 hover:text-gray-700"> <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <div v-if="loading" class="text-center py-4 text-gray-600">
        Đang tải phương thức vận chuyển...
      </div>
      <div v-else-if="error" class="text-center py-4 text-red-600">
        {{ error }}
      </div>
      <div v-else-if="shippingMethods.length === 0" class="text-center py-4 text-gray-600">
        Không có phương thức vận chuyển nào khả dụng.
      </div>
      <div v-else class="space-y-3">
        <div v-for="method in shippingMethods" :key="method.id"
          class="flex items-center justify-between p-3 border rounded-md cursor-pointer hover:bg-gray-50 transition"
          :class="{ 'border-blue-500 bg-blue-50': temporarySelectedMethodId === method.id }"
          @click="temporarySelectedMethodId = method.id"> <label class="flex items-center flex-grow cursor-pointer">
            <input type="radio" :value="method.id" v-model="temporarySelectedMethodId" class="form-radio h-4 w-4 text-blue-600" />
            <div class="ml-3">
              <p class="font-medium text-gray-800">{{ method.name }}</p>
              <p class="text-sm text-gray-600">
                Dự kiến: {{ method.delivery_time_min }} - {{ method.delivery_time_max }} {{ method.delivery_time_unit }}
              </p>
            </div>
          </label>
          <span class="font-semibold text-gray-700">
            {{ formatCurrency(method.price) }}
          </span>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-3">
        <button @click="cancelSelection" class="px-5 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-100 transition">
          Hủy
        </button>
        <button @click="confirmSelection" :disabled="!temporarySelectedMethodId"
          class="px-5 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
          Xác nhận
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  currentSelectedMethodId: { // Phương thức hiện tại đang được chọn từ component cha
    type: Number,
    default: null,
  },
});

const emit = defineEmits(['update:isVisible', 'methodSelected']);

const shippingMethods = ref([]);
const temporarySelectedMethodId = ref(null); // Lưu lựa chọn tạm thời trong modal
const loading = ref(false);
const error = ref(null);

const fetchShippingMethods = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/shipping-methods');
    shippingMethods.value = response.data.shipping_methods;
  } catch (err) {
    console.error('Error fetching shipping methods:', err);
    error.value = 'Không thể tải các phương thức vận chuyển. Vui lòng thử lại.';
  } finally {
    loading.value = false;
  }
};

const formatCurrency = (amount) => {
  if (amount === null || amount === undefined || isNaN(amount)) {
    return '₫0';
  }
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(amount);
};

// Hàm xử lý khi nhấn "Xác nhận"
const confirmSelection = () => {
  emit('methodSelected', temporarySelectedMethodId.value); // Gửi lựa chọn tạm thời về cha
  emit('update:isVisible', false); // Đóng modal
};

// Hàm xử lý khi nhấn "Hủy"
const cancelSelection = () => {
  // KHÔNG emit 'methodSelected' ở đây.
  // Điều này đảm bảo rằng component cha vẫn giữ nguyên giá trị của `selectedShippingMethodId`
  // là `props.currentSelectedMethodId` ban đầu khi modal được mở.
  emit('update:isVisible', false); // Chỉ đóng modal
};

// Hàm xử lý khi nhấn nút 'X' hoặc click ra ngoài
const closeModalAndApplyTemporary = () => {
  // Emit lựa chọn tạm thời và đóng modal
  emit('methodSelected', temporarySelectedMethodId.value);
  emit('update:isVisible', false);
};

// Theo dõi prop isVisible: Khi modal mở, cập nhật lựa chọn tạm thời
watch(() => props.isVisible, (newValue) => {
  if (newValue) {
    fetchShippingMethods();
    // Khi modal mở, khởi tạo lựa chọn tạm thời bằng lựa chọn hiện tại từ props
    temporarySelectedMethodId.value = props.currentSelectedMethodId;
  }
});

onMounted(() => {
    // Nếu modal được mount và đã hiển thị ngay từ đầu (ít xảy ra trong trường hợp modal bật/tắt)
    if (props.isVisible) {
        fetchShippingMethods();
    }
});
</script>

<style scoped>
@import '@/assets/tailwind.css';

input[type="radio"].form-radio:checked {
  background-color: theme('colors.blue.600');
  border-color: theme('colors.blue.600');
}
/* Adjust styles for selected item to ensure text is readable */
.border-blue-500.bg-blue-50 label p,
.border-blue-500.bg-blue-50 span {
  color: theme('colors.gray.800'); /* Or a specific color like blue.800 */
}
</style>