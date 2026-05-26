<template>
  <div class="relative">
    <button
      :class="[
        'flex items-center transition-all duration-300 group',
        collapsed ? 'justify-center p-3 rounded-xl w-full' : 'justify-between w-full px-4 py-3 rounded-xl',
        isOpen || isAnyChildActive
          ? 'bg-blue-500/10 text-blue-700 dark:text-blue-400 font-bold'
          : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100/50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white font-medium',
      ]"
      @click="toggleDropdown"
    >
      <div class="flex items-center">
        <component
          :is="item.icon"
          :class="[
            'h-6 w-6 flex-shrink-0 transition-transform duration-300 group-hover:scale-110',
            isOpen || isAnyChildActive ? 'text-blue-600 dark:text-blue-400' : ''
          ]"
        />
        <span
          v-if="!collapsed"
          class="ml-3 truncate text-[15px]"
        >{{
          t(item.labelKey)
        }}</span>
      </div>
      <svg
        v-if="!collapsed"
        :class="['h-4 w-4 transition-transform duration-300', isOpen ? 'rotate-180' : '']"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2.5"
          d="M19 9l-7 7-7-7"
        />
      </svg>
    </button>

    <div
      v-if="isOpen && !collapsed"
      class="mt-1 ml-9 space-y-1 relative"
    >
      <!-- Sub-item indicator line -->
      <div class="absolute left-[-1.25rem] top-2 bottom-2 w-0.5 bg-gray-200 dark:bg-gray-800 rounded-full" />
      
      <router-link
        v-for="child in item.items"
        :key="child.id"
        :to="child.to"
        :class="[
          'block px-4 py-2.5 rounded-xl text-sm transition-all duration-200 relative',
          isChildActive(child.to)
            ? 'text-blue-700 dark:text-blue-400 font-bold bg-blue-500/5'
            : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100/50 dark:hover:bg-white/5 font-medium',
        ]"
        @click="$emit('click')"
      >
        {{ t(child.labelKey) }}
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute } from "vue-router";

const { t } = useI18n();
const route = useRoute();
const emit = defineEmits(["click"]);

const props = defineProps({
  item: { type: Object, required: true },
  collapsed: { type: Boolean, default: false },
});

const isOpen = ref(false);

const isChildActive = (path) => {
  return route.path === path;
};

const isAnyChildActive = computed(() => {
  return props.item.items?.some((child) => route.path === child.to) || false;
});

const toggleDropdown = () => {
  if (!props.collapsed) {
    isOpen.value = !isOpen.value;
  }
};

// Auto-open if child is active
if (isAnyChildActive.value) {
  isOpen.value = true;
}
</script>
