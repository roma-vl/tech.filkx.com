<template>
  <div class="space-y-6">
    <AdminClientFilters
      v-model:search-query="searchQuery"
      v-model:filters="filters"
      :available-plans="availablePlans"
      @add="openAddModal"
      @export="exportCsv"
      @reset="resetFilters"
    />

    <div class="relative min-h-[400px]">
      <AppLoadingOverlay
        :loading="loading"
        :text="$t('admin.users.loading')"
      />

      <AdminClientTable
        :clients="clients"
        @edit="openEditModal"
        @suspend="openSuspendModal"
        @delete="openDeleteModal"
        @impersonate="clientToImpersonate = $event"
      />
    </div>

    <AppPagination
      v-if="pagination.total > 0"
      :pagination="{
        current_page: pagination.currentPage,
        last_page: pagination.lastPage,
        total: pagination.total,
      }"
      @page-change="goToPage"
    />

    <AdminClientModal
      :is-open="showModal"
      :is-editing="isEditing"
      :saving="saving"
      :form="form"
      :available-roles="availableRoles"
      @close="closeModal"
      @save="saveUser"
    />

    <AdminClientSuspendModal
      :is-open="!!clientToSuspend"
      :client="clientToSuspend"
      :loading="modalsLoading"
      @close="clientToSuspend = null"
      @confirm="handleSuspendConfirm"
    />

    <AdminClientDeleteModal
      :is-open="!!clientToDelete"
      :client="clientToDelete"
      :loading="modalsLoading"
      @close="clientToDelete = null"
      @confirm="handleDeleteConfirm"
    />

    <AdminImpersonationModal
      :client="clientToImpersonate"
      :loading="impersonationLoading"
      @close="clientToImpersonate = null"
      @confirm="handleImpersonate"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "@/services/api";
import { useI18n } from "vue-i18n";
import { useAuthStore } from "@/stores/auth";

import AppLoadingOverlay from "@/components/admin/ui/Feedback/AppLoadingOverlay.vue";
import AppPagination from "@/components/application/ui/Data/AppPagination.vue";
import AdminClientFilters from "@/components/admin/features/client/AdminClientFilters.vue";
import AdminClientTable from "@/components/admin/features/client/AdminClientTable.vue";
import AdminClientModal from "@/components/admin/features/client/AdminClientModal.vue";
import AdminClientSuspendModal from "@/components/admin/features/client/AdminClientSuspendModal.vue";
import AdminClientDeleteModal from "@/components/admin/features/client/AdminClientDeleteModal.vue";
import AdminImpersonationModal from "@/components/admin/features/client/AdminImpersonationModal.vue";

const { t } = useI18n();
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const searchQuery = ref(route.query.search || "");
const filters = ref({
  plan: route.query.plan || "",
  status: route.query.status || "",
  dateFrom: route.query.dateFrom || "",
  dateTo: route.query.dateTo || "",
  withDeleted: route.query.withDeleted === "1",
});

const availablePlans = ref([]);
const availableRoles = ref([]);
const clients = ref([]);
const loading = ref(false);
const modalsLoading = ref(false);

const pagination = ref({
  currentPage: parseInt(route.query.page) || 1,
  total: 0,
  lastPage: 1,
});

const showModal = ref(false);
const isEditing = ref(false);
const saving = ref(false);
const clientToSuspend = ref(null);
const clientToDelete = ref(null);
const clientToImpersonate = ref(null);
const impersonationLoading = ref(false);

const form = ref({
  id: null,
  name: "",
  email: "",
  password: "",
  status: "active",
  subscription: null,
  roles: [],
});

const fetchMetadata = async () => {
  try {
    const plansRes = await api.get("/admin/users/plans");
    availablePlans.value = plansRes.data.data;

    const rolesRes = await api.get("/admin/roles?per_page=100");
    availableRoles.value = rolesRes.data.data.data;
  } catch (e) {
    console.error("Failed to fetch metadata", e);
  }
};

const getParams = () => {
  return {
    search: searchQuery.value,
    plan: filters.value.plan,
    status: filters.value.status,
    dateFrom: filters.value.dateFrom,
    dateTo: filters.value.dateTo,
    withDeleted: filters.value.withDeleted ? 1 : 0,
  };
};

const updateUrl = () => {
  const query = {
    ...getParams(),
    page: pagination.value.currentPage,
  };

  // Remove empty values
  Object.keys(query).forEach((key) => {
    if (!query[key] && query[key] !== 0) delete query[key];
  });

  router.replace({ query });
};

const fetchClients = async (page = pagination.value.currentPage) => {
  loading.value = true;
  try {
    const params = {
      page,
      ...getParams(),
    };

    const { data } = await api.get("/admin/users", { params });
    const payload = data.data;
    clients.value = payload.data;
    pagination.value = {
      currentPage: payload.currentPage,
      total: payload.total,
      lastPage: payload.lastPage,
    };
    updateUrl();
  } catch (e) {
    console.error("Failed to fetch clients", e);
  } finally {
    loading.value = false;
  }
};

