<template>
  <div class="user-address p-6 bg-gray-100 min-h-screen">
    <h2 class="text-3xl font-bold mb-8 text-gray-800">Địa chỉ của tôi</h2>

    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xl font-medium text-gray-700">Danh sách Địa chỉ</h3>
      <button @click="openAddAddressModal"
        class="px-5 py-2 bg-red-500 text-white rounded-lg flex items-center hover:bg-red-600 transition duration-200 ease-in-out shadow-md">
        <i class="fas fa-plus mr-2"></i> Thêm địa chỉ mới
      </button>
    </div>

    <div v-if="addresses.length === 0 && !isLoading" class="text-center py-10 text-gray-600">
      <p class="text-lg">Bạn chưa có địa chỉ nào. Hãy thêm một địa chỉ mới!</p>
    </div>

    <div v-if="isLoading" class="text-center py-10 text-blue-500">
      <i class="fas fa-spinner fa-spin text-4xl"></i>
      <p class="mt-2 text-lg">Đang tải địa chỉ...</p>
    </div>

    <div class="space-y-4">
      <div v-for="address in addresses" :key="address.id"
        class="border border-gray-200 rounded-lg p-5 bg-white shadow-sm hover:shadow-md transition duration-150 ease-in-out">
        <div class="flex justify-between items-start mb-3">
          <div>
            <p class="font-semibold text-lg text-gray-800">
              {{ address.recipient_name }} |
              <span class="text-gray-600 text-base">{{ address.phone_number }}</span>
            </p>
            <p class="text-gray-700 mt-1">{{ address.address_line }}</p>
            <p class="text-gray-700">
              {{ address.ward }}, {{ address.district }}, {{ address.province }}
            </p>
          </div>
          <div class="flex space-x-3 text-blue-600 text-sm">
            <button @click="updateAddress(address.id)" class="hover:underline font-medium">
              Cập nhật
            </button>
            <button @click="deleteAddress(address.id)" class="hover:underline font-medium text-red-500">
              Xóa
            </button>
          </div>
        </div>
        <div class="flex items-center flex-wrap gap-2">
          <span v-if="address.is_default"
            class="px-3 py-1 bg-red-100 text-red-600 text-xs font-semibold rounded-full">Mặc định</span>
          <span v-if="address.is_return_address"
            class="px-3 py-1 border border-red-500 text-red-500 text-xs font-semibold rounded-full">Địa chỉ trả
            hàng</span>
          <span v-if="address.is_pickup_address"
            class="px-3 py-1 border border-gray-400 text-gray-700 text-xs font-semibold rounded-full">Địa chỉ lấy
            hàng</span>
          <button v-if="!address.is_default" @click="setDefaultAddress(address.id)"
            class="px-4 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-full hover:bg-gray-50 transition duration-150 ease-in-out">
            Thiết lập mặc định
          </button>
        </div>
        <p v-if="address.is_outdated" class="text-orange-600 text-sm italic mt-2">
          Một vài thông tin đã cũ, vui lòng giúp chúng tôi cập nhật địa chỉ này.
        </p>
      </div>
    </div>

    <div v-if="showUpdateModal"
      class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
      <div ref="updateModalRef"
        class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-lg transform scale-100 transition-all duration-300 ease-out">
        <h3 class="text-2xl font-semibold mb-6 text-gray-800">Cập nhật Địa chỉ</h3>
        <form @submit.prevent="saveUpdatedAddress">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="update_recipient_name" class="block text-gray-700 text-sm font-bold mb-2">Tên người
                nhận:</label>
              <input type="text" id="update_recipient_name" v-model="currentAddress.recipient_name"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required />
            </div>
            <div>
              <label for="update_phone_number" class="block text-gray-700 text-sm font-bold mb-2">Số điện thoại:</label>
              <input type="text" id="update_phone_number" v-model="currentAddress.phone_number"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required />
            </div>
          </div>
          <div class="mb-4">
            <label for="update_address_line" class="block text-gray-700 text-sm font-bold mb-2">Địa chỉ cụ thể:</label>
            <input type="text" id="update_address_line" v-model="currentAddress.address_line"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
              required />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
              <label for="update_province" class="block text-gray-700 text-sm font-bold mb-2">Tỉnh/Thành phố:</label>
              <select id="update_province" v-model="selectedProvinceCode" @change="fetchDistricts"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required>
                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                <option v-for="province in provinces" :key="province.code" :value="province.code">
                  {{ province.name }}
                </option>
              </select>
            </div>
            <div>
              <label for="update_district" class="block text-gray-700 text-sm font-bold mb-2">Quận/Huyện:</label>
              <select id="update_district" v-model="selectedDistrictCode" @change="fetchWards"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required :disabled="!selectedProvinceCode">
                <option value="">-- Chọn Quận/Huyện --</option>
                <option v-for="district in districts" :key="district.code" :value="district.code">
                  {{ district.name }}
                </option>
              </select>
            </div>
            <div>
              <label for="update_ward" class="block text-gray-700 text-sm font-bold mb-2">Phường/Xã:</label>
              <select id="update_ward" v-model="selectedWardCode"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required :disabled="!selectedDistrictCode">
                <option value="">-- Chọn Phường/Xã --</option>
                <option v-for="ward in wards" :key="ward.code" :value="ward.code">
                  {{ ward.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex items-center space-x-4 mb-6">
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="currentAddress.is_return_address"
                class="form-checkbox h-5 w-5 text-blue-600 rounded" />
              <span class="ml-2 text-gray-700">Địa chỉ trả hàng</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="currentAddress.is_pickup_address"
                class="form-checkbox h-5 w-5 text-blue-600 rounded" />
              <span class="ml-2 text-gray-700">Địa chỉ lấy hàng</span>
            </label>
          </div>

          <div class="flex justify-end space-x-3">
            <button type="button" @click="showUpdateModal = false"
              class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-200 ease-in-out">
              Hủy
            </button>
            <button type="submit" :disabled="isUpdatingAddress"
              class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 ease-in-out shadow-md"
              :class="{ 'opacity-50 cursor-not-allowed': isUpdatingAddress }">
              <span v-if="!isUpdatingAddress">Cập nhật Địa Chỉ</span> <span v-else>Đang cập nhật...</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showAddModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
      <div ref="addModalRef"
        class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-4xl transform scale-100 transition-all duration-300 ease-out">
        <h3 class="text-2xl font-semibold mb-6 text-gray-800">Thêm Địa chỉ Mới</h3>
        <form @submit.prevent="saveNewAddress">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="add_recipient_name" class="block text-gray-700 text-sm font-bold mb-2">Tên người nhận:</label>
              <input type="text" id="add_recipient_name" v-model="newAddress.recipient_name"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required />
            </div>
            <div>
              <label for="add_phone_number" class="block text-gray-700 text-sm font-bold mb-2">Số điện thoại:</label>
              <input type="text" id="add_phone_number" v-model="newAddress.phone_number"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required />
            </div>
          </div>
          <div class="mb-4">
            <label for="add_address_line" class="block text-gray-700 text-sm font-bold mb-2">Địa chỉ cụ thể:</label>
            <input type="text" id="add_address_line" v-model="newAddress.address_line"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
              required />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
              <label for="add_province" class="block text-gray-700 text-sm font-bold mb-2">Tỉnh/Thành phố:</label>
              <select id="add_province" v-model="selectedProvinceCode" @change="fetchDistricts"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required>
                <option value="">-- Chọn Tỉnh/Thành phố --</option>
                <option v-for="province in provinces" :key="province.code" :value="province.code">
                  {{ province.name }}
                </option>
              </select>
            </div>
            <div>
              <label for="add_district" class="block text-gray-700 text-sm font-bold mb-2">Quận/Huyện:</label>
              <select id="add_district" v-model="selectedDistrictCode" @change="fetchWards"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required :disabled="!selectedProvinceCode">
                <option value="">-- Chọn Quận/Huyện --</option>
                <option v-for="district in districts" :key="district.code" :value="district.code">
                  {{ district.name }}
                </option>
              </select>
            </div>
            <div>
              <label for="add_ward" class="block text-gray-700 text-sm font-bold mb-2">Phường/Xã:</label>
              <select id="add_ward" v-model="selectedWardCode"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required :disabled="!selectedDistrictCode">
                <option value="">-- Chọn Phường/Xã --</option>
                <option v-for="ward in wards" :key="ward.code" :value="ward.code">
                  {{ ward.name }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex items-center space-x-4 mb-6">
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="newAddress.set_as_default"
                class="form-checkbox h-5 w-5 text-green-600 rounded" />
              <span class="ml-2 text-gray-700">Đặt làm địa chỉ mặc định</span>
            </label>
          </div>

          <div class="flex justify-end space-x-3">
            <button type="button" @click="showAddModal = false"
              class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-200 ease-in-out">
              Hủy
            </button>
            <button type="submit" @click.prevent="saveNewAddress" :disabled="isSavingAddress"
              class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 ease-in-out shadow-md"
              :class="{ 'opacity-50 cursor-not-allowed': isSavingAddress }">
              <span v-if="!isSavingAddress">Thêm địa chỉ</span><span v-else>Đang
                thêm...</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <div v-if="showDeleteConfirmModal"
      class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
      <div ref="deleteConfirmModalRef"
        class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-sm text-center transform scale-100 transition-all duration-300 ease-out">
        <h3 class="text-2xl font-semibold mb-4 text-red-600">Xác nhận xóa địa chỉ</h3>
        <p class="mb-8 text-gray-700 text-lg">Bạn có chắc chắn muốn xóa địa chỉ này không?</p>
        <div class="flex justify-center space-x-4">
          <button type="button" @click="showDeleteConfirmModal = false"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-200 ease-in-out">
            Hủy
          </button>
          <button type="button" @click="confirmDeleteAddress"
            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 ease-in-out shadow-md">
            Xóa
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

// --- Dữ liệu để hiển thị ---
const addresses = ref([]);
const isLoading = ref(false);

// --- Quản lý trạng thái Modal ---
const showUpdateModal = ref(false);
const currentAddress = ref({}); // Địa chỉ hiện tại đang được chỉnh sửa

const showDeleteConfirmModal = ref(false);
const addressToDeleteId = ref(null);

const showAddModal = ref(false);
const isSavingAddress = ref(false);
const isUpdatingAddress = ref(false);

// --- Dữ liệu cho Form Thêm Địa chỉ Mới ---
const newAddress = ref({
    recipient_name: '',
    phone_number: '',
    address_line: '',
    is_default: false,
    is_return_address: false, // Đảm bảo có các trường này nếu dùng trong form
    is_pickup_address: false, // Đảm bảo có các trường này nếu dùng trong form
});

// --- Dữ liệu và logic cho dropdown địa lý ---
const provinces = ref([]);
const districts = ref([]);
const wards = ref([]);
const selectedProvinceCode = ref('');
const selectedDistrictCode = ref('');
const selectedWardCode = ref('');

// --- Ref tới phần tử DOM của modal (để kiểm tra click outside) ---
const updateModalRef = ref(null);
const addModalRef = ref(null);
const deleteConfirmModalRef = ref(null);

// --- Hàm tải dữ liệu địa chỉ từ API thật ---
const fetchAddresses = async () => {
    isLoading.value = true;
    try {
        const token = localStorage.getItem('authToken');
        if (!token) {
            console.warn('Authentication token not found. Please log in.');
            return;
        }
        const response = await fetch('http://localhost:8000/api/user/addresses', {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
        });

        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                console.error('Authentication error: Invalid or expired token.');
                alert('Your session has expired or is invalid. Please log in again.');
                localStorage.removeItem('authToken');
                return;
            }
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to fetch addresses');
        }

        const data = await response.json();
        addresses.value = data;
        console.log('Addresses loaded:', addresses.value);
    } catch (error) {
        console.error("Error fetching addresses:", error);
        alert('An error occurred while loading addresses. Please try again later: ' + error.message);
    } finally {
        isLoading.value = false;
    }
};

// --- Logic xử lý click ra ngoài để đóng modal ---
const handleClickOutside = (event, modalRef, closeModalCallback) => {
    if (modalRef.value && !modalRef.value.contains(event.target)) {
        closeModalCallback();
    }
};

const setupClickOutsideListener = (modalRef, closeModalCallback) => {
    const handler = (event) => handleClickOutside(event, modalRef, closeModalCallback);
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
};

let cleanupUpdateListener = null;
let cleanupAddListener = null;
let cleanupDeleteListener = null;

// --- Lifecycle Hooks ---
onMounted(async () => {
    await fetchAddresses(); // Tải địa chỉ người dùng
    await fetchProvinces(); // **QUAN TRỌNG: Tải tất cả tỉnh/thành phố ngay khi component được mount**
});

onBeforeUnmount(() => {
    if (cleanupUpdateListener) cleanupUpdateListener();
    if (cleanupAddListener) cleanupAddListener();
    if (cleanupDeleteListener) cleanupDeleteListener();
});

// --- Hàm tải danh sách Tỉnh/Thành phố ---
const fetchProvinces = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/provinces');
        provinces.value = response.data;
    } catch (error) {
        console.error("Error fetching provinces:", error);
        provinces.value = []; // Đảm bảo làm rỗng nếu có lỗi
        alert('Could not load provinces. Please try again.');
    }
};

