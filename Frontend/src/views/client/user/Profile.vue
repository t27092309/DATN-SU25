<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const user = ref({
  name: '',
  email: '',
  phone_number: '',
  gender: '',
  birthday: '',
  avatar: ''
})
const loading = ref(true)
const error = ref('')

const loadUser = async () => {
  try {
    const res = await axios.get('/user/profile', { withCredentials: true });
    user.value = res.data;
  } catch (err) {
    console.error(err)
    error.value = 'Không thể load dữ liệu user.'
  } finally {
    loading.value = false
  }
}

onMounted(loadUser)
</script>

<template>
  <div>
    <h1 class="text-2xl font-semibold text-gray-800 mb-4">Hồ Sơ Của Tôi</h1>

    <div v-if="loading" class="text-gray-500">Đang tải thông tin...</div>
    <div v-else-if="error" class="text-red-500">{{ error }}</div>
    <div v-else class="flex flex-col lg:flex-row">
      <div class="lg:w-2/3 lg:pr-8">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Tên</label>
          <input type="text" :value="user.name" class="flex-1 border-b border-gray-300 py-2 focus:outline-none"
            readonly />
        </div>

        <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Email</label>
          <input type="text" :value="user.email" class="flex-1 border-b border-gray-300 py-2 focus:outline-none"
            readonly />
        </div>

        <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Số điện thoại</label>
          <input type="text" :value="user.phone_number || 'Chưa cập nhật'"
            class="flex-1 border-b border-gray-300 py-2 focus:outline-none" readonly />
        </div>

        <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Giới tính</label>
          <input type="text" :value="user.gender === 'male' ? 'Nam' : user.gender === 'female' ? 'Nữ' : 'Khác'"
            class="flex-1 border-b border-gray-300 py-2 focus:outline-none" readonly />
        </div>

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center">
          <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Ngày sinh</label>
          <input type="text" :value="user.birthday || 'Chưa cập nhật'"
            class="flex-1 border-b border-gray-300 py-2 focus:outline-none" readonly />
        </div>

        <router-link to="/tai-khoan/thong-tin-ca-nhan"
          class="px-4 py-2 bg-red-500 text-white rounded hover:bg-blue-600">
          Chỉnh sửa hồ sơ
        </router-link>
      </div>

      <div
        class="lg:w-1/3 flex flex-col items-center justify-center lg:border-l lg:border-gray-200 lg:pl-8 mt-8 lg:mt-0 pt-8 lg:pt-0 border-t lg:border-t-0 border-gray-200">
        <div
          class="w-32 h-32 rounded-full overflow-hidden mb-4 border border-gray-300 flex items-center justify-center">
          <img :src="user.avatar ? `http://localhost:8000${user.avatar}` : 'https://via.placeholder.com/128'"
            alt="Profile Avatar" class="w-full h-full object-cover" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import '@/assets/tailwind.css';
</style>
