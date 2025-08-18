<template>
  <div class="container mx-auto p-4">
    <h1 class="text-3xl font-bold mb-6">Quản lý Banner</h1>

    <!-- Form Thêm/Sửa Banner -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
      <h2 class="text-xl font-semibold mb-4">
        {{ editingBanner.id ? "Cập nhật Banner" : "Thêm Banner Mới" }}
      </h2>
      <form @submit.prevent="saveBanner">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Title -->
          <div>
            <label for="title" class="block text-gray-700 font-bold mb-2"
              >Tiêu đề</label
            >
            <input
              type="text"
              id="title"
              v-model="editingBanner.title"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <!-- Link URL -->
          <div>
            <label for="link_url" class="block text-gray-700 font-bold mb-2"
              >Đường dẫn</label
            >
            <input
              type="url"
              id="link_url"
              v-model="editingBanner.link_url"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <!-- Description -->
          <div class="md:col-span-2">
            <label for="description" class="block text-gray-700 font-bold mb-2"
              >Mô tả</label
            >
            <textarea
              id="description"
              v-model="editingBanner.description"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>
          <!-- Image -->
          <div class="md:col-span-2">
            <label for="image" class="block text-gray-700 font-bold mb-2"
              >Hình ảnh</label
            >
            <input
              type="file"
              id="image"
              @change="handleFileChange"
              class="w-full px-3 py-2 border rounded-lg"
              accept="image/*"
            />

            <!-- Hiển thị ảnh xem trước -->
            <div v-if="previewImageUrl" class="mt-4">
              <img
                :src="previewImageUrl"
                alt="Image Preview"
                class="max-h-48 rounded-lg shadow-md"
              />
            </div>
            <!-- Hiển thị ảnh cũ nếu đang ở chế độ sửa và chưa chọn ảnh mới -->
            <div v-else-if="editingBanner.image_url" class="mt-4">
              <p class="text-sm text-gray-500 mb-2">Đang sử dụng ảnh cũ:</p>
              <img
                :src="`${BASE_URL}${editingBanner.image_url}`"
                alt="Current Banner Image"
                class="max-h-48 rounded-lg shadow-md"
              />
            </div>
          </div>
          <!-- Is Active -->
          <div class="flex items-center">
            <input
              type="checkbox"
              id="is_active"
              v-model="editingBanner.is_active"
              class="form-checkbox h-5 w-5 text-blue-600"
            />
            <label for="is_active" class="ml-2 text-gray-700">Hiển thị</label>
          </div>
        </div>
        <div class="mt-6 flex gap-4">
          <button
            type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          >
            {{ editingBanner.id ? "Cập nhật" : "Thêm mới" }}
          </button>
          <button
            type="button"
            @click="resetForm"
            class="bg-gray-400 text-white px-4 py-2 rounded-lg hover:bg-gray-500 transition-colors"
          >
            Hủy
          </button>
        </div>
      </form>
    </div>

    <!-- Danh sách Banner -->
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4">Danh sách Banner</h2>
      <div v-if="loading" class="text-center py-8 text-gray-500">
        Đang tải danh sách banner...
      </div>
      <div v-else-if="error" class="text-center py-8 text-red-500">
        Lỗi khi tải dữ liệu: {{ error }}
      </div>
      <div
        v-else-if="banners.length === 0"
        class="text-center py-8 text-gray-500"
      >
        Hiện tại chưa có banner nào được thêm.
      </div>
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr>
            <th
              class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              ID
            </th>
            <th
              class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Ảnh
            </th>
            <th
              class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Tiêu đề
            </th>
            <th
              class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Trạng thái
            </th>
            <th
              class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Hành động
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="banner in banners" :key="banner.id">
            <td
              class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
            >
              {{ banner.id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <img
                :src="
                  banner.image_url
                    ? `${BASE_URL}${banner.image_url}`
                    : 'https://placehold.co/64x64/E5E7EB/4B5563?text=No+Image'
                "
                :alt="banner.title || 'No Image'"
                class="h-16 w-16 object-cover rounded-md"
              />
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
              {{ banner.title || "Không có tiêu đề" }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="{
                  'bg-green-100 text-green-800': banner.is_active,
                  'bg-red-100 text-red-800': !banner.is_active,
                }"
                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ banner.is_active ? "Hiển thị" : "Ẩn" }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button
                @click="showDetail(banner)"
                class="text-green-600 hover:text-green-900 mr-4 transition-colors"
              >
                Xem chi tiết
              </button>
              <button
                @click="editBanner(banner)"
                class="text-indigo-600 hover:text-indigo-900 mr-4 transition-colors"
              >
                Sửa
              </button>
              <button
                @click="deleteBanner(banner.id)"
                class="text-red-600 hover:text-red-900 transition-colors"
              >
                Xóa
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex justify-center mt-4">
        <button
          v-for="page in pagination.last_page"
          :key="page"
          @click="fetchBanners(page)"
          :class="{
            'bg-blue-500 text-white': pagination.current_page === page,
            'bg-gray-200': pagination.current_page !== page,
          }"
          class="px-4 py-2 rounded-lg mx-1 transition-colors"
        >
          {{ page }}
        </button>
      </div>
    </div>

    <!-- Modal cho Success Message -->
    <div
      v-if="showSuccessModal"
      class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50"
    >
      <div
        class="bg-white p-6 rounded-lg shadow-2xl max-w-md mx-auto text-center transform transition-all duration-300 ease-in-out"
      >
        <div class="mb-4">
          <div
            class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-10 w-10 text-green-600"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
            </svg>
          </div>
        </div>
        <h3 class="text-2xl font-bold mb-2 text-gray-900">
          {{ successTitle }}
        </h3>
        <p class="text-gray-700 mb-4">{{ successMessage }}</p>
        <button
          @click="showSuccessModal = false"
          class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors"
        >
          Đóng
        </button>
      </div>
    </div>

    <!-- Modal cho Xem Chi Tiết Banner -->
    <div
      v-if="showDetailModal"
      class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50"
    >
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-lg mx-auto">
        <h3 class="text-xl font-bold mb-4 text-gray-900">Chi tiết Banner</h3>
        <div class="grid grid-cols-1 gap-4">
          <p>
            <strong>ID:</strong>
            <span class="text-gray-700">{{ selectedBanner.id }}</span>
          </p>
          <p>
            <strong>Tiêu đề:</strong>
            <span class="text-gray-700">{{
              selectedBanner.title || "Không có tiêu đề"
            }}</span>
          </p>
          <p>
            <strong>Mô tả:</strong>
            <span class="text-gray-700">{{
              selectedBanner.description || "Không có mô tả"
            }}</span>
          </p>
          <p>
            <strong>Đường dẫn:</strong>
            <a
              :href="selectedBanner.link_url"
              target="_blank"
              class="text-blue-600 hover:underline"
              >{{ selectedBanner.link_url || "Không có đường dẫn" }}</a
            >
          </p>
          <p>
            <strong>Trạng thái:</strong>
            <span class="text-gray-700">{{
              selectedBanner.is_active ? "Hiển thị" : "Ẩn"
            }}</span>
          </p>
          <div class="mt-4">
            <img
              :src="`${BASE_URL}${selectedBanner.image_url}`"
              alt="Banner Image"
              class="w-full h-64 object-cover rounded-lg shadow-md"
            />
          </div>
        </div>
        <button
          @click="showDetailModal = false"
          class="mt-6 bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
        >
          Đóng
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";

const BASE_URL = "http://127.0.0.1:8000"; // Điều chỉnh nếu URL/cổng backend thay đổi

const banners = ref([]);
const editingBanner = ref({
  id: null,
  title: "",
  description: "",
  link_url: "",
  is_active: true,
  image_url: null,
});
const imageFile = ref(null);
const previewImageUrl = ref(null);
const loading = ref(true);
const error = ref(null);
const pagination = ref({});

const showSuccessModal = ref(false);
const successTitle = ref("");
const successMessage = ref("");
const showDetailModal = ref(false);
const selectedBanner = ref({});

const API_BASE_URL = "http://127.0.0.1:8000/api/admin/banners";

onMounted(() => {
  fetchBanners();
});

// Lấy danh sách banner
const fetchBanners = async (page = 1) => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get(`${API_BASE_URL}?page=${page}`);
    banners.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
    };
  } catch (err) {
    error.value = "Không thể tải danh sách banner.";
    console.error("Error fetching banners:", err);
  } finally {
    loading.value = false;
  }
};

// Xử lý khi chọn file ảnh
const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    imageFile.value = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      previewImageUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    imageFile.value = null;
    previewImageUrl.value = null;
  }
};

// Lưu banner (thêm mới hoặc cập nhật)
const saveBanner = async () => {
  const formData = new FormData();
  formData.append("title", editingBanner.value.title || "");
  formData.append("description", editingBanner.value.description || "");
  formData.append("link_url", editingBanner.value.link_url || "");
  formData.append("is_active", editingBanner.value.is_active ? 1 : 0);

  if (imageFile.value) {
    formData.append("image", imageFile.value);
  }

  try {
    if (editingBanner.value.id) {
      formData.append("_method", "PUT");
      await axios.post(`${API_BASE_URL}/${editingBanner.value.id}`, formData);
      successTitle.value = "Cập nhật thành công";
      successMessage.value = "Banner đã được cập nhật thành công!";
    } else {
      await axios.post(API_BASE_URL, formData);
      successTitle.value = "Thêm mới thành công";
      successMessage.value = "Banner mới đã được thêm thành công!";
    }

    showSuccessModal.value = true;
    resetForm();
    fetchBanners(pagination.value.current_page);
  } catch (err) {
    console.error(
      "Error saving banner:",
      err.response?.data?.message || err.message
    );
    alert(`Có lỗi xảy ra: ${err.response?.data?.message || err.message}`);
  }
};

// Bắt đầu sửa banner
const editBanner = (banner) => {
  editingBanner.value = {
    ...banner,
    is_active: !!banner.is_active,
  };
  imageFile.value = null;
  previewImageUrl.value = null;
  document.getElementById("image").value = "";
};

// Xóa banner
const deleteBanner = async (id) => {
  if (confirm("Bạn có chắc chắn muốn xóa banner này không?")) {
    try {
      await axios.delete(`${API_BASE_URL}/${id}`);
      successTitle.value = "Xóa thành công";
      successMessage.value = "Banner đã được xóa thành công!";
      showSuccessModal.value = true;
      fetchBanners(pagination.value.current_page);
    } catch (err) {
      console.error(
        "Error deleting banner:",
        err.response?.data?.message || err.message
      );
      alert(
        `Có lỗi xảy ra khi xóa: ${err.response?.data?.message || err.message}`
      );
    }
  }
};

// Xem chi tiết banner
const showDetail = (banner) => {
  selectedBanner.value = { ...banner };
  showDetailModal.value = true;
};

// Reset form
const resetForm = () => {
  editingBanner.value = {
    id: null,
    title: "",
    description: "",
    link_url: "",
    is_active: true,
    image_url: null,
  };
  imageFile.value = null;
  previewImageUrl.value = null;
  document.getElementById("image").value = "";
};
</script>

<style scoped>
/* Thêm hiệu ứng cho modal */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
</style>
