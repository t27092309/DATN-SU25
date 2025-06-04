<template>
  <div class="product-carousel flex-shrink-0 relative w-full lg:w-1/3">
    <div class="main-image-wrapper relative mb-4">
      <img :src="currentMainImage" alt="Sản phẩm chính" class="w-full h-auto rounded-lg shadow-lg object-cover max-h-96">

      <button
        @click="prevImage"
        class="absolute left-0 top-1/2 -translate-y-1/2 bg-white bg-opacity-75 rounded-full p-2 shadow-lg z-10
               hover:bg-opacity-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200
               ml-2 sm:ml-4"
        aria-label="Previous image"
      >
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
      </button>

      <button
        @click="nextImage"
        class="absolute right-0 top-1/2 -translate-y-1/2 bg-white bg-opacity-75 rounded-full p-2 shadow-lg z-10
               hover:bg-opacity-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200
               mr-2 sm:mr-4"
        aria-label="Next image"
      >
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
      </button>
    </div>

    <div class="thumbnail-container flex justify-center space-x-2 overflow-x-auto pb-2">
      <img
        v-for="(image, index) in productImages"
        :key="index"
        :src="image"
        @click="setMainImageByIndex(index)"
        :class="['thumbnail w-24 h-24 object-cover rounded-md cursor-pointer transition-all duration-300', { 'border-4 border-blue-500 shadow-md': index === currentImageIndex }]"
        alt="Ảnh sản phẩm nhỏ"
      >
    </div>
  </div>
</template>

<script setup>

import { ref, watch, computed } from 'vue';

const props = defineProps({
  productImages: {
    type: Array,
    required: true,
    default: () => []
  }
});

const currentImageIndex = ref(0);

const currentMainImage = computed(() => {
  if (props.productImages.length === 0) {
    return '';
  }
  return props.productImages[currentImageIndex.value];
});

watch(
  () => props.productImages,
  (newImages) => {
    if (newImages && newImages.length > 0) {
      currentImageIndex.value = 0;
    }
  },
  { immediate: true }
);

const setMainImageByIndex = (index) => {
  currentImageIndex.value = index;
};

const nextImage = () => {
  if (props.productImages.length === 0) return;
  currentImageIndex.value = (currentImageIndex.value + 1) % props.productImages.length;
};

const prevImage = () => {
  if (props.productImages.length === 0) return;
  currentImageIndex.value = (currentImageIndex.value - 1 + props.productImages.length) % props.productImages.length;
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
/*
  .product-carousel không cần position: relative nữa nếu các nút nằm trong .main-image-wrapper
  .main-image-wrapper cần position: relative để các nút absolute bên trong nó hoạt động đúng
*/
.main-image-wrapper {
  position: relative; /* Quan trọng: để các nút absolute bên trong nó */
  /* Có thể thêm chiều cao tối đa cho wrapper nếu muốn ảnh luôn có cùng kích thước */
  min-height: 250px; /* Ví dụ: đảm bảo wrapper có chiều cao tối thiểu */
  display: flex;
  align-items: center; /* Để căn giữa ảnh và nút dọc theo trục */
  justify-content: center;
}

.main-image-wrapper img {
  max-height: 400px;
  width: 100%; /* Đảm bảo ảnh chiếm hết chiều rộng của wrapper */
  object-fit: contain; /* Hoặc 'cover' tùy thuộc vào cách bạn muốn ảnh hiển thị */
                      /* 'contain' sẽ hiển thị toàn bộ ảnh, 'cover' sẽ cắt để lấp đầy */
}


.thumbnail-container::-webkit-scrollbar {
  height: 8px;
}

.thumbnail-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

.thumbnail-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

.thumbnail-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>