<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Chỉnh sửa danh mục sản phẩm</h3>
        <ul class="breadcrumbs mb-3">
          <li class="nav-home">
            <router-link to="/admin/danh-muc"><i class="icon-home"></i></router-link>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
          <li class="nav-item">
            <router-link to="/admin/danh-muc">Quản lý danh mục</router-link>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
          <li class="nav-item">
            <span>Chỉnh sửa</span>
          </li>
        </ul>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title">Chỉnh sửa danh mục</div>
            </div>
            <div class="card-body">
              <form @submit.prevent="updateCategory" class="mb-5">
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
                <div class="form-group">
                  <label for="slug">Đường dẫn tĩnh</label>
                  <input
                    type="text"
                    v-model="category.slug"
                    class="form-control"
                    id="slug"
                    placeholder="Nhập đường dẫn tĩnh"
                    required
                  />
                </div>
                <div class="card-action">
                  <button type="submit" class="btn btn-primary">Cập nhật danh mục</button>
                  <router-link to="/admin/danh-muc" class="btn btn-secondary ms-2">Hủy</router-link>
                </div>
                <p v-if="message" :class="messageClass">{{ message }}</p>
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
          this.$router.push('/admin/danh-muc');
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
.btn-secondary {
  padding: 10px 20px;
  background-color: #6c757d;
  color: white;
  border: none;
  border-radius: 4px;
  text-decoration: none;
}
.btn-secondary:hover {
  background-color: #5a6268;
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
</style>