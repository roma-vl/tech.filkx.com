<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-200"
  >
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center"
        >
          <VideoPlusIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
        </div>
        <div>
          <h3 class="text-lg font-semibold">
            {{ $t("settings.platforms") }}
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $t("settings.platforms_description") }}
          </p>
        </div>
      </div>
    </div>

    <div class="p-6 space-y-4">
      <PlatformIntegration
        :platform="platforms.youtube"
        @connect="$emit('connect', 'youtube')"
        @disconnect="$emit('disconnect', 'youtube')"
      />

      <PlatformIntegration
        :platform="platforms.twitch"
        @connect="$emit('connect', 'twitch')"
        @disconnect="$emit('disconnect', 'twitch')"
      />

      <PlatformIntegration
        :platform="platforms.facebook"
        @connect="$emit('connect', 'facebook')"
        @disconnect="$emit('disconnect', 'facebook')"
      />
    </div>
  </div>
</template>

<script setup>
import PlatformIntegration from "./PlatformIntegration.vue";
import VideoPlusIcon from "@/components/Icon/VideoPlusIcon.vue";

defineProps({
  platforms: {
    type: Object,
    required: true,
  },
});

defineEmits(["connect", "disconnect"]);
</script>
