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
                            <span class="text-sm text-gray-600">Đã bán {{ formatSold(product.variants) }}</span>
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
                        <div class="mb-6">
                            <span class="font-semibold text-gray-600">Mã vạch:</span>
                            <span class="ml-2">{{ selectedVariantBarcode || 'N/A' }}</span>
                            <div class="mt-2 w-36" v-if="selectedVariantBarcode && selectedVariantBarcode !== 'N/A'">
                                <canvas id="barcodeCanvas"></canvas>
                            </div>
                            <div v-else class="text-sm text-gray-500 mt-2">
                                Không có mã vạch cho biến thể này.
                            </div>
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
                        <div v-else-if="!product.variants || product.variants.length === 0" class="mb-8 text-gray-600">
                            Sản phẩm này không có biến thể.
                        </div>

                        <div class="mb-4">
                            <span class="font-semibold text-gray-600">Mã SKU:</span>
                            <span class="ml-2">{{ selectedVariantSku || 'N/A' }}</span>
                        </div>



                        <div class="flex gap-4 mt-6">
                            <button
                                class="flex-1 py-3 px-6 border border-red-500 text-red-500 rounded-lg font-bold hover:bg-red-50 hover:text-red-600 transition-colors duration-200"
                                :disabled="selectedVariantStatus === 'unavailable' || selectedVariantStock === 0"
                                @click="addToCart">
                                Thêm vào giỏ hàng
                            </button>
                            <button
                                class="flex-1 py-3 px-6 bg-red-500 text-white rounded-lg font-bold hover:bg-red-600 transition-colors duration-200"
                                :disabled="selectedVariantStatus === 'unavailable' || selectedVariantStock === 0"
                                @click="buyNow">
                                Mua ngay
                            </button>
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
                                :disabled="selectedVariantStatus === 'unavailable' || selectedVariantStock === 0"
                                @click="addToCart">
                                Thêm vào giỏ hàng
                            </button>
                            <router-link :to="{ path: '/thanh-toan' }" :class="[
                                'flex-1 py-3 px-6 bg-red-500 text-white rounded-lg font-bold hover:bg-red-600 transition-colors duration-200 text-center flex items-center justify-center', // Added flex/items-center/justify-center for better text centering
                                { 'opacity-50 cursor-not-allowed': selectedVariantStatus === 'unavailable' || selectedVariantStock === 0 || !foundVariant } // Thêm !foundVariant vào điều kiện làm mờ
                            ]" @click.prevent="handleBuyNowClick"
                                v-if="selectedVariantStatus !== 'unavailable' && selectedVariantStock !== 0">
                                Mua ngay
                            </router-link>
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
                                :style="{ width: `${scent.strength}%`, backgroundColor: getScentColor(scent.scent_group_id) }"
                                :class="['text-white', { 'text-white': isDarkColor(getScentColor(scent.scent_group_id)), 'text-gray-800': !isDarkColor(getScentColor(scent.scent_group_id)) }]">
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
import { ref, onMounted, computed, watch, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import JsBarcode from 'jsbarcode';

import ProductReview from '@/components/ProductReview.vue';
import ProductDescription from '@/components/ProductDescription.vue';
import ProductCarousel from '@/components/ProductCarousel.vue';
import RelatedProduct from '@/components/RelatedProduct.vue';

const product = ref(null);
const route = useRoute();

// Khởi tạo một đối tượng để lưu trữ các giá trị thuộc tính đã chọn
// Key là attribute_slug, value là đối tượng { value_id, value_name }
const selectedAttributes = ref({});

// Ref để lưu trữ biến thể tìm được dựa trên các lựa chọn thuộc tính
const foundVariant = ref(null);

// Computed properties để hiển thị thông tin của biến thể đã tìm thấy
const selectedVariantPrice = computed(() => foundVariant.value ? foundVariant.value.price : product.value ? product.value.price : '0');
const selectedVariantStock = computed(() => foundVariant.value ? foundVariant.value.stock : 'N/A');
const selectedVariantStatus = computed(() => foundVariant.value ? foundVariant.value.status : 'N/A');
const selectedVariantBarcode = computed(() => foundVariant.value ? foundVariant.value.barcode : 'N/A');
const selectedVariantSku = computed(() => foundVariant.value ? foundVariant.value.sku : 'N/A');

// Computed property để nhóm các thuộc tính
const groupedAttributes = computed(() => {
    if (!product.value || !product.value.variants || product.value.variants.length === 0) {
        return [];
    }

    const attributeMap = new Map(); // Map để lưu trữ các thuộc tính duy nhất

    product.value.variants.forEach(variant => {
        if (variant.attributes) {
            variant.attributes.forEach(attr => {
                if (!attributeMap.has(attr.attribute_slug)) {
                    attributeMap.set(attr.attribute_slug, {
                        name: attr.attribute_name,
                        slug: attr.attribute_slug,
                        values: new Map() // Map để lưu trữ các giá trị thuộc tính duy nhất cho nhóm này
                    });
                }
                const attrGroup = attributeMap.get(attr.attribute_slug);
                if (!attrGroup.values.has(attr.value_id)) {
                    attrGroup.values.set(attr.value_id, {
                        value_id: attr.value_id,
                        value_name: attr.value_name,
                        // Có thể thêm image_url nếu API của bạn cung cấp hình ảnh cho mỗi giá trị thuộc tính
                    });
                }
            });
        }
    });

    // Chuyển đổi Map thành mảng để dễ dàng lặp trong template
    return Array.from(attributeMap.values()).map(attrGroup => ({
        ...attrGroup,
        values: Array.from(attrGroup.values.values())
    }));
});

// Hàm để chọn một giá trị thuộc tính
const selectAttributeValue = (attributeSlug, attributeValue) => {
    // Nếu giá trị này đã được chọn, bỏ chọn nó
    if (selectedAttributes.value[attributeSlug]?.value_id === attributeValue.value_id) {
        // Xóa thuộc tính đó khỏi danh sách đã chọn
        const newSelected = { ...selectedAttributes.value };
        delete newSelected[attributeSlug];
        selectedAttributes.value = newSelected;
    } else {
        // Nếu không, chọn giá trị này
        selectedAttributes.value = {
            ...selectedAttributes.value,
            [attributeSlug]: attributeValue
        };
    }
    // findMatchingVariant() sẽ được gọi tự động bởi watch
};

// Hàm kiểm tra xem một giá trị thuộc tính có đang được chọn hay không
const isSelectedAttribute = (attributeSlug, attributeValue) => {
    return selectedAttributes.value[attributeSlug]?.value_id === attributeValue.value_id;
};


// HÀM MỚI VÀ ĐÃ ĐIỀU CHỈNH LOGIC
// Hàm kiểm tra xem một giá trị thuộc tính có sẵn để chọn hay không
const isAttributeValueAvailable = (currentAttributeSlug, currentAttributeValue) => {
    if (!product.value || !product.value.variants || product.value.variants.length === 0) {
        return false;
    }

    // Lấy các lựa chọn hiện tại ĐÃ CÓ (ngoại trừ thuộc tính đang xét)
    const currentSelectionsWithoutThisAttribute = Object.entries(selectedAttributes.value)
        .filter(([slug]) => slug !== currentAttributeSlug)
        .map(([, value]) => value);

    // Kiểm tra xem có bất kỳ biến thể nào chứa `currentAttributeValue` VÀ khớp với tất cả
    // các lựa chọn khác đã có trong `currentSelectionsWithoutThisAttribute` hay không.
    return product.value.variants.some(variant => {
        // 1. Biến thể phải chứa `currentAttributeValue`
        const hasCurrentValue = variant.attributes.some(attr =>
            attr.attribute_slug === currentAttributeSlug && attr.value_id === currentAttributeValue.value_id
        );

        if (!hasCurrentValue) {
            return false;
        }

        // 2. Biến thể phải khớp với TẤT CẢ các lựa chọn khác đã được chọn
        const matchesOtherSelections = currentSelectionsWithoutThisAttribute.every(selectedVal => {
            return variant.attributes.some(variantAttr =>
                variantAttr.attribute_slug === Object.keys(selectedAttributes.value).find(key => selectedAttributes.value[key] === selectedVal) &&
                variantAttr.value_id === selectedVal.value_id
            );
        });

        return hasCurrentValue && matchesOtherSelections;
    });
};


// Hàm tìm biến thể phù hợp dựa trên các thuộc tính đã chọn
const findMatchingVariant = () => {
    foundVariant.value = null; // Reset biến thể tìm được

    if (!product.value || !product.value.variants || product.value.variants.length === 0) {
        return;
    }

    const currentSelectedAttrSlugs = Object.keys(selectedAttributes.value);

    // Nếu số lượng thuộc tính đã chọn không khớp với tổng số nhóm thuộc tính
    // thì chưa thể xác định một biến thể duy nhất (trừ khi chỉ có 1 nhóm thuộc tính)
    if (currentSelectedAttrSlugs.length !== groupedAttributes.value.length) {
        return; // Không tìm biến thể nếu chưa chọn đủ tất cả các loại thuộc tính
    }

    const matchingVariant = product.value.variants.find(variant => {
        if (!variant.attributes || variant.attributes.length === 0) return false;

        // Kiểm tra xem tất cả các thuộc tính đã chọn có khớp với thuộc tính của biến thể không
        return currentSelectedAttrSlugs.every(attrSlug => {
            const selectedVal = selectedAttributes.value[attrSlug];
            return variant.attributes.some(vAttr =>
                vAttr.attribute_slug === attrSlug && vAttr.value_id === selectedVal.value_id
            );
        });
    });

    foundVariant.value = matchingVariant;
};

const generateBarcode = (barcodeData) => {
    const canvas = document.getElementById('barcodeCanvas');
    if (canvas && barcodeData && barcodeData !== 'N/A') {
        try {
            // JsBarcode sẽ vẽ mã vạch lên canvas
            // Bạn có thể tùy chỉnh các tùy chọn ở đây (format, displayValue, lineColor, etc.)
            JsBarcode(canvas, barcodeData, {
                format: "CODE128", // Hoặc EAN13, UPC, etc. tùy thuộc vào định dạng mã vạch của bạn
                displayValue: true, // Hiển thị giá trị mã vạch bên dưới
                lineColor: "#000",
                width: 2,
                height: 50,
                margin: 0
            });
        } catch (error) {
            console.error("Lỗi khi tạo mã vạch:", error);
            // Xóa nội dung canvas nếu có lỗi
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }
    } else if (canvas) {
        // Xóa mã vạch nếu không có dữ liệu
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
};

// Theo dõi sự thay đổi của foundVariant để generate lại barcode
watch(foundVariant, async (newVariant) => {
    // nextTick đảm bảo DOM đã được cập nhật (canvas đã tồn tại) trước khi gọi JsBarcode
    await nextTick();
    if (newVariant && newVariant.barcode) {
        generateBarcode(newVariant.barcode);
    } else {
        generateBarcode(null); // Xóa barcode nếu không có biến thể hoặc barcode
    }
}, { immediate: true }); // immediate: true để chạy lần đầu khi component mount

// Theo dõi sự thay đổi của selectedAttributes để tự động tìm biến thể phù hợp
watch(selectedAttributes, findMatchingVariant, { deep: true });


// Hàm định dạng tiền tệ
const formatPrice = (price) => {
    if (!price) return '0 ₫';
    return parseFloat(price).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
};

// Hàm tính tổng số lượng đã bán từ tất cả các biến thể
const formatSold = (variants) => {
    if (!variants || variants.length === 0) return '0';
    const totalSold = variants.reduce((sum, variant) => sum + (variant.sold || 0), 0);
    if (totalSold >= 1000) {
        return `${(totalSold / 1000).toFixed(1)}K`;
    }
    return totalSold.toString();
};

// Computed property để sắp xếp scent_profiles theo strength giảm dần
const sortedScentProfiles = computed(() => {
    if (product.value && product.value.scent_profiles) {
        return [...product.value.scent_profiles].sort((a, b) => b.strength - a.strength);
    }
    return [];
});

// Hàm trả về màu sắc dựa trên scent_group_id hoặc ngẫu nhiên
const getScentColor = (scentGroupId) => {
    const colors = {
        88: '#A78BFA', // purple-400
        53: '#EC4899', // pink-500
        90: '#F472B6', // pink-400
        76: '#F97316', // orange-500
        49: '#10B981', // green-500
    };
    return colors[scentGroupId] || `#${Math.floor(Math.random() * 16777215).toString(16)}`;
};

// Hàm kiểm tra màu tối hay sáng để chọn màu chữ phù hợp
const isDarkColor = (hexColor) => {
    if (!hexColor) return false;
    const r = parseInt(hexColor.slice(1, 3), 16);
    const g = parseInt(hexColor.slice(3, 5), 16);
    const b = parseInt(hexColor.slice(5, 7), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance < 0.5;
};

// Placeholder functions for button actions
const addToCart = () => {
    if (foundVariant.value) {
        alert(`Thêm vào giỏ hàng: Sản phẩm ${product.value.name}, Biến thể: ${JSON.stringify(selectedAttributes.value)}`);
        // Implement your actual add to cart logic here
        // e.g., using a Vuex store, an API call, etc.
    } else {
        alert('Vui lòng chọn đầy đủ các thuộc tính để thêm sản phẩm vào giỏ hàng.');
    }
};

const buyNow = () => {
    if (foundVariant.value) {
        alert(`Mua ngay: Sản phẩm ${product.value.name}, Biến thể: ${JSON.stringify(selectedAttributes.value)}`);
        // Implement your actual buy now logic here
        // e.g., redirect to checkout with this product
    } else {
        alert('Vui lòng chọn đầy đủ các thuộc tính để mua sản phẩm.');
    }
};

const handleBuyNowClick = (event) => {
    // Nếu biến thể không có sẵn (hết hàng, ngừng kinh doanh) hoặc không có tồn kho
    if (selectedVariantStatus.value === 'unavailable' || selectedVariantStock.value === 0) {
        event.preventDefault(); // Ngăn router-link chuyển hướng
        alert('Sản phẩm này hiện không có sẵn để mua.');
        return;
    }

    // THÊM ĐIỀU KIỆN NÀY: Nếu chưa tìm thấy biến thể phù hợp (chưa chọn đủ thuộc tính)
    if (!foundVariant.value) {
        event.preventDefault(); // Ngăn router-link chuyển hướng
        alert('Vui lòng chọn đầy đủ các thuộc tính để mua sản phẩm.');
        return;
    }

    // Nếu đã chọn đủ thuộc tính và biến thể hợp lệ
    alert(`Chuẩn bị mua ngay: Sản phẩm ${product.value.name}, Biến thể: ${JSON.stringify(selectedAttributes.value)}. Chuyển hướng đến trang thanh toán.`);
    // router-link sẽ tự động xử lý việc chuyển hướng nếu không bị preventDefault()
};

// Hook lifecycle: Gọi API khi component được mount
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

        // Tự động chọn biến thể đầu tiên để có dữ liệu ban đầu
        // hoặc chọn các thuộc tính của biến thể đầu tiên làm mặc định
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
            // findMatchingVariant sẽ được gọi bởi watch sau khi selectedAttributes thay đổi
        }

    } catch (error) {
        console.error('Lỗi khi lấy dữ liệu sản phẩm:', error);
    }
});
</script>