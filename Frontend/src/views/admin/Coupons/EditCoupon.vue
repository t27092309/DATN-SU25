<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Chỉnh sửa mã giảm giá</h3>
        <ul class="breadcrumbs mb-3">
          <li class="nav-home">
            <router-link to="/admin/ma-giam-gia"><i class="icon-home"></i></router-link>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
          <li class="nav-item">
            <router-link to="/admin/ma-giam-gia">Quản lý mã giảm giá</router-link>
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
              <div class="card-title">Chỉnh sửa mã giảm giá</div>
            </div>
            <div class="card-body">
              <form @submit.prevent="updateCoupon" class="mb-5">
                <div class="form-group">
                  <label for="code">Mã giảm giá</label>
                  <input
                    type="text"
                    v-model="coupon.code"
                    class="form-control"
                    id="code"
                    placeholder="Nhập mã giảm giá"
                    required
                  />
                  <small class="form-text text-muted"
                    >Mã giảm giá (ví dụ: SALE2025)</small
                  >
                </div>
                <div class="form-group">
                  <label for="discount_type">Loại giảm giá</label>
                  <select
                    v-model="coupon.discount_type"
                    class="form-control"
                    id="discount_type"
                    required
                  >
                    <option value="percent">Phần trăm (%)</option>
                    <option value="fixed">Cố định (VNĐ)</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="discount_value">Giá trị giảm</label>
                  <input
                    type="number"
                    v-model="coupon.discount_value"
                    class="form-control"
                    id="discount_value"
                    placeholder="Nhập giá trị giảm"
                    required
                  />
                  <small class="form-text text-muted"
                    >Ví dụ: 20 (cho 20%) hoặc 100000 (cho 100,000 VNĐ)</small
                  >
                </div>
                <div class="form-group">
                  <label for="expires_at">Ngày hết hạn</label>
                  <input
                    type="datetime-local"
                    v-model="coupon.expires_at"
                    class="form-control"
                    id="expires_at"
                  />
                </div>
                <div class="card-action">
                  <button type="submit" class="btn btn-primary">Cập nhật mã giảm giá</button>
                  <router-link to="/admin/ma-giam-gia" class="btn btn-secondary ms-2">Hủy</router-link>
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
  name: 'EditCoupon',
  data() {
    return {
      coupon: {
        code: '',
        discount_type: 'percent',
        discount_value: '',
        expires_at: '',
      },
      message: '',
      messageClass: '',
    };
  },
  watch: {
    'coupon.code'(newCode) {
      this.coupon.code = slugify(newCode, {
        lower: true,
        strict: true,
        locale: 'vi',
      });
    },
  },
  async mounted() {
    await this.fetchCoupon();
  },
  methods: {
    async fetchCoupon() {
      try {
        const response = await axios.get(`http://localhost:8000/api/admin/coupons/${this.$route.params.id}`);
        this.coupon = {
          code: response.data.code,
          discount_type: response.data.discount_type,
          discount_value: response.data.discount_value,
          expires_at: response.data.expires_at ? new Date(response.data.expires_at).toISOString().slice(0, 16) : '',
        };
      } catch (error) {
        this.message = error.response?.data?.message || 'Có lỗi khi tải thông tin mã giảm giá!';
        this.messageClass = 'text-danger';
      }
    },
    async updateCoupon() {
      if (!this.coupon.code || !this.coupon.discount_value) {
        this.message = 'Vui lòng nhập mã và giá trị giảm giá!';
        this.messageClass = 'text-danger';
        return;
      }
      try {
        const response = await axios.put(`http://localhost:8000/api/admin/coupons/${this.$route.params.id}`, {
          code: this.coupon.code,
          discount_type: this.coupon.discount_type,
          discount_value: this.coupon.discount_value,
          expires_at: this.coupon.expires_at || null,
        });
        this.message = response.data.message;
        this.messageClass = 'text-success';
        setTimeout(() => {
          this.$router.push('/admin/ma-giam-gia');
        }, 1000);
      } catch (error) {
        const errors = error.response?.data?.errors;
        if (errors) {
          this.message = Object.values(errors).flat().join(' ');
        } else {
          this.message = error.response?.data?.message || 'Có lỗi khi cập nhật mã giảm giá!';
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