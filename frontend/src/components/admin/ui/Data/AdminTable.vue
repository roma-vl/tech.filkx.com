<template>
  <div class="overflow-hidden">
    <div
      v-if="loading"
      class="text-center py-12"
    >
      <div class="flex flex-col items-center">
        <div
          class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-4"
        />
        <p class="text-gray-500 dark:text-gray-400">
          {{ loadingText || $t("ui.loading") }}
        </p>
      </div>
    </div>

    <div
      v-else-if="items.length === 0"
      class="text-center py-12 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-700"
    >
      <p class="text-gray-500 dark:text-gray-400">
        {{ emptyText }}
      </p>
      <slot name="empty-action" />
    </div>

    <div
      v-else
      class="overflow-x-auto"
    >
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead>
          <tr class="bg-gray-50/50 dark:bg-gray-900/50">
            <th
              v-for="header in headers"
              :key="header.key"
              :class="[
                'px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider',
                header.class,
              ]"
            >
              {{ header.label }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="(item, index) in items"
            :key="item.id || index"
            class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group"
          >
            <slot
              name="row"
              :item="item"
              :index="index"
            />
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  headers: {
    type: Array,
    required: true,
  },
  items: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  loadingText: {
    type: String,
    default: "",
  },
  emptyText: {
    type: String,
    required: true,
  },
});
</script>
