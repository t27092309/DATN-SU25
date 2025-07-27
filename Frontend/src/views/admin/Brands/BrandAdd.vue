<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <div class="mb-6 flex justify-between items-center">
        <h3 class="text-3xl font-bold mb-3">
          {{ route.meta.title || "Thêm Thương Hiệu Mới" }}
        </h3>
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
            <router-link :to="{ name: 'BrandList' }" class="hover:text-blue-600">Thương hiệu</router-link>
          </li>
          <li class="separator">
            <i class="fas fa-chevron-right text-xs"></i>
          </li>
          <li class="nav-item">
            <a href="#" class="text-blue-600">{{ route.meta.title || "Thêm Mới" }}</a>
          </li>
        </ul>
      </div>

      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="mb-6 flex justify-between items-center">
          <h1 class="text-2xl font-semibold text-gray-800">{{ route.meta.title || "Thêm Thương Hiệu Mới" }}</h1>
          <router-link :to="{ name: 'BrandList' }"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
          </router-link>
        </div>

        <div class="card-body">
          <form @submit.prevent="addBrand">
            <div class="mb-4">
              <label for="brandName" class="block text-sm font-medium text-gray-700 mb-1">
                Tên Thương hiệu <span class="text-red-500">*</span>
              </label>
              <input type="text" id="brandName" v-model="brand.name" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
              <div v-if="errors.name" class="text-red-500 text-xs mt-1">
                {{ errors.name[0] }}
              </div>
            </div>

            <div class="mb-4">
              <label for="brandSlug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
              <input type="text" id="brandSlug" v-model="brand.slug"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
              <small class="mt-1 block text-xs text-gray-500">Tự động tạo nếu để trống, hoặc bạn có thể nhập thủ
                công.</small>
              <div v-if="errors.slug" class="text-red-500 text-xs mt-1">
                {{ errors.slug[0] }}
              </div>
            </div>

            <div class="mb-4">
              <label for="brandLogo" class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
              <input type="file" id="brandLogo" @change="handleLogoUpload"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              <small class="mt-1 block text-xs text-gray-500">Chọn file ảnh logo (JPG, PNG, GIF, WebP).</small>
              <div v-if="errors.logo" class="text-red-500 text-xs mt-1">
                {{ errors.logo[0] }}
              </div>
              <div v-if="brand.logoPreview" class="mt-3">
                <img :src="brand.logoPreview" alt="Logo Preview" class="w-24 h-24 object-contain rounded" />
              </div>
            </div>

            <div class="mb-6">
              <label for="brandDescription" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
              <Editor v-model="brand.description" :init="{
                height: 300,
                menubar: true,
                base_url: '/tinymce', // Đường dẫn gốc đến thư mục tinymce
                suffix: '.min', // Sử dụng file nén
                external_plugins: null, // Vô hiệu hóa plugin từ CDN
                plugins:
                  'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
                toolbar:
                  'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
              }" />
              <div v-if="errors.description" class="text-red-500 text-xs mt-1">
                {{ errors.description[0] }}
              </div>
            </div>

            <button type="submit" :disabled="loading"
              class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="loading" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                  viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
              </span>
              <span v-else><i class="fas fa-save mr-2"></i> Thêm mới</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from "vue";
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

// Biến cờ để kiểm soát việc tự động cập nhật slug
// true: slug đang được tự động tạo/cập nhật
// false: người dùng đã chỉnh sửa slug thủ công
const isSlugAutoGenerated = ref(true);

// Hàm slugify: Chuyển đổi chuỗi thành định dạng slug
const slugify = (text) => {
  if (!text) return '';
  text = text.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, "") // Bỏ dấu tiếng Việt
    .toLowerCase() // Chuyển sang chữ thường
    .trim() // Cắt khoảng trắng đầu/cuối
    .replace(/\s+/g, '-') // Thay khoảng trắng bằng dấu gạch ngang
    .replace(/[^\w-]+/g, '') // Loại bỏ ký tự không phải chữ, số, gạch ngang
    .replace(/--+/g, '-'); // Thay nhiều dấu gạch ngang bằng một dấu

  return text;
};

// Watcher để theo dõi sự thay đổi của brand.name
watch(() => brand.name, (newName) => {
  if (isSlugAutoGenerated.value) { // Chỉ tự động tạo slug nếu nó chưa bị chỉnh sửa thủ công
    brand.slug = slugify(newName);
  }
}, { immediate: true }); // Thêm immediate: true để chạy watcher một lần khi component được mount

// Watcher để theo dõi sự thay đổi của brand.slug (khi người dùng tự nhập)
watch(() => brand.slug, (newSlug, oldSlug) => {
  // Nếu slug mới khác với slug tự động tạo từ tên,
  // và slug mới không rỗng sau khi người dùng xóa nội dung
  // thì đánh dấu là người dùng đã tự chỉnh sửa
  if (newSlug !== slugify(brand.name) && newSlug !== '') {
    isSlugAutoGenerated.value = false;
  } else if (newSlug === '' && oldSlug === slugify(brand.name) && isSlugAutoGenerated.value === false) {
    // Nếu người dùng xóa sạch slug mà trước đó đã tự chỉnh sửa
    // và tên vẫn không đổi, thì cho phép tự động sinh lại
    isSlugAutoGenerated.value = true;
  }
});


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
  formData.append("slug", brand.slug); // Luôn gửi slug, dù tự động hay thủ công
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
@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.container {
  max-width: 900px;
  margin-left: auto;
  margin-right: auto;
}
</style>