<script setup>
const brands = [
  "APPLE",
  "SAMSUNG",
  "SONY",
  "MICROSOFT",
  "RAZER",
  "LOGITECH",
  "BOSE",
  "ASUS",
  "LG",
  "PHILIPS",
];
</script>

<template>
  <section
    class="border-y border-zinc-100 dark:border-zinc-900 bg-white dark:bg-zinc-950 py-10 font-sans overflow-hidden"
  >
    <div class="max-w-container-max mx-auto px-4 md:px-8 mb-7">
      <h2
        class="text-center text-[11px] text-zinc-400 dark:text-zinc-500 font-extrabold uppercase tracking-[0.25em]"
      >
        Офіційні преміум-партнери бренду
      </h2>
    </div>

    <!-- Marquee track -->
    <div class="relative flex overflow-hidden select-none [mask-image:linear-gradient(to_right,transparent,black_10%,black_90%,transparent)]">
      <div
        class="flex animate-marquee gap-12 md:gap-16 shrink-0 items-center"
        aria-hidden="true"
      >
        <span
          v-for="brand in brands"
          :key="brand"
          class="font-extrabold text-base md:text-lg tracking-widest text-zinc-400 dark:text-zinc-600 hover:text-[#00a046] dark:hover:text-[#00a046] transition-colors duration-300 cursor-default whitespace-nowrap"
        >
          {{ brand }}
        </span>
      </div>
      <!-- Duplicate for seamless loop -->
      <div
        class="flex animate-marquee gap-12 md:gap-16 shrink-0 items-center"
        aria-hidden="true"
      >
        <span
          v-for="brand in brands"
          :key="`dup-${brand}`"
          class="font-extrabold text-base md:text-lg tracking-widest text-zinc-400 dark:text-zinc-600 hover:text-[#00a046] dark:hover:text-[#00a046] transition-colors duration-300 cursor-default whitespace-nowrap"
        >
          {{ brand }}
        </span>
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes marquee {
  from { transform: translateX(0); }
  to { transform: translateX(-100%); }
}

.animate-marquee {
  animation: marquee 22s linear infinite;
}

.animate-marquee:hover {
  animation-play-state: paused;
}
</style>
