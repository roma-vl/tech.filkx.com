<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center">
      <!-- Search + view-mode toggle -->
      <div class="flex items-center gap-3 flex-1 max-w-md">
        <AppInput
          v-model="searchQuery"
          :placeholder="t('admin.roles.search_placeholder')"
          class="flex-1"
        >
          <template #prepend>
            <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
          </template>
        </AppInput>

        <!-- View-mode toggles -->
        <div class="flex p-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm shrink-0">
          <button
            class="p-2 rounded-md transition-all"
            :class="
              viewMode === 'grid'
                ? 'bg-[#00a046] text-white shadow-sm'
                : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
            "
            :title="t('admin.roles.view_grid')"
            @click="viewMode = 'grid'"
          >
            <Squares2X2Icon class="w-4 h-4" />
          </button>
          <button
            class="p-2 rounded-md transition-all"
            :class="
              viewMode === 'list'
                ? 'bg-[#00a046] text-white shadow-sm'
                : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
            "
            :title="t('admin.roles.view_list')"
            @click="viewMode = 'list'"
          >
            <ListBulletIcon class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="flex items-center gap-3 shrink-0">
        <AppButton
          variant="secondary"
          class="flex items-center gap-2 h-[38px] !py-0"
          @click="openPermissionModal"
        >
          <PlusIcon class="w-4 h-4" />
          {{ t("admin.roles.create_permission") }}
        </AppButton>

        <AppButton
          variant="primary"
          class="flex items-center gap-2 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
          @click="openAddModal"
        >
          <PlusIcon class="w-4 h-4 stroke-[2px]" />
          {{ t("admin.roles.create_role") }}
        </AppButton>
      </div>
    </div>

    <!-- Roles grid / list -->
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
          @delete="promptDelete"
        />
      </div>

      <AdminRoleTable
        v-else
        :roles="filteredRoles"
        @edit="openEditModal"
        @delete="promptDelete"
      />
    </div>

    <!-- Role create / edit modal -->
    <AdminRoleModal
      v-model="showModal"
      v-model:form="form"
      v-model:selected-permissions="selectedPermissions"
      :is-editing="isEditing"
      :saving="saving"
      :available-permissions="availablePermissions"
      @save="saveRole"
      @toggle-all="toggleAllPermissions"
    />

    <!-- Permission create modal -->
    <AdminPermissionModal
      v-model="showPermissionModal"
      :saving="savingPermission"
      :form="permissionForm"
      @save="savePermission"
      @update-field="updatePermissionField"
    />

    <!-- Delete confirmation modal -->
    <AppConfirmModal
      v-model="showDeleteConfirm"
      :title="t('admin.roles.alerts.delete_confirm_title') || 'Видалення ролі'"
      :message="t('admin.roles.alerts.delete_confirm')"
      confirm-text="Видалити"
      cancel-text="Скасувати"
      :loading="deleteLoading"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import {
  ListBulletIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  Squares2X2Icon,
} from "@heroicons/vue/24/outline";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";

import AppLoadingOverlay from "@/components/admin/ui/AppLoadingOverlay.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppConfirmModal from "@/components/admin/ui/AppConfirmModal.vue";
import AdminRoleCard from "@/components/admin/features/roles/AdminRoleCard.vue";
import AdminRoleTable from "@/components/admin/features/roles/AdminRoleTable.vue";
import AdminRoleModal from "@/components/admin/features/roles/AdminRoleModal.vue";
import AdminPermissionModal from "@/components/admin/features/roles/AdminPermissionModal.vue";

const { t } = useI18n();
const toast = useToast();

const roles = ref([]);
const availablePermissions = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const viewMode = ref("list");

// Role Modal state
const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const selectedPermissions = ref([]);
const form = ref({ id: null, name: "", description: "" });

// Permission Modal state
const showPermissionModal = ref(false);
const savingPermission = ref(false);
const permissionForm = ref({
  name: "",
  slug: "",
  resource: "",
  action: "",
  description: "",
});

// Delete confirmation state
const showDeleteConfirm = ref(false);
const deleteLoading = ref(false);
const roleIdToDelete = ref(null);

// ─── Data fetching ───────────────────────────────────────────────────────────

const fetchRoles = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/roles");
    roles.value = data.data.data;
  } catch (e) {
    console.error("Failed to fetch roles", e);
    toast.error(t("admin.roles.alerts.load_error") || "Помилка завантаження ролей");
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

// ─── Computed ────────────────────────────────────────────────────────────────

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

// ─── Role CRUD ────────────────────────────────────────────────────────────────

const openAddModal = () => {
  isEditing.value = false;
  form.value = { id: null, name: "", description: "" };
  selectedPermissions.value = [];
  showModal.value = true;
};

const openEditModal = (role) => {
  isEditing.value = true;
  form.value = { id: role.id, name: role.name, description: role.description || "" };
  selectedPermissions.value = role.permissions.map((p) => p.slug);
  showModal.value = true;
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
      toast.success(t("admin.roles.alerts.update_success") || "Роль оновлено успішно");
    } else {
      await api.post("/admin/roles", payload);
      toast.success(t("admin.roles.alerts.create_success") || "Роль створено успішно");
    }

    showModal.value = false;
    await fetchRoles();
  } catch (e) {
    console.error("Failed to save role", e);
    toast.error(e.response?.data?.message || t("admin.roles.alerts.save_error"));
  } finally {
    saving.value = false;
  }
};

const promptDelete = (id) => {
  roleIdToDelete.value = id;
  showDeleteConfirm.value = true;
};

const confirmDelete = async () => {
  if (!roleIdToDelete.value) return;
  deleteLoading.value = true;
  try {
    await api.delete(`/admin/roles/${roleIdToDelete.value}`);
    toast.success(t("admin.roles.alerts.delete_success") || "Роль видалено успішно");
    showDeleteConfirm.value = false;
    roleIdToDelete.value = null;
    await fetchRoles();
  } catch (e) {
    console.error("Failed to delete role", e);
    toast.error(e.response?.data?.message || t("admin.roles.alerts.delete_error"));
  } finally {
    deleteLoading.value = false;
  }
};

// ─── Permission CRUD ──────────────────────────────────────────────────────────

const openPermissionModal = () => {
  permissionForm.value = { name: "", slug: "", resource: "", action: "", description: "" };
  showPermissionModal.value = true;
};

const updatePermissionField = ({ field, value }) => {
  permissionForm.value[field] = value;
  if (field === "name") generateSlug();
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
    toast.success(t("admin.roles.alerts.permission_create_success") || "Дозвіл створено успішно");
    showPermissionModal.value = false;
    await fetchPermissions();
  } catch (e) {
    console.error("Failed to create permission", e);
    toast.error(e.response?.data?.message || t("admin.roles.alerts.permission_create_error"));
  } finally {
    savingPermission.value = false;
  }
};

onMounted(() => {
  fetchRoles();
  fetchPermissions();
});
</script>
