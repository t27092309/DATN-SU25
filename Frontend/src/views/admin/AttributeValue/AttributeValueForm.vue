<template>
  <div class="container py-5">
    <h1 class="text-center mb-5 text-success">
      {{ isEditMode ? 'Cập nhật' : 'Thêm mới' }} Giá trị cho "{{ attributeName }}"
    </h1>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-success text-white">
        <h2 class="h5 mb-0">Thông tin Giá trị</h2>
      </div>
      <div class="card-body">
        <form @submit.prevent="saveAttributeValue">
          <div class="mb-3">
            <label for="attribute-value" class="form-label">Giá trị</label>
            <input
              type="text"
              id="attribute-value"
              v-model="attributeValue.value"
              placeholder="Ví dụ: Red"
              class="form-control"
              :class="{'is-invalid': errors.value}"
              required
            />
            <div v-if="errors.value" class="invalid-feedback">{{ errors.value[0] }}</div>
          </div>

          <div class="d-flex justify-content-end">
            <router-link :to="{ name: 'AttributeValueIndex', params: { attributeId: attributeId } }" class="btn btn-secondary me-2">Hủy</router-link>
            <button type="submit" class="btn btn-success">Lưu Giá trị</button>
          </div>
        </form>
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
import { ref, onMounted, computed, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
  attributeId: {
    type: [String, Number],
    required: true
  },
  valueId: { // Chỉ có khi ở chế độ chỉnh sửa giá trị
    type: [String, Number],
    default: null
  }
});

let liveToastInstance = null;

const route = useRoute();
const router = useRouter();

const attributeValue = ref({ id: null, value: '', attribute_id: props.attributeId });
const attributeName = ref('');
const errors = ref({});
const toast = ref({ show: false, message: '', type: '' });

const isEditMode = computed(() => props.valueId !== null);

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

const fetchAttributeDetails = async () => {
  if (!props.attributeId) {
    console.error("Attribute ID is undefined in AttributeValueForm. Redirecting.");
    showToast('Lỗi: Không tìm thấy ID thuộc tính cha.', 'error');
    router.push({ name: 'AttributeIndex' });
    return false; // Indicate failure
  }

  try {
    const attrResponse = await axios.get(`/admin/attributes/${props.attributeId}`);

    // --- SỬA ĐỔI PHẦN KIỂM TRA TẠI ĐÂY ---
    // API khi fetch một thuộc tính đơn lẻ sẽ luôn bọc dữ liệu trong `data.data`
    if (attrResponse.data && attrResponse.data.data && typeof attrResponse.data.data === 'object' && attrResponse.data.data.name) {
        attributeName.value = attrResponse.data.data.name; // <--- TRUY CẬP ĐÚNG CẤU TRÚC
    } else {
        // Nếu không tìm thấy thuộc tính cha hoặc dữ liệu không hợp lệ
        console.warn('API trả về dữ liệu thuộc tính cha không hợp lệ hoặc rỗng cho ID:', props.attributeId, attrResponse.data);
        showToast('Không tìm thấy thông tin thuộc tính cha. Vui lòng thử lại.', 'error');
        router.push({ name: 'AttributeIndex' }); // Điều hướng về trang danh sách thuộc tính
        return false; // Dừng hàm nếu không tìm thấy thuộc tính cha
    }
    // --- KẾT THÚC PHẦN SỬA ĐỔI ---

    return true; // Indicate success
  } catch (error) {
    console.error('Lỗi khi lấy thông tin thuộc tính cha:', error);
    if (error.response && error.response.status === 404) {
      showToast('Không tìm thấy thuộc tính cha. ID có thể không tồn tại.', 'error');
    } else {
      showToast('Có lỗi xảy ra khi tải thông tin thuộc tính cha.', 'error');
    }
    router.push({ name: 'AttributeIndex' });
    return false; // Indicate failure
  }
};

const fetchAttributeValue = async (valueId) => {
  try {
    const response = await axios.get(`/admin/attribute-values/${valueId}`);
    // Đảm bảo rằng giá trị thuộc về attributeId hiện tại
    if (response.data.data.attribute_id != props.attributeId) {
        showToast('Giá trị không thuộc về thuộc tính này.', 'error');
        router.push({ name: 'AttributeValueIndex', params: { attributeId: props.attributeId } });
        return;
    }
    attributeValue.value = response.data.data;
  } catch (error) {
    console.error('Lỗi khi lấy giá trị thuộc tính:', error);
    showToast('Lỗi khi tải dữ liệu giá trị thuộc tính.', 'error');
    router.push({ name: 'AttributeValueIndex', params: { attributeId: props.attributeId } });
  }
};

const saveAttributeValue = async () => {
  try {
    errors.value = {}; // Reset lỗi
    attributeValue.value.attribute_id = props.attributeId; // Đảm bảo đúng attribute_id
    if (isEditMode.value) {
      await axios.put(`/admin/attribute-values/${attributeValue.value.id}`, attributeValue.value);
      showToast('Giá trị đã được cập nhật thành công!');
    } else {
      await axios.post('/admin/attribute-values', attributeValue.value);
      showToast('Giá trị đã được thêm mới thành công!');
    }
    router.push({ name: 'AttributeValueIndex', params: { attributeId: props.attributeId } }); // Chuyển hướng về trang danh sách giá trị
  } catch (error) {
    console.error('Lỗi khi lưu giá trị thuộc tính:', error);
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors;
    } else if (error.response && error.response.status === 401) {
        showToast('Không được phép. Vui lòng đăng nhập.', 'error');
    } else {
      showToast('Có lỗi xảy ra khi lưu giá trị thuộc tính.', 'error');
    }
  }
};

onMounted(() => {
  fetchAttributeDetails(); // Luôn lấy tên thuộc tính cha
  if (isEditMode.value) {
    fetchAttributeValue(props.valueId);
  }
});
</script>