// --- Hàm tải danh sách Quận/Huyện dựa trên Tỉnh/Thành phố đã chọn ---
const fetchDistricts = async () => {
    // Reset districts và wards nếu không có tỉnh được chọn
    if (!selectedProvinceCode.value) {
        districts.value = [];
        wards.value = [];
        selectedDistrictCode.value = '';
        selectedWardCode.value = '';
        return;
    }
    try {
        const response = await axios.get(`http://localhost:8000/api/provinces/${selectedProvinceCode.value}/districts`);
        districts.value = response.data;
    } catch (error) {
        console.error("Error fetching districts:", error);
        districts.value = []; // Xóa danh sách nếu có lỗi
        selectedDistrictCode.value = '';
        selectedWardCode.value = '';
        alert('Could not load districts. Please try again.');
    }
};

// --- Hàm tải danh sách Phường/Xã dựa trên Quận/Huyện đã chọn ---
const fetchWards = async () => {
    // Reset wards nếu không có huyện được chọn
    if (!selectedDistrictCode.value) {
        wards.value = [];
        selectedWardCode.value = '';
        return;
    }
    try {
        const response = await axios.get(`http://localhost:8000/api/districts/${selectedDistrictCode.value}/wards`);
        wards.value = response.data;
    } catch (error) {
        console.error("Error fetching wards:", error);
        wards.value = []; // Xóa danh sách nếu có lỗi
        selectedWardCode.value = '';
        alert('Could not load wards. Please try again.');
    }
};

