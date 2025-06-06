<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Các mã giảm giá</h3>
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
              <div class="card-title">Quản lý mã giảm giá</div>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Form thêm coupon -->
                <div class="col-md-6 col-lg-4">
                  <form @submit.prevent="addCoupon" class="mb-5">
                    <div class="form-group">
                      <h5 class="card-title">Thêm mới mã giảm giá</h5>
                    </div>
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
                        <option value="" disabled>Chọn loại giảm giá</option>
                        <option value="percent ">Phần trăm (%)</option>
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
                      <button type="submit" class="btn btn-primary">Thêm mã giảm giá</button>
                    </div>
                    <p v-if="addMessage" :class="addMessageClass">{{ addMessage }}</p>
                  </form>
                </div>

                <!-- Bảng hiển thị coupons -->
                <div class="col-md-6 col-lg-8">
                  <div class="table-responsive">
                    <table id="add-row" class="display table table-bordered">
                      <thead>
                        <tr>
                          <th>Mã</th>
                          <th>Loại giảm giá</th>
                          <th>Giá trị</th>
                          <th>Ngày hết hạn</th>
                          <th style="width: 10%">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="coupon in coupons" :key="coupon.id">
                          <td>{{ coupon.code }}</td>
                          <td>{{ coupon.discount_type === 'percent' ? 'Phần trăm' : 'Cố định' }}</td>
                          <td>{{ coupon.discount_value }} {{ coupon.discount === 'percentage' ? '%' : 'VNĐ' }}</td>
                          <td>{{ coupon.expires_at ? new Date(coupon.expires_at).toLocaleString() : 'Không có' }}</td>
                          <td>
                            <div class="form-button-action">
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title="Edit Coupon"
                                class="btn btn-link btn-primary btn-lg"
                                @click="editCoupon(coupon.id)"
                              >
                                <i class="fa fa-edit"></i>
                              </button>
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title="Remove"
                                class="btn btn-link btn-danger"
                                @click="openDeleteModal(coupon.id)"
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
            <p>Bạn có chắc muốn xóa mã giảm giá này?</p>
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
  name: 'CouponManager',
  data() {
    return {
      coupon: {
        code: '',
        discount_type: 'percent',
        discount_value: '',
        expires_at: '',
      },
      coupons: [],
      addMessage: '',
      addMessageClass: '',
      listMessage: '',
      listMessageClass: '',
      showDeleteModal: false,
      deleteId: null,
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
  mounted() {
    this.fetchCoupons();
  },
  methods: {
    async fetchCoupons() {
      try {
        const response = await axios.get('http://localhost:8000/api/coupons');
        this.coupons = response.data;
      } catch (error) {
        this.listMessage = error.response?.data?.message || 'Có lỗi khi tải danh sách mã giảm giá!';
        this.listMessageClass = 'text-danger';
      }
    },
    async addCoupon() {
      if (!this.coupon.code || !this.coupon.discount_type || !this.coupon.discount_value) {
        this.addMessage = 'Vui lòng nhập mã, loại giảm giá và giá trị giảm giá!';
        this.addMessageClass = 'text-danger';
        return;
      }
      console.log('Payload:', {
        code: this.coupon.code,
        discount_type: this.coupon.discount_type,
        discount_value: this.coupon.discount_value,
        expires_at: this.coupon.expires_at || null,
      });
      try {
        const response = await axios.post('http://localhost:8000/api/coupons', {
          code: this.coupon.code,
          discount_type: this.coupon.discount_type,
          discount_value: this.coupon.discount_value,
          expires_at: this.coupon.expires_at || null,
        });
        this.coupons.push(response.data.coupon);
        this.addMessage = response.data.message;
        this.addMessageClass = 'text-success';
        this.coupon.code = '';
        this.coupon.discount_type = 'percent';
        this.coupon.discount_value = '';
        this.coupon.expires_at = '';
      } catch (error) {
        console.error('Error:', error.response);
        const errors = error.response?.data?.errors;
        if (errors) {
          this.addMessage = Object.values(errors).flat().join(' ');
        } else {
          this.addMessage = error.response?.data?.message || 'Có lỗi khi thêm mã giảm giá!';
        }
        this.addMessageClass = 'text-danger';
      }
    },
    editCoupon(id) {
      this.$router.push(`/coupons/edit/${id}`);
    },
    openDeleteModal(id) {
      this.deleteId = id;
      this.showDeleteModal = true;
    },
    async confirmDelete() {
      try {
        const response = await axios.delete(`http://localhost:8000/api/coupons/${this.deleteId}`);
        this.coupons = this.coupons.filter(coupon => coupon.id !== this.deleteId);
        this.listMessage = response.data.message;
        this.listMessageClass = 'text-success';
        this.showDeleteModal = false;
        this.deleteId = null;
      } catch (error) {
        this.listMessage = error.response?.data?.message || 'Có lỗi khi xóa mã giảm giá!';
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