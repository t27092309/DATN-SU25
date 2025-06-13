<template>
  <div class="user-address p-6 bg-gray-100 min-h-screen">
    <h2 class="text-3xl font-bold mb-8 text-gray-800">Địa chỉ của tôi</h2>

    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xl font-medium text-gray-700">Danh sách Địa chỉ</h3>
      <button
        @click="openAddAddressModal"
        class="px-5 py-2 bg-red-500 text-white rounded-lg flex items-center hover:bg-red-600 transition duration-200 ease-in-out shadow-md"
      >
        <i class="fas fa-plus mr-2"></i> Thêm địa chỉ mới
      </button>
    </div>

    <div class="space-y-4">
      <div
        v-for="address in addresses"
        :key="address.id"
        class="border border-gray-200 rounded-lg p-5 bg-white shadow-sm hover:shadow-md transition duration-150 ease-in-out"
      >
        <div class="flex justify-between items-start mb-3">
          <div>
            <p class="font-semibold text-lg text-gray-800">
              {{ address.receiver_name }} |
              <span class="text-gray-600 text-base">{{ address.phone_number }}</span>
            </p>
            <p class="text-gray-700 mt-1">{{ address.street_address }}</p>
            <p class="text-gray-700">
              {{ address.ward }}, {{ address.district }}, {{ address.province }}
            </p>
          </div>
          <div class="flex space-x-3 text-blue-600 text-sm">
            <button
              @click="updateAddress(address.id)"
              class="hover:underline font-medium"
            >
              Cập nhật
            </button>
            <button
              @click="deleteAddress(address.id)"
              class="hover:underline font-medium text-red-500"
            >
              Xóa
            </button>
          </div>
        </div>
        <div class="flex items-center flex-wrap gap-2">
          <span
            v-if="address.is_default"
            class="px-3 py-1 bg-red-100 text-red-600 text-xs font-semibold rounded-full"
            >Mặc định</span
          >
          <span
            v-if="address.is_return_address"
            class="px-3 py-1 border border-red-500 text-red-500 text-xs font-semibold rounded-full"
            >Địa chỉ trả hàng</span
          >
          <span
            v-if="address.is_pickup_address"
            class="px-3 py-1 border border-gray-400 text-gray-700 text-xs font-semibold rounded-full"
            >Địa chỉ lấy hàng</span
          >
          <button
            v-if="!address.is_default"
            @click="setDefaultAddress(address.id)"
            class="px-4 py-1.5 border border-gray-300 text-gray-700 text-sm rounded-full hover:bg-gray-50 transition duration-150 ease-in-out"
          >
            Thiết lập mặc định
          </button>
        </div>
        <p v-if="address.is_outdated" class="text-orange-600 text-sm italic mt-2">
          Một vài thông tin đã cũ, vui lòng giúp chúng tôi cập nhật địa chỉ này.
        </p>
      </div>
    </div>

    <div
      v-if="showUpdateModal"
      class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4"
    >
      <div ref="updateModalRef" class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-lg transform scale-100 transition-all duration-300 ease-out">
        <h3 class="text-2xl font-semibold mb-6 text-gray-800">Cập nhật Địa chỉ</h3>
        <form @submit.prevent="saveUpdatedAddress">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="update_receiver_name" class="block text-gray-700 text-sm font-bold mb-2">Tên người nhận:</label>
              <input
                type="text"
                id="update_receiver_name"
                v-model="currentAddress.receiver_name"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
              />
            </div>
            <div>
              <label for="update_phone_number" class="block text-gray-700 text-sm font-bold mb-2">Số điện thoại:</label>
              <input
                type="text"
                id="update_phone_number"
                v-model="currentAddress.phone_number"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
              />
            </div>
          </div>
          <div class="mb-4">
            <label for="update_street_address" class="block text-gray-700 text-sm font-bold mb-2">Địa chỉ cụ thể:</label>
            <input
              type="text"
              id="update_street_address"
              v-model="currentAddress.street_address"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
              required
            />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
              <label for="update_province" class="block text-gray-700 text-sm font-bold mb-2">Tỉnh/Thành phố:</label>
              <input
                type="text"
                id="update_province"
                v-model="currentAddress.province"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
              />
            </div>
            <div>
              <label for="update_district" class="block text-gray-700 text-sm font-bold mb-2">Quận/Huyện:</label>
              <input
                type="text"
                id="update_district"
                v-model="currentAddress.district"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
              />
            </div>
            <div>
              <label for="update_ward" class="block text-gray-700 text-sm font-bold mb-2">Phường/Xã:</label>
              <input
                type="text"
                id="update_ward"
                v-model="currentAddress.ward"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required
              />
            </div>
          </div>

          <div class="flex items-center space-x-4 mb-6">
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="currentAddress.is_return_address" class="form-checkbox h-5 w-5 text-blue-600 rounded" />
              <span class="ml-2 text-gray-700">Địa chỉ trả hàng</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="currentAddress.is_pickup_address" class="form-checkbox h-5 w-5 text-blue-600 rounded" />
              <span class="ml-2 text-gray-700">Địa chỉ lấy hàng</span>
            </label>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showUpdateModal = false"
              class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-200 ease-in-out"
            >
              Hủy
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 ease-in-out shadow-md"
            >
              Lưu thay đổi
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showAddModal"
      class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4"
    >
      <div ref="addModalRef" class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-lg transform scale-100 transition-all duration-300 ease-out">
        <h3 class="text-2xl font-semibold mb-6 text-gray-800">Thêm Địa chỉ Mới</h3>
        <form @submit.prevent="saveNewAddress">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <label for="add_receiver_name" class="block text-gray-700 text-sm font-bold mb-2">Tên người nhận:</label>
              <input
                type="text"
                id="add_receiver_name"
                v-model="newAddress.receiver_name"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required
              />
            </div>
            <div>
              <label for="add_phone_number" class="block text-gray-700 text-sm font-bold mb-2">Số điện thoại:</label>
              <input
                type="text"
                id="add_phone_number"
                v-model="newAddress.phone_number"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required
              />
            </div>
          </div>
          <div class="mb-4">
            <label for="add_street_address" class="block text-gray-700 text-sm font-bold mb-2">Địa chỉ cụ thể:</label>
            <input
              type="text"
              id="add_street_address"
              v-model="newAddress.street_address"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
              required
            />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
              <label for="add_province" class="block text-gray-700 text-sm font-bold mb-2">Tỉnh/Thành phố:</label>
              <input
                type="text"
                id="add_province"
                v-model="newAddress.province"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required
              />
            </div>
            <div>
              <label for="add_district" class="block text-gray-700 text-sm font-bold mb-2">Quận/Huyện:</label>
              <input
                type="text"
                id="add_district"
                v-model="newAddress.district"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required
              />
            </div>
            <div>
              <label for="add_ward" class="block text-gray-700 text-sm font-bold mb-2">Phường/Xã:</label>
              <input
                type="text"
                id="add_ward"
                v-model="newAddress.ward"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required
              />
            </div>
          </div>

          <div class="flex items-center space-x-4 mb-6">
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="newAddress.is_return_address" class="form-checkbox h-5 w-5 text-green-600 rounded" />
              <span class="ml-2 text-gray-700">Địa chỉ trả hàng</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="newAddress.is_pickup_address" class="form-checkbox h-5 w-5 text-green-600 rounded" />
              <span class="ml-2 text-gray-700">Địa chỉ lấy hàng</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="newAddress.set_as_default" class="form-checkbox h-5 w-5 text-green-600 rounded" />
              <span class="ml-2 text-gray-700">Đặt làm địa chỉ mặc định</span>
            </label>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showAddModal = false"
              class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-200 ease-in-out"
            >
              Hủy
            </button>
            <button
              type="submit"
              class="px-5 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 ease-in-out shadow-md"
            >
              Thêm địa chỉ
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showDeleteConfirmModal"
      class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4"
    >
      <div ref="deleteConfirmModalRef" class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-sm text-center transform scale-100 transition-all duration-300 ease-out">
        <h3 class="text-2xl font-semibold mb-4 text-red-600">Xác nhận xóa địa chỉ</h3>
        <p class="mb-8 text-gray-700 text-lg">Bạn có chắc chắn muốn xóa địa chỉ này không?</p>
        <div class="flex justify-center space-x-4">
          <button
            type="button"
            @click="showDeleteConfirmModal = false"
            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-200 ease-in-out"
          >
            Hủy
          </button>
          <button
            type="button"
            @click="confirmDeleteAddress"
            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 ease-in-out shadow-md"
          >
            Xóa
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

