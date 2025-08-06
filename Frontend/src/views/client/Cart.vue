```vue
<template>
    <div class="bg-gray-100 min-h-screen">
        <div class="max-w-[1200px] mx-auto">
            <main class="p-4 md:p-6 flex-grow relative">
                <div
                    class="bg-white p-4 rounded-t-lg shadow-md grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center text-gray-700 font-semibold gap-x-4">
                    <div class="col-span-1"></div>
                    <div class="col-span-1">Sản Phẩm</div>
                    <div class="col-span-1 text-center">Đơn Giá</div>
                    <div class="col-span-1 text-center">Số Lượng</div>
                    <div class="col-span-1 text-center">Số Tiền</div>
                    <div class="col-span-1 text-center">Thao Tác</div>
                </div>

                <div v-if="loading" class="text-center py-8 text-gray-600">
                    <div class="flex justify-center items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Đang tải giỏ hàng...</span>
                    </div>
                </div>
                <div v-else-if="error" class="text-center py-8 text-orange-600 bg-orange-50 rounded-lg">{{ error }}</div>
                <div v-else-if="!cartData || !cartData.items || cartData.items.length === 0" class="text-center py-8 text-gray-600 bg-white rounded-lg shadow-sm">
                    Giỏ hàng của bạn đang trống.
                </div>
                <div v-else>
                    <div v-for="item in cartData.items" :key="item.id"
                        class="bg-white rounded-lg shadow-sm mb-4 p-4 grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center gap-x-4 transition-all duration-200"
                        :class="{ 'opacity-70': isOutOfStock(item) }">
                        <div class="col-span-1">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded"
                                :checked="selectedItems.has(item.id)" @change="toggleItemSelection(item.id)"
                                :disabled="isOutOfStock(item)" />
                        </div>
                        <div class="flex items-center space-x-4 col-span-1">
                            <div
                                class="w-20 h-20 bg-gray-200 object-cover rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 overflow-hidden">
                                <img v-if="item.thumbnail_url" :src="item.thumbnail_url" :alt="item.product_name"
                                    class="w-full h-full object-cover rounded-lg" />
                                <span v-else>Không ảnh</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-800 font-medium text-base">{{ item.product_name }}</p>
                                <div v-if="isOutOfStock(item)" class="flex items-center text-orange-600 text-sm mt-2 font-semibold bg-orange-50 px-3 py-1 rounded-lg">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Hết hàng
                                </div>
                                <div v-else-if="item.available_variants && item.available_variants.length > 0"
                                    class="flex items-center text-gray-500 text-sm mt-2">
                                    <span>Phân Loại Hàng:</span>
                                    <div class="relative flex-grow min-w-0 ml-2">
                                        <select
                                            class="border border-gray-300 rounded-md px-3 py-1 text-sm appearance-none block w-full truncate pr-8 cursor-pointer bg-white hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-200"
                                            :value="item.variant ? item.variant.id : null"
                                            @change="event => handleChangeVariant(item.id, parseInt(event.target.value))"
                                            :disabled="isOutOfStock(item)">
                                            <option v-for="availableVariant in item.available_variants"
                                                :key="availableVariant.id" :value="availableVariant.id"
                                                :disabled="availableVariant.stock < item.quantity"
                                                :class="{ 'text-gray-400': availableVariant.stock < item.quantity }">
                                                {{ truncateText(availableVariant.name, 30) }}
                                                <span v-if="availableVariant.stock < item.quantity">(Hết hàng: {{ availableVariant.stock }})</span>
                                            </option>
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 6.757 7.586 5.343 9z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-gray-500 text-sm mt-2">
                                    <span>Không có phân loại</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 text-center">
                            <span v-if="!isOutOfStock(item)" class="text-red-500 font-semibold text-lg">{{ formatCurrency(item.price) }}</span>
                            <span v-else class="text-orange-600 font-semibold text-sm bg-orange-50 px-3 py-1 rounded-lg">Hết hàng</span>
                        </div>
                        <div class="col-span-1 flex justify-center">
                            <div v-if="!isOutOfStock(item)" class="flex items-center border border-gray-300 rounded-md bg-gray-50">
                                <button class="px-3 py-1 text-gray-700 rounded-l-md hover:bg-red-100 transition-colors"
                                    @click="handleChangeQuantity(item.id, item.quantity - 1)"
                                    :disabled="item.quantity <= 1">
                                    -
                                </button>
                                <input type="number" :value="item.quantity"
                                    @input="event => handleChangeQuantity(item.id, parseInt(event.target.value))"
                                    class="w-14 text-center border-l border-r border-gray-300 bg-white focus:outline-none focus:ring-2 focus:ring-red-200"
                                    min="1" :disabled="isOutOfStock(item)" />
                                <button class="px-3 py-1 text-gray-700 rounded-r-md hover:bg-red-100 transition-colors"
                                    @click="handleChangeQuantity(item.id, item.quantity + 1)"
                                    :disabled="isOutOfStock(item)">
                                    +
                                </button>
                            </div>
                            <span v-else class="text-orange-600 font-semibold text-sm bg-orange-50 px-3 py-1 rounded-lg">Hết hàng</span>
                        </div>
                        <div class="col-span-1 text-center">
                            <span v-if="!isOutOfStock(item)" class="text-red-500 font-semibold text-lg">{{ formatCurrency(item.price * item.quantity) }}</span>
                            <span v-else class="text-orange-600 font-semibold text-sm bg-orange-50 px-3 py-1 rounded-lg">Hết hàng</span>
                        </div>
                        <div class="col-span-1 text-sm space-y-1 text-center">
                            <button class="text-blue-500 text-base hover:text-red-500 block mx-auto transition-colors"
                                @click="handleRemoveCartItem(item.id)">
                                Xóa
                            </button>
                        </div>
                    </div>
                </div>

                <footer
                    class="bg-white p-4 flex justify-between items-center rounded-b-lg shadow-md sticky bottom-0 z-10">
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox"
                                class="outline-none focus:outline-none form-checkbox h-5 w-5 text-red-500 rounded"
                                :checked="isAllSelected" @change="toggleSelectAll" />
                            <span class="ml-2 text-gray-700">Chọn Tất Cả ({{ totalSelectedItemsInCart }})</span>
                        </label>
                        <button class="text-blue-500 hover:text-red-500 transition-colors" @click="handleRemoveSelectedItems">Xóa</button>
                        <button class="text-blue-500 hover:text-red-500 transition-colors">Bỏ sản phẩm không hoạt động</button>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-700 font-medium">Tổng cộng ({{ totalSelectedItemsInCart }} Sản phẩm):</span>
                        <span class="text-red-500 font-bold text-xl ml-2">{{ formatCurrency(subtotalSelectedAmount) }}</span>
                        <button class="bg-red-500 text-white font-semibold py-3 px-6 rounded-lg ml-4 hover:bg-red-600 transition-colors"
                            @click="handleCheckout">Mua Hàng</button>
                    </div>
                </footer>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

// --- Biến trạng thái ---
const cartData = ref(null);
const loading = ref(true);
const error = ref(null);
const selectedItems = ref(new Set());

// --- Hàm tiện ích ---
const formatCurrency = (amount) => {
    if (amount === null || amount === undefined || isNaN(amount)) {
        return '₫0';
    }
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
};

const truncateText = (text, maxLength) => {
    if (!text) return '';
    if (text.length > maxLength) {
        return text.substring(0, maxLength) + '...';
    }
    return text;
};

/**
 * Kiểm tra xem sản phẩm có hết hàng không.
 * @param {Object} item - Đối tượng sản phẩm trong giỏ hàng.
 * @returns {boolean} True nếu sản phẩm hoặc biến thể đã chọn hết hàng.
 */
const isOutOfStock = (item) => {
    if (!item) return true;
    if (item.available_variants && item.available_variants.length > 0 && item.variant) {
        return item.variant.stock <= 0 || item.quantity > item.variant.stock;
    }
    return item.stock !== undefined && (item.stock <= 0 || item.quantity > item.stock);
};

// --- Computed Properties ---
const totalCartItemsCount = computed(() => {
    return cartData.value && cartData.value.items ? cartData.value.items.length : 0;
});

const totalSelectedItemsInCart = computed(() => {
    let count = 0;
    if (cartData.value && cartData.value.items) {
        cartData.value.items.forEach(item => {
            if (selectedItems.value.has(item.id) && !isOutOfStock(item)) {
                count += item.quantity;
            }
        });
    }
    return count;
});

const subtotalSelectedAmount = computed(() => {
    let total = 0;
    if (cartData.value && cartData.value.items) {
        cartData.value.items.forEach(item => {
            if (selectedItems.value.has(item.id) && !isOutOfStock(item)) {
                total += parseFloat(item.price) * item.quantity;
            }
        });
    }
    return total;
});

const isAllSelected = computed(() => {
    if (!cartData.value || !cartData.value.items || cartData.value.items.length === 0) {
        return false;
    }
    const inStockItems = cartData.value.items.filter(item => !isOutOfStock(item));
    return inStockItems.length > 0 && inStockItems.every(item => selectedItems.value.has(item.id));
});

// --- Logic chọn sản phẩm ---
const toggleItemSelection = (itemId) => {
    if (selectedItems.value.has(itemId)) {
        selectedItems.value.delete(itemId);
    } else {
        const item = cartData.value.items.find(i => i.id === itemId);
        if (item && !isOutOfStock(item)) {
            selectedItems.value.add(itemId);
        }
    }
};

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedItems.value.clear();
    } else {
        if (cartData.value && cartData.value.items) {
            cartData.value.items.forEach(item => {
                if (!isOutOfStock(item)) {
                    selectedItems.value.add(item.id);
                }
            });
        }
    }
};

// --- Logic gọi API ---
const fetchCartData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/cart-items');
        if (response.data && Array.isArray(response.data.items)) {
            cartData.value = response.data;
            const existingItemIds = new Set(cartData.value.items.map(item => item.id));
            selectedItems.value = new Set([...selectedItems.value].filter(id => existingItemIds.has(id)));
            if (cartData.value.items.length > 0 && selectedItems.value.size === 0) {
                toggleSelectAll();
            }
        } else {
            throw new Error('Dữ liệu API không đúng định dạng');
        }
    } catch (err) {
        console.error('Lỗi khi tải dữ liệu giỏ hàng:', err);
        error.value = err.response?.status === 401
            ? 'Bạn cần đăng nhập để xem giỏ hàng.'
            : `Không thể tải giỏ hàng: ${err.message || 'Vui lòng thử lại sau.'}`;
        cartData.value = null;
    } finally {
        loading.value = false;
    }
};

const handleChangeQuantity = async (cartItemId, newQuantity) => {
    if (newQuantity < 1) {
        newQuantity = 1;
    }

    const itemIndex = cartData.value?.items?.findIndex(item => item.id === cartItemId);
    if (itemIndex === -1) {
        console.error('Không tìm thấy cart item để cập nhật số lượng.');
        return;
    }

    const item = cartData.value.items[itemIndex];
    if (isOutOfStock(item)) {
        alert('Sản phẩm đã hết hàng, không thể thay đổi số lượng.');
        return;
    }

    const originalQuantity = item.quantity;
    cartData.value.items[itemIndex].quantity = newQuantity;

    try {
        await axios.put(`/cart-items/${cartItemId}`, { quantity: newQuantity });
        await fetchCartData();
    } catch (err) {
        console.error('Lỗi khi cập nhật số lượng:', err);
        alert(err.response?.data?.message || 'Không thể cập nhật số lượng. Vui lòng thử lại.');
        if (itemIndex !== -1) {
            cartData.value.items[itemIndex].quantity = originalQuantity;
        }
        await fetchCartData();
    }
};

const handleChangeVariant = async (cartItemId, newVariantId) => {
    const itemIndex = cartData.value?.items?.findIndex(item => item.id === cartItemId);
    if (itemIndex === -1) {
        console.error('Không tìm thấy cart item để thay đổi biến thể.');
        return;
    }

    const currentItem = cartData.value.items[itemIndex];
    if (isOutOfStock(currentItem)) {
        alert('Sản phẩm đã hết hàng, không thể thay đổi biến thể.');
        return;
    }

    if (currentItem.variant && currentItem.variant.id === newVariantId) {
        return;
    }

    const newVariant = currentItem.available_variants?.find(v => v.id === newVariantId);
    if (!newVariant) {
        alert('Biến thể không hợp lệ.');
        await fetchCartData();
        return;
    }

    if (newVariant.stock < currentItem.quantity) {
        alert(`Biến thể này chỉ còn ${newVariant.stock} sản phẩm. Vui lòng giảm số lượng hoặc chọn biến thể khác.`);
        await fetchCartData();
        return;
    }

    try {
        await axios.put(`/cart-items/${cartItemId}`, { product_variant_id: newVariantId });
        await fetchCartData();
    } catch (err) {
        console.error('Lỗi khi thay đổi biến thể:', err);
        alert(err.response?.data?.message || 'Không thể thay đổi biến thể. Vui lòng thử lại.');
        await fetchCartData();
    }
};

const handleRemoveCartItem = async (cartItemId) => {
    if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        return;
    }

    try {
        await axios.delete(`/cart-items/${cartItemId}`);
        selectedItems.value.delete(cartItemId);
        await fetchCartData();
    } catch (err) {
        console.error('Lỗi khi xóa sản phẩm:', err);
        alert(err.response?.data?.message || 'Không thể xóa sản phẩm. Vui lòng thử lại.');
    }
};

const handleRemoveSelectedItems = async () => {
    if (selectedItems.value.size === 0) {
        alert('Vui lòng chọn ít nhất một sản phẩm để xóa.');
        return;
    }

    if (!confirm(`Bạn có chắc chắn muốn xóa ${selectedItems.value.size} sản phẩm đã chọn khỏi giỏ hàng?`)) {
        return;
    }

    try {
        const itemIdsToDelete = Array.from(selectedItems.value);
        await Promise.all(itemIdsToDelete.map(id => axios.delete(`/cart-items/${id}`)));
        selectedItems.value.clear();
        await fetchCartData();
    } catch (err) {
        console.error('Lỗi khi xóa các sản phẩm đã chọn:', err);
        alert(err.response?.data?.message || 'Không thể xóa các sản phẩm đã chọn. Vui lòng thử lại.');
    }
};

const handleCheckout = async () => {
    if (selectedItems.value.size === 0) {
        alert('Vui lòng chọn sản phẩm để mua hàng.');
        return;
    }

    // Gọi lại API để lấy dữ liệu tồn kho mới nhất
    await fetchCartData();

    const selectedCartItemIds = Array.from(selectedItems.value);
    const unavailableItems = [];

    // Kiểm tra tồn kho cho các sản phẩm đã chọn
    selectedCartItemIds.forEach(itemId => {
        const item = cartData.value?.items?.find(i => i.id === itemId);
        if (item && isOutOfStock(item)) {
            const variantName = item.variant ? item.variant.name : 'Không có phân loại';
            unavailableItems.push(`${item.product_name} (${variantName})`);
        }
    });

    if (unavailableItems.length > 0) {
        alert(`Các sản phẩm sau đã hết hàng, vui lòng kiểm tra lại:\n- ${unavailableItems.join('\n- ')}`);
        return;
    }

    // Nếu tất cả sản phẩm đều còn hàng, chuyển hướng đến trang thanh toán
    router.push({
        name: 'ThanhToan',
        query: {
            cart_item_ids: selectedCartItemIds.join(',')
        }
    });

    console.log('Chuyển đến trang thanh toán với các Cart Item IDs:', selectedCartItemIds);
};

onMounted(() => {
    fetchCartData();
});
</script>

<style scoped>
@import '@/assets/tailwind.css';

input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type='number'] {
    -moz-appearance: textfield;
}
</style>
```