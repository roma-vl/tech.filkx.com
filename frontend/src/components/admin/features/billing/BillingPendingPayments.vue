<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between mb-2">
      <h2 class="text-xl font-bold">
        {{ t("admin.billing.pending.title") }}
      </h2>
      <button
        class="text-sm font-bold text-primary-600 hover:text-primary-700 flex items-center gap-1.5 transition-colors group"
        @click="$emit('refresh')"
      >
        <ArrowPathIcon
          class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500"
        />
        {{ t("admin.billing.pending.refresh") }}
      </button>
    </div>

    <div
      class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden relative"
    >
      <AppLoadingOverlay :loading="loading" />

      <AdminTable
        :headers="headers"
        :items="payments"
        :empty-text="t('admin.billing.pending.empty')"
      >
        <template #row="{ item: payment }">
          <!-- User Info -->
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div>
                <p class="font-bold text-gray-900 dark:text-gray-100">
                  {{
                    payment.user?.name || t("admin.billing.pending.unknownUser")
                  }}
                </p>
                <p class="text-[10px] text-gray-500 font-medium">
                  {{ payment.user?.email }}
                </p>
                <p class="text-[9px] text-gray-400 mt-1">
                  {{ formatDate(payment.createdAt) }}
                </p>
              </div>
            </div>
          </td>

          <!-- Payment Details -->
          <td class="px-6 py-4">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span
                  class="text-[10px] font-black px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 uppercase tracking-tighter"
                >
                  {{ payment.type || "subscription" }}
                </span>
                <span class="font-bold text-gray-900 dark:text-gray-100">
                  {{
                    payment.purchaseDetails?.plan?.name ||
                      payment.subscription?.plan?.name ||
                      "N/A"
                  }}
                </span>
              </div>

              <!-- Addons -->
              <div
                v-if="payment.purchaseDetails?.addons?.length"
                class="flex flex-wrap gap-1"
              >
                <span
                  v-for="addon in payment.purchaseDetails.addons"
                  :key="addon.name"
                  class="text-[9px] px-1.5 py-0.5 rounded bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 border border-primary-100 dark:border-primary-900/30 font-bold"
                >
                  +{{ addon.name }}
                </span>
              </div>

              <!-- Promo -->
              <div
                v-if="
                  payment.purchaseDetails?.campaign ||
                    payment.purchaseDetails?.coupon
                "
                class="flex items-center gap-2"
              >
                <span
                  v-if="payment.purchaseDetails?.campaign"
                  class="text-[9px] text-blue-600 dark:text-blue-400 font-bold flex items-center gap-0.5"
                >
                  <TagIcon class="w-2.5 h-2.5" />
                  {{
                    payment.purchaseDetails.campaign.name ||
                      payment.purchaseDetails.campaign.slug
                  }}
                </span>
                <span
                  v-if="payment.purchaseDetails?.coupon"
                  class="text-[9px] text-green-600 dark:text-green-400 font-bold flex items-center gap-0.5"
                >
                  <TagIcon class="w-2.5 h-2.5" />
                  {{ payment.purchaseDetails.coupon.code }}
                </span>
              </div>
            </div>
          </td>

          <!-- Amount -->
          <td class="px-6 py-4">
            <div class="flex flex-col">
              <span
                class="text-sm font-black text-primary-600 dark:text-primary-400"
              >
                ${{ payment.amount }} {{ payment.currency }}
              </span>
              <AdminBadge
                v-if="payment.status"
                variant="warning"
                size="sm"
                class="mt-1"
              >
                {{ payment.status }}
              </AdminBadge>
            </div>
          </td>

          <!-- Actions -->
          <td class="px-6 py-4 text-right">
            <div class="flex justify-end gap-1 items-center">
              <button
                class="px-3 py-1.5 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-[10px] font-black uppercase text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-all"
                @click="$emit('view-proof', payment)"
              >
                {{ t("admin.billing.pending.actions.viewProof") }}
              </button>
              <button
                class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-[10px] font-black uppercase transition-all shadow-sm"
                @click="$emit('approve', payment)"
              >
                {{ t("admin.billing.pending.actions.approve") }}
              </button>
              <button
                class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-[10px] font-black uppercase transition-all shadow-sm"
                @click="$emit('reject', payment)"
              >
                {{ t("admin.billing.pending.actions.reject") }}
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
import AppLoadingOverlay from "@/components/admin/ui/Feedback/AppLoadingOverlay.vue";
import { ArrowPathIcon, TagIcon } from "@heroicons/vue/24/outline";

const { t } = useI18n();

defineProps({
  payments: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["refresh", "view-proof", "approve", "reject"]);

const headers = computed(() => [
  { key: "user", label: t("admin.billing.subscriptions.table.user") },
  { key: "details", label: t("admin.billing.pending.table.details") },
  { key: "amount", label: t("admin.billing.pending.table.amount") },
  {
    key: "actions",
    label: t("admin.billing.subscriptions.table.actions"),
    class: "text-right",
  },
]);

const formatDate = (date) => {
  return date ? new Date(date).toLocaleString() : "";
};
</script>
