<template>
  <tr
    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer"
    @click="$emit('click')"
  >
    <td class="py-4 px-6 whitespace-nowrap">
      <div class="flex items-center">
        <div class="w-10 h-10 rounded bg-gray-200 dark:bg-gray-700 mr-3" />
        <div>
          <div class="font-medium">
            {{ activity.title }}
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400">
            ID: {{ activity.id }}
          </div>
        </div>
      </div>
    </td>
    <td class="py-4 px-6">
      <span class="font-medium">{{ activity.views.toLocaleString() }}</span>
    </td>
    <td class="py-4 px-6">
      <span class="font-medium">{{ activity.likes.toLocaleString() }}</span>
    </td>
    <td
      class="py-4 px-6 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap"
    >
      {{ activity.date }}
    </td>
  </tr>
</template>

<script setup>
defineProps({
  activity: {
    type: Object,
    required: true,
  },
});

defineEmits(["click"]);
</script>
