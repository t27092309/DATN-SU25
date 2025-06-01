<style scoped>
@import '@/assets/tailwind.css';
</style>
<template>
  <div class="container mx-auto p-4">
    <p class="font-bold text-3xl text-black">Nước hoa Mini</p>
    <nav class="text-sm breadcrumbs mb-6 ">
      <ul class="flex items-center space-x-2">
        <li><router-link to="/" class="text-2xl"><span class="text-base text-gray-500">Trang chủ</span></router-link>
        </li>
        <span class="text-gray-500 text-base">/</span>
        <li class="ml-2"><router-link to="/nuoc-hoa"><span class="text-base text-gray-500">Nước hoa</span></router-link>
        </li>
        <span class="text-gray-500 text-base">/</span>
        <li class="text-gray-900 font-bold text-base">Nước Hoa Mini</li>
      </ul>
    </nav>
    <div class=" flex flex-col md:flex-row gap-8 mt-5">

      <BrandList :brands="brands" :selected-brand="selectedBrand" @select-brand="handleSelectBrand" />

      <div class="flex-1">
        <div class="mb-8">
          <ProductFilters :priceRanges="priceRanges" :selectedPriceRange="selectedPriceRange"
            :aromaOptions="aromaOptions" :selectedAromas="selectedAromas" @select-price-range="handleSelectPriceRange"
            @select-aroma="handleSelectAroma" ref="productFilters" />
        </div>

        <div class="bg-gray-100 p-6 rounded-lg min-h-[300px]">
          <h4 class="text-lg font-medium mb-4">
            Nội dung chính cho {{ selectedBrand ? selectedBrand : 'tất cả hãng' }}
          </h4>
          <p v-if="selectedBrand">Bạn đã chọn hãng: **{{ selectedBrand }}**</p>
          <p v-else>Chọn một hãng từ danh sách bên trái để xem sản phẩm.</p>
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
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import BrandList from '@/components/BrandList.vue';
import ProductFilters from '@/components/ProductFilter.vue';

export default {
  name: 'MainLayout', // Or 'NuocHoaNamPage' if this is specific to that page
  components: {
    BrandList,
    ProductFilters
  },
  data() {
    return {
      brands: [
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
      ],
      priceRanges: [
        { label: 'Dưới 2 Triệu', value: 'under_2' },
        { label: '2 - 4 Triệu', value: '2_4' },
        { label: 'Trên 4 Triệu', value: 'over_4' },
      ],
      // Define aroma options for the dropdown
      aromaOptions: [
        'Hương hoa cỏ',
        'Hương gỗ',
        'Hương phương Đông',
        'Hương trái cây',
        'Hương cam chanh',
        'Hương gia vị',
        'Hương da thuộc',
        'Hương biển',
        'Hương Fougere',
      ],
      selectedBrand: null,
      selectedPriceRange: null,
      selectedAromas: [], // Initialize selectedAroma to null
    };
  },
  methods: {
    handleSelectBrand(brandName) {
      this.selectedBrand = brandName;
      console.log('MainLayout: Selected Brand:', this.selectedBrand);
    },
    handleSelectPriceRange(range) {
      this.selectedPriceRange = range;
      console.log('MainLayout: Selected Price Range:', this.selectedPriceRange);
    },
    handleSelectAroma(selectedAromas) {
      this.selectedAromas = selectedAromas; // Gán trực tiếp mảng mới
      console.log('MainLayout: Selected Aromas:', this.selectedAromas);
    },
    handleClickOutside(event) {
      if (this.$refs.productFilters && !this.$refs.productFilters.$el.contains(event.target)) {
        this.$refs.productFilters.closeDropdownProgrammatically();
      }
    }
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  }
};
</script>

<style scoped>
/* You don't need scrollbar-hide here for MainLayout */
</style>