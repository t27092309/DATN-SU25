<template>
  <div class="shipping-method-list-container">
    <h2>Quản lí phương thức vận chuyển</h2>

    <form @submit.prevent="saveShippingMethod" class="shipping-method-form">
      <h3>{{ editingMethod.id ? 'Sửa phương thức' : 'Thêm phương thức' }}</h3>
      
      <div class="form-group">
        <label for="methodName">Tên phương thức vận chuyển</label>
        <input type="text" id="methodName" v-model="editingMethod.name" placeholder="VD: Giao hàng tiêu chuẩn" required />
      </div>

      <div class="form-group">
        <label for="methodPrice">Giá (VNĐ)</label>
        <input type="number" id="methodPrice" v-model.number="editingMethod.price" placeholder="VD: 50000" step="0.01" min="0" required />
      </div>

      <div class="form-group is-active-toggle">
        <input type="checkbox" id="isActive" v-model="editingMethod.is_active" />
        <label for="isActive">Kích hoạt phương thức này</label>
      </div>

      <div class="form-group">
        <label for="deliveryUnit">Đơn vị thời gian giao hàng</label>
        <select id="deliveryUnit" v-model="editingMethod.delivery_time_unit">
          <option :value="null">-- Chọn đơn vị --</option>
          <option value="hours">Giờ</option>
          <option value="days">Ngày</option>
        </select>
      </div>

      <div class="form-group half-width" v-if="editingMethod.delivery_time_unit">
        <label for="deliveryMin">Thời gian giao tối thiểu</label>
        <input type="number" id="deliveryMin" v-model.number="editingMethod.delivery_time_min" min="0"
               :placeholder="`VD: 2 (${editingMethod.delivery_time_unit === 'hours' ? 'giờ' : 'ngày'})`" />
      </div>

      <div class="form-group half-width" v-if="editingMethod.delivery_time_unit">
        <label for="deliveryMax">Thời gian giao tối đa</label>
        <input type="number" id="deliveryMax" v-model.number="editingMethod.delivery_time_max" min="0"
               :placeholder="`VD: 4 (${editingMethod.delivery_time_unit === 'hours' ? 'giờ' : 'ngày'})`" />
      </div>
      <div class="form-actions">
        <button type="submit" class="btn primary-btn">{{ editingMethod.id ? 'Cập nhật phương thức' : 'Thêm phương thức' }}</button>
        <button type="button" @click="cancelEdit" v-if="editingMethod.id" class="btn secondary-btn">Hủy</button>
      </div>
    </form>

    <div class="shipping-methods-display">
      <p v-if="shippingMethods.length === 0 && !errorMessage" class="no-methods">Không tìm thấy phương thức vận chuyển nào, vui lòng thêm ở bên trên.</p>
      <ul class="shipping-method-items">
        <li v-for="method in shippingMethods" :key="method.id" class="shipping-method-item" :class="{ 'inactive-method': !method.is_active }">
          <div class="method-details">
            <div class="status-toggle-inline">
                <input type="checkbox" :id="`toggle-${method.id}`" v-model="method.is_active" @change="toggleActiveStatus(method)" />
                <label :for="`toggle-${method.id}`" class="toggle-label"></label>
            </div>
            
            <span class="method-name">{{ method.name }}</span>
            <span class="method-price">{{ parseFloat(method.price || 0).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' }) }}</span>
            
            <span class="method-status" :class="{ 'active': method.is_active, 'inactive': !method.is_active }">
              {{ method.is_active ? 'Đang hoạt động' : 'Không hoạt động' }}
            </span>

            <span class="method-delivery-time">
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
          <div class="item-actions">
            <button @click="editShippingMethod(method)" class="btn edit-btn">Sửa</button>
            <button @click="deleteShippingMethod(method.id)" class="btn delete-btn">Xóa</button>
          </div>
        </li>
      </ul>
    </div>

    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
  </div>
</template>

<script>
import axios from 'axios';

export default {
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
      errorMessage: '',
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
            delivery_time_min: method.delivery_time_min !== null ? parseInt(method.delivery_time_min) || 0 : null,
            delivery_time_max: method.delivery_time_max !== null ? parseInt(method.delivery_time_max) || 0 : null,
        }));
        this.errorMessage = '';
      } catch (error) {
        this.errorMessage = 'Lỗi khi tải danh sách phương thức vận chuyển: ' + (error.response?.data?.message || error.message);
        console.error('Lỗi khi tải danh sách phương thức vận chuyển:', error);
      }
    },
    
    async saveShippingMethod() {
      this.errorMessage = '';
      try {
        const payload = {
            ...this.editingMethod,
            price: parseFloat(this.editingMethod.price) || 0,
            is_active: Boolean(this.editingMethod.is_active), // Chuyển đổi sang boolean để gửi đi
            delivery_time_min: this.editingMethod.delivery_time_min !== null ? parseInt(this.editingMethod.delivery_time_min) || 0 : null,
            delivery_time_max: this.editingMethod.delivery_time_max !== null ? parseInt(this.editingMethod.delivery_time_max) || 0 : null,
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
                delivery_time_min: response.data.delivery_time_min !== null ? parseInt(response.data.delivery_time_min) || 0 : null,
                delivery_time_max: response.data.delivery_time_max !== null ? parseInt(response.data.delivery_time_max) || 0 : null,
            });
          }
        } else {
          response = await axios.post(this.apiUrl, payload);
          this.shippingMethods.push({ 
              ...response.data, 
              price: parseFloat(response.data.price) || 0,
              is_active: Boolean(response.data.is_active), // Đảm bảo là boolean
              delivery_time_min: response.data.delivery_time_min !== null ? parseInt(response.data.delivery_time_min) || 0 : null,
              delivery_time_max: response.data.delivery_time_max !== null ? parseInt(response.data.delivery_time_max) || 0 : null,
          });
        }
        this.resetForm();
      } catch (error) {
        this.errorMessage = 'Lỗi khi lưu phương thức vận chuyển: ' + (error.response?.data?.message || error.message);
        if (error.response?.data?.errors) {
            this.errorMessage += ' - ' + JSON.stringify(error.response.data.errors);
        }
        console.error('Lỗi khi lưu phương thức vận chuyển:', error.response || error);
      }
    },

    editShippingMethod(method) {
      this.editingMethod = { 
          ...method,
          price: parseFloat(method.price) || 0,
          is_active: Boolean(method.is_active), // Đảm bảo là boolean khi gán vào form
          delivery_time_min: method.delivery_time_min !== null ? parseInt(method.delivery_time_min) || 0 : null,
          delivery_time_max: method.delivery_time_max !== null ? parseInt(method.delivery_time_max) || 0 : null,
      }; 
      this.errorMessage = '';
    },

    async deleteShippingMethod(id) {
      this.errorMessage = '';
      if (confirm('Bạn có chắc chắn muốn xóa phương thức vận chuyển này không?')) {
        try {
          await axios.delete(`${this.apiUrl}/${id}`);
          this.shippingMethods = this.shippingMethods.filter(method => method.id !== id);
          this.resetForm();
        } catch (error) {
          this.errorMessage = 'Lỗi khi xóa phương thức vận chuyển: ' + (error.response?.data?.message || error.message);
          console.error('Lỗi khi xóa phương thức vận chuyển:', error);
        }
      }
    },

    // PHƯƠNG THỨC MỚI ĐỂ THAY ĐỔI TRẠNG THÁI NHANH
    async toggleActiveStatus(method) {
        this.errorMessage = '';
        try {
            // Gửi chỉ trường is_active lên để cập nhật
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
                    delivery_time_min: response.data.delivery_time_min !== null ? parseInt(response.data.delivery_time_min) || 0 : null,
                    delivery_time_max: response.data.delivery_time_max !== null ? parseInt(response.data.delivery_time_max) || 0 : null,
                });
            }
            // Không cần reset form vì chỉ thay đổi trạng thái
        } catch (error) {
            // Nếu có lỗi, hoàn tác lại trạng thái checkbox trên UI
            method.is_active = !method.is_active; 
            this.errorMessage = 'Lỗi khi cập nhật trạng thái: ' + (error.response?.data?.message || error.message);
            console.error('Lỗi khi cập nhật trạng thái:', error.response || error);
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
      this.errorMessage = '';
    },
  },
};
</script>

