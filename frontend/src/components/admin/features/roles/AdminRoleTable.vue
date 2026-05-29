<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm relative z-10"
  >
    <div class="flex flex-col">
      <AdminRoleListRow
        v-for="role in roles"
        :key="role.id"
        :role="role"
        @edit="$emit('edit', $event)"
        @delete="$emit('delete', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import AdminRoleListRow from "./AdminRoleListRow.vue";

defineProps({
  roles: {
    type: Array,
    required: true,
  },
});

defineEmits(["edit", "delete"]);
</script>
