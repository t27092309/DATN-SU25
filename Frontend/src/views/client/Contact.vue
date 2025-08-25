<template>
  <div class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Liên Hệ Với Chúng Tôi</h1>
    <p class="text-gray-700 mb-8">
      Chúng tôi rất sẵn lòng hỗ trợ bạn. Vui lòng điền thông tin vào biểu mẫu dưới đây hoặc liên hệ trực tiếp qua các kênh sau:
    </p>

    <!-- Thông tin liên hệ -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Thông Tin Liên Hệ</h2>
        <ul class="space-y-3 text-gray-700">
          <li>
            <strong>Điện thoại:</strong> 028 999 999 42 | 0977 591 562
          </li>
          <li>
            <strong>Email:</strong>
            <a href="mailto:cskh@florea.vn" class="text-red-600 hover:underline">cskh@florea.vn</a>
          </li>
          <li>
            <strong>Giờ làm việc:</strong> 08:00 - 22:00 (Tất cả các ngày)
          </li>
          <li>
            <strong>Địa chỉ:</strong> 123 Đường Xuân Phương, Quận Nam Từ Liêm, TP. Hà Nội
          </li>
        </ul>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Kết Nối Với Chúng Tôi</h2>
        <div class="flex space-x-4">
          <a href="https://www.facebook.com/profile.php?id=61557016340628" target="_blank" class="text-gray-400 hover:text-red-600">
            <img src="https://www.facebook.com/images/fb_icon_325x325.png" alt="Facebook" class="w-8 h-8 rounded-full" />
          </a>
          <a href="https://youtu.be/6tOrgDPhz8o?si=ICXxRAORr-vJ4drr" target="_blank" class="text-gray-400 hover:text-red-600">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/09/YouTube_full-color_icon_%282017%29.svg/1024px-YouTube_full-color_icon_%282017%29.svg.png" alt="YouTube" class="w-8 h-8 rounded-full" />
          </a>
          <a href="#" class="text-gray-400 hover:text-red-600">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e7/Instagram_logo_2016.svg/768px-Instagram_logo_2016.svg.png" alt="Instagram" class="w-8 h-8 rounded-full" />
          </a>
          <a href="#" class="text-gray-400 hover:text-red-600">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Icon_of_Zalo.svg/50px-Icon_of_Zalo.svg.png" alt="Zalo" class="w-8 h-8 rounded-full" />
          </a>
        </div>
      </div>
    </div>

    <!-- Biểu mẫu liên hệ -->
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold text-gray-900 mb-4">Gửi Tin Nhắn Cho Chúng Tôi</h2>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Họ và Tên</label>
          <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" required />
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" type="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" required />
        </div>
        <div>
          <label for="subject" class="block text-sm font-medium text-gray-700">Tiêu Đề</label>
          <input v-model="form.subject" type="text" id="subject" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" required />
        </div>
        <div>
          <label for="message" class="block text-sm font-medium text-gray-700">Nội Dung</label>
          <textarea v-model="form.message" id="message" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500" required></textarea>
        </div>
        <button type="submit" class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
          Gửi Tin Nhắn
        </button>
      </form>
      <p v-if="submitMessage" class="mt-4 text-sm text-green-600">{{ submitMessage }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// State
const form = ref({
  name: '',
  email: '',
  subject: '',
  message: ''
});
const submitMessage = ref('');

// Hàm xử lý gửi biểu mẫu (giả lập)
const handleSubmit = () => {
  // Giả lập gửi thành công
  submitMessage.value = 'Cảm ơn bạn đã liên hệ với chúng tôi, chúng tôi sẽ trả lời bạn sớm nhất có thể.';
  // Reset form sau 3 giây
  setTimeout(() => {
    submitMessage.value = '';
    form.value = { name: '', email: '', subject: '', message: '' };
  }, 3000);
};
</script>

<style scoped>
.client-root {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
</style>