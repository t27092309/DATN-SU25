<template>
  <div class="container py-5">
    <h1 class="text-center mb-5 text-primary">Quản lý Thuộc tính & Giá trị</h1>

    <div class="row g-4">

      <div class="col-md-6">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Danh sách Thuộc tính</h2>
            <button
              @click="openAttributeModal()"
              class="btn btn-light btn-sm"
            >
              + Thêm Thuộc tính
            </button>
          </div>
          <div class="card-body">
            <div v-if="attributes.length === 0" class="text-center text-muted py-4">
              Chưa có thuộc tính nào được tạo.
            </div>
            
            <ul v-else class="list-group list-group-flush">
              <li
                v-for="attr in attributes"
                :key="attr.id"
                :class="{
                  'list-group-item-primary': selectedAttribute?.id === attr.id,
                }"
                class="list-group-item d-flex justify-content-between align-items-center cursor-pointer"
                @click="selectAttribute(attr)"
              >
                <div>
                  <span class="fw-bold">{{ attr.name }}</span>
                  <span class="text-muted ms-2">({{ attr.slug }})</span>
                </div>
                <div class="btn-group">
                  <button
                    @click.stop="openAttributeModal(attr)"
                    class="btn btn-outline-primary btn-sm me-1"
                    title="Sửa"
                  >
                    <i class="bi bi-pencil-fill"></i> Sửa
                  </button>
                  <button
                    @click.stop="deleteAttribute(attr.id)"
                    class="btn btn-outline-danger btn-sm"
                    title="Xóa"
                  >
                    <i class="bi bi-trash-fill"></i> Xóa
                  </button>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">
              <span v-if="selectedAttribute">Giá trị của "{{ selectedAttribute.name }}"</span>
              <span v-else>Giá trị thuộc tính</span>
            </h2>
            <button
              @click="openValueModal()"
              class="btn btn-light btn-sm"
              :disabled="!selectedAttribute"
            >
              + Thêm Giá trị
            </button>
          </div>
          <div class="card-body">
            <div v-if="!selectedAttribute" class="text-center text-muted py-4">
              Chọn một thuộc tính để xem và quản lý các giá trị của nó.
            </div>
            <div v-else-if="attributeValues.length === 0" class="text-center text-muted py-4">
              Chưa có giá trị nào cho thuộc tính này.
            </div>

            <ul v-else class="list-group list-group-flush">
              <li
                v-for="val in attributeValues"
                :key="val.id"
                class="list-group-item d-flex justify-content-between align-items-center"
              >
                <span class="fw-bold">{{ val.value }}</span>
                <div class="btn-group">
                  <button
                    @click="openValueModal(val)"
                    class="btn btn-outline-primary btn-sm me-1"
                    title="Sửa"
                  >
                    <i class="bi bi-pencil-fill"></i> Sửa
                  </button>
                  <button
                    @click="deleteAttributeValue(val.id)"
                    class="btn btn-outline-danger btn-sm"
                    title="Xóa"
                  >
                    <i class="bi bi-trash-fill"></i> Xóa
                  </button>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

    </div>

    <div class="modal fade" id="attributeModal" tabindex="-1" aria-labelledby="attributeModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="attributeModalLabel">{{ currentAttribute.id ? 'Cập nhật' : 'Thêm mới' }} Thuộc tính</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="saveAttribute">
            <div class="modal-body">
              <div class="mb-3">
                <label for="attribute-name" class="form-label">Tên thuộc tính</label>
                <input
                  type="text"
                  id="attribute-name"
                  v-model="currentAttribute.name"
                  placeholder="Ví dụ: Color"
                  class="form-control"
                  :class="{'is-invalid': attributeErrors.name}"
                  required
                />
                <div v-if="attributeErrors.name" class="invalid-feedback">{{ attributeErrors.name[0] }}</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" class="btn btn-primary">Lưu</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="attributeValueModal" tabindex="-1" aria-labelledby="attributeValueModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="attributeValueModalLabel">{{ currentAttributeValue.id ? 'Cập nhật' : 'Thêm mới' }} Giá trị</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form @submit.prevent="saveAttributeValue">
            <div class="modal-body">
              <div class="mb-3">
                <label for="attribute-value" class="form-label">Giá trị</label>
                <input
                  type="text"
                  id="attribute-value"
                  v-model="currentAttributeValue.value"
                  placeholder="Ví dụ: Red"
                  class="form-control"
                  :class="{'is-invalid': valueErrors.value}"
                  required
                />
                <div v-if="valueErrors.value" class="invalid-feedback">{{ valueErrors.value[0] }}</div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
              <button type="submit" class="btn btn-success">Lưu</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div
      class="toast-container position-fixed bottom-0 end-0 p-3"
      style="z-index: 11"
    >
      <div
        id="liveToast"
        class="toast"
        :class="{'show': toast.show, 'text-bg-success': toast.type === 'success', 'text-bg-danger': toast.type === 'error'}"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
      >
        <div class="d-flex">
          <div class="toast-body">
            {{ toast.message }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import axios from 'axios';

// ==============================================
// 1. STATE REACTIVE
// ==============================================
const attributes = ref([]); // Danh sách tất cả thuộc tính
const selectedAttribute = ref(null); // Thuộc tính đang được chọn để quản lý giá trị
const attributeValues = ref([]); // Danh sách giá trị của thuộc tính được chọn

let attributeModalInstance = null; // Biến để lưu instance của Bootstrap Modal
let attributeValueModalInstance = null; // Biến để lưu instance của Bootstrap Modal
let liveToastInstance = null; // Biến để lưu instance của Bootstrap Toast

const currentAttribute = ref({ id: null, name: '' }); // Dữ liệu attribute đang được chỉnh sửa
const currentAttributeValue = ref({ id: null, value: '', attribute_id: null }); // Dữ liệu value đang được chỉnh sửa

const attributeErrors = ref({}); // Lỗi validation cho attribute
const valueErrors = ref({}); // Lỗi validation cho value

const toast = ref({ show: false, message: '', type: '' });

// ==============================================
// 2. LOGIC CHUNG
// ==============================================

// Hàm hiển thị toast notification
const showToast = (message, type = 'success') => {
  toast.value.message = message;
  toast.value.type = type;
  toast.value.show = true;
  // Dùng nextTick để đảm bảo DOM đã render trước khi khởi tạo Toast
  nextTick(() => {
    const toastEl = document.getElementById('liveToast');
    if (toastEl) {
      if (liveToastInstance) {
        liveToastInstance.dispose(); // Xóa instance cũ nếu có
      }
      liveToastInstance = new Toast(toastEl, { delay: 3000 });
      liveToastInstance.show();
      toastEl.addEventListener('hidden.bs.toast', () => {
        toast.value.show = false;
        liveToastInstance = null;
      }, { once: true });
    }
  });
};

// ==============================================
// 3. LOGIC CHO ATTRIBUTE
// ==============================================

// Lấy danh sách tất cả attributes
const fetchAttributes = async () => {
  try {
    const response = await axios.get('http://localhost:8000/api/attributes');
    attributes.value = response.data.data; // Dữ liệu từ phân trang của Laravel
  } catch (error) {
    console.error('Lỗi khi lấy attributes:', error);
    // Xử lý lỗi token nếu có (ví dụ: chuyển hướng về trang đăng nhập)
    if (error.response && error.response.status === 401) {
        showToast('Phiên làm việc hết hạn. Vui lòng đăng nhập lại.', 'error');
        // Optional: Redirect to login page
        // window.location.href = '/login'; 
    } else {
        showToast('Lỗi khi tải danh sách thuộc tính.', 'error');
    }
  }
};

// Mở modal thêm/sửa attribute
const openAttributeModal = (attr = null) => {
  attributeErrors.value = {}; // Reset lỗi
  if (attr) {
    // Chế độ chỉnh sửa
    currentAttribute.value = { ...attr };
  } else {
    // Chế độ thêm mới
    currentAttribute.value = { id: null, name: '' };
  }
  attributeModalInstance.show(); // Hiển thị modal Bootstrap
};

// Lưu (tạo mới hoặc cập nhật) attribute
const saveAttribute = async () => {
  try {
    if (currentAttribute.value.id) {
      // Cập nhật
      await axios.put(`/api/attributes/${currentAttribute.value.id}`, currentAttribute.value);
      showToast('Thuộc tính đã được cập nhật thành công!');
    } else {
      // Tạo mới
      await axios.post('/api/attributes', currentAttribute.value);
      showToast('Thuộc tính đã được thêm mới thành công!');
    }
    attributeModalInstance.hide(); // Ẩn modal Bootstrap
    fetchAttributes(); // Tải lại danh sách
    attributeErrors.value = {}; // Xóa lỗi sau khi thành công
  } catch (error) {
    console.error('Lỗi khi lưu thuộc tính:', error);
    if (error.response && error.response.status === 422) {
      attributeErrors.value = error.response.data.errors;
    } else if (error.response && error.response.status === 401) {
        showToast('Không được phép. Vui lòng đăng nhập.', 'error');
    } else {
      showToast('Có lỗi xảy ra khi lưu thuộc tính.', 'error');
    }
  }
};

// Xóa attribute
const deleteAttribute = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa thuộc tính này không? Thao tác này sẽ xóa tất cả các giá trị và liên kết của thuộc tính này.')) {
    try {
      await axios.delete(`/api/attributes/${id}`);
      showToast('Thuộc tính đã được xóa thành công!');
      fetchAttributes(); // Tải lại danh sách
      if (selectedAttribute.value?.id === id) {
        selectedAttribute.value = null; // Bỏ chọn nếu thuộc tính bị xóa
        attributeValues.value = [];
      }
    } catch (error) {
      console.error('Lỗi khi xóa thuộc tính:', error);
      if (error.response && error.response.status === 401) {
        showToast('Không được phép. Vui lòng đăng nhập.', 'error');
      } else {
        showToast('Có lỗi xảy ra khi xóa thuộc tính.', 'error');
      }
    }
  }
};

