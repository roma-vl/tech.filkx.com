<template>
  <section
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
  >
    <div
      class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30 flex items-center gap-2"
    >
      <GlobeAltIcon class="w-5 h-5 text-indigo-600" />
      <h2 class="font-bold text-lg">
        {{ $t("admin.settings.general.title") }}
      </h2>
    </div>
    <div class="p-6 space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <AppInput
          v-model="modelValue.platformName"
          :label="$t('admin.settings.general.platform_name')"
        />
        <AppInput
          v-model="modelValue.supportEmail"
          type="email"
          :label="$t('admin.settings.general.support_email')"
        />
      </div>
      <div
        class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800"
      >
        <AppToggle
          v-model="modelValue.allowRegistration"
          :label="$t('admin.settings.general.registration')"
          :description="$t('admin.settings.general.registration_desc')"
          active-color="indigo"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
import { GlobeAltIcon } from "@heroicons/vue/24/outline";
import AppInput from "@/components/application/ui/Form/AppInput.vue";
import AppToggle from "@/components/application/ui/Form/AppToggle.vue";

defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
});
</script>
