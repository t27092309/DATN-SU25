<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <div class="mb-6">
        <h3 class="text-3xl font-bold mb-3">Chỉnh sửa danh mục sản phẩm</h3>
        <ul class="flex items-center space-x-2 text-gray-600 text-sm">
          <li class="nav-home">
            <router-link to="/admin/categories" class="hover:text-blue-600">
              <i class="fas fa-home"></i>
            </router-link>
          </li>
          <li class="separator">
            <i class="fas fa-chevron-right text-xs"></i>
          </li>
          <li class="nav-item">
            <router-link to="/admin/categories" class="hover:text-blue-600">Quản lý danh mục</router-link>
          </li>
          <li class="separator">
            <i class="fas fa-chevron-right text-xs"></i>
          </li>
          <li class="nav-item">
            <span class="text-blue-600">Chỉnh sửa</span>
          </li>
        </ul>
      </div>
      <div class="flex flex-wrap -mx-4">
        <div class="w-full px-4">
          <div class="bg-white shadow-md rounded-lg p-6">
            <div class="card-header mb-4">
              <div class="card-title text-xl font-semibold">Chỉnh sửa danh mục</div>
            </div>
            <div class="card-body">
              <form @submit.prevent="updateCategory" class="mb-5">
                <div class="mb-4">
                  <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Tên danh mục</label>
                  <input type="text" v-model="category.name"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="name" placeholder="Nhập tên danh mục" required />
                  <small class="text-gray-500 text-xs mt-1">Ví dụ: Nước hoa nam</small>
                </div>
                <div class="mb-4">
                  <label for="slug" class="block text-gray-700 text-sm font-bold mb-2">Đường dẫn tĩnh</label>
                  <input type="text" v-model="category.slug"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="slug" placeholder="Nhập đường dẫn tĩnh" required />
                </div>
                <div class="flex items-center mt-6">
                  <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Cập
                    nhật danh mục</button>
                  <router-link to="/admin/categories"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline ml-2">Hủy</router-link>
                </div>
                <p v-if="message" :class="messageClass" class="mt-4 text-sm">{{ message }}</p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import slugify from 'slugify';

export default {
  name: 'EditCategory',
  data() {
    return {
      category: {
        name: '',
        slug: '',
      },
      message: '',
      messageClass: '',
      apiUrl: 'http://localhost:8000/api/admin/categories',
    };
  },
  watch: {
    'category.name'(newName) {
      this.category.slug = slugify(newName, {
        lower: true,
        strict: true,
        locale: 'vi',
      });
    },
  },
  async mounted() {
    await this.fetchCategory();
  },
  methods: {
    async fetchCategory() {
      try {
        const response = await axios.get(`${this.apiUrl}/${this.$route.params.id}`);
        const data = response.data.data || response.data;
        this.category = {
          name: data.name || '',
          slug: data.slug || '',
        };
      } catch (error) {
        console.error('Lỗi khi tải thông tin danh mục:', error.response?.data || error);
        this.message = error.response?.data?.message || 'Có lỗi khi tải thông tin danh mục!';
        this.messageClass = 'text-danger';
      }
    },
    async updateCategory() {
      if (!this.category.name || !this.category.slug) {
        this.message = 'Vui lòng nhập tên và đường dẫn tĩnh!';
        this.messageClass = 'text-danger';
        return;
      }
      try {
        const response = await axios.put(`${this.apiUrl}/${this.$route.params.id}`, {
          name: this.category.name,
          slug: this.category.slug,
        });
        this.message = response.data.message || 'Cập nhật danh mục thành công!';
        this.messageClass = 'text-success';
        setTimeout(() => {
          this.$router.push('/admin/categories');
        }, 1000);
      } catch (error) {
        console.error('Lỗi khi cập nhật danh mục:', error.response?.data || error);
        const errors = error.response?.data?.errors;
        this.message = errors
          ? Object.values(errors).flat().join(' ')
          : error.response?.data?.message || 'Có lỗi khi cập nhật danh mục!';
        this.messageClass = 'text-danger';
      }
    },
  },
};
</script>