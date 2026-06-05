<script setup lang="ts">
withDefaults(
  defineProps<{
    size?: "md" | "lg";
  }>(),
  {
    size: "md",
  },
);
</script>

<template>
  <div
    class="dark min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex"
  >
    <!-- Left decorative panel (only for 'lg') -->
    <div
      v-if="size === 'lg'"
      class="hidden lg:flex lg:w-1/2 relative overflow-hidden items-center justify-center p-12"
    >
      <!-- Decorative background blobs -->
      <div
        class="absolute inset-0 bg-gradient-to-br from-primary-600/30 via-violet-600/20 to-indigo-700/30"
      />
      <div
        class="absolute top-1/4 left-1/4 w-64 h-64 bg-primary-500/20 rounded-full blur-3xl"
      />
      <div
        class="absolute bottom-1/4 right-1/4 w-48 h-48 bg-violet-500/20 rounded-full blur-3xl"
      />

      <div class="relative z-10 w-full max-w-sm">
        <!-- Brand -->
        <a href="/" class="inline-flex items-center gap-3 mb-12 group">
          <div
            class="w-10 h-10 rounded-xl bg-primary-500 flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform"
          >
            <svg
              class="w-6 h-6 text-white"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z"
              />
            </svg>
          </div>
          <span class="text-2xl font-bold text-white tracking-tight"
            >Tech Filkx</span
          >
        </a>

        <!-- Slot for left panel content (lottie, illustration) -->
        <slot name="left" />

        <div class="mt-12 space-y-4">
          <div class="flex items-center gap-3 text-slate-300">
            <div
              class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center shrink-0"
            >
              <svg
                class="w-4 h-4 text-primary-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
            <span class="text-sm"
              >Premium electronics at competitive prices</span
            >
          </div>
          <div class="flex items-center gap-3 text-slate-300">
            <div
              class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center shrink-0"
            >
              <svg
                class="w-4 h-4 text-primary-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
            <span class="text-sm">Fast worldwide shipping</span>
          </div>
          <div class="flex items-center gap-3 text-slate-300">
            <div
              class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center shrink-0"
            >
              <svg
                class="w-4 h-4 text-primary-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
            <span class="text-sm">24/7 customer support</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right panel — form area -->
    <div class="flex-1 flex flex-col items-center justify-center p-6 sm:p-10">
      <!-- Brand for small screens -->
      <a href="/" class="flex items-center gap-2 mb-8 lg:hidden group">
        <div
          class="w-9 h-9 rounded-xl bg-primary-500 flex items-center justify-center"
        >
          <svg
            class="w-5 h-5 text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M13 10V3L4 14h7v7l9-11h-7z"
            />
          </svg>
        </div>
        <span class="text-xl font-bold text-white">Tech Filkx</span>
      </a>

      <div
        class="w-full max-w-md bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl"
      >
        <slot />
      </div>
    </div>
  </div>
</template>
