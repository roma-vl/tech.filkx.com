<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <AppButton
        variant="primary"
        class="flex items-center gap-2 shrink-0 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
        @click="openCreateModal"
      >
        <PlusIcon class="w-4 h-4 stroke-[2px]" />
        {{ t("admin.marketing.coupons.new") }}
      </AppButton>
    </div>

    <CouponStats :data="statsData" />

    <CouponTable
      v-model:search="search"
      v-model:status-filter="statusFilter"
      :coupons="coupons"
      :loading="loading"
      :pagination="pagination"
      @edit="openEditModal"
      @delete="openDeleteModal"
      @sort="setSort"
      @change-page="fetchCoupons"
    />

    <CouponEditModal
      :is-open="isEditModalOpen"
      :coupon="editingCoupon"
      @close="closeEditModal"
      @saved="handleSaved"
    />

    <!-- Global Delete Confirmation Modal -->
    <AppConfirmModal
      v-model="showDeleteConfirm"
      :title="t('admin.marketing.coupons.alerts.delete_confirm_title')"
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
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";
import { PlusIcon } from "@heroicons/vue/24/outline";

import CouponStats from "@/components/admin/features/marketing/coupons/CouponStats.vue";
import CouponTable from "@/components/admin/features/marketing/coupons/CouponTable.vue";
import CouponEditModal from "@/components/admin/features/marketing/coupons/CouponEditModal.vue";
import AppConfirmModal from "@/components/admin/ui/AppConfirmModal.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

const { t } = useI18n();
const toast = useToast();

const coupons = ref([]);
const loading = ref(false);
const deleteLoading = ref(false);
const search = ref("");
const statusFilter = ref("");
const sortBy = ref("created_at");
const sortDir = ref("desc");

const pagination = ref({
  current_page: 1,
  last_page: 1,
});

const isEditModalOpen = ref(false);
const editingCoupon = ref(null);
const couponToDelete = ref(null);

const showDeleteConfirm = computed({
  get: () => !!couponToDelete.value,
  set: (val) => {
    if (!val) couponToDelete.value = null;
  }
});

const deleteConfirmMessage = computed(() => {
  if (!couponToDelete.value) return "";
  return t("admin.marketing.coupons.alerts.delete_confirm", {
    code: couponToDelete.value.code,
  });
});

const statsData = computed(() => {
  return {
    activeCoupons: coupons.value.filter((c) => c.isActive).length,
    activeTrend: "+12%",
    totalRedemptions: coupons.value.reduce(
      (acc, c) => acc + (c.usedCount || 0),
      0,
    ),
    redemptionsTrend: "+5.4%",
    totalRevenue: "$1,240",
    revenueTrend: "+21%",
    avgDiscount: "15%",
    discountTrend: "Neutral",
  };
});

const fetchCoupons = async (page = 1) => {
  loading.value = true;
  try {
    const params = {
      page,
      search: search.value,
      status: statusFilter.value,
      sortBy: sortBy.value,
      sortDir: sortDir.value,
    };

    const response = await api.get("/admin/marketing/coupons", { params });
    const payload = response.data.data;

    coupons.value = payload.data;
    pagination.value = {
      currentPage: payload.currentPage,
      lastPage: payload.lastPage,
      total: payload.total,
    };
  } catch (e) {
    console.error("Failed to fetch coupons", e);
    toast.error(t("admin.marketing.coupons.alerts.loadError"));
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingCoupon.value = null;
  isEditModalOpen.value = true;
};

const openEditModal = (coupon) => {
  editingCoupon.value = coupon;
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
  editingCoupon.value = null;
};

const handleSaved = () => {
  toast.success(t("admin.marketing.coupons.alerts.saveSuccess"));
  fetchCoupons(pagination.value.currentPage);
};

const openDeleteModal = (coupon) => {
  couponToDelete.value = coupon;
};

const handleDeleteConfirm = async () => {
  if (!couponToDelete.value) return;
  deleteLoading.value = true;
  try {
    await api.delete(`/admin/marketing/coupons/${couponToDelete.value.id}`);
    toast.success(t("admin.marketing.coupons.alerts.deleteSuccess"));
    couponToDelete.value = null;
    fetchCoupons(pagination.value.currentPage);
  } catch {
    toast.error(t("admin.marketing.coupons.alerts.deleteError"));
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
  fetchCoupons(1);
};

let debounceTimer;
watch(search, () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchCoupons(1);
  }, 300);
});

watch(statusFilter, () => {
  fetchCoupons(1);
});

onMounted(() => {
  fetchCoupons();
});
</script>

<style scoped></style>
