<template>
  <div class="container py-5">
    <h1 class="text-center mb-5 text-primary">Quản lý Thuộc tính</h1>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Danh sách Thuộc tính</h2>
        <router-link :to="{ name: 'AttributeCreate' }" class="btn btn-light btn-sm">
          + Thêm Thuộc tính
        </router-link>
      </div>
      <div class="card-body">
        <div v-if="isLoading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Đang tải...</span>
          </div>
          <p class="mt-2 text-muted">Đang tải thuộc tính...</p>
        </div>
        <div v-else-if="attributes.length === 0" class="text-center text-muted py-4">
          Chưa có thuộc tính nào được tạo.
        </div>
        
        <ul v-else class="list-group list-group-flush">
          <li
            v-for="attr in attributes"
            :key="attr.id"
            class="list-group-item d-flex justify-content-between align-items-center"
          >
            <div>
              <span class="fw-bold">{{ attr.name }}</span>
              <span class="text-muted ms-2">({{ attr.slug }})</span>
            </div>
            <div class="btn-group">
              <router-link :to="{ name: 'AttributeValueIndex', params: { attributeId: attr.id } }" class="btn btn-outline-info btn-sm me-1" title="Quản lý giá trị">
                <i class="bi bi-list"></i> Giá trị
              </router-link>
              <router-link :to="{ name: 'AttributeEdit', params: { id: attr.id } }" class="btn btn-outline-primary btn-sm me-1" title="Sửa">
                <i class="bi bi-pencil-fill"></i> Sửa
              </router-link>
              <button
                @click="deleteAttribute(attr.id)"
                class="btn btn-outline-danger btn-sm"
                title="Xóa"
              >
                <i class="bi bi-trash-fill"></i> Xóa
              </button>
            </div>
          </li>
        </ul>

        <nav v-if="totalPages > 1 && !isLoading" aria-label="Page navigation" class="mt-4">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="goToPage(currentPage - 1)">Trước</a>
            </li>
            <li
              v-for="page in totalPages"
              :key="page"
              class="page-item"
              :class="{ active: page === currentPage }"
            >
              <a class="page-link" href="#" @click.prevent="goToPage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="goToPage(currentPage + 1)">Sau</a>
            </li>
          </ul>
        </nav>
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
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

let liveToastInstance = null;

const attributes = ref([]);
const toast = ref({ show: false, message: '', type: '' });
const isLoading = ref(false); // Added for loading state

// Pagination state
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = ref(10); // Default items per page, adjust as needed

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

const fetchAttributes = async (page = 1) => {
  isLoading.value = true; // Start loading
  try {
    const response = await axios.get(`/admin/attributes?page=${page}&per_page=${perPage.value}`);
    attributes.value = response.data.data;
    
    // Update pagination state based on API response
    currentPage.value = response.data.meta.current_page;
    totalPages.value = response.data.meta.last_page;
    perPage.value = response.data.meta.per_page;

  } catch (error) {
    console.error('Lỗi khi lấy attributes:', error);
    if (error.response && error.response.status === 401) {
        showToast('Phiên làm việc hết hạn. Vui lòng đăng nhập lại.', 'error');
    } else {
        showToast('Lỗi khi tải danh sách thuộc tính.', 'error');
    }
  } finally {
    isLoading.value = false; // End loading
  }
};

const deleteAttribute = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa thuộc tính này không? Thao tác này sẽ xóa tất cả các giá trị và liên kết của thuộc tính này.')) {
    isLoading.value = true; // Start loading for deletion
    try {
      await axios.delete(`/admin/attributes/${id}`);
      showToast('Thuộc tính đã được xóa thành công!');
      // After deletion, re-fetch attributes for the current page
      // Consider if the current page becomes empty after deletion,
      // you might need to go to the previous page:
      const newPage = attributes.value.length === 1 && currentPage.value > 1 
                      ? currentPage.value - 1 
                      : currentPage.value;
      fetchAttributes(newPage); 
    } catch (error) {
      console.error('Lỗi khi xóa thuộc tính:', error);
      if (error.response && error.response.status === 401) {
        showToast('Không được phép. Vui lòng đăng nhập.', 'error');
      } else {
        showToast('Có lỗi xảy ra khi xóa thuộc tính.', 'error');
      }
    } finally {
      isLoading.value = false; // End loading
    }
  }
};

// Function to navigate to a specific page
const goToPage = (page) => {
  if (page > 0 && page <= totalPages.value && page !== currentPage.value) {
    currentPage.value = page;
    fetchAttributes(page);
  }
};

onMounted(() => {
  fetchAttributes(); // Initial fetch on component mount
});
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
</style>