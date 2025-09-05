<template>
  <div class="container mx-auto p-4 max-w-[1200px]">
    <p class="font-bold text-3xl text-black">Thương hiệu</p>
    <nav class="text-sm text-gray-500 mb-6">
      <ul class="flex items-center space-x-1">
        <li class="flex items-center">
          <router-link to="/" class="text-base hover:text-gray-700 transition-colors duration-200">Trang
            chủ</router-link>
        </li>
        <li class="flex items-center">
          <span class="mx-2 text-gray-400">/</span>
          <router-link to="nuoc-hoa" class="text-base hover:text-gray-700 transition-colors duration-200">Nước
            hoa</router-link>
        </li>
        <li class="flex items-center">
          <span class="mx-2 text-gray-400">/</span>
          <span class="text-gray-900 font-bold text-base">{{ categoryName }}</span>
        </li>
      </ul>
    </nav>
    <div class="flex flex-col md:flex-row gap-8 mt-5">
      <BrandList :brands="brands" :selected-brand="selectedBrand" :loading-brands="loadingBrands"
        :brands-error="brandsError" @select-brand="handleSelectBrand" />
      <div class="flex-1">
        <div class="mb-8">
          <ProductFilters :priceRanges="priceRanges" :selectedPriceRange="selectedPriceRange"
            :aromaOptions="aromaOptions" :selectedAromas="selectedAromas" @select-price-range="handleSelectPriceRange"
            @select-aroma="handleSelectAroma" ref="productFilters" />
        </div>
        <div class=" p-6 rounded-lg min-h-[300px]">
          <p v-if="selectedBrand" class="mb-2">
            Hãng đã chọn: <strong>{{ selectedBrand }}</strong>
          </p>
          <p v-if="selectedPriceRange" class="mb-2">Phạm vi giá đã chọn: <strong>{{ selectedPriceRange }}</strong></p>
          <div v-if="selectedAromas.length > 0" class="mb-2">
            <p class="font-medium">Nhóm Hương:</p>
            <div class="flex flex-wrap gap-2 mt-1">
              <span v-for="aroma in selectedAromas" :key="aroma"
                class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">
                {{ aroma }}
              </span>
            </div>
          </div>
          <div v-if="loading" class="text-center py-4">Đang tải sản phẩm...</div>
          <div v-else-if="error" class="error text-red-600 text-center py-4">{{ error }}</div>
          <div v-else-if="filteredProducts.length"
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-4">
            <router-link v-for="product in filteredProducts" :key="product.slug || product.id"
              :to="{ name: 'ProductDetail', params: { slug: product.slug || product.id } }"
              class="block p-4 bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200">
              <img :src="product.image" :alt="product.name" class="w-full h-48 object-cover rounded-t-lg">
              <h5 class="text-md font-semibold mt-2">{{ product.name }}</h5>
              <p class="text-gray-700">{{ product.brand }}</p>
              <p class="text-lg font-bold text-red-600">{{ new Intl.NumberFormat('vi-VN').format(product.price) }} VNĐ
              </p>
            </router-link>
          </div>
          <p v-else class="text-center py-4">Không có sản phẩm nào phù hợp với bộ lọc.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import BrandList from '@/components/BrandList.vue';
import ProductFilters from '@/components/ProductFilter.vue';

// Props
const props = defineProps(['categorySlug']);

// State
const products = ref([]); // Danh sách sản phẩm GỐC của category
const brands = ref([]); // Danh sách thương hiệu từ API
const loading = ref(false); // Trạng thái tải sản phẩm
const loadingBrands = ref(false); // Trạng thái tải thương hiệu
const error = ref(null); // Lỗi khi tải sản phẩm
const brandsError = ref(null); // Lỗi khi tải thương hiệu
const router = useRouter();
const categoryName = ref('Danh mục')

const priceRanges = ref([
  { label: 'Dưới 2 Triệu', value: 'under_2' },
  { label: '2 - 4 Triệu', value: '2_4' },
  { label: 'Trên 4 Triệu', value: 'over_4' },
]);
const aromaOptions = ref([
  'Hương hoa cỏ', 'Hương gỗ', 'Hương phương Đông', 'Hương trái cây', 'Hương cam chanh',
  'Hương gia vị', 'Hương da thuộc', 'Hương biển', 'Hương Fougere',
]);
const selectedBrand = ref(null);
const selectedPriceRange = ref(null);
const selectedAromas = ref([]);

