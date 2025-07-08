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
                <div class="row">
                  <div class="col-md-6 col-lg-4">
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
                        min="0"
                      />
                      <small class="form-text text-muted"
                        >Ví dụ: 20 (cho 20%) hoặc 100000 (cho 100,000 VNĐ)</small
                      >
                    </div>
                  </div>

                  <div class="col-md-6 col-lg-4">
                    <div class="form-group">
                      <label for="min_order_amount">Giá trị đơn hàng tối thiểu</label>
                      <input
                        type="number"
                        v-model="coupon.min_order_amount"
                        class="form-control"
                        id="min_order_amount"
                        placeholder="Để trống nếu không có"
                        min="0"
                      />
                      <small class="form-text text-muted"
                        >Đơn hàng phải đạt giá trị này để áp dụng mã.</small
                      >
                    </div>
                    <div class="form-group">
                      <label for="max_discount">Giảm tối đa (chỉ áp dụng cho %)</label>
                      <input
                        type="number"
                        v-model="coupon.max_discount"
                        class="form-control"
                        id="max_discount"
                        placeholder="Để trống nếu không giới hạn"
                        min="0"
                      />
                      <small class="form-text text-muted"
                        >Giới hạn số tiền giảm tối đa (chỉ cho loại phần trăm).</small
                      >
                    </div>
                    <div class="form-group">
                      <label for="usage_limit">Tổng số lượt dùng tối đa</label>
                      <input
                        type="number"
                        v-model="coupon.usage_limit"
                        class="form-control"
                        id="usage_limit"
                        placeholder="Để trống nếu không giới hạn"
                        min="1"
                      />
                      <small class="form-text text-muted"
                        >Tổng số lần mã này có thể được sử dụng.</small
                      >
                    </div>
                  </div>

                  <div class="col-md-6 col-lg-4">
                    <div class="form-group">
                      <label for="per_user_limit">Số lượt dùng cho mỗi người dùng</label>
                      <input
                        type="number"
                        v-model="coupon.per_user_limit"
                        class="form-control"
                        id="per_user_limit"
                        placeholder="Để trống nếu không giới hạn"
                        min="1"
                      />
                      <small class="form-text text-muted"
                        >Số lần mỗi người dùng có thể sử dụng mã này.</small
                      >
                    </div>
                    <div class="form-group">
                      <label for="start_date">Ngày bắt đầu hiệu lực</label>
                      <input
                        type="datetime-local"
                        v-model="coupon.start_date"
                        class="form-control"
                        id="start_date"
                      />
                      <small class="form-text text-muted"
                        >Mã giảm giá sẽ có hiệu lực từ ngày này.</small
                      >
                    </div>
                    <div class="form-group">
                      <label for="end_date">Ngày hết hạn hiệu lực</label>
                      <input
                        type="datetime-local"
                        v-model="coupon.end_date"
                        class="form-control"
                        id="end_date"
                      />
                      <small class="form-text text-muted"
                        >Mã giảm giá sẽ hết hiệu lực vào ngày này.</small
                      >
                    </div>
                  </div>
                </div> <div class="card-action mt-3">
                  <button type="submit" class="btn btn-primary">
                    <i class="fa fa-sync-alt me-2"></i> Cập nhật mã giảm giá
                  </button>
                  <router-link to="/admin/ma-giam-gia" class="btn btn-secondary ms-2">
                    <i class="fa fa-times-circle me-2"></i> Hủy
                  </router-link>
                </div>
                <p v-if="message" :class="messageClass" class="mt-3">{{ message }}</p>
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
        min_order_amount: '', // New
        max_discount: '',    // New
        start_date: '',      // New (renamed from expires_at logic, but represents start)
        end_date: '',        // New (renamed from expires_at)
        usage_limit: '',     // New
        per_user_limit: '',  // New
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
        const couponData = response.data;

        // Helper function to format date for datetime-local input
        const formatDateTimeLocal = (dateString) => {
          if (!dateString) return '';
          const date = new Date(dateString);
          date.setMinutes(date.getMinutes() - date.getTimezoneOffset()); // Adjust for timezone offset
          return date.toISOString().slice(0, 16);
        };

        this.coupon = {
          code: couponData.code,
          discount_type: couponData.discount_type,
          discount_value: couponData.discount_value,
          min_order_amount: couponData.min_order_amount || '', // Populate, use '' for null
          max_discount: couponData.max_discount || '',         // Populate, use '' for null
          start_date: formatDateTimeLocal(couponData.start_date), // Populate
          end_date: formatDateTimeLocal(couponData.end_date),     // Populate
          usage_limit: couponData.usage_limit || '',           // Populate, use '' for null
          per_user_limit: couponData.per_user_limit || '',     // Populate, use '' for null
        };
      } catch (error) {
        this.message = error.response?.data?.message || 'Có lỗi khi tải thông tin mã giảm giá!';
        this.messageClass = 'text-danger';
        console.error("Error fetching coupon:", error);
      }
    },
    async updateCoupon() {
      if (!this.coupon.code || this.coupon.discount_value === '' || this.coupon.discount_value < 0) {
        this.message = 'Vui lòng nhập mã và giá trị giảm giá hợp lệ!';
        this.messageClass = 'text-danger';
        return;
      }

      // Client-side validation for dates
      if (this.coupon.start_date && this.coupon.end_date && new Date(this.coupon.start_date) > new Date(this.coupon.end_date)) {
        this.message = 'Ngày bắt đầu không thể lớn hơn ngày kết thúc!';
        this.messageClass = 'text-danger';
        return;
      }

      try {
        const payload = {
          code: this.coupon.code,
          discount_type: this.coupon.discount_type,
          discount_value: parseFloat(this.coupon.discount_value),
          min_order_amount: this.coupon.min_order_amount !== '' ? parseFloat(this.coupon.min_order_amount) : null,
          max_discount: this.coupon.max_discount !== '' ? parseFloat(this.coupon.max_discount) : null,
          start_date: this.coupon.start_date || null,
          end_date: this.coupon.end_date || null, // Renamed from expires_at
          usage_limit: this.coupon.usage_limit !== '' ? parseInt(this.coupon.usage_limit) : null,
          per_user_limit: this.coupon.per_user_limit !== '' ? parseInt(this.coupon.per_user_limit) : null,
        };

        const response = await axios.put(`http://localhost:8000/api/admin/coupons/${this.$route.params.id}`, payload);
        this.message = response.data.message || 'Cập nhật mã giảm giá thành công!';
        this.messageClass = 'text-success';
        setTimeout(() => {
          this.$router.push('/admin/ma-giam-gia');
        }, 1000);
      } catch (error) {
        console.error("Error updating coupon:", error.response);
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
/* Keep existing styles, add any new ones as needed */
.container {
  max-width: 1200px;
  margin: 50px auto;
}
.form-group {
  margin-bottom: 1rem; /* Adjusted for consistency with CouponManager */
}
.form-control {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ced4da;
  border-radius: 4px;
}
.form-text {
  font-size: 0.875em; /* Adjusted for consistency */
  color: #6c757d;
  margin-top: 0.25rem;
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
  display: inline-flex; /* Ensure icon and text are aligned */
  align-items: center;
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
.ms-2 {
    margin-left: 0.5rem; /* For spacing between buttons */
}
.me-2 {
    margin-right: 0.5rem; /* For spacing between icon and text */
}
</style>