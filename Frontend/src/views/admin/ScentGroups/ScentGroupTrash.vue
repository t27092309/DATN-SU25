<template>
    <div class="container mx-auto px-4 py-8">
        <div class="page-inner">
            <div class="mb-6">
                <ul class="flex items-center space-x-2 text-gray-600 text-sm">
                    <li>
                        <router-link :to="{ name: 'AdminDashboard' }" class="hover:text-blue-800">
                            <i class="fas fa-home"></i>
                        </router-link>
                    </li>
                    <li class="separator">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </li>
                    <li>
                        <router-link :to="{ name: 'products' }" class="hover:text-blue-800">Danh sách sản phẩm</router-link>
                    </li>
                    <li>
                        <i class="fas fa-chevron-right text-gray-400"></i>
                    </li>
                    <li>
                        <span class="font-semibold">{{ route.meta.title }}</span>
                    </li>
                </ul>
            </div>

      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="mb-6 flex justify-between items-center">
          <h1 class="text-2xl font-semibold text-gray-800">{{ route.meta.title }}</h1>
          <router-link :to="{ name: 'ScentGroups' }"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
          </router-link>
        </div>

        <div class="card-body">
          <div class="overflow-x-auto">
            <table id="trashed-scent-groups-table" class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã màu</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày cập nhật
                  </th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày xóa</th>
                  <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap"
                    style="width: 15%">Hành động</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="scentGroup in scentGroups" :key="scentGroup.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ scentGroup.id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ scentGroup.name || "Không có" }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span :style="{ backgroundColor: scentGroup.color_code }"
                      class="inline-block w-5 h-5 align-middle mr-2 border border-gray-300 rounded"></span>
                    {{ scentGroup.color_code || "Không có" }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{
                      scentGroup.created_at
                        ? new Date(scentGroup.created_at).toLocaleString('vi-VN')
                        : "Không có"
                    }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{
                      scentGroup.updated_at
                        ? new Date(scentGroup.updated_at).toLocaleString('vi-VN')
                        : "Không có"
                    }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{
                      scentGroup.deleted_at
                        ? new Date(scentGroup.deleted_at).toLocaleString('vi-VN')
                        : "Không có"
                    }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex flex-col space-y-1">
                      <button type="button" title="Khôi phục"
                        class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        @click="confirmActionWithSwal(scentGroup.id, 'restore')">
                        <i class="fas fa-undo mr-1"></i> Khôi phục
                      </button>
                      <button type="button" title="Xóa vĩnh viễn"
                        class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        @click="confirmActionWithSwal(scentGroup.id, 'force')">
                        <i class="fas fa-trash-alt mr-1"></i> Xóa vĩnh viễn
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!scentGroups || scentGroups.length === 0">
                  <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                    Không có nhóm hương nào trong thùng rác.
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="listMessage" :class="listMessageClass" class="mt-4 text-sm">
              {{ listMessage }}
            </p>
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
import Swal from 'sweetalert2';

const route = useRoute();
const router = useRouter();

const scentGroups = ref([]);
const listMessage = ref("");
const listMessageClass = ref("");

// Biến để theo dõi instance của DataTables
let dataTableInstance = null;

// Fetch trashed scent groups
const fetchTrashedScentGroups = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/scent-groups/trashed");
    const data = Array.isArray(response.data) ? response.data : response.data.data || [];
    scentGroups.value = data;

    if (!scentGroups.value.length) {
      listMessage.value = "Không có nhóm hương nào trong thùng rác.";
      listMessageClass.value = "text-blue-500";
      // Quan trọng: Hủy DataTables nếu không có dữ liệu
      destroyDataTableOnly();
    } else {
      listMessage.value = "";
      // Quan trọng: Khởi tạo/tái khởi tạo DataTables chỉ khi có dữ liệu
      await initializeDataTable();
    }

  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách nhóm hương trong thùng rác!";
    listMessageClass.value = "text-red-500";
    console.error("Lỗi khi tải danh sách thùng rác:", error);
    // Hủy DataTables nếu có lỗi xảy ra và không có dữ liệu được hiển thị
    destroyDataTableOnly();
  }
};

// Hàm chỉ hủy DataTables instance
const destroyDataTableOnly = () => {
  if (dataTableInstance) {
    dataTableInstance.destroy();
    dataTableInstance = null;
  }
};

