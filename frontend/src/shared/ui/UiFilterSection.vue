<template>
  <div class="border-b border-zinc-100 dark:border-zinc-800 last:border-0">
    <!-- Header -->
    <button
      type="button"
      class="w-full flex items-center justify-between px-4 py-3.5 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 transition-colors"
      @click="isOpen = !isOpen"
    >
      <div class="flex items-center gap-2">
        <span class="text-[10px] font-extrabold uppercase tracking-widest text-zinc-400 dark:text-zinc-500">
          {{ title }}
        </span>
        <span
          v-if="activeCount"
          class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-[#00a046] text-white text-[11px] font-black"
        >{{ activeCount }}</span>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="activeCount && showReset"
          type="button"
          class="text-[10px] text-zinc-400 hover:text-rose-500 font-bold uppercase tracking-wider transition-colors flex items-center gap-0.5"
          @click.stop="emit('reset')"
        >
          <span class="material-symbols-outlined text-[11px]">close</span>
          Скинути
        </button>
        <span
          class="material-symbols-outlined text-[18px] text-zinc-400 transition-transform duration-200"
          :class="isOpen ? 'rotate-180' : ''"
        >expand_more</span>
      </div>
    </button>

    <!-- Content -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out overflow-hidden"
      leave-active-class="transition-all duration-150 ease-in overflow-hidden"
      enter-from-class="max-h-0 opacity-0"
      enter-to-class="max-h-[600px] opacity-100"
      leave-from-class="max-h-[600px] opacity-100"
      leave-to-class="max-h-0 opacity-0"
    >
      <div v-show="isOpen" class="px-4 pb-4">
        <slot />
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";

const props = withDefaults(defineProps<{
  title: string;
  defaultOpen?: boolean;
  activeCount?: number;
  showReset?: boolean;
}>(), {
  defaultOpen: true,
  activeCount: 0,
  showReset: true,
});

const emit = defineEmits<{
  (e: "reset"): void;
}>();

const isOpen = ref(props.defaultOpen);
</script>
