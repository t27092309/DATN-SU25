<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Quản lý mã giảm giá</h3>
        <ul class="breadcrumbs mb-3">
          <li class="nav-home">
            <router-link :to="{ name: 'AdminDashboard' }">
              <i class="icon-home"></i>
            </router-link>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
          <li class="nav-item">
            <a href="#">Mã Giảm Giá</a>
          </li>
        </ul>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title d-flex justify-content-between align-items-center">
                Quản lý mã giảm giá
                <div class="d-flex gap-2">
                  <router-link :to="{ name: 'CouponTrash' }" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-trash"></i> Thùng rác
                  </router-link>
                </div>
              </div>
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
                      <input type="text" v-model="coupon.code" class="form-control" id="code"
                        placeholder="Nhập mã giảm giá" required />
                      <small class="form-text text-muted">Ví dụ: SALE2025</small>
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
                        placeholder="Nhập giá trị giảm" required />
                      <small class="form-text text-muted">Ví dụ: 20 (cho 20%) hoặc 100000 (cho 100,000 VNĐ)</small>
                    </div>
                    <div class="form-group">
                      <label for="expires_at">Ngày hết hạn</label>
                      <input type="datetime-local" v-model="coupon.expires_at" class="form-control" id="expires_at" />
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
                          <th style="width: 10%">Hành động</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
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
  </div>
</template>

<script setup>
import { onMounted, nextTick, ref, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import slugify from "slugify";
import Swal from "sweetalert2";

// Reactive variables
const coupon = ref({
  code: "",
  discount_type: "percent",
  discount_value: "",
  expires_at: "",
});
const coupons = ref([]);
const addMessage = ref("");
const addMessageClass = ref("");
const listMessage = ref("");
const listMessageClass = ref("");

const router = useRouter();

// Watch for code changes to slugify
watch(
  () => coupon.value.code,
  (newCode) => {
    coupon.value.code = slugify(newCode, {
      lower: true,
      strict: true,
      locale: "vi",
    });
  }
);

// Fetch active coupons
const fetchCoupons = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/coupons");
    coupons.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    if (!coupons.value.length) {
      listMessage.value = "Không có mã giảm giá nào.";
      listMessageClass.value = "text-info";
    } else {
      listMessage.value = "";
    }
    await destroyAndReinitializeDataTable();
  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách mã giảm giá!";
    listMessageClass.value = "text-danger";
    console.error("Lỗi khi tải danh sách:", error);
    await destroyAndReinitializeDataTable();
  }
};

// Handle adding a new coupon
const addCoupon = async () => {
  if (!coupon.value.code || !coupon.value.discount_type || !coupon.value.discount_value) {
    addMessage.value = "Vui lòng nhập mã, loại giảm giá và giá trị giảm giá!";
    addMessageClass.value = "text-danger";
    return;
  }
  try {
    const response = await axios.post(
      "http://localhost:8000/api/admin/coupons",
      {
        code: coupon.value.code,
        discount_type: coupon.value.discount_type,
        discount_value: parseFloat(coupon.value.discount_value),
        expires_at: coupon.value.expires_at || null,
      },
      { validateStatus: (status) => status >= 200 && status < 300 }
    );
    addMessage.value = response.data.message || "Thêm mã giảm giá thành công!";
    addMessageClass.value = "text-success";
    coupon.value.code = "";
    coupon.value.discount_type = "percent";
    coupon.value.discount_value = "";
    coupon.value.expires_at = "";
    await fetchCoupons();
  } catch (error) {
    console.error("Lỗi từ API:", error.response);
    const errors = error.response?.data?.errors;
    if (errors) {
      addMessage.value = Object.values(errors).flat().join(" ");
    } else {
      addMessage.value = error.response?.data?.message || "Có lỗi khi thêm mã giảm giá!";
    }
    addMessageClass.value = "text-danger";
  }
};

// Navigate to edit page
const editCoupon = (id) => {
  router.push(`/coupons/edit/${id}`);
};

