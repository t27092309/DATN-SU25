<template>
  <div v-if="sortedAttributeValues.length === 0" class="text-center text-gray-500 py-16 text-xl">
    <p class="mb-4">Chưa có giá trị nào cho thuộc tính này.</p>
    <p>Hãy thêm giá trị mới!</p>
  </div>
  <ul v-else class="divide-y divide-gray-200">
    <li
      v-for="val in sortedAttributeValues"
      :key="val.id"
      class="py-5 px-4 -mx-4 flex justify-between items-center hover:bg-gray-50 transition-colors duration-200 rounded-lg"
    >
      <span class="font-bold text-gray-800 text-lg">{{ val.value }}</span>
      <div class="flex items-center space-x-3">
        <button
          @click="$emit('edit-value', val)"
          class="p-3 bg-indigo-100 text-indigo-700 rounded-full hover:bg-indigo-200 transition-all duration-200 text-sm font-medium flex items-center justify-center shadow-sm hover:shadow-md"
          title="Sửa"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
          </svg>
        </button>
        <button
          @click="$emit('delete-value', val.id)"
          class="p-3 bg-red-100 text-red-700 rounded-full hover:bg-red-200 transition-all duration-200 text-sm font-medium flex items-center justify-center shadow-sm hover:shadow-md"
          title="Xóa"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </li>
  </ul>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';

const props = defineProps({
  attributeValues: {
    type: Array,
    required: true,
  },
});

defineEmits(['edit-value', 'delete-value']);

// Hàm để tách số từ chuỗi (ví dụ: "1ml" -> 1, "25ml" -> 25)
const extractNumber = (str) => {
  const match = str.match(/\d+(\.\d+)?/);
  return match ? parseFloat(match[0]) : Infinity;
};

// Sử dụng computed property để sắp xếp mảng
const sortedAttributeValues = computed(() => {
  // Tạo một bản sao để tránh thay đổi prop trực tiếp
  return [...props.attributeValues].sort((a, b) => {
    const numA = extractNumber(a.value);
    const numB = extractNumber(b.value);
    return numA - numB;
  });
});
</script>