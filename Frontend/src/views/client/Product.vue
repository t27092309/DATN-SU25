<style scoped>
@import '@/assets/tailwind.css';
</style>

<template>
    <div class="container mx-auto p-4 max-w-[1230px]">
        <div v-if="product" class="flex flex-col lg:flex-row justify-between gap-8">
            <div class="w-full lg:max-w-[920px] lg:flex-shrink-0">
                <div class="flex flex-col lg:flex-row gap-8">
                    <ProductCarousel :productImages="product.images.length > 0 ? product.images : [product.image]" />
                    <div class="flex-grow">
                        <h1 class="text-3xl font-semibold mb-2">{{ product.name }}</h1>
                        <div class="flex items-center mb-4">
                            <div class="flex text-yellow-400 mr-2">
                                <span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9734;</span>
                            </div>
                            <span class="text-sm text-gray-600">(9 đánh giá)</span>
                            <span class="mx-2 text-gray-300">|</span>
                            <span class="text-sm text-gray-600" v-if="selectedVariantStock !== 'N/A'">Đã bán {{
                                formatSold(product.variants) }}</span>
                            <span class="mx-2 text-gray-300" v-if="selectedVariantStock !== 'N/A'">|</span>
                            <span class="text-sm text-gray-600" v-if="selectedVariantStock !== 'N/A'">Tồn kho: {{
                                selectedVariantStock }}</span>
                        </div>
                        <p class="text-gray-700 mb-6">{{ product.description }}</p>

                        <div class="mb-4">
                            <span class="font-semibold text-gray-600">Thương hiệu:</span>
                            <a href="#" class="text-blue-600 hover:underline ml-2">{{ product.brand_name }}</a>
                        </div>
                        <div class="mb-4">
                            <span class="font-semibold text-gray-600">Loại sản phẩm:</span>
                            <router-link
                                :to="{ name: 'CategoryProducts', params: { categorySlug: product.category_slug } }"
                                class="text-blue-600 hover:underline ml-2">
                                {{ product.category_name }}
                            </router-link>
                        </div>
                        <div class="mb-6">
                            <span class="font-semibold text-gray-600">Tình trạng:</span>
                            <span
                                :class="['font-bold ml-2', selectedVariantStatus === 'available' ? 'text-green-600' : 'text-red-600']">
                                {{ selectedVariantStatus === 'available' ? 'Còn hàng' : 'Hết hàng / Ngừng kinh doanh' }}
                            </span>
                        </div>

                        <div class="text-4xl font-bold text-red-500 mb-2">{{ formatPrice(selectedVariantPrice) }}</div>
                        <p class="text-sm text-gray-500 mb-6">Giá cho Khách hàng thân thiết <i
                                class="fas fa-chevron-down ml-1"></i></p>

                        <div v-if="groupedAttributes.length > 0" class="mb-8">
                            <div v-for="attrGroup in groupedAttributes" :key="attrGroup.name" class="mb-6">
                                <h3 class="font-semibold text-gray-700 mb-3">
                                    {{ attrGroup.name }}:
                                    <span v-if="selectedAttributes[attrGroup.slug]" class="text-gray-500">
                                        {{ selectedAttributes[attrGroup.slug].value_name }}
                                    </span>
                                </h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <button v-for="attrValue in attrGroup.values" :key="attrValue.value_id"
                                        @click="selectAttributeValue(attrGroup.slug, attrValue)" :class="[
                                            'flex flex-col items-center p-2 border rounded-lg cursor-pointer',
                                            'transition-all duration-200 ease-in-out',
                                            isSelectedAttribute(attrGroup.slug, attrValue)
                                                ? 'border-pink-500 bg-pink-50'
                                                : 'border-gray-300 hover:border-pink-500',
                                            !isAttributeValueAvailable(attrGroup.slug, attrValue)
                                                ? 'opacity-50 cursor-not-allowed border-gray-200' : ''
                                        ]" :disabled="!isAttributeValueAvailable(attrGroup.slug, attrValue)">
                                        <span
                                            :class="['text-sm', isSelectedAttribute(attrGroup.slug, attrValue) ? 'font-semibold text-pink-700' : '']">
                                            {{ attrValue.value_name }}
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="!product.variants || product.variants.length === 0"
                            class="mb-8 text-gray-600">
                            Sản phẩm này không có biến thể.
                        </div>

                        <div class="mb-4">
                            <span class="font-semibold text-gray-600">Mã SKU:</span>
                            <span class="ml-2">{{ selectedVariantSku || 'N/A' }}</span>
                        </div>

                        <div class="flex items-center mb-6">
                            <span class="font-semibold text-gray-700 mr-4">Số lượng:</span>
                            <div class="flex items-center border border-gray-300 rounded-md">
                                <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-l-md hover:bg-gray-200"
                                    @click="quantity = Math.max(1, quantity - 1)">-</button>
                                <input type="number" v-model.number="quantity" min="1"
                                    class="w-16 text-center border-l border-r border-gray-200 focus:outline-none focus:border-blue-300" />
                                <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-r-md hover:bg-gray-200"
                                    @click="quantity++">+</button>
                            </div>
                        </div>
                        <div
                            class="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded-lg flex items-center mb-6 mt-4">
                            <i class="fas fa-gift mr-2"></i>
                            <span>Giảm <span class="font-bold">100K</span> khi thanh toán qua Fundiin (<a href="#"
                                    class="underline">xem thêm</a>)</span>
                        </div>
                        <div class="flex gap-4 mt-6">
                            <button
                                class="flex-1 py-3 px-6 border border-red-500 text-red-500 rounded-lg font-bold hover:bg-red-50 hover:text-red-600 transition-colors duration-200"
                                :disabled="selectedVariantStatus === 'unavailable' || selectedVariantStock === 0 || !foundVariant"
                                @click="addToCart">
                                Thêm vào giỏ hàng
                            </button>
                            <button :class="[
                                'flex-1 py-3 px-6 bg-red-500 text-white rounded-lg font-bold hover:bg-red-600 transition-colors duration-200 text-center flex items-center justify-center', // Added flex/items-center/justify-center for better text centering
                                { 'opacity-50 cursor-not-allowed': selectedVariantStatus === 'unavailable' || selectedVariantStock === 0 || !foundVariant } // Thêm !foundVariant vào điều kiện làm mờ
                            ]" @click="handleBuyNowClick"
                                :disabled="selectedVariantStatus === 'unavailable' || selectedVariantStock === 0 || !foundVariant">
                                Mua ngay
                            </button>
                        </div>

                        <div v-if="cartMessage"
                            :class="['mt-4 p-3 rounded-md text-sm', cartError ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700']">
                            {{ cartMessage }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-[310px] lg:flex-shrink-0">
                <div class="p-4 border border-gray-200 rounded-lg shadow-sm">
                    <button class="w-full py-2 px-4 border border-gray-300 rounded-lg text-gray-700 font-semibold mb-6">
                        {{ product.brand_id ? `Brand ID: ${product.brand_id}` : 'Thương hiệu không rõ' }}
                    </button>
                    <h3 class="text-center font-bold text-gray-800 mb-4">MÙI HƯƠNG CHÍNH (ACCORDS)</h3>
                    <p class="text-center text-gray-500 text-xs italic mb-6">
                        (*click tên nhóm hương để tìm hiểu chi tiết)
                    </p>
                    <div class="space-y-3 mb-8" v-if="product.scent_profiles && product.scent_profiles.length > 0">
                        <div v-for="(scent, index) in sortedScentProfiles" :key="index"
                            class="relative h-7 rounded-full bg-gray-200 overflow-hidden">
                            <div class="absolute top-0 left-0 h-full rounded-full text-xs font-medium flex items-center pl-3"
                                :style="{ width: `${scent.strength}%`, backgroundColor: scent.scent_group_color_code }"
                                :class="['text-white', { 'text-white': isDarkColor(scent.scent_group_color_code), 'text-gray-800': !isDarkColor(scent.scent_group_color_code) }]">
                                {{ scent.scent_group_name || `Group ID: ${scent.scent_group_id}` }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500">Không có thông tin mùi hương.</div>

                    <div class="text-center mb-6">
                        <img src="https://i.imgur.com/fragrantica-logo-placeholder.png" alt="Fragrantica Logo"
                            class="mx-auto h-8 mb-2" />
                        <span class="font-semibold text-gray-700 text-lg">FRAGRANTICA</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#10052;</div>
                            <div class="text-sm font-semibold text-gray-700">Đông</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-gray-500 rounded-full" style="width: 40%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#127807;</div>
                            <div class="text-sm font-semibold text-gray-700">Xuân</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-green-500 rounded-full" style="width: 60%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#9728;</div>
                            <div class="text-sm font-semibold text-gray-700">Hè</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-red-500 rounded-full" style="width: 80%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#127809;</div>
                            <div class="text-sm font-semibold text-gray-700">Thu</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-orange-500 rounded-full" style="width: 70%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#9728;</div>
                            <div class="text-sm font-semibold text-gray-700">Ngày</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-yellow-500 rounded-full" style="width: 90%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#127769;</div>
                            <div class="text-sm font-semibold text-gray-700">Đêm</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-gray-500 rounded-full" style="width: 50%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#128337;</div>
                            <div class="text-sm font-semibold text-gray-700">Lưu <br> đến 12h</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-green-500 rounded-full" style="width: 85%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="text-gray-400 text-2xl mb-1">&#128100;</div>
                            <div class="text-sm font-semibold text-gray-700">Toả <br> ~ 1 mét</div>
                            <div class="h-1 bg-gray-200 rounded-full mt-1">
                                <div class="h-full bg-red-500 rounded-full" style="width: 70%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center text-xl text-gray-500 py-10">Đang tải thông tin sản phẩm...</div>

        <ProductDescription v-if="product" :description="product.description" />
        <ProductReview />
        <RelatedProduct />
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router'; // Import useRouter
import axios from 'axios';

// Import các components đã có
import ProductReview from '@/components/ProductReview.vue';
import ProductDescription from '@/components/ProductDescription.vue';
import ProductCarousel from '@/components/ProductCarousel.vue';
import RelatedProduct from '@/components/RelatedProduct.vue';

const product = ref(null);
const route = useRoute();
const router = useRouter(); // Khởi tạo router

const selectedAttributes = ref({});
const foundVariant = ref(null);

const quantity = ref(1);
const cartMessage = ref('');
const cartError = ref(false);

const selectedVariantPrice = computed(() => foundVariant.value ? foundVariant.value.price : product.value ? product.value.price : '0');
const selectedVariantStock = computed(() => foundVariant.value ? foundVariant.value.stock : 'N/A');
const selectedVariantStatus = computed(() => foundVariant.value ? foundVariant.value.status : 'N/A');
const selectedVariantSku = computed(() => foundVariant.value ? foundVariant.value.sku : 'N/A');

const groupedAttributes = computed(() => {
    if (!product.value || !product.value.variants || product.value.variants.length === 0) {
        return [];
    }

    const attributeMap = new Map();

    product.value.variants.forEach(variant => {
        if (variant.attributes) {
            variant.attributes.forEach(attr => {
                if (!attributeMap.has(attr.attribute_slug)) {
                    attributeMap.set(attr.attribute_slug, {
                        name: attr.attribute_name,
                        slug: attr.attribute_slug,
                        values: new Map()
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

    return Array.from(attributeMap.values()).map(attrGroup => ({
        ...attrGroup,
        values: Array.from(attrGroup.values.values())
    }));
});

const selectAttributeValue = (attributeSlug, attributeValue) => {
    if (selectedAttributes.value[attributeSlug]?.value_id === attributeValue.value_id) {
        const newSelected = { ...selectedAttributes.value };
        delete newSelected[attributeSlug];
        selectedAttributes.value = newSelected;
    } else {
        selectedAttributes.value = {
            ...selectedAttributes.value,
            [attributeSlug]: attributeValue
        };
    }
};

const isSelectedAttribute = (attributeSlug, attributeValue) => {
    return selectedAttributes.value[attributeSlug]?.value_id === attributeValue.value_id;
};

const isAttributeValueAvailable = (currentAttributeSlug, currentAttributeValue) => {
    if (!product.value || !product.value.variants || product.value.variants.length === 0) {
        return false;
    }

    const currentSelectionsWithoutThisAttribute = Object.entries(selectedAttributes.value)
        .filter(([slug]) => slug !== currentAttributeSlug)
        .map(([, value]) => value);

    return product.value.variants.some(variant => {
        const hasCurrentValue = variant.attributes.some(attr =>
            attr.attribute_slug === currentAttributeSlug && attr.value_id === currentAttributeValue.value_id
        );

        if (!hasCurrentValue) {
            return false;
        }

        const matchesOtherSelections = currentSelectionsWithoutThisAttribute.every(selectedVal => {
            const selectedValSlug = Object.keys(selectedAttributes.value).find(key => selectedAttributes.value[key] === selectedVal);
            return variant.attributes.some(variantAttr =>
                variantAttr.attribute_slug === selectedValSlug &&
                variantAttr.value_id === selectedVal.value_id
            );
        });

        return hasCurrentValue && matchesOtherSelections;
    });
};

const findMatchingVariant = () => {
    foundVariant.value = null;

    if (!product.value || !product.value.variants || product.value.variants.length === 0) {
        return;
    }

    const currentSelectedAttrSlugs = Object.keys(selectedAttributes.value);

    if (groupedAttributes.value.length === 0) {
        // Handle simple products (no attributes)
        const defaultVariant = product.value.variants.find(v => !v.attributes || v.attributes.length === 0);
        if (defaultVariant) {
            foundVariant.value = defaultVariant;
        }
        return;
    }


    // Only attempt to find a variant if all attribute groups have a selection
    if (currentSelectedAttrSlugs.length !== groupedAttributes.value.length) {
        return;
    }

    const matchingVariant = product.value.variants.find(variant => {
        if (!variant.attributes || variant.attributes.length === 0) {
             return false;
        }

        return currentSelectedAttrSlugs.every(attrSlug => {
            const selectedVal = selectedAttributes.value[attrSlug];
            return variant.attributes.some(vAttr =>
                vAttr.attribute_slug === attrSlug && vAttr.value_id === selectedVal.value_id
            );
        });
    });

    foundVariant.value = matchingVariant;
};

watch(selectedAttributes, findMatchingVariant, { deep: true });

const formatPrice = (price) => {
    if (price === null || price === undefined || isNaN(price)) return '0 ₫';
    return parseFloat(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
};

const formatSold = (variants) => {
    if (!variants || variants.length === 0) return '0';
    const totalSold = variants.reduce((sum, variant) => sum + (variant.sold || 0), 0);
    if (totalSold >= 1000) {
        return `${(totalSold / 1000).toFixed(1)}K`;
    }
    return totalSold.toString();
};

const sortedScentProfiles = computed(() => {
    if (product.value && product.value.scent_profiles) {
        return [...product.value.scent_profiles].sort((a, b) => b.strength - a.strength);
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

const addToCart = async () => {
    cartMessage.value = '';
    cartError.value = false;

    if (!foundVariant.value) {
        cartMessage.value = 'Vui lòng chọn đầy đủ các thuộc tính để thêm sản phẩm vào giỏ hàng.';
        cartError.value = true;
        return;
    }

    if (quantity.value < 1) {
        cartMessage.value = 'Số lượng phải lớn hơn hoặc bằng 1.';
        cartError.value = true;
        return;
    }

    if (selectedVariantStatus.value === 'unavailable' || selectedVariantStock.value === 0) {
        cartMessage.value = 'Sản phẩm này hiện không có sẵn hoặc đã hết hàng.';
        cartError.value = true;
        return;
    }

    if (quantity.value > selectedVariantStock.value) {
        cartMessage.value = `Số lượng yêu cầu (${quantity.value}) vượt quá tồn kho hiện có (${selectedVariantStock.value}).`;
        cartError.value = true;
        return;
    }

    try {
        const response = await axios.post('cart-items', { // Đảm bảo đường dẫn API chính xác
            product_variant_id: foundVariant.value.id,
            quantity: quantity.value
        });

        if (response.status === 200 || response.status === 201) {
            cartMessage.value = response.data.message || 'Sản phẩm đã được thêm vào giỏ hàng thành công!';
            cartError.value = false;
        } else {
            cartMessage.value = `Có lỗi xảy ra: ${response.data.message || 'Lỗi không xác định'}`;
            cartError.value = true;
        }
    } catch (error) {
        console.error('Lỗi khi thêm sản phẩm vào giỏ hàng:', error);
        cartError.value = true;
        if (error.response) {
            if (error.response.status === 422) {
                const validationErrors = error.response.data.errors;
                let errorMessage = 'Vui lòng kiểm tra lại thông tin: \n';
                for (const key in validationErrors) {
                    errorMessage += `- ${validationErrors[key].join(', ')}\n`;
                }
                cartMessage.value = errorMessage;
            } else if (error.response.status === 401) {
                cartMessage.value = 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.';
            } else if (error.response.data && error.response.data.message) {
                cartMessage.value = `Lỗi: ${error.response.data.message}`;
            } else {
                cartMessage.value = 'Không thể thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.';
            }
        } else if (error.request) {
            cartMessage.value = 'Không có phản hồi từ server. Vui lòng kiểm tra kết nối mạng của bạn.';
        } else {
            cartMessage.value = 'Đã xảy ra lỗi không mong muốn. Vui lòng thử lại.';
        }
    }
};

const handleBuyNowClick = () => {
    cartMessage.value = '';
    cartError.value = false;

    if (!foundVariant.value) {
        cartMessage.value = 'Vui lòng chọn đầy đủ các thuộc tính để mua sản phẩm.';
        cartError.value = true;
        return;
    }

    if (quantity.value < 1) {
        cartMessage.value = 'Số lượng phải lớn hơn hoặc bằng 1.';
        cartError.value = true;
        return;
    }

    if (selectedVariantStatus.value === 'unavailable' || selectedVariantStock.value === 0) {
        cartMessage.value = 'Sản phẩm này hiện không có sẵn để mua hoặc đã hết hàng.';
        cartError.value = true;
        return;
    }

    if (quantity.value > selectedVariantStock.value) {
        cartMessage.value = `Số lượng yêu cầu (${quantity.value}) vượt quá tồn kho hiện có (${selectedVariantStock.value}).`;
        cartError.value = true;
        return;
    }

    // Chuyển hướng trực tiếp đến trang thanh toán với query parameters
    router.push({
        path: '/thanh-toan',
        query: {
            variant_id: foundVariant.value.id,
            qty: quantity.value,
            buy_now: 'true' // Flag để trang thanh toán biết đây là luồng "Mua ngay"
        }
    });
};


onMounted(async () => {
    const productSlug = route.params.slug;
    if (!productSlug) {
        console.error('Không tìm thấy slug sản phẩm trong URL.');
        return;
    }

    try {
        const response = await fetch(`http://localhost:8000/api/detailproducts/${productSlug}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        product.value = data.data;

        if (product.value && product.value.variants && product.value.variants.length > 0) {
            const firstVariant = product.value.variants[0];
            const initialSelectedAttributes = {};
            if (firstVariant.attributes) {
                firstVariant.attributes.forEach(attr => {
                    initialSelectedAttributes[attr.attribute_slug] = {
                        value_id: attr.value_id,
                        value_name: attr.value_name
                    };
                });
            }
            selectedAttributes.value = initialSelectedAttributes;
        } else if (product.value && (!product.value.variants || product.value.variants.length === 0)) {
            const defaultVariant = product.value.variants.find(v => !v.attributes || v.attributes.length === 0);
            if (defaultVariant) {
                foundVariant.value = defaultVariant;
            } else {
                console.warn('Sản phẩm không có biến thể và cũng không tìm thấy biến thể mặc định.');
            }
        }

    } catch (error) {
        console.error('Lỗi khi lấy dữ liệu sản phẩm:', error);
        cartMessage.value = 'Không thể tải thông tin sản phẩm. Vui lòng thử lại sau.';
        cartError.value = true;
    }
});
</script>