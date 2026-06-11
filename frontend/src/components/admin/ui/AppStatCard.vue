<template>
  <div
    class="bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all group"
  >
    <div class="flex items-center justify-between mb-4">
      <div
        :class="[
          'w-12 h-12 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300',
          bgClass,
        ]"
      >
        <component
          :is="icon"
          class="w-6 h-6 text-white"
        />
      </div>
      <span
        v-if="trend !== undefined"
        class="text-xs font-semibold px-2 py-1 rounded-full"
        :class="
          trend >= 0
            ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400'
            : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400'
        "
      >
        {{ trend >= 0 ? "+" : "" }}{{ trend }}%
      </span>
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
      {{ label }}
    </p>
    <p class="text-2xl font-bold mt-1 text-gray-900 dark:text-white">
      {{ value }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  icon: {
    type: [Object, Function, String],
    required: true,
  },
  trend: {
    type: Number,
    default: undefined,
  },
  bgClass: {
    type: String,
    default: "bg-primary-600",
  },
});
</script>