<style scoped>
/* Main container styling */
.shipping-method-list-container {
  max-width: 900px;
  margin: 90px auto;
  padding: 25px;
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}

h2 {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 30px;
  font-size: 2em;
  border-bottom: 2px solid #eee;
  padding-bottom: 15px;
}

h3 {
  color: #34495e;
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 1.5em;
}

/* Form styling */
.shipping-method-form {
  background-color: #f9fbfd;
  padding: 25px;
  border-radius: 8px;
  margin-bottom: 30px;
  border: 1px solid #e0e6ed;
  display: grid;
  grid-template-columns: 1fr;
  gap: 18px 20px;
}

.shipping-method-form .form-group:nth-child(1),
.shipping-method-form .form-group:nth-child(2) {
    grid-column: span 1;
}

/* Các trường min/max sẽ chiếm một nửa chiều rộng nếu có 2 cột */
.shipping-method-form .form-group.half-width {
    grid-column: span 1;
}

@media (min-width: 600px) {
  .shipping-method-form {
    grid-template-columns: 1fr 1fr;
  }

  /* Tên và giá chiếm cả 2 cột */
  .shipping-method-form .form-group:nth-child(1),
  .shipping-method-form .form-group:nth-child(2) {
      grid-column: span 2;
  }
  
  /* Trường is_active và đơn vị thời gian chiếm 1 cột */
  .shipping-method-form .form-group.is-active-toggle,
  .shipping-method-form .form-group:nth-child(4) { /* deliveryUnit */
      grid-column: span 2; /* Để đơn vị và active luôn nằm trên một dòng riêng */
  }

  .shipping-method-form .form-group.half-width {
      grid-column: span 1;
  }
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #555;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select {
  width: calc(100% - 22px);
  padding: 12px;
  border: 1px solid #cdd4da;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  box-sizing: border-box;
}

