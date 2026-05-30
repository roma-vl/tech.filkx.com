<template>
  <div
    class="group flex flex-col sm:flex-row sm:items-center gap-4 p-4 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-all duration-200"
  >
    <div class="flex items-center gap-4 flex-1 min-w-0">
      <div
        class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 flex-shrink-0"
      >
        <ShieldCheckIcon class="w-6 h-6" />
      </div>

      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-0.5">
          <h4
            class="font-bold text-gray-900 dark:text-white truncate group-hover:text-primary-500 transition-colors uppercase"
          >
            {{ role.name }}
          </h4>
          <span
            v-if="role.isSystem"
            class="px-1.5 py-0.5 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-[8px] font-black uppercase tracking-widest border border-amber-200 dark:border-amber-800/50"
          >
            {{ t("admin.roles.table.system_tag") }}
          </span>
          <span
            class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest border"
            :class="
              role.scope === 'global'
                ? 'bg-indigo-50 text-indigo-600 border-indigo-100 dark:bg-indigo-900/10 dark:text-indigo-400 dark:border-indigo-900/30'
                : 'bg-slate-50 text-slate-600 border-slate-100 dark:bg-slate-900/10 dark:text-slate-400 dark:border-slate-900/30'
            "
          >
            {{ role.scope }}
          </span>
        </div>
        <div
          class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400 font-medium"
        >
          <div class="flex items-center gap-1">
            <UsersIcon class="w-3.5 h-3.5 opacity-60" />
            {{ role.usersCount }}
            {{ t("admin.roles.table.users").toLowerCase() }}
          </div>
          <div
            class="truncate max-w-sm"
            :title="role.description || t('admin.roles.table.no_description')"
          >
            {{ role.description || t("admin.roles.table.no_description") }}
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2 self-end sm:self-auto">
      <AppButton
        variant="ghost"
        class="!p-2.5 !rounded-xl !text-gray-400 hover:!text-primary-600 !border !border-transparent hover:!border-primary-100 dark:hover:!border-primary-800"
        @click="$emit('edit', role)"
      >
        <PencilSquareIcon class="w-5 h-5" />
      </AppButton>
      <AppButton
        v-if="!role.isSystem"
        variant="ghost"
        class="!p-2.5 !rounded-xl !text-gray-400 hover:!text-red-600 !border !border-transparent hover:!border-red-100 dark:hover:!border-red-800"
        @click="$emit('delete', role.id)"
      >
        <TrashIcon class="w-5 h-5" />
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import {
  PencilSquareIcon,
  ShieldCheckIcon,
  TrashIcon,
  UsersIcon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();

defineProps({
  role: {
    type: Object,
    required: true,
  },
});

defineEmits(["edit", "delete"]);
</script>
