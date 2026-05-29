<template>
  <div class="pt-6 border-t border-gray-100 dark:border-gray-800">
    <label
      class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-4 uppercase tracking-widest opacity-50"
    >
      {{ $t("streams.addModal.scheduleType.label") }}
    </label>
    <div
      class="flex gap-4 p-1 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700"
    >
      <button
        type="button"
        class="flex-1 px-4 py-2.5 rounded-lg text-sm font-black transition-all"
        :class="
          formData.scheduleType === 'now'
            ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-white shadow-sm'
            : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200'
        "
        @click="formData.scheduleType = 'now'"
      >
        {{ $t("streams.addModal.scheduleType.now") }}
      </button>
      <button
        type="button"
        class="flex-1 px-4 py-2.5 rounded-lg text-sm font-black transition-all"
        :class="
          formData.scheduleType === 'scheduled'
            ? 'bg-white dark:bg-gray-700 text-primary-600 dark:text-white shadow-sm'
            : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200'
        "
        @click="formData.scheduleType = 'scheduled'"
      >
        {{ $t("streams.addModal.scheduleType.scheduled") }}
      </button>
    </div>

    <div
      v-if="formData.scheduleType === 'scheduled'"
      class="mt-4 pt-4 animate-in fade-in slide-in-from-top-2 duration-300"
    >
      <div class="grid grid-cols-2 gap-4">
        <AppInput
          v-model="formData.scheduleDate"
          type="date"
          :min="todayDate"
          :label="$t('streams.addModal.scheduleDate')"
        />
        <AppInput
          v-model="formData.scheduleTime"
          type="time"
          :label="$t('streams.addModal.scheduleTime')"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import AppInput from "@/components/application/ui/Form/AppInput.vue";

defineProps({
  formData: {
    type: Object,
    required: true,
  },
  todayDate: {
    type: String,
    required: true,
  },
});
</script>
