<template>
  <div class="product-carousel flex-shrink-0 relative w-full lg:w-1/3">
    <div class="main-image-wrapper relative mb-4">
      <img :src="currentMainImage" :key="currentImageIndex" alt="Sản phẩm chính"
        class="w-full h-auto rounded-lg shadow-lg object-contain max-h-96 transition-opacity duration-300 ease-in-out">

      <button @click="prevImage" class="absolute left-0 top-1/2 -translate-y-1/2 bg-white bg-opacity-75 rounded-full p-2 shadow-lg z-10
               hover:bg-opacity-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200
               ml-2 sm:ml-4" aria-label="Previous main image">
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
      </button>

      <button @click="nextImage" class="absolute right-0 top-1/2 -translate-y-1/2 bg-white bg-opacity-75 rounded-full p-2 shadow-lg z-10
               hover:bg-opacity-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200
               mr-2 sm:mr-4" aria-label="Next main image">
        <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>
    </div>

    <div ref="thumbnailContainer"
      class="thumbnail-container flex space-x-2 overflow-x-auto pb-2 scroll-smooth">
      <img v-for="(image, index) in productImages" :key="index" :src="image" @click="setMainImageByIndex(index)"
        :class="['thumbnail flex-shrink-0 w-24 h-24 object-cover rounded-md cursor-pointer transition-all duration-300', { 'border-4 border-blue-500 shadow-md': index === currentImageIndex }]"
        alt="Ảnh sản phẩm nhỏ">
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick, onMounted } from 'vue';

const props = defineProps({
  productImages: {
    type: Array,
    required: true,
    default: () => []
  }
});

const currentImageIndex = ref(0);
const thumbnailContainer = ref(null);

const currentMainImage = computed(() => {
  if (props.productImages.length === 0) {
    return 'https://via.placeholder.com/600x600.png?text=No+Image';
  }
  return props.productImages[currentImageIndex.value];
});

watch(
  () => props.productImages,
  (newImages) => {
    if (newImages && newImages.length > 0) {
      currentImageIndex.value = 0;
      nextTick(() => {
        scrollToCurrentThumbnail();
      });
    }
  },
  { immediate: true }
);

watch(currentImageIndex, () => {
  nextTick(() => {
    scrollToCurrentThumbnail();
  });
});

onMounted(() => {
  if (props.productImages.length > 0) {
    nextTick(() => {
      scrollToCurrentThumbnail();
    });
  }
});

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

const scrollToCurrentThumbnail = () => {
  if (thumbnailContainer.value && currentImageIndex.value !== -1) {
    const activeThumbnail = thumbnailContainer.value.children[currentImageIndex.value];
    if (activeThumbnail) {
      activeThumbnail.scrollIntoView({
        behavior: 'smooth',
        inline: 'center',
        block: 'nearest'
      });
    }
  }
};
</script>

<style scoped>
.main-image-wrapper {
  position: relative;
  min-height: 250px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: #f9f9f9;
}

.main-image-wrapper img {
  max-height: 400px;
  width: 100%;
  object-fit: contain;
}

.thumbnail-container {
  overflow-x: auto;
  scroll-behavior: smooth;
  padding-bottom: 8px;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;

  /* Đảm bảo flex container này chiếm đủ chiều rộng */
  width: 100%;
  /* Bỏ justify-center/justify-start để flexbox mặc định từ trái qua */
  /* Thay vì justify-start, chúng ta sẽ dùng padding-left cho ảnh đầu tiên nếu cần */
  padding-left: 0; /* Đảm bảo không có padding không mong muốn */
  padding-right: 0; /* Đảm bảo không có padding không mong muốn */
}

/* Các thuộc tính cho từng ảnh thumbnail */
.thumbnail {
  width: 96px; /* Kích thước cố định */
  height: 96px;
  flex-shrink: 0; /* Quan trọng để ảnh không bị co lại */
  scroll-snap-align: start; /* THAY ĐỔI: Snap vào cạnh BẮT ĐẦU */
}

/* Tùy chỉnh scrollbar */
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
