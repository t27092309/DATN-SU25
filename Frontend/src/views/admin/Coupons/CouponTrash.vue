<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <div class="mb-6">
        <h3 class="text-3xl font-bold mb-3">{{ route.meta.title }}</h3>
        <ul class="flex items-center space-x-2 text-gray-600 text-sm">
          <li class="nav-home">
            <router-link :to="{ name: 'AdminDashboard' }" class="hover:text-blue-600">
              <i class="fas fa-home"></i>
            </router-link>
          </li>
          <li class="separator">
            <i class="fas fa-chevron-right text-xs"></i>
          </li>
          <li class="nav-item">
            <router-link :to="{ name: 'Coupons' }" class="hover:text-blue-600">Mã Giảm Giá</router-link>
          </li>
          <li class="separator">
            <i class="fas fa-chevron-right text-xs"></i>
          </li>
          <li class="nav-item">
            <a href="#" class="text-blue-600">{{ route.meta.title }}</a>
          </li>
        </ul>
      </div>

      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-semibold">{{ route.meta.title }}</h1>
          <router-link :to="{ name: 'Coupons' }"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
          </router-link>
        </div>

        <div class="card-body">
          <div class="overflow-x-auto">
            <table id="trashed-coupons-table" class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại giảm giá</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giá trị</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày hết hạn</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày xóa</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap" style="width: 15%">Hành động</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200"></tbody>
            </table>
            <p v-if="listMessage" :class="listMessageClass" class="mt-4 text-sm">{{ listMessage }}</p>
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
      listMessageClass.value = "text-blue-500"; // Tailwind class for info
    } else {
      listMessage.value = "";
    }
    await destroyAndReinitializeDataTable();
  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách mã giảm giá trong thùng rác!";
    listMessageClass.value = "text-red-500"; // Tailwind class for danger
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
    successIcon = "success";
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
          render: (data) => (data ? new Date(data).toLocaleString("vi-VN") : "Không có"),
        },
        {
          data: "deleted_at",
          render: (data) => (data ? new Date(data).toLocaleString("vi-VN") : "Không có"),
        },
        {
          data: null,
          render: (data, type, row) => `
            <div class="flex space-x-2 justify-center items-center">
              <button type="button" title="Khôi phục" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 whitespace-nowrap" data-action="restore" data-id="${row.id}">
                <i class="fas fa-undo"></i>
              </button>
              <button type="button" title="Xóa vĩnh viễn" class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 whitespace-nowrap" data-action="force" data-id="${row.id}">
                <i class="fas fa-trash-alt"></i>
              </button>
            </div>
          `,
        },
      ],
      data: coupons.value,
      drawCallback: () => {
        // Unbind previous event handlers before re-binding to prevent multiple calls
        jQuery("#trashed-coupons-table").off("click", "button[data-action]");
        jQuery("#trashed-coupons-table").on("click", "button[data-action]", (e) => {
          const id = jQuery(e.currentTarget).data("id");
          const action = jQuery(e.currentTarget).data("action");
          if (action === "restore") {
            confirmActionWithSwal(id, "restore");
          } else if (action === "force") {
            confirmActionWithSwal(id, "force");
          }
        });
      },
    });
  } else {
    console.error("DataTables không được tải đúng cách hoặc không có jQuery.");
    listMessage.value = "Không thể khởi tạo bảng!";
    listMessageClass.value = "text-red-500";
  }
};

onMounted(async () => {
  const scripts = [
    "https://code.jquery.com/jquery-3.7.1.min.js",
    "https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js",
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
    listMessageClass.value = "text-red-500";
  }
});
</script>

<style scoped>
/* Base container and page layout */
.container {
  max-width: 1200px;
  margin: 0 auto; /* Center the container */
}

/* Remove default padding from page-inner to let Tailwind's px-4 handle it */
.page-inner {
  padding: 0;
}

/* Specific styling for the DataTables generated elements if needed */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
  margin-bottom: 1rem; /* Tailwind's mb-4 equivalent */
  display: flex; /* Use flex to align items horizontally */
  align-items: center; /* Center items vertically */
  gap: 0.5rem; /* Space between filter/length elements */
}

/* Adjust select input inside DataTables length control */
.dataTables_wrapper .dataTables_length select {
  padding: 0.25rem 0.5rem; /* Tailwind's px-2 py-1 */
  border: 1px solid #d1d5db; /* Tailwind's border-gray-300 */
  border-radius: 0.25rem; /* Tailwind's rounded-md */
  background-color: #fff; /* White background */
  appearance: none; /* Remove default arrow */
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%236B7280'%3e%3cpath d='M7 7l3-3 3 3m0 6l-3 3-3-3' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 1.5em 1.5em;
  min-width: unset; /* Override any conflicting min-width */
  width: auto; /* Allow width to adjust to content */
}

/* Adjust search input inside DataTables filter control */
.dataTables_wrapper .dataTables_filter input {
  padding: 0.25rem 0.75rem; /* Tailwind's px-3 py-2 */
  border: 1px solid #d1d5db; /* Tailwind's border-gray-300 */
  border-radius: 0.25rem; /* Tailwind's rounded-md */
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); /* Tailwind's shadow-sm */
}

/* Pagination and info */
.dataTables_wrapper .dataTables_paginate,
.dataTables_wrapper .dataTables_info {
  margin-top: 1rem; /* Tailwind's mt-4 equivalent */
}

/* Specific styling for the DataTables table itself */
.dataTables_wrapper table.dataTable {
  border-collapse: collapse !important; /* Ensure no double borders */
}

.dataTables_wrapper table.dataTable th,
.dataTables_wrapper table.dataTable td {
  padding: 0.75rem 1.5rem; /* Equivalent to px-6 py-3 for th, but more flexible for td */
}
</style>