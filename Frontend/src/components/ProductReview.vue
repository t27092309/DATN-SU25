<template>
  <div class="max-w-4xl  p-4">
    <h2 class="text-2xl font-bold mb-6">Đánh giá & Hỏi đáp về sản phẩm</h2>

    <p class="mb-4">9 đánh giá cho Narciso Rodriguez For Her EDP</p>

    <div class="bg-gray-100 p-4 rounded-lg mb-6 flex items-center justify-between">
      <div class="flex items-center">
        <span class="text-5xl font-bold text-orange-500 mr-2">4.9</span>
        <div class="flex flex-col">
          <div class="flex">
            <svg class="w-6 h-6 text-orange-500 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z"/></svg>
            <svg class="w-6 h-6 text-orange-500 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z"/></svg>
            <svg class="w-6 h-6 text-orange-500 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z"/></svg>
            <svg class="w-6 h-6 text-orange-500 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z"/></svg>
            <svg class="w-6 h-6 text-orange-500 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z"/></svg>
          </div>
          <span class="text-sm text-gray-600">ĐÁNH GIÁ TRUNG BÌNH</span>
        </div>
      </div>

      <div class="flex-grow ml-8">
        <div class="flex items-center mb-1">
          <span class="w-8 text-right text-sm">5★</span>
          <div class="flex-grow bg-gray-300 h-2 rounded-full mx-2">
            <div class="bg-orange-500 h-2 rounded-full" style="width: 88%;"></div>
          </div>
          <span class="text-sm">88% | 8 đánh giá</span>
        </div>
        <div class="flex items-center mb-1">
          <span class="w-8 text-right text-sm">4★</span>
          <div class="flex-grow bg-gray-300 h-2 rounded-full mx-2">
            <div class="bg-orange-500 h-2 rounded-full" style="width: 11%;"></div>
          </div>
          <span class="text-sm">11% | 1 đánh giá</span>
        </div>
        <div class="flex items-center mb-1">
          <span class="w-8 text-right text-sm">3★</span>
          <div class="flex-grow bg-gray-300 h-2 rounded-full mx-2">
            <div class="bg-gray-300 h-2 rounded-full" style="width: 0%;"></div>
          </div>
          <span class="text-sm">0% | 0 đánh giá</span>
        </div>
        <div class="flex items-center mb-1">
          <span class="w-8 text-right text-sm">2★</span>
          <div class="flex-grow bg-gray-300 h-2 rounded-full mx-2">
            <div class="bg-gray-300 h-2 rounded-full" style="width: 0%;"></div>
          </div>
          <span class="text-sm">0% | 0 đánh giá</span>
        </div>
        <div class="flex items-center">
          <span class="w-8 text-right text-sm">1★</span>
          <div class="flex-grow bg-gray-300 h-2 rounded-full mx-2">
            <div class="bg-gray-300 h-2 rounded-full" style="width: 0%;"></div>
          </div>
          <span class="text-sm">0% | 0 đánh giá</span>
        </div>
      </div>
      <button class="ml-8 px-6 py-3 bg-black text-white rounded-md text-lg font-semibold whitespace-nowrap">ĐÁNH GIÁ NGAY</button>
    </div>

    <div class="space-y-6">
      <div v-for="review in reviews" :key="review.id" class="border-b pb-4 last:border-b-0">
        <div class="flex items-center mb-2">
          <p class="font-semibold mr-2">{{ review.author }}</p>
          <span v-if="review.isVerified" class="text-green-600 text-sm flex items-center">
            <svg class="w-4 h-4 mr-1 fill-current" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z"/></svg>
            Khách hàng của orchard.vn
          </span>
        </div>
        <div class="flex mb-2">
          <svg v-for="n in review.rating" :key="n" class="w-5 h-5 text-orange-500 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z"/></svg>
        </div>
        <p class="mb-2">{{ review.comment }}</p>
        <button class="text-blue-600 text-sm">Trả lời</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const reviews = ref([
  {
    id: 1,
    author: 'Trần Thị Thanh Tâm',
    isVerified: true,
    rating: 5,
    comment: '', // Để trống vì ảnh không có nội dung
  },
  {
    id: 2,
    author: 'Trần Thị Ngọc Mai',
    isVerified: true,
    rating: 5,
    comment: 'Giá cả phải chăng',
  },
  {
    id: 3,
    author: 'Nguyễn Thị Thanh Hằng',
    isVerified: true,
    rating: 5,
    comment: 'sp tốt lắm',
  },
  {
    id: 4,
    author: 'Lê Phan Anh Thư',
    isVerified: true,
    rating: 5,
    comment: 'Ungggggg',
  },
  // Thêm các đánh giá khác nếu có
]);
</script>

<style scoped>
@import '@/assets/tailwind.css';

/* Không cần CSS tùy chỉnh ở đây vì đã sử dụng Tailwind CSS */
</style>