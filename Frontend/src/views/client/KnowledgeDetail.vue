<template>
  <div class="container mx-auto px-4 py-12">
    <div v-if="article" class="bg-white p-6 rounded-lg shadow-md">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ article.title }}</h1>
      <div class="flex items-center text-gray-600 text-sm mb-6">
        <span>Đăng ngày: {{ formatDate(new Date()) }}</span>
        <span class="mx-2">•</span>
        <span>Đọc: 1,234 lượt</span>
      </div>
      <img :src="article.image" alt="Article Image" class="w-full h-124 object-cover rounded-lg mb-6" />
      <div class="prose text-gray-700 leading-relaxed">
        <p>{{ article.content }}</p>
        <p class="mt-4">
          Nước hoa không chỉ là một sản phẩm, mà còn là một phần của câu chuyện cá nhân. Khi chọn một mùi hương, bạn không chỉ chọn một mùi thơm mà còn chọn cách bạn muốn thế giới nhìn nhận bạn. Hãy thử nghiệm với các nốt hương khác nhau để tìm ra sự kết hợp hoàn hảo nhất!
        </p>
        <h2 class="text-xl font-semibold mt-6 mb-2">Mẹo Chọn Nước Hoa</h2>
        <ul class="list-disc pl-5 space-y-2">
          <li>Thử nước hoa vào buổi sáng khi da sạch để cảm nhận chính xác.</li>
          <li>Lưu ý đến nốt hương đầu, giữa và cuối để hiểu rõ vòng đời của mùi hương.</li>
          <li>Bảo quản ở nơi khô ráo, tránh ánh nắng trực tiếp.</li>
        </ul>
        <p class="mt-4">
          Với sự phát triển không ngừng của ngành công nghiệp nước hoa, năm 2025 hứa hẹn sẽ mang đến nhiều xu hướng mới. Hãy theo dõi chúng tôi để cập nhật những thông tin mới nhất!
        </p>
      </div>
      <router-link to="/knowledge" class="mt-6 inline-block text-red-600 hover:underline text-sm font-medium">Quay lại Kiến Thức</router-link>
    </div>
    <div v-else class="text-center text-gray-700 py-12">
      <p>Bài viết không tồn tại hoặc đang được cập nhật.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

// Dữ liệu giả lập cho các bài viết
const articles = ref([
  {
    id: 1,
    title: 'Cách Chọn Nước Hoa Phù Hợp Với Tính Cách',
    summary: 'Tìm hiểu cách mùi hương phản ánh cá tính của bạn và chọn nước hoa lý tưởng.',
    image: 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80',
    content: 'Việc chọn nước hoa phù hợp với tính cách là một nghệ thuật. Mỗi mùi hương mang một câu chuyện riêng, từ hương gỗ ấm áp đến hương hoa tươi mát. Hãy dành thời gian để thử nghiệm và khám phá bản thân qua từng giọt nước hoa.'
  },
  {
    id: 2,
    title: 'Hướng Dẫn Bảo Quản Nước Hoa Đúng Cách',
    summary: 'Những mẹo đơn giản để giữ cho nước hoa của bạn luôn tươi mới và bền lâu.',
    image: 'https://res.cloudinary.com/dfhextjvl/image/upload/v1756116028/OIP_1_efjzjn.webp',
    content: 'Bảo quản nước hoa đúng cách giúp duy trì hương thơm nguyên vẹn. Tránh để nước hoa tiếp xúc với ánh nắng mặt trời hoặc nhiệt độ cao, và luôn đậy kín nắp sau khi sử dụng.'
  },
  {
    id: 3,
    title: 'Top 10 Mùi Hương Được Yêu Thích Năm 2025',
    summary: 'Khám phá những xu hướng nước hoa hot nhất trong năm nay theo thời gian thực.',
    image: 'https://thafd.bing.com/th/id/OIP.VNSwdbIv-23vqpmkUyUkuQHaEJ?w=293&h=181&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3',
    content: 'Năm 2025 chứng kiến sự bùng nổ của các mùi hương mới như hương vani kết hợp với gỗ tuyết tùng và các nốt hương cam chanh tươi sáng. Đây là thời điểm lý tưởng để bạn cập nhật tủ nước hoa của mình!'
  }
]);

const route = useRoute();
const article = ref(null);

// Lấy bài viết dựa trên id từ route
onMounted(() => {
  const articleId = parseInt(route.params.id);
  const foundArticle = articles.value.find((a) => a.id === articleId);
  if (foundArticle) {
    article.value = foundArticle;
  }
});

// Định dạng ngày hiện tại
const formatDate = (date) => {
  return date.toLocaleDateString('vi-VN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};
</script>

<style scoped>
.container {
  max-width: 1280px;
}
.prose p {
  margin-bottom: 1rem;
}
.prose h2 {
  color: #4b5563;
}
</style>