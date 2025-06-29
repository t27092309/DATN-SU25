<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Nhóm Hương</h3>
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
              <div class="card-title">Quản lý nhóm hương</div>
            </div>
            <div class="card-body">
              <div class="row">
                <!-- Form thêm nhóm hương -->
                <div class="col-md-6 col-lg-4">
                  <form @submit.prevent="addScentGroup" class="mb-5">
                    <div class="form-group">
                      <h5 class="card-title">Thêm mới nhóm hương</h5>
                    </div>
                    <div class="form-group">
                      <label for="name">Tên nhóm hương</label>
                      <input
                        type="text"
                        v-model="scentGroup.name"
                        class="form-control"
                        id="name"
                        placeholder="Nhập tên nhóm hương"
                        required
                      />
                      <small class="form-text text-muted">Ví dụ: Hương hoa, Hương gỗ</small>
                    </div>
                    <div class="form-group">
                      <label for="color_code">Mã màu</label>
                      <input
                        type="color"
                        v-model="scentGroup.color_code"
                        class="form-control color-picker"
                        id="color_code"
                        required
                      />
                      <small class="form-text text-muted">Chọn màu từ bảng màu</small>
                    </div>
                    <div class="card-action">
                      <button type="submit" class="btn btn-primary">Thêm nhóm hương</button>
                    </div>
                    <p v-if="addMessage" :class="addMessageClass">{{ addMessage }}</p>
                  </form>
                </div>

                <!-- Bảng hiển thị nhóm hương -->
                <div class="col-md-6 col-lg-8">
                  <div class="table-responsive">
                    <table id="add-row" class="display table table-bordered">
                      <thead>
                        <tr>
                          <th>Tên</th>
                          <th>Mã màu</th>
                          <th>Ngày tạo</th>
                          <th>Ngày cập nhật</th>
                          <th style="width: 10%">Hành động</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="scentGroup in scentGroups" :key="scentGroup.id">
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
                            <div class="form-button-action">
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title="Chỉnh sửa nhóm hương"
                                class="btn btn-link btn-primary btn-lg"
                                @click="editScentGroup(scentGroup.id)"
                              >
                                <i class="fa fa-edit"></i>
                              </button>
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title="Xóa"
                                class="btn btn-link btn-danger"
                                @click="openDeleteModal(scentGroup.id)"
                              >
                                <i class="fa fa-times"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                        <tr v-if="!scentGroups || scentGroups.length === 0">
                          <td colspan="5" class="text-center">Không có nhóm hương nào</td>
                        </tr>
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
            <p>Bạn có chắc muốn xóa nhóm hương này?</p>
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

<script setup>
import { onMounted, nextTick, ref, watch } from "vue";
import { useRouter } from "vue-router"; // Import useRouter
import axios from "axios";
import slugify from "slugify";

// Khai báo các biến reactive
const scentGroup = ref({
  name: "",
  color_code: "#000000",
});
const scentGroups = ref([]);
const addMessage = ref("");
const addMessageClass = ref("");
const listMessage = ref("");
const listMessageClass = ref("");
const showDeleteModal = ref(false);
const deleteId = ref(null);

const router = useRouter(); // Khởi tạo router

watch(
  () => scentGroup.value.name,
  (newName) => {
    scentGroup.value.name = slugify(newName, {
      lower: true,
      strict: true,
      locale: "vi",
    });
  }
);

const fetchScentGroups = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/scent-groups");
    console.log("Dữ liệu từ API:", response.data);
    scentGroups.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    if (!scentGroups.value.length) {
      listMessage.value = "Không có nhóm hương nào.";
      listMessageClass.value = "text-info";
    }
  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách nhóm hương!";
    listMessageClass.value = "text-danger";
    console.error("Lỗi khi tải danh sách:", error);
  }
};

const addScentGroup = async () => {
  if (!scentGroup.value.name || !scentGroup.value.color_code) {
    addMessage.value = "Vui lòng nhập tên nhóm hương và chọn màu!";
    addMessageClass.value = "text-danger";
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
    console.log("Phản hồi từ API:", response);
    await fetchScentGroups();
    addMessage.value = response.data.message || "Thêm nhóm hương thành công!";
    addMessageClass.value = "text-success";
    scentGroup.value.name = "";
    scentGroup.value.color_code = "#000000";
  } catch (error) {
    console.error("Lỗi từ API:", error.response);
    const errors = error.response?.data?.errors;
    if (errors) {
      addMessage.value = Object.values(errors).flat().join(" ");
    } else {
      addMessage.value = error.response?.data?.message || "Có lỗi khi thêm nhóm hương!";
    }
    addMessageClass.value = "text-danger";
  }
};

const editScentGroup = (id) => {
  console.log("Click chỉnh sửa, ID:", id);
  router.push(`/scent-group/edit/${id}`);
};

const openDeleteModal = (id) => {
  deleteId.value = id;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  try {
    const response = await axios.delete(`http://localhost:8000/api/admin/scent-groups/${deleteId.value}`);
    await fetchScentGroups();
    listMessage.value = response.data.message || "Xóa nhóm hương thành công!";
    listMessageClass.value = "text-success";
    showDeleteModal.value = false;
    deleteId.value = null;
  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi xóa nhóm hương!";
    listMessageClass.value = "text-danger";
    showDeleteModal.value = false;
  }
};

onMounted(async () => {
  await fetchScentGroups();
  const scripts = [
    "https://code.jquery.com/jquery-3.7.1.min.js",
    "https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js",
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js", // Thêm Bootstrap JS cho tooltip
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

    // Thêm Font Awesome
    const fontAwesomeLink = document.createElement("link");
    fontAwesomeLink.rel = "stylesheet";
    fontAwesomeLink.href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css";
    document.head.appendChild(fontAwesomeLink);

    await nextTick();
    if (typeof jQuery !== "undefined" && jQuery.fn.DataTable) {
      jQuery("#add-row").DataTable({
        pageLength: 10,
        responsive: true,
        drawCallback: () => {
          jQuery('[data-bs-toggle="tooltip"]').tooltip(); // Khởi tạo tooltip
        },
      });
    } else {
      console.error("DataTables không được tải đúng cách.");
    }
  } catch (error) {
    console.error("Lỗi khi tải script:", error.message, error.stack);
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
.color-picker {
  height: 40px;
  padding: 5px;
  cursor: pointer;
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
</style>