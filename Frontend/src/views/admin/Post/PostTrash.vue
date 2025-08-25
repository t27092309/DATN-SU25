<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <div class="mb-6">
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
            <span class="font-semibold">{{ $route.meta.title }}</span>
          </li>
        </ul>
      </div>

      <div class="flex flex-wrap -mx-4">
        <div class="w-full px-4">
          <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
              <h1 class="text-2xl font-semibold">Thùng rác bài viết</h1>
              <div class="flex gap-2">
                <router-link :to="{ name: 'PostManager' }"
                  class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  <i class="fas fa-arrow-left mr-2"></i> Quay lại
                </router-link>
              </div>
            </div>

            <div class="card-body">
              <div class="flex flex-wrap -mx-4">
                <div class="w-full px-4 mb-8">
                  <div class="overflow-x-auto">
                    <table id="trash-table" class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiêu đề</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày xóa</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 15%">Hành động</th>
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, nextTick, ref } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";

const toast = useToast();
const posts = ref([]);
const listMessage = ref("");
const listMessageClass = ref("");

// Fetch trashed posts
const fetchTrashedPosts = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/posts/trashed");
    posts.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    if (!posts.value.length) {
      listMessage.value = "Không có bài viết nào trong thùng rác.";
      listMessageClass.value = "text-blue-500";
    } else {
      listMessage.value = "";
    }
    await destroyAndReinitializeDataTable();
  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách bài viết đã xóa!";
    listMessageClass.value = "text-red-500";
    console.error("Lỗi khi tải danh sách:", error);
    toast.error("Lỗi khi tải danh sách bài viết đã xóa. Vui lòng thử lại!");
    await destroyAndReinitializeDataTable();
  }
};

// Restore post
const restorePost = async (id) => {
  try {
    const result = await Swal.fire({
      title: "Bạn có chắc muốn khôi phục bài viết này?",
      text: "Bài viết sẽ được khôi phục và hiển thị lại trong danh sách.",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Có, khôi phục!",
      cancelButtonText: "Hủy",
    });

    if (result.isConfirmed) {
      const response = await axios.put(`http://localhost:8000/api/admin/posts/${id}/restore`);
      await fetchTrashedPosts();
      toast.success(response.data.message || "Khôi phục bài viết thành công!");
    } else {
      toast.info("Đã hủy thao tác khôi phục bài viết.");
    }
  } catch (error) {
    console.error("Lỗi khi khôi phục bài viết:", error);
    const errorMessage = error.response?.data?.message || "Không kết nối được tới server.";
    toast.error(`Xảy ra lỗi khi khôi phục: ${errorMessage}`);
  }
};

// Permanently delete post
const forceDeletePost = async (id) => {
  try {
    const result = await Swal.fire({
      title: "Bạn có chắc muốn xóa vĩnh viễn bài viết này?",
      text: "Hành động này không thể hoàn tác!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Có, xóa vĩnh viễn!",
      cancelButtonText: "Hủy",
    });

    if (result.isConfirmed) {
      const response = await axios.delete(`http://localhost:8000/api/admin/posts/${id}/force`);
      await fetchTrashedPosts();
      toast.success(response.data.message || "Xóa vĩnh viễn bài viết thành công!");
    } else {
      toast.info("Đã hủy thao tác xóa vĩnh viễn bài viết.");
    }
  } catch (error) {
    console.error("Lỗi khi xóa vĩnh viễn bài viết:", error);
    const errorMessage = error.response?.data?.message || "Không kết nối được tới server.";
    toast.error(`Xảy ra lỗi khi xóa vĩnh viễn: ${errorMessage}`);
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
    dataTableInstance = jQuery("#trash-table").DataTable({
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
        emptyTable: "Không có bài viết nào trong thùng rác",
      },
      columns: [
        { data: "title", defaultContent: "Không có" },
        { data: "content", defaultContent: "Không có" },
        {
          data: "deleted_at",
          render: (data) => (data ? new Date(data).toLocaleString("vi-VN") : "Không có"),
        },
        {
          data: null,
          render: (data, type, row) => `
            <div class="flex space-x-2 justify-center">
              <button type="button" title="Khôi phục" class="text-green-600 hover:text-green-900" data-action="restore" data-id="${row.id}">
                <i class="fa fa-undo"></i>
              </button>
              <button type="button" title="Xóa vĩnh viễn" class="text-red-600 hover:text-red-900" data-action="force-delete" data-id="${row.id}">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          `,
        },
      ],
      data: posts.value,
      drawCallback: () => {
        jQuery("#trash-table").off("click", "button[data-action]");
        jQuery("#trash-table").on("click", "button[data-action]", (e) => {
          const id = jQuery(e.currentTarget).data("id");
          const action = jQuery(e.currentTarget).data("action");

          if (action === "restore") {
            restorePost(id);
          } else if (action === "force-delete") {
            forceDeletePost(id);
          }
        });
      },
    });
  } else {
    console.error("DataTables is not loaded correctly or jQuery is missing.");
    listMessage.value = "Không thể khởi tạo bảng!";
    listMessageClass.value = "text-red-500";
    toast.error("Không thể tải hoặc khởi tạo bảng dữ liệu.");
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
      script.onerror = () => reject(new Error(`Không thể tải script: ${src}`));
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

    await fetchTrashedPosts();
  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error);
    listMessage.value = "Có lỗi khi tải bảng bài viết đã xóa!";
    listMessageClass.value = "text-red-500";
    toast.error("Có lỗi khi tải tài nguyên. Vui lòng thử lại!");
  }
});
</script>

<style scoped>
.form-group-item {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  margin-bottom: 1rem;
}

.dataTables_wrapper .dataTables_length {
  margin-bottom: 0.5rem;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
  display: inline-block;
  vertical-align: middle;
}

.dataTables_wrapper .dataTables_length select {
  margin-right: 0.5em;
  display: inline-block;
  width: auto;
  min-width: unset;
}
</style>