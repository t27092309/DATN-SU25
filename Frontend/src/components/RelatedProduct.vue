<template>
  <div v-if="relatedProducts.length > 0" class="max-w-7xl mx-auto p-4">
    <h2 class="text-xl font-bold mb-6 uppercase">Nước hoa có cùng mùi hương</h2>
    <div
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-12"
    >
      <router-link
        v-for="product in relatedProducts"
        :key="product.id"
        :to="{ name: 'ProductDetail', params: { slug: product.slug } }"
        class="product-card group"
      >
        <!-- Image Container - Fixed Height -->
        <div class="product-image-container">
          <span v-if="product.discount_percent" class="discount-badge">
            -{{ product.discount_percent }}%
          </span>
          <img
            :src="product.image"
            :alt="product.name"
            class="product-image"
            loading="lazy"
          />
        </div>

        <!-- Product Info - Fixed Height -->
        <div class="product-info">
          <!-- Brand Name -->
          <p class="brand-name">
            {{ product.brand_name }}
          </p>

          <!-- Product Name - Single Line with Ellipsis -->
          <p class="product-name" :title="product.name">
            {{ product.name }}
          </p>

          <!-- Pricing -->
          <div class="pricing-container">
            <span class="current-price" :title="formatPrice(product.price)">
              {{ formatPrice(product.price) }}
            </span>
            <span
              v-if="
                product.original_price && product.original_price > product.price
              "
              class="original-price"
              :title="formatPrice(product.original_price)"
            >
              {{ formatPrice(product.original_price) }}
            </span>
          </div>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";

import ProductReview from "@/components/ProductReview.vue";
import ProductDescription from "@/components/ProductDescription.vue";
import ProductCarousel from "@/components/ProductCarousel.vue";
import RelatedProduct from "@/components/RelatedProduct.vue";

const product = ref(null);
const relatedProducts = ref([]);
const route = useRoute();
const router = useRouter();

const selectedAttributes = ref({});
const foundVariant = ref(null);

const quantity = ref(1);
const cartMessage = ref("");
const cartError = ref(false);
const reviews = ref([]);
const totalReviews = computed(() => reviews.value.length);

const selectedVariantPrice = computed(() =>
  foundVariant.value
    ? foundVariant.value.price
    : product.value
    ? product.value.price
    : "0"
);
const selectedVariantStock = computed(() =>
  foundVariant.value ? foundVariant.value.stock : "N/A"
);
const selectedVariantSold = computed(() =>
  foundVariant.value ? foundVariant.value.sold : "N/A"
);

const selectedVariantStatus = computed(() => {
  if (!foundVariant.value) {
    return "pending_selection";
  }
  return foundVariant.value.status;
});
const selectedVariantSku = computed(() =>
  foundVariant.value ? foundVariant.value.sku : "N/A"
);

const computedProductImages = computed(() => {
  if (!product.value) return [];

  const images = [];
  const mainImageUrl = product.value.image;
  const galleryImages = Array.isArray(product.value.images)
    ? product.value.images
    : [];

  if (
    mainImageUrl &&
    typeof mainImageUrl === "string" &&
    mainImageUrl.trim() !== ""
  ) {
    images.push(mainImageUrl);
  }

  galleryImages.forEach((imgUrl) => {
    if (
      imgUrl &&
      typeof imgUrl === "string" &&
      imgUrl.trim() !== "" &&
      imgUrl !== mainImageUrl &&
      !images.includes(imgUrl)
    ) {
      images.push(imgUrl);
    }
  });

  return images;
});

const groupedAttributes = computed(() => {
  if (
    !product.value ||
    !product.value.variants ||
    product.value.variants.length === 0
  ) {
    return [];
  }

  const attributeMap = new Map();

  product.value.variants.forEach((variant) => {
    if (variant.attributes) {
      variant.attributes.forEach((attr) => {
        if (!attributeMap.has(attr.attribute_slug)) {
          attributeMap.set(attr.attribute_slug, {
            name: attr.attribute_name,
            slug: attr.attribute_slug,
            values: new Map(),
          });
        }
        const attrGroup = attributeMap.get(attr.attribute_slug);
        if (!attrGroup.values.has(attr.value_id)) {
          attrGroup.values.set(attr.value_id, {
            value_id: attr.value_id,
            value_name: attr.value_name,
          });
        }
      });
    }
  });

  return Array.from(attributeMap.values()).map((attrGroup) => ({
    ...attrGroup,
    values: Array.from(attrGroup.values.values()),
  }));
});

