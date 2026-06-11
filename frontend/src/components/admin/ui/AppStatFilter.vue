<template>
  <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-4 gap-3 w-full">
    <button
      v-for="filter in filters"
      :key="filter.id"
      class="flex items-center justify-between px-4 py-3 rounded-2xl border transition-all text-left group"
      :class="
        activeFilter === filter.id
          ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/10'
          : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600'
      "
      @click="$emit('update:activeFilter', filter.id)"
    >
      <div>
        <p
          class="text-[10px] font-bold text-gray-400 uppercase tracking-wider group-hover:text-gray-500 transition-colors"
        >
          {{ filter.label }}
        </p>
        <p
          v-if="filter.count !== undefined"
          class="text-sm font-bold mt-0.5"
          :class="
            activeFilter === filter.id
              ? 'text-primary-600 dark:text-primary-400'
              : 'text-gray-700 dark:text-gray-200'
          "
        >
          {{ filter.count }}
        </p>
      </div>
      <div :class="['w-2 h-2 rounded-full shadow-sm', filter.colorClass]" />
    </button>
  </div>
</template>

<script setup>
defineProps({
  filters: {
    type: Array,
    required: true,
    // Each filter: { id: string, label: string, count: number|string, colorClass: string }
  },
  activeFilter: {
    type: String,
    required: true,
  },
});

defineEmits(["update:activeFilter"]);
</script>
