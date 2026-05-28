<template>
  <div class="space-y-8">
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-2"
    >
      <AppButton
        variant="primary"
        size="lg"
        @click="createNewPlan"
      >
        <template #prefix>
          <PlusIcon class="w-6 h-6" />
        </template>
        {{ t("admin.plans.createTier") }}
      </AppButton>
    </div>

    <div
      v-if="plansLoading && !plans.length"
      class="flex flex-col items-center justify-center py-24 bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-300 dark:border-gray-700"
    >
      <div class="loading loading-spinner loading-lg text-primary-500 mb-4" />
      <p class="text-gray-500 font-bold uppercase tracking-widest text-xs">
        {{ t("admin.plans.alerts.loading") }}
      </p>
    </div>

    <div
      v-else
      class="grid grid-cols-1 lg:grid-cols-3 gap-8"
    >
      <PlanCard
        v-for="plan in plans"
        :key="plan.id || plan.tempId"
        :plan="plan"
        :loading="savingPlanId === (plan.id || plan.tempId)"
        @save="handleSavePlan"
        @delete="openDeleteModal"
      />
    </div>

    <div class="mt-20 pt-12 border-t border-gray-100 dark:border-gray-700/50">
      <div class="flex items-center justify-between mb-10">
        <div>
          <h3
            class="text-2xl font-black text-gray-900 dark:text-white tracking-tight"
          >
            {{ t("admin.plans.addons.title") }}
          </h3>
          <p
            class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1"
          >
            {{ t("admin.plans.addons.description") }}
          </p>
        </div>
        <AppButton
          variant="primary"
          size="lg"
          @click="createNewAddon"
        >
          <template #prefix>
            <PlusIcon class="w-6 h-6"/>
          </template>
          {{ t("admin.plans.addons.addItem") }}
        </AppButton>
      </div>

      <div
        v-if="addonsLoading && !addons.length"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8"
      >
        <div
          v-for="i in 4"
          :key="i"
          class="h-56 bg-gray-100 dark:bg-gray-800/50 rounded-3xl animate-pulse"
        />
      </div>

      <div
        v-else
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8"
      >
        <AddonItem
          v-for="addon in addons"
          :key="addon.id || addon.tempId"
          :addon="addon"
          :loading="savingAddonId === (addon.id || addon.tempId)"
          @save="handleSaveAddon"
          @delete="openAddonDeleteModal"
        />
      </div>
    </div>

    <PlanDeleteModal
      :is-open="!!planToDelete"
      :plan="planToDelete"
      :loading="deleteLoading"
      @close="planToDelete = null"
      @confirm="handleDeleteConfirm"
    />

    <AddonDeleteModal
      :is-open="!!addonToDelete"
      :addon="addonToDelete"
      :loading="deleteLoading"
      @close="addonToDelete = null"
      @confirm="handleDeleteAddonConfirm"
    />
  </div>
</template>

<script setup>
import {onMounted, ref} from "vue";
import {PlusIcon} from "@heroicons/vue/24/outline";
import {useToast} from "vue-toastification";
import {useI18n} from "vue-i18n";
import api from "@/services/api";

import PlanCard from "@/components/admin/features/plan-builder/PlanCard.vue";
import AddonItem from "@/components/admin/features/plan-builder/AddonItem.vue";
import PlanDeleteModal from "@/components/admin/features/plan-builder/PlanDeleteModal.vue";
import AddonDeleteModal from "@/components/admin/features/plan-builder/AddonDeleteModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const toast = useToast();
const { t } = useI18n();

const plans = ref([]);
const addons = ref([]);
const plansLoading = ref(false);
const addonsLoading = ref(false);
const savingPlanId = ref(null);
const savingAddonId = ref(null);
const deleteLoading = ref(false);

const planToDelete = ref(null);
const addonToDelete = ref(null);

const GB = 1024 * 1024 * 1024;

const formatPlanForFrontend = (plan) => {
  return {
    ...plan,
    features: {
      ...plan.features,
      storageGB: plan.features?.storageLimit
        ? Math.round(plan.features.storageLimit / GB)
        : 0,
      concurrentStreams: plan.features?.concurrentStreams ?? 0,
      maxVideos: plan.features?.maxVideos ?? 0,
      streamQuality: plan.features?.streamQuality ?? "sd",
      platforms: plan.features?.platforms ?? [],
      hasPlaylists: plan.features?.hasPlaylists ?? false,
      hasScheduler: plan.features?.hasScheduler ?? false,
      hasPrioritySupport: plan.features?.hasPrioritySupport ?? false,
      watermarkType: plan.features?.watermarkType ?? "platform",
    },
  };
};

