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
            <a href="#">Thương hiệu</a>
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
            <div class="d-flex">
              <router-link :to="{ name: 'BrandTrash' }" class="btn btn-sm btn-warning me-2">
                <i class="fas fa-trash"></i> Thùng rác
              </router-link>
              <router-link :to="{ name: 'BrandAdd' }" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Thêm mới
              </router-link>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="brands-table" class="display table table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tên</th>
                  <th>Slug</th>
                  <th>Logo</th>
                  <th style="width: 15%">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="brand in brands" :key="brand.id">
                  <td>{{ brand.id }}</td>
                  <td>{{ brand.name || "Không có" }}</td>
                  <td>{{ brand.slug || "Không có" }}</td>
                  <td>
                    <img
                      v-if="brand.logo"
                      :src="brand.logo"
                      alt="Brand Logo"
                      style="width: 50px; height: 50px; object-fit: contain;"
                    />
                    <span v-else>Không có</span>
                  </td>
                  <td>
                    <div class="form-button-action">
                      <router-link
                        :to="{ name: 'BrandEdit', params: { id: brand.id } }"
                        data-bs-toggle="tooltip"
                        title="Sửa"
                        class="btn btn-link btn-primary"
                      >
                        <i class="fa fa-edit"></i> Sửa
                      </router-link>
                      <button
                        type="button"
                        data-bs-toggle="tooltip"
                        title="Xóa"
                        class="btn btn-link btn-danger"
                        @click="confirmActionWithSwal(brand.id, 'delete')"
                      >
                        <i class="fa fa-times"></i> Xóa
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!brands || brands.length === 0">
                  <td colspan="5" class="text-center">Không có thương hiệu nào.</td>
                </tr>
              </tbody>
            </table>
            <p v-if="listMessage" :class="listMessageClass">
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

const brands = ref([]);
const listMessage = ref("");
const listMessageClass = ref("");

// Khởi tạo biến DataTable instance
let dataTableInstance = null;

// Fetch all active brands
const fetchBrands = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/brands");
    brands.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    if (!brands.value.length) {
      listMessage.value = "Không có thương hiệu nào.";
      listMessageClass.value = "text-info";
    } else {
      listMessage.value = ""; // Clear message if data is loaded
    }
    // Re-initialize DataTables after data change
    await destroyAndReinitializeDataTable();

  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách thương hiệu!";
    listMessageClass.value = "text-danger";
    console.error("Lỗi khi tải danh sách:", error);
    await destroyAndReinitializeDataTable(); // Still re-init even on error to clear table if needed
  }
};

// Centralized function to handle actions with SweetAlert2 confirmation
const confirmActionWithSwal = async (id, type) => {
  let title, text, confirmButtonText, icon;
  let actionEndpoint = '';
  let successMessage = '';
  let successIcon = 'success';
  let method = '';

  if (type === 'delete') {
    title = 'Bạn có chắc muốn xóa thương hiệu này?';
    text = 'Thương hiệu sẽ được chuyển vào thùng rác. Bạn có thể khôi phục lại sau.';
    confirmButtonText = 'Có, xóa!';
    icon = 'warning';
    actionEndpoint = `http://localhost:8000/api/admin/brands/${id}`;
    successMessage = 'Xóa thương hiệu thành công và chuyển vào thùng rác!';
    successIcon = 'success';
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
      if (method === 'delete') {
        response = await axios.delete(actionEndpoint);
      } else {
        throw new Error('Unsupported HTTP method.');
      }

      await fetchBrands(); // Refresh the list after action

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

// Function to destroy and reinitialize DataTables
const destroyAndReinitializeDataTable = async () => {
  if (dataTableInstance) {
    dataTableInstance.destroy(); // Destroy the existing DataTable instance
    dataTableInstance = null; // Clear the instance
  }
  await nextTick(); // Wait for Vue to render the updated table
  if (typeof jQuery !== "undefined" && jQuery.fn.DataTable) {
    dataTableInstance = jQuery("#brands-table").DataTable({
      pageLength: 10, // Số hàng trên mỗi trang
      responsive: true,
      destroy: true, // Quan trọng: cho phép DataTables được khởi tạo lại
      drawCallback: () => {
        jQuery('[data-bs-toggle="tooltip"]').tooltip(); // Re-initialize Bootstrap tooltips
      },
    });
  } else {
    console.error("DataTables không được tải đúng cách hoặc không có jQuery.");
  }
};

onMounted(async () => {
  // Load scripts and stylesheets dynamically
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

    // Initial fetch and DataTable initialization
    await fetchBrands();

  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error.message, error.stack);
  }
});
</script>

<style scoped>
/* Các style giữ nguyên như BrandList.vue trước đó */
.container {
  max-width: 1200px;
  margin: 50px auto;
}
.form-button-action {
  display: flex;
  flex-direction: column; /* Stack buttons vertically */
  gap: 5px; /* Small gap between buttons */
  white-space: nowrap; /* Prevent text wrapping inside buttons */
}

.form-button-action .btn {
  width: 100%; /* Make buttons take full width of their container */
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
</style>