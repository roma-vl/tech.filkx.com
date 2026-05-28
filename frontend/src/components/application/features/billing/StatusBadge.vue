<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
      statusClasses[status] || statusClasses.default,
    ]"
  >
    <span
      class="w-1.5 h-1.5 rounded-full mr-1.5"
      :class="dotClasses[status] || dotClasses.default"
    />
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  status: {
    type: String,
    required: true,
  },
});

const statusClasses = {
  active:
    "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
  pending:
    "bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400",
  completed:
    "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400",
  failed: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  cancelled: "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400",
  trial: "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400",
  expired: "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
  default: "bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400",
};

const dotClasses = {
  active: "bg-green-600 dark:bg-green-400",
  pending: "bg-amber-600 dark:bg-amber-400",
  completed: "bg-green-600 dark:bg-green-400",
  failed: "bg-red-600 dark:bg-red-400",
  cancelled: "bg-gray-600 dark:bg-gray-400",
  trial: "bg-blue-600 dark:bg-blue-400",
  expired: "bg-red-600 dark:bg-red-400",
  default: "bg-gray-600 dark:bg-gray-400",
};

const label = computed(() => {
  return t(`subscription.statuses.${props.status}`) !==
    `subscription.statuses.${props.status}`
    ? t(`subscription.statuses.${props.status}`)
    : props.status;
});
</script>
