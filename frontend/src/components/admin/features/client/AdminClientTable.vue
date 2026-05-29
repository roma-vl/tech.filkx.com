<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden relative"
  >
    <AdminTable
      :headers="headers"
      :items="clients"
      :empty-text="$t('admin.users.table.empty')"
    >
      <template #row="{ item: row }">
        <td class="px-4 py-4">
          <div
            class="flex items-center gap-3"
            :class="{ 'opacity-60': row.status === 'deleted' }"
          >
            <div
              class="w-10 h-10 rounded-full flex items-center justify-center font-black text-white shadow-sm ring-2 ring-white dark:ring-gray-800"
              :style="{ background: row.avatarColor || '#3b82f6' }"
            >
              {{ row.name?.charAt(0) || "?" }}
            </div>
            <div>
              <div
                class="font-bold text-gray-900 dark:text-gray-100 flex flex-wrap items-center gap-2"
              >
                {{ row.name }}
                <AdminBadge
                  v-if="row.status === 'deleted'"
                  variant="error"
                  custom-class="!py-0 !px-1.5 !text-[9px]"
                >
                  {{ $t("admin.users.status.deleted") }}
                </AdminBadge>
              </div>
              <div class="text-xs text-gray-500 font-medium">
                {{ row.email }}
              </div>
            </div>
          </div>
        </td>

        <td class="px-4 py-4">
          <div class="flex flex-wrap gap-1">
            <span
              v-for="role in row.roles"
              :key="role"
              class="text-[9px] px-1.5 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded font-bold dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-400 uppercase tracking-tighter"
            >
              {{ role }}
            </span>
            <span v-if="!row.roles || row.roles.length === 0" class="text-xs text-gray-400">
              Покупець
            </span>
          </div>
        </td>

        <td class="px-4 py-4">
          <div class="flex flex-col gap-1.5">
            <AdminBadge :variant="getStatusVariant(row.status)">
              <span
                v-if="row.status === 'active'"
                class="w-1 h-1 rounded-full bg-current mr-1 animate-pulse"
              />
              {{ $t(`admin.users.status.${row.status}`) }}
            </AdminBadge>
          </div>
        </td>

        <td
          class="px-4 py-4 text-xs font-bold text-gray-600 dark:text-gray-400 whitespace-nowrap"
        >
          <div>{{ formatDate(row.createdAt) }}</div>
          <p class="text-[10px] text-gray-400 font-normal italic mt-0.5">
            {{ t("admin.users.table.idPrefix") }} CL{{
              row.id.toString().padStart(6, "0")
            }}
          </p>
        </td>

        <td class="px-4 py-4 text-right">
          <div class="flex justify-end items-center gap-1">
            <AppButton
              variant="secondary"
              class="!p-2 text-gray-400 hover:!text-amber-600 hover:!bg-amber-50 dark:hover:!bg-amber-900/30"
              :title="t('admin.impersonation.title')"
              @click="$emit('impersonate', row)"
            >
              <IdentificationIcon class="w-5 h-5" />
            </AppButton>
            <AppButton
              variant="secondary"
              class="!p-2 text-gray-400 hover:!text-primary-600 hover:!bg-primary-50 dark:hover:!bg-primary-900/30"
              :title="t('admin.users.actions.edit')"
              @click="$emit('edit', row)"
            >
              <PencilSquareIcon class="w-5 h-5" />
            </AppButton>
            <AppButton
              v-if="row.status !== 'deleted'"
              variant="secondary"
              class="!p-2 text-gray-400 hover:!text-amber-600 hover:!bg-amber-50 dark:hover:!bg-amber-900/30"
              :title="
                row.status === 'suspended'
                  ? t('admin.users.actions.unsuspend')
                  : t('admin.users.actions.suspend')
              "
              @click="$emit('suspend', row)"
            >
              <NoSymbolIcon
                class="w-5 h-5"
                :class="{ 'text-amber-600': row.status === 'suspended' }"
              />
            </AppButton>
            <AppButton
              v-if="row.status !== 'deleted'"
              variant="secondary"
              class="!p-2 text-gray-400 hover:!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/30"
              :title="t('admin.users.actions.delete')"
              @click="$emit('delete', row)"
            >
              <TrashIcon class="w-5 h-5" />
            </AppButton>
          </div>
        </td>
      </template>
    </AdminTable>
  </div>
</template>

<script setup>
import {computed} from "vue";
import {useI18n} from "vue-i18n";
import AdminTable from "@/components/admin/ui/Data/AdminTable.vue";
import AdminBadge from "@/components/admin/ui/Data/AdminBadge.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import {IdentificationIcon, NoSymbolIcon, PencilSquareIcon, TrashIcon,} from "@heroicons/vue/24/outline";

const { t } = useI18n();

defineProps({
  clients: {
    type: Array,
    required: true,
  },
});

defineEmits(["edit", "suspend", "delete", "impersonate"]);

const headers = computed(() => [
  { key: "client", label: t("admin.users.table.client") },
  { key: "roles", label: "Ролі" },
  { key: "status", label: t("admin.users.filters.status") },
  { key: "joinDate", label: t("admin.users.table.joinDate") },
  {
    key: "actions",
    label: t("admin.users.table.actions"),
    class: "text-right",
  },
]);

const getStatusVariant = (status) => {
  switch (status) {
    case "active":
      return "success";
    case "trial":
      return "info";
    case "suspended":
      return "warning";
    case "expired":
      return "error";
    case "deleted":
      return "neutral";
    default:
      return "neutral";
  }
};

const formatDate = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString();
};
</script>
