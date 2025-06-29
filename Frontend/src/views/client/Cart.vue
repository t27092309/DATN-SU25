<template>
    <div class="bg-gray-100">
        <div class="max-w-[1200px] mx-auto">
            <main class="p-4 md:p-6 flex-grow relative">
                <div
                    class="bg-white p-3 rounded-t-md grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center text-gray-600 font-semibold gap-x-2">
                    <div class="col-span-1"></div>
                    <div class="col-span-1">Sản Phẩm</div>
                    <div class="col-span-1 text-center">Đơn Giá</div>
                    <div class="col-span-1 text-center">Số Lượng</div>
                    <div class="col-span-1 text-center">Số Tiền</div>
                    <div class="col-span-1 text-center">Thao Tác</div>
                </div>

                <div v-if="loading" class="text-center py-8 text-gray-600">Đang tải giỏ hàng...</div>
                <div v-else-if="error" class="text-center py-8 text-red-600">{{ error }}</div>
                <div v-else-if="!cartData || cartData.items.length === 0" class="text-center py-8 text-gray-600">
                    Giỏ hàng của bạn đang trống.
                </div>

                <div v-else>
                    <div v-for="item in cartData.items" :key="item.id"
                        class="bg-white rounded-md shadow-sm mb-3 p-4 grid grid-cols-[30px_minmax(0,2.5fr)_repeat(3,1fr)_120px] items-center gap-x-2">
                        <div class="col-span-1">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded"
                                :checked="selectedItems.has(item.id)" @change="toggleItemSelection(item.id)" />
                        </div>
                        <div class="flex items-center space-x-3 col-span-1">
                            <div
                                class="w-20 h-20 bg-gray-200 object-cover rounded-md border border-gray-200 flex items-center justify-center text-gray-500 overflow-hidden">
                                <img v-if="item.thumbnail_url" :src="item.thumbnail_url" :alt="item.product_name"
                                    class="w-full h-full object-cover rounded-md" />
                                <span v-else>Không ảnh</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-gray-800 font-medium text-base">{{ item.product_name }}</p>

                                <div v-if="item.available_variants && item.available_variants.length > 0"
                                    class="flex items-center text-gray-500 text-sm mt-1">
                                    <span>Phân Loại Hàng:</span>
                                    <div class="relative flex-grow min-w-0">
                                        <select
                                            class="border rounded-md ml-2 px-2 py-1 text-sm appearance-none block w-full truncate pr-8 cursor-pointer"
                                            :value="item.variant ? item.variant.id : null"
                                            @change="event => handleChangeVariant(item.id, parseInt(event.target.value))">
                                            <option v-for="availableVariant in item.available_variants"
                                                :key="availableVariant.id" :value="availableVariant.id"
                                                :disabled="availableVariant.stock < item.quantity"
                                                :class="{ 'text-gray-400': availableVariant.stock < item.quantity }">
                                                {{ truncateText(availableVariant.name, 30) }}
                                                <span v-if="availableVariant.stock < item.quantity" >(Hết hàng: {{
                                                    availableVariant.stock }})
                                                </span>
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
                                <div v-else class="text-gray-500 text-sm mt-1">
                                    <span>Không có phân loại</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-1 text-center">
                            <span class="text-red-500 font-semibold text-lg">{{ formatCurrency(item.price) }}</span>
                        </div>
                        <div class="col-span-1 flex justify-center">
                            <div class="flex items-center border rounded-md">
                                <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-l-md hover:bg-gray-200"
                                    @click="handleChangeQuantity(item.id, item.quantity - 1)"
                                    :disabled="item.quantity <= 1">
                                    -
                                </button>
                                <input type="number" :value="item.quantity"
                                    @input="event => handleChangeQuantity(item.id, parseInt(event.target.value))"
                                    class="w-14 text-center border-l border-r border-gray-200 focus:outline-none focus:border-blue-300"
                                    min="1" />
                                <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-r-md hover:bg-gray-200"
                                    @click="handleChangeQuantity(item.id, item.quantity + 1)">
                                    +
                                </button>
                            </div>
                        </div>
                        <span class="col-span-1 text-red-500 font-semibold text-lg text-center">
                            {{ formatCurrency(item.price * item.quantity) }}
                        </span>
                        <div class="col-span-1 text-sm space-y-1 text-center">
                            <button class="text-blue-500 text-base hover:text-red-500 block mx-auto"
                                @click="handleRemoveCartItem(item.id)">
                                Xóa
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end items-center py-3 pr-4 mb-4">
                    <div
                        class="h-5 w-5 bg-gray-300 rounded-full mr-2 flex items-center justify-center text-xs text-gray-600">
                        V</div>
                    <span class="text-gray-700 font-medium mr-4">Shopee Voucher</span>
                    <button class="text-blue-500 hover:underline">Chọn hoặc nhập mã</button>
                </div>

                <footer
                    class="bg-white p-4 flex justify-between items-center rounded-b-md shadow-md sticky bottom-0 z-10">
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-red-500 rounded"
                                :checked="isAllSelected" @change="toggleSelectAll" />
                            <span class="ml-2 text-gray-700">Chọn Tất Cả ({{ totalSelectedItemsInCart }})</span>
                        </label>
                        <button class="text-blue-500 hover:underline" @click="handleRemoveSelectedItems">Xóa</button>
                        <button class="text-blue-500 hover:underline">Bỏ sản phẩm không hoạt động</button>
                    </div>
                    <div class="flex items-center">
                        <div class="flex items-center mr-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" value="" class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 dark:peer-focus:ring-orange-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-orange-600">
                                </div>
                                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Shopee Xu</span>
                            </label>
                            <span class="ms-2 text-gray-500 text-sm">Bạn chưa chọn sản phẩm ⓘ</span>
                        </div>
                        <span class="text-gray-700 font-medium">Tổng cộng ({{ totalSelectedItemsInCart }} Sản
                            phẩm):</span>
                        <span class="text-red-500 font-bold text-xl ml-2">{{ formatCurrency(subtotalSelectedAmount)
                            }}</span>
                        <button
                            class="bg-red-500 text-white font-semibold py-3 px-6 rounded-md ml-4 hover:bg-red-600">Mua
                            Hàng</button>
                    </div>
                </footer>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

