<template>
  <div
    class="setting-section p-4 rounded-lg border border-gray-200 dark:border-gray-700"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center">
        <div
          class="w-10 h-10 rounded-lg flex items-center justify-center mr-3"
          :class="`bg-${platform.color}-100 dark:bg-${platform.color}-900/30`"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            :class="`text-${platform.color}-600 dark:text-${platform.color}-400`"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path :d="platform.icon" />
          </svg>
        </div>
        <div>
          <h4 class="font-medium">
            {{ platform.name }}
          </h4>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ platform.description }}
          </p>
        </div>
      </div>
      <AppButton
        :variant="platform.connected ? 'secondary' : 'primary'"
        size="sm"
        @click="handleClick"
      >
        {{
          platform.connected ? $t("settings.connected") : $t("settings.connect")
        }}
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import AppButton from "@/components/admin/ui/AppButton.vue";

const props = defineProps({
  platform: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["connect", "disconnect"]);

const handleClick = () => {
  if (props.platform.connected) {
    emit("disconnect", props.platform.name.toLowerCase());
  } else {
    emit("connect", props.platform.name.toLowerCase());
  }
};
</script>

<style scoped>
.setting-section {
  transition: all 0.2s ease;
}

.setting-section:hover {
  background-color: rgba(59, 130, 246, 0.05);
}

.dark .setting-section:hover {
  background-color: rgba(59, 130, 246, 0.1);
}
</style>
