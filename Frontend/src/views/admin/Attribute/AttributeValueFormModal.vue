<template>
  <div v-if="show" class="fixed inset-0 bg-gray-900 bg-opacity-70 flex items-center justify-center p-4 z-50 animate-fade-in">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-auto transform transition-all scale-100 opacity-100 animate-slide-up">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50 rounded-t-xl">
        <h5 class="text-2xl font-semibold text-gray-800">{{ editedAttributeValue.id ? 'Cập nhật' : 'Thêm mới' }} Giá trị cho {{ attributeName ? `"${attributeName}"` : 'thuộc tính' }}</h5>
        <button type="button" @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <form @submit.prevent="submitForm">
        <div class="p-6">
          <div class="mb-4">
            <label for="modal-attribute-value" class="block text-gray-700 text-lg font-semibold mb-3">Giá trị</label>
            <input
              type="text"
              id="modal-attribute-value"
              v-model="editedAttributeValue.value"
              placeholder="Ví dụ: Red"
              class="shadow-sm appearance-none border rounded-lg w-full py-3 px-4 text-gray-800 leading-tight focus:outline-none focus:ring-3 focus:ring-emerald-200 focus:border-emerald-500 transition-all duration-200 text-lg"
              :class="{'border-red-500 ring-1 ring-red-300': errors.value}"
              required
            />
            <p v-if="errors.value" class="text-red-600 text-sm italic mt-2">{{ errors.value[0] }}</p>
          </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-4 bg-gray-50 rounded-b-xl">
          <button type="button" @click="$emit('close')" class="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors duration-200 text-base font-medium shadow-md">Hủy</button>
          <button type="submit" class="px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors duration-200 text-base font-medium shadow-md">Lưu</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  attributeValue: {
    type: Object,
    required: true,
  },
  attributeName: {
    type: String,
    default: 'thuộc tính đã chọn'
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['close', 'save']);

const editedAttributeValue = ref({ id: null, value: '', attribute_id: null });

watch(
  () => props.attributeValue,
  (newVal) => {
    // Đảm bảo tạo bản sao sâu (deep copy) nếu attributeValue có các object lồng nhau
    // Đối với object phẳng như thế này, spread operator là đủ:
    editedAttributeValue.value = { ...newVal };
  },
  { immediate: true }
);

const submitForm = () => {
  emit('save', editedAttributeValue.value);
};
</script>

<style scoped>
/* Custom animations for modals */
@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}
.animate-fade-in {
  animation: fade-in 0.3s ease-out forwards;
}

@keyframes slide-up {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up {
  animation: slide-up 0.3s ease-out forwards;
}
</style>