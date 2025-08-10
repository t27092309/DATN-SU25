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
                        <router-link :to="{ name: 'BrandList' }" class="hover:text-blue-800">Quản lí thương hiệu</router-link>
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
          <h1 class="text-2xl font-semibold text-gray-800">{{ route.meta.title || 'Thùng Rác Thương Hiệu' }}</h1>
          <router-link :to="{ name: 'BrandList' }"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
          </router-link>
        </div>

        <div class="card-body">
          <div class="overflow-x-auto">
            <table id="trashed-brands-table" class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày xóa</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Hành động</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="brand in brands" :key="brand.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ brand.id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ brand.name || "Không có" }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ brand.slug || "Không có" }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <img v-if="brand.logo_url" :src="brand.logo_url" alt="Brand Logo"
                      class="w-12 h-12 object-contain rounded" />
                    <span v-else>Không có</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ brand.deleted_at ? new Date(brand.deleted_at).toLocaleString('vi-VN') : "Không có" }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex flex-col space-y-2">
                      <button type="button"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                        @click="confirmActionWithSwal(brand.id, 'restore')" title="Khôi phục">
                        <i class="fas fa-undo mr-1"></i> Khôi phục
                      </button>
                      <button type="button"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        @click="confirmActionWithSwal(brand.id, 'force')" title="Xóa vĩnh viễn">
                        <i class="fas fa-trash-alt mr-1"></i> Xóa vĩnh viễn
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
            <p v-if="brands.length === 0 && !dataTableInstance" :class="listMessageClass" class="mt-4 text-sm">
              {{ listMessage || "Không có thương hiệu nào trong thùng rác." }}
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

const brands = ref([]);
const listMessage = ref("");
const listMessageClass = ref("");

let dataTableInstance = null; // Biến để theo dõi instance của DataTables

const fetchTrashedBrands = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/brands/trashed");
    const data = Array.isArray(response.data) ? response.data : response.data.data || [];
    brands.value = data;

    if (!brands.value.length) {
      listMessage.value = "Không có thương hiệu nào trong thùng rác.";
      listMessageClass.value = "text-blue-500";
      // Quan trọng: Hủy DataTables nếu không có dữ liệu
      destroyDataTableOnly();
    } else {
      listMessage.value = "";
      // Quan trọng: Khởi tạo/tái khởi tạo DataTables chỉ khi có dữ liệu
      await initializeDataTable();
    }

  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách thương hiệu trong thùng rác!";
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
    dataTableInstance = jQuery("#trashed-brands-table").DataTable({
      pageLength: 10,
      responsive: true,
      destroy: true, // Đảm bảo rằng nó có thể bị hủy nếu được gọi lại
      order: [], // Bỏ sắp xếp mặc định để không gây lỗi với cột "Hành động"
      columnDefs: [ // Định nghĩa cột để DataTables xử lý chính xác
        { "orderable": false, "targets": [3, 5] } // Cột Logo (index 3) và Hành động (index 5) không thể sắp xếp
      ],
      language: { // Thêm tùy chỉnh ngôn ngữ cho DataTables
        lengthMenu: "Hiển thị _MENU_ mục",
        search: "Tìm kiếm:",
        info: "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
        paginate: {
          previous: "Trước",
          next: "Tiếp",
        },
        emptyTable: "Không có dữ liệu trong bảng", // Quan trọng cho trường hợp không có dữ liệu
      },
      drawCallback: () => {
        // Có thể thêm lại khởi tạo tooltips ở đây nếu cần một thư viện tooltip riêng
      },
    });
  } else {
    console.error("DataTables không được tải đúng cách hoặc không có jQuery.");
  }
};

const confirmActionWithSwal = async (id, type) => {
  let title, text, confirmButtonText, icon;
  let actionEndpoint = '';
  let successMessage = '';
  let successIcon = 'success';
  let method = '';

  if (type === 'restore') {
    title = 'Bạn có chắc muốn khôi phục thương hiệu này?';
    text = 'Thương hiệu sẽ được đưa về trạng thái hoạt động và xuất hiện lại trong danh sách chính.';
    confirmButtonText = 'Có, khôi phục!';
    icon = 'info';
    actionEndpoint = `http://localhost:8000/api/admin/brands/${id}/restore`;
    successMessage = 'Khôi phục thương hiệu thành công!';
    successIcon = 'success';
    method = 'post';
  } else if (type === 'force') {
    title = 'Bạn có chắc muốn xóa VĨNH VIỄN thương hiệu này?';
    text = 'Hành động này không thể hoàn tác! Toàn bộ dữ liệu liên quan sẽ bị xóa. Bạn vẫn muốn tiếp tục?';
    confirmButtonText = 'Có, xóa vĩnh viễn!';
    icon = 'error';
    actionEndpoint = `http://localhost:8000/api/admin/brands/${id}/force`;
    successMessage = 'Xóa vĩnh viễn thương hiệu thành công!';
    successIcon = 'info';
    method = 'delete';
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
      if (method === 'post') {
        response = await axios.post(actionEndpoint);
      } else if (method === 'delete') {
        response = await axios.delete(actionEndpoint);
      } else {
        throw new Error('Unsupported HTTP method.');
      }

      await fetchTrashedBrands(); // Refresh the list after action

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

    await fetchTrashedBrands();

  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error.message, error.stack);
  }
});
</script>

<style scoped>
/* Base container and page layout */
.container {
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
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
</style>