// Handle soft delete with SweetAlert2
const confirmSoftDeleteWithSwal = async (id) => {
  try {
    const result = await Swal.fire({
      title: "Bạn có chắc muốn xóa mềm mã giảm giá này?",
      text: "Hành động này sẽ chuyển mã giảm giá vào thùng rác, bạn có thể khôi phục nó sau này. Bạn vẫn muốn tiếp tục?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Có, xóa mềm!",
      cancelButtonText: "Hủy",
    });

    if (result.isConfirmed) {
      const response = await axios.delete(`http://localhost:8000/api/admin/coupons/${id}`);
      await fetchCoupons();
      Swal.fire({
        title: response.data.message || "Xóa mềm mã giảm giá thành công!",
        icon: "success",
        confirmButtonText: "Đã hiểu!",
      });
    }
  } catch (error) {
    console.error("Lỗi khi xóa mềm mã giảm giá:", error);
    const errorMessage =
      error.response?.data?.message || "Không kết nối được tới server. Vui lòng kiểm tra mạng của bạn.";
    Swal.fire({
      icon: "error",
      title: "Lỗi!",
      text: `Xảy ra lỗi: ${errorMessage}`,
    });
  }
};

// Function to destroy and reinitialize DataTables
let dataTableInstance = null;
const destroyAndReinitializeDataTable = async () => {
  if (dataTableInstance) {
    dataTableInstance.destroy();
    dataTableInstance = null;
  }
  await nextTick();
  if (typeof jQuery !== "undefined" && jQuery.fn.DataTable) {
    dataTableInstance = jQuery("#add-row").DataTable({
      pageLength: 10,
      lengthMenu: [10, 25, 50, 100],
      searching: true,
      paging: true,
      info: true,
      responsive: true,
      language: {
        lengthMenu: "Hiển thị _MENU_ mục",
        search: "Tìm kiếm:",
        info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
        paginate: {
          previous: "Trước",
          next: "Tiếp",
        },
        emptyTable: "Không có mã giảm giá nào",
      },
      columns: [
        { data: "code", defaultContent: "Không có" },
        {
          data: "discount_type",
          render: (data) => (data === "percent" ? "Phần trăm" : "Cố định"),
        },
        {
          data: "discount_value",
          render: (data, type, row) => `${data} ${row.discount_type === "percent" ? "%" : "VNĐ"}`,
        },
        {
          data: "expires_at",
          render: (data) => (data ? new Date(data).toLocaleString() : "Không có"),
        },
        {
          data: null,
          render: (data, type, row) => `
            <div class="form-button-action">
              <button type="button" data-bs-toggle="tooltip" title="Chỉnh sửa mã giảm giá" class="btn btn-link btn-primary btn-lg" data-id="${row.id}">
                <i class="fa fa-edit"></i>
              </button>
              <button type="button" data-bs-toggle="tooltip" title="Xóa mềm" class="btn btn-link btn-danger" data-id="${row.id}">
                <i class="fa fa-times"></i>
              </button>
            </div>
          `,
        },
      ],
      data: coupons.value,
      drawCallback: () => {
        jQuery('[data-bs-toggle="tooltip"]').tooltip();
        jQuery("#add-row")
          .find(".btn-primary")
          .off("click")
          .on("click", (e) => {
            const id = jQuery(e.currentTarget).data("id");
            editCoupon(id);
          });
        jQuery("#add-row")
          .find(".btn-danger")
          .off("click")
          .on("click", (e) => {
            const id = jQuery(e.currentTarget).data("id");
            confirmSoftDeleteWithSwal(id);
          });
      },
    });
  } else {
    console.error("DataTables không được tải đúng cách hoặc không có jQuery.");
    listMessage.value = "Không thể khởi tạo bảng!";
    listMessageClass.value = "text-danger";
  }
};

onMounted(async () => {
  const scripts = [
    "https://code.jquery.com/jquery-3.7.1.min.js",
    "https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js",
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js",
  ];

  const loadScript = (src) =>
    new Promise((resolve, reject) => {
      const script = document.createElement("script");
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
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = "https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css";
    document.head.appendChild(link);

    const fontAwesomeLink = document.createElement("link");
    fontAwesomeLink.rel = "stylesheet";
    fontAwesomeLink.href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css";
    document.head.appendChild(fontAwesomeLink);

    await fetchCoupons();
  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error.message, error.stack);
    listMessage.value = "Có lỗi khi tải bảng mã giảm giá!";
    listMessageClass.value = "text-danger";
  }
});
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