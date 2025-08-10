<template>
  <div class="container mx-auto px-4 py-8">
    <div class="page-inner">
      <h2 class="text-center text-3xl font-bold text-gray-800 mb-8 border-b-2 pb-4">Quản lý phương thức vận chuyển</h2>

      <form @submit.prevent="saveShippingMethod" class="bg-white shadow-md rounded-lg p-6 mb-8 border border-gray-200">
        <h3 class="text-2xl font-semibold text-gray-700 mb-6">{{ editingMethod.id ? 'Sửa phương thức' : 'Thêm phương thức' }}</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
          <div class="md:col-span-2">
            <label for="methodName" class="block text-sm font-medium text-gray-700 mb-1">Tên phương thức vận chuyển</label>
            <input type="text" id="methodName" v-model="editingMethod.name" placeholder="VD: Giao hàng tiêu chuẩn" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
          </div>

          <div class="md:col-span-2">
            <label for="methodPrice" class="block text-sm font-medium text-gray-700 mb-1">Giá (VNĐ)</label>
            <input type="number" id="methodPrice" v-model.number="editingMethod.price" placeholder="VD: 50000" step="0.01" min="0" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
          </div>

          <div class="md:col-span-2 flex items-center mt-2 mb-4">
            <input type="checkbox" id="isActive" v-model="editingMethod.is_active"
                   class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer" />
            <label for="isActive" class="ml-2 block text-base font-medium text-gray-700 cursor-pointer">Kích hoạt phương thức này</label>
          </div>

          <div class="md:col-span-2">
            <label for="deliveryUnit" class="block text-sm font-medium text-gray-700 mb-1">Đơn vị thời gian giao hàng</label>
            <select id="deliveryUnit" v-model="editingMethod.delivery_time_unit"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
              <option :value="null">-- Chọn đơn vị --</option>
              <option value="hours">Giờ</option>
              <option value="days">Ngày</option>
            </select>
          </div>

          <div v-if="editingMethod.delivery_time_unit" class="grid grid-cols-1 md:grid-cols-2 gap-4 md:col-span-2">
            <div>
              <label for="deliveryMin" class="block text-sm font-medium text-gray-700 mb-1">Thời gian giao tối thiểu</label>
              <input type="number" id="deliveryMin" v-model.number="editingMethod.delivery_time_min" min="0"
                     :placeholder="`VD: 2 (${editingMethod.delivery_time_unit === 'hours' ? 'giờ' : 'ngày'})`"
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
            </div>

            <div>
              <label for="deliveryMax" class="block text-sm font-medium text-gray-700 mb-1">Thời gian giao tối đa</label>
              <input type="number" id="deliveryMax" v-model.number="editingMethod.delivery_time_max" min="0"
                     :placeholder="`VD: 4 (${editingMethod.delivery_time_unit === 'hours' ? 'giờ' : 'ngày'})`"
                     class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button type="submit"
                  class="px-5 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
            {{ editingMethod.id ? 'Cập nhật phương thức' : 'Thêm phương thức' }}
          </button>
          <button type="button" @click="cancelEdit" v-if="editingMethod.id"
                  class="px-5 py-2 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150">
            Hủy
          </button>
        </div>
      </form>

      <div class="bg-white shadow-md rounded-lg p-6">
        <h3 class="text-2xl font-semibold text-gray-700 mb-6">Danh sách phương thức vận chuyển</h3>
        <p v-if="shippingMethods.length === 0 && !errorMessage" class="text-center text-gray-500 italic p-5 border border-dashed rounded-md mb-6">
          Không tìm thấy phương thức vận chuyển nào, vui lòng thêm ở bên trên.
        </p>

        <ul class="space-y-4">
          <li v-for="method in shippingMethods" :key="method.id"
              class="flex flex-col md:flex-row items-start md:items-center justify-between p-4 border border-gray-200 rounded-lg shadow-sm transition-all duration-200 ease-in-out"
              :class="{ 'opacity-60 bg-gray-50 border-gray-300': !method.is_active, 'hover:shadow-md': true, 'hover:translate-y-[-2px]': true }">

            <div class="flex items-center flex-wrap gap-x-4 gap-y-2 mb-3 md:mb-0 md:flex-1">
              <div class="relative inline-block w-10 h-5 mr-3">
                <input type="checkbox"
                       :id="`toggle-${method.id}`"
                       v-model="method.is_active"
                       @change="toggleActiveStatus(method)"
                       class="sr-only peer" />

                <label :for="`toggle-${method.id}`"
                       class="block cursor-pointer bg-gray-300 h-5 rounded-full transition-colors duration-300 ease-in-out
                             peer-checked:bg-green-500
                             peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-offset-2 peer-focus:ring-blue-500">
                  <span class="absolute left-0 top-0 h-5 w-5 bg-white rounded-full shadow transition-transform duration-300 ease-in-out
                               peer-checked:translate-x-full custom-toggle-knob"></span>
                </label>
              </div>
              <span class="font-bold text-lg text-gray-900 flex-grow-0 min-w-[150px]">{{ method.name }}</span>
              <span class="bg-blue-50 text-blue-800 text-sm font-semibold px-2.5 py-0.5 rounded-full inline-flex items-center justify-center min-w-[100px]">{{ parseFloat(method.price || 0).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' }) }}</span>

              <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full inline-flex items-center"
                    :class="{ 'bg-green-100 text-green-800': method.is_active, 'bg-red-100 text-red-800': !method.is_active }">
                {{ method.is_active ? 'Đang hoạt động' : 'Không hoạt động' }}
              </span>

              <span class="text-sm text-gray-600 bg-gray-100 px-2.5 py-0.5 rounded-full">
                Giao hàng:
                <span v-if="method.delivery_time_min !== null && method.delivery_time_max !== null && method.delivery_time_unit">
                  {{ method.delivery_time_min }} - {{ method.delivery_time_max }} {{ method.delivery_time_unit === 'hours' ? 'giờ' : 'ngày' }}
                </span>
                <span v-else-if="method.delivery_time_min !== null && method.delivery_time_unit">
                  Trong {{ method.delivery_time_min }} {{ method.delivery_time_unit === 'hours' ? 'giờ' : 'ngày' }}
                </span>
                <span v-else>N/A</span>
              </span>
            </div>

            <div class="flex space-x-2 mt-3 md:mt-0">
              <button @click="editShippingMethod(method)"
                      class="px-3 py-1.5 text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150">
                Sửa
              </button>
              <button @click="deleteShippingMethod(method.id)"
                      class="px-3 py-1.5 text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition ease-in-out duration-150">
                Xóa
              </button>
            </div>
          </li>
        </ul>
      </div>

      <p v-if="errorMessage" class="mt-8 p-4 bg-red-100 text-red-700 border border-red-300 rounded-md text-center font-medium">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import { useToast } from "vue-toastification"; // Import useToast

export default {
  setup() { // Sử dụng setup để dùng composition API, đặc biệt là useToast
    const toast = useToast(); // Khởi tạo instance của toast
    return { toast }; // Trả về toast để có thể dùng trong template và options API
  },
  data() {
    return {
      shippingMethods: [],
      editingMethod: {
        id: null,
        name: '',
        price: 0,
        is_active: true, // Mặc định là true khi thêm mới
        delivery_time_unit: null,
        delivery_time_min: null,
        delivery_time_max: null,
      },
      errorMessage: '', // Giữ lại errorMessage cho những lỗi chung hoặc validate trên form
      apiUrl: 'http://localhost:8000/api/admin/shipping-methods',
    };
  },
  created() {
    this.fetchShippingMethods();
  },
  methods: {
    async fetchShippingMethods() {
      try {
        const response = await axios.get(this.apiUrl);
        this.shippingMethods = response.data.map(method => ({
            ...method,
            price: parseFloat(method.price) || 0,
            is_active: Boolean(method.is_active), // Đảm bảo là boolean
            delivery_time_min: method.delivery_time_min !== null ? parseInt(method.delivery_time_min) || null : null,
            delivery_time_max: method.delivery_time_max !== null ? parseInt(method.delivery_time_max) || null : null,
        }));
        this.errorMessage = '';
      } catch (error) {
        this.errorMessage = 'Lỗi khi tải danh sách phương thức vận chuyển: ' + (error.response?.data?.message || error.message);
        console.error('Lỗi khi tải danh sách phương thức vận chuyển:', error);
        // Sử dụng toast.error thay vì Swal.fire cho lỗi tải
        this.toast.error(this.errorMessage);
      }
    },

    async saveShippingMethod() {
      this.errorMessage = ''; // Xóa lỗi cũ
      try {
        const payload = {
            ...this.editingMethod,
            price: parseFloat(this.editingMethod.price) || 0,
            is_active: Boolean(this.editingMethod.is_active), // Chuyển đổi sang boolean để gửi đi
            delivery_time_min: this.editingMethod.delivery_time_min !== null ? parseInt(this.editingMethod.delivery_time_min) || null : null,
            delivery_time_max: this.editingMethod.delivery_time_max !== null ? parseInt(this.editingMethod.delivery_time_max) || null : null,
        };

        if (!payload.delivery_time_unit) {
            payload.delivery_time_min = null;
            payload.delivery_time_max = null;
        }

        let response;
        if (payload.id) {
          response = await axios.put(`${this.apiUrl}/${payload.id}`, payload);
          const index = this.shippingMethods.findIndex(m => m.id === response.data.id);
          if (index !== -1) {
            this.shippingMethods.splice(index, 1, {
                ...response.data,
                price: parseFloat(response.data.price) || 0,
                is_active: Boolean(response.data.is_active), // Đảm bảo là boolean
                delivery_time_min: response.data.delivery_time_min !== null ? parseInt(response.data.delivery_time_min) || null : null,
                delivery_time_max: response.data.delivery_time_max !== null ? parseInt(response.data.delivery_time_max) || null : null,
            });
          }
          this.toast.success('Cập nhật phương thức vận chuyển thành công!'); // Sử dụng toast.success
        } else {
          response = await axios.post(this.apiUrl, payload);
          this.shippingMethods.push({
              ...response.data,
              price: parseFloat(response.data.price) || 0,
              is_active: Boolean(response.data.is_active), // Đảm bảo là boolean
              delivery_time_min: response.data.delivery_time_min !== null ? parseInt(response.data.delivery_time_min) || null : null,
              delivery_time_max: response.data.delivery_time_max !== null ? parseInt(response.data.delivery_time_max) || null : null,
          });
          this.toast.success('Thêm phương thức vận chuyển thành công!'); // Sử dụng toast.success
        }
        this.resetForm();
      } catch (error) {
        let message = 'Lỗi khi lưu phương thức vận chuyển: ' + (error.response?.data?.message || error.message);
        if (error.response?.data?.errors) {
            message += ' - ' + Object.values(error.response.data.errors).flat().join(' '); // Hiển thị chi tiết lỗi validate
        }
        this.errorMessage = message; // Giữ lại errorMessage để hiển thị dưới form nếu muốn
        console.error('Lỗi khi lưu phương thức vận chuyển:', error.response || error);
        this.toast.error(message); // Sử dụng toast.error
      }
    },

    editShippingMethod(method) {
      this.editingMethod = {
          ...method,
          price: parseFloat(method.price) || 0,
          is_active: Boolean(method.is_active), // Đảm bảo là boolean khi gán vào form
          delivery_time_min: method.delivery_time_min !== null ? parseInt(method.delivery_time_min) || null : null,
          delivery_time_max: method.delivery_time_max !== null ? parseInt(method.delivery_time_max) || null : null,
      };
      this.errorMessage = '';
    },

    async deleteShippingMethod(id) {
      this.errorMessage = '';
      const result = await Swal.fire({
        title: 'Bạn có chắc chắn muốn xóa?',
        text: "Bạn sẽ không thể hoàn tác hành động này!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Có, xóa nó!',
        cancelButtonText: 'Hủy'
      });

      if (result.isConfirmed) {
        try {
          await axios.delete(`${this.apiUrl}/${id}`);
          this.shippingMethods = this.shippingMethods.filter(method => method.id !== id);
          this.resetForm();
          this.toast.success('Phương thức vận chuyển đã được xóa.'); // Sử dụng toast.success
        } catch (error) {
          let message = 'Lỗi khi xóa phương thức vận chuyển: ' + (error.response?.data?.message || error.message);
          if (error.response?.data?.errors) {
              message += ' - ' + Object.values(error.response.data.errors).flat().join(' ');
          }
          this.errorMessage = message;
          console.error('Lỗi khi xóa phương thức vận chuyển:', error);
          this.toast.error(message); // Sử dụng toast.error
        }
      } else {
        this.toast.info('Thao tác xóa đã bị hủy.'); // Thông báo hủy xóa
      }
    },

    async toggleActiveStatus(method) {
        this.errorMessage = '';
        const originalStatus = !method.is_active; // Lưu trạng thái ban đầu để hoàn tác nếu lỗi
        try {
            const response = await axios.put(`${this.apiUrl}/${method.id}`, {
                is_active: method.is_active
            });
            // Cập nhật lại đối tượng trong danh sách với dữ liệu trả về từ API
            const index = this.shippingMethods.findIndex(m => m.id === response.data.id);
            if (index !== -1) {
                this.shippingMethods.splice(index, 1, {
                    ...response.data,
                    price: parseFloat(response.data.price) || 0,
                    is_active: Boolean(response.data.is_active),
                    delivery_time_min: response.data.delivery_time_min !== null ? parseInt(response.data.delivery_time_min) || null : null,
                    delivery_time_max: response.data.delivery_time_max !== null ? parseInt(response.data.delivery_time_max) || null : null,
                });
            }
            this.toast.success('Cập nhật trạng thái thành công!'); // Sử dụng toast.success
        } catch (error) {
            // Nếu có lỗi, hoàn tác lại trạng thái checkbox trên UI
            method.is_active = originalStatus;
            let message = 'Lỗi khi cập nhật trạng thái: ' + (error.response?.data?.message || error.message);
            if (error.response?.data?.errors) {
                message += ' - ' + Object.values(error.response.data.errors).flat().join(' ');
            }
            this.errorMessage = message;
            console.error('Lỗi khi cập nhật trạng thái:', error.response || error);
            this.toast.error(message); // Sử dụng toast.error
        }
    },

    cancelEdit() {
      this.resetForm();
    },

    resetForm() {
      this.editingMethod = {
        id: null,
        name: '',
        price: 0,
        is_active: true, // Mặc định là true khi reset
        delivery_time_unit: null,
        delivery_time_min: null,
        delivery_time_max: null,
      };
      this.errorMessage = ''; // Xóa lỗi khi reset form
    },
  },
};
</script>

<style>
/* Đảm bảo các style này vẫn giữ nguyên cho toggle switch nếu bạn muốn */
.custom-toggle-knob {
  transition: transform 0.3s ease-in-out;
}

input[id^='toggle-']:checked ~ label .custom-toggle-knob {
  transform: translateX(100%);
}
</style>