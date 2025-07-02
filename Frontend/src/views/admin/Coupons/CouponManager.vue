<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Quản lý mã giảm giá</h3>
        <ul class="breadcrumbs mb-3">
          <li class="nav-home">
            <a href="#"><i class="icon-home"></i></a>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
          <li class="nav-item">
            <a href="#">Mã giảm giá</a>
          </li>
        </ul>
      </div>


      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title">Danh sách mã giảm giá hiện có</div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="add-row" class="display table table-bordered">
                  <thead>
                    <tr>
                      <th>Mã</th>
                      <th>Loại</th>
                      <th>Giá trị</th>
                      <th>Giảm tối đa</th>
                      <th>Đơn tối thiểu</th>
                      <th>Ngày bắt đầu</th>
                      <th>Ngày kết thúc</th>
                      <th>Tổng lượt dùng</th>
                      <th>Lượt dùng/người</th>
                      <th style="width: 10%">Hành động</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="coupon in coupons" :key="coupon.id">
                      <td>{{ coupon.code || 'Không có' }}</td>
                      <td>{{ coupon.discount_type === 'percent' ? 'Phần trăm' : 'Cố định' }}</td>
                      <td>
                        {{ coupon.discount_value }}
                        {{ coupon.discount_type === 'percent' ? '%' : 'VNĐ' }}
                      </td>
                      <td>{{ coupon.max_discount ? coupon.max_discount.toLocaleString('vi-VN') + ' VNĐ' : 'Không có' }}</td>
                      <td>{{ coupon.min_order_amount ? coupon.min_order_amount.toLocaleString('vi-VN') + ' VNĐ' : 'Không có' }}</td>
                      <td>{{ coupon.start_date ? new Date(coupon.start_date).toLocaleString('vi-VN') : 'Ngay lập tức' }}</td>
                      <td>{{ coupon.end_date ? new Date(coupon.end_date).toLocaleString('vi-VN') : 'Không có' }}</td>
                      <td>{{ coupon.usage_limit || 'Không giới hạn' }}</td>
                      <td>{{ coupon.per_user_limit || 'Không giới hạn' }}</td>
                      <td>
                        <div class="form-button-action">
                          <button type="button" data-bs-toggle="tooltip" title="Chỉnh sửa mã giảm giá"
                            class="btn btn-link btn-primary btn-lg" @click="editCoupon(coupon.id)">
                            <i class="fa fa-edit"></i>
                          </button>
                          <button type="button" data-bs-toggle="tooltip" title="Xóa" class="btn btn-link btn-danger"
                            @click="openDeleteModal(coupon.id)">
                            <i class="fa fa-times"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="!coupons || coupons.length === 0">
                      <td colspan="10" class="text-center">Không có mã giảm giá nào</td>
                    </tr>
                  </tbody>
                </table>
                <p v-if="listMessage" :class="listMessageClass" class="mt-3">{{ listMessage }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title">Thêm mới mã giảm giá</div>
            </div>
            <div class="card-body">
              <form @submit.prevent="addCoupon">
                <div class="row">
                  <div class="col-md-6 col-lg-4">
                    <div class="form-group">
                      <label for="code">Mã giảm giá</label>
                      <input type="text" v-model="coupon.code" class="form-control" id="code"
                        placeholder="Nhập mã giảm giá (VD: SALE2025)" required />
                      <small class="form-text text-muted">Mã này sẽ được khách hàng nhập vào để áp dụng giảm giá.</small>
                    </div>
                    <div class="form-group">
                      <label for="discount_type">Loại giảm giá</label>
                      <select v-model="coupon.discount_type" class="form-control" id="discount_type" required>
                        <option value="" disabled>Chọn loại giảm giá</option>
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Cố định (VNĐ)</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="discount_value">Giá trị giảm</label>
                      <input type="number" v-model="coupon.discount_value" class="form-control" id="discount_value"
                        placeholder="Nhập giá trị giảm" required min="0"/>
                      <small class="form-text text-muted">Giá trị giảm (ví dụ: 20 cho 20% hoặc 100000 cho 100,000 VNĐ).</small>
                    </div>
                  </div>

                  <div class="col-md-6 col-lg-4">
                    <div class="form-group">
                      <label for="min_order_amount">Giá trị đơn hàng tối thiểu</label>
                      <input type="number" v-model="coupon.min_order_amount" class="form-control"
                        id="min_order_amount" placeholder="Để trống nếu không có" min="0"/>
                      <small class="form-text text-muted">Đơn hàng phải đạt giá trị này để áp dụng mã.</small>
                    </div>
                    <div class="form-group">
                      <label for="max_discount">Giảm tối đa (chỉ áp dụng cho %)</label>
                      <input type="number" v-model="coupon.max_discount" class="form-control" id="max_discount"
                        placeholder="Để trống nếu không giới hạn" min="0"/>
                      <small class="form-text text-muted">Giới hạn số tiền giảm tối đa (chỉ cho loại phần trăm).</small>
                    </div>
                    <div class="form-group">
                      <label for="usage_limit">Tổng số lượt dùng tối đa</label>
                      <input type="number" v-model="coupon.usage_limit" class="form-control" id="usage_limit"
                        placeholder="Để trống nếu không giới hạn" min="1"/>
                      <small class="form-text text-muted">Tổng số lần mã này có thể được sử dụng.</small>
                    </div>
                  </div>

                  <div class="col-md-6 col-lg-4">
                    <div class="form-group">
                      <label for="per_user_limit">Số lượt dùng cho mỗi người dùng</label>
                      <input type="number" v-model="coupon.per_user_limit" class="form-control" id="per_user_limit"
                        placeholder="Để trống nếu không giới hạn" min="1"/>
                      <small class="form-text text-muted">Số lần mỗi người dùng có thể sử dụng mã này.</small>
                    </div>
                    <div class="form-group">
                      <label for="start_date">Ngày bắt đầu hiệu lực</label>
                      <input type="datetime-local" v-model="coupon.start_date" class="form-control" id="start_date" />
                      <small class="form-text text-muted">Mã giảm giá sẽ có hiệu lực từ ngày này.</small>
                    </div>
                    <div class="form-group">
                      <label for="end_date">Ngày hết hạn hiệu lực</label>
                      <input type="datetime-local" v-model="coupon.end_date" class="form-control" id="end_date" />
                      <small class="form-text text-muted">Mã giảm giá sẽ hết hiệu lực vào ngày này.</small>
                    </div>
                  </div>
                </div> <div class="card-action mt-3">
                  <button type="submit" class="btn btn-primary">
                    <i class="fa fa-plus-circle me-2"></i> Thêm mã giảm giá
                  </button>
                  <p v-if="addMessage" :class="addMessageClass" class="mt-3">{{ addMessage }}</p>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div v-if="showDeleteModal" class="modal fade show" style="display: block; background: rgba(0, 0, 0, 0.5);">
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
      // ... your coupon data structure
      coupon: {
        code: '',
        discount_type: 'percent',
        discount_value: '',
        min_order_amount: '',
        max_discount: '',
        start_date: '',
        end_date: '',
        usage_limit: '',
        per_user_limit: '',
      },
      coupons: [],
      addMessage: '',
      addMessageClass: '',
      listMessage: '',
      listMessageClass: '',
      showDeleteModal: false,
      deleteId: null,
      dataTable: null, // Store DataTable instance
      scriptsLoaded: false, // Flag to track if external scripts are loaded
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
    // Watch for changes in coupons array to re-draw DataTable if already initialized
    coupons: {
      handler(newCoupons) {
        if (this.dataTable) {
          // Clear existing rows and add new ones
          this.dataTable.clear().rows.add(newCoupons).draw();
          jQuery('[data-bs-toggle="tooltip"]').tooltip('dispose'); // Dispose old tooltips
          jQuery('[data-bs-toggle="tooltip"]').tooltip(); // Reinitialize tooltips
        }
      },
      deep: true // Watch for deep changes in the array elements
    }
  },
  mounted() {
    this.loadScriptsAndInitializeTable();
    // Assign component instance to window for DataTables button actions
    window.vm = this;
  },
  methods: {
    async loadScriptsAndInitializeTable() {
      const scripts = [
        'https://code.jquery.com/jquery-3.7.1.min.js',
        'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', // For tooltips
      ];

      const loadScript = (src) =>
        new Promise((resolve, reject) => {
          const script = document.createElement('script');
          script.src = src;
          script.async = false; // Important: Load synchronously to ensure order
          script.onload = () => {
            console.log(`Script loaded: ${src}`);
            resolve();
          };
          script.onerror = () => {
            console.error(`Failed to load script: ${src}`);
            reject(new Error(`Không thể tải script: ${src}`));
          };
          document.head.appendChild(script);
        });

      try {
        // Load scripts sequentially. Setting async=false helps ensure execution order.
        for (const src of scripts) {
          await loadScript(src);
        }

        // Load CSS after JS
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css';
        document.head.appendChild(link);

        const fontAwesomeLink = document.createElement('link');
        fontAwesomeLink.rel = 'stylesheet';
        fontAwesomeLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
        document.head.appendChild(fontAwesomeLink);

        // Set flag that scripts are loaded
        this.scriptsLoaded = true;
        console.log("All external scripts loaded. Attempting to fetch data and initialize DataTables.");

        // Fetch coupons first
        await this.fetchCoupons();

        // Use nextTick to ensure the DOM is updated by Vue with the fetched data
        // before DataTables tries to read it.
        this.$nextTick(() => {
          this.initializeDataTable();
        });

      } catch (error) {
        console.error('Lỗi khi tải script hoặc khởi tạo bảng:', error.message);
        this.listMessage = `Có lỗi khi tải tài nguyên: ${error.message}`;
        this.listMessageClass = 'text-danger';
      }
    },
    initializeDataTable() {
      // Ensure scripts are loaded and jQuery/DataTables are available
      if (this.scriptsLoaded && typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
        console.log("Initializing DataTable.");

        // Destroy existing instance if any
        if (this.dataTable) {
            this.dataTable.destroy();
            this.dataTable = null; // Important to nullify after destroy
        }

        // Initialize DataTable. DataTables will read directly from the DOM (#add-row)
        this.dataTable = jQuery('#add-row').DataTable({
          pageLength: 10,
          lengthMenu: [10, 25, 50, 100],
          searching: true,
          paging: true,
          info: true,
          responsive: true,
          language: {
            lengthMenu: 'Hiển thị _MENU_ mục',
            search: 'Tìm kiếm:',
            info: 'Hiển thị _START_ đến _END_ của _TOTAL_ mục',
            paginate: {
              previous: 'Trước',
              next: 'Tiếp',
            },
            emptyTable: 'Không có mã giảm giá nào',
          },
          // NO 'data' property here. DataTables reads directly from the DOM (v-for)
          // The columns configuration is also not strictly necessary when DataTables reads from DOM
          // but can be useful for rendering/ordering overrides if you were to use it with 'data' later.
          // For now, removing 'columns' if you're populating via v-for is simpler.
          drawCallback: () => {
            // Reinitialize Bootstrap tooltips after each draw
            // Dispose previous tooltips to prevent duplicates
            jQuery('[data-bs-toggle="tooltip"]').tooltip('dispose');
            jQuery('[data-bs-toggle="tooltip"]').tooltip();
          },
        });
      } else {
        console.error('DataTables hoặc jQuery không được tải đúng cách khi initializeDataTable được gọi.');
        this.listMessage = 'Không thể khởi tạo bảng! Vui lòng tải lại trang.';
        this.listMessageClass = 'text-danger';
      }
    },
    async fetchCoupons() {
      try {
        const response = await axios.get('http://localhost:8000/api/admin/coupons');
        this.coupons = Array.isArray(response.data) ? response.data : response.data.data || [];
        if (!this.coupons.length) {
          this.listMessage = 'Không có mã giảm giá nào.';
          this.listMessageClass = 'text-info';
        } else {
          this.listMessage = ''; // Clear message if coupons are loaded
        }
        // The watch handler for 'coupons' will handle updating the DataTable
      } catch (error) {
        console.error('Lỗi khi tải danh sách:', error);
        this.listMessage = error.response?.data?.message || 'Có lỗi khi tải danh sách mã giảm giá!';
        this.listMessageClass = 'text-danger';
      }
    },
    async addCoupon() {
      // ... (your existing addCoupon logic)
      if (!this.coupon.code || !this.coupon.discount_type || this.coupon.discount_value === '' || this.coupon.discount_value < 0) {
        this.addMessage = 'Vui lòng nhập mã, loại giảm giá và giá trị giảm giá hợp lệ!';
        this.addMessageClass = 'text-danger';
        return;
      }

      if (this.coupon.start_date && this.coupon.end_date && new Date(this.coupon.start_date) > new Date(this.coupon.end_date)) {
        this.addMessage = 'Ngày bắt đầu không thể lớn hơn ngày kết thúc!';
        this.addMessageClass = 'text-danger';
        return;
      }

      const payload = {
        code: this.coupon.code,
        discount_type: this.coupon.discount_type,
        discount_value: parseFloat(this.coupon.discount_value),
        min_order_amount: this.coupon.min_order_amount !== '' ? parseFloat(this.coupon.min_order_amount) : null,
        max_discount: this.coupon.max_discount !== '' ? parseFloat(this.coupon.max_discount) : null,
        start_date: this.coupon.start_date || null,
        end_date: this.coupon.end_date || null,
        usage_limit: this.coupon.usage_limit !== '' ? parseInt(this.coupon.usage_limit) : null,
        per_user_limit: this.coupon.per_user_limit !== '' ? parseInt(this.coupon.per_user_limit) : null,
      };

      try {
        const response = await axios.post('http://localhost:8000/api/admin/coupons', payload);
        await this.fetchCoupons(); // This will trigger the 'coupons' watcher and re-draw DataTables
        this.addMessage = response.data.message || 'Thêm mã giảm giá thành công!';
        this.addMessageClass = 'text-success';
        // Clear form fields
        this.coupon.code = '';
        this.coupon.discount_type = 'percent';
        this.coupon.discount_value = '';
        this.coupon.min_order_amount = '';
        this.coupon.max_discount = '';
        this.coupon.start_date = '';
        this.coupon.end_date = '';
        this.coupon.usage_limit = '';
        this.coupon.per_user_limit = '';
      } catch (error) {
        console.error('Lỗi từ API:', error.response);
        const errors = error.response?.data?.errors;
        this.addMessage = errors
          ? Object.values(errors).flat().join(' ')
          : error.response?.data?.message || 'Có lỗi khi thêm mã giảm giá!';
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
        const response = await axios.delete(`http://localhost:8000/api/admin/coupons/${this.deleteId}`);
        await this.fetchCoupons(); // This will trigger the 'coupons' watcher and re-draw DataTables
        this.listMessage = response.data.message || 'Xóa mã giảm giá thành công!';
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
  beforeUnmount() {
    if (this.dataTable) {
      this.dataTable.destroy();
      this.dataTable = null;
    }
    // Remove global vm reference
    if (window.vm === this) {
        delete window.vm;
    }
    // Clean up any remaining tooltips from Bootstrap
    jQuery('[data-bs-toggle="tooltip"]').tooltip('dispose');
  },
};
</script>

<style scoped>
.container {
  max-width: 1200px;
  margin: 50px auto;
}

.form-group {
  margin-bottom: 1rem; /* Reduced margin for tighter spacing in form */
}

.form-control {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  border: 1px solid #ced4da;
  border-radius: 4px;
}

.form-text {
  font-size: 0.875em; /* Smaller helper text */
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
  vertical-align: middle; /* Align content vertically */
}

.table th {
  background-color: #f8f9fa;
  white-space: nowrap; /* Prevent headers from wrapping too much */
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
  /* No need for background-color here if you set it on the modal-dialog or the inner div */
}

.modal-dialog {
  margin: 100px auto;
  z-index: 1050; /* Ensure modal is above other content */
}

.modal-backdrop.show {
    opacity: 0.5; /* Ensure backdrop is visible if not handled by custom modal style */
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.2rem;
}

/* Style for DataTables controls */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
  margin-bottom: 15px;
}

.dataTables_wrapper .dataTables_paginate {
  margin-top: 15px;
}

.dataTables_wrapper .dataTables_info {
  margin-top: 15px;
}
</style>