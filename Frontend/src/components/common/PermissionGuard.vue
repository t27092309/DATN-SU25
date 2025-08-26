<template>
  <div v-if="hasPermission">
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePermissionsStore } from '@/stores/permissions';

const props = defineProps({
  permission: {
    type: String,
    required: true
  },
  permissions: {
    type: Array,
    default: () => []
  },
  requireAll: {
    type: Boolean,
    default: false
  }
});

const permissionsStore = usePermissionsStore();

const hasPermission = computed(() => {
  if (props.permissions.length > 0) {
    return props.requireAll 
      ? permissionsStore.hasAllPermissions(props.permissions)
      : permissionsStore.hasAnyPermission(props.permissions);
  }
  
  return permissionsStore.hasPermission(props.permission);
});
</script>

