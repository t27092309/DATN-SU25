<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Các danh mục sản phẩm</h3>
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
                      <h5 class="card-title">Thêm mới danh mục sản phẩm</h5>
                    </div>
                    <div class="form-group">
                      <label for="name">Tên</label>
                      <input
                        type="text"
                        v-model="category.name"
                        class="form-control"
                        id="name"
                        placeholder="Nhập tên danh mục"
                        required
                      />
                      <small id="emailHelp2" class="form-text text-muted"
                        >Tên thuộc tính (được hiển thị ngoài frontend)</small
                      >
                    </div>
                    <div class="form-group">
                      <label for="slug">Đường dẫn tĩnh</label>
                      <input
                        type="text"
                        v-model="category.slug"
                        class="form-control"
                        id="slug"
                        placeholder="Nhập đường dẫn tĩnh"
                      />
                    </div>
                    <div class="form-group">
                      <label for="description">Mô tả</label>
                      <textarea
                        v-model="category.description"
                        class="form-control"
                        id="description"
                        rows="5"
                        placeholder="Nhập mô tả danh mục"
                      ></textarea>
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
                    <table id="add-row" class="display table table-bordered">
                      <thead>
                        <tr>
                          <th>Tên</th>
                          <th>Đường dẫn tĩnh</th>
                          <th>Mô tả</th>
                          <th style="width: 10%">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="category in categories" :key="category.id">
                          <td>{{ category.name }}</td>
                          <td>{{ category.slug }}</td>
                          <td>{{ category.description || 'Không có mô tả' }}</td>
                          <td>
                            <div class="form-button-action">
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title="Edit Task"
                                class="btn btn-link btn-primary btn-lg"
                                @click="editCategory(category.id)"
                              >
                                <i class="fa fa-edit"></i>
                              </button>
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title="Remove"
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
        slug: '',
        description: '',
      },
      categories: [],
      addMessage: '',
      addMessageClass: '',
      listMessage: '',
      listMessageClass: '',
      showDeleteModal: false,
      deleteId: null,
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
  mounted() {
    this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      try {
        const response = await axios.get('http://localhost:8000/api/categories');
        this.categories = response.data;
      } catch (error) {
        this.listMessage = error.response?.data?.message || 'Có lỗi khi tải danh sách danh mục!';
        this.listMessageClass = 'text-danger';
      }
    },
    async addCategory() {
      if (!this.category.name || !this.category.slug) {
        this.addMessage = 'Vui lòng nhập tên và đường dẫn tĩnh!';
        this.addMessageClass = 'text-danger';
        return;
      }
      try {
        const response = await axios.post('http://localhost:8000/api/categories', {
          name: this.category.name,
          slug: this.category.slug,
          description: this.category.description,
        });
        this.categories.push(response.data.category);
        this.addMessage = response.data.message;
        this.addMessageClass = 'text-success';
        this.category.name = '';
        this.category.slug = '';
        this.category.description = '';
      } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors) {
          this.addMessage = Object.values(errors).flat().join(' ');
        } else {
          this.addMessage = error.response?.data?.message || 'Có lỗi xảy ra khi thêm danh mục!';
        }
        this.addMessageClass = 'text-danger';
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
        const response = await axios.delete(`http://localhost:8000/api/categories/${this.deleteId}`);
        this.categories = this.categories.filter(category => category.id !== this.deleteId);
        this.listMessage = response.data.message;
        this.listMessageClass = 'text-success';
        this.showDeleteModal = false;
        this.deleteId = null;
      } catch (error) {
        this.listMessage = error.response?.data?.message || 'Có lỗi khi xóa danh mục!';
        this.listMessageClass = 'text-danger';
        this.showDeleteModal = false;
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