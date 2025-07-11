<template>
    <div v-if="isVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-600 bg-opacity-75">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-auto relative">
            <h3 class="text-xl font-semibold mb-4 border-b pb-2">Chọn phương thức thanh toán</h3>

            <button @click="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <div class="space-y-4">
                <div v-for="method in internalPaymentMethods" :key="method.code" @click="selectMethod(method.code)"
                    :class="[
                        'flex items-center p-3 border rounded-md cursor-pointer transition duration-150 ease-in-out',
                        method.code === currentSelectedMethod ? 'border-blue-500 ring-2 ring-blue-200 bg-blue-50' : 'border-gray-300 hover:border-blue-400'
                    ]">
                    <input type="radio" :id="`payment-${method.code}`" :value="method.code"
                        :checked="method.code === currentSelectedMethod" class="form-radio h-4 w-4 text-blue-600 mr-3"
                        @change="selectMethod(method.code)" />
                    <label :for="`payment-${method.code}`" class="flex-grow flex items-center cursor-pointer">
                        <span class="font-medium text-gray-800">{{ method.name }}</span>
                        <img v-if="method.icon_url" :src="method.icon_url" :alt="method.name" class="h-6 w-auto ml-2" />
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button @click="closeModal"
                    class="px-5 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Hủy
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    isVisible: {
        type: Boolean,
        default: false,
    },
    currentSelectedMethod: {
        type: String,
        default: 'cash',
    },
});

const emit = defineEmits(['update:isVisible', 'methodSelected']);

// Các phương thức thanh toán có sẵn. Bạn có thể fetch từ API nếu cần động hơn.
// Đã thêm icon_url để bạn có thể hiển thị logo Momo/VNPay
const internalPaymentMethods = ref([
    { code: 'cash', name: 'Thanh toán khi nhận hàng (COD)', icon_url: null },
    { code: 'momo', name: 'Momo', icon_url: '/images/momo_logo.png' }, // Thay bằng đường dẫn ảnh thực tế
    { code: 'vnpay', name: 'VNPay', icon_url: '/images/vnpay_logo.png' }, // Thay bằng đường dẫn ảnh thực tế
]);

const selectMethod = (methodCode) => {
    emit('methodSelected', methodCode);
    closeModal();
};

const closeModal = () => {
    emit('update:isVisible', false);
};

// Đóng modal khi nhấn Esc
watch(() => props.isVisible, (newVal) => {
    if (newVal) {
        document.addEventListener('keydown', handleEscape);
    } else {
        document.removeEventListener('keydown', handleEscape);
    }
});

const handleEscape = (e) => {
    if (e.key === 'Escape') {
        closeModal();
    }
};
</script>
<style scoped>
@import '@/assets/tailwind.css';
</style>