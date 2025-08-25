<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Cập Nhật Hồ Sơ</h2>

    <div v-if="loading" class="text-gray-500">Đang tải...</div>
    <div v-else>
      <form @submit.prevent="updateProfile" class="flex flex-col lg:flex-row">

        <!-- Form thông tin -->
        <div class="lg:w-2/3 lg:pr-8">
          <!-- Tên -->
          <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
            <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Tên</label>
            <input v-model="form.name" class="flex-1 border-b border-gray-300 py-2 focus:outline-none" required />
          </div>

          <!-- SĐT -->
          <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
            <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Số điện thoại</label>
            <input v-model="form.phone_number" class="flex-1 border-b border-gray-300 py-2 focus:outline-none" />
          </div>

          <!-- Email -->
          <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
            <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Email</label>
            <input v-model="form.email" type="email" class="flex-1 border-b border-gray-300 py-2 focus:outline-none" />
          </div>

          <!-- Giới tính -->
          <div class="mb-4 flex flex-col sm:flex-row sm:items-center">
            <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Giới tính</label>
            <select v-model="form.gender" class="flex-1 border-b border-gray-300 py-2 focus:outline-none">
              <option value="">Chọn giới tính</option>
              <option value="male">Nam</option>
              <option value="female">Nữ</option>
              <option value="other">Khác</option>
            </select>
          </div>

          <!-- Ngày sinh -->
          <div class="mb-6 flex flex-col sm:flex-row sm:items-center">
            <label class="w-full sm:w-32 text-gray-600 mb-1 sm:mb-0">Ngày sinh</label>
            <input type="date" v-model="form.birthday"
              class="flex-1 border-b border-gray-300 py-2 focus:outline-none" />
          </div>

          <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-blue-700">
            Cập nhật
          </button>
        </div>

        <!-- Avatar -->
        <div
          class="lg:w-1/3 flex flex-col items-center justify-center lg:border-l lg:border-gray-200 lg:pl-8 mt-8 lg:mt-0 pt-8 lg:pt-0 border-t lg:border-t-0 border-gray-200">
          <div
            class="w-32 h-32 rounded-full overflow-hidden mb-4 border border-gray-300 flex items-center justify-center">
            <img
              :src="avatarPreview || (user.avatar ? `http://localhost:8000${user.avatar}?t=${timestamp}` : 'https://via.placeholder.com/128')"
              alt="Profile Avatar" class="w-full h-full object-cover" />
          </div>
          <input type="file" @change="onFileChange" accept="image/*" class="mt-2" />
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const user = ref({})
const form = ref({
  name: '',
  phone_number: '',
  email: '',
  gender: '',
  birthday: ''
})
const avatarFile = ref(null)
const avatarPreview = ref(null)
const loading = ref(true)
const timestamp = ref(Date.now()) // để reload ảnh tránh cache

const loadUser = async () => {
  try {
    const res = await axios.get('/user/profile', { withCredentials: true })
    user.value = res.data
    form.value.name = res.data.name || ''
    form.value.phone_number = res.data.phone_number || ''
    form.value.email = res.data.email || ''
    form.value.gender = res.data.gender || ''
    form.value.birthday = res.data.birthday || ''
  } catch (err) {
    console.error('Lấy dữ liệu user thất bại:', err)
  } finally {
    loading.value = false
  }
}

const onFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    avatarFile.value = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

const updateProfile = async () => {
  const data = new FormData()
  data.append('name', form.value.name)
  data.append('phone_number', form.value.phone_number)
  data.append('email', form.value.email)
  data.append('gender', form.value.gender)
  data.append('birthday', form.value.birthday)
  if (avatarFile.value) {
    data.append('avatar', avatarFile.value)
  }

  try {
    const res = await axios.post('/user/profile/update', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
      withCredentials: true
    })
    user.value = res.data
    avatarPreview.value = null
    timestamp.value = Date.now() // cập nhật để reload ảnh
    alert('Cập nhật hồ sơ thành công!')
  } catch (err) {
    console.error('Cập nhật thất bại:', err)
    alert('Cập nhật hồ sơ thất bại!')
  }
}

onMounted(loadUser)
</script>

<style scoped>
/* Import Tailwind nếu cần */
@import '@/assets/tailwind.css';
</style>
