<template>
  <div
    class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50/50 dark:bg-gray-900/20"
  >
    <div class="flex items-center gap-6">
      <div class="flex flex-col">
        <span
          class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1"
        >
          {{ t("admin.team.total_members") }}
        </span>
        <span
          class="text-xl font-black text-gray-900 dark:text-white leading-none"
        >
          {{ stats.total }}
        </span>
      </div>

      <div class="w-px h-8 bg-gray-200 dark:bg-gray-700"/>

      <div class="flex flex-col">
        <span
          class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1"
        >
          {{ t("admin.team.owner_access") }}
        </span>
        <span
          class="text-xl font-black text-gray-900 dark:text-white leading-none"
        >
          {{ stats.owners }}
        </span>
      </div>
    </div>

    <div class="flex items-center gap-4">
      <!-- View Mode Toggles -->
      <div
        class="flex p-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm"
      >
        <button
          class="p-2 rounded-lg transition-all"
          :class="
            viewMode === 'grid'
              ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30'
              : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
          "
          @click="$emit('update:viewMode', 'grid')"
        >
          <Squares2X2Icon class="w-5 h-5"/>
        </button>
        <button
          class="p-2 rounded-lg transition-all"
          :class="
            viewMode === 'list'
              ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30'
              : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
          "
          @click="$emit('update:viewMode', 'list')"
        >
          <ListBulletIcon class="w-5 h-5"/>
        </button>
      </div>

      <AppButton
        variant="ghost"
        class="!p-3 !text-gray-400 hover:!text-primary-500"
        :title="t('admin.analytics.refresh')"
        @click="$emit('refresh')"
      >
        <ArrowPathIcon
          class="w-5 h-5"
          :class="{ 'animate-spin': loading }"
        />
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import {ArrowPathIcon, ListBulletIcon, Squares2X2Icon,} from "@heroicons/vue/24/outline";
import {useI18n} from "vue-i18n";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();

defineProps({
  stats: {
    type: Object,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  viewMode: {
    type: String,
    required: true,
  },
});

defineEmits(["refresh", "update:viewMode"]);
</script>
