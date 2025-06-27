<template>
  <div class="container py-5">
    <h1 class="text-center mb-5 text-success">
      Giá trị của Thuộc tính: "{{ attributeName }}"
    </h1>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Danh sách Giá trị</h2>
        <div>
          <router-link :to="{ name: 'AttributeIndex' }" class="btn btn-light btn-sm me-2">
            <i class="bi bi-arrow-left"></i> Quay lại
          </router-link>
          <router-link :to="{ name: 'AttributeValueCreate', params: { attributeId: attributeId } }" class="btn btn-light btn-sm">
            + Thêm Giá trị
          </router-link>
        </div>
      </div>
      <div class="card-body">
        <div v-if="attributeValues.length === 0" class="text-center text-muted py-4">
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
              <router-link :to="{ name: 'AttributeValueEdit', params: { attributeId: attributeId, valueId: val.id } }" class="btn btn-outline-primary btn-sm me-1" title="Sửa">
                <i class="bi bi-pencil-fill"></i> Sửa
              </router-link>
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
import { ref, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
  attributeId: {
    type: [String, Number],
    required: true
  }
});

let liveToastInstance = null;

const route = useRoute();
const router = useRouter();

const attributeValues = ref([]);
const attributeName = ref('');
const toast = ref({ show: false, message: '', type: '' });

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

const fetchAttributeValues = async () => {
  try {
    // Lấy tên thuộc tính trước
    const attrResponse = await axios.get(`/api/attributes/${props.attributeId}`);
    attributeName.value = attrResponse.data.data.name;

    // Sau đó lấy các giá trị của thuộc tính
    const valuesResponse = await axios.get(`/api/attribute-values?attribute_id=${props.attributeId}`);
    attributeValues.value = valuesResponse.data.data;
  } catch (error) {
    console.error('Lỗi khi lấy giá trị thuộc tính:', error);
    showToast('Lỗi khi tải giá trị thuộc tính.', 'error');
    router.push({ name: 'AttributeIndex' }); // Quay về trang danh sách thuộc tính nếu lỗi
  }
};

const deleteAttributeValue = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa giá trị này không?')) {
    try {
      await axios.delete(`/api/attribute-values/${id}`);
      showToast('Giá trị đã được xóa thành công!');
      fetchAttributeValues();
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

onMounted(() => {
  fetchAttributeValues();
});

// Watch for changes in attributeId if user navigates directly between value lists
watch(() => props.attributeId, () => {
  fetchAttributeValues();
});
</script>