<template>
  <div
    class="relative min-h-[400px] flex items-center justify-center overflow-hidden rounded-xl bg-gray-50 dark:bg-gray-900/50"
  >
    <!-- Decorative background elements -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div
        class="absolute -top-24 -right-24 w-96 h-96 bg-primary-500/10 rounded-full blur-3xl"
      />
      <div
        class="absolute -bottom-24 -left-24 w-96 h-96 bg-primary-600/10 rounded-full blur-3xl"
      />
    </div>

    <div class="relative z-10 text-center px-6 py-12 max-w-lg">
      <div class="mb-8 flex justify-center">
        <div
          class="w-24 h-24 bg-white dark:bg-gray-800 rounded-3xl shadow-xl flex items-center justify-center transform rotate-6 hover:rotate-0 transition-transform duration-500"
        >
          <div
            class="w-16 h-16 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/20"
          >
            <LockIcon class="w-8 h-8 text-white" />
          </div>
        </div>
      </div>

      <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
        {{ title || $t("featureLock.title") }}
      </h2>

      <p class="text-lg text-gray-600 dark:text-gray-300 mb-10 leading-relaxed">
        {{ description || $t("featureLock.description") }}
      </p>

      <div class="flex items-center justify-center gap-4">
        <AppButton
          to="/account/subscription"
          variant="primary"
          size="lg"
          class="px-10 shadow-lg shadow-primary-500/30"
        >
          <ZapIcon class="w-5 h-5 mr-2" />
          {{ $t("featureLock.upgrade") }}
        </AppButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { LockIcon, ZapIcon } from "lucide-vue-next";
import AppButton from "./AppButton.vue";

defineProps({
  title: String,
  description: String,
});
</script>