// --- Dữ liệu Mẫu (Sẽ được thay thế bằng dữ liệu từ API thực tế) ---
const addresses = ref([
  {
    id: 1,
    receiver_name: 'Đỗ Xuân Trường',
    phone_number: '(+84) 374 729 420',
    street_address: 'Ngõ 36 Thiết Ưng',
    ward: 'Xã Vân Hà',
    district: 'Huyện Đông Anh',
    province: 'Hà Nội',
    is_default: true,
    is_return_address: true,
    is_pickup_address: false,
    is_outdated: false,
  },
  {
    id: 2,
    receiver_name: 'Đỗ Xuân Trường',
    phone_number: '(+84) 865 248 426',
    street_address: 'Bưu Điện xã Vân Hà',
    ward: 'Xã Vân Hà',
    district: 'Huyện Đông Anh',
    province: 'Hà Nội',
    is_default: false,
    is_return_address: false,
    is_pickup_address: true,
    is_outdated: true, // Đánh dấu là cũ để hiển thị thông báo
  },
]);

// --- Quản lý trạng thái Modal ---
const showUpdateModal = ref(false); // Trạng thái hiển thị modal cập nhật
const currentAddress = ref({}); // Dùng để lưu thông tin địa chỉ đang được chỉnh sửa

const showDeleteConfirmModal = ref(false); // Trạng thái hiển thị modal xác nhận xóa
const addressToDeleteId = ref(null); // Dùng để lưu ID địa chỉ cần xóa