// --- Watchers để tự động gọi API khi mã code thay đổi ---
// Sử dụng `immediate: false` để tránh chạy khi khởi tạo và `async/await` để đảm bảo đồng bộ
watch(selectedProvinceCode, async (newVal, oldVal) => {
    if (newVal !== oldVal) {
        selectedDistrictCode.value = ''; // Reset district
        selectedWardCode.value = '';      // Reset ward
        districts.value = [];             // Clear districts list
        wards.value = [];                 // Clear wards list
        if (newVal) {
            await fetchDistricts(); // Chờ fetch hoàn thành
        }
    }
}, { immediate: false });

watch(selectedDistrictCode, async (newVal, oldVal) => {
    if (newVal !== oldVal) {
        selectedWardCode.value = '';      // Reset ward
        wards.value = [];                 // Clear wards list
        if (newVal) {
            await fetchWards(); // Chờ fetch hoàn thành
        }
    }
}, { immediate: false });

// --- Xử lý sự kiện thêm địa chỉ mới ---
const openAddAddressModal = async () => {
    resetNewAddressForm();
    showAddModal.value = true;
    cleanupAddListener = setupClickOutsideListener(addModalRef, () => showAddModal.value = false);
    // fetchProvinces() đã được gọi trong onMounted, không cần gọi lại ở đây
    // nếu bạn muốn đảm bảo dữ liệu mới nhất, có thể gọi lại: await fetchProvinces();
};

