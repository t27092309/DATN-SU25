<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">{{ route.meta.title }}</h3>
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
            <router-link :to="{ name: 'ma-giam-gia' }">Mã Giảm Giá</router-link>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
          <li class="nav-item">
            <a href="#">{{ route.meta.title }}</a>
          </li>
        </ul>
      </div>
      <div class="card">
        <div class="card-header">
          <div class="card-title d-flex justify-content-between align-items-center">
            <h1>{{ route.meta.title }}</h1>
            <router-link :to="{ name: 'ma-giam-gia' }" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </router-link>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="trashed-coupons-table" class="display table table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Mã</th>
                  <th>Loại giảm giá</th>
                  <th>Giá trị</th>
                  <th>Ngày hết hạn</th>
                  <th>Ngày xóa</th>
                  <th style="width: 15%">Hành động</th>
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
</template>

<script setup>
import { onMounted, nextTick, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";

const route = useRoute();
const router = useRouter();

const coupons = ref([]);
const listMessage = ref("");
const listMessageClass = ref("");

// Fetch trashed coupons
const fetchTrashedCoupons = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/coupons/trashed");
    coupons.value = Array.isArray(response.data) ? response.data : response.data.data || [];

    if (!coupons.value.length) {
      listMessage.value = "Không có mã giảm giá nào trong thùng rác.";
      listMessageClass.value = "text-info";
    } else {
      listMessage.value = "";
    }
    await destroyAndReinitializeDataTable();
  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách mã giảm giá trong thùng rác!";
    listMessageClass.value = "text-danger";
    console.error("Lỗi khi tải danh sách thùng rác:", error);
    await destroyAndReinitializeDataTable();
  }
};

// Centralized function to handle actions with SweetAlert2 confirmation
const confirmActionWithSwal = async (id, type) => {
  let title, text, confirmButtonText, icon;
  let actionEndpoint = "";
  let successMessage = "";
  let successIcon = "success";

  if (type === "restore") {
    title = "Bạn có chắc muốn khôi phục mã giảm giá này?";
    text = "Mã giảm giá sẽ được đưa về trạng thái hoạt động và xuất hiện lại trong danh sách mã giảm giá.";
    confirmButtonText = "Có, khôi phục!";
    icon = "info";
    actionEndpoint = `http://localhost:8000/api/admin/coupons/${id}/restore`;
    successMessage = "Khôi phục mã giảm giá thành công!";
  } else if (type === "force") {
    title = "Bạn có chắc muốn xóa VĨNH VIỄN mã giảm giá này?";
    text = "Hành động này không thể hoàn tác! Toàn bộ dữ liệu liên quan sẽ bị xóa. Bạn vẫn muốn tiếp tục?";
    confirmButtonText = "Có, xóa vĩnh viễn!";
    icon = "error";
    actionEndpoint = `http://localhost:8000/api/admin/coupons/${id}/force`;
    successMessage = "Xóa vĩnh viễn mã giảm giá thành công!";
    successIcon = "info";
  } else {
    Swal.fire("Lỗi!", "Hành động không hợp lệ.", "error");
    return;
  }

  try {
    const result = await Swal.fire({
      title: title,
      text: text,
      icon: icon,
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: confirmButtonText,
      cancelButtonText: "Hủy",
    });

    if (result.isConfirmed) {
      let response;
      if (type === "restore") {
        response = await axios.put(actionEndpoint);
      } else {
        response = await axios.delete(actionEndpoint);
      }

      await fetchTrashedCoupons();

      Swal.fire({
        title: response.data.message || successMessage,
        icon: successIcon,
        confirmButtonText: "Đã hiểu!",
      });
    }
  } catch (error) {
    console.error(`Lỗi khi thực hiện hành động ${type}:`, error);
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
    dataTableInstance = jQuery("#trashed-coupons-table").DataTable({
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
        emptyTable: "Không có mã giảm giá nào trong thùng rác",
      },
      columns: [
        { data: "id" },
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
          data: "deleted_at",
          render: (data) => (data ? new Date(data).toLocaleString() : "Không có"),
        },
        {
          data: null,
          render: (data, type, row) => `
            <div class="form-button-action">
              <button type="button" data-bs-toggle="tooltip" title="Khôi phục" class="btn btn-link btn-success" data-id="${row.id}">
                <i class="fas fa-undo"></i> Khôi phục
              </button>
              <button type="button" data-bs-toggle="tooltip" title="Xóa vĩnh viễn" class="btn btn-link btn-danger" data-id="${row.id}">
                <i class="fas fa-trash-alt"></i> Xóa vĩnh viễn
              </button>
            </div>
          `,
        },
      ],
      data: coupons.value,
      drawCallback: () => {
        jQuery('[data-bs-toggle="tooltip"]').tooltip();
        jQuery("#trashed-coupons-table")
          .find(".btn-success")
          .off("click")
          .on("click", (e) => {
            const id = jQuery(e.currentTarget).data("id");
            confirmActionWithSwal(id, "restore");
          });
        jQuery("#trashed-coupons-table")
          .find(".btn-danger")
          .off("click")
          .on("click", (e) => {
            const id = jQuery(e.currentTarget).data("id");
            confirmActionWithSwal(id, "force");
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

    await fetchTrashedCoupons();
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
.form-button-action {
  display: flex;
  flex-direction: column;
  gap: 5px;
  white-space: nowrap;
}
.form-button-action .btn {
  width: 100%;
  text-align: center;
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