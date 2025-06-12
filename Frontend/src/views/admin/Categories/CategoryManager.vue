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
                    <table id="category-table" class="display table table-bordered">
                      <thead>
                        <tr>
                          <th>Tên danh mục</th>
                          <th style="width: 10%">Hành động</th>
                        </tr>
                      </thead>
                      <tbody>
                        <!-- DataTables will populate this -->
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
      categories: {
        data: [],
        current_page: 1,
        last_page: 1,
        total: 0,
      },
      addMessage: '',
      addMessageClass: '',
      listMessage: '',
      listMessageClass: '',
      showDeleteModal: false,
      deleteId: null,
      dataTable: null, // Store DataTable instance
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
    this.loadScriptsAndInitializeTable();
  },
  beforeDestroy() {
    if (this.dataTable) {
      this.dataTable.destroy();
    }
  },
  methods: {
    async loadScriptsAndInitializeTable() {
      const scripts = [
        'https://code.jquery.com/jquery-3.7.1.min.js',
        'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
      ];

      const loadScript = (src) =>
        new Promise((resolve, reject) => {
          const script = document.createElement('script');
          script.src = src;
          script.async = true;
          script.onload = () => {
            console.log(`Loaded: ${src}`);
            resolve();
          };
          script.onerror = () => {
            console.error(`Failed to load: ${src}`);
            reject(new Error(`Không thể tải script: ${src}`));
          };
          document.head.appendChild(script);
        });

      try {
        for (const src of scripts) {
          await loadScript(src);
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css';
        document.head.appendChild(link);

        const fontAwesomeLink = document.createElement('link');
        fontAwesomeLink.rel = 'stylesheet';
        fontAwesomeLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
        document.head.appendChild(fontAwesomeLink);

        this.initializeDataTable();
      } catch (error) {
        console.error('Lỗi khi tải script:', error.message);
        this.listMessage = 'Có lỗi khi tải bảng danh mục!';
        this.listMessageClass = 'text-danger';
      }
    },
    initializeDataTable() {
      if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
        this.dataTable = jQuery('#category-table').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
            url: 'http://localhost:8000/api/categories',
            data: (d) => ({
              page: Math.ceil((d.start + 1) / d.length),
              per_page: d.length,
              search: d.search.value,
            }),
            dataSrc: (response) => {
              this.categories = {
                data: response.data,
                current_page: response.current_page,
                last_page: response.last_page,
                total: response.total,
              };
              if (!response.data.length) {
                this.listMessage = 'Không có danh mục nào!';
                this.listMessageClass = 'text-info';
              } else {
                this.listMessage = '';
                this.listMessageClass = '';
              }
              return response.data;
            },
            error: (xhr) => {
              console.error('DataTables error:', xhr);
              this.listMessage = 'Có lỗi khi tải danh sách danh mục!';
              this.listMessageClass = 'text-danger';
            },
          },
          columns: [
            { data: 'name', defaultContent: '-' },
            {
              data: null,
              orderable: false,
              searchable: false,
              render: (data) => `
                <div class="form-button-action">
                  <button type="button" data-bs-toggle="tooltip" title="Chỉnh sửa danh mục" class="btn btn-link btn-primary btn-lg edit-btn" data-id="${data.id}">
                    <i class="fa fa-edit"></i>
                  </button>
                  <button type="button" data-bs-toggle="tooltip" title="Xóa" class="btn btn-link btn-danger delete-btn" data-id="${data.id}">
                    <i class="fa fa-times"></i>
                  </button>
                </div>
              `,
            },
          ],
          pageLength: 10,
          lengthMenu: [10, 25, 50, 100],
          searching: true,
          paging: true,
          info: true,
          language: {
            lengthMenu: 'Hiển thị _MENU_ mục',
            search: 'Tìm kiếm:',
            info: 'Hiển thị _START_ đến _END_ của _TOTAL_ mục',
            paginate: {
              previous: 'Trước',
              next: 'Tiếp',
            },
            emptyTable: 'Không có danh mục nào',
            processing: 'Đang tải...',
          },
          drawCallback: () => {
            // Reinitialize tooltips
            jQuery('[data-bs-toggle="tooltip"]').tooltip();
            // Bind click events for buttons
            jQuery('#category-table .edit-btn').off('click').on('click', (e) => {
              const id = jQuery(e.currentTarget).data('id');
              this.editCategory(id);
            });
            jQuery('#category-table .delete-btn').off('click').on('click', (e) => {
              const id = jQuery(e.currentTarget).data('id');
              this.openDeleteModal(id);
            });
          },
        });
      } else {
        console.error('DataTables không được tải đúng cách.');
        this.listMessage = 'Không thể khởi tạo bảng!';
        this.listMessageClass = 'text-danger';
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
        const response = await axios.post('http://localhost:8000/api/categories', payload);
        this.addMessage = response.data.message || 'Thêm danh mục thành công!';
        this.addMessageClass = 'text-success';
        this.category.name = '';
        if (this.dataTable) {
          this.dataTable.ajax.reload(null, false); // Refresh table, stay on current page
        }
      } catch (error) {
        console.error('Add category error:', error.response || error);
        const errors = error.response?.data?.errors;
        this.addMessage = errors
          ? Object.values(errors).flat().join(' ')
          : error.response?.data?.message || 'Có lỗi khi thêm danh mục!';
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
        this.listMessage = response.data.message || 'Xóa danh mục thành công!';
        this.listMessageClass = 'text-success';
        this.showDeleteModal = false;
        this.deleteId = null;
        if (this.dataTable) {
          this.dataTable.ajax.reload(null, false); // Refresh table, stay on current page
        }
      } catch (error) {
        console.error('Delete category error:', error);
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
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
  margin-bottom: 15px;
}
.dataTables_wrapper .dataTables_paginate,
.dataTables_wrapper .dataTables_info {
  margin-top: 15px;
}
</style>