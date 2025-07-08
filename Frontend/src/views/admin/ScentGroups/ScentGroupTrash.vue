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
            <router-link :to="{ name: 'nhom-huong' }">Nhóm Hương</router-link>
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
            <router-link :to="{ name: 'nhom-huong' }" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </router-link>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="trashed-scent-groups-table" class="display table table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tên</th>
                  <th>Mã màu</th>
                  <th>Ngày tạo</th>
                  <th>Ngày cập nhật</th>
                  <th>Ngày xóa</th>
                  <th style="width: 15%">Hành động</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="scentGroup in scentGroups" :key="scentGroup.id">
                  <td>{{ scentGroup.id }}</td>
                  <td>{{ scentGroup.name || "Không có" }}</td>
                  <td>
                    <span
                      :style="{ backgroundColor: scentGroup.color_code }"
                      class="color-box"
                    ></span>
                    {{ scentGroup.color_code || "Không có" }}
                  </td>
                  <td>
                    {{
                      scentGroup.created_at
                        ? new Date(scentGroup.created_at).toLocaleString()
                        : "Không có"
                    }}
                  </td>
                  <td>
                    {{
                      scentGroup.updated_at
                        ? new Date(scentGroup.updated_at).toLocaleString()
                        : "Không có"
                    }}
                  </td>
                  <td>
                    {{
                      scentGroup.deleted_at
                        ? new Date(scentGroup.deleted_at).toLocaleString()
                        : "Không có"
                    }}
                  </td>
                  <td>
                    <div class="form-button-action">
                      <button
                        type="button"
                        data-bs-toggle="tooltip"
                        title="Khôi phục"
                        class="btn btn-link btn-success"
                        @click="confirmActionWithSwal(scentGroup.id, 'restore')"
                      >
                        <i class="fas fa-undo"></i> Khôi phục
                      </button>
                      <button
                        type="button"
                        data-bs-toggle="tooltip"
                        title="Xóa vĩnh viễn"
                        class="btn btn-link btn-danger"
                        @click="confirmActionWithSwal(scentGroup.id, 'force')"
                      >
                        <i class="fas fa-trash-alt"></i> Xóa vĩnh viễn
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!scentGroups || scentGroups.length === 0">
                  <td colspan="7" class="text-center">
                    Không có nhóm hương nào trong thùng rác.
                  </td>
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
import Swal from 'sweetalert2'; // Import SweetAlert2

const route = useRoute();
const router = useRouter();

const scentGroups = ref([]);
const listMessage = ref("");
const listMessageClass = ref("");

// Fetch trashed scent groups
const fetchTrashedScentGroups = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/scent-groups/trashed");
    scentGroups.value = Array.isArray(response.data) ? response.data : response.data.data || [];

    if (!scentGroups.value.length) {
      listMessage.value = "Không có nhóm hương nào trong thùng rác.";
      listMessageClass.value = "text-info";
    } else {
      listMessage.value = ""; // Clear message if data is loaded
    }
    // Re-initialize DataTables after data change
    await destroyAndReinitializeDataTable();

  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách nhóm hương trong thùng rác!";
    listMessageClass.value = "text-danger";
    console.error("Lỗi khi tải danh sách thùng rác:", error);
    await destroyAndReinitializeDataTable(); // Still re-init even on error to clear table if needed
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
    successIcon = 'info'; // Use info icon for force delete success to differentiate
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
        response = await axios.put(actionEndpoint); // PUT for restore
      } else {
        response = await axios.delete(actionEndpoint); // DELETE for force delete
      }

      // Re-fetch trashed groups after successful action
      await fetchTrashedScentGroups();

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
let dataTableInstance = null;
const destroyAndReinitializeDataTable = async () => {
  if (dataTableInstance) {
    dataTableInstance.destroy(); // Destroy the existing DataTable instance
    dataTableInstance = null; // Clear the instance
  }
  await nextTick(); // Wait for Vue to render the updated table
  if (typeof jQuery !== "undefined" && jQuery.fn.DataTable) {
    dataTableInstance = jQuery("#trashed-scent-groups-table").DataTable({ // Use unique ID
      pageLength: 10,
      responsive: true,
      destroy: true,
      drawCallback: () => {
        jQuery('[data-bs-toggle="tooltip"]').tooltip();
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

    // Initial fetch for trashed scent groups
    await fetchTrashedScentGroups();

  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error.message, error.stack);
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
  flex-direction: column; /* Stack buttons vertically */
  gap: 5px; /* Small gap between buttons */
  white-space: nowrap; /* Prevent text wrapping inside buttons */
}

/* Specific styles for buttons to make them look good when stacked */
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
.color-box {
  display: inline-block;
  width: 20px;
  height: 20px;
  vertical-align: middle;
  margin-right: 10px;
  border: 1px solid #ced4da;
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