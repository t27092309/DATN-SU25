<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <div class="mb-6 flex justify-between items-center">
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
            <a href="#" class="">Quản lí nhóm hương</a>
          </li>
        </ul>
      </div>

      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-semibold">Quản lý nhóm hương</h1>
          <div class="flex gap-2">
            <router-link :to="{ name: 'ScentGroupTrash' }"
              class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <i class="fas fa-trash mr-2"></i> Thùng rác
            </router-link>
          </div>
        </div>

        <div class="card-body">
          <div class="overflow-x-auto mb-8">
            <table id="add-row" class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã màu</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày cập nhật</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap"
                    style="width: 10%">Hành động</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="scentGroupItem in scentGroups" :key="scentGroupItem.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ scentGroupItem.id }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ scentGroupItem.name || "Không có" }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span :style="{ backgroundColor: scentGroupItem.color_code }"
                      class="inline-block w-5 h-5 align-middle mr-2 border border-gray-300 rounded"></span>
                    {{ scentGroupItem.color_code || "Không có" }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{
                      scentGroupItem.created_at
                        ? new Date(scentGroupItem.created_at).toLocaleString('vi-VN')
                        : "Không có"
                    }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{
                      scentGroupItem.updated_at
                        ? new Date(scentGroupItem.updated_at).toLocaleString('vi-VN')
                        : "Không có"
                    }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex space-x-2">
                      <button type="button" title="Chỉnh sửa nhóm hương"
                        class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        @click="startEdit(scentGroupItem)">
                        <i class="fa fa-edit"></i>
                      </button>
                      <button type="button" title="Xóa mềm"
                        class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        @click="confirmSoftDeleteWithSwal(scentGroupItem.id)">
                        <i class="fa fa-times"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!scentGroups || scentGroups.length === 0">
                  <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                    Không có nhóm hương nào.
                  </td>
                </tr>
              </tbody>
            </table>
            </div>

          <div class="w-full px-4 mt-8">
            <form @submit.prevent="isEditing ? updateScentGroup() : addScentGroup()"
              class="p-6 border border-gray-200 rounded-lg shadow-sm">
              <div class="mb-6">
                <h5 class="text-xl font-semibold text-gray-800">{{ isEditing ? 'Chỉnh sửa nhóm hương' : 'Thêm mới nhóm hương' }}</h5>
              </div>
              <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tên nhóm hương</label>
                <input type="text" v-model="scentGroup.name"
                  class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  id="name" placeholder="Nhập tên nhóm hương" required />
                <p class="mt-1 text-sm text-gray-500">Ví dụ: Hương hoa, Hương gỗ (Tên này sẽ được lưu nguyên bản)</p>
              </div>
              <div class="mb-6">
                <label for="color_code" class="block text-sm font-medium text-gray-700 mb-1">Mã màu</label>
                <input type="color" v-model="scentGroup.color_code"
                  class="block w-full h-10 px-1 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm cursor-pointer"
                  id="color_code" required />
                <p class="mt-1 text-sm text-gray-500">Chọn màu từ bảng màu</p>
              </div>
              <div class="flex justify-end space-x-2">
                <button type="submit"
                  class="inline-flex items-center px-5 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <i :class="isEditing ? 'fas fa-save' : 'fas fa-plus'" class="mr-2"></i>
                  {{ isEditing ? 'Cập nhật nhóm hương' : 'Thêm nhóm hương' }}
                </button>
                <button v-if="isEditing" type="button" @click="cancelEdit"
                  class="inline-flex items-center px-5 py-2 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  <i class="fas fa-times mr-2"></i> Hủy
                </button>
              </div>
              </form>
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
import { useToast } from "vue-toastification"; // Import useToast

const route = useRoute();
const router = useRouter();
const toast = useToast(); // Khởi tạo instance của toast

const scentGroup = ref({
  id: null,
  name: "",
  color_code: "#000000",
});
const scentGroups = ref([]);
// Bỏ addMessage, addMessageClass, listMessage, listMessageClass vì dùng toast
const isEditing = ref(false);

const fetchScentGroups = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/scent-groups");
    scentGroups.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    if (!scentGroups.value.length) {
      toast.info("Không có nhóm hương nào."); // Thay thế listMessage
    }
    await destroyAndReinitializeDataTable();
  } catch (error) {
    toast.error(error.response?.data?.message || "Có lỗi khi tải danh sách nhóm hương!"); // Thay thế listMessage
    console.error("Lỗi khi tải danh sách:", error);
    await destroyAndReinitializeDataTable();
  }
};