const resetNewAddressForm = () => {
    newAddress.value = {
        recipient_name: '',
        phone_number: '',
        address_line: '',
        is_default: false,
        is_return_address: false,
        is_pickup_address: false,
    };
    selectedProvinceCode.value = '';
    selectedDistrictCode.value = '';
    selectedWardCode.value = '';
    // Không cần reset provinces ở đây vì nó được quản lý bởi onMounted hoặc openAddAddressModal
    districts.value = [];
    wards.value = [];
};

const saveNewAddress = async () => {
    if (isSavingAddress.value) {
        return;
    }
    isSavingAddress.value = true;
    try {
        const token = localStorage.getItem('authToken');
        if (!token) {
            console.warn('Authentication token not found. Please log in to add an address.');
            alert('You need to log in to add a new address.');
            return;
        }

        const provinceName = provinces.value.find(p => p.code === selectedProvinceCode.value)?.name || '';
        const districtName = districts.value.find(d => d.code === selectedDistrictCode.value)?.name || '';
        const wardName = wards.value.find(w => w.code === selectedWardCode.value)?.name || '';

        const dataToSend = {
            recipient_name: newAddress.value.recipient_name,
            phone_number: newAddress.value.phone_number,
            address_line: newAddress.value.address_line,
            ward: wardName,
            district: districtName,
            province: provinceName,
            is_default: newAddress.value.is_default,
            is_return_address: newAddress.value.is_return_address,
            is_pickup_address: newAddress.value.is_pickup_address,
        };

        const response = await fetch('http://localhost:8000/api/user/addresses', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(dataToSend)
        });

        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                console.error('Authentication error adding address: Invalid or expired token.');
                alert('Your session has expired or is invalid. Please log in again.');
                localStorage.removeItem('authToken');
                return;
            }
            const errorData = await response.json();
            if (errorData.errors) {
                let errorMessages = '';
                for (const key in errorData.errors) {
                    errorMessages += errorData.errors[key].join(', ') + '\n';
                }
                throw new Error(errorMessages);
            }
            throw new Error(errorData.message || 'Failed to add new address');
        }

        await fetchAddresses();
        showAddModal.value = false;
        alert('New address added successfully!');
    } catch (error) {
        console.error("Error adding new address:", error);
        alert('An error occurred while adding the new address. Please try again: ' + error.message);
    } finally {
        isSavingAddress.value = false;
        if (cleanupAddListener) cleanupAddListener();
    }
};

