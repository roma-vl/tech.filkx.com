<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div
      v-for="stat in stats"
      :key="stat.label"
      class="group bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700/50 shadow-sm hover:shadow-xl hover:shadow-primary-500/5 transition-all duration-500 hover:-translate-y-1 overflow-hidden relative"
    >
      <!-- Background Glow -->
      <div
        class="absolute -top-4 -right-4 w-24 h-24 rounded-full blur-3xl opacity-0 group-hover:opacity-20 transition-opacity duration-700"
        :class="stat.glowClass"
      />

      <div class="flex items-center gap-5">
        <div
          class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-500 shadow-sm"
          :class="stat.colorClass"
        >
          <component
            :is="stat.icon"
            class="w-7 h-7"
          />
        </div>
        <div class="flex flex-col">
          <p
            class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-1 leading-none"
          >
            {{ stat.label }}
          </p>
          <div class="flex items-baseline gap-2">
            <span
              class="text-2xl font-black text-gray-900 dark:text-white tracking-tight leading-none"
            >{{ stat.value }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stats: {
    type: Array,
    required: true,
  },
});
</script>
