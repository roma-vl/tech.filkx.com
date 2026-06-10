<template>
  <div
    class="group bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-xl hover:shadow-primary-500/10 transition-all duration-500 overflow-hidden flex flex-col h-full"
  >
    <div class="p-6 flex-1 flex flex-col">
      <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-4">
          <div
            class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3"
          >
            <ShieldCheckIcon class="w-6 h-6" />
          </div>
          <div>
            <h3
              class="text-lg font-black text-gray-900 dark:text-white tracking-tight group-hover:text-primary-500 transition-colors uppercase"
            >
              {{ role.name }}
            </h3>
            <span
              v-if="role.isSystem"
              class="inline-flex px-1.5 py-0.5 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-[8px] font-black uppercase tracking-widest border border-amber-200 dark:border-amber-800/50 mt-1"
            >
              {{ t("admin.roles.table.system_tag") }}
            </span>
          </div>
        </div>

        <div class="relative">
          <Dropdown
            align="right"
            width="48"
          >
            <template #trigger>
              <button
                class="p-2 text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <EllipsisVerticalIcon class="w-5 h-5" />
              </button>
            </template>
            <template #content>
              <div class="p-2 space-y-1">
                <AppButton
                  variant="ghost"
                  class="w-full !justify-start !px-3 !py-2 !text-xs !font-bold !text-gray-600 dark:!text-gray-300 !rounded-lg"
                  @click="$emit('edit', role)"
                >
                  <template #prefix>
                    <PencilSquareIcon class="w-4 h-4 mr-2" />
                  </template>
                  {{ t("common.actions.edit") }}
                </AppButton>
                <AppButton
                  v-if="!role.isSystem"
                  variant="ghost"
                  class="w-full !justify-start !px-3 !py-2 !text-xs !font-bold !text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20 !rounded-lg"
                  @click="$emit('delete', role.id)"
                >
                  <template #prefix>
                    <TrashIcon class="w-4 h-4 mr-2" />
                  </template>
                  {{ t("common.actions.delete") }}
                </AppButton>
              </div>
            </template>
          </Dropdown>
        </div>
      </div>

      <div class="mb-6 flex-1">
        <p
          class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 leading-relaxed"
        >
          {{ role.description || t("admin.roles.table.no_description") }}
        </p>
      </div>

      <div
        class="flex items-center justify-between pt-6 border-t border-gray-50 dark:border-gray-700/50"
      >
        <div class="flex items-center gap-4">
          <div class="flex flex-col">
            <span
              class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1"
            >
              {{ t("admin.roles.table.users") }}
            </span>
            <div
              class="flex items-center gap-1.5 font-bold text-gray-700 dark:text-gray-200 text-sm"
            >
              <UsersIcon class="w-3.5 h-3.5 text-gray-400" />
              {{ role.usersCount }}
            </div>
          </div>

          <div class="w-px h-6 bg-gray-100 dark:bg-gray-700" />

          <div class="flex flex-col">
            <span
              class="text-[9px] font-black text-gray-400 uppercase tracking-widest leading-none mb-1"
            >
              {{ t("admin.roles.table.scope") }}
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
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  EllipsisVerticalIcon,
  PencilSquareIcon,
  ShieldCheckIcon,
  TrashIcon,
  UsersIcon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import Dropdown from "@/shared/ui/Dropdown.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

const { t } = useI18n();

defineProps({
  role: {
    type: Object,
    required: true,
  },
});

defineEmits(["edit", "delete"]);
</script>
