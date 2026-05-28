<template>
  <AdminTable
    :headers="headers"
    :items="referrals"
    :loading="loading"
    :empty-text="$t('admin.affiliates.messages.no_referrals')"
  >
    <template #row="{ item }">
      <td class="px-4 py-4">
        <div class="flex items-center gap-3">
          <div
            class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 font-bold text-xs"
          >
            {{ item.userName.charAt(0) }}
          </div>
          <div>
            <p
              class="font-bold text-gray-900 dark:text-white truncate max-w-[150px]"
            >
              {{ item.userName }}
            </p>
            <p class="text-xs text-gray-500">
              {{ item.email }}
            </p>
          </div>
        </div>
      </td>
      <td class="px-4 py-4">
        <AdminBadge
          variant="primary"
          custom-class="text-[10px] uppercase font-bold"
        >
          {{ item.planName }}
        </AdminBadge>
      </td>
      <td class="px-4 py-4 text-sm font-bold text-green-600">
        ${{ (item.commission || 0).toFixed(2) }}
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
  referrals: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const headers = [
  {key: "user", label: t("admin.affiliates.table.user")},
  {key: "plan", label: t("admin.affiliates.table.plan")},
  {key: "earnings", label: t("admin.affiliates.table.earnings")},
  {
    key: "date",
    label: t("admin.affiliates.table.joined_at"),
    class: "text-right",
  },
];
</script>
