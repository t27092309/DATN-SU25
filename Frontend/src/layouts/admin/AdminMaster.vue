<template>
  <div class="flex h-screen bg-gray-100">
    <Sidebar />

    <div class="flex flex-col flex-1 ml-[265px]">
      <Header />

      <main class="flex-1 p-6 overflow-y-auto pt-16">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from './Sidebar.vue'; // Sử dụng alias nếu có
import Header from './Header.vue';   // Sử dụng alias nếu có
</script>

<style scoped>
/* Scoped styles specific to AdminLayout if any */
</style>