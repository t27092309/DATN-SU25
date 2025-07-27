<template>
  <div class="container mx-auto py-12 px-4 sm:px-6 lg:px-8 max-w-7xl">
    <h1 class="text-center mb-12 text-4xl font-extrabold text-gray-800 sm:text-5xl tracking-tight">
      Quản lý <span class="text-purple-600">Thuộc tính</span> & <span class="text-emerald-600">Giá trị</span>
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div class="h-full flex flex-col">
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 flex flex-col flex-grow">
          <div
            class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-6 rounded-t-2xl flex justify-between items-center">
            <h2 class="text-2xl font-semibold flex items-center gap-3">
              Danh sách Thuộc tính
            </h2>
            <button @click="openAttributeModal()"
              class="px-6 py-2 bg-white text-purple-700 rounded-lg hover:bg-gray-100 hover:shadow-lg transition-all duration-300 text-base font-medium flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Thêm Thuộc tính
            </button>
          </div>
          <div class="p-8 flex-grow overflow-y-auto min-h-[300px] lg:min-h-[500px]">
            <AttributeList :attributes="attributes" :selectedAttribute="selectedAttribute"
              @select-attribute="handleSelectAttribute" @edit-attribute="openAttributeModal"
              @delete-attribute="deleteAttribute" />
            <div v-if="totalPages > 1" class="flex justify-between items-center mt-6">
              <button @click="changeAttributePage(currentPage - 1)" :disabled="currentPage === 1"
                class="px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                Trang trước
              </button>
              <span class="text-gray-700">Trang {{ currentPage }} / {{ totalPages }}</span>
              <button @click="changeAttributePage(currentPage + 1)" :disabled="currentPage === totalPages"
                class="px-4 py-2 bg-purple-500 text-white rounded-md hover:bg-purple-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                Trang sau
              </button>
            </div>
            <div v-else-if="attributes.length === 0" class="text-center text-gray-500 py-4">
              Không tìm thấy thuộc tính nào.
            </div>
          </div>
        </div>
      </div>

      <div class="h-full flex flex-col">
        <div class="bg-white shadow-xl rounded-2xl border border-gray-100 flex flex-col flex-grow">
          <div
            class="bg-gradient-to-r from-emerald-600 to-green-600 text-white p-6 rounded-t-2xl flex justify-between items-center">
            <h2 class="text-2xl font-semibold flex items-center gap-3">
              <span v-if="selectedAttribute">Giá trị của "{{ selectedAttribute.name }}"</span>
              <span v-else>Giá trị thuộc tính</span>
            </h2>
            <button @click="openValueModal()"
              class="px-6 py-2 bg-white text-emerald-700 rounded-lg hover:bg-gray-100 hover:shadow-lg transition-all duration-300 text-base font-medium flex items-center gap-2"
              :disabled="!selectedAttribute" :class="{ 'opacity-50 cursor-not-allowed': !selectedAttribute }">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Thêm Giá trị
            </button>
          </div>
          <div class="p-8 flex-grow overflow-y-auto min-h-[300px] lg:min-h-[500px]">
            <div v-if="isLoadingValues" class="text-center text-gray-500 py-16 text-xl">
              Đang tải giá trị...
            </div>
            <AttributeValueList v-else-if="selectedAttribute" :attributeValues="attributeValues"
              @edit-value="openValueModal" @delete-value="deleteAttributeValue" />
            <div v-else class="text-center text-gray-500 py-16 text-xl">
              Chọn một thuộc tính ở cột bên trái để xem và quản lý các giá trị của nó.
            </div>
          </div>
        </div>
      </div>
    </div>

    <AttributeFormModal :show="showAttributeModal" :attribute="currentAttribute" @close="showAttributeModal = false"
      @save="saveAttribute" :errors="attributeErrors" />

    <AttributeValueFormModal :show="showValueModal" :attributeValue="currentAttributeValue"
      :attributeName="selectedAttribute?.name" @close="showValueModal = false" @save="saveAttributeValue"
      :errors="valueErrors" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
// Import components
import AttributeList from './AttributeList.vue';
import AttributeFormModal from './AttributeFormModal.vue';
import AttributeValueList from './AttributeValueList.vue';
import AttributeValueFormModal from './AttributeValueFormModal.vue';

// ==============================================
// 1. STATE REACTIVE
// ==============================================
const attributes = ref([]);
const selectedAttribute = ref(null);
const attributeValues = ref([]);

const showAttributeModal = ref(false);
const showValueModal = ref(false);

const currentAttribute = ref({ id: null, name: '' });
const currentAttributeValue = ref({ id: null, value: '', attribute_id: null });

const attributeErrors = ref({});
const valueErrors = ref({});

const toast = useToast();

const currentPage = ref(1);
const totalPages = ref(1);
const itemsPerPage = ref(10);

// Biến trạng thái loading cho bảng giá trị
const isLoadingValues = ref(false); // <-- Thêm biến này

// ==============================================
// 3. LOGIC CHO ATTRIBUTE (Master)
// ==============================================

const fetchAttributes = async () => {
  try {
    const response = await axios.get('/admin/attributes', {
      params: {
        page: currentPage.value,
        per_page: itemsPerPage.value
      }
    });

    if (response.data && response.data.data) {
      attributes.value = response.data.data;
      if (response.data.meta) {
        totalPages.value = response.data.meta.last_page;
        currentPage.value = response.data.meta.current_page;
      } else {
        totalPages.value = response.data.last_page || 1;
        currentPage.value = response.data.current_page || 1;
      }
    } else {
      attributes.value = [];
      totalPages.value = 1;
      currentPage.value = 1;
      console.warn("API response for attributes is empty or malformed.");
    }
  } catch (error) {
    console.error('Lỗi khi lấy attributes:', error.response || error);
    if (error.response && error.response.status === 401) {
      toast.error('Phiên làm việc hết hạn. Vui lòng đăng nhập lại.');
    } else {
      toast.error('Lỗi khi tải danh sách thuộc tính.');
    }
    attributes.value = [];
    currentPage.value = 1;
    totalPages.value = 1;
  }
};

