<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    >
      <div
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        @click="$emit('cancel')"
      />
      <div
        class="relative w-full max-w-sm bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-zinc-200 dark:border-zinc-800 p-6"
      >
        <h3 class="text-zinc-900 dark:text-white font-bold text-lg mb-2">
          {{ title }}
        </h3>
        <p class="text-zinc-500 dark:text-zinc-400 text-sm mb-6">
          {{ message }}
        </p>
        <div class="flex gap-3">
          <UiButton variant="secondary" class="flex-1" @click="$emit('cancel')">
            {{ cancelLabel }}
          </UiButton>
          <UiButton variant="danger" class="flex-1" @click="$emit('confirm')">
            {{ confirmLabel }}
          </UiButton>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import UiButton from "./UiButton.vue";

defineProps<{
  open: boolean;
  title: string;
  message: string;
  confirmLabel: string;
  cancelLabel: string;
}>();

defineEmits<{
  (e: "confirm"): void;
  (e: "cancel"): void;
}>();
</script>