const selectAttributeValue = (attributeSlug, attributeValue) => {
  if (
    selectedAttributes.value[attributeSlug]?.value_id ===
    attributeValue.value_id
  ) {
    const newSelected = { ...selectedAttributes.value };
    delete newSelected[attributeSlug];
    selectedAttributes.value = newSelected;
  } else {
    selectedAttributes.value = {
      ...selectedAttributes.value,
      [attributeSlug]: attributeValue,
    };
  }
};

const isSelectedAttribute = (attributeSlug, attributeValue) => {
  return (
    selectedAttributes.value[attributeSlug]?.value_id ===
    attributeValue.value_id
  );
};

const isAttributeValueAvailable = (
  currentAttributeSlug,
  currentAttributeValue
) => {
  if (
    !product.value ||
    !product.value.variants ||
    product.value.variants.length === 0
  ) {
    return false;
  }

  const currentSelectionsWithoutThisAttribute = Object.entries(
    selectedAttributes.value
  )
    .filter(([slug]) => slug !== currentAttributeSlug)
    .map(([, value]) => value);

  return product.value.variants.some((variant) => {
    const hasCurrentValue = variant.attributes.some(
      (attr) =>
        attr.attribute_slug === currentAttributeSlug &&
        attr.value_id === currentAttributeValue.value_id
    );

    if (!hasCurrentValue) {
      return false;
    }

    const matchesOtherSelections = currentSelectionsWithoutThisAttribute.every(
      (selectedVal) => {
        const selectedValSlug = Object.keys(selectedAttributes.value).find(
          (key) => selectedAttributes.value[key] === selectedVal
        );
        return variant.attributes.some(
          (variantAttr) =>
            variantAttr.attribute_slug === selectedValSlug &&
            variantAttr.value_id === selectedVal.value_id
        );
      }
    );

    return hasCurrentValue && matchesOtherSelections;
  });
};

const findMatchingVariant = () => {
  foundVariant.value = null;

  if (
    !product.value ||
    !product.value.variants ||
    product.value.variants.length === 0
  ) {
    return;
  }

  const currentSelectedAttrSlugs = Object.keys(selectedAttributes.value);

  if (groupedAttributes.value.length === 0) {
    const defaultVariant = product.value.variants.find(
      (v) => !v.attributes || v.attributes.length === 0
    );
    if (defaultVariant) {
      foundVariant.value = defaultVariant;
    }
    return;
  }

  if (currentSelectedAttrSlugs.length !== groupedAttributes.value.length) {
    return;
  }

  const matchingVariant = product.value.variants.find((variant) => {
    if (!variant.attributes || variant.attributes.length === 0) {
      return false;
    }

    return currentSelectedAttrSlugs.every((attrSlug) => {
      const selectedVal = selectedAttributes.value[attrSlug];
      return variant.attributes.some(
        (vAttr) =>
          vAttr.attribute_slug === attrSlug &&
          vAttr.value_id === selectedVal.value_id
      );
    });
  });

  foundVariant.value = matchingVariant;
};

watch(selectedAttributes, findMatchingVariant, { deep: true });

const formatPrice = (price) => {
  if (price === null || price === undefined || isNaN(price)) return "0 ₫";
  return parseFloat(price).toLocaleString("vi-VN", {
    style: "currency",
    currency: "VND",
  });
};

const formatSold = (variants) => {
  if (!variants || variants.length === 0) return "0";
  const totalSold = variants.reduce(
    (sum, variant) => sum + (variant.sold || 0),
    0
  );
  if (totalSold >= 1000) {
    return `${(totalSold / 1000).toFixed(1)}K`;
  }
  return totalSold.toString();
};

const sortedScentProfiles = computed(() => {
  if (product.value && product.value.scent_profiles) {
    return [...product.value.scent_profiles].sort(
      (a, b) => b.strength - a.strength
    );
  }
  return [];
});

const isDarkColor = (hexColor) => {
  if (!hexColor) return false;
  const r = parseInt(hexColor.slice(1, 3), 16);
  const g = parseInt(hexColor.slice(3, 5), 16);
  const b = parseInt(hexColor.slice(5, 7), 16);
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
  return luminance < 0.5;
};

const formatLongevity = (longevity) => {
  if (longevity === null || longevity === undefined) return "N/A";
  const hours = parseFloat(longevity);
  if (isNaN(hours)) return "N/A";
  if (hours <= 0) return "Không đáng kể";
  if (hours < 1) return `~ ${Math.round(hours * 60)} phút`;
  if (hours < 24) return `~ ${hours} giờ`;
  return `hơn 24 giờ`;
};

const calculateLongevityPercent = (longevity) => {
  if (longevity === null || longevity === undefined) return 0;
  const hours = parseFloat(longevity);
  if (isNaN(hours) || hours <= 0) return 0;
  const maxHoursForDisplay = 12;
  return Math.min(100, (hours / maxHoursForDisplay) * 100);
};

