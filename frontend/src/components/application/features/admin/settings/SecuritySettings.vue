<template>
  <section
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
  >
    <div
      class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30 flex items-center gap-2"
    >
      <ShieldCheckIcon class="w-5 h-5 text-emerald-600" />
      <h2 class="font-bold text-lg">
        {{ $t("admin.settings.security.title") }}
      </h2>
    </div>
    <div class="p-6 space-y-4">
      <div
        class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-2xl transition-colors"
      >
        <AppToggle
          v-model="modelValue.enforce2fa"
          :label="$t('admin.settings.security.two_fa')"
          :description="$t('admin.settings.security.two_fa_desc')"
          active-color="emerald"
        >
          <template #default>
            <div class="flex items-center gap-3">
              <KeyIcon class="w-5 h-5 text-gray-400" />
              <span>{{ $t("admin.settings.security.two_fa") }}</span>
            </div>
          </template>
        </AppToggle>
      </div>
      <div
        class="p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 rounded-2xl transition-colors"
      >
        <AppToggle
          v-model="modelValue.rateLimiting"
          :label="$t('admin.settings.security.rate_limiting')"
          :description="$t('admin.settings.security.rate_limiting_desc')"
          active-color="emerald"
        >
          <template #default>
            <div class="flex items-center gap-3">
              <NoSymbolIcon class="w-5 h-5 text-gray-400" />
              <span>{{ $t("admin.settings.security.rate_limiting") }}</span>
            </div>
          </template>
        </AppToggle>
      </div>
    </div>
  </section>
</template>

<script setup>
import {
  ShieldCheckIcon,
  KeyIcon,
  NoSymbolIcon,
} from "@heroicons/vue/24/outline";
import AppToggle from "@/components/application/ui/Form/AppToggle.vue";

defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
});
</script>
