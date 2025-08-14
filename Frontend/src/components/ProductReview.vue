<template>
  <div class="max-w-4xl p-4">
    <h2 class="text-2xl font-bold mb-6">Đánh giá & Hỏi đáp về sản phẩm</h2>

    <div v-if="loading" class="text-center text-gray-500 my-8">Đang tải đánh giá...</div>
    <div v-else-if="error" class="text-center text-red-500 my-8">{{ error }}</div>
    <div v-else-if="reviews.length > 0">
      <p class="mb-4">{{ totalReviews }} đánh giá cho sản phẩm</p>

      <div class="bg-gray-100 p-4 rounded-lg mb-6 flex items-center justify-between">
        <div class="flex items-center">
          <span class="text-5xl font-bold text-orange-500 mr-2">{{ averageRating }}</span>
          <div class="flex flex-col">
            <div class="flex">
              <svg v-for="n in 5" :key="n" :class="[
                'w-6 h-6 fill-current',
                n <= Math.round(averageRating) ? 'text-orange-500' : 'text-gray-300'
              ]" viewBox="0 0 24 24">
                <path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z" />
              </svg>
            </div>
            <span class="text-sm text-gray-600">ĐÁNH GIÁ TRUNG BÌNH</span>
          </div>
        </div>

        <div class="flex-grow ml-8">
          <div v-for="rating in 5" :key="rating" class="flex items-center mb-1">
            <span class="w-8 text-right text-sm">{{ 6 - rating }}★</span>
            <div class="flex-grow bg-gray-300 h-2 rounded-full mx-2">
              <div class="bg-orange-500 h-2 rounded-full"
                :style="{ width: `${getRatingPercentage(6 - rating)}%` }"></div>
            </div>
            <span class="text-sm">{{ getRatingCount(6 - rating) }} đánh giá</span>
          </div>
        </div>

        <!-- <button class="ml-8 px-6 py-3 bg-black text-white rounded-md text-lg font-semibold whitespace-nowrap">ĐÁNH GIÁ
          NGAY</button> -->
      </div>

      <div class="space-y-6">
        <div v-for="review in reviews" :key="review.id" class="border-b pb-4 last:border-b-0">
          <div class="flex items-center mb-2">
            <p class="font-semibold mr-2">{{ review.user.name }}</p>
            <span class="text-green-600 text-sm flex items-center">
              <svg class="w-4 h-4 mr-1 fill-current" viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" />
              </svg>
              Khách hàng đã mua
            </span>
          </div>
          <div class="flex mb-2">
            <svg v-for="n in 5" :key="n" :class="[
              'w-5 h-5 fill-current',
              n <= review.rating ? 'text-orange-500' : 'text-gray-300'
            ]" viewBox="0 0 24 24">
              <path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z" />
            </svg>
          </div>
          <p class="mb-2">{{ review.comment }}</p>
          <!-- <button class="text-blue-600 text-sm">Trả lời</button> -->
        </div>
      </div>
    </div>
    <div v-else class="text-center text-gray-500 my-8">
      Sản phẩm này chưa có đánh giá nào.
    </div>
  </div>
</template>
// Tệp: src/components/ProductReview.vue
<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const reviews = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchReviews = async (slug) => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`http://localhost:8000/api/products/${slug}/reviews`);
    reviews.value = response.data.reviews;
  } catch (err) {
    console.error('Lỗi khi lấy đánh giá:', err);
    error.value = 'Không thể tải đánh giá sản phẩm. Vui lòng thử lại sau.';
  } finally {
    loading.value = false;
  }
};

const totalReviews = computed(() => reviews.value.length);
const averageRating = computed(() => {
  if (totalReviews.value === 0) return 0;
  const sum = reviews.value.reduce((total, review) => total + review.rating, 0);
  return (sum / totalReviews.value).toFixed(1);
});

const getRatingCount = (rating) => {
  return reviews.value.filter(review => review.rating === rating).length;
};

const getRatingPercentage = (rating) => {
  if (totalReviews.value === 0) return 0;
  return (getRatingCount(rating) / totalReviews.value) * 100;
};

// Theo dõi slug từ route và gọi API khi thay đổi
watch(() => route.params.slug, (newSlug) => {
  if (newSlug) {
    fetchReviews(newSlug);
  }
}, { immediate: true });
</script>