// Hàm lấy danh sách thương hiệu từ API
const fetchBrands = async () => {
  loadingBrands.value = true;
  brandsError.value = null;

  try {
    const response = await axios.get('http://localhost:8000/api/brands');
    if (Array.isArray(response.data)) {
      // Ánh xạ dữ liệu từ API thành định dạng { name, imageUrl }
      brands.value = response.data
        .filter(brand => !brand.deleted_at) // Lọc bỏ các thương hiệu bị xóa mềm
        .map(brand => ({
          name: brand.name,
          imageUrl: brand.logo || 'https://via.placeholder.com/150?text=No+Image' // Fallback nếu không có logo
        }));
      console.log('Fetched brands:', brands.value);
    } else {
      throw new Error('Dữ liệu thương hiệu từ API không hợp lệ');
    }
  } catch (err) {
    brandsError.value = `Không thể tải danh sách thương hiệu: ${err.response?.status === 404 ? 'API /api/brands không tồn tại.' : err.message}`;
    brands.value = [];
    console.error('Error fetching brands:', err);
  } finally {
    loadingBrands.value = false;
  }
};

// Hàm lấy sản phẩm từ API
const fetchProducts = async () => {
  loading.value = true;
  error.value = null;
  selectedBrand.value = null;
  selectedPriceRange.value = null;
  selectedAromas.value = [];

  try {
    const response = await axios.get(`http://localhost:8000/api/category-page-products`);
    if (response.data && Array.isArray(response.data.data)) {
      const category = response.data.data.find(item => item.category_slug === props.categorySlug);
      if (category) {
        products.value = category.products || [];
        categoryName.value = category.category_name;
        console.log('Fetched products:', products.value);
        console.log('Available brands in products:', [...new Set(products.value.map(p => p.brand))]);
      } else {
        error.value = `Không tìm thấy danh mục với slug: ${props.categorySlug}`;
        products.value = [];
      }
    } else {
      throw new Error('Dữ liệu sản phẩm từ API không hợp lệ');
    }
  } catch (err) {
    error.value = 'Không thể tải sản phẩm: ' + (err.message || 'Lỗi không xác định');
    products.value = [];
    console.error('Error fetching products:', err);
  } finally {
    loading.value = false;
  }
};

// Hàm chuyển đổi phạm vi giá
const getPriceRange = (range) => {
  switch (range) {
    case 'under_2': return [0, 2000000];
    case '2_4': return [2000000, 4000000];
    case 'over_4': return [4000000, null];
    default: return [null, null];
  }
};

// Computed property để lọc sản phẩm
const filteredProducts = computed(() => {
  let result = [...products.value];

  if (selectedBrand.value) {
    result = result.filter(product =>
      product.brand && product.brand.toLowerCase() === selectedBrand.value.toLowerCase()
    );
  }

  if (selectedPriceRange.value) {
    const [min, max] = getPriceRange(selectedPriceRange.value);
    result = result.filter(product => {
      const priceString = String(product.price).replace(/\./g, '').replace(' VNĐ', '').trim();
      const price = parseFloat(priceString);
      return !isNaN(price) && (min === null || price >= min) && (max === null || price <= max);
    });
  }

  if (selectedAromas.value.length > 0) {
    result = result.filter(product =>
      product.aroma && selectedAromas.value.includes(product.aroma)
    );
  }

  console.log('Filtered products:', result);
  return result;
});

// Xử lý sự kiện chọn hãng
const handleSelectBrand = (brandName) => {
  selectedBrand.value = brandName;
  console.log('Selected Brand:', selectedBrand.value);
};

// Xử lý sự kiện chọn phạm vi giá
const handleSelectPriceRange = (range) => {
  selectedPriceRange.value = range;
  console.log('Selected Price Range:', selectedPriceRange.value);
};

// Xử lý sự kiện chọn nhóm hương
const handleSelectAroma = (aromas) => {
  selectedAromas.value = aromas;
  console.log('Selected Aromas:', selectedAromas.value);
};

// Gọi API khi component được mount
onMounted(() => {
  fetchBrands(); // Lấy danh sách thương hiệu
  fetchProducts(); // Lấy danh sách sản phẩm
});

// Theo dõi thay đổi categorySlug
watch(() => props.categorySlug, () => {
  fetchProducts();
}, { immediate: true });
</script>

<style scoped>
.error {
  color: red;
}
</style>