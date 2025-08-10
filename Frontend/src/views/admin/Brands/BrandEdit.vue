<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <div class="mb-6 flex justify-between items-center">
        <h3 class="text-3xl font-bold mb-3">
          {{ route.meta.title || 'Sửa Thương Hiệu' }}
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
            <a href="#" class="text-blue-600">{{ route.meta.title || 'Sửa' }}</a>
          </li>
        </ul>
      </div>

      <div class="bg-white shadow-md rounded-lg p-6">
        <div class="mb-6 flex justify-between items-center">
          <h1 class="text-2xl font-semibold text-gray-800">{{ route.meta.title || 'Sửa Thương Hiệu' }}</h1>
          <router-link :to="{ name: 'BrandList' }"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại danh sách
          </router-link>
        </div>

        <div class="card-body">
          <div v-if="loadingBrand" class="text-center py-5">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-t-4 border-blue-600 border-r-transparent">
              <span class="sr-only">Đang tải...</span>
            </div>
            <p class="mt-2 text-gray-600">Đang tải thông tin thương hiệu...</p>
          </div>
          <div v-else-if="!brand.id" class="text-center py-5 text-red-500">
            <p>Không tìm thấy thương hiệu hoặc có lỗi khi tải dữ liệu.</p>
          </div>
          <form v-else @submit.prevent="updateBrand">
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
              <small class="mt-1 block text-xs text-gray-500"
                >Tự động tạo nếu để trống, hoặc bạn có thể nhập thủ công.</small
              >
              <div v-if="errors.slug" class="text-red-500 text-xs mt-1">
                {{ errors.slug[0] }}
              </div>
            </div>

            <div class="mb-4">
              <label for="brandLogo" class="block text-sm font-medium text-gray-700 mb-1">Logo Hiện Tại</label>
              <div v-if="brand.logo_url" class="flex items-center mb-2">
                <img :src="brand.logo_url" alt="Current Logo" class="w-24 h-24 object-contain mr-4 rounded" />
                <button type="button" @click="confirmRemoveLogo"
                  class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                  <i class="fas fa-times-circle mr-1"></i> Xóa Logo này
                </button>
              </div>
              <p v-else class="text-gray-500 text-sm">Không có logo hiện tại.</p>

              <label for="newBrandLogo" class="block text-sm font-medium text-gray-700 mt-4 mb-1">
                Chọn Logo Mới (nếu muốn thay đổi)
              </label>
              <input type="file" id="newBrandLogo" @change="handleLogoUpload"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
              <small class="mt-1 block text-xs text-gray-500"
                >Chọn file ảnh logo (JPG, PNG, GIF, WebP). Sẽ thay thế logo hiện tại.</small
              >
              <div v-if="errors.logo" class="text-red-500 text-xs mt-1">
                {{ errors.logo[0] }}
              </div>
              <div v-if="brand.logoPreview" class="mt-3">
                <p class="text-sm font-medium text-gray-700 mb-1">Logo Mới Sẽ Tải Lên:</p>
                <img :src="brand.logoPreview" alt="New Logo Preview" class="w-24 h-24 object-contain rounded" />
              </div>
            </div>

            <div class="mb-6">
              <label for="brandDescription" class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
              <Editor
                v-model="brand.description"
                :init="{
                  height: 300,
                  menubar: true,
                  base_url: '/tinymce',
                  suffix: '.min',
                  external_plugins: null,
                  plugins:
                    'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
                  toolbar:
                    'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
                }"
              />
              <div v-if="errors.description" class="text-red-500 text-xs mt-1">
                {{ errors.description[0] }}
              </div>
            </div>

            <button type="submit" :disabled="loadingUpdate"
              class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="loadingUpdate" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
              </span>
              <span v-else><i class="fas fa-save mr-2"></i> Cập nhật</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from "vue"; // Import 'watch'
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import Swal from 'sweetalert2';
import Editor from '@tinymce/tinymce-vue';

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
const loadingBrand = ref(true);
const loadingUpdate = ref(false);
const removeLogoFlag = ref(false);

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
  // Khi tên thay đổi, nếu slug đang ở trạng thái tự động tạo, thì cập nhật
  if (isSlugAutoGenerated.value) {
    brand.slug = slugify(newName);
  }
});

// Watcher để theo dõi sự thay đổi của brand.slug (khi người dùng tự nhập)
// Logic này giúp xác định khi nào người dùng đã tự tay chỉnh sửa slug.
watch(() => brand.slug, (newSlug, oldSlug) => {
  const generatedSlugFromName = slugify(brand.name);

  // Nếu slug mới khác với slug tự động tạo từ tên
  // VÀ slug mới không rỗng (tránh trường hợp người dùng xóa để tự động tạo lại)
  if (newSlug !== generatedSlugFromName && newSlug !== '') {
    isSlugAutoGenerated.value = false; // Đánh dấu là người dùng đã tự chỉnh sửa
  } else if (newSlug === '' && oldSlug === generatedSlugFromName && !isSlugAutoGenerated.value) {
    // Nếu người dùng xóa sạch slug, và giá trị cũ là slug tự động,
    // và trước đó đã là trạng thái không tự động sinh (người dùng tự sửa rồi xóa)
    // thì cho phép tự động sinh lại.
    isSlugAutoGenerated.value = true;
  }
});


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
    brand.logo_url = data.logo;
    brand.description = data.description;

    // Quan trọng: Sau khi tải dữ liệu, kiểm tra xem slug có phải tự động sinh ra không.
    // Nếu slug tải về khớp với slug được tạo từ tên, thì đặt cờ là tự động.
    // Ngược lại, nếu người dùng đã tùy chỉnh slug trên server, thì đặt cờ là không tự động.
    isSlugAutoGenerated.value = (brand.slug === slugify(brand.name));

  } catch (error) {
    console.error("Lỗi khi tải thông tin thương hiệu:", error);
    Swal.fire({
      icon: 'error',
      title: 'Lỗi!',
      text: error.response?.data?.message || 'Không thể tải thông tin thương hiệu. Vui lòng thử lại.',
      confirmButtonText: 'Đã hiểu!'
    }).then(() => {
      router.push({ name: 'BrandList' });
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
    removeLogoFlag.value = false;
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
      removeLogoFlag.value = true;
      brand.logo_url = null; // Xóa hiển thị logo hiện tại trên UI
      brand.newLogo = null;
      brand.logoPreview = '';
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
  formData.append('slug', brand.slug || '');
  formData.append('description', brand.description || '');

  formData.append('_method', 'PUT');

  if (brand.newLogo) {
    formData.append('logo', brand.newLogo);
  } else if (removeLogoFlag.value) {
    formData.append('clear_logo', 1);
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
      router.push({ name: 'BrandList' });
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
/* Spinner animation */
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

/* Base container and page layout,
   though most of this is now handled by Tailwind classes in the template */
.container {
  max-width: 900px; /* Equivalent to max-w-3xl or custom width in Tailwind */
  margin-left: auto; /* mx-auto in Tailwind */
  margin-right: auto; /* mx-auto in Tailwind */
}

/* No other scoped styles needed as Tailwind handles most of the styling */
</style>