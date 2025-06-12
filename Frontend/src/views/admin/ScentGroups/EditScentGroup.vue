```vue
<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Chỉnh sửa nhóm hương</h3>
        <ul class="breadcrumbs mb-3">
          <li class="nav-home">
            <router-link to="/admin/nhom-huong"><i class="icon-home"></i></router-link>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
          <li class="nav-item">
            <router-link to="/admin/nhom-huong">Quản lý nhóm hương</router-link>
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
              <div class="card-title">Chỉnh sửa nhóm hương</div>
            </div>
            <div class="card-body">
              <form @submit.prevent="updateScentGroup" class="mb-5">
                <div class="form-group">
                  <label for="name">Tên nhóm hương</label>
                  <input
                    type="text"
                    v-model="scentGroup.name"
                    class="form-control"
                    id="name"
                    placeholder="Nhập tên nhóm hương"
                    required
                  />
                  <small class="form-text text-muted">Ví dụ: Hương hoa, Hương gỗ</small>
                </div>
                <div class="form-group">
                  <label for="color_code">Mã màu</label>
                  <input
                    type="color"
                    v-model="scentGroup.color_code"
                    class="form-control color-picker"
                    id="color_code"
                    required
                  />
                  <small class="form-text text-muted">Chọn màu từ bảng màu</small>
                </div>
                <div class="card-action">
                  <button type="submit" class="btn btn-primary">Cập nhật nhóm hương</button>
                  <router-link to="/admin/nhom-huong" class="btn btn-secondary ms-2">Hủy</router-link>
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
  name: 'EditScentGroup',
  data() {
    return {
      scentGroup: {
        name: '',
        color_code: '#000000', // Giá trị mặc định cho color picker
      },
      message: '',
      messageClass: '',
    };
  },
  watch: {
    'scentGroup.name'(newName) {
      this.scentGroup.name = slugify(newName, {
        lower: true,
        strict: true,
        locale: 'vi',
      });
    },
  },
  async mounted() {
    await this.fetchScentGroup();
  },
  methods: {
    async fetchScentGroup() {
      try {
        const response = await axios.get(`http://localhost:8000/api/scent-groups/${this.$route.params.id}`);
        this.scentGroup = {
          name: response.data.name,
          color_code: response.data.color_code || '#000000', // Đảm bảo color_code hợp lệ
        };
      } catch (error) {
        this.message = error.response?.data?.message || 'Có lỗi khi tải thông tin nhóm hương!';
        this.messageClass = 'text-danger';
      }
    },
    async updateScentGroup() {
      if (!this.scentGroup.name || !this.scentGroup.color_code) {
        this.message = 'Vui lòng nhập tên nhóm hương và chọn màu!';
        this.messageClass = 'text-danger';
        return;
      }
      try {
        const response = await axios.put(`http://localhost:8000/api/scent-groups/${this.$route.params.id}`, {
          name: this.scentGroup.name,
          color_code: this.scentGroup.color_code,
        });
        this.message = response.data.message || 'Cập nhật nhóm hương thành công!';
        this.messageClass = 'text-success';
        setTimeout(() => {
          this.$router.push('/admin/nhom-huong');
        }, 1000);
      } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors) {
          this.message = Object.values(errors).flat().join(' ');
        } else {
          this.message = error.response?.data?.message || 'Có lỗi khi cập nhật nhóm hương!';
        }
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
.color-picker {
  height: 40px; /* Tăng chiều cao để dễ chọn màu */
  padding: 5px;
  cursor: pointer;
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