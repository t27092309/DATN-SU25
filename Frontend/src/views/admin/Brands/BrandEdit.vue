<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">{{ route.meta.title || 'Sửa Thương Hiệu' }}</h3>
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
            <a href="#">{{ route.meta.title || 'Sửa' }}</a>
          </li>
        </ul>
      </div>
      <div class="card">
        <div class="card-header">
          <div class="card-title d-flex justify-content-between align-items-center">
            <h1>{{ route.meta.title || 'Sửa Thương Hiệu' }}</h1>
            <router-link :to="{ name: 'BrandList' }" class="btn btn-sm btn-outline-primary">
              <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </router-link>
          </div>
        </div>
        <div class="card-body">
          <div v-if="loadingBrand" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Đang tải...</span>
            </div>
            <p class="mt-2">Đang tải thông tin thương hiệu...</p>
          </div>
          <div v-else-if="!brand.id" class="text-center py-5 text-danger">
            <p>Không tìm thấy thương hiệu hoặc có lỗi khi tải dữ liệu.</p>
          </div>
          <form v-else @submit.prevent="updateBrand">
            <div class="mb-3">
              <label for="brandName" class="form-label">Tên Thương hiệu <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="brandName" v-model="brand.name" required>
              <div v-if="errors.name" class="text-danger">{{ errors.name[0] }}</div>
            </div>
            <div class="mb-3">
              <label for="brandSlug" class="form-label">Slug</label>
              <input type="text" class="form-control" id="brandSlug" v-model="brand.slug">
              <small class="form-text text-muted">Tự động tạo nếu để trống, hoặc bạn có thể nhập thủ công.</small>
              <div v-if="errors.slug" class="text-danger">{{ errors.slug[0] }}</div>
            </div>
            <div class="mb-3">
              <label for="brandLogo" class="form-label">Logo Hiện Tại</label>
              <div v-if="brand.logo_url" class="d-flex align-items-center mb-2">
                <img :src="brand.logo_url" alt="Current Logo" style="width: 100px; height: 100px; object-fit: contain; margin-right: 15px;">
                <button type="button" class="btn btn-sm btn-outline-danger" @click="confirmRemoveLogo">
                  <i class="fas fa-times-circle"></i> Xóa Logo này
                </button>
              </div>
              <p v-else class="text-muted">Không có logo hiện tại.</p>
              
              <label for="newBrandLogo" class="form-label mt-3">Chọn Logo Mới (nếu muốn thay đổi)</label>
              <input type="file" class="form-control" id="newBrandLogo" @change="handleLogoUpload">
              <small class="form-text text-muted">Chọn file ảnh logo (JPG, PNG, GIF, WebP). Sẽ thay thế logo hiện tại.</small>
              <div v-if="errors.logo" class="text-danger">{{ errors.logo[0] }}</div>
              <div v-if="brand.logoPreview" class="mt-2">
                <p>Logo Mới Sẽ Tải Lên:</p>
                <img :src="brand.logoPreview" alt="New Logo Preview" style="width: 100px; height: 100px; object-fit: contain;">
              </div>
            </div>
            <div class="mb-3">
              <label for="brandDescription" class="form-label">Mô tả</label>
              <Editor
                v-model="brand.description"
                :init="{
                  height: 300,
                  menubar: true,
                  base_url: '/tinymce', // Đường dẫn gốc đến thư mục tinymce
                  suffix: '.min', // Sử dụng file nén
                  external_plugins: null, // Vô hiệu hóa plugin từ CDN
                  plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount', // Chuỗi plugin
                  toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
                }"
              />
              <div v-if="errors.description" class="text-danger">{{ errors.description[0] }}</div>
            </div>
            <button type="submit" class="btn btn-success" :disabled="loadingUpdate">
              <span v-if="loadingUpdate" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              <span v-else><i class="fas fa-save"></i> Cập nhật</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from 'sweetalert2';
import Editor from '@tinymce/tinymce-vue'; // Import component TinyMCE

const route = useRoute();
const router = useRouter();

const brand = reactive({
  id: null,
  name: '',
  slug: '',
  logo_url: '', // Để hiển thị logo hiện tại từ server
  newLogo: null, // Để lưu trữ file logo mới khi người dùng chọn
  logoPreview: '', // Để hiển thị preview của logo mới
  description: ''
});

