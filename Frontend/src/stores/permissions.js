import { defineStore } from 'pinia';
import axios from 'axios';

export const usePermissionsStore = defineStore('permissions', {
  state: () => ({
    userPermissions: [],
    availablePermissions: [],
    roles: [],
    loading: false
  }),

  getters: {
    hasPermission: (state) => (permission) => {
      return state.userPermissions.includes(permission);
    },

    hasAnyPermission: (state) => (permissions) => {
      if (!Array.isArray(permissions)) {
        permissions = [permissions];
      }
      return permissions.some(permission => state.userPermissions.includes(permission));
    },

    hasAllPermissions: (state) => (permissions) => {
      if (!Array.isArray(permissions)) {
        permissions = [permissions];
      }
      return permissions.every(permission => state.userPermissions.includes(permission));
    },

    canView: (state) => (module) => {
      return state.userPermissions.includes(`${module}:view`);
    },

    canCreate: (state) => (module) => {
      return state.userPermissions.includes(`${module}:create`);
    },

    canEdit: (state) => (module) => {
      return state.userPermissions.includes(`${module}:edit`);
    },

    canDelete: (state) => (module) => {
      return state.userPermissions.includes(`${module}:delete`);
    }
  },

  actions: {
    async fetchUserPermissions() {
      try {
        const response = await axios.get('/admin/user/permissions');
        this.userPermissions = response.data.permissions || [];
      } catch (error) {
        console.error('Error fetching user permissions:', error);
        this.userPermissions = [];
      }
    },

    async fetchAvailablePermissions() {
      try {
        const response = await axios.get('/admin/roles/permissions');
        this.availablePermissions = response.data || [];
      } catch (error) {
        console.error('Error fetching available permissions:', error);
        this.availablePermissions = [];
      }
    },

    async fetchRoles() {
      this.loading = true;
      try {
        const response = await axios.get('/admin/roles');
        this.roles = response.data || [];
      } catch (error) {
        console.error('Error fetching roles:', error);
        this.roles = [];
      } finally {
        this.loading = false;
      }
    },

    async createRole(roleData) {
      try {
        const response = await axios.post('/admin/roles', roleData);
        await this.fetchRoles();
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async updateRole(roleId, roleData) {
      try {
        const response = await axios.put(`/admin/roles/${roleId}`, roleData);
        await this.fetchRoles();
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async deleteRole(roleId) {
      try {
        await axios.delete(`/admin/roles/${roleId}`);
        await this.fetchRoles();
      } catch (error) {
        throw error;
      }
    },

    async assignRoleToUser(userId, roleId) {
      try {
        await axios.post('/admin/roles/assign', { user_id: userId, role_id: roleId });
      } catch (error) {
        throw error;
      }
    },

    async removeRoleFromUser(userId, roleId) {
      try {
        await axios.post('/admin/roles/remove', { user_id: userId, role_id: roleId });
      } catch (error) {
        throw error;
      }
    },

    initializePermissions() {
      this.fetchUserPermissions();
      this.fetchAvailablePermissions();
    }
  }
});

