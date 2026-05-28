<template>
  <div class="space-y-4">
    <div class="flex justify-start">
      <AppSelect
        v-model="internalStatus"
        :options="payoutOptions"
        class="sm:w-64"
        @update:model-value="onFilter"
      />
    </div>

    <AdminTable
      :headers="headers"
      :items="payouts"
      :loading="loading"
      :empty-text="$t('admin.affiliates.messages.no_payouts')"
    >
      <template #row="{ item }">
        <td class="px-4 py-4">
          <div class="flex items-center gap-3">
            <div
              class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 font-bold text-xs uppercase"
            >
              {{ item.affiliate.userName.charAt(0) }}
            </div>
            <div>
              <p
                class="font-bold text-gray-900 dark:text-white truncate max-w-[150px]"
              >
                {{ item.affiliate.userName }}
              </p>
              <p class="text-xs text-gray-500">
                {{ item.affiliate.userEmail }}
              </p>
            </div>
          </div>
        </td>
        <td class="px-4 py-4 text-sm font-bold text-gray-900 dark:text-white">
          ${{ (item.amount || 0).toFixed(2) }}
        </td>
        <td class="px-4 py-4">
          <AdminBadge :variant="getStatusVariant(item.status)">
            {{ $t(`admin.affiliates.status.${item.status}`) }}
          </AdminBadge>
        </td>
        <td class="px-4 py-4 text-sm text-gray-500 font-medium">
          {{
            new Date(item.requestedAt).toLocaleDateString(
              $i18n.locale === "uk" ? "uk-UA" : "en-US",
            )
          }}
        </td>
        <td class="px-4 py-4">
          <div class="flex justify-end gap-2">
            <button
              v-if="item.status === 'requested'"
              class="px-3 py-1.5 bg-green-50 hover:bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400 rounded-lg text-xs font-bold transition-colors"
              @click="$emit('approve', item)"
            >
              {{ $t("admin.affiliates.actions.approve") }}
            </button>
            <button
              v-if="item.status === 'processing'"
              class="px-3 py-1.5 bg-primary-50 hover:bg-primary-100 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400 rounded-lg text-xs font-bold transition-colors"
              @click="$emit('mark-paid', item)"
            >
              {{ $t("admin.affiliates.actions.mark_paid") }}
            </button>
            <button
              v-if="['requested', 'processing'].includes(item.status)"
              class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400 rounded-lg text-xs font-bold transition-colors"
              @click="$emit('reject', item)"
            >
              {{ $t("admin.affiliates.actions.reject") }}
            </button>
          </div>
        </td>
      </template>
    </AdminTable>
  </div>
</template>

<script setup>
import {computed, ref, watch} from "vue";
import AdminTable from "@/components/admin/ui/Data/AdminTable.vue";
import AdminBadge from "@/components/admin/ui/Data/AdminBadge.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import {useI18n} from "vue-i18n";

const { t } = useI18n();
const props = defineProps({
  payouts: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  statusFilter: String,
});

const emit = defineEmits([
  "update:statusFilter",
  "filter",
  "approve",
  "mark-paid",
  "reject",
]);

const internalStatus = ref(props.statusFilter);

const payoutOptions = computed(() => [
  {id: "", name: t("admin.affiliates.filters.all_payouts")},
  {id: "requested", name: t("admin.affiliates.status.requested")},
  {id: "processing", name: t("admin.affiliates.status.processing")},
  {id: "paid", name: t("admin.affiliates.status.paid")},
  {id: "rejected", name: t("admin.affiliates.status.rejected")},
]);

const headers = [
  {key: "partner", label: t("admin.affiliates.table.partner")},
  {key: "amount", label: t("admin.affiliates.table.amount")},
  {key: "status", label: t("admin.affiliates.table.status")},
  {key: "requested_at", label: t("admin.affiliates.table.requested_at")},
  {
    key: "actions",
    label: t("admin.affiliates.table.actions"),
    class: "text-right",
  },
];

const getStatusVariant = (status) => {
  const map = {
    paid: "success",
    processing: "info",
    requested: "warning",
    rejected: "error",
  };
  return map[status] || "info";
};

const onFilter = () => {
  emit("update:statusFilter", internalStatus.value);
  emit("filter");
};

watch(
  () => props.statusFilter,
  (val) => (internalStatus.value = val),
);
</script>
