<template>
  <div class="container py-5">
    <h1 class="text-center mb-5 text-primary">{{ isEditMode ? 'Cập nhật' : 'Thêm mới' }} Thuộc tính</h1>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white">
        <h2 class="h5 mb-0">Thông tin Thuộc tính</h2>
      </div>
      <div class="card-body">
        <form @submit.prevent="saveAttribute" v-if="attribute && attribute.name !== undefined">
          <div class="mb-3">
            <label for="attribute-name" class="form-label">Tên thuộc tính</label>
            <input
              type="text"
              id="attribute-name"
              v-model="attribute.name"
              placeholder="Ví dụ: Color"
              class="form-control"
              :class="{'is-invalid': errors.name}"
              required
            />
            <div v-if="errors.name" class="invalid-feedback">{{ errors.name[0] }}</div>
          </div>

          <div class="d-flex justify-content-end">
            <router-link :to="{ name: 'AttributeIndex' }" class="btn btn-secondary me-2">Hủy</router-link>
            <button type="submit" class="btn btn-primary">Lưu Thuộc tính</button>
          </div>
        </form>
        <div v-else class="text-center text-muted py-4">
          Đang tải dữ liệu hoặc không tìm thấy thuộc tính...
        </div>
      </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11">
      <div id="liveToast" class="toast" :class="{'show': toast.show, 'text-bg-success': toast.type === 'success', 'text-bg-danger': toast.type === 'error'}" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">{{ toast.message }}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'; // Thêm nextTick
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

let liveToastInstance = null;

const route = useRoute();
const router = useRouter();

// Khởi tạo attribute với các giá trị mặc định để tránh undefined
const attribute = ref({ id: null, name: '' }); 
const errors = ref({});
const toast = ref({ show: false, message: '', type: '' });

const isEditMode = computed(() => route.params.id !== undefined);

const showToast = (message, type = 'success') => {
  toast.value.message = message;
  toast.value.type = type;
  toast.value.show = true;
  nextTick(() => {
    const toastEl = document.getElementById('liveToast');
    if (toastEl && window.bootstrap && window.bootstrap.Toast) {
      if (liveToastInstance) {
        liveToastInstance.dispose();
      }
      liveToastInstance = new window.bootstrap.Toast(toastEl, { delay: 3000 });
      liveToastInstance.show();
      toastEl.addEventListener('hidden.bs.toast', () => {
        toast.value.show = false;
        liveToastInstance = null;
      }, { once: true });
    } else {
        console.error("Bootstrap Toast is not available globally or toast element not found.");
    }
  });
};

const fetchAttribute = async (id) => {
  try {
    const response = await axios.get(`/admin/attributes/${id}`);
    // Kiểm tra kỹ dữ liệu trả về từ API
    if (response.data && response.data.data && typeof response.data.data === 'object') {
      attribute.value = response.data.data;
    } else {
      // Nếu dữ liệu không hợp lệ, hiển thị lỗi và chuyển hướng
      showToast('Dữ liệu thuộc tính không hợp lệ từ API.', 'error');
      router.push({ name: 'AttributeIndex' });
    }
  } catch (error) {
    console.error('Lỗi khi lấy thuộc tính:', error);
    // Xử lý các mã lỗi cụ thể từ API
    if (error.response && error.response.status === 404) {
      showToast('Không tìm thấy thuộc tính này.', 'error');
    } else if (error.response && error.response.status === 401) {
      showToast('Không được phép. Vui lòng đăng nhập.', 'error');
    } else {
      showToast('Có lỗi xảy ra khi tải dữ liệu thuộc tính.', 'error');
    }
    // Luôn chuyển hướng về trang danh sách nếu có lỗi khi tải dữ liệu
    router.push({ name: 'AttributeIndex' }); 
  }
};

const saveAttribute = async () => {
  try {
    errors.value = {}; // Reset lỗi
    if (isEditMode.value) {
      await axios.put(`/admin/attributes/${attribute.value.id}`, attribute.value);
      showToast('Thuộc tính đã được cập nhật thành công!');
    } else {
      await axios.post('/admin/attributes', attribute.value);
      showToast('Thuộc tính đã được thêm mới thành công!');
    }
    router.push({ name: 'AttributeIndex' }); // Chuyển hướng về trang danh sách
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
    // Đảm bảo route.params.id tồn tại trước khi gọi API
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