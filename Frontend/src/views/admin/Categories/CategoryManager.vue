<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <!-- breadcrumb -->
      <div class="mb-6">
        <ul class="flex items-center space-x-2 text-gray-600 text-sm">
          <li class="nav-home">
            <router-link
              :to="{ name: 'AdminDashboard' }"
              class="hover:text-blue-600"
            >
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

      <!-- form + list -->
      <div class="flex flex-wrap -mx-4">
        <!-- form thêm -->
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
                  <label
                    for="name"
                    class="block text-gray-700 text-sm font-bold mb-2"
                  >
                    Tên danh mục
                  </label>
                  <input
                    type="text"
                    v-model="category.name"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    id="name"
                    placeholder="Nhập tên danh mục"
                    required
                  />
                  <small class="text-gray-500 text-sm mt-1 block"
                    >Ví dụ: Nước hoa nam</small
                  >
                </div>
                <div class="flex items-center justify-between">
                  <button
                    type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                  >
                    Thêm danh mục
                  </button>
                </div>
                <p
                  v-if="addMessage"
                  :class="addMessageClass"
                  class="mt-4 text-sm"
                >
                  {{ addMessage }}
                </p>
              </form>
            </div>
          </div>
        </div>

        <!-- danh sách -->
        <div class="w-full md:w-1/2 lg:w-3/5 px-4">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between mb-4">
              <h3 class="text-xl font-semibold">Danh sách danh mục</h3>
              <button
                @click="showTrash = !showTrash"
                class="bg-gray-500 hover:bg-gray-700 text-white px-3 py-1 rounded"
              >
                {{ showTrash ? "Ẩn thùng rác" : "Xem thùng rác" }}
              </button>
            </div>

            <!-- danh mục đang hoạt động -->
            <div v-if="!showTrash" class="overflow-x-auto">
              <table class="min-w-full bg-white border border-gray-200">
                <thead>
                  <tr>
                    <th
                      class="py-3 px-4 border-b text-left text-gray-600 font-bold uppercase text-sm"
                    >
                      Tên danh mục
                    </th>
                    <th
                      style="width: 10%"
                      class="py-3 px-4 border-b text-left text-gray-600 font-bold uppercase text-sm"
                    >
                      Hành động
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="category in categories"
                    :key="category.id"
                    class="border-b"
                  >
                    <td class="py-3 px-4">{{ category.name || "-" }}</td>
                    <td class="py-3 px-4">
                      <div class="flex space-x-2">
                        <button
                          type="button"
                          title="Chỉnh sửa danh mục"
                          class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                          @click="editCategory(category.id)"
                        >
                          <i class="fa fa-edit"></i>
                        </button>
                        <button
                          type="button"
                          title="Xóa"
                          class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                          @click="openDeleteModal(category.id)"
                        >
                          <i class="fa fa-times"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <p
                v-if="listMessage"
                :class="listMessageClass"
                class="mt-4 text-sm"
              >
                {{ listMessage }}
              </p>
            </div>

            <!-- thùng rác -->
            <div v-else class="overflow-x-auto">
              <table class="min-w-full bg-white border border-gray-200">
                <thead>
                  <tr>
                    <th
                      class="py-3 px-4 border-b text-left text-gray-600 font-bold uppercase text-sm"
                    >
                      Tên danh mục (đã xóa)
                    </th>
                    <th
                      style="width: 20%"
                      class="py-3 px-4 border-b text-left text-gray-600 font-bold uppercase text-sm"
                    >
                      Hành động
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="category in trashCategories"
                    :key="category.id"
                    class="border-b"
                  >
                    <td class="py-3 px-4">{{ category.name || "-" }}</td>
                    <td class="py-3 px-4">
                      <div class="flex space-x-2">
                        <button
                          type="button"
                          title="Khôi phục"
                          class="text-green-500 hover:text-green-700 p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                          @click="restoreCategory(category.id)"
                        >
                          <i class="fa fa-undo"></i>
                        </button>
                        <button
                          type="button"
                          title="Xóa vĩnh viễn"
                          class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-gray-100 transition-colors duration-200"
                          @click="forceDelete(category.id)"
                        >
                          <i class="fa fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              <p
                v-if="trashMessage"
                :class="trashMessageClass"
                class="mt-4 text-sm"
              >
                {{ trashMessage }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- modal xác nhận xóa -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center"
    >
      <div
        class="relative p-6 bg-white w-96 max-w-sm mx-auto rounded-lg shadow-xl"
      >
        <div class="flex justify-between items-start pb-3">
          <h5 class="text-xl font-bold">Xác nhận xóa</h5>
          <button
            type="button"
            class="text-gray-400 hover:text-gray-600"
            @click="showDeleteModal = false"
          >
            <span class="sr-only">Close</span>
            <svg
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
        <div class="modal-body mb-4">
          <p>Bạn có chắc muốn xóa danh mục này?</p>
        </div>
        <div class="flex justify-end space-x-4">
          <button
            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
            @click="showDeleteModal = false"
          >
            Hủy
          </button>
          <button
            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
            @click="confirmDelete"
          >
            Xóa
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import slugify from "slugify";

export default {
  name: "CategoryManager",
  data() {
    return {
      category: { name: "" },
      categories: [],
      trashCategories: [],
      showTrash: false,

      addMessage: "",
      addMessageClass: "",
      listMessage: "",
      listMessageClass: "",
      trashMessage: "",
      trashMessageClass: "",

      showDeleteModal: false,
      deleteId: null,
      apiUrl: "http://localhost:8000/api/admin/categories",
    };
  },
  mounted() {
    this.fetchCategories();
    this.fetchTrash();
  },
  methods: {
    async fetchCategories() {
      try {
        const response = await axios.get(this.apiUrl);
        this.categories = response.data.data || [];
        this.listMessage = this.categories.length
          ? ""
          : "Chưa có danh mục nào.";
        this.listMessageClass = this.categories.length
          ? ""
          : "text-red-600 bg-red-100 border border-red-400 p-2 rounded";
      } catch (error) {
        this.listMessage =
          error.response?.data?.message || "Có lỗi khi tải danh sách danh mục!";
        this.listMessageClass =
          "text-red-600 bg-red-100 border border-red-400 p-2 rounded";
      }
    },
    async fetchTrash() {
      try {
        const response = await axios.get(this.apiUrl + "/trashed");
        this.trashCategories = response.data.data || [];
        this.trashMessage = this.trashCategories.length
          ? ""
          : "Thùng rác trống.";
        this.trashMessageClass = this.trashCategories.length
          ? ""
          : "text-gray-600 bg-gray-100 border border-gray-300 p-2 rounded";
      } catch (error) {
        this.trashMessage =
          error.response?.data?.message || "Có lỗi khi tải thùng rác!";
        this.trashMessageClass =
          "text-red-600 bg-red-100 border border-red-400 p-2 rounded";
      }
    },
    async addCategory() {
      if (!this.category.name.trim()) {
        this.addMessage = "Vui lòng nhập tên danh mục!";
        this.addMessageClass =
          "text-red-600 bg-red-100 border border-red-400 p-2 rounded";
        return;
      }
      const slug = slugify(this.category.name, {
        lower: true,
        strict: true,
        locale: "vi",
        remove: /[*+~.()'"!:@]/g,
      });
      try {
        const response = await axios.post(this.apiUrl, {
          name: this.category.name.trim(),
          slug,
        });
        this.addMessage = response.data.message || "Thêm danh mục thành công!";
        this.addMessageClass =
          "text-green-600 bg-green-100 border border-green-400 p-2 rounded";
        this.category.name = "";
        await this.fetchCategories();
      } catch (error) {
        this.addMessage =
          error.response?.data?.message || "Có lỗi khi thêm danh mục!";
        this.addMessageClass =
          "text-red-600 bg-red-100 border border-red-400 p-2 rounded";
      }
    },
    editCategory(id) {
      this.$router.push(`/admin/categories/edit/${id}`);
    },
    openDeleteModal(id) {
      this.deleteId = id;
      this.showDeleteModal = true;
    },
    async confirmDelete() {
      try {
        const response = await axios.delete(`${this.apiUrl}/${this.deleteId}`);
        this.listMessage = response.data.message || "Xóa danh mục thành công!";
        this.listMessageClass =
          "text-green-600 bg-green-100 border border-green-400 p-2 rounded";
        this.showDeleteModal = false;
        await this.fetchCategories();
        await this.fetchTrash();
      } catch (error) {
        this.listMessage =
          error.response?.data?.message || "Có lỗi khi xóa danh mục!";
        this.listMessageClass =
          "text-red-600 bg-red-100 border border-red-400 p-2 rounded";
        this.showDeleteModal = false;
      }
    },
    async restoreCategory(id) {
      try {
        const response = await axios.put(`${this.apiUrl}/${id}/restore`);
        this.trashMessage =
          response.data.message || "Khôi phục danh mục thành công!";
        this.trashMessageClass =
          "text-green-600 bg-green-100 border border-green-400 p-2 rounded";
        await this.fetchCategories();
        await this.fetchTrash();
      } catch (error) {
        this.trashMessage =
          error.response?.data?.message || "Có lỗi khi khôi phục!";
        this.trashMessageClass =
          "text-red-600 bg-red-100 border border-red-400 p-2 rounded";
      }
    },
    async forceDelete(id) {
      try {
        const response = await axios.delete(`${this.apiUrl}/${id}/force`);
        this.trashMessage =
          response.data.message || "Xóa vĩnh viễn thành công!";
        this.trashMessageClass =
          "text-green-600 bg-green-100 border border-green-400 p-2 rounded";
        await this.fetchTrash();
      } catch (error) {
        this.trashMessage =
          error.response?.data?.message || "Có lỗi khi xóa vĩnh viễn!";
        this.trashMessageClass =
          "text-red-600 bg-red-100 border border-red-400 p-2 rounded";
      }
    },
  },
};
</script>
