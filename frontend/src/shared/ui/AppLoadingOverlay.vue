<template>
  <Transition
    enter-active-class="transition duration-300 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="transition duration-200 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div
      v-if="loading"
      class="absolute inset-0 bg-white/60 dark:bg-gray-900/60 backdrop-blur-[2px] z-50 flex items-center justify-center rounded-2xl"
    >
      <div class="flex flex-col items-center gap-4">
        <div class="relative">
          <div
            class="w-12 h-12 border-4 border-gray-200 dark:border-gray-800 rounded-full"
          />
          <div
            class="absolute top-0 left-0 w-12 h-12 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"
          />
        </div>
        <p
          v-if="text"
          class="text-sm font-semibold text-gray-600 dark:text-gray-300 animate-pulse"
        >
          {{ text }}
        </p>
      </div>
    </div>
  </Transition>
</template>

<script setup>
defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  text: {
    type: String,
    default: "",
  },
});
</script>
