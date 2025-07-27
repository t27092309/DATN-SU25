<script setup>
import { ref, watch } from 'vue';

// 1. Định nghĩa props: Cần nhận prop là 'attribute' chứ không phải 'currentAttribute'
//    và đảm bảo có giá trị mặc định hoặc kiểu an toàn.
const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  attribute: { // <--- Đổi tên prop từ 'currentAttribute' thành 'attribute' nếu bạn đã đặt sai
    type: Object,
    default: () => ({ id: null, name: '' }), // Cung cấp giá trị mặc định an toàn
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['close', 'save']);

// Sử dụng một biến cục bộ để làm việc với dữ liệu form
// Điều này giúp tránh thay đổi trực tiếp props, là một anti-pattern trong Vue
const formAttribute = ref({ id: null, name: '' });

// 2. Sử dụng watch để đồng bộ prop 'attribute' với biến cục bộ 'formAttribute'
//    Mỗi khi prop 'attribute' thay đổi (ví dụ: khi mở modal để sửa), cập nhật formAttribute.
watch(() => props.attribute, (newVal) => {
  formAttribute.value = { ...newVal }; // Sao chép giá trị để không sửa trực tiếp prop
}, { immediate: true, deep: true }); // 'immediate' để chạy lần đầu khi component mount, 'deep' cho nested objects

const saveForm = () => {
  emit('save', formAttribute.value);
};

const closeForm = () => {
  emit('close');
};
</script>

<template>
  <div v-if="props.show" class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl p-8 max-w-lg w-full transform transition-all duration-300 scale-100 opacity-100">
      <div class="flex justify-between items-center mb-6 border-b pb-4">
        <h3 class="text-2xl font-semibold text-gray-800">
          {{ formAttribute.id ? 'Sửa Thuộc tính' : 'Thêm Thuộc tính Mới' }}
        </h3>
        <button @click="closeForm" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form @submit.prevent="saveForm">
        <div class="mb-4">
          <label for="attributeName" class="block text-sm font-medium text-gray-700 mb-2">Tên thuộc tính</label>
          <input
            type="text"
            id="attributeName"
            v-model="formAttribute.name"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            placeholder="Ví dụ: Màu sắc, Kích thước"
          />
          <p v-if="props.errors.name" class="mt-1 text-sm text-red-600">{{ props.errors.name[0] }}</p>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            type="button"
            @click="closeForm"
            class="inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Hủy
          </button>
          <button
            type="submit"
            class="inline-flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            {{ formAttribute.id ? 'Cập nhật' : 'Thêm mới' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
/* Có thể thêm style riêng nếu cần, nhưng Tailwind CSS đã xử lý phần lớn */
</style>