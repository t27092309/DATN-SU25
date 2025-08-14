<template>
  <div v-if="visible" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 relative">
      <button @click="$emit('close')" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition-colors">
        <i class="fas fa-times"></i>
      </button>
      <h3 class="text-2xl font-bold mb-4 text-gray-800">Đánh giá sản phẩm</h3>

      <div class="flex items-center mb-4">
        <img :src="productImage" :alt="productName" class="w-16 h-16 object-cover rounded mr-4">
        <p class="font-semibold">{{ productName }}</p>
      </div>

      <form @submit.prevent="submitReview">
        <div class="mb-4">
          <label for="rating" class="block text-gray-700 font-medium mb-2">Đánh giá của bạn</label>
          <div class="flex items-center space-x-1">
            <i v-for="star in 5" :key="star"
               :class="['fas fa-star cursor-pointer transition-colors', star <= rating ? 'text-yellow-400' : 'text-gray-300']"
               @click="rating = star"></i>
          </div>
        </div>

        <div class="mb-4">
          <label for="comment" class="block text-gray-700 font-medium mb-2">Nhận xét chi tiết</label>
          <textarea id="comment" v-model="comment" rows="4"
                    class="w-full border rounded-md p-2 focus:ring-red-500 focus:border-red-500 resize-none"
                    placeholder="Hãy chia sẻ cảm nhận của bạn về sản phẩm..."></textarea>
        </div>

        <button type="submit"
                :disabled="isSubmitting"
                class="w-full py-2 px-4 bg-red-600 text-white font-bold rounded-md hover:bg-red-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
          <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
          Gửi đánh giá
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const api = axios;

const props = defineProps({
  visible: { type: Boolean, required: true },
  orderItemId: { type: [Number, null], required: true },
  productName: { type: String, required: true },
  productImage: { type: String, required: true },
});

const emit = defineEmits(['close', 'submitted']);

const rating = ref(0);
const comment = ref('');
const isSubmitting = ref(false);

// Reset form khi popup hiển thị
watch(() => props.visible, (newVal) => {
  if (newVal) {
    rating.value = 0;
    comment.value = '';
    isSubmitting.value = false;
  }
});

const submitReview = async () => {
  if (!rating.value || !comment.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Thiếu thông tin!',
      text: 'Vui lòng điền đầy đủ cả điểm số và nhận xét.',
    });
    return;
  }

  isSubmitting.value = true;
  try {
    const response = await api.post('/reviews', {
      order_item_id: props.orderItemId,
      rating: rating.value,
      comment: comment.value,
    });
    Swal.fire('Thành công!', response.data.message, 'success');
    emit('submitted'); // Phát ra sự kiện để component cha xử lý
  } catch (err) {
    console.error('Lỗi khi gửi đánh giá:', err);
    Swal.fire({
      icon: 'error',
      title: 'Lỗi!',
      text: err.response?.data?.message || 'Không thể gửi đánh giá. Vui lòng thử lại.',
    });
  } finally {
    isSubmitting.value = false;
  }
};
</script>