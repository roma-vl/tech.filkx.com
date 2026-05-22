<script setup>
const brands = [
  'APPLE',
  'SAMSUNG',
  'SONY',
  'MICROSOFT',
  'RAZER',
  'LOGITECH',
  'BOSE'
];
</script>

<template>
  <!-- Shop by Brand (Enhanced Logos) -->
  <section class="border-y border-surface-variant bg-white py-16">
    <div class="max-w-container-max mx-auto px-margin-desktop">
      <h2 class="font-label-md text-center mb-12 text-on-surface-variant uppercase tracking-[0.3em] font-black">
        Authorized Premium Partners
      </h2>
      <div class="flex flex-wrap items-center justify-center gap-16 md:gap-24 opacity-30 grayscale hover:grayscale-0 transition-all duration-700">
        <span 
          v-for="brand in brands" 
          :key="brand"
          class="font-display-lg text-2xl font-black cursor-default hover:text-primary transition-colors duration-300"
        >
          {{ brand }}
        </span>
      </div>
    </div>
  </section>
</template>
