
<template>
  <router-link
    :to="{ name: 'ProductDetail', params: { slug: product.slug || product.id } }"
    class="block p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200"
  >
    <img
      :src="product.image"
      :alt="product.name"
      class="w-full h-48 object-cover rounded-t-lg"
    >
    <h5 class="text-md font-semibold mt-2">{{ product.name }}</h5>
    <p class="text-gray-700">{{ product.brand }}</p>
    <p class="text-lg font-bold text-red-600">{{ formattedPrice }}</p>
  </router-link>
</template>

<script setup>
import { computed } from 'vue';

// Props
const props = defineProps({
  product: {
    type: Object,
    required: true,
    validator: (product) => {
      return (
        'name' in product &&
        'brand' in product &&
        'price' in product &&
        'image' in product &&
        ('slug' in product || 'id' in product)
      );
    },
  },
});

// Định dạng giá sang VND
const formattedPrice = computed(() => {
  const price = parseFloat(String(props.product.price).replace(/\./g, '').replace(' VNĐ', '').trim());
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(price);
});
</script>

<style scoped>
@import '@/assets/tailwind.css';
</style>