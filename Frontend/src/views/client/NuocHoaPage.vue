<template>
  <div class="p-6">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-500 mb-4">
      Trang chủ / <span class="text-black font-medium">Nước Hoa</span>
    </div>

    <!-- Logo thương hiệu -->
    <div class="flex overflow-x-auto space-x-6 pb-4 border-b">
      <img v-for="brand in brands" :key="brand" :src="brand" class="h-10 object-contain" />
    </div>

    <!-- Bộ lọc -->
    <div class="flex flex-wrap gap-4 mt-6">
      <button class="border px-4 py-2 rounded-md">Nhóm Hương</button>
      <button class="border px-4 py-2 rounded-md">Nam / Nữ</button>
      <button class="border px-4 py-2 rounded-md">DƯỚI 2 TRIỆU</button>
      <button class="border px-4 py-2 rounded-md">2 - 4 TRIỆU</button>
      <button class="border px-4 py-2 rounded-md">TRÊN 4 TRIỆU</button>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-8">
      <div v-for="item in products" :key="item.name" class="border p-4 rounded-md relative">
        <span class="absolute top-2 left-2 bg-red-100 text-red-600 text-xs px-2 py-1 rounded">-{{ item.discount }}%</span>

        <img :src="item.image" alt="" class="h-40 mx-auto object-contain" />
        <h3 class="mt-4 font-semibold uppercase text-sm">{{ item.brand }}</h3>
        <p class="text-gray-600 text-sm">{{ item.name }}</p>

        <div class="flex gap-2 mt-2 text-xs">
          <button v-for="ml in item.ml" :key="ml" class="border px-2 py-1 rounded">
            {{ ml }} ML
          </button>
        </div>

        <div class="mt-2">
          <p class="line-through text-gray-400 text-sm">{{ formatCurrency(item.oldPrice) }}</p>
          <p class="text-red-600 font-semibold">{{ formatCurrency(item.newPrice) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const brands = [
  '/images/dior.png',
  '/images/chanel.png',
  '/images/versace.png',
  '/images/gucci.png',
  // thêm ảnh logo khác nếu cần
]

const products = [
  {
    brand: 'AFNAN',
    name: '9AM EDP',
    ml: ['100'],
    image: '/images/afnan.png',
    discount: 31,
    oldPrice: 1280000,
    newPrice: 880000,
  },
  {
    brand: 'CHANEL',
    name: 'Bleu De Chanel EDP',
    ml: ['50', '100', '150'],
    image: '/images/chanel_bleu.png',
    discount: 21,
    oldPrice: 4200000,
    newPrice: 3780000,
  },
  // thêm sản phẩm khác...
]

const formatCurrency = (value) => {
  return value.toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })
}
</script>

<style scoped>
/* Tuỳ chọn thêm hiệu ứng cuộn ngang nếu cần */
</style>