const addScentGroup = async () => {
  if (!scentGroup.value.name || !scentGroup.value.color_code) {
    toast.error("Vui lòng nhập tên nhóm hương và chọn màu!"); // Thay thế addMessage
    return;
  }
  try {
    const response = await axios.post(
      "http://localhost:8000/api/admin/scent-groups",
      {
        name: scentGroup.value.name,
        color_code: scentGroup.value.color_code,
      },
      { validateStatus: (status) => status >= 200 && status < 300 }
    );
    toast.success(response.data.message || "Thêm nhóm hương thành công!"); // Thay thế addMessage
    resetForm();
    await fetchScentGroups();
  } catch (error) {
    console.error("Lỗi từ API:", error.response);
    const errors = error.response?.data?.errors;
    if (errors) {
      toast.error(Object.values(errors).flat().join(" ")); // Thay thế addMessage
    } else {
      toast.error(error.response?.data?.message || "Có lỗi khi thêm nhóm hương!"); // Thay thế addMessage
    }
  }
};

const startEdit = (item) => {
  scentGroup.value = { ...item };
  isEditing.value = true;
  window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
};

const updateScentGroup = async () => {
  if (!scentGroup.value.name || !scentGroup.value.color_code || !scentGroup.value.id) {
    toast.error("Dữ liệu cập nhật không hợp lệ!"); // Thay thế addMessage
    return;
  }
  try {
    const response = await axios.put(
      `http://localhost:8000/api/admin/scent-groups/${scentGroup.value.id}`,
      {
        name: scentGroup.value.name,
        color_code: scentGroup.value.color_code,
      },
      { validateStatus: (status) => status >= 200 && status < 300 }
    );
    toast.success(response.data.message || "Cập nhật nhóm hương thành công!"); // Thay thế addMessage
    resetForm();
    await fetchScentGroups();
  } catch (error) {
    console.error("Lỗi từ API:", error.response);
    const errors = error.response?.data?.errors;
    if (errors) {
      toast.error(Object.values(errors).flat().join(" ")); // Thay thế addMessage
    } else {
      toast.error(error.response?.data?.message || "Có lỗi khi cập nhật nhóm hương!"); // Thay thế addMessage
    }
  }
};

const cancelEdit = () => {
  resetForm();
};

const resetForm = () => {
  scentGroup.value = {
    id: null,
    name: "",
    color_code: "#000000",
  };
  isEditing.value = false;
};

const confirmSoftDeleteWithSwal = async (id) => {
  try {
    const result = await Swal.fire({
      title: 'Bạn có chắc muốn xóa mềm nhóm hương này?',
      text: 'Hành động này sẽ chuyển nhóm hương vào thùng rác, bạn có thể khôi phục nó sau này. Bạn vẫn muốn tiếp tục?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Có, xóa mềm!',
      cancelButtonText: 'Hủy'
    });

    if (result.isConfirmed) {
      const response = await axios.delete(`http://localhost:8000/api/admin/scent-groups/${id}`);
      await fetchScentGroups();
      toast.success(response.data.message || 'Xóa mềm nhóm hương thành công!'); // Thay thế Swal.fire success
    } else {
        toast.info("Thao tác xóa mềm đã bị hủy.");
    }
  } catch (error) {
    console.error('Lỗi khi xóa mềm nhóm hương:', error);
    const errorMessage = error.response?.data?.message || 'Không kết nối được tới server. Vui lòng kiểm tra mạng của bạn.';
    toast.error(`Xảy ra lỗi: ${errorMessage}`); // Thay thế Swal.fire error
  }
};

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
      responsive: true,
      destroy: true,
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
      drawCallback: () => {
        // No Bootstrap tooltips needed.
      },
    });
  } else {
    console.error("DataTables không được tải đúng cách hoặc không có jQuery.");
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

    await fetchScentGroups();
  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error.message, error.stack);
    // Có thể hiển thị một toast thông báo lỗi tải tài nguyên ở đây
    toast.error("Không thể tải đầy đủ tài nguyên cần thiết. Vui lòng thử lại.");
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

/* Specific styling for the DataTables generated elements if needed */
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

/* Custom styles for color box if needed */
</style>