// --- Biến trạng thái ---
const cartData = ref(null); // Lưu trữ toàn bộ dữ liệu giỏ hàng từ API
const loading = ref(true); // Trạng thái tải dữ liệu
const error = ref(null); // Thông báo lỗi nếu có
const selectedItems = ref(new Set()); // Lưu trữ ID của các item được chọn

// --- Hàm tiện ích ---
/**
 * Định dạng số tiền sang tiền tệ Việt Nam (VND).
 * @param {number} amount
 * @returns {string}
 */
const formatCurrency = (amount) => {
    if (amount === null || amount === undefined || isNaN(amount)) {
        return '₫0';
    }
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
};

/**
 * Cắt chuỗi văn bản nếu nó dài hơn maxLength và thêm dấu ba chấm.
 * @param {string} text - Chuỗi cần cắt.
 * @param {number} maxLength - Chiều dài tối đa mong muốn.
 * @returns {string} Chuỗi đã được cắt hoặc nguyên bản.
 */
const truncateText = (text, maxLength) => {
    if (!text) return '';
    if (text.length > maxLength) {
        return text.substring(0, maxLength) + '...';
    }
    return text;
};

// --- Computed Properties ---
// Tổng số lượng sản phẩm TRONG GIỎ HÀNG (cho checkbox chọn tất cả)
const totalCartItemsCount = computed(() => {
    return cartData.value ? cartData.value.items.length : 0;
});

// Tổng số lượng sản phẩm ĐƯỢC CHỌN trong giỏ hàng
const totalSelectedItemsInCart = computed(() => {
    let count = 0;
    if (cartData.value && cartData.value.items) {
        cartData.value.items.forEach(item => {
            if (selectedItems.value.has(item.id)) {
                count += item.quantity;
            }
        });
    }
    return count;
});

// Tổng tiền của các sản phẩm ĐƯỢC CHỌN trong giỏ hàng
const subtotalSelectedAmount = computed(() => {
    let total = 0;
    if (cartData.value && cartData.value.items) {
        cartData.value.items.forEach(item => {
            if (selectedItems.value.has(item.id)) {
                total += parseFloat(item.price) * item.quantity;
            }
        });
    }
    return total;
});

// Kiểm tra xem tất cả các sản phẩm có được chọn không
const isAllSelected = computed(() => {
    if (!cartData.value || cartData.value.items.length === 0) {
        return false;
    }
    // Đảm bảo rằng tất cả các item trong cartData đều có trong selectedItems
    return cartData.value.items.every(item => selectedItems.value.has(item.id));
});