const calculateSillagePercent = (sillage) => {
  if (sillage === null || sillage === undefined) return 0;
  const sillageValue = parseFloat(sillage.replace("m", ""));
  if (isNaN(sillageValue) || sillageValue <= 0) return 0;
  const maxSillageForDisplay = 3;
  return Math.min(100, (sillageValue / maxSillageForDisplay) * 100);
};

const addToCart = async () => {
  cartMessage.value = "";
  cartError.value = false;

  if (!foundVariant.value) {
    cartMessage.value =
      "Vui lòng chọn đầy đủ các thuộc tính để thêm sản phẩm vào giỏ hàng.";
    cartError.value = true;
    return;
  }

  if (quantity.value < 1) {
    cartMessage.value = "Số lượng phải lớn hơn hoặc bằng 1.";
    cartError.value = true;
    return;
  }

  if (
    selectedVariantStatus.value === "unavailable" ||
    selectedVariantStock.value === 0
  ) {
    cartMessage.value = "Sản phẩm này hiện không có sẵn hoặc đã hết hàng.";
    cartError.value = true;
    return;
  }

  if (quantity.value > selectedVariantStock.value) {
    cartMessage.value = `Số lượng yêu cầu (${quantity.value}) vượt quá tồn kho hiện có (${selectedVariantStock.value}).`;
    cartError.value = true;
    return;
  }

  try {
    const response = await axios.post("cart-items", {
      product_variant_id: foundVariant.value.id,
      quantity: quantity.value,
    });

    if (response.status === 200 || response.status === 201) {
      cartMessage.value =
        response.data.message ||
        "Sản phẩm đã được thêm vào giỏ hàng thành công!";
      cartError.value = false;
    } else {
      cartMessage.value = `Có lỗi xảy ra: ${
        response.data.message || "Lỗi không xác định"
      }`;
      cartError.value = true;
    }
  } catch (error) {
    console.error("Lỗi khi thêm sản phẩm vào giỏ hàng:", error);
    cartError.value = true;
    if (error.response) {
      if (error.response.status === 422) {
        const validationErrors = error.response.data.errors;
        let errorMessage = "Vui lòng kiểm tra lại thông tin: \n";
        for (const key in validationErrors) {
          errorMessage += `- ${validationErrors[key].join(", ")}\n`;
        }
        cartMessage.value = errorMessage;
      } else if (error.response.status === 401) {
        cartMessage.value = "Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.";
      } else if (error.response.data && error.response.data.message) {
        cartMessage.value = `Lỗi: ${error.response.data.message}`;
      } else {
        cartMessage.value =
          "Không thể thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.";
      }
    } else if (error.request) {
      cartMessage.value =
        "Không có phản hồi từ server. Vui lòng kiểm tra kết nối mạng của bạn.";
    } else {
      cartMessage.value = "Đã xảy ra lỗi không mong muốn. Vui lòng thử lại.";
    }
  }
};

const handleBuyNowClick = () => {
  cartMessage.value = "";
  cartError.value = false;

  if (!foundVariant.value) {
    cartMessage.value = "Vui lòng chọn đầy đủ các thuộc tính để mua sản phẩm.";
    cartError.value = true;
    return;
  }

  if (quantity.value < 1) {
    cartMessage.value = "Số lượng phải lớn hơn hoặc bằng 1.";
    cartError.value = true;
    return;
  }

  if (
    selectedVariantStatus.value === "unavailable" ||
    selectedVariantStock.value === 0
  ) {
    cartMessage.value =
      "Sản phẩm này hiện không có sẵn để mua hoặc đã hết hàng.";
    cartError.value = true;
    return;
  }

  if (quantity.value > selectedVariantStock.value) {
    cartMessage.value = `Số lượng yêu cầu (${quantity.value}) vượt quá tồn kho hiện có (${selectedVariantStock.value}).`;
    cartError.value = true;
    return;
  }

  router.push({
    path: "/thanh-toan",
    query: {
      variant_id: foundVariant.value.id,
      qty: quantity.value,
      buy_now: "true",
    },
  });
};

