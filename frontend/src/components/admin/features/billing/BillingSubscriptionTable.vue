<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
      <h2 class="text-xl font-bold">
        {{ t("admin.billing.subscriptions.title") }}
      </h2>
      <div class="flex w-full sm:w-auto gap-3">
        <div class="flex-1 sm:flex-initial">
          <AppSelect
            :model-value="filter"
            :options="statusOptions"
            @update:model-value="$emit('update:filter', $event)"
          />
        </div>
        <button
          class="p-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all group"
          :title="t('admin.billing.pending.refresh')"
          @click="$emit('refresh')"
        >
          <ArrowPathIcon
            class="w-5 h-5 text-gray-500 group-hover:text-primary-600 transition-colors"
          />
        </button>
      </div>
    </div>

    <div
      class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden relative"
    >
      <AppLoadingOverlay :loading="loading" />

      <AdminTable
        :headers="headers"
        :items="subscriptions"
        :empty-text="t('admin.billing.subscriptions.empty')"
      >
        <template #row="{ item: sub }">
          <!-- User info with a more accented look -->
          <td class="px-6 py-5">
            <div class="flex items-center gap-4">
              <div class="relative group">
                <div
                  class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500/10 to-primary-600/20 dark:from-primary-900/40 dark:to-primary-800/20 flex items-center justify-center font-bold text-primary-700 dark:text-primary-300 shadow-sm border border-primary-100 dark:border-primary-800 transition-transform group-hover:scale-105 duration-300"
                >
                  {{ sub.user?.name?.charAt(0) || "?" }}
                </div>
                <div
                  class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-gray-800"
                  :class="
                    sub.status === 'active' ? 'bg-green-500' : 'bg-gray-400'
                  "
                />
              </div>
              <div>
                <p
                  class="font-bold text-gray-900 dark:text-gray-100 leading-tight"
                >
                  {{ sub.user?.name || "Unknown" }}
                </p>
                <p class="text-xs text-gray-400 font-medium">
                  {{ sub.user?.email }}
                </p>
              </div>
            </div>
          </td>

          <!-- Plan with accentuation -->
          <td class="px-6 py-5">
            <div class="flex flex-col gap-1.5">
              <div class="flex items-center gap-2">
                <div
                  class="px-2.5 py-1 bg-primary-50 dark:bg-primary-900/30 border border-primary-100 dark:border-primary-800/50 rounded-lg"
                >
                  <span
                    class="text-xs font-black text-primary-700 dark:text-primary-400 capitalize whitespace-nowrap"
                  >
                    {{ sub.plan?.name }}
                  </span>
                </div>
                <span
                  class="text-[10px] text-gray-400 font-black uppercase tracking-widest hidden sm:inline"
                >
                  {{ sub.billingCycle || "monthly" }}
                </span>
              </div>

              <!-- Addons nested neatly -->
              <div
                v-if="sub.addons?.length"
                class="flex flex-wrap gap-1.5 mt-0.5"
              >
                <span
                  v-for="subAddon in sub.addons"
                  :key="subAddon.id"
                  class="text-[9px] font-black px-1.5 py-0.5 rounded-md bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-500 border border-amber-100/50 dark:border-amber-900/30 flex items-center gap-1"
                >
                  <TagIcon class="w-2.5 h-2.5" />
                  {{ subAddon.addon?.name }}
                </span>
              </div>
            </div>
          </td>

          <!-- Price column with accent -->
          <td class="px-6 py-5">
            <div class="flex flex-col items-start">
              <span
                class="text-lg font-black bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-400"
              >
                ${{ sub.plan?.price || 0 }}
              </span>
              <span
                class="text-[10px] text-gray-400 font-bold uppercase tracking-tight"
              >
                {{
                  t(
                    `admin.billing.subscriptions.changePlan.cycles.${sub.billingCycle || "monthly"}`,
                  )
                }}
              </span>
            </div>
          </td>

          <!-- Status badge -->
          <td class="px-6 py-5">
            <AdminBadge
              :variant="getStatusVariant(sub.status)"
              size="sm"
            >
              {{ t(`admin.billing.subscriptions.status.${sub.status}`) }}
            </AdminBadge>
          </td>

          <!-- Refined actions -->
          <td class="px-6 py-5 text-right">
            <div class="flex justify-end items-center gap-1.5">
              <button
                class="p-2 rounded-xl hover:bg-primary-50 dark:hover:bg-primary-900/30 text-gray-400 hover:text-primary-600 transition-all border border-transparent hover:border-primary-100 dark:hover:border-primary-900/50 shadow-none hover:shadow-sm"
                :title="t('admin.billing.subscriptions.actions.changePlan')"
                @click="$emit('change-plan', sub)"
              >
                <PencilSquareIcon class="w-5 h-5" />
              </button>

              <button
                v-if="
                  ['active', 'trial', 'cancelled'].includes(sub.status) &&
                    sub.status !== 'expired'
                "
                class="p-2 rounded-xl hover:bg-amber-50 dark:hover:bg-amber-900/30 text-gray-400 hover:text-amber-600 transition-all border border-transparent hover:border-amber-100 dark:hover:border-amber-900/50 shadow-none hover:shadow-sm"
                :title="t('admin.billing.subscriptions.actions.expire')"
                @click="$emit('expire', sub)"
              >
                <NoSymbolIcon class="w-5 h-5" />
              </button>

              <button
                v-if="sub.status === 'active' || sub.status === 'trial'"
                class="p-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/30 text-gray-400 hover:text-red-600 transition-all border border-transparent hover:border-red-100 dark:hover:border-red-900/50 shadow-none hover:shadow-sm"
                :title="t('admin.billing.subscriptions.actions.cancel')"
                @click="$emit('cancel', sub)"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>
          </td>
        </template>
      </AdminTable>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AdminTable from "@/components/admin/ui/Data/AdminTable.vue";
import AdminBadge from "@/components/admin/ui/Data/AdminBadge.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppLoadingOverlay from "@/components/admin/ui/Feedback/AppLoadingOverlay.vue";
import {
  ArrowPathIcon,
  NoSymbolIcon,
  PencilSquareIcon,
  TagIcon,
  TrashIcon,
} from "@heroicons/vue/24/outline";

const { t } = useI18n();

defineProps({
  subscriptions: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  filter: {
    type: String,
    default: "",
  },
});

defineEmits(["update:filter", "refresh", "change-plan", "cancel", "expire"]);

const headers = computed(() => [
  { key: "user", label: t("admin.billing.subscriptions.table.user") },
  { key: "plan", label: t("admin.billing.subscriptions.table.plan") },
  { key: "price", label: t("admin.billing.pending.table.amount") },
  { key: "status", label: t("admin.billing.subscriptions.table.status") },
  {
    key: "actions",
    label: t("admin.billing.subscriptions.table.actions"),
    class: "text-right",
  },
]);

const statusOptions = computed(() => [
  { id: "", name: t("admin.billing.subscriptions.allStatuses") },
  { id: "trial", name: t("admin.billing.subscriptions.status.trial") },
  { id: "active", name: t("admin.billing.subscriptions.status.active") },
  { id: "cancelled", name: t("admin.billing.subscriptions.status.cancelled") },
  { id: "expired", name: t("admin.billing.subscriptions.status.expired") },
]);

const getStatusVariant = (status) => {
  switch (status) {
    case "active":
      return "success";
    case "trial":
      return "info";
    case "expired":
      return "error";
    case "cancelled":
      return "warning";
    default:
      return "neutral";
  }
};
</script>