// --- Logic chọn sản phẩm ---
/**
 * Chuyển đổi trạng thái chọn của một sản phẩm.
 * @param {number} itemId - ID của cart item.
 */
const toggleItemSelection = (itemId) => {
    if (selectedItems.value.has(itemId)) {
        selectedItems.value.delete(itemId);
    } else {
        selectedItems.value.add(itemId);
    }
};

/**
 * Chuyển đổi trạng thái chọn tất cả sản phẩm.
 */
const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedItems.value.clear(); // Bỏ chọn tất cả
    } else {
        // Chọn tất cả
        if (cartData.value && cartData.value.items) {
            cartData.value.items.forEach(item => {
                selectedItems.value.add(item.id);
            });
        }
    }
};

// --- Logic gọi API ---
/**
 * Lấy dữ liệu giỏ hàng từ backend API.
 */
const fetchCartData = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/cart-items');
        cartData.value = response.data;

        // Sau khi tải lại dữ liệu, cập nhật lại selectedItems để loại bỏ các item không còn tồn tại
        // và giữ lại các item đã chọn trước đó
        const existingItemIds = new Set(cartData.value.items.map(item => item.id));
        selectedItems.value = new Set([...selectedItems.value].filter(id => existingItemIds.has(id)));

        // Tự động chọn tất cả sản phẩm nếu giỏ hàng không rỗng và chưa có gì được chọn
        // hoặc nếu trước đó đã chọn tất cả
        if (cartData.value.items.length > 0 && (selectedItems.value.size === 0 || isAllSelected.value)) {
            toggleSelectAll();
        }

    } catch (err) {
        console.error('Lỗi khi tải dữ liệu giỏ hàng:', err);
        if (err.response && err.response.status === 401) {
            error.value = 'Bạn cần đăng nhập để xem giỏ hàng.';
        } else {
            error.value = 'Không thể tải giỏ hàng. Vui lòng thử lại sau.';
        }
    } finally {
        loading.value = false;
    }
};

/**
 * Xử lý thay đổi số lượng của một sản phẩm trong giỏ hàng.
 * @param {number} cartItemId - ID của cart item.
 * @param {number} newQuantity - Số lượng mới.
 */
const handleChangeQuantity = async (cartItemId, newQuantity) => {
    if (newQuantity < 1) {
        newQuantity = 1;
    }

    const itemIndex = cartData.value.items.findIndex(item => item.id === cartItemId);
    if (itemIndex === -1) {
        console.error('Không tìm thấy cart item để cập nhật số lượng.');
        return;
    }

    const originalQuantity = cartData.value.items[itemIndex].quantity;
    // Cập nhật UI ngay lập tức
    cartData.value.items[itemIndex].quantity = newQuantity;

    try {
        await axios.put(`/cart-items/${cartItemId}`, { quantity: newQuantity });
        await fetchCartData(); // Tải lại toàn bộ dữ liệu để đảm bảo đồng bộ hoàn toàn
    } catch (err) {
        console.error('Lỗi khi cập nhật số lượng:', err);
        if (err.response && err.response.data && err.response.data.message) {
            alert(`Lỗi: ${err.response.data.message}`);
        } else {
            alert('Không thể cập nhật số lượng. Vui lòng thử lại.');
        }
        // Hoàn tác thay đổi UI nếu lỗi
        if (itemIndex !== -1) {
            cartData.value.items[itemIndex].quantity = originalQuantity;
        }
        // Có thể cần fetch lại để đảm bảo trạng thái đúng nếu lỗi không rõ ràng
        await fetchCartData();
    }
};

/**
 * Xử lý thay đổi biến thể của một sản phẩm trong giỏ hàng.
 * @param {number} cartItemId - ID của cart item.
 * @param {number} newVariantId - ID của biến thể mới.
 */
