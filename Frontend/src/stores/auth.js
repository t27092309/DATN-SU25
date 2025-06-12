// src/stores/auth.js
import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('authToken') || null,
    user: localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null,
  }),
  getters: {
    // Trả về tên người dùng nếu có
    userName: (state) => state.user ? state.user.name : null,
    // Kiểm tra xem người dùng đã đăng nhập chưa
    isAuthenticated: (state) => !!state.token && !!state.user,
  },
  actions: {
    // Thiết lập token và user sau khi đăng nhập/đăng ký thành công
    setAuth(token, user) {
      this.token = token;
      this.user = user;
      localStorage.setItem('authToken', token);
      localStorage.setItem('user', JSON.stringify(user));
      // Đặt header Authorization mặc định cho Axios
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
    // Xử lý logout
    async logout() {
      try {
        // Nếu bạn có API logout ở backend để thu hồi token, hãy gọi nó ở đây
        // await axios.post('/api/logout'); // Ví dụ

        // Xóa state
        this.token = null;
        this.user = null;
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        // Xóa header Authorization khỏi Axios defaults
        delete axios.defaults.headers.common['Authorization'];
        console.log('Đã đăng xuất thành công.');
      } catch (error) {
        console.error('Lỗi khi đăng xuất:', error);
        // Dù có lỗi API, vẫn đảm bảo state cục bộ được reset
        this.token = null;
        this.user = null;
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        delete axios.defaults.headers.common['Authorization'];
      }
    },
    // Khôi phục token và user từ localStorage khi ứng dụng khởi động
    initializeAuth() {
      const storedToken = localStorage.getItem('authToken');
      const storedUser = localStorage.getItem('user');

      if (storedToken && storedUser) {
        this.token = storedToken;
        this.user = JSON.parse(storedUser);
        axios.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
      } else {
        this.token = null;
        this.user = null;
        delete axios.defaults.headers.common['Authorization'];
      }
    }
  },
});