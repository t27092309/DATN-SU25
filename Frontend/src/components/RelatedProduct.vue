<template>
  <div class="max-w-7xl mx-auto p-4">
    <h2 class="text-xl font-bold mb-6 uppercase">Nước hoa có cùng mùi hương</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 mb-12">
      <div v-for="product in sameScentProducts" :key="product.id" class="flex flex-col items-center text-center p-2">
        <div class="relative w-full mb-2">
          <span v-if="product.discount" class="absolute top-0 left-0 bg-red-500 text-white text-xs px-2 py-1 rounded-br-lg z-10">
            -{{ product.discount }}%
          </span>
          <img :src="product.imageUrl" :alt="product.name" class="w-full h-auto object-contain">
        </div>
        <p class="text-sm text-gray-500">{{ product.volume }}</p>
        <p class="font-bold text-sm uppercase mt-1">{{ product.brand }}</p>
        <p class="text-sm mb-2">{{ product.name }}</p>
        <div class="flex flex-col items-center">
          <span class="text-red-500 font-semibold">{{ product.currentPrice.toLocaleString('vi-VN') }}đ</span>
          <span v-if="product.oldPrice" class="text-gray-500 text-xs line-through">{{ product.oldPrice.toLocaleString('vi-VN') }}đ</span>
        </div>
      </div>
    </div>

    <h2 class="text-xl font-bold mb-6 uppercase">Có thể bạn thích...</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
      <div v-for="product in recommendedProducts" :key="product.id" class="flex flex-col items-center text-center p-2">
        <div class="relative w-full mb-2">
          <span v-if="product.discount" class="absolute top-0 left-0 bg-red-500 text-white text-xs px-2 py-1 rounded-br-lg z-10">
            -{{ product.discount }}%
          </span>
          <img :src="product.imageUrl" :alt="product.name" class="w-full h-auto object-contain">
        </div>
        <p class="text-sm text-gray-500">{{ product.volume }}</p>
        <p class="font-bold text-sm uppercase mt-1">{{ product.brand }}</p>
        <p class="text-sm mb-2">{{ product.name }}</p>
        <div class="flex flex-col items-center">
          <span class="text-red-500 font-semibold">{{ product.currentPrice.toLocaleString('vi-VN') }}đ</span>
          <span v-if="product.oldPrice" class="text-gray-500 text-xs line-through">{{ product.oldPrice.toLocaleString('vi-VN') }}đ</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const sameScentProducts = ref([
  {
    id: 1,
    imageUrl: 'https://via.placeholder.com/150/FF69B4/FFFFFF?text=Narciso', // Placeholder
    discount: 41,
    volume: '100 ML',
    brand: 'NARCISO RODRIGUEZ',
    name: 'Narciso Rodriguez For Her Fleur Mu...',
    oldPrice: 3500000,
    currentPrice: 2080000,
  },
  {
    id: 2,
    imageUrl: 'https://via.placeholder.com/150/FFB6C1/FFFFFF?text=Narciso2', // Placeholder
    discount: 14,
    volume: '30 ML/90ML',
    brand: 'NARCISO RODRIGUEZ',
    name: 'Narciso Rodriguez For Her EDP Chiết',
    oldPrice: 3800000,
    currentPrice: 3280000,
  },
  {
    id: 3,
    imageUrl: 'https://via.placeholder.com/150/F0E68C/FFFFFF?text=Chanel', // Placeholder
    discount: 12,
    volume: '100 ML',
    brand: 'CHANEL',
    name: 'Chanel Gabrielle Essence EDP',
    oldPrice: 4650000,
    currentPrice: 4080000,
  },
  {
    id: 4,
    imageUrl: 'https://via.placeholder.com/150/36454F/FFFFFF?text=CK', // Placeholder
    discount: 67,
    volume: '100 ML/200ML',
    brand: 'CALVIN KLEIN',
    name: 'Calvin Klein CK BE EDT',
    oldPrice: 1740000,
    currentPrice: 580000,
  },
  {
    id: 5,
    imageUrl: 'https://via.placeholder.com/150/8A2BE2/FFFFFF?text=Bvlgari', // Placeholder
    discount: 16,
    volume: '65 ML',
    brand: 'BVLGARI',
    name: 'Bvlgari Omnia Amethyste EDT',
    oldPrice: 2860000,
    currentPrice: 2380000,
  },
]);

const recommendedProducts = ref([
  {
    id: 6,
    imageUrl: 'https://via.placeholder.com/150/000000/FFFFFF?text=TomFord', // Placeholder
    discount: 16,
    volume: '100 ML',
    brand: 'TOM FORD',
    name: 'Tom Ford Black Orchid EDP',
    oldPrice: 4200000,
    currentPrice: 3580000,
  },
  {
    id: 7,
    imageUrl: 'https://via.placeholder.com/150/F8F8FF/000000?text=Chloe', // Placeholder
    discount: 50,
    volume: '75 ML',
    brand: 'CHLOÉ',
    name: 'Chloe Love Story EDP',
    oldPrice: 3840000,
    currentPrice: 1980000,
  },
  {
    id: 8,
    imageUrl: 'https://via.placeholder.com/150/F08080/FFFFFF?text=Lancome', // Placeholder
    discount: 31,
    volume: '100 ML',
    brand: 'LANCÔME',
    name: 'Lancôme La Vie Est Belle EDP',
    oldPrice: 3800000,
    currentPrice: 2580000,
  },
  {
    id: 9,
    imageUrl: 'https://via.placeholder.com/150/FFDEAD/FFFFFF?text=Chanel2', // Placeholder
    discount: 19,
    volume: '100 ML/200 ML',
    brand: 'CHANEL',
    name: 'Chanel Coco Mademoiselle EDP',
    oldPrice: 5290000,
    currentPrice: 4260000,
  },
  {
    id: 10,
    imageUrl: 'https://via.placeholder.com/150/F08080/FFFFFF?text=Chanel3', // Placeholder
    discount: 5,
    volume: '100 ML',
    brand: 'CHANEL',
    name: 'Chanel Chance Eau Tendre EDP',
    oldPrice: 4590000,
    currentPrice: 4380000,
  },
]);
</script>

<style scoped>
@import '@/assets/tailwind.css';

/* Không cần CSS tùy chỉnh ở đây vì đã sử dụng Tailwind CSS */
</style>