const goToPage = (page) => {
  pagination.value.currentPage = page;
  fetchClients(page);
};

const resetFilters = () => {
  searchQuery.value = "";
  filters.value = {
    plan: "",
    status: "",
    dateFrom: "",
    dateTo: "",
    withDeleted: false,
  };
  pagination.value.currentPage = 1;
  fetchClients(1);
};

const openAddModal = () => {
  isEditing.value = false;
  form.value = {
    id: null,
    name: "",
    email: "",
    password: "",
    status: "active",
    subscription: null,
    roles: [],
  };
  showModal.value = true;
};

const openEditModal = (client) => {
  isEditing.value = true;
  form.value = {
    id: client.id,
    name: client.name,
    email: client.email,
    status: client.status,
    subscription: client.subscription,
    roles: client.roles || [],
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveUser = async (formData) => {
  saving.value = true;
  try {
    const payload = {
      name: formData.name,
      email: formData.email,
      status: formData.status,
      roles: formData.roles,
    };

    if (formData.featuresSnapshot) {
      payload.featuresSnapshot = formData.featuresSnapshot;
    }

    if (formData.subscriptionUsage) {
      payload.subscriptionUsage = formData.subscriptionUsage;
    }

    if (!isEditing.value) {
      payload.password = formData.password;
      await api.post("/admin/users", payload);
    } else {
      await api.put(`/admin/users/${formData.id}`, payload);
    }

    closeModal();
    fetchClients(pagination.value.currentPage);
  } catch (e) {
    console.error("Failed to save user", e);
    alert(e.response?.data?.message || t("admin.users.alerts.saveError"));
  } finally {
    saving.value = false;
  }
};

const handleImpersonate = async () => {
  if (!clientToImpersonate.value) return;
  impersonationLoading.value = true;
  try {
    const result = await auth.impersonate(clientToImpersonate.value.id);
    if (result.ok) {
      router.push("/dashboard");
    }
  } catch (e) {
    console.error("Impersonation failed", e);
  } finally {
    impersonationLoading.value = false;
    clientToImpersonate.value = null;
  }
};

const openSuspendModal = (client) => {
  clientToSuspend.value = client;
};

const handleSuspendConfirm = async (id) => {
  modalsLoading.value = true;
  try {
    await api.post(`/admin/users/${id}/suspend`);
    clientToSuspend.value = null;
    fetchClients(pagination.value.currentPage);
  } catch (e) {
    console.error(t("admin.users.alerts.suspendError"), e);
  } finally {
    modalsLoading.value = false;
  }
};

const openDeleteModal = (client) => {
  clientToDelete.value = client;
};

const handleDeleteConfirm = async () => {
  if (!clientToDelete.value) return;
  modalsLoading.value = true;
  try {
    await api.delete(`/admin/users/${clientToDelete.value.id}`);
    clientToDelete.value = null;
    fetchClients(pagination.value.currentPage);
  } catch (e) {
    console.error(t("admin.users.alerts.deleteError"), e);
  } finally {
    modalsLoading.value = false;
  }
};

const exportCsv = async () => {
  try {
    const { data } = await api.get("/admin/users/export", {
      params: getParams(),
      responseType: "blob",
    });
    const url = window.URL.createObjectURL(new Blob([data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `clients-export-${new Date().getTime()}.csv`);
    document.body.appendChild(link);
    link.click();
  } catch (e) {
    console.error("Export failed", e);
  }
};

// Listeners
let debounceTimer;
watch(
  [searchQuery, filters],
  () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      pagination.value.currentPage = 1;
      fetchClients(1);
    }, 300);
  },
  { deep: true },
);

// Listen to URL changes (back/forward navigation)
watch(
  () => route.query,
  (newQuery) => {
    const newPage = parseInt(newQuery.page) || 1;
    const newSearch = newQuery.search || "";
    const newFilters = {
      plan: newQuery.plan || "",
      status: newQuery.status || "",
      dateFrom: newQuery.dateFrom || "",
      dateTo: newQuery.dateTo || "",
      withDeleted: newQuery.withDeleted === "1",
    };

    if (
      newPage !== pagination.value.currentPage ||
      newSearch !== searchQuery.value ||
      JSON.stringify(newFilters) !== JSON.stringify(filters.value)
    ) {
      pagination.value.currentPage = newPage;
      searchQuery.value = newSearch;
      filters.value = newFilters;
      fetchClients(newPage);
    }
  },
  { deep: true },
);

onMounted(() => {
  fetchMetadata();
  fetchClients();
});
</script>
