<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <AppButton
        variant="primary"
        class="flex items-center gap-2 shrink-0 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
        @click="openCreateModal"
      >
        <PlusIcon class="w-4 h-4 stroke-[2px]" />
        {{ t("admin.marketing.promotions.new") }}
      </AppButton>
    </div>

    <PromotionStats :data="statsData" />
    <PromotionTable
      v-model:search="search"
      v-model:status-filter="statusFilter"
      :promotions="promotions"
      :loading="loading"
      :pagination="pagination"
      @edit="openEditModal"
      @delete="openDeleteModal"
      @sort="setSort"
      @change-page="fetchPromotions"
    />

    <PromotionEditModal
      :is-open="isEditModalOpen"
      :promotion="editingPromotion"
      @close="closeEditModal"
      @saved="handleSaved"
    />

    <!-- Global Delete Confirmation Modal -->
    <AppConfirmModal
      v-model="showDeleteConfirm"
      :title="t('admin.marketing.promotions.alerts.delete_confirm_title')"
      :message="deleteConfirmMessage"
      confirm-text="Видалити"
      cancel-text="Скасувати"
      :loading="deleteLoading"
      @confirm="handleDeleteConfirm"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { PlusIcon } from "@heroicons/vue/24/outline";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";

import AppButton from "@/components/admin/ui/AppButton.vue";
import AppConfirmModal from "@/components/admin/ui/AppConfirmModal.vue";
import PromotionStats from "@/components/admin/features/marketing/promotions/PromotionStats.vue";
import PromotionTable from "@/components/admin/features/marketing/promotions/PromotionTable.vue";
import PromotionEditModal from "@/components/admin/features/marketing/promotions/PromotionEditModal.vue";

const { t } = useI18n();
const toast = useToast();
const promotions = ref([]);
const loading = ref(false);
const deleteLoading = ref(false);
const search = ref("");
const statusFilter = ref("");
const sortBy = ref("created_at");
const sortDir = ref("desc");

const pagination = ref({
  currentPage: 1,
  lastPage: 1,
});

const isEditModalOpen = ref(false);
const editingPromotion = ref(null);
const promotionToDelete = ref(null);

const showDeleteConfirm = computed({
  get: () => !!promotionToDelete.value,
  set: (val) => {
    if (!val) promotionToDelete.value = null;
  }
});

const deleteConfirmMessage = computed(() => {
  if (!promotionToDelete.value) return "";
  return t("admin.marketing.promotions.alerts.delete_confirm", {
    name: promotionToDelete.value.name,
  });
});

const statsData = computed(() => {
  const activeList = (promotions.value || []).filter((p) => p.isActive);
  return {
    activePromotions: activeList.length,
    activeTrend: activeList.length > 0 ? "+4.2%" : "Neutral",
    totalConversions: "1,452",
    conversionsTrend: "+12.1%",
    totalRevenue: "$4,240",
    revenueTrend: "+18%",
    reachedAudience: "45.2k",
    audienceTrend: "+8.4%",
  };
});

const fetchPromotions = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: search.value,
      status: statusFilter.value,
      sortBy: sortBy.value,
      sortDir: sortDir.value,
    };

    const response = await api.get("/admin/marketing/promotions", { params });
    const payload = response.data.data;

    promotions.value = payload.data;
    pagination.value = {
      currentPage: payload.currentPage,
      lastPage: payload.lastPage,
    };
  } catch (e) {
    console.error("Failed to fetch promotions", e);
    toast.error(t("admin.marketing.promotions.alerts.loadError"));
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingPromotion.value = null;
  isEditModalOpen.value = true;
};

const openEditModal = (promotion) => {
  editingPromotion.value = promotion;
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
  editingPromotion.value = null;
};

const handleSaved = () => {
  toast.success(t("admin.marketing.promotions.alerts.saveSuccess"));
  fetchPromotions(pagination.value.currentPage);
};

const openDeleteModal = (promotion) => {
  promotionToDelete.value = promotion;
};

const handleDeleteConfirm = async () => {
  if (!promotionToDelete.value) return;
  deleteLoading.value = true;
  try {
    await api.delete(`/admin/marketing/promotions/${promotionToDelete.value.id}`);
    toast.success(t("admin.marketing.promotions.alerts.deleteSuccess"));
    promotionToDelete.value = null;
    fetchPromotions(pagination.value.currentPage);
  } catch {
    toast.error(t("admin.marketing.promotions.alerts.deleteError"));
  } finally {
    deleteLoading.value = false;
  }
};

const setSort = (field) => {
  if (sortBy.value === field) {
    sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
  } else {
    sortBy.value = field;
    sortDir.value = "desc";
  }
  fetchPromotions(1);
};

let debounceTimer;
watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchPromotions(1);
  }, 300);
});

watch(statusFilter, () => {
  fetchPromotions(1);
});

onMounted(() => {
  fetchPromotions();
});
</script>

<style scoped></style>