const changeAttributePage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    fetchAttributes();
  }
};

const openAttributeModal = (attr = null) => {
  attributeErrors.value = {};
  currentAttribute.value = attr ? { ...attr } : { id: null, name: '' };
  showAttributeModal.value = true;
};

const saveAttribute = async (attributeToSave) => {
  try {
    if (attributeToSave.id) {
      await axios.put(`/admin/attributes/${attributeToSave.id}`, attributeToSave);
      toast.success('Thuộc tính đã được cập nhật thành công!');
    } else {
      await axios.post('/admin/attributes', attributeToSave);
      toast.success('Thuộc tính đã được thêm mới thành công!');
    }
    showAttributeModal.value = false;
    await fetchAttributes();
    attributeErrors.value = {};
  } catch (error) {
    console.error('Lỗi khi lưu thuộc tính:', error);
    if (error.response && error.response.status === 422) {
      attributeErrors.value = error.response.data.errors;
      toast.error('Vui lòng kiểm tra lại thông tin thuộc tính.');
    } else if (error.response && error.response.status === 401) {
      toast.error('Không được phép. Vui lòng đăng nhập.');
    } else {
      toast.error('Có lỗi xảy ra khi lưu thuộc tính.');
    }
  }
};

const deleteAttribute = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa thuộc tính này không? Thao tác này sẽ xóa tất cả các giá trị và liên kết của thuộc tính này.')) {
    try {
      await axios.delete(`/admin/attributes/${id}`);
      toast.success('Thuộc tính đã được xóa thành công!');
      if (attributes.value.length === 1 && currentPage.value > 1) {
        currentPage.value--;
      }
      await fetchAttributes();
      if (selectedAttribute.value?.id === id) {
        selectedAttribute.value = null;
        attributeValues.value = [];
      }
    } catch (error) {
      console.error('Lỗi khi xóa thuộc tính:', error);
      if (error.response && error.response.status === 401) {
        toast.error('Không được phép. Vui lòng đăng nhập.');
      } else {
        toast.error('Có lỗi xảy ra khi xóa thuộc tính.');
      }
    }
  }
};

// ==============================================
// 4. LOGIC CHO ATTRIBUTE VALUE (Detail)
// ==============================================

const handleSelectAttribute = (attr) => {
  selectedAttribute.value = attr;
  fetchAttributeValues(attr.id);
};

const fetchAttributeValues = async (attributeId) => {
  isLoadingValues.value = true; // Bật loading
  try {
    const response = await axios.get(`/admin/attributes/${attributeId}/values`);
    attributeValues.value = response.data.data;
  } catch (error) {
    console.error('Lỗi khi lấy giá trị thuộc tính:', error);
    if (error.response && error.response.status === 401) {
      toast.error('Không được phép. Vui lòng đăng nhập.');
    } else {
      toast.error('Lỗi khi tải giá trị thuộc tính.');
    }
    attributeValues.value = []; // Xóa dữ liệu cũ nếu có lỗi
  } finally {
    isLoadingValues.value = false; // Tắt loading dù thành công hay thất bại
  }
};

const openValueModal = (val = null) => {
  if (!selectedAttribute.value) {
    toast.error('Vui lòng chọn một thuộc tính trước để thêm giá trị.');
    return;
  }
  valueErrors.value = {};
  currentAttributeValue.value = val
    ? { ...val }
    : { id: null, value: '', attribute_id: selectedAttribute.value.id };
  showValueModal.value = true;
};

const saveAttributeValue = async (valueToSave) => {
  try {
    if (valueToSave.id) {
      await axios.put(`/admin/attribute-values/${valueToSave.id}`, valueToSave);
      toast.success('Giá trị đã được cập nhật thành công!');
    } else {
      await axios.post('/admin/attribute-values', valueToSave);
      toast.success('Giá trị đã được thêm mới thành công!');
    }
    showValueModal.value = false;
    fetchAttributeValues(selectedAttribute.value.id);
    valueErrors.value = {};
  } catch (error) {
    console.error('Lỗi khi lưu giá trị thuộc tính:', error);
    if (error.response && error.response.status === 422) {
      valueErrors.value = error.response.data.errors;
      toast.error('Vui lòng kiểm tra lại thông tin giá trị.');
    } else if (error.response && error.response.status === 401) {
      toast.error('Không được phép. Vui lòng đăng nhập.');
    } else {
      toast.error('Có lỗi xảy ra khi lưu giá trị thuộc tính.');
    }
  }
};

const deleteAttributeValue = async (id) => {
  if (confirm('Bạn có chắc chắn muốn xóa giá trị này không?')) {
    try {
      await axios.delete(`/admin/attribute-values/${id}`);
      toast.success('Giá trị đã được xóa thành công!');
      fetchAttributeValues(selectedAttribute.value.id);
    } catch (error) {
      console.error('Lỗi khi xóa giá trị thuộc tính:', error);
      if (error.response && error.response.status === 401) {
        toast.error('Không được phép. Vui lòng đăng nhập.');
      } else {
        toast.error('Có lỗi xảy ra khi xóa giá trị thuộc tính.');
      }
    }
  }
};

// ==============================================
// 5. LIFECYCLE HOOKS
// ==============================================
onMounted(() => {
  fetchAttributes();
});
</script>