.form-group input[type="text"]:focus,
.form-group input[type="number"]:focus,
.form-group select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
  outline: none;
}

/* Styling cho checkbox is_active trong form */
.form-group.is-active-toggle {
    display: flex;
    align-items: center;
    gap: 10px; /* Khoảng cách giữa checkbox và label */
    margin-bottom: 18px;
}

.form-group.is-active-toggle input[type="checkbox"] {
    width: auto; /* Để checkbox không bị kéo giãn */
    margin-bottom: 0; /* Bỏ margin dưới của input */
    transform: scale(1.3); /* Phóng to checkbox */
    cursor: pointer;
}

.form-group.is-active-toggle label {
    margin-bottom: 0;
    cursor: pointer;
}


.form-actions {
  text-align: right;
  margin-top: 25px;
  grid-column: span 2;
}

/* Button styling */
.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
  transition: background-color 0.3s ease, transform 0.2s ease;
  min-width: 100px;
}

.btn:hover {
  transform: translateY(-2px);
}

.primary-btn {
  background-color: #007bff;
  color: white;
  margin-left: 10px;
}

.primary-btn:hover {
  background-color: #0056b3;
}

.secondary-btn {
  background-color: #6c757d;
  color: white;
}

.secondary-btn:hover {
  background-color: #5a6268;
}

.edit-btn {
  background-color: #28a745;
  color: white;
  margin-right: 8px;
}

.edit-btn:hover {
  background-color: #218838;
}

.delete-btn {
  background-color: #dc3545;
  color: white;
}

.delete-btn:hover {
  background-color: #c82333;
}

/* List styling */
.shipping-methods-display {
  margin-top: 30px;
}

.no-methods {
  text-align: center;
  color: #666;
  font-style: italic;
  padding: 20px;
  border: 1px dashed #ddd;
  border-radius: 5px;
  margin-bottom: 20px;
}

.shipping-method-items {
  list-style: none;
  padding: 0;
}

.shipping-method-item {
  background-color: #fefefe;
  border: 1px solid #e9e9e9;
  padding: 15px 20px;
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.shipping-method-item:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

/* Highlight inactive methods */
.shipping-method-item.inactive-method {
    opacity: 0.6;
    background-color: #f5f5f5;
    border-color: #e0e0e0;
}

.method-details {
  flex-grow: 1;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}

/* Style cho checkbox nhanh trạng thái */
.status-toggle-inline {
    position: relative;
    display: inline-block;
    width: 40px; /* Chiều rộng của toggle */
    height: 20px; /* Chiều cao của toggle */
    margin-right: 10px;
}

.status-toggle-inline input[type="checkbox"] {
    opacity: 0; /* Ẩn checkbox gốc */
    width: 0;
    height: 0;
}

.status-toggle-inline .toggle-label {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
    border-radius: 20px; /* Bo tròn */
}

.status-toggle-inline .toggle-label:before {
    position: absolute;
    content: "";
    height: 16px; /* Chiều cao của nút tròn */
    width: 16px; /* Chiều rộng của nút tròn */
    left: 2px; /* Khoảng cách từ lề trái */
    bottom: 2px; /* Khoảng cách từ lề dưới */
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
    border-radius: 50%; /* Bo tròn hoàn toàn */
}

.status-toggle-inline input[type="checkbox"]:checked + .toggle-label {
    background-color: #28a745; /* Màu xanh khi bật */
}

.status-toggle-inline input[type="checkbox"]:focus + .toggle-label {
    box-shadow: 0 0 1px #28a745;
}

.status-toggle-inline input[type="checkbox"]:checked + .toggle-label:before {
    -webkit-transform: translateX(20px); /* Di chuyển nút tròn sang phải */
    -ms-transform: translateX(20px);
    transform: translateX(20px);
}


.method-name {
  font-weight: 700;
  color: #333;
  font-size: 1.1em;
}

.method-price {
  background-color: #e0f7fa;
  color: #00796b;
  padding: 5px 8px;
  border-radius: 4px;
  font-size: 0.9em;
  font-weight: 600;
}

.method-status { /* Style cho trạng thái hoạt động */
    font-size: 0.85em;
    padding: 5px 8px;
    border-radius: 4px;
    font-weight: 600;
    white-space: nowrap; /* Ngăn không cho chữ bị ngắt dòng */
}

.method-status.active {
    background-color: #d4edda;
    color: #28a745;
}

.method-status.inactive {
    background-color: #f8d7da;
    color: #dc3545;
}

.method-delivery-time {
    font-size: 0.85em;
    color: #555;
    background-color: #e6ffe6;
    padding: 5px 8px;
    border-radius: 4px;
    font-weight: 500;
}

.item-actions {
  display: flex;
  gap: 8px;
}

/* Error message styling */
.error-message {
  color: #c0392b;
  background-color: #fbecec;
  border: 1px solid #ebccd1;
  padding: 15px;
  border-radius: 8px;
  margin-top: 25px;
  font-weight: 500;
  text-align: center;
}
</style>