const showAddModal = ref(false); // Trạng thái hiển thị modal thêm địa chỉ mới
const newAddress = ref({ // Đối tượng địa chỉ mới, khởi tạo các giá trị mặc định
  receiver_name: '',
  phone_number: '',
  street_address: '',
  ward: '',
  district: '',
  province: '',
  is_return_address: false,
  is_pickup_address: false,
  set_as_default: false,
});

// --- Ref tới phần tử DOM của modal (để kiểm tra click outside) ---
const updateModalRef = ref(null);
const addModalRef = ref(null);
const deleteConfirmModalRef = ref(null);


// --- Hàm tải dữ liệu địa chỉ (Mock API Call) ---
const fetchAddresses = async () => {
  // Trong một ứng dụng thực tế, bạn sẽ gọi API tại đây:
  // try {
  //   const response = await fetch('/api/user/addresses');
  //   if (!response.ok) throw new Error('Failed to fetch addresses');
  //   const data = await response.json();
  //   addresses.value = data;
  // } catch (error) {
  //   console.error("Lỗi khi tải địa chỉ:", error);
  //   // Hiển thị thông báo lỗi cho người dùng
  // }
  console.log('Fetching addresses from API...');
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
  // Trả về hàm gỡ listener để có thể cleanup sau
  return () => document.removeEventListener('mousedown', handler);
};

// Lưu các hàm gỡ listener để cleanup khi component unmount
let cleanupUpdateListener = null;
let cleanupAddListener = null;
let cleanupDeleteListener = null;

// --- Lifecycle Hooks ---
onMounted(() => {
  fetchAddresses(); // Tải dữ liệu khi component được mount
});

onBeforeUnmount(() => {
  // Đảm bảo gỡ bỏ tất cả các event listeners khi component bị hủy
  if (cleanupUpdateListener) cleanupUpdateListener();
  if (cleanupAddListener) cleanupAddListener();
  if (cleanupDeleteListener) cleanupDeleteListener();
});

// --- Xử lý sự kiện thêm địa chỉ mới ---
const openAddAddressModal = () => {
  newAddress.value = {
    receiver_name: '',
    phone_number: '',
    street_address: '',
    ward: '',
    district: '',
    province: '',
    is_return_address: false,
    is_pickup_address: false,
    set_as_default: false,
  };
  showAddModal.value = true;
  // Thiết lập listener khi modal mở
  cleanupAddListener = setupClickOutsideListener(addModalRef, () => showAddModal.value = false);
};

