<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Danh mục</h3>
        <ul class="breadcrumbs mb-3">
          <li class="nav-home">
            <a href="#"><i class="icon-home"></i></a>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
        </ul>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title">Quản lý danh mục</div>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Form thêm danh mục -->
                <div class="col-md-6 col-lg-4">
                  <form @submit.prevent="addCategory" class="mb-5">
                    <div class="form-group">
                      <h5 class="card-title">Thêm mới danh mục</h5>
                    </div>
                    <div class="form-group">
                      <label for="name">Tên danh mục</label>
                      <input
                        type="text"
                        v-model="category.name"
                        class="form-control"
                        id="name"
                        placeholder="Nhập tên danh mục"
                        required
                      />
                      <small class="form-text text-muted">Ví dụ: Nước hoa nam</small>
                    </div>
                    <div class="card-action">
                      <button type="submit" class="btn btn-primary">Thêm danh mục</button>
                    </div>
                    <p v-if="addMessage" :class="addMessageClass">{{ addMessage }}</p>
                  </form>
                </div>

                <!-- Bảng hiển thị danh mục -->
                <div class="col-md-6 col-lg-8">
                  <div class="table-responsive">
                    <table class="display table table-bordered">
                      <thead>
                        <tr>
                          <th>Tên danh mục</th>
                          <th style="width: 10%">Hành động</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="category in categories" :key="category.id">
                          <td>{{ category.name || '-' }}</td>
                          <td>
                            <div class="form-button-action">
                              <button
                                type="button"
                                title="Chỉnh sửa danh mục"
                                class="btn btn-link btn-primary btn-lg"
                                @click="editCategory(category.id)"
                              >
                                <i class="fa fa-edit"></i>
                              </button>
                              <button
                                type="button"
                                title="Xóa"
                                class="btn btn-link btn-danger"
                                @click="openDeleteModal(category.id)"
                              >
                                <i class="fa fa-times"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <p v-if="listMessage" :class="listMessageClass">{{ listMessage }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal xác nhận xóa -->
    <div v-if="showDeleteModal" class="modal fade show" style="display: block">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Xác nhận xóa</h5>
            <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
          </div>
          <div class="modal-body">
            <p>Bạn có chắc muốn xóa danh mục này?</p>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="showDeleteModal = false">Hủy</button>
            <button class="btn btn-danger" @click="confirmDelete">Xóa</button>
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
  watch: {
    'category.name'(newName) {
      this.category.name = slugify(newName, {
        lower: true,
        strict: true,
        locale: 'vi',
      });
    },
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
      if (!this.category.name) {
        this.addMessage = 'Vui lòng nhập tên danh mục!';
        this.addMessageClass = 'text-danger';
        return;
      }
      const payload = {
        name: this.category.name,
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

<style scoped>
.container {
  max-width: 1200px;
  margin: 50px auto;
}
.form-group {
  margin-bottom: 20px;
}
.form-control {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ced4da;
  border-radius: 4px;
}
.form-text {
  font-size: 14px;
}
.btn-primary {
  padding: 10px 20px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.btn-primary:hover {
  background-color: #0056b3;
}
.btn-link.btn-primary {
  color: #007bff;
}
.btn-link.btn-primary:hover {
  color: #0056b3;
}
.btn-link.btn-danger {
  color: #dc3545;
}
.btn-link.btn-danger:hover {
  color: #b02a37;
}
.form-button-action {
  display: flex;
  gap: 10px;
}
.table {
  width: 100%;
  border-collapse: collapse;
}
.table th,
.table td {
  padding: 12px;
  text-align: left;
}
.table th {
  background-color: #f8f9fa;
}
.text-success {
  color: green;
  margin-top: 15px;
}
.text-danger {
  color: red;
  margin-top: 15px;
}
.text-info {
  color: #17a2b8;
  margin-top: 15px;
}
.mb-5 {
  margin-bottom: 3rem;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.breadcrumbs {
  display: flex;
  list-style: none;
  padding: 0;
}
.breadcrumbs li {
  margin-right: 10px;
}
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
.modal-dialog {
  margin: 100px auto;
}
.btn-close {
  background: none;
  border: none;
  font-size: 1.2rem;
}
</style>