<template>
  <div class="container mx-auto p-4 max-w-[1200px]">
    <div class="flex flex-col md:flex-row gap-8 mt-5">
      <BrandList :brands="brands" :selected-brand="selectedBrand" @select-brand="handleSelectBrand" />
      <div class="flex-1">
        <div class="mb-8">
          <ProductFilters :priceRanges="priceRanges" :selectedPriceRange="selectedPriceRange"
            :aromaOptions="aromaOptions" :selectedAromas="selectedAromas" @select-price-range="handleSelectPriceRange"
            @select-aroma="handleSelectAroma" ref="productFilters" />
        </div>
        <div class="bg-gray-100 p-6 rounded-lg min-h-[300px]">
          <p v-if="selectedPriceRange">Phạm vi giá đã chọn: **{{ selectedPriceRange }}**</p>
          <div v-if="selectedAromas.length > 0" class="mb-2">
            <p class="font-medium">Nhóm Hương:</p>
            <div class="flex flex-wrap gap-2 mt-1">
              <span v-for="aroma in selectedAromas" :key="aroma"
                class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">
                {{ aroma }}
              </span>
            </div>
          </div>
          <div v-if="loading" class="text-center py-4">Đang tải...</div>
          <div v-else-if="error" class="error text-red-600 text-center py-4">{{ error }}</div>
          <div v-else-if="products.length"
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
            <router-link v-for="product in products" :key="product.slug || product.id"
              :to="{ name: 'ProductDetail', params: { slug: product.slug || product.id } }"
              class="block p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
              <img :src="product.image" :alt="product.name" class="w-full h-48 object-cover rounded-t-lg">
              <h5 class="text-md font-semibold mt-2">{{ product.name }}</h5>
              <p class="text-gray-700">{{ product.brand }}</p>
              <p class="text-lg font-bold text-red-600">{{ product.price }} VNĐ</p>
            </router-link>
          </div>
          <p v-else class="text-center py-4">Không có sản phẩm nào trong danh mục này.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted, defineProps, watch } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import axios from 'axios';
  import BrandList from '@/components/BrandList.vue';
  import ProductFilters from '@/components/ProductFilter.vue';

  // Props
  const props = defineProps(['categorySlug']); // Đổi từ 'categoryName' sang 'categorySlug'

  // State
  const products = ref([]); // Danh sách sản phẩm HIỂN THỊ (đã lọc)
  const allProducts = ref([]); // Danh sách sản phẩm GỐC của category hiện tại (sau khi tìm từ data API)
  const loading = ref(false);
  const error = ref(null);
  const router = useRouter();

  const brands = ref([
    { name: 'BVLGARI', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-bvlgary.webp' },
    { name: 'Parfums', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/04/logo-brand-calvin-klein.webp' },
    { name: 'CHANEL', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-chanel.webp' },
    { name: 'DIOR', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-dior.webp' },
    { name: 'GIORGIO ARMANI', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/04/logo-brand-giorgio-armani.webp' },
    { name: 'GUCCI', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-gucci.webp' },
    { name: 'Jean Paul GAULTIER', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jean-paul-gaultier.webp' },
    { name: 'Jo Malone', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jo-malone.webp' },
    { name: 'Kilian', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-kilian.webp' },
    { name: 'LANCÔME', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-lancome.webp' },
    { name: 'MONTBLANC', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-montblanc.webp' },
    { name: 'narciso rodriguez', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/04/logo-brand-narciso-rodriguez.webp' },
    { name: 'PARFUMS DE MARLY', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-parfums-de-marly.webp' },
    { name: 'TOM FORD', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-tom-ford.webp' },
    { name: 'VERSACE', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-versace.webp' },
    { name: 'Yves Saint Laurent', imageUrl: 'https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-ysl.webp' },
  ]);
  const priceRanges = ref([
    { label: 'Dưới 2 Triệu', value: 'under_2' },
    { label: '2 - 4 Triệu', value: '2_4' },
    { label: 'Trên 4 Triệu', value: 'over_4' },
  ]);
  const aromaOptions = ref([
    'Hương hoa cỏ',
    'Hương gỗ',
    'Hương phương Đông',
    'Hương trái cây',
    'Hương cam chanh',
    'Hương gia vị',
    'Hương da thuộc',
    'Hương biển',
    'Hương Fougere',
  ]);
  const selectedBrand = ref(null);
  const selectedPriceRange = ref(null);
  const selectedAromas = ref([]);


  // Xử lý lọc sản phẩm theo Brand
  const handleSelectBrand = (brandName) => {
    selectedBrand.value = brandName;
    console.log('Selected Brand:', selectedBrand.value);
    applyFilters();
  };

  // Xử lý lọc sản phẩm theo Price Range
  const handleSelectPriceRange = (range) => {
    selectedPriceRange.value = range;
    console.log('Selected Price Range:', selectedPriceRange.value);
    applyFilters();
  };

  // Xử lý lọc sản phẩm theo Aroma
  const handleSelectAroma = (aromas) => {
    selectedAromas.value = aromas;
    console.log('Selected Aromas:', selectedAromas.value);
    applyFilters();
  };

  // Hàm áp dụng tất cả các bộ lọc
  const applyFilters = () => {
    // Luôn bắt đầu lọc từ danh sách sản phẩm GỐC của category hiện tại
    let filteredProducts = [...allProducts.value];

    // Lọc theo Brand
    if (selectedBrand.value) {
      filteredProducts = filteredProducts.filter(product => product.brand === selectedBrand.value);
    }

    // Lọc theo Price Range
    if (selectedPriceRange.value) {
      const [min, max] = getPriceRange(selectedPriceRange.value);
      filteredProducts = filteredProducts.filter(product => {
        // Chuẩn hóa chuỗi giá: loại bỏ dấu chấm và " VNĐ", sau đó parse ra số thực
        const priceString = String(product.price).replace(/\./g, '').replace(' VNĐ', '').trim();
        const price = parseFloat(priceString);

        // Đảm bảo giá là số hợp lệ và nằm trong phạm vi
        return !isNaN(price) && (min === null || price >= min) && (max === null || price <= max);
      });
    }

    // Lọc theo Aroma
    if (selectedAromas.value.length > 0) {
      // Giả định product.aroma là một chuỗi đơn (ví dụ: "Hương hoa cỏ")
      filteredProducts = filteredProducts.filter(product =>
        product.aroma && selectedAromas.value.includes(product.aroma)
      );
      // Nếu product.aroma là một MẢNG các chuỗi (ví dụ: ["Hương hoa cỏ", "Hương gỗ"]), bạn sẽ cần điều chỉnh lại thành:
      // filteredProducts = filteredProducts.filter(product =>
      //     product.aromas && product.aromas.some(aroma => selectedAromas.value.includes(aroma))
      // );
    }

    products.value = filteredProducts; // Cập nhật danh sách sản phẩm hiển thị
  };

  // Hàm giúp chuyển đổi tên phạm vi giá thành giá trị số
  const getPriceRange = (range) => {
    switch (range) {
      case 'under_2': return [0, 2000000];
      case '2_4': return [2000000, 4000000];
      case 'over_4': return [4000000, null];
      default: return [null, null];
    }
  };

  const route = useRoute();

  const fetchResults = async () => {
    loading.value = true;
    const keyword = route.params.keyword || '';
    if (!keyword) return;
    const response = await axios.get('http://localhost:8000/api/products/search', { params: { keyword } });
    products.value = response.data.data;
    loading.value = false;
  };

  onMounted(fetchResults);
  watch(() => route.params.keyword, fetchResults);
</script>
<style scoped>
  @import '@/assets/tailwind.css';

  .error {
    color: red;
  }
</style>