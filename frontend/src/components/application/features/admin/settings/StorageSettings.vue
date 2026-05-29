<template>
  <section
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
  >
    <div
      class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30 flex items-center gap-2"
    >
      <CircleStackIcon class="w-5 h-5 text-amber-600" />
      <h2 class="font-bold text-lg">
        {{ $t("admin.settings.storage.title") }}
      </h2>
    </div>
    <div class="p-6 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <AppInput
            v-model="modelValue.cleanupDays"
            type="number"
            :label="$t('admin.settings.storage.cleanup_days')"
            :description="$t('admin.settings.storage.cleanup_desc')"
          />
          <p class="text-[10px] text-gray-400 mt-1">
            {{ $t("admin.settings.storage.cleanup_desc") }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { CircleStackIcon } from "@heroicons/vue/24/outline";
import AppInput from "@/components/application/ui/Form/AppInput.vue";

defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
});
</script>
