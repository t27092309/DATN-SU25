<template>
  <div class="p-6 bg-gray-100 min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-3xl font-bold text-gray-900">Quản lý Vai trò và Quyền hạn</h1>
        <button 
          @click="showCreateModal = true"
          class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
        >
          Thêm Vai trò mới
        </button>
      </div>
    </div>

    <!-- Danh sách Roles -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
      <h2 class="text-xl font-semibold mb-4">Danh sách Vai trò</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mô tả</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quyền hạn</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Người dùng</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="role in roles" :key="role.id">
              <td class="px-6 py-4 whitespace-nowrap font-medium">{{ role.name }}</td>
              <td class="px-6 py-4">{{ role.description || 'Không có mô tả' }}</td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span 
                    v-for="permission in role.permissions" 
                    :key="permission"
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    {{ permission }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="text-sm text-gray-500">{{ role.users?.length || 0 }} người dùng</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button 
                  @click="editRole(role)"
                  class="text-indigo-600 hover:text-indigo-900 mr-3"
                >
                  Sửa
                </button>
                <button 
                  @click="deleteRole(role.id)"
                  class="text-red-600 hover:text-red-900"
                >
                  Xóa
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal tạo/sửa Role -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold mb-4">
          {{ showEditModal ? 'Sửa Vai trò' : 'Thêm Vai trò mới' }}
        </h3>
        
        <form @submit.prevent="showEditModal ? updateRoleData() : createRoleData()">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tên vai trò</label>
            <input 
              v-model="roleForm.name"
              type="text"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              required
            >
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả</label>
            <textarea 
              v-model="roleForm.description"
              class="w-full border border-gray-300 rounded-md px-3 py-2"
              rows="3"
            ></textarea>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Quyền hạn</label>
            <div class="grid grid-cols-2 gap-4 max-h-60 overflow-y-auto">
              <div v-for="permission in availablePermissions" :key="permission" class="flex items-center">
                <input 
                  :id="permission"
                  v-model="roleForm.permissions"
                  :value="permission"
                  type="checkbox"
                  class="mr-2"
                >
                <label :for="permission" class="text-sm">{{ formatPermission(permission) }}</label>
              </div>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button 
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
            >
              Hủy
            </button>
            <button 
              type="submit"
              class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
            >
              {{ showEditModal ? 'Cập nhật' : 'Tạo' }}
            </button>
          </div>
        </form>
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
import { usePermissionsStore } from '@/stores/permissions';

const permissionsStore = usePermissionsStore();

const loading = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingRoleId = ref(null);

const roleForm = ref({
  name: '',
  description: '',
  permissions: []
});

const roles = computed(() => permissionsStore.roles);
const availablePermissions = computed(() => permissionsStore.availablePermissions);

const formatPermission = (permission) => {
  const [module, action] = permission.split(':');
  const moduleNames = {
    dashboard: 'Bảng điều khiển',
    products: 'Sản phẩm',
    categories: 'Danh mục',
    brands: 'Thương hiệu',
    orders: 'Đơn hàng',
    users: 'Người dùng',
    reports: 'Báo cáo',
    settings: 'Cài đặt',
    coupons: 'Mã giảm giá',
    banners: 'Banner',
    shipping: 'Vận chuyển',
    inventory: 'Kho hàng'
  };
  
  const actionNames = {
    view: 'Xem',
    create: 'Tạo',
    edit: 'Sửa',
    delete: 'Xóa'
  };
  
  return `${moduleNames[module] || module} - ${actionNames[action] || action}`;
};

const editRole = (role) => {
  editingRoleId.value = role.id;
  roleForm.value = {
    name: role.name,
    description: role.description || '',
    permissions: role.permissions || []
  };
  showEditModal.value = true;
};

const createRoleData = async () => {
  try {
    await permissionsStore.createRole(roleForm.value);
    closeModal();
  } catch (error) {
    console.error('Error creating role:', error);
  }
};

const updateRoleData = async () => {
  try {
    await permissionsStore.updateRole(editingRoleId.value, roleForm.value);
    closeModal();
  } catch (error) {
    console.error('Error updating role:', error);
  }
};

const deleteRole = async (roleId) => {
  if (confirm('Bạn có chắc chắn muốn xóa vai trò này?')) {
    try {
      await permissionsStore.deleteRole(roleId);
    } catch (error) {
      console.error('Error deleting role:', error);
    }
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  editingRoleId.value = null;
  roleForm.value = {
    name: '',
    description: '',
    permissions: []
  };
};

onMounted(async () => {
  loading.value = true;
  try {
    await Promise.all([
      permissionsStore.fetchRoles(),
      permissionsStore.fetchAvailablePermissions()
    ]);
  } catch (error) {
    console.error('Error loading data:', error);
  } finally {
    loading.value = false;
  }
});
</script>