const fetchProductData = async (productSlug) => {
  if (!productSlug) {
    console.error("Không tìm thấy slug sản phẩm trong URL.");
    return;
  }

  // Reset states before fetching new data
  product.value = null;
  relatedProducts.value = [];
  selectedAttributes.value = {};
  foundVariant.value = null;

  try {
    const response = await axios.get(
      `http://localhost:8000/api/detailproducts/${productSlug}`
    );
    const data = response.data;

    product.value = data.product;
    relatedProducts.value = data.related || [];

    // Fetch reviews separately
    const reviewsResponse = await axios.get(
      `http://localhost:8000/api/products/${productSlug}/reviews`
    );
    reviews.value = reviewsResponse.data.reviews;

    // Logic to find and set initial variant
    if (
      product.value &&
      product.value.variants &&
      product.value.variants.length > 0
    ) {
      const firstVariant = product.value.variants[0];
      const initialSelectedAttributes = {};
      if (firstVariant.attributes) {
        firstVariant.attributes.forEach((attr) => {
          initialSelectedAttributes[attr.attribute_slug] = {
            value_id: attr.value_id,
            value_name: attr.value_name,
          };
        });
      }
      selectedAttributes.value = initialSelectedAttributes;
      findMatchingVariant();
    } else if (
      product.value &&
      (!product.value.variants || product.value.variants.length === 0)
    ) {
      foundVariant.value = {
        id: product.value.id,
        price: product.value.price,
        stock: product.value.stock,
        status: product.value.stock > 0 ? "available" : "unavailable",
        sku: product.value.sku,
        attributes: [],
      };
    }
  } catch (error) {
    console.error("Lỗi khi lấy dữ liệu sản phẩm:", error);
    cartMessage.value =
      "Không thể tải thông tin sản phẩm. Vui lòng thử lại sau.";
    cartError.value = true;
  }
};

onMounted(() => {
  fetchProductData(route.params.slug);
});

watch(
  () => route.params.slug,
  (newSlug, oldSlug) => {
    if (newSlug !== oldSlug) {
      fetchProductData(newSlug);
    }
  }
);
</script>

<style scoped>
/* Product Card */
.product-card {
  @apply flex flex-col bg-white rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300 border border-gray-100 h-full;
}

.product-card:hover {
  @apply transform -translate-y-1 shadow-xl;
}

/* Image Container - Fixed Aspect Ratio */
.product-image-container {
  @apply relative w-full bg-gray-50;
  aspect-ratio: 1;
  min-height: 160px;
}

@media (min-width: 640px) {
  .product-image-container {
    min-height: 180px;
  }
}

@media (min-width: 768px) {
  .product-image-container {
    min-height: 200px;
  }
}

/* Product Image */
.product-image {
  @apply w-full h-full object-cover transition-transform duration-300;
}

.group:hover .product-image {
  @apply scale-105;
}

/* Discount Badge */
.discount-badge {
  @apply absolute top-2 left-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-md z-10;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Product Info Container */
.product-info {
  @apply flex flex-col justify-between flex-grow p-3 min-h-0;
  min-height: 100px;
}

@media (min-width: 640px) {
  .product-info {
    min-height: 110px;
  }
}

@media (min-width: 768px) {
  .product-info {
    min-height: 120px;
  }
}

/* Brand Name */
.brand-name {
  @apply font-bold text-xs uppercase text-gray-600 mb-1 text-center;
  line-height: 1.2;
  /* Single line with ellipsis */
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Product Name */
.product-name {
  @apply text-sm mb-2 text-gray-800 font-medium text-center flex-grow;
  line-height: 1.3;
  /* Single line with ellipsis */
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word;
  min-height: 1.3em;
}

/* Pricing Container */
.pricing-container {
  @apply flex flex-col items-center space-y-1 mt-auto;
}

/* Current Price */
.current-price {
  @apply text-red-500 font-bold text-sm text-center;
  /* Single line with ellipsis */
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word;
  max-width: 100%;
}

/* Original Price */
.original-price {
  @apply text-gray-500 text-xs line-through text-center;
  /* Single line with ellipsis */
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word;
  max-width: 100%;
}

/* Responsive Text Sizing */
@media (min-width: 640px) {
  .brand-name {
    @apply text-sm;
  }

  .product-name {
    @apply text-base;
    min-height: 1.5em;
  }

  .current-price {
    @apply text-base;
  }

  .original-price {
    @apply text-sm;
  }
}

@media (min-width: 768px) {
  .product-name {
    line-height: 1.4;
    min-height: 1.4em;
  }
}

/* Grid Layout Consistency */
@media (max-width: 639px) {
  .product-card {
    min-height: 280px;
  }
}

@media (min-width: 640px) and (max-width: 767px) {
  .product-card {
    min-height: 320px;
  }
}

@media (min-width: 768px) and (max-width: 1023px) {
  .product-card {
    min-height: 360px;
  }
}

@media (min-width: 1024px) {
  .product-card {
    min-height: 380px;
  }
}

/* Loading State */
.product-image[loading="lazy"] {
  @apply bg-gray-100;
}

/* Accessibility */
.product-card:focus {
  @apply outline-none ring-2 ring-blue-500 ring-offset-2;
}

/* Animation for Smooth Transitions */
.product-card * {
  @apply transition-colors duration-200;
}
</style>
