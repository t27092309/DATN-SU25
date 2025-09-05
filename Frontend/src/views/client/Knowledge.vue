<template>
  <div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Kiến Thức Về Nước Hoa</h1>
    <p class="text-gray-700 mb-8">
      Khám phá những thông tin mới nhất về nước hoa, cách chọn mùi hương phù hợp, và xu hướng năm 2025. Cập nhật ngày: {{ formatDate(new Date()) }}.
    </p>

    <!-- Danh sách bài viết -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
      <div v-for="article in articles" :key="article.id" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
        <img :src="article.image" alt="Article Image" class="w-full h-48 object-cover rounded-t-lg mb-4" />
        <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ article.title }}</h2>
        <p class="text-gray-700 text-sm mb-4">{{ article.summary }}</p>
        <router-link :to="`/knowledge/${article.id}`" class="text-red-600 hover:underline text-sm font-medium">Đọc thêm</router-link>
      </div>
    </div>

    <!-- Gợi ý thêm -->
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">Bạn Muốn Biết Thêm?</h2>
      <p class="text-gray-700 mb-4">
        Nếu bạn có câu hỏi hoặc muốn chúng tôi viết về chủ đề cụ thể, hãy gửi gợi ý qua biểu mẫu liên hệ!
      </p>
      <router-link to="/contact" class="inline-block bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition-colors">
        Liên Hệ Ngay
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Dữ liệu giả lập cho các bài viết
const articles = ref([
  {
    id: 1,
    title: 'Cách Chọn Nước Hoa Phù Hợp Với Tính Cách',
    summary: 'Tìm hiểu cách mùi hương phản ánh cá tính của bạn và chọn nước hoa lý tưởng trong năm 2025.',
    image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80'
  },
  {
    id: 2,
    title: 'Hướng Dẫn Bảo Quản Nước Hoa Đúng Cách',
    summary: 'Những mẹo đơn giản để giữ cho nước hoa của bạn luôn tươi mới và bền lâu trong mùa hè 2025.',
    image: 'https://res.cloudinary.com/dfhextjvl/image/upload/v1756116028/OIP_1_efjzjn.webp'
  },
  {
    id: 3,
    title: 'Top 10 Mùi Hương Được Yêu Thích Tháng 8/2025',
    summary: 'Khám phá những xu hướng nước hoa hot nhất trong tháng 8 năm 2025 theo thời gian thực.',
    image: 'https://thafd.bing.com/th/id/OIP.VNSwdbIv-23vqpmkUyUkuQHaEJ?w=293&h=181&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3'
  }
]);

// Định dạng ngày hiện tại
const formatDate = (date) => {
  return date.toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
.container {
  max-width: 1280px;
}
</style>