<template>
  <div class="p-6 bg-gray-100 min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-gray-900">Quản lý Người dùng</h1>
      </div>
      
      <!-- Thống kê -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 p-4 rounded-lg">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-blue-600">Tổng số</p>
              <p class="text-2xl font-bold text-blue-900">{{ stats.total || 0 }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-green-50 p-4 rounded-lg">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-green-600">Khách hàng</p>
              <p class="text-2xl font-bold text-green-900">{{ stats.customers || 0 }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-yellow-50 p-4 rounded-lg">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-yellow-600">Nhân viên</p>
              <p class="text-2xl font-bold text-yellow-900">{{ stats.staff || 0 }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-red-50 p-4 rounded-lg">
          <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-red-600">Admin</p>
              <p class="text-2xl font-bold text-red-900">{{ stats.admins || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Bộ lọc -->
      <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
          <input 
            v-model="searchQuery"
            type="text"
            placeholder="Tìm kiếm theo tên hoặc email..."
            class="w-full border border-gray-300 rounded-md px-3 py-2"
            @input="filterUsers"
          >
        </div>
        <div class="flex gap-2">
          <button 
            @click="filterByRole('all')"
            :class="{'bg-blue-500 text-white': selectedRole === 'all', 'bg-gray-200': selectedRole !== 'all'}"
            class="px-4 py-2 rounded-md font-medium transition-colors"
          >
            Tất cả
          </button>
          <button 
            @click="filterByRole('user')"
            :class="{'bg-green-500 text-white': selectedRole === 'user', 'bg-gray-200': selectedRole !== 'user'}"
            class="px-4 py-2 rounded-md font-medium transition-colors"
          >
            Khách hàng
          </button>
          <button 
            @click="filterByRole('staff')"
            :class="{'bg-yellow-500 text-white': selectedRole === 'staff', 'bg-gray-200': selectedRole !== 'staff'}"
            class="px-4 py-2 rounded-md font-medium transition-colors"
          >
            Nhân viên
          </button>
          <button 
            @click="filterByRole('admin')"
            :class="{'bg-red-500 text-white': selectedRole === 'admin', 'bg-gray-200': selectedRole !== 'admin'}"
            class="px-4 py-2 rounded-md font-medium transition-colors"
          >
            Admin
          </button>
        </div>
      </div>
    </div>

    <!-- Danh sách Users -->
    <div class="bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-xl font-semibold mb-4">Danh sách Người dùng</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thông tin</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in filteredUsers" :key="user.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <img 
                      :src="user.avatar || '/default-avatar.png'" 
                      :alt="user.name"
                      class="h-10 w-10 rounded-full object-cover"
                    >
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-500">{{ user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <select 
                  v-model="user.role"
                  @change="updateUserRole(user)"
                  class="border border-gray-300 rounded-md px-3 py-1 text-sm"
                  :disabled="user.id === currentUserId"
                >
                  <option value="user">Khách hàng</option>
                  <option value="staff">Nhân viên</option>
                  <option value="admin">Admin</option>
                </select>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button 
                  @click="viewUserDetails(user)"
                  class="text-indigo-600 hover:text-indigo-900 mr-3"
                >
                  Chi tiết
                </button>
                <button 
                  v-if="user.id !== currentUserId"
                  @click="deleteUser(user.id)"
                  class="text-red-600 hover:text-red-900"
                >
                  Xóa
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Phân trang -->
      <div class="mt-6 flex justify-between items-center">
        <div class="text-sm text-gray-700">
          Hiển thị {{ pagination.from || 0 }} đến {{ pagination.to || 0 }} trong tổng số {{ pagination.total || 0 }} người dùng
        </div>
        <div class="flex space-x-2">
          <button 
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50"
          >
            Trước
          </button>
          <span class="px-3 py-1 text-sm">
            Trang {{ pagination.current_page || 1 }} / {{ pagination.last_page || 1 }}
          </span>
          <button 
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50"
          >
            Sau
          </button>
        </div>
      </div>
    </div>

    <!-- Modal chi tiết user -->
    <div v-if="showUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold">Chi tiết người dùng</h3>
          <button @click="showUserModal = false" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div v-if="selectedUser" class="space-y-4">
          <div class="flex items-center space-x-4">
            <img 
              :src="selectedUser.avatar || '/default-avatar.png'" 
              :alt="selectedUser.name"
              class="h-16 w-16 rounded-full object-cover"
            >
            <div>
              <h4 class="text-xl font-semibold">{{ selectedUser.name }}</h4>
              <p class="text-gray-600">{{ selectedUser.email }}</p>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Vai trò hiện tại</label>
              <p class="mt-1 text-sm text-gray-900">{{ getRoleName(selectedUser.role) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Ngày tham gia</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(selectedUser.created_at) }}</p>
            </div>
          </div>
          
          <div v-if="selectedUser.addresses && selectedUser.addresses.length > 0">
            <label class="block text-sm font-medium text-gray-700 mb-2">Địa chỉ</label>
            <div class="space-y-2">
              <div 
                v-for="address in selectedUser.addresses" 
                :key="address.id"
                class="p-3 border border-gray-200 rounded-md"
              >
                <p class="text-sm">{{ address.address_line_1 }}</p>
                <p class="text-sm text-gray-600">{{ address.city }}, {{ address.state }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
          <span>Đang tải...</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const currentUserId = computed(() => authStore.user?.id);

const loading = ref(false);
const users = ref([]);
const stats = ref({});
const pagination = ref({});
const searchQuery = ref('');
const selectedRole = ref('all');
const showUserModal = ref(false);
const selectedUser = ref(null);

const filteredUsers = computed(() => {
  let filtered = users.value;
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(query) || 
      user.email.toLowerCase().includes(query)
    );
  }
  
  if (selectedRole.value !== 'all') {
    filtered = filtered.filter(user => user.role === selectedRole.value);
  }
  
  return filtered;
});

const getRoleName = (role) => {
  const roleNames = {
    'user': 'Khách hàng',
    'staff': 'Nhân viên',
    'admin': 'Admin'
  };
  return roleNames[role] || role;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('vi-VN');
};

const fetchUsers = async (page = 1) => {
  loading.value = true;
  try {
    const response = await axios.get(`/admin/users?page=${page}`);
    users.value = response.data.data;
    pagination.value = response.data;
  } catch (error) {
    console.error('Error fetching users:', error);
  } finally {
    loading.value = false;
  }
};

const fetchStats = async () => {
  try {
    const response = await axios.get('/admin/users/stats');
    stats.value = response.data;
  } catch (error) {
    console.error('Error fetching stats:', error);
  }
};

const updateUserRole = async (user) => {
  try {
    await axios.patch(`/admin/users/${user.id}/role`, {
      role: user.role
    });
    
    // Cập nhật lại thống kê
    await fetchStats();
    
    // Hiển thị thông báo thành công
    alert('Cập nhật vai trò thành công!');
  } catch (error) {
    console.error('Error updating user role:', error);
    alert('Có lỗi xảy ra khi cập nhật vai trò!');
  }
};

const deleteUser = async (userId) => {
  if (!confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
    return;
  }
  
  try {
    await axios.delete(`/admin/users/${userId}`);
    await fetchUsers(pagination.value.current_page);
    await fetchStats();
    alert('Xóa người dùng thành công!');
  } catch (error) {
    console.error('Error deleting user:', error);
    alert('Có lỗi xảy ra khi xóa người dùng!');
  }
};

const viewUserDetails = (user) => {
  selectedUser.value = user;
  showUserModal.value = true;
};

const filterUsers = () => {
  // Filter được xử lý bởi computed property
};

const filterByRole = (role) => {
  selectedRole.value = role;
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchUsers(page);
  }
};

onMounted(async () => {
  await Promise.all([
    fetchUsers(),
    fetchStats()
  ]);
});
</script>