const handleChangeVariant = async (cartItemId, newVariantId) => {
    console.log(`Đổi biến thể cho item ${cartItemId} sang variant ${newVariantId}`);

    const itemIndex = cartData.value.items.findIndex(item => item.id === cartItemId);
    if (itemIndex === -1) {
        console.error('Không tìm thấy cart item để thay đổi biến thể.');
        return;
    }

    const currentItem = cartData.value.items[itemIndex];

    // Nếu biến thể không thay đổi, không làm gì cả
    if (currentItem.variant && currentItem.variant.id === newVariantId) {
        return;
    }

    // Tìm biến thể mới trong danh sách available_variants để kiểm tra tồn kho cục bộ
    const newVariant = currentItem.available_variants.find(v => v.id === newVariantId);
    if (!newVariant) {
        alert('Biến thể không hợp lệ.');
        await fetchCartData(); // Tải lại để đảm bảo trạng thái đúng
        return;
    }

    // Kiểm tra tồn kho của biến thể mới trước khi gửi yêu cầu
    if (newVariant.stock < currentItem.quantity) {
        alert(`Biến thể này chỉ còn ${newVariant.stock} sản phẩm. Vui lòng giảm số lượng hoặc chọn biến thể khác.`);
        await fetchCartData(); // Tải lại để đảm bảo trạng thái đúng
        return;
    }

    try {
        // Gửi yêu cầu PUT để cập nhật product_variant_id
        await axios.put(`/cart-items/${cartItemId}`, { product_variant_id: newVariantId });

        // Sau khi cập nhật thành công, tải lại toàn bộ giỏ hàng
        // Đây là cách an toàn nhất để đảm bảo tất cả dữ liệu (giá, tổng tiền) được đồng bộ.
        await fetchCartData();
    } catch (err) {
        console.error('Lỗi khi thay đổi biến thể:', err);
        // Xử lý lỗi từ backend (ví dụ: tồn kho không đủ, biến thể không tồn tại, biến thể không thuộc cùng sản phẩm)
        if (err.response && err.response.data && err.response.data.message) {
            error.value = `Lỗi: ${err.response.data.message}`;
            alert(`Lỗi: ${err.response.data.message}`);
        } else {
            error.value = 'Không thể thay đổi biến thể. Vui lòng thử lại.';
            alert('Không thể thay đổi biến thể. Vui lòng thử lại.');
        }
        // Tải lại dữ liệu để hoàn tác thay đổi UI nếu lỗi
        await fetchCartData();
    }
};

/**
 * Xử lý xóa một sản phẩm khỏi giỏ hàng.
 * @param {number} cartItemId - ID của cart item cần xóa.
 */
const handleRemoveCartItem = async (cartItemId) => {
    if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        return;
    }

    try {
        await axios.delete(`/cart-items/${cartItemId}`);
        // Xóa item khỏi selectedItems nếu nó đang được chọn
        selectedItems.value.delete(cartItemId);
        await fetchCartData(); // Tải lại toàn bộ dữ liệu sau khi xóa
    } catch (err) {
        console.error('Lỗi khi xóa sản phẩm:', err);
        if (err.response && err.response.data && err.response.data.message) {
            error.value = `Lỗi: ${err.response.data.message}`;
            alert(`Lỗi: ${err.response.data.message}`);
        } else {
            error.value = 'Không thể xóa sản phẩm. Vui lòng thử lại.';
            alert('Không thể xóa sản phẩm. Vui lòng thử lại.');
        }
    }
};

/**
 * Xử lý xóa các sản phẩm đã được chọn khỏi giỏ hàng.
 */
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
        // Gửi nhiều yêu cầu DELETE concurrently.
        // Tùy chọn: Bạn có thể cân nhắc tạo một API endpoint trên backend để xóa nhiều item cùng lúc nếu số lượng lớn.
        await Promise.all(itemIdsToDelete.map(id => axios.delete(`/cart-items/${id}`)));

        selectedItems.value.clear(); // Xóa tất cả lựa chọn sau khi xóa thành công
        await fetchCartData(); // Tải lại toàn bộ dữ liệu sau khi xóa
    } catch (err) {
        console.error('Lỗi khi xóa các sản phẩm đã chọn:', err);
        if (err.response && err.response.data && err.response.data.message) {
            error.value = `Lỗi: ${err.response.data.message}`;
            alert(`Lỗi: ${err.response.data.message}`);
        } else {
            error.value = 'Không thể xóa các sản phẩm đã chọn. Vui lòng thử lại.';
            alert('Không thể xóa các sản phẩm đã chọn. Vui lòng thử lại.');
        }
    }
};


// --- Lifecycle Hook ---
// Khi component được mount, gọi hàm lấy dữ liệu giỏ hàng
onMounted(() => {
    fetchCartData();
});
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Không cần import tailwind.css nếu đã import global trong main.js */
/* @import '@/assets/tailwind.css'; */
/* Bỏ comment dòng này nếu bạn import Tailwind CSS theo cách này */

input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Any component-specific styles if needed, though Tailwind aims to minimize this. */
</style>