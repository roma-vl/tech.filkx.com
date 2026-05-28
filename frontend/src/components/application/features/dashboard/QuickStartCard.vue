<template>
  <div
    class="relative overflow-hidden bg-white/40 dark:bg-gray-800/20 backdrop-blur-xl rounded-[2rem] border border-white/60 dark:border-white/10 p-6 sm:p-8 hover:shadow-lg transition-all duration-300 cursor-pointer group flex flex-col h-full"
    @click="$emit('click')"
  >
    <!-- Background glow effect on hover -->
    <div
      class="absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-10 transition-opacity duration-500 pointer-events-none"
      :class="{
        'from-blue-500 to-transparent': action.color === 'blue',
        'from-red-500 to-transparent': action.color === 'red',
        'from-purple-500 to-transparent': action.color === 'purple'
      }"
    />

    <div
      class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6 shadow-sm border"
      :class="{
        'bg-blue-50 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-500/30': action.color === 'blue',
        'bg-red-50 text-red-600 border-red-200 dark:bg-red-500/20 dark:text-red-400 dark:border-red-500/30': action.color === 'red',
        'bg-purple-50 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-500/30': action.color === 'purple'
      }"
    >
      <component
        :is="action.icon"
        class="h-7 w-7 flex-shrink-0"
      />
    </div>
    
    <h4 class="font-bold text-xl mb-2 text-gray-900 dark:text-white">
      {{ action.title }}
    </h4>
    
    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6 flex-grow leading-relaxed font-medium">
      {{ action.description }}
    </p>
    
    <div class="mt-auto">
      <AppButton
        variant="text"
        class="text-sm font-semibold p-0 hover:bg-transparent shadow-none"
        :class="{
          'text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300': action.color === 'blue',
          'text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300': action.color === 'red',
          'text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300': action.color === 'purple'
        }"
        @click.stop="$emit('click')"
      >
        {{ buttonText }}
        <template #suffix>
          <ArrowRightIcon class="ml-2 h-5 w-5 flex-shrink-0" />
        </template>
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import ArrowRightIcon from "@/components/Icon/ArrowRightIcon.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  action: {
    type: Object,
    required: true,
  },
});

defineEmits(["click"]);

const buttonText = computed(() => {
  return props.action.id === "setup-content"
    ? t("dashboard.quickStart.buttons.setup")
    : t("dashboard.quickStart.buttons.start");
});
</script>
