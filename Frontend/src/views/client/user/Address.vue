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
              <input type="text" id="update_province" v-model="currentAddress.province"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required />
            </div>
            <div>
              <label for="update_district" class="block text-gray-700 text-sm font-bold mb-2">Quận/Huyện:</label>
              <input type="text" id="update_district" v-model="currentAddress.district"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required />
            </div>
            <div>
              <label for="update_ward" class="block text-gray-700 text-sm font-bold mb-2">Phường/Xã:</label>
              <input type="text" id="update_ward" v-model="currentAddress.ward"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-400"
                required />
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
            <button type="submit" @click.prevent="saveUpdatedAddress" saveUpdatedAddress :disabled="isUpdatingAddress"
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
        class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-lg transform scale-100 transition-all duration-300 ease-out">
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
              <input type="text" id="add_province" v-model="newAddress.province"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required />
            </div>
            <div>
              <label for="add_district" class="block text-gray-700 text-sm font-bold mb-2">Quận/Huyện:</label>
              <input type="text" id="add_district" v-model="newAddress.district"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required />
            </div>
            <div>
              <label for="add_ward" class="block text-gray-700 text-sm font-bold mb-2">Phường/Xã:</label>
              <input type="text" id="add_ward" v-model="newAddress.ward"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-green-400"
                required />
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
import { ref, onMounted, onBeforeUnmount } from 'vue';

// --- Dữ liệu để hiển thị ---
const addresses = ref([]); // Khởi tạo mảng rỗng để chứa dữ liệu thật từ API

// --- Trạng thái loading ---
const isLoading = ref(false);

// --- Quản lý trạng thái Modal ---
const showUpdateModal = ref(false);
const currentAddress = ref({});

const showDeleteConfirmModal = ref(false);
const addressToDeleteId = ref(null);

const showAddModal = ref(false);
const isSavingAddress = ref(false); // Biến mới để kiểm soát trạng thái lưu
const isUpdatingAddress = ref(false); // Biến mới để kiểm soát trạng thái cập nhật

const newAddress = ref({
  // Đảm bảo tên trường khớp với tên cột trong Laravel model
  recipient_name: '',
  phone_number: '',
  address_line: '',
  ward: '',
  district: '',
  province: '',
  set_as_default: false,
});

// --- Ref tới phần tử DOM của modal (để kiểm tra click outside) ---
const updateModalRef = ref(null);
const addModalRef = ref(null);
const deleteConfirmModalRef = ref(null);


