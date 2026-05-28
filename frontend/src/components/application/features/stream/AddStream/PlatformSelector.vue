<template>
  <div class="space-y-4">
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <div
        v-for="platform in platforms"
        :key="platform.id"
        class="relative group"
        :class="[
          platform.isDevelopment ? 'opacity-70 grayscale' : 'cursor-pointer',
        ]"
        @click="onSelect(platform)"
      >
        <div
          class="flex flex-col items-center justify-center p-6 border-2 rounded-xl transition-all duration-200 h-full"
          :class="[
            modelValue === platform.id
              ? 'border-primary bg-primary/5 dark:bg-primary/10'
              : 'border-gray-100 dark:border-gray-800 hover:border-primary/50 bg-white dark:bg-gray-900',
          ]"
        >
          <div class="w-16 h-16 mb-4 flex items-center justify-center">
            <img
              :src="getPlatformIcon(platform.id)"
              :alt="platform.name"
              class="w-full h-full object-contain"
            >
          </div>
          <span class="font-semibold text-gray-900 dark:text-white">{{
            platform.name
          }}</span>

          <div
            v-if="platform.isDevelopment"
            class="absolute -top-2 -right-2 bg-yellow-500 text-white text-[10px] px-2 py-0.5 rounded-full font-bold shadow-sm"
          >
            {{ $t("streams.addModal.platformUnderDevelopment") }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  platforms: {
    type: Array,
    required: true,
  },
  modelValue: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue", "select"]);

const getPlatformIcon = (platformId) => {
  if (!platformId) return "";
  const fileName = `${platformId}.png`;
  return new URL(`/src/assets/images/platforms/${fileName}`, import.meta.url)
    .href;
};

const onSelect = (platform) => {
  if (platform.isDevelopment) return;
  emit("update:modelValue", platform.id);
  emit("select", platform);
};
</script>
