<template>
  <main>
    <Splide
      ref="splide"
      :options="{
        type: 'loop',
        perPage: 1,
        autoplay: true,
        interval: 3000,
        arrows: true,
      }"
    >
      <SplideSlide v-for="bannerItem in banners" :key="bannerItem.id">
        <a :href="bannerItem.link_url">
          <img
            :src="`${BASE_URL}${bannerItem.image_url}`"
            class="w-full object-cover"
            alt="Banner"
          />
        </a>
      </SplideSlide>
    </Splide>

    <div class="mega-menu mx-auto max-w-[1200px] px-4">
      <div class="py-4 px-4 items-center">
        <h2 class="text-2xl font-bold text-gray-800">
          Thương hiệu và danh mục
        </h2>
        <div
          class="mx-auto bg-white rounded-lg shadow-xl p-8 w-[1200px] -translate-x-4"
          style="max-height: calc(100vh - 70px); overflow-y: auto"
        >
          <div class="container mx-auto max-w-6xl">
            <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">
              Khám Phá Nước Hoa
            </h2>

            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-12"
            >
              <div
                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden"
              >
                <div class="relative w-full h-64 overflow-hidden">
                  <img
                    :src="cateNuocHoaNam"
                    alt="Men's Perfume"
                    class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300"
                  />
                </div>
              </div>

              <div
                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden"
              >
                <div class="relative w-full h-64 overflow-hidden">
                  <img
                    :src="cateNuocHoaNu"
                    alt="Women's Perfume"
                    class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300"
                  />
                </div>
              </div>

              <div
                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden"
              >
                <div class="relative w-full h-64 overflow-hidden">
                  <img
                    :src="cateNuocHoaUnisex"
                    alt="Unisex Perfume"
                    class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300"
                  />
                </div>
              </div>

              <div
                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden"
              >
                <div class="relative w-full h-64 overflow-hidden">
                  <img
                    :src="cateNuocHoaNiche"
                    alt="Niche Perfume"
                    class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300"
                  />
                </div>
              </div>

              <div
                class="flex flex-col items-center group cursor-pointer border rounded-lg hover:shadow-lg transition-all duration-300 overflow-hidden"
              >
                <div class="relative w-full h-64 overflow-hidden">
                  <img
                    :src="cateNuocHoaMini"
                    alt="Mini Perfume"
                    class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-300"
                  />
                </div>
              </div>
            </div>

            <h3 class="text-2xl font-bold text-gray-800 mb-6 text-center">
              Các thương Hiệu Nổi Bật
            </h3>
            <div
              class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-y-8 gap-x-4"
            >
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-bvlgary.webp"
                  alt="BVLGARI Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/04/logo-brand-calvin-klein.webp"
                  alt="Parfums Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-chanel.webp"
                  alt="CHANEL Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-dior.webp"
                  alt="DIOR Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/04/logo-brand-giorgio-armani.webp"
                  alt="GIORGIO ARMANI Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-gucci.webp"
                  alt="GUCCI Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jean-paul-gaultier.webp"
                  alt="Jean Paul GAULTIER Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-jo-malone.webp"
                  alt="Jo Malone Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-kilian.webp"
                  alt="Kilian Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-lancome.webp"
                  alt="LANCÔME Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-montblanc.webp"
                  alt="MONTBLANC Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/04/logo-brand-narciso-rodriguez.webp"
                  alt="narciso rodriguez Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-parfums-de-marly.webp"
                  alt="PARFUMS DE MARLY Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-tom-ford.webp"
                  alt="TOM FORD Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-versace.webp"
                  alt="VERSACE Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
              <div class="flex justify-center items-center">
                <img
                  src="https://orchard.vn/wp-content/uploads/2024/06/fragnance-logo-text-ysl.webp"
                  alt="Yves Saint Laurent Logo"
                  class="h-16 max-w-full object-contain"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <ProductsByCategory />
  </main>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { Splide, SplideSlide } from "@splidejs/vue-splide";
import ProductsByCategory from "@/components/ProductsByCategory.vue";

const categoriesData = ref([]);
const loading = ref(true);
const error = ref(null);

const fetchMostViewedProducts = async () => {
  try {
    const response = await axios.get(
      "http://localhost:8000/api/most-viewed-products-by-categories"
    );
    categoriesData.value = response.data;
  } catch (err) {
    error.value = "Không thể tải dữ liệu sản phẩm. Vui lòng thử lại sau.";
    console.error("Lỗi khi fetch API:", err);
  } finally {
    loading.value = false;
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(value);
};

const BASE_URL = "http://127.0.0.1:8000";

const banners = ref([]);
const splide = ref(null);

const fetchBanners = async () => {
  try {
    const response = await axios.get("http://127.0.0.1:8000/api/banners");
    banners.value = response.data.data.filter((banner) => banner.is_active);
    if (splide.value && splide.value.splide) {
      splide.value.splide.refresh(); // Làm mới Splide khi dữ liệu thay đổi
    }
  } catch (err) {
    console.error("Lỗi khi fetch banner:", err);
  }
};

onMounted(() => {
  fetchMostViewedProducts();
  fetchBanners();
});

// Theo dõi thay đổi của banners và làm mới Splide
watch(banners, () => {
  if (splide.value && splide.value.splide) {
    splide.value.splide.refresh(); // Làm mới Splide khi banners thay đổi
  }
});

import cateNuocHoaNam from "@/assets/images/categories/category-nuoc-hoa-nam.webp";
import cateNuocHoaNu from "@/assets/images/categories/category-nuoc-hoa-nu-600x720.webp";
import cateNuocHoaUnisex from "@/assets/images/categories/category-nuoc-hoa-unisex-600x720.webp";
import cateNuocHoaNiche from "@/assets/images/categories/category-nuoc-hoa-niche-600x720.webp";
import cateNuocHoaMini from "@/assets/images/categories/category-nuoc-hoa-mini-600x720.webp";
</script>

<style scoped>
@import "@/assets/tailwind.css";

/* Đảm bảo banner chiếm toàn bộ chiều cao mong muốn, tùy chỉnh theo thiết kế */
.splide__slide img {
  height: auto; /* Loại bỏ chiều cao cố định */
  max-height: 800px; /* Điều chỉnh theo kích thước ban đầu nếu cần */
}

/* Thêm style cho link trong banner để không ảnh hưởng đến layout */
.splide__slide a {
  display: block; /* Đảm bảo link chiếm toàn bộ không gian của slide */
  text-decoration: none; /* Loại bỏ gạch chân mặc định */
}
</style>