// ==============================================
// 4. LOGIC CHO ATTRIBUTE VALUE
// ==============================================

// Chọn một attribute để quản lý giá trị của nó
const selectAttribute = (attr) => {
  selectedAttribute.value = attr;
  fetchAttributeValues(attr.id);
};

// Lấy danh sách giá trị cho attribute đã chọn
const fetchAttributeValues = async (attributeId) => {
  try {
    // Controller API đã được chỉnh sửa để nhận tham số attribute_id
    const response = await axios.get(`/api/attribute-values?attribute_id=${attributeId}`);
    attributeValues.value = response.data.data;
  } catch (error) {
    console.error('Lỗi khi lấy giá trị thuộc tính:', error);
    if (error.response && error.response.status === 401) {
        showToast('Không được phép. Vui lòng đăng nhập.', 'error');
    } else {
        showToast('Lỗi khi tải giá trị thuộc tính.', 'error');
    }
  }
};

// Mở modal thêm/sửa value
const openValueModal = (val = null) => {
  if (!selectedAttribute.value) {
    showToast('Vui lòng chọn một thuộc tính trước để thêm giá trị.', 'error');
    return;
  }
  valueErrors.value = {}; // Reset lỗi
  if (val) {
    // Chế độ chỉnh sửa
    currentAttributeValue.value = { ...val };
  } else {
    // Chế độ thêm mới
    currentAttributeValue.value = {
      id: null,
      value: '',
      attribute_id: selectedAttribute.value.id,
    };
  }
  attributeValueModalInstance.show(); // Hiển thị modal Bootstrap
};

