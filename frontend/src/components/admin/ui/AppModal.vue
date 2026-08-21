<script setup>
import { computed } from "vue";

const props = defineProps({
  modelValue: Boolean,
  title: String,

  showClose: {
    type: Boolean,
    default: true,
  },

  closeOnBackdrop: {
    type: Boolean,
    default: true,
  },

  /**
   * sm      – confirm / alert
   * md      – small forms
   * lg      – default
   * xl      – complex forms
   * 2xl     – settings / wizards
   * 3xl     – dashboards / previews
   * 4xl     – tables / heavy UI
   * full    – fullscreen modal (desktop constrained)
   * screen  – true fullscreen (mobile-first)
   */
  maxWidth: {
    type: String,
    default: "lg",
    validator: (v) =>
      ["sm", "md", "lg", "xl", "2xl", "3xl", "4xl", "full", "screen"].includes(
        v,
      ),
  },
});

defineEmits(["update:modelValue"]);

/**
 * Width presets
 */
const maxWidthClass = computed(() => {
  const map = {
    sm: "sm:max-w-sm",
    md: "sm:max-w-md",
    lg: "sm:max-w-lg",
    xl: "sm:max-w-xl",
    "2xl": "sm:max-w-2xl",
    "3xl": "sm:max-w-5xl xl:max-w-6xl",
    "4xl": "sm:max-w-7xl",
    full: "w-full sm:max-w-none sm:w-[95vw] sm:h-auto",
    screen: "w-screen h-screen max-w-none",
  };

  return map[props.maxWidth];
});

/**
 * Vertical alignment
 */
const alignmentClass = computed(() => {
  return props.maxWidth === "screen"
    ? "items-start"
    : "items-end sm:items-center";
});

/**
 * Container behavior
 */
const panelClass = computed(() => {
  return [
    "relative transform bg-white dark:bg-gray-800",
    "text-left shadow-xl transition-all",
    "rounded-lg sm:rounded-xl",
    props.maxWidth === "screen" ? "h-full" : "sm:my-8",
  ];
});
</script>

<template>
  <div class="relative z-50">
    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
        @click="closeOnBackdrop && $emit('update:modelValue', false)"
      />
    </transition>

    <div
      v-if="modelValue"
      class="fixed inset-0 overflow-y-auto"
    >
      <div
        class="flex min-h-full justify-center p-4 text-center sm:p-6"
        :class="alignmentClass"
      >
        <transition
          enter-active-class="transition duration-500 cubic-bezier(0.34, 1.56, 0.64, 1)"
          enter-from-class="translate-y-8 opacity-0 scale-90 blur-xl"
          enter-to-class="translate-y-0 opacity-100 scale-100 blur-0"
          leave-active-class="transition duration-300 ease-in"
          leave-from-class="translate-y-0 opacity-100 scale-100 blur-0"
          leave-to-class="translate-y-8 opacity-0 scale-90 blur-xl"
          appear
        >
          <div
            v-if="modelValue"
            :class="[panelClass, maxWidthClass]"
            class="w-full"
            role="dialog"
            aria-modal="true"
          >
            <div
              v-if="$slots.header || title"
              class="flex items-center justify-between px-4 py-4 sm:px-6 border-b border-gray-100 dark:border-gray-700 rounded-t-lg sm:rounded-t-xl"
            >
              <h3
                class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100"
              >
                <slot name="header">
                  {{ title }}
                </slot>
              </h3>

              <button
                v-if="showClose"
                class="modal-close-button rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none"
                @click="$emit('update:modelValue', false)"
              >
                <span class="sr-only">Close</span>
                <svg
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
            <div
              class="px-4 py-5 sm:px-6"
              :class="
                props.maxWidth === 'screen'
                  ? 'overflow-y-auto h-[calc(100vh-4rem)]'
                  : ''
              "
            >
              <slot />
            </div>

            <div
              v-if="$slots.footer"
              class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end px-4 py-4 sm:px-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 rounded-b-lg sm:rounded-b-xl"
            >
              <slot name="footer" />
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
