<template>
  <div class="space-y-4">
    <div class="flex flex-col sm:flex-row gap-4">
      <AppInput
        v-model="internalSearch"
        :placeholder="$t('admin.affiliates.actions.search_placeholder')"
        class="flex-1"
        @input="onSearch"
      >
        <template #prepend>
          <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
        </template>
      </AppInput>

      <AppSelect
        v-model="internalStatus"
        :options="statusOptions"
        class="sm:w-64"
        @update:model-value="onFilter"
      />
    </div>

    <AdminTable
      :headers="headers"
      :items="affiliates"
      :loading="loading"
      :empty-text="$t('admin.affiliates.messages.no_partners')"
    >
      <template #row="{ item }">
        <td class="px-4 py-4">
          <div class="flex items-center gap-3">
            <div
              class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 font-bold text-xs"
            >
              {{ item.user.name.charAt(0) }}
            </div>
            <div>
              <p
                class="font-bold text-gray-900 dark:text-white truncate max-w-[150px]"
              >
                {{ item.user.name }}
              </p>
              <p class="text-xs text-gray-500">
                {{ item.user.email }}
              </p>
            </div>
          </div>
        </td>
        <td
          class="px-4 py-4 text-sm font-mono text-gray-600 dark:text-gray-400"
        >
          {{ item.referralCode }}
        </td>
        <td class="px-4 py-4 text-sm font-medium">
          {{ item.totalReferrals }}
        </td>
        <td class="px-4 py-4 text-sm font-bold text-green-600">
          ${{ (item.totalEarnings || 0).toFixed(2) }}
        </td>
        <td class="px-4 py-4 text-sm font-bold">
          ${{ (item.availableBalance || 0).toFixed(2) }}
        </td>
        <td class="px-4 py-4">
          <AdminBadge :variant="getStatusVariant(item.status)">
            {{ $t(`admin.affiliates.status.${item.status}`) }}
          </AdminBadge>
        </td>
        <td class="px-4 py-4">
          <button
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg text-primary-600 transition-colors"
            title="Change status"
            @click="$emit('open-modal', item)"
          >
            <PencilSquareIcon class="w-5 h-5" />
          </button>
        </td>
      </template>
    </AdminTable>
  </div>
</template>

<script setup>
import {computed, ref, watch} from "vue";
import AdminTable from "@/components/admin/ui/Data/AdminTable.vue";
import AdminBadge from "@/components/admin/ui/Data/AdminBadge.vue";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import {MagnifyingGlassIcon, PencilSquareIcon,} from "@heroicons/vue/24/outline";
import {useI18n} from "vue-i18n";

const { t } = useI18n();
const props = defineProps({
  affiliates: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  searchQuery: String,
  statusFilter: String,
});

const emit = defineEmits([
  "update:searchQuery",
  "update:statusFilter",
  "search",
  "filter",
  "open-modal",
]);

const internalSearch = ref(props.searchQuery);
const internalStatus = ref(props.statusFilter);

const statusOptions = computed(() => [
  {id: "", name: t("admin.affiliates.filters.all_statuses")},
  {id: "pending", name: t("admin.affiliates.status.pending")},
  {id: "active", name: t("admin.affiliates.status.active")},
  {id: "rejected", name: t("admin.affiliates.status.rejected")},
  {id: "suspended", name: t("admin.affiliates.status.suspended")},
]);

const headers = [
  {key: "partner", label: t("admin.affiliates.table.partner")},
  {key: "code", label: t("admin.affiliates.table.code")},
  {key: "referrals", label: t("admin.affiliates.table.referrals")},
  {key: "earnings", label: t("admin.affiliates.table.earnings")},
  {key: "available", label: t("admin.affiliates.table.available")},
  {key: "status", label: t("admin.affiliates.table.status")},
  {
    key: "actions",
    label: t("admin.affiliates.table.actions"),
    class: "text-right",
  },
];

const getStatusVariant = (status) => {
  const map = {
    active: "success",
    pending: "warning",
    rejected: "error",
    suspended: "neutral",
  };
  return map[status] || "info";
};

let searchTimeout;
const onSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    emit("update:searchQuery", internalSearch.value);
    emit("search");
  }, 500);
};

const onFilter = () => {
  emit("update:statusFilter", internalStatus.value);
  emit("filter");
};

watch(
  () => props.searchQuery,
  (val) => (internalSearch.value = val),
);
watch(
  () => props.statusFilter,
  (val) => (internalStatus.value = val),
);
</script>
