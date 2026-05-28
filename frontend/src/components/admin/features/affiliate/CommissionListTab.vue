<template>
  <AdminTable
    :headers="headers"
    :items="commissions"
    :loading="loading"
    :empty-text="$t('admin.affiliates.messages.no_commissions')"
  >
    <template #row="{ item }">
      <td class="px-4 py-4">
        <div class="flex items-center gap-3">
          <div
            class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 font-bold text-xs"
          >
            {{ item.userName.charAt(0) }}
          </div>
          <div>
            <p
              class="font-bold text-gray-900 dark:text-white truncate max-w-[150px]"
            >
              {{ item.userName }}
            </p>
            <p class="text-xs text-gray-400">
              <b>{{ $t("admin.affiliates.table.plan") }}:</b>
              {{ item.planName }}
            </p>
          </div>
        </div>
      </td>
      <td class="px-4 py-4 text-sm font-bold text-green-600">
        ${{ (item.amount || 0).toFixed(2) }}
      </td>
      <td class="px-4 py-4 text-xs font-mono text-gray-500 uppercase">
        {{ item.type }}
      </td>
      <td class="px-4 py-4">
        <AdminBadge :variant="item.status === 'available' ? 'success' : 'info'">
          {{ $t(`admin.affiliates.status.${item.status}`) }}
        </AdminBadge>
      </td>
      <td class="px-4 py-4 text-sm text-gray-500 font-medium text-right">
        {{
          new Date(item.createdAt).toLocaleDateString(
            $i18n.locale === "uk" ? "uk-UA" : "en-US",
          )
        }}
      </td>
    </template>
  </AdminTable>
</template>

<script setup>
import AdminTable from "@/components/admin/ui/Data/AdminTable.vue";
import AdminBadge from "@/components/admin/ui/Data/AdminBadge.vue";
import {useI18n} from "vue-i18n";

const { t } = useI18n();
defineProps({
  commissions: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const headers = [
  {
    key: "partner_referral",
    label: t("admin.affiliates.table.partner_referral"),
  },
  {key: "amount", label: t("admin.affiliates.table.amount")},
  {key: "type", label: t("admin.affiliates.table.type")},
  {key: "status", label: t("admin.affiliates.table.status")},
  {key: "date", label: t("admin.affiliates.table.date"), class: "text-right"},
];
</script>
