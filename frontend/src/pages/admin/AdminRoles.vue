<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4"
    >
      <div class="flex items-center gap-3">
        <AppButton
          variant="secondary"
          @click="openPermissionModal"
        >
          <template #prefix>
            <PlusIcon class="w-4 h-4 mr-2" />
          </template>
          {{ t("admin.roles.create_permission") }}
        </AppButton>

        <AppButton
          variant="primary"
          @click="openAddModal"
        >
          <template #prefix>
            <PlusIcon class="w-4 h-4 mr-2" />
          </template>
          {{ t("admin.roles.create_role") }}
        </AppButton>
      </div>
    </div>

    <AdminRoleFilters
      v-model:search-query="searchQuery"
      v-model:view-mode="viewMode"
    />

    <div class="relative min-h-[400px]">
      <AppLoadingOverlay
        :loading="loading"
        :text="t('admin.roles.loading_roles')"
      />

      <div
        v-if="viewMode === 'grid'"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
      >
        <AdminRoleCard
          v-for="role in filteredRoles"
          :key="role.id"
          :role="role"
          @edit="openEditModal"
          @delete="deleteRole"
        />
      </div>

      <AdminRoleTable
        v-else
        :roles="filteredRoles"
        @edit="openEditModal"
        @delete="deleteRole"
      />
    </div>

    <AdminRoleModal
      v-model="showModal"
      v-model:selected-permissions="selectedPermissions"
      :is-editing="isEditing"
      :saving="saving"
      :form="form"
      :available-permissions="availablePermissions"
      @save="saveRole"
      @toggle-all="toggleAllPermissions"
    />

    <AdminPermissionModal
      v-model="showPermissionModal"
      :saving="savingPermission"
      :form="permissionForm"
      @save="savePermission"
      @update-field="updatePermissionField"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { PlusIcon } from "@heroicons/vue/24/outline";
import api from "@/shared/services/api/apiClient";

// Feature Components
import AppLoadingOverlay from "@/components/admin/ui/AppLoadingOverlay.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import AdminRoleFilters from "@/components/admin/features/roles/AdminRoleFilters.vue";
import AdminRoleCard from "@/components/admin/features/roles/AdminRoleCard.vue";
import AdminRoleTable from "@/components/admin/features/roles/AdminRoleTable.vue";
import AdminRoleModal from "@/components/admin/features/roles/AdminRoleModal.vue";
import AdminPermissionModal from "@/components/admin/features/roles/AdminPermissionModal.vue";

const { t } = useI18n();

const roles = ref([]);
const availablePermissions = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const viewMode = ref("list");

// Modal State
const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const selectedPermissions = ref([]);
const form = ref({
  id: null,
  name: "",
  description: "",
});

const showPermissionModal = ref(false);
const savingPermission = ref(false);
const permissionForm = ref({
  name: "",
  slug: "",
  resource: "",
  action: "",
  description: "",
});

const fetchRoles = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/roles");
    // Roles are paginated
    roles.value = data.data.data;
  } catch (e) {
    console.error("Failed to fetch roles", e);
  } finally {
    loading.value = false;
  }
};

const fetchPermissions = async () => {
  try {
    const { data } = await api.get("/admin/permissions");
    availablePermissions.value = data.data;
  } catch (e) {
    console.error("Failed to fetch permissions", e);
  }
};

const filteredRoles = computed(() => {
  if (!searchQuery.value) return roles.value;
  const q = searchQuery.value.toLowerCase();
  return roles.value.filter(
    (r) =>
      r.name.toLowerCase().includes(q) ||
      (r.description && r.description.toLowerCase().includes(q)) ||
      r.slug.toLowerCase().includes(q),
  );
});

const openAddModal = () => {
  isEditing.value = false;
  form.value = { id: null, name: "", description: "" };
  selectedPermissions.value = [];
  showModal.value = true;
};

const openPermissionModal = () => {
  permissionForm.value = {
    name: "",
    slug: "",
    resource: "",
    action: "",
    description: "",
  };
  showPermissionModal.value = true;
};

const updatePermissionField = ({ field, value }) => {
  permissionForm.value[field] = value;
  if (field === "name") {
    generateSlug();
  }
};

const generateSlug = () => {
  if (permissionForm.value.name) {
    const parts = permissionForm.value.name.toLowerCase().split(" ");
    if (parts.length >= 2) {
      permissionForm.value.action = parts[0];
      permissionForm.value.resource = parts.slice(1).join("");
      permissionForm.value.slug = `${permissionForm.value.resource}.${permissionForm.value.action}`;
    }
  }
};

const savePermission = async () => {
  savingPermission.value = true;
  try {
    await api.post("/admin/permissions", permissionForm.value);
    showPermissionModal.value = false;
    await fetchPermissions();
  } catch (e) {
    console.error("Failed to create permission", e);
    alert(
      e.response?.data?.message ||
        t("admin.roles.alerts.permission_create_error"),
    );
  } finally {
    savingPermission.value = false;
  }
};

const openEditModal = (role) => {
  isEditing.value = true;
  form.value = {
    id: role.id,
    name: role.name,
    description: role.description || "",
  };
  selectedPermissions.value = role.permissions.map((p) => p.slug);
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const toggleAllPermissions = () => {
  if (selectedPermissions.value.length === availablePermissions.value.length) {
    selectedPermissions.value = [];
  } else {
    selectedPermissions.value = availablePermissions.value.map((p) => p.slug);
  }
};

const saveRole = async () => {
  saving.value = true;
  try {
    const payload = {
      name: form.value.name,
      description: form.value.description,
      permissions: selectedPermissions.value,
    };

    if (isEditing.value) {
      await api.put(`/admin/roles/${form.value.id}`, payload);
    } else {
      await api.post("/admin/roles", payload);
    }

    closeModal();
    await fetchRoles();
  } catch (e) {
    console.error("Failed to save role", e);
    alert(e.response?.data?.message || t("admin.roles.alerts.save_error"));
  } finally {
    saving.value = false;
  }
};

const deleteRole = async (id) => {
  if (!confirm(t("admin.roles.alerts.delete_confirm"))) return;
  try {
    await api.delete(`/admin/roles/${id}`);
    await fetchRoles();
  } catch (e) {
    console.error("Failed to delete role", e);
    alert(e.response?.data?.message || t("admin.roles.alerts.delete_error"));
  }
};

onMounted(() => {
  fetchRoles();
  fetchPermissions();
});
</script>