// --- Hàm tải dữ liệu địa chỉ từ API thật ---
const fetchAddresses = async () => {
  isLoading.value = true; // Bắt đầu loading
  try {
    // 1. Lấy token từ localStorage
    const token = localStorage.getItem('authToken');

    // 2. Kiểm tra xem có token không. Nếu không có, có nghĩa là chưa đăng nhập hoặc token đã hết hạn/bị xóa.
    if (!token) {
      console.warn('Không tìm thấy token xác thực. Vui lòng đăng nhập.');
      // Tùy chọn: Chuyển hướng người dùng về trang đăng nhập
      // router.push('/login');
      isLoading.value = false; // Ngừng loading
      return; // Dừng hàm lại
    }

    const response = await fetch('http://localhost:8000/api/user/addresses', {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        // 3. Thêm Authorization token vào header
        'Authorization': `Bearer ${token}` // Sử dụng token đã lấy từ localStorage
      },
      // Nếu bạn đang dùng Laravel Sanctum (cookie-based SPA auth)
      // và API của bạn nằm cùng domain/subdomain, bạn cũng cần thêm:
      // credentials: 'include'
      // Tuy nhiên, nếu bạn đã thiết lập axios.defaults.withCredentials = true
      // cho toàn bộ ứng dụng Axios của mình thì không cần thêm dòng này ở đây.
    });

    if (!response.ok) {
      // Xử lý các lỗi phản hồi từ server
      if (response.status === 401 || response.status === 403) {
        // Đây là lỗi "Unauthenticated" hoặc "Unauthorized"
        console.error('Lỗi xác thực: Token không hợp lệ hoặc đã hết hạn.');
        alert('Phiên đăng nhập đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.');
        localStorage.removeItem('authToken'); // Xóa token đã hết hạn
        // Tùy chọn: Chuyển hướng người dùng về trang đăng nhập
        // router.push('/login');
        return; // Dừng hàm
      }
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to fetch addresses');
    }

    const data = await response.json();
    addresses.value = data; // Gán dữ liệu thật từ API vào biến phản ứng
    console.log('Địa chỉ đã được tải:', addresses.value);
  } catch (error) {
    console.error("Lỗi khi tải địa chỉ:", error);
    alert('Đã xảy ra lỗi khi tải danh sách địa chỉ. Vui lòng thử lại sau: ' + error.message);
  } finally {
    isLoading.value = false; // Kết thúc loading dù thành công hay thất bại
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
onMounted(() => {
  fetchAddresses(); // Gọi hàm tải dữ liệu khi component được mount
});

onBeforeUnmount(() => {
  if (cleanupUpdateListener) cleanupUpdateListener();
  if (cleanupAddListener) cleanupAddListener();
  if (cleanupDeleteListener) cleanupDeleteListener();
});

// --- Xử lý sự kiện thêm địa chỉ mới ---
const openAddAddressModal = () => {
  newAddress.value = {
    recipient_name: '',
    phone_number: '',
    address_line: '',
    ward: '',
    district: '',
    province: '',
    set_as_default: false,
  };
  showAddModal.value = true;
  cleanupAddListener = setupClickOutsideListener(addModalRef, () => showAddModal.value = false);
};

const saveNewAddress = async () => {
  // Ngăn chặn gửi nhiều lần nếu đang trong quá trình lưu
  if (isSavingAddress.value) {
    return;
  }

  isSavingAddress.value = true; // Bắt đầu quá trình lưu, vô hiệu hóa nút
  try {
    // 1. Lấy token từ localStorage
    const token = localStorage.getItem('authToken'); // Lấy token đã lưu sau khi đăng nhập

    // 2. Kiểm tra xem có token không. Nếu không có, không thể thêm địa chỉ mới.
    if (!token) {
      console.warn('Không tìm thấy token xác thực. Vui lòng đăng nhập để thêm địa chỉ.');
      alert('Bạn cần đăng nhập để thêm địa chỉ mới.');
      // Tùy chọn: Chuyển hướng người dùng về trang đăng nhập
      // router.push('/login');
      return; // Dừng hàm lại
    }

    const response = await fetch('http://localhost:8000/api/user/addresses', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        // 3. Thêm Authorization token vào header
        'Authorization': `Bearer ${token}` // Sử dụng token đã lấy từ localStorage
      },
      body: JSON.stringify(newAddress.value)
    });

    if (!response.ok) {
      // Xử lý các lỗi phản hồi từ server
      if (response.status === 401 || response.status === 403) {
        // Đây là lỗi "Unauthenticated" hoặc "Unauthorized"
        console.error('Lỗi xác thực khi thêm địa chỉ: Token không hợp lệ hoặc đã hết hạn.');
        alert('Phiên đăng nhập đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.');
        localStorage.removeItem('authToken'); // Xóa token đã hết hạn
        // Tùy chọn: Chuyển hướng người dùng về trang đăng nhập
        // router.push('/login');
        return; // Dừng hàm
      }
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to add new address');
    }

    await fetchAddresses(); // Tải lại danh sách địa chỉ sau khi thêm thành công

    showAddModal.value = false;
    alert('Địa chỉ mới đã được thêm thành công!');
  } catch (error) {
    console.error("Lỗi khi thêm địa chỉ mới:", error);
    alert('Đã xảy ra lỗi khi thêm địa chỉ mới. Vui lòng thử lại: ' + error.message);
  } finally {
    if (cleanupAddListener) cleanupAddListener();
  }
};

