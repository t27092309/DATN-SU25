<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <div class="mb-6">
        <ul class="flex items-center space-x-2 text-gray-600 text-sm">
          <li class="nav-home">
            <router-link :to="{ name: 'AdminDashboard' }" class="hover:text-blue-600">
              <i class="fas fa-home"></i>
            </router-link>
          </li>
          <li class="separator">
            <i class="fas fa-chevron-right text-xs"></i>
          </li>
          <li class="nav-item">
            <span class="font-semibold">{{ $route.meta.title }}</span>
          </li>
        </ul>
      </div>
      <div class="flex flex-wrap -mx-4">
        <div class="w-full md:w-1/2 lg:w-2/5 px-4 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="card-header">
              <div class="text-2xl font-semibold mb-4">Quản lý danh mục</div>
            </div>
            <div class="card-body">
              <form @submit.prevent="addCategory" class="mb-8">
                <div class="mb-4">
                  <h5 class="text-xl font-semibold mb-4">Thêm mới danh mục</h5>
                </div>
                <div class="mb-4">
                  <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Tên danh mục</label>
                  <input type="text" v-model="category.name"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="name" placeholder="Nhập tên danh mục" required />
                  <small class="text-gray-500 text-sm mt-1 block">Ví dụ: Nước hoa nam</small>
                </div>
                <div class="flex items-center justify-between">
                  <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Thêm danh mục
                  </button>
                </div>
                <p v-if="addMessage" :class="addMessageClass" class="mt-4 text-sm">{{ addMessage }}</p>
              </form>
            </div>
          </div>
        </div>

        <div class="w-full md:w-1/2 lg:w-3/5 px-4">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full bg-white border border-gray-200">
                <thead>
                  <tr>
                    <th class="py-3 px-4 border-b text-left text-gray-600 font-bold uppercase text-sm">Tên danh mục</th>
                    <th style="width: 10%"
                      class="py-3 px-4 border-b text-left text-gray-600 font-bold uppercase text-sm">Hành động</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="category in categories" :key="category.id" class="border-b">
                    <td class="py-3 px-4">{{ category.name || '-' }}</td>
                    <td class="py-3 px-4">
                      <div class="flex space-x-2">
                        <button type="button" title="Chỉnh sửa danh mục"
                          class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                          @click="editCategory(category.id)">
                          <i class="fa fa-edit"></i>
                        </button>
                        <button type="button" title="Xóa"
                          class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                          @click="openDeleteModal(category.id)">
                          <i class="fa fa-times"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <p v-if="listMessage" :class="listMessageClass" class="mt-4 text-sm">{{ listMessage }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showDeleteModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
      <div class="relative p-6 bg-white w-96 max-w-sm mx-auto rounded-lg shadow-xl">
        <div class="flex justify-between items-start pb-3">
          <h5 class="text-xl font-bold">Xác nhận xóa</h5>
          <button type="button" class="text-gray-400 hover:text-gray-600" @click="showDeleteModal = false">
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="modal-body mb-4">
          <p>Bạn có chắc muốn xóa danh mục này?</p>
        </div>
        <div class="flex justify-end space-x-4">
          <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
            @click="showDeleteModal = false">Hủy</button>
          <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
            @click="confirmDelete">Xóa</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import slugify from 'slugify';

export default {
  name: 'CategoryManager',
  data() {
    return {
      category: {
        name: '',
      },
      categories: [],
      addMessage: '',
      addMessageClass: '',
      listMessage: '',
      listMessageClass: '',
      showDeleteModal: false,
      deleteId: null,
      apiUrl: 'http://localhost:8000/api/admin/categories',
    };
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      try {
        const response = await axios.get(this.apiUrl);
        const data = Array.isArray(response.data) ? response.data : response.data.data || [];
        this.categories = data.filter(item => item && typeof item === 'object' && item.id);
        if (!this.categories.length) {
          this.listMessage = 'Chưa có danh mục nào.';
          this.listMessageClass = 'text-info';
        } else {
          this.listMessage = '';
          this.listMessageClass = '';
        }
      } catch (error) {
        console.error('Lỗi khi lấy danh mục:', error.response?.data || error);
        this.listMessage = error.response?.data?.message || 'Có lỗi khi tải danh sách danh mục!';
        this.listMessageClass = 'text-danger';
        this.categories = [];
        if (error.response?.status === 401) {
          this.$router.push('/login');
        }
      }
    },
    async addCategory() {
      if (!this.category.name.trim()) {
        this.addMessage = 'Vui lòng nhập tên danh mục!';
        this.addMessageClass = 'text-danger';
        return;
      }

      const slug = slugify(this.category.name, {
        lower: true,
        strict: true,
        locale: 'vi',
        remove: /[*+~.()'"!:@]/g
      });

      const payload = {
        name: this.category.name.trim(),
        slug: slug,                     
      };

      try {
        const response = await axios.post(this.apiUrl, payload);
        this.addMessage = response.data.message || 'Thêm danh mục thành công!';
        this.addMessageClass = 'text-success';
        this.category.name = '';
        await this.fetchCategories();
      } catch (error) {
        console.error('Lỗi khi thêm danh mục:', error.response?.data || error);
        const errors = error.response?.data?.errors;
        this.addMessage = errors
          ? Object.values(errors).flat().join(' ')
          : error.response?.data?.message || 'Có lỗi khi thêm danh mục!';
        this.addMessageClass = 'text-danger';
        if (error.response?.status === 401) {
          this.$router.push('/login');
        }
      }
    },
    editCategory(id) {
      this.$router.push(`/categories/edit/${id}`);
    },
    openDeleteModal(id) {
      this.deleteId = id;
      this.showDeleteModal = true;
    },
    async confirmDelete() {
      try {
        const response = await axios.delete(`${this.apiUrl}/${this.deleteId}`);
        this.listMessage = response.data.message || 'Xóa danh mục thành công!';
        this.listMessageClass = 'text-success';
        this.showDeleteModal = false;
        this.deleteId = null;
        await this.fetchCategories();
      } catch (error) {
        console.error('Lỗi khi xóa danh mục:', error.response?.data || error);
        this.listMessage = error.response?.data?.message || 'Có lỗi khi xóa danh mục!';
        this.listMessageClass = 'text-danger';
        this.showDeleteModal = false;
        if (error.response?.status === 401) {
          this.$router.push('/login');
        }
      }
    },
  },
};
</script>