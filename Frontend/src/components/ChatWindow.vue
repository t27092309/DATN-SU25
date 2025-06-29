<template>
  <div class="fixed bottom-20 right-4 w-80 bg-white rounded-lg shadow-lg z-50 overflow-hidden">
    <div class="bg-red-500 text-white p-3 flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        <span class="font-semibold">Florea</span>
      </div>
      <div class="flex items-center space-x-3">
        <svg class="w-6 h-6 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        <svg @click="$emit('close')" class="w-6 h-6 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </div>
    </div>
    <div class="text-white bg-red-500 py-1 px-3 text-sm">Chat với chúng tôi</div>

    <div class="h-64 bg-white p-3 border-b border-gray-200 overflow-y-auto">
      <p class="text-gray-500 text-center text-sm">Chào mừng bạn đến với Florea!</p>
      </div>

    <div class="p-3 flex items-center border-t border-gray-200">
      <input type="text" placeholder="Nhập nội dung..." class="flex-grow border-none focus:outline-none focus:ring-0 text-gray-700" />
      <svg class="w-6 h-6 text-gray-400 ml-2 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
    <div class="text-right text-xs text-gray-500 p-2">CareSoft</div>
  </div>
</template>

<script setup>
// Không cần script gì nhiều ở đây cho một cửa sổ tĩnh
</script>

<style scoped>
@import '@/assets/tailwind.css';
/* Có thể thêm các style tùy chỉnh nếu Tailwind không đủ */
</style>