// --- Xử lý sự kiện cập nhật địa chỉ ---
const updateAddress = (addressId) => {
  const addressToUpdate = addresses.value.find((addr) => addr.id === addressId);
  if (addressToUpdate) {
    currentAddress.value = {
      ...JSON.parse(JSON.stringify(addressToUpdate)),
      // Đảm bảo tên trường khớp với Laravel model, xử lý tương thích ngược nếu cần
      recipient_name: addressToUpdate.recipient_name, // Chắc chắn dùng recipient_name
      address_line: addressToUpdate.address_line,     // Chắc chắn dùng address_line
    };
    showUpdateModal.value = true;
    cleanupUpdateListener = setupClickOutsideListener(updateModalRef, () => showUpdateModal.value = false);
  }
};

// --- Xử lý sự kiện lưu địa chỉ đã cập nhật ---
const saveUpdatedAddress = async () => {
  // Ngăn chặn gửi nhiều lần nếu đang trong quá trình cập nhật
  if (isUpdatingAddress.value) {
    return;
  }

  isUpdatingAddress.value = true; // Bắt đầu quá trình cập nhật, vô hiệu hóa nút
  try {
    // 1. Lấy token xác thực từ localStorage
    const token = localStorage.getItem('authToken'); // Đảm bảo khóa là 'authToken'

    // 2. Kiểm tra xem có token không. Nếu không, người dùng chưa được xác thực.
    if (!token) {
      console.warn('Không tìm thấy token xác thực. Vui lòng đăng nhập để cập nhật địa chỉ.');
      alert('Bạn cần đăng nhập để cập nhật địa chỉ.');
      // Tùy chọn: Chuyển hướng người dùng về trang đăng nhập nếu cần
      // router.push('/login');
      return; // Dừng hàm lại
    }

    // currentAddress.value đang được truy cập ở đây
    const response = await fetch(`http://localhost:8000/api/user/addresses/${currentAddress.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        // 3. Đính kèm token vào tiêu đề Authorization
        'Authorization': `Bearer ${token}`
      },
      // Đảm bảo rằng currentAddress.value chứa dữ liệu form cập nhật
      body: JSON.stringify(currentAddress.value)
    });

    if (!response.ok) {
      // Xử lý các lỗi phản hồi từ API
      if (response.status === 401 || response.status === 403) {
        // Lỗi xác thực (Unauthenticated) hoặc không có quyền (Unauthorized)
        console.error('Lỗi xác thực khi cập nhật địa chỉ: Token không hợp lệ hoặc đã hết hạn.');
        alert('Phiên đăng nhập đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.');
        localStorage.removeItem('authToken'); // Xóa token cũ
        // Tùy chọn: Chuyển hướng người dùng về trang đăng nhập
        // router.push('/login');
        return; // Dừng xử lý
      }
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to update address');
    }

    // Nếu cập nhật thành công, tải lại danh sách địa chỉ để phản ánh thay đổi
    await fetchAddresses();

    showUpdateModal.value = false; // Đóng modal cập nhật
    alert('Địa chỉ đã được cập nhật thành công!');
  } catch (error) {
    console.error("Lỗi khi cập nhật địa chỉ:", error);
    alert('Đã xảy ra lỗi khi cập nhật địa chỉ. Vui lòng thử lại: ' + error.message);
  } finally {
    // Luôn dọn dẹp listener, dù thành công hay thất bại
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
    // 1. Get the authentication token from localStorage
    const token = localStorage.getItem('authToken'); // Make sure the key matches what you use to save the token

    // 2. Check if a token exists. If not, the user isn't authenticated.
    if (!token) {
      console.warn('Authentication token not found. Please log in to delete an address.');
      alert('You need to be logged in to delete an address.');
      // Optional: Redirect the user to the login page if needed
      // router.push('/login');
      return; // Stop the function here
    }

    const response = await fetch(`http://localhost:8000/api/user/addresses/${addressToDeleteId.value}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        // 3. Attach the token to the Authorization header
        'Authorization': `Bearer ${token}` // Use the retrieved token
      }
    });

    if (!response.ok) {
      // Handle API response errors
      if (response.status === 401 || response.status === 403) {
        // This is an "Unauthenticated" or "Unauthorized" error
        console.error('Authentication error when deleting address: Token invalid or expired.');
        alert('Your session has expired or is invalid. Please log in again.');
        localStorage.removeItem('authToken'); // Clear the expired token
        // Optional: Redirect the user to the login page
        // router.push('/login');
        return; // Stop processing
      }
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to delete address');
    }

    // If deletion is successful, refetch the addresses to update the UI
    await fetchAddresses();

    showDeleteConfirmModal.value = false; // Close the confirmation modal
    alert('Address deleted successfully!');
  } catch (error) {
    console.error("Error deleting address:", error);
    alert('An error occurred while deleting the address. Please try again: ' + error.message);
  } finally {
    addressToDeleteId.value = null; // Clear the ID of the address to delete
    if (cleanupDeleteListener) cleanupDeleteListener(); // Clean up the listener
  }
};