const errors = ref({});
const loadingBrand = ref(true); // Trạng thái tải dữ liệu thương hiệu
const loadingUpdate = ref(false); // Trạng thái cập nhật thương hiệu
const removeLogoFlag = ref(false); // Cờ để biết có xóa logo hiện tại không

// Hàm lấy thông tin thương hiệu
const fetchBrand = async () => {
  loadingBrand.value = true;
  try {
    const brandId = route.params.id;
    if (!brandId) {
      throw new Error("Không tìm thấy ID thương hiệu trong URL.");
    }
    const response = await axios.get(`http://localhost:8000/api/admin/brands/${brandId}`);
    const data = response.data;
    
    brand.id = data.id;
    brand.name = data.name;
    brand.slug = data.slug;
    brand.logo_url = data.logo; // API trả về URL đầy đủ
    brand.description = data.description;

  } catch (error) {
    console.error("Lỗi khi tải thông tin thương hiệu:", error);
    Swal.fire({
      icon: 'error',
      title: 'Lỗi!',
      text: error.response?.data?.message || 'Không thể tải thông tin thương hiệu. Vui lòng thử lại.',
      confirmButtonText: 'Đã hiểu!'
    }).then(() => {
      router.push({ name: 'BrandList' }); // Quay lại danh sách nếu lỗi
    });
  } finally {
    loadingBrand.value = false;
  }
};

// Hàm xử lý tải lên logo mới
const handleLogoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    brand.newLogo = file;
    removeLogoFlag.value = false; // Nếu chọn logo mới thì không xóa cái cũ nữa
    const reader = new FileReader();
    reader.onload = (e) => {
      brand.logoPreview = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    brand.newLogo = null;
    brand.logoPreview = '';
  }
};

// Hàm xác nhận xóa logo
const confirmRemoveLogo = () => {
  Swal.fire({
    title: 'Bạn có chắc chắn?',
    text: 'Logo hiện tại sẽ bị xóa vĩnh viễn và không thể khôi phục!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Có, xóa nó!',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      removeLogoFlag.value = true; // Đặt cờ để báo hiệu xóa logo
      brand.logo_url = null; // Xóa hiển thị logo hiện tại
      brand.newLogo = null; // Đảm bảo không có logo mới được chọn
      brand.logoPreview = ''; // Xóa preview logo mới
      Swal.fire(
        'Đã đặt để xóa!',
        'Logo sẽ bị xóa khi bạn lưu cập nhật.',
        'info'
      );
    }
  });
};

// Hàm cập nhật thương hiệu
const updateBrand = async () => {
  loadingUpdate.value = true;
  errors.value = {};

  const formData = new FormData();
  formData.append('name', brand.name);
  formData.append('slug', brand.slug || ''); // Gửi slug rỗng nếu người dùng muốn tự động tạo
  formData.append('description', brand.description || '');

  // Quan trọng: Đối với Laravel, khi cập nhật (PUT/PATCH) có file, bạn cần thêm method spoofing
  formData.append('_method', 'PUT'); 

  if (brand.newLogo) {
    formData.append('logo', brand.newLogo);
  } else if (removeLogoFlag.value) {
    formData.append('clear_logo', 1); // Gửi cờ này để controller biết cần xóa logo
  }

  try {
    const response = await axios.post(`http://localhost:8000/api/admin/brands/${brand.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });

    Swal.fire({
      title: 'Thành công!',
      text: response.data.message || 'Thương hiệu đã được cập nhật thành công.',
      icon: 'success',
      confirmButtonText: 'Đã hiểu!'
    }).then(() => {
      router.push({ name: 'BrandList' }); // Chuyển về trang danh sách sau khi cập nhật thành công
    });

  } catch (error) {
    console.error("Lỗi khi cập nhật thương hiệu:", error);
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors;
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Lỗi!',
        text: error.response?.data?.message || 'Có lỗi xảy ra khi cập nhật thương hiệu.',
        confirmButtonText: 'Đã hiểu!'
      });
    }
  } finally {
    loadingUpdate.value = false;
  }
};

onMounted(() => {
  fetchBrand();
});
</script>

<style scoped>
.container {
  max-width: 900px;
  margin: 50px auto;
}
.card-header .card-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.form-label {
  font-weight: bold;
}
.text-danger {
  font-size: 0.875em;
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