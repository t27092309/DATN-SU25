<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">
          {{ route.meta.title || "Thêm Thương Hiệu Mới" }}
        </h3>
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
            <a href="#">{{ route.meta.title || "Thêm Mới" }}</a>
          </li>
        </ul>
      </div>
      <div class="card">
        <div class="card-header">
          <div
            class="card-title d-flex justify-content-between align-items-center"
          >
            <h1>{{ route.meta.title || "Thêm Thương Hiệu Mới" }}</h1>
            <router-link
              :to="{ name: 'BrandList' }"
              class="btn btn-sm btn-outline-primary"
            >
              <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </router-link>
          </div>
        </div>
        <div class="card-body">
          <form @submit.prevent="addBrand">
            <div class="mb-3">
              <label for="brandName" class="form-label"
                >Tên Thương hiệu <span class="text-danger">*</span></label
              >
              <input
                type="text"
                class="form-control"
                id="brandName"
                v-model="brand.name"
                required
              />
              <div v-if="errors.name" class="text-danger">
                {{ errors.name[0] }}
              </div>
            </div>
            <div class="mb-3">
              <label for="brandSlug" class="form-label">Slug</label>
              <input
                type="text"
                class="form-control"
                id="brandSlug"
                v-model="brand.slug"
              />
              <small class="form-text text-muted"
                >Tự động tạo nếu để trống, hoặc bạn có thể nhập thủ công.</small
              >
              <div v-if="errors.slug" class="text-danger">
                {{ errors.slug[0] }}
              </div>
            </div>
            <div class="mb-3">
              <label for="brandLogo" class="form-label">Logo</label>
              <input
                type="file"
                class="form-control"
                id="brandLogo"
                @change="handleLogoUpload"
              />
              <small class="form-text text-muted"
                >Chọn file ảnh logo (JPG, PNG, GIF, WebP).</small
              >
              <div v-if="errors.logo" class="text-danger">
                {{ errors.logo[0] }}
              </div>
              <div v-if="brand.logoPreview" class="mt-2">
                <img
                  :src="brand.logoPreview"
                  alt="Logo Preview"
                  style="width: 100px; height: 100px; object-fit: contain"
                />
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
                  plugins:
                    'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount', // Chuỗi plugin
                  toolbar:
                    'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                }"
              />
              <div v-if="errors.description" class="text-danger">
                {{ errors.description[0] }}
              </div>
            </div>
            <button type="submit" class="btn btn-success" :disabled="loading">
              <span
                v-if="loading"
                class="spinner-border spinner-border-sm"
                role="status"
                aria-hidden="true"
              ></span>
              <span v-else><i class="fas fa-save"></i> Thêm mới</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from "sweetalert2";
import Editor from "@tinymce/tinymce-vue";

const route = useRoute();
const router = useRouter();

const brand = reactive({
  name: "",
  slug: "",
  logo: null,
  logoPreview: "",
  description: "",
});

const errors = ref({});
const loading = ref(false);

const handleLogoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    brand.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      brand.logoPreview = e.target.result;
    };
    reader.readAsDataURL(file);
  } else {
    brand.logo = null;
    brand.logoPreview = "";
  }
};

const addBrand = async () => {
  loading.value = true;
  errors.value = {};

  const formData = new FormData();
  formData.append("name", brand.name);
  if (brand.slug) {
    formData.append("slug", brand.slug);
  }
  if (brand.logo) {
    formData.append("logo", brand.logo);
  }
  if (brand.description) {
    formData.append("description", brand.description);
  }

  try {
    const response = await axios.post(
      "http://localhost:8000/api/admin/brands",
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );

    Swal.fire({
      title: "Thành công!",
      text: response.data.message || "Thương hiệu đã được thêm mới.",
      icon: "success",
      confirmButtonText: "Đã hiểu!",
    }).then(() => {
      router.push({ name: "BrandList" });
    });
  } catch (error) {
    console.error("Lỗi khi thêm thương hiệu:", error);
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors;
    } else {
      Swal.fire({
        icon: "error",
        title: "Lỗi!",
        text:
          error.response?.data?.message ||
          "Có lỗi xảy ra khi thêm thương hiệu.",
        confirmButtonText: "Đã hiểu!",
      });
    }
  } finally {
    loading.value = false;
  }
};
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