// --- Xử lý sự kiện thiết lập địa chỉ mặc định ---
const setDefaultAddress = async (addressId) => {
  try {
    // 1. Lấy token xác thực từ localStorage
    const token = localStorage.getItem('authToken'); // Đảm bảo khóa là 'authToken'

    // 2. Kiểm tra xem có token không. Nếu không, người dùng chưa được xác thực.
    if (!token) {
      console.warn('Không tìm thấy token xác thực. Vui lòng đăng nhập để thiết lập địa chỉ mặc định.');
      alert('Bạn cần đăng nhập để thiết lập địa chỉ mặc định.');
      // Tùy chọn: Chuyển hướng người dùng về trang đăng nhập nếu cần
      // router.push('/login');
      return; // Dừng hàm lại
    }

    // URL API cho việc thiết lập địa chỉ mặc định
    // Đảm bảo method là 'PUT' như trong route Laravel của bạn
    const response = await fetch(`http://localhost:8000/api/user/addresses/${addressId}/set-default`, {
      method: 'PUT', // Đã sửa từ 'POST' thành 'PUT' để khớp với route Laravel
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        // 3. Đính kèm token vào tiêu đề Authorization
        'Authorization': `Bearer ${token}`
      },
      // Thường thì request setDefault không cần body nếu chỉ truyền ID qua URL
      // body: JSON.stringify({}) // Bạn có thể gửi body rỗng nếu backend yêu cầu
    });

    if (!response.ok) {
      // Xử lý các lỗi phản hồi từ API
      if (response.status === 401 || response.status === 403) {
        // Lỗi xác thực (Unauthenticated) hoặc không có quyền (Unauthorized)
        console.error('Lỗi xác thực khi thiết lập địa chỉ mặc định: Token không hợp lệ hoặc đã hết hạn.');
        alert('Phiên đăng nhập đã hết hạn hoặc không hợp lệ. Vui lòng đăng nhập lại.');
        localStorage.removeItem('authToken'); // Xóa token cũ
        // Tùy chọn: Chuyển hướng người dùng về trang đăng nhập
        // router.push('/login');
        return; // Dừng xử lý
      }
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to set default address');
    }

    // Nếu thiết lập mặc định thành công, tải lại danh sách địa chỉ để phản ánh thay đổi
    await fetchAddresses();

    alert('Địa chỉ mặc định đã được cập nhật thành công!');
  } catch (error) {
    console.error("Lỗi khi thiết lập mặc định:", error);
    alert('Đã xảy ra lỗi khi thiết lập địa chỉ mặc định. Vui lòng thử lại: ' + error.message);
  }
  // Không có finally block vì không có cleanup đặc biệt nào
};
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Không cần thay đổi gì ở đây nếu bạn đang sử dụng Tailwind CSS */
</style>