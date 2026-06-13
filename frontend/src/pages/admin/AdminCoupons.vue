<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <CouponStats :data="statsData" />

    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center">
      <!-- Search bar & filters toggle button -->
      <div class="flex items-center gap-3 flex-1 max-w-md">
        <AppInput
          v-model="search"
          :placeholder="t('admin.marketing.coupons.search')"
          class="flex-1"
        >
          <template #prepend>
            <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
          </template>
        </AppInput>
        <AppButton
          variant="secondary"
          class="!p-2.5 relative h-[38px] flex items-center justify-center shrink-0 border border-gray-300 dark:border-gray-600 rounded-lg hover:border-gray-400"
          :class="{
            'ring-2 ring-primary-500 !bg-primary-50 dark:!bg-primary-900/20 !border-primary-200 dark:!border-primary-800':
              showFilters,
          }"
          title="Фільтри"
          @click="showFilters = !showFilters"
        >
          <svg
            class="w-5 h-5 transition-colors"
            :class="showFilters ? 'text-[#00a046]' : 'text-gray-500'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
            />
          </svg>
          <span
            v-if="activeFiltersCount > 0"
            class="absolute -top-1 -right-1 w-5 h-5 bg-[#00a046] text-white text-[10px] flex items-center justify-center rounded-full font-black shadow-lg shadow-emerald-500/30 ring-2 ring-white dark:ring-gray-800"
          >
            {{ activeFiltersCount }}
          </span>
        </AppButton>
      </div>

      <!-- Add Coupon Button -->
      <AppButton
        variant="primary"
        class="flex items-center gap-2 shrink-0 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
        @click="openCreateModal"
      >
        <PlusIcon class="w-4 h-4 stroke-[2px]" />
        {{ t("admin.marketing.coupons.new") }}
      </AppButton>
    </div>

    <!-- Toggleable Filters Panel -->
    <transition name="expand">
      <div
        v-if="showFilters"
        class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xl space-y-6 animate-in slide-in-from-top-2 duration-300"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <AppSelect
            v-model="statusFilter"
            label="Статус"
            placeholder="Всі статуси"
            :options="[
              { id: '', name: 'Всі статуси' },
              { id: 'active', name: 'Активні' },
              { id: 'inactive', name: 'Неактивні' }
            ]"
            option-value="id"
            option-label="name"
          />
        </div>
        <div class="flex items-center justify-end pt-4 border-t border-gray-150 dark:border-gray-700">
          <AppButton
            variant="text"
            class="!text-red-500 hover:!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20 !px-4 !py-2 !rounded-xl font-bold"
            @click="resetFilters"
          >
            Скинути фільтри
          </AppButton>
        </div>
      </div>
    </transition>

    <CouponTable
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
import { PlusIcon, MagnifyingGlassIcon } from "@heroicons/vue/24/outline";

import CouponStats from "@/components/admin/features/marketing/coupons/CouponStats.vue";
import CouponTable from "@/components/admin/features/marketing/coupons/CouponTable.vue";
import CouponEditModal from "@/components/admin/features/marketing/coupons/CouponEditModal.vue";
import AppConfirmModal from "@/components/admin/ui/AppConfirmModal.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";

const { t } = useI18n();
const toast = useToast();

const coupons = ref([]);
const loading = ref(false);
const deleteLoading = ref(false);
const search = ref("");
const statusFilter = ref("");
const sortBy = ref("created_at");
const sortDir = ref("desc");
const showFilters = ref(false);

const activeFiltersCount = computed(() => {
  let count = 0;
  if (statusFilter.value) count++;
  return count;
});

const resetFilters = () => {
  search.value = "";
  statusFilter.value = "";
};

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
