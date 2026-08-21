<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200"
    :class="[
      hoverable ? 'hover:shadow-md hover:-translate-y-0.5' : '',
      customClass,
    ]"
  >
    <div
      v-if="$slots.header || title"
      class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
    >
      <slot name="header">
        <h3 class="font-semibold text-gray-900 dark:text-gray-100">
          {{ title }}
        </h3>
      </slot>
    </div>

    <div
      class="p-4"
      :class="bodyClass"
    >
      <slot />
    </div>

    <div
      v-if="$slots.footer"
      class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
    >
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  hoverable: {
    type: Boolean,
    default: false,
  },
  customClass: String,
  bodyClass: String,
});
</script>
