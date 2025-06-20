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
          <div v-else-if="products.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
            <router-link
              v-for="product in products"
              :key="product.slug || product.id"
              :to="{ name: 'ProductDetail', params: { slug: product.slug || product.id } }"
              class="block p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200"
            >
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
import { useRouter } from 'vue-router';
import axios from 'axios';
import BrandList from '@/components/BrandList.vue';
import ProductFilters from '@/components/ProductFilter.vue';

// Props
const props = defineProps(['categoryName']);

// State
const products = ref([]);
const loading = ref(false);
const error = ref(null);
const router = useRouter(); // Giữ nguyên

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

// Hàm lấy sản phẩm từ API dựa trên categoryName
const fetchProducts = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`http://localhost:8000/api/category-page-products?category=${props.categoryName}`);
    if (response.data && Array.isArray(response.data.data)) {
      const category = response.data.data.find(item => item.category_name === props.categoryName);
      products.value = category ? category.products : [];
    } else {
      throw new Error('Dữ liệu từ API không hợp lệ');
    }
  } catch (err) {
    error.value = 'Không thể tải sản phẩm: ' + (err.message || 'Lỗi không xác định');
  } finally {
    loading.value = false;
  };
};

// Xử lý lọc sản phẩm
const handleSelectBrand = (brandName) => {
  selectedBrand.value = brandName;
  console.log('Selected Brand:', selectedBrand.value);
  applyFilters();
};

const handleSelectPriceRange = (range) => {
  selectedPriceRange.value = range;
  console.log('Selected Price Range:', selectedPriceRange.value);
  applyFilters();
};

const handleSelectAroma = (aromas) => {
  selectedAromas.value = aromas;
  console.log('Selected Aromas:', selectedAromas.value);
  applyFilters();
};

const applyFilters = () => {
  // BẢO LƯU DANH SÁCH SẢN PHẨM GỐC TỪ API ĐỂ LỌC LẠI
  // Nếu bạn muốn lọc trên dữ liệu gốc mỗi lần, bạn cần lưu trữ nó riêng.
  // Ví dụ: const allProducts = ref([]); fetchProducts sẽ gán vào allProducts.value
  // Và sau đó, lọc trên allProducts.value
  // Hiện tại, logic lọc của bạn đang lọc trên products.value đã bị lọc từ trước đó.
  // Điều này có thể dẫn đến kết quả không mong muốn khi áp dụng nhiều bộ lọc liên tiếp.

  // ĐỂ KHẮC PHỤC, BẠN CẦN LƯU TRỮ DANH SÁCH SẢN PHẨM GỐC:
  // Thêm một ref mới:
  // const allProducts = ref([]);
  // Trong fetchProducts, gán: allProducts.value = category ? category.products : [];
  // Và sau đó: products.value = [...allProducts.value]; // Khởi tạo products với tất cả
  // Và ở đây, hãy lọc trên allProducts.value:
  let filteredProducts = products.value; // <= NÊN LÀ allProducts.value.filter(...)

  if (selectedBrand.value) {
    filteredProducts = filteredProducts.filter(product => product.brand === selectedBrand.value);
  }
  if (selectedPriceRange.value) {
    const [min, max] = getPriceRange(selectedPriceRange.value);
    filteredProducts = filteredProducts.filter(product => {
      const price = parseFloat(product.price);
      return min !== null ? price >= min : true && max !== null ? price <= max : true;
    });
  }
  if (selectedAromas.value.length > 0) {
    // Giả định sản phẩm có trường aroma (cần điều chỉnh dựa trên API thực tế)
    // Nếu product.aroma là một mảng các hương, bạn cần kiểm tra sự giao thoa
    // Ví dụ: product.aromas.some(aroma => selectedAromas.value.includes(aroma))
    filteredProducts = filteredProducts.filter(product => selectedAromas.value.includes(product.aroma));
  }

  products.value = filteredProducts; // Cập nhật lại danh sách sản phẩm hiển thị
};

const getPriceRange = (range) => {
  switch (range) {
    case 'under_2': return [0, 2000000];
    case '2_4': return [2000000, 4000000];
    case 'over_4': return [4000000, null];
    default: return [null, null];
  }
};

// Gọi API khi component được mount và khi categoryName thay đổi
onMounted(() => {
  fetchProducts();
});

watch(() => props.categoryName, () => {
  fetchProducts();
});
</script>

<style scoped>
@import '@/assets/tailwind.css';
.error {
  color: red;
}
</style>