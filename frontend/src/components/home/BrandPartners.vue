<script setup>
const brands = [
  "APPLE",
  "SAMSUNG",
  "SONY",
  "MICROSOFT",
  "RAZER",
  "LOGITECH",
  "BOSE",
];
</script>

<template>
  <!-- Shop by Brand Section -->
  <section
    class="border-y border-zinc-100 dark:border-zinc-900 bg-white dark:bg-zinc-950 py-10 font-sans"
  >
    <div class="max-w-container-max mx-auto px-4 md:px-8">
      <h2
        class="text-center mb-8 text-[11px] text-zinc-400 dark:text-zinc-500 font-extrabold uppercase tracking-[0.25em]"
      >
        Офіційні преміум-партнери бренду
      </h2>
      <div
        class="flex flex-wrap items-center justify-center gap-10 md:gap-16 opacity-45 grayscale hover:grayscale-0 transition-all duration-700"
      >
        <span
          v-for="brand in brands"
          :key="brand"
          class="font-extrabold text-lg md:text-xl tracking-widest text-zinc-600 dark:text-zinc-400 cursor-default hover:text-[#00a046] dark:hover:text-[#00b050] transition-colors duration-300"
        >
          {{ brand }}
        </span>
      </div>
    </div>
  </section>
</template>

<style scoped></style>