// Lưu (tạo mới hoặc cập nhật) value
const saveAttributeValue = async () => {
  try {
    if (currentAttributeValue.value.id) {
      // Cập nhật
      await axios.put(`/api/attribute-values/${currentAttributeValue.value.id}`, currentAttributeValue.value);
      showToast('Giá trị đã được cập nhật thành công!');
    } else {
      // Tạo mới
      await axios.post('/api/attribute-values', currentAttributeValue.value);
      showToast('Giá trị đã được thêm mới thành công!');
    }
    attributeValueModalInstance.hide(); // Ẩn modal Bootstrap
    fetchAttributeValues(selectedAttribute.value.id); // Tải lại danh sách
    valueErrors.value = {}; // Xóa lỗi sau khi thành công
  } catch (error) {
    console.error('Lỗi khi lưu giá trị thuộc tính:', error);
    if (error.response && error.response.status === 422) {
      valueErrors.value = error.response.data.errors;
    } else if (error.response && error.response.status === 401) {
        showToast('Không được phép. Vui lòng đăng nhập.', 'error');
    } else {
      showToast('Có lỗi xảy ra khi lưu giá trị thuộc tính.', 'error');
    }
  }
};

// Xóa value
const deleteAttributeValue = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa giá trị này không?')) {
    try {
      await axios.delete(`/api/attribute-values/${id}`);
      showToast('Giá trị đã được xóa thành công!');
      fetchAttributeValues(selectedAttribute.value.id); // Tải lại danh sách
    } catch (error) {
      console.error('Lỗi khi xóa giá trị thuộc tính:', error);
      if (error.response && error.response.status === 401) {
        showToast('Không được phép. Vui lòng đăng nhập.', 'error');
      } else {
        showToast('Có lỗi xảy ra khi xóa giá trị thuộc tính.', 'error');
      }
    }
  }
};

// ==============================================
// 5. LIFECYCLE HOOKS
// ==============================================
onMounted(() => {
  fetchAttributes();
  // Khởi tạo instance của Bootstrap Modals
  attributeModalInstance = new Modal(document.getElementById('attributeModal'));
  attributeValueModalInstance = new Modal(document.getElementById('attributeValueModal'));
});
</script>

<style scoped>
/* Custom styles (nếu cần) - Giữ nguyên một số style để biểu thị hành vi cursor */
.cursor-pointer {
  cursor: pointer;
}
/* Font Awesome hoặc Bootstrap Icons có thể cần thêm cài đặt nếu bạn muốn dùng icon */
/* Ví dụ cho Bootstrap Icons: npm install bootstrap-icons */
</style>