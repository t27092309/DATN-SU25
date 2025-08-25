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
              <h1 class="text-2xl font-semibold">Quản lý bài viết</h1>
              <div class="flex gap-2">
                <router-link :to="{ name: 'PostTrash' }" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  <i class="fas fa-trash mr-2"></i> Thùng rác
                </router-link>
              </div>
            </div>

            <div class="card-body">
              <div class="flex flex-wrap -mx-4">
                <div class="w-full md:w-1/3 px-4 mb-8">
                  <h2 class="text-lg font-semibold mb-4">Thêm bài viết</h2>
                  <div class="form-group-item">
                    <label for="title" class="block text-sm font-medium text-gray-700">Tiêu đề</label>
                    <input v-model="post.title" type="text" id="title" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Nhập tiêu đề">
                  </div>
                  <div class="form-group-item">
                    <label for="content" class="block text-sm font-medium text-gray-700">Nội dung</label>
                    <textarea v-model="post.content" id="content" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Nhập nội dung"></textarea>
                  </div>
                  <div class="form-group-item">
                    <label for="image" class="block text-sm font-medium text-gray-700">Ảnh bài viết</label>
                    <input type="file" id="image" @change="handleImageUpload" accept="image/*" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <img v-if="post.imagePreview" :src="post.imagePreview" alt="Preview" class="mt-2 w-32 h-32 object-cover">
                  </div>
                  <div class="form-group-item">
                    <label for="is_active" class="block text-sm font-medium text-gray-700">Trạng thái</label>
                    <select v-model="post.is_active" id="is_active" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                      <option :value="true">Kích hoạt</option>
                      <option :value="false">Ẩn</option>
                    </select>
                  </div>
                  <button @click="addPost" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Thêm bài viết
                  </button>
                  <p v-if="formMessage" :class="formMessageClass" class="mt-2 text-sm">{{ formMessage }}</p>
                </div>
                <div class="w-full md:w-2/3 px-4 mb-8">
                  <div class="overflow-x-auto">
                    <table id="posts-table" class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiêu đề</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nội dung</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ảnh</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
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
import { onMounted, ref, nextTick } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import { useToast } from "vue-toastification";

const toast = useToast();
const posts = ref([]);
const post = ref({
  title: "",
  content: "",
  image: null,
  imagePreview: null,
  is_active: true,
});
const formMessage = ref("");
const formMessageClass = ref("");
const listMessage = ref("");
const listMessageClass = ref("");

const fetchPosts = async () => {
  try {
    const response = await axios.get("http://localhost:8000/api/admin/posts", {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });
    posts.value = Array.isArray(response.data) ? response.data : response.data.data || [];
    if (!posts.value.length) {
      listMessage.value = "Không có bài viết nào.";
      listMessageClass.value = "text-blue-500";
    } else {
      listMessage.value = "";
    }
    await destroyAndReinitializeDataTable();
  } catch (error) {
    listMessage.value = error.response?.data?.message || "Có lỗi khi tải danh sách bài viết!";
    listMessageClass.value = "text-red-500";
    console.error("Lỗi khi tải danh sách:", error);
    toast.error("Lỗi khi tải danh sách bài viết. Vui lòng thử lại!");
    await destroyAndReinitializeDataTable();
  }
};

const handleImageUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    post.value.image = file;
    post.value.imagePreview = URL.createObjectURL(file);
  }
};

const addPost = async () => {
  try {
    if (!post.value.title || !post.value.content) {
      formMessage.value = "Vui lòng điền đầy đủ tiêu đề và nội dung!";
      formMessageClass.value = "text-red-500";
      return;
    }

    const formData = new FormData();
    formData.append('title', post.value.title);
    formData.append('content', post.value.content);
    formData.append('is_active', post.value.is_active);
    if (post.value.image) {
      formData.append('image', post.value.image);
    }

    const response = await axios.post("http://localhost:8000/api/admin/posts", formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    formMessage.value = response.data.message || "Thêm bài viết thành công!";
    formMessageClass.value = "text-green-500";
    post.value = { title: "", content: "", image: null, imagePreview: null, is_active: true };
    await fetchPosts();
    toast.success("Thêm bài viết thành công!");
  } catch (error) {
    formMessage.value = error.response?.data?.message || "Có lỗi khi thêm bài viết!";
    formMessageClass.value = "text-red-500";
    console.error("Lỗi khi thêm bài viết:", error);
    toast.error("Có lỗi khi thêm bài viết. Vui lòng thử lại!");
  }
};

const deletePost = async (id) => {
  try {
    const result = await Swal.fire({
      title: "Bạn có chắc muốn xóa bài viết này?",
      text: "Bài viết sẽ được chuyển vào thùng rác!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Có, xóa!",
      cancelButtonText: "Hủy",
    });

    if (result.isConfirmed) {
      const response = await axios.delete(`http://localhost:8000/api/admin/posts/${id}`, {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      });
      await fetchPosts();
      toast.success(response.data.message || "Xóa bài viết thành công!");
    } else {
      toast.info("Đã hủy thao tác xóa bài viết.");
    }
  } catch (error) {
    console.error("Lỗi khi xóa bài viết:", error);
    const errorMessage = error.response?.data?.message || "Không kết nối được tới server.";
    toast.error(`Xảy ra lỗi khi xóa: ${errorMessage}`);
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
    dataTableInstance = jQuery("#posts-table").DataTable({
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
        emptyTable: "Không có bài viết nào",
      },
      columns: [
        { data: "title", defaultContent: "Không có" },
        { data: "content", defaultContent: "Không có" },
        {
          data: "image",
          render: (data) => data ? `<img src="/storage/${data}" class="w-16 h-16 object-cover" />` : "Không có",
        },
        {
          data: "is_active",
          render: (data) => (data ? "Kích hoạt" : "Ẩn"),
        },
        {
          data: null,
          render: (data, type, row) => `
            <div class="flex space-x-2 justify-center">
              <button type="button" title="Xóa" class="text-red-600 hover:text-red-900" data-action="delete" data-id="${row.id}">
                <i class="fa fa-trash"></i>
              </button>
            </div>
          `,
        },
      ],
      data: posts.value,
      drawCallback: () => {
        jQuery("#posts-table").off("click", "button[data-action]");
        jQuery("#posts-table").on("click", "button[data-action]", (e) => {
          const id = jQuery(e.currentTarget).data("id");
          const action = jQuery(e.currentTarget).data("action");
          if (action === "delete") {
            deletePost(id);
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

    await fetchPosts();
  } catch (error) {
    console.error("Lỗi khi tải tài nguyên:", error);
    listMessage.value = "Có lỗi khi tải bảng bài viết!";
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