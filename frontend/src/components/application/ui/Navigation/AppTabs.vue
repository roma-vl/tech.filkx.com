<template>
  <div class="border-b border-gray-200/50 dark:border-gray-700/50 w-full">
    <nav
      class="-mb-px flex gap-6"
      :aria-label="label"
    >
      <router-link
        v-for="tab in tabs"
        :key="tab.name"
        :to="tab.href"
        class="py-3 px-1 font-medium text-sm border-b-2 transition-all duration-200 flex items-center gap-2 whitespace-nowrap"
        :class="[
          isActive(tab)
            ? 'border-primary-500 text-primary-600 dark:text-primary-400'
            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300/50 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-600/50',
        ]"
      >
        <div class="relative flex items-center justify-center">
          <component
            :is="tab.icon"
            v-if="tab.icon"
            class="h-4 w-4"
            :class="[
              isActive(tab)
                ? 'text-primary-600 dark:text-primary-400'
                : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300',
              tab.isLocked ? 'opacity-40 grayscale' : '',
            ]"
          />
          <LockIcon
            v-if="tab.isLocked"
            class="absolute -top-1.5 -right-1.5 w-2.5 h-2.5 text-gray-400"
          />
        </div>

        <span>{{ tab.label }}</span>

        <span
          v-if="tab.count !== undefined"
          class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-black leading-none bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300"
        >
          {{ tab.count }}
        </span>

        <span
          v-if="tab.badge"
          class="inline-flex items-center justify-center px-1.5 py-0.5 rounded bg-red-500 text-white text-[9px] font-black uppercase tracking-tighter leading-none"
        >
          {{ tab.badge }}
        </span>
      </router-link>
    </nav>
  </div>
</template>

<script setup>
import { useRoute } from "vue-router";
import { LockIcon } from "lucide-vue-next";

const props = defineProps({
  tabs: {
    type: Array,
    required: true,
  },
  label: {
    type: String,
    default: "Tabs",
  },
});

const route = useRoute();

const isActive = (tab) => {
  return route.path.startsWith(tab.href);
};
</script>