// Hàm chỉ khởi tạo DataTables instance
const initializeDataTable = async () => {
  destroyDataTableOnly(); // Đảm bảo hủy instance cũ trước khi tạo mới
  await nextTick(); // Chờ Vue cập nhật DOM

  if (typeof jQuery !== "undefined" && jQuery.fn.DataTable) {
    dataTableInstance = jQuery("#trashed-scent-groups-table").DataTable({
      pageLength: 10,
      responsive: true,
      destroy: true, // Đảm bảo rằng nó có thể bị hủy nếu được gọi lại
      language: {
        lengthMenu: "Hiển thị _MENU_ mục",
        search: "Tìm kiếm:",
        info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
        paginate: {
          previous: "Trước",
          next: "Tiếp",
        },
        emptyTable: "Không có dữ liệu trong bảng",
      },
    });
  } else {
    console.error("DataTables không được tải đúng cách hoặc không có jQuery.");
  }
};

// Centralized function to handle actions with SweetAlert2 confirmation
const confirmActionWithSwal = async (id, type) => {
  let title, text, confirmButtonText, icon;
  let actionEndpoint = '';
  let successMessage = '';
  let successIcon = 'success';

  if (type === 'restore') {
    title = 'Bạn có chắc muốn khôi phục nhóm hương này?';
    text = 'Nhóm hương sẽ được đưa về trạng thái hoạt động và xuất hiện lại trong danh sách sản phẩm.';
    confirmButtonText = 'Có, khôi phục!';
    icon = 'info';
    actionEndpoint = `http://localhost:8000/api/admin/scent-groups/${id}/restore`;
    successMessage = 'Khôi phục nhóm hương thành công!';
  } else if (type === 'force') {
    title = 'Bạn có chắc muốn xóa VĨNH VIỄN nhóm hương này?';
    text = 'Hành động này không thể hoàn tác! Toàn bộ dữ liệu liên quan sẽ bị xóa. Bạn vẫn muốn tiếp tục?';
    confirmButtonText = 'Có, xóa vĩnh viễn!';
    icon = 'error';
    actionEndpoint = `http://localhost:8000/api/admin/scent-groups/${id}/force`;
    successMessage = 'Xóa vĩnh viễn nhóm hương thành công!';
    successIcon = 'info';
  } else {
    Swal.fire('Lỗi!', 'Hành động không hợp lệ.', 'error');
    return;
  }

  try {
    const result = await Swal.fire({
      title: title,
      text: text,
      icon: icon,
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: confirmButtonText,
      cancelButtonText: 'Hủy'
    });

    if (result.isConfirmed) {
      let response;
      if (type === 'restore') {
        response = await axios.put(actionEndpoint);
      } else {
        response = await axios.delete(actionEndpoint);
      }

      await fetchTrashedScentGroups(); // Re-fetch trashed groups after successful action

      Swal.fire({
        title: response.data.message || successMessage,
        icon: successIcon,
        confirmButtonText: 'Đã hiểu!'
      });
    }
  } catch (error) {
    console.error(`Lỗi khi thực hiện hành động ${type}:`, error);
    const errorMessage = error.response?.data?.message || 'Không kết nối được tới server. Vui lòng kiểm tra mạng của bạn.';
    Swal.fire({
      icon: 'error',
      title: 'Lỗi!',
      text: `Xảy ra lỗi: ${errorMessage}`,
    });
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
      script.onload = () => resolve();
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

    // Initial fetch for trashed scent groups
    await fetchTrashedScentGroups();

  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error.message, error.stack);
  }
});
</script>

<style scoped>
/* Base container and page layout */
.container {
  max-width: 1200px;
  /* Removed fixed margin to rely on Tailwind's auto margins or padding utilities */
  margin-left: auto;
  margin-right: auto;
  /* Tailwind's px-4 py-8 on the outer div handle spacing */
}

.page-inner {
  padding: 0;
}

/* Specific styling for the DataTables generated elements */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dataTables_wrapper .dataTables_length select {
  padding: 0.25rem 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  background-color: #fff;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%236B7280'%3e%3cpath d='M7 7l3-3 3 3m0 6l-3 3-3-3' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
  background-size: 1.5em 1.5em;
  min-width: unset;
  width: auto;
}

.dataTables_wrapper .dataTables_filter input {
  padding: 0.25rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.25rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* Pagination and info */
.dataTables_wrapper .dataTables_paginate,
.dataTables_wrapper .dataTables_info {
  margin-top: 1rem;
}

/* Specific styling for the DataTables table itself */
.dataTables_wrapper table.dataTable {
  border-collapse: collapse !important;
}

.dataTables_wrapper table.dataTable th,
.dataTables_wrapper table.dataTable td {
  padding: 0.75rem 1.5rem;
}

/* Custom styles that were originally Bootstrap-specific are now Tailwind classes */
/* .table, .card, .btn, .form-button-action, .color-box, .text-success, .text-danger, .text-info have been replaced */
</style>