const formatPlanForBackend = (plan) => {
  return {
    name: plan.name,
    description: plan.description,
    priceMonthly: plan.priceMonthly,
    isActive: plan.isActive,
    isFeatured: plan.isFeatured,
    sortOrder: plan.sortOrder ?? 0,
    features: {
      storageLimit: (plan.features.storageGB || 0) * GB,
      concurrentStreams: plan.features.concurrentStreams || 0,
      maxVideos: plan.features.maxVideos || 0,
      streamQuality: plan.features.streamQuality || "sd",
      platforms: plan.features.platforms || [],
      hasPlaylists: plan.features.hasPlaylists,
      hasScheduler: plan.features.hasScheduler,
      hasPrioritySupport: plan.features.hasPrioritySupport,
      watermarkType: plan.features.watermarkType || "platform",
    },
  };
};

const fetchPlans = async () => {
  plansLoading.value = true;
  try {
    const { data } = await api.get("/admin/billing/plans");
    plans.value = data.data.map(formatPlanForFrontend);
  } catch (e) {
    toast.error(t("admin.plans.alerts.loadPlansError"));
  } finally {
    plansLoading.value = false;
  }
};

const fetchAddons = async () => {
  addonsLoading.value = true;
  try {
    const { data } = await api.get("/admin/billing/addons");
    addons.value = data.data;
  } catch (e) {
    toast.error(t("admin.plans.alerts.loadAddonsError"));
  } finally {
    addonsLoading.value = false;
  }
};

const createNewPlan = () => {
  const tempId = Date.now();
  plans.value.unshift({
    tempId,
    name: "New Plan",
    priceMonthly: 0,
    isActive: false,
    isFeatured: false,
    features: {
      storageGB: 10,
      concurrentStreams: 1,
      maxVideos: 100,
      streamQuality: "sd",
      platforms: ["youtube"],
      hasPlaylists: false,
      hasScheduler: false,
      hasPrioritySupport: false,
      watermarkType: "platform",
    },
  });
};

const handleSavePlan = async (plan) => {
  const id = plan.id || plan.tempId;
  savingPlanId.value = id;
  try {
    const payload = formatPlanForBackend(plan);
    let res;
    if (plan.id) {
      res = await api.put(`/admin/billing/plans/${plan.id}`, payload);
    } else {
      res = await api.post("/admin/billing/plans", payload);
    }

    const idx = plans.value.findIndex((p) => (p.id || p.tempId) === id);
    if (idx !== -1) {
      plans.value[idx] = formatPlanForFrontend(res.data.data);
    }
    toast.success(t("admin.plans.alerts.saveSuccess"));
  } catch (e) {
    toast.error(t("admin.plans.alerts.saveError"));
  } finally {
    savingPlanId.value = null;
  }
};

const openDeleteModal = (plan) => {
  planToDelete.value = plan;
};

const handleDeleteConfirm = async (plan) => {
  if (!plan.id) {
    plans.value = plans.value.filter((p) => p !== plan);
    planToDelete.value = null;
    return;
  }

  deleteLoading.value = true;
  try {
    await api.delete(`/admin/billing/plans/${plan.id}`);
    plans.value = plans.value.filter((p) => p.id !== plan.id);
    toast.success(t("admin.plans.alerts.deleteSuccess"));
    planToDelete.value = null;
  } catch (e) {
    toast.error(t("admin.plans.alerts.deleteError"));
  } finally {
    deleteLoading.value = false;
  }
};

const createNewAddon = () => {
  const tempId = Date.now();
  addons.value.unshift({
    tempId,
    name: "New Addon",
    priceMonthly: 0,
    type: "features",
    isActive: false,
    effect: [],
  });
};

const handleSaveAddon = async (addon) => {
  const id = addon.id || addon.tempId;
  savingAddonId.value = id;
  try {
    const payload = {
      ...addon,
      priceMonthly: addon.priceMonthly,
      isActive: addon.isActive,
      effect: addon.effect || [],
    };

    let res;
    if (addon.id) {
      res = await api.put(`/admin/billing/addons/${addon.id}`, payload);
    } else {
      res = await api.post("/admin/billing/addons", payload);
    }

    const idx = addons.value.findIndex((a) => (a.id || a.tempId) === id);
    if (idx !== -1) {
      addons.value[idx] = res.data.data;
    }
    toast.success(t("admin.plans.alerts.addonSaveSuccess"));
  } catch (e) {
    toast.error(t("admin.plans.alerts.addonSaveError"));
  } finally {
    savingAddonId.value = null;
  }
};

const openAddonDeleteModal = (addon) => {
  addonToDelete.value = addon;
};

const handleDeleteAddonConfirm = async (addon) => {
  if (!addon.id) {
    addons.value = addons.value.filter((a) => a !== addon);
    addonToDelete.value = null;
    return;
  }

  deleteLoading.value = true;
  try {
    await api.delete(`/admin/billing/addons/${addon.id}`);
    addons.value = addons.value.filter((a) => a.id !== addon.id);
    toast.success(t("admin.plans.alerts.addonDeleteSuccess"));
    addonToDelete.value = null;
  } catch (e) {
    toast.error(t("admin.plans.alerts.addonDeleteError"));
  } finally {
    deleteLoading.value = false;
  }
};

onMounted(() => {
  fetchPlans();
  fetchAddons();
});
</script>
