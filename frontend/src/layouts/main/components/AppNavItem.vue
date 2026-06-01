<template>
  <router-link
    :to="item.to"
    :class="[
      'flex items-center transition-all duration-200 group relative overflow-hidden',
      collapsed ? 'justify-center p-3 rounded-xl' : 'px-4 py-3 rounded-xl',
      item.isActive
        ? 'bg-blue-500/10 text-blue-700 dark:text-blue-400 font-bold'
        : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100/50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white font-medium',
    ]"
    @click="$emit('click')"
  >
    <!-- Active state indicator bar -->
    <div
      v-if="item.isActive && !collapsed"
      class="absolute left-0 top-3 bottom-3 w-1 bg-blue-600 dark:bg-blue-400 rounded-r-full"
    />

    <div class="relative flex-shrink-0 flex items-center justify-center">
      <component
        :is="item.icon"
        :class="[
          'h-6 w-6 transition-transform duration-200 group-hover:scale-110',
          item.isActive ? 'text-blue-600 dark:text-blue-400' : '',
          item.isLocked ? 'opacity-40 grayscale' : 'opacity-100',
        ]"
      />
      <div
        v-if="item.isLocked"
        class="absolute -top-1.5 -right-1.5 bg-gray-100 dark:bg-gray-700 rounded-full p-0.5 border border-white dark:border-gray-800 shadow-sm"
      >
        <LockIcon class="w-2.5 h-2.5 text-gray-500 dark:text-gray-400" />
      </div>
    </div>
    <span
      v-if="!collapsed"
      class="ml-3 truncate text-[15px]"
    >{{
      t(item.labelKey)
    }}</span>
  </router-link>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { LockIcon } from "lucide-vue-next";

interface NavItem {
  to: string;
  icon: any;
  labelKey: string;
  isActive?: boolean;
  isLocked?: boolean;
}

const { t } = useI18n();
defineEmits<{ (e: "click"): void }>();
defineProps<{
  item: NavItem;
  collapsed?: boolean;
}>();
</script>