// --- Xử lý sự kiện cập nhật địa chỉ ---
const updateAddress = async (addressId) => {
    const addressToUpdate = addresses.value.find((addr) => addr.id === addressId);

    if (addressToUpdate) {
        // Clone đối tượng để tránh thay đổi trực tiếp addresses.value
        currentAddress.value = { ...JSON.parse(JSON.stringify(addressToUpdate)) };

        showUpdateModal.value = true;
        // Đảm bảo lắng nghe click outside
        cleanupUpdateListener = setupClickOutsideListener(updateModalRef, () => showUpdateModal.value = false);

        // **Bước 1: Reset các lựa chọn và dữ liệu liên quan để đảm bảo sạch sẽ**
        selectedProvinceCode.value = '';
        selectedDistrictCode.value = '';
        selectedWardCode.value = '';
        districts.value = []; // Xóa dữ liệu cũ của districts
        wards.value = [];     // Xóa dữ liệu cũ của wards

        // **Bước 2: Tìm và set selectedProvinceCode**
        // provinces.value đã có dữ liệu do được fetch trong onMounted
        const foundProvince = provinces.value.find(p => p.name === currentAddress.value.province);
        if (foundProvince) {
            selectedProvinceCode.value = foundProvince.code;

            // **Bước 3: Tải districts DỰA TRÊN `selectedProvinceCode` MỚI SET**
            // Gọi tường minh `await fetchDistricts()` để đảm bảo dữ liệu sẵn sàng.
            // Watcher cũng sẽ kích hoạt nhưng việc await ở đây đảm bảo tuần tự trong hàm này.
            await fetchDistricts();

            // **Bước 4: Tìm và set selectedDistrictCode**
            const foundDistrict = districts.value.find(d => d.name === currentAddress.value.district);
            if (foundDistrict) {
                selectedDistrictCode.value = foundDistrict.code;

                // **Bước 5: Tải wards DỰA TRÊN `selectedDistrictCode` MỚI SET**
                // Gọi tường minh `await fetchWards()` để đảm bảo dữ liệu sẵn sàng.
                await fetchWards();

                // **Bước 6: Tìm và set selectedWardCode**
                const foundWard = wards.value.find(w => w.name === currentAddress.value.ward);
                if (foundWard) {
                    selectedWardCode.value = foundWard.code;
                }
            }
        }
    }
};

// --- Xử lý sự kiện lưu địa chỉ đã cập nhật ---
const saveUpdatedAddress = async () => {
    if (isUpdatingAddress.value) {
        return;
    }

    isUpdatingAddress.value = true;
    try {
        const token = localStorage.getItem('authToken');

        if (!token) {
            console.warn('Authentication token not found. Please log in to update address.');
            alert('You need to log in to update an address.');
            return;
        }

        // Lấy tên đầy đủ của tỉnh, huyện, xã từ code đã chọn hiện tại
        const provinceName = provinces.value.find(p => p.code === selectedProvinceCode.value)?.name || '';
        const districtName = districts.value.find(d => d.code === selectedDistrictCode.value)?.name || '';
        const wardName = wards.value.find(w => w.code === selectedWardCode.value)?.name || '';

        // Tạo đối tượng dữ liệu cập nhật
        const dataToSend = {
            ...currentAddress.value, // Giữ lại các trường khác
            ward: wardName, // Gửi tên phường/xã
            district: districtName, // Gửi tên quận/huyện
            province: provinceName, // Gửi tên tỉnh/thành phố
        };

        const response = await fetch(`http://localhost:8000/api/user/addresses/${currentAddress.value.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(dataToSend)
        });

        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                console.error('Authentication error updating address: Invalid or expired token.');
                alert('Your session has expired or is invalid. Please log in again.');
                localStorage.removeItem('authToken');
                return;
            }
            const errorData = await response.json();
            if (errorData.errors) {
                let errorMessages = '';
                for (const key in errorData.errors) {
                    errorMessages += errorData.errors[key].join(', ') + '\n';
                }
                throw new Error(errorMessages);
            }
            throw new Error(errorData.message || 'Failed to update address');
        }

        await fetchAddresses();

        showUpdateModal.value = false;
        alert('Address updated successfully!');
    } catch (error) {
        console.error("Error updating address:", error);
        alert('An error occurred while updating the address. Please try again: ' + error.message);
    } finally {
        isUpdatingAddress.value = false;
        if (cleanupUpdateListener) cleanupUpdateListener();
    }
};

// --- Xử lý sự kiện xóa địa chỉ ---
const deleteAddress = (addressId) => {
    addressToDeleteId.value = addressId;
    showDeleteConfirmModal.value = true;
    cleanupDeleteListener = setupClickOutsideListener(deleteConfirmModalRef, () => showDeleteConfirmModal.value = false);
};

// --- Xử lý sự kiện xác nhận xóa địa chỉ ---
const confirmDeleteAddress = async () => {
    try {
        const token = localStorage.getItem('authToken');
        if (!token) {
            console.warn('Authentication token not found. Please log in to delete an address.');
            alert('You need to be logged in to delete an address.');
            return;
        }

        const response = await fetch(`http://localhost:8000/api/user/addresses/${addressToDeleteId.value}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                console.error('Authentication error when deleting address: Invalid or expired token.');
                alert('Your session has expired or is invalid. Please log in again.');
                localStorage.removeItem('authToken');
                return;
            }
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to delete address');
        }

        await fetchAddresses();

        showDeleteConfirmModal.value = false;
        alert('Address deleted successfully!');
    } catch (error) {
        console.error("Error deleting address:", error);
        alert('An error occurred while deleting the address. Please try again: ' + error.message);
    } finally {
        addressToDeleteId.value = null;
        if (cleanupDeleteListener) cleanupDeleteListener();
    }
};

// --- Xử lý sự kiện thiết lập địa chỉ mặc định ---
const setDefaultAddress = async (addressId) => {
    try {
        const token = localStorage.getItem('authToken');
        if (!token) {
            console.warn('Authentication token not found. Please log in to set default address.');
            alert('You need to be logged in to set a default address.');
            return;
        }

        const response = await fetch(`http://localhost:8000/api/user/addresses/${addressId}/set-default`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
        });

        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                console.error('Authentication error setting default address: Invalid or expired token.');
                alert('Your session has expired or is invalid. Please log in again.');
                localStorage.removeItem('authToken');
                return;
            }
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to set default address');
        }

        await fetchAddresses();

        // alert('Default address updated successfully!');
    } catch (error) {
        console.error("Error setting default address:", error);
        alert('An error occurred while setting the default address. Please try again: ' + error.message);
    }
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Không cần thay đổi gì ở đây nếu bạn đang sử dụng Tailwind CSS */
</style>