const saveNewAddress = async () => {
  console.log('Đang lưu địa chỉ mới:', newAddress.value);
  // (Logic gọi API và cập nhật addresses.value như trước)

  const newId = Math.max(...addresses.value.map(a => a.id)) + 1 || 1;
  const addressToAdd = {
    id: newId,
    ...newAddress.value,
    is_default: newAddress.value.set_as_default,
    is_outdated: false
  };

  if (addressToAdd.is_default) {
    addresses.value.forEach(addr => addr.is_default = false);
  }

  addresses.value.push(addressToAdd);
  showAddModal.value = false;
  alert('Địa chỉ mới đã được thêm (ví dụ)!');
  // Gỡ listener sau khi modal đóng
  if (cleanupAddListener) cleanupAddListener();
};

// --- Xử lý sự kiện cập nhật địa chỉ ---
const updateAddress = (addressId) => {
  const addressToUpdate = addresses.value.find((addr) => addr.id === addressId);
  if (addressToUpdate) {
    currentAddress.value = JSON.parse(JSON.stringify(addressToUpdate));
    showUpdateModal.value = true;
    // Thiết lập listener khi modal mở
    cleanupUpdateListener = setupClickOutsideListener(updateModalRef, () => showUpdateModal.value = false);
  }
};

// --- Xử lý sự kiện lưu địa chỉ đã cập nhật ---
const saveUpdatedAddress = async () => {
  console.log('Đang lưu địa chỉ đã cập nhật:', currentAddress.value);
  // (Logic gọi API và cập nhật addresses.value như trước)

  const index = addresses.value.findIndex(addr => addr.id === currentAddress.value.id);
  if (index !== -1) {
    addresses.value[index] = { ...currentAddress.value };
  }

  showUpdateModal.value = false;
  alert('Địa chỉ đã được cập nhật (ví dụ)!');
  // Gỡ listener sau khi modal đóng
  if (cleanupUpdateListener) cleanupUpdateListener();
};

// --- Xử lý sự kiện xóa địa chỉ ---
const deleteAddress = (addressId) => {
  addressToDeleteId.value = addressId;
  showDeleteConfirmModal.value = true;
  // Thiết lập listener khi modal mở
  cleanupDeleteListener = setupClickOutsideListener(deleteConfirmModalRef, () => showDeleteConfirmModal.value = false);
};

// --- Xử lý sự kiện xác nhận xóa địa chỉ ---
const confirmDeleteAddress = async () => {
  console.log('Đang thực hiện xóa địa chỉ:', addressToDeleteId.value);
  // (Logic gọi API và cập nhật addresses.value như trước)

  addresses.value = addresses.value.filter(addr => addr.id !== addressToDeleteId.value);
  alert('Địa chỉ đã được xóa (ví dụ)!');
  showDeleteConfirmModal.value = false;
  addressToDeleteId.value = null;
  // Gỡ listener sau khi modal đóng
  if (cleanupDeleteListener) cleanupDeleteListener();
};

// --- Xử lý sự kiện thiết lập địa chỉ mặc định ---
const setDefaultAddress = async (addressId) => {
  console.log('Thiết lập mặc định địa chỉ:', addressId);
  // (Logic gọi API và cập nhật addresses.value như trước)

  addresses.value = addresses.value.map(addr => ({
    ...addr,
    is_default: addr.id === addressId
  }));
  alert('Địa chỉ mặc định đã được cập nhật (ví dụ)!');
};
</script>

<style scoped>
/* Không cần thay đổi gì ở đây nếu bạn đang sử dụng Tailwind CSS */
</style>
<style scoped>
@import '@/assets/tailwind.css';
/*
  Nếu bạn đã cấu hình Tailwind CSS đúng cách trong dự án Vue của mình,
  bạn không cần @import '@/assets/tailwind.css'; ở đây.
  Tailwind thường được tích hợp vào quá trình build thông qua postcss.config.js
  và file CSS chính của bạn (ví dụ: src/main.js hoặc src/assets/index.css).

  Bạn chỉ cần thêm các style tùy chỉnh (nếu có) vào đây.
  Hiện tại, tất cả các style đều được xử lý bằng Tailwind utility classes.
*/
</style>