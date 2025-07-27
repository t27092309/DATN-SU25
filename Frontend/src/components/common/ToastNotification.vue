<template>
  <div
    id="liveToast"
    class="min-w-[300px] p-6 rounded-xl shadow-2xl text-white transition-all duration-500 transform ease-out"
    :class="{
      'opacity-100 translate-y-0': show,
      'opacity-0 translate-y-full': !show,
      'bg-green-600': type === 'success',
      'bg-red-600': type === 'error'
    }"
    role="alert"
    aria-live="assertive"
    aria-atomic="true"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <svg v-if="type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <svg v-else-if="type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2A9 9 0 111 12a9 9 0 0118 0z" />
        </svg>
        <span class="text-lg font-semibold">{{ message }}</span>
      </div>
      <button type="button" @click="$emit('close')" class="ml-4 text-white hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-white rounded-md p-1">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  message: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'success', // 'success' or 'error'
  },
});

defineEmits(['close']);
</script>

<style scoped>
/* Custom animations for toasts */
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