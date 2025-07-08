<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">{{ route.meta.title || 'Thùng Rác Thương Hiệu' }}</h3>
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
            <router-link :to="{ name: 'BrandList' }">Thương hiệu</router-link>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>
          <li class="nav-item">
            <a href="#">{{ route.meta.title || 'Thùng Rác' }}</a>
          </li>
        </ul>
      </div>
      <div class="card">
        <div class="card-header">
          <div class="card-title d-flex justify-content-between align-items-center">
            <h1>{{ route.meta.title || 'Thùng Rác Thương Hiệu' }}</h1>
            <router-link :to="{ name: 'BrandList' }" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </router-link>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="trashed-brands-table" class="display table table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Tên</th>
                  <th>Slug</th>
                  <th>Logo</th>
                  <th>Ngày xóa</th>
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
                      v-if="brand.logo_url"
                      :src="brand.logo_url"
                      alt="Brand Logo"
                      style="width: 50px; height: 50px; object-fit: contain;"
                    />
                    <span v-else>Không có</span>
                  </td>
                  <td>
                    {{
                      brand.deleted_at
                        ? new Date(brand.deleted_at).toLocaleString()
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
                        @click="confirmActionWithSwal(brand.id, 'restore')"
                      >
                        <i class="fas fa-undo"></i> Khôi phục
                      </button>
                      <button
                        type="button"
                        data-bs-toggle="tooltip"
                        title="Xóa vĩnh viễn"
                        class="btn btn-link btn-danger"
                        @click="confirmActionWithSwal(brand.id, 'force')"
                      >
                        <i class="fas fa-trash-alt"></i> Xóa vĩnh viễn
                      </button>
                    </div>
                  </td>
                </tr>
                </tbody>
            </table>
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
const listMessage = ref(""); // Giữ lại nếu bạn muốn hiển thị tin nhắn tùy chỉnh khác
const listMessageClass = ref("");

let dataTableInstance = null;

const fetchTrashedBrands = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/brands/trashed");
    
    // Lưu ý: DataTables cần mảng dữ liệu thô.
    // Nếu API của bạn trả về object có 'data' bên trong, hãy lấy 'response.data.data'
    // Nếu API trả về trực tiếp mảng, hãy lấy 'response.data'
    brands.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    
    // Không cần quản lý listMessage thủ công nữa, DataTables sẽ lo
    // if (!brands.value.length) {
    //   listMessage.value = "Không có thương hiệu nào trong thùng rác.";
    //   listMessageClass.value = "text-info";
    // } else {
    //   listMessage.value = "";
    // }

    // Rất quan trọng: Đảm bảo DataTables được khởi tạo sau khi dữ liệu đã render vào DOM
    await destroyAndReinitializeDataTable();

  } catch (error) {
    console.error("Lỗi khi tải danh sách thùng rác:", error);
    // Vẫn gọi để đảm bảo table được destroy, tránh lỗi khi dữ liệu rỗng
    await destroyAndReinitializeDataTable(); 
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách thương hiệu trong thùng rác!";
    listMessageClass.value = "text-danger";
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

const destroyAndReinitializeDataTable = async () => {
  if (dataTableInstance) {
    dataTableInstance.destroy(); // Hủy bỏ instance DataTables hiện có
    dataTableInstance = null; // Xóa tham chiếu
  }
  await nextTick(); // Đợi Vue cập nhật DOM

  // Chỉ khởi tạo DataTables nếu phần tử bảng tồn tại
  const tableElement = document.getElementById("trashed-brands-table");
  if (tableElement && typeof jQuery !== "undefined" && jQuery.fn.DataTable) {
    dataTableInstance = jQuery(tableElement).DataTable({
      pageLength: 10,
      responsive: true,
      destroy: true, // Cho phép khởi tạo lại
      order: [], // Bỏ sắp xếp mặc định để không gây lỗi với cột "Hành động"
      columnDefs: [ // Định nghĩa cột để DataTables xử lý chính xác
        { "orderable": false, "targets": [3, 5] } // Cột Logo (index 3) và Hành động (index 5) không thể sắp xếp
      ],
      drawCallback: () => {
        // Khởi tạo lại tooltips sau mỗi lần vẽ lại bảng
        jQuery('[data-bs-toggle="tooltip"]').tooltip();
      },
    });
  } else {
    console.warn("Không tìm thấy bảng 'trashed-brands-table' hoặc DataTables/jQuery chưa sẵn sàng.");
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

    await fetchTrashedBrands();

  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error.message, error.stack);
  }
});
</script>

<style scoped>
/* (Không có thay đổi trong phần style so với trước) */
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
</style>