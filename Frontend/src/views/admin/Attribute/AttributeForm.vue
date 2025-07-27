<template>
  <div class="container mx-auto py-12 px-4 sm:px-6 lg:px-8 max-w-2xl">
    <h1 class="text-center mb-12 text-4xl font-extrabold text-gray-800 sm:text-5xl tracking-tight">
      {{ isEditMode ? 'Cập nhật' : 'Thêm mới' }} <span class="text-purple-600">Thuộc tính</span>
    </h1>

    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
      <div class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-6 rounded-t-2xl">
        <h2 class="text-2xl font-semibold flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Thông tin Thuộc tính
        </h2>
      </div>
      <div class="p-8">
        <form @submit.prevent="saveAttribute" v-if="attribute && attribute.name !== undefined">
          <div class="mb-6">
            <label for="attribute-name" class="block text-gray-700 text-lg font-semibold mb-3">Tên thuộc tính</label>
            <input
              type="text"
              id="attribute-name"
              v-model="attribute.name"
              placeholder="Ví dụ: Color"
              class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-800 leading-tight focus:outline-none focus:ring-3 focus:ring-purple-200 focus:border-purple-500 transition-all duration-200 text-lg"
              :class="{'border-red-500 ring-1 ring-red-300': errors.name}"
              required
            />
            <p v-if="errors.name" class="text-red-600 text-sm italic mt-2">{{ errors.name[0] }}</p>
          </div>

          <div class="flex justify-end gap-4 mt-8">
            <router-link :to="{ name: 'AttributeIndex' }" class="px-6 py-3 rounded-lg text-base font-semibold transition-all duration-200 bg-gray-200 hover:bg-gray-300 text-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-opacity-75 shadow-md">
              Hủy
            </router-link>
            <button type="submit" class="px-6 py-3 rounded-lg text-base font-semibold transition-all duration-200 bg-purple-600 hover:bg-purple-700 text-white focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-opacity-75 shadow-md">
              Lưu Thuộc tính
            </button>
          </div>
        </form>
        <div v-else class="text-center text-gray-500 py-12 text-xl">
          Đang tải dữ liệu hoặc không tìm thấy thuộc tính...
        </div>
      </div>
    </div>

    <div class="fixed bottom-8 right-8 p-4 z-50">
      <div
        id="liveToast"
        class="min-w-[300px] p-6 rounded-xl shadow-2xl text-white transition-all duration-500 transform ease-out"
        :class="{
          'opacity-100 translate-y-0': toast.show,
          'opacity-0 translate-y-full': !toast.show,
          'bg-green-600': toast.type === 'success',
          'bg-red-600': toast.type === 'error'
        }"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else-if="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2A9 9 0 111 12a9 9 0 0118 0z" />
            </svg>
            <span class="text-lg font-semibold">{{ toast.message }}</span>
          </div>
          <button type="button" @click="toast.show = false" class="ml-4 text-white hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-white rounded-md p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();

const attribute = ref({ id: null, name: '' });
const errors = ref({});
const toast = ref({ show: false, message: '', type: '' });

const isEditMode = computed(() => route.params.id !== undefined);

const showToast = (message, type = 'success') => {
  toast.value.message = message;
  toast.value.type = type;
  toast.value.show = true;
  setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const fetchAttribute = async (id) => {
  try {
    const response = await axios.get(`/admin/attributes/${id}`);
    if (response.data && response.data.data && typeof response.data.data === 'object') {
      attribute.value = response.data.data;
    } else {
      showToast('Dữ liệu thuộc tính không hợp lệ từ API.', 'error');
      router.push({ name: 'AttributeIndex' });
    }
  } catch (error) {
    console.error('Lỗi khi lấy thuộc tính:', error);
    if (error.response && error.response.status === 404) {
      showToast('Không tìm thấy thuộc tính này.', 'error');
    } else if (error.response && error.response.status === 401) {
      showToast('Không được phép. Vui lòng đăng nhập.', 'error');
    } else {
      showToast('Có lỗi xảy ra khi tải dữ liệu thuộc tính.', 'error');
    }
    router.push({ name: 'AttributeIndex' });
  }
};

const saveAttribute = async () => {
  try {
    errors.value = {};
    if (isEditMode.value) {
      await axios.put(`/admin/attributes/${attribute.value.id}`, attribute.value);
      showToast('Thuộc tính đã được cập nhật thành công!');
    } else {
      await axios.post('/admin/attributes', attribute.value);
      showToast('Thuộc tính đã được thêm mới thành công!');
    }
    router.push({ name: 'AttributeIndex' });
  } catch (error) {
    console.error('Lỗi khi lưu thuộc tính:', error);
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else if (error.response && error.response.status === 401) {
      showToast('Không được phép. Vui lòng đăng nhập.', 'error');
    } else {
      showToast('Có lỗi xảy ra khi lưu thuộc tính.', 'error');
    }
  }
};

onMounted(() => {
  if (isEditMode.value) {
    if (route.params.id) {
      fetchAttribute(route.params.id);
    } else {
      console.error('Thiếu ID thuộc tính để chỉnh sửa. Chuyển hướng về trang danh sách.');
      showToast('Thiếu ID thuộc tính để chỉnh sửa.', 'error');
      router.push({ name: 'AttributeIndex' });
    }
  }
});
</script>