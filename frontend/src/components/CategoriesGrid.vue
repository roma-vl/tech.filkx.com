<script setup>
const categories = [
  { name: 'Smartphones', icon: 'smartphone', link: '#' },
  { name: 'Laptops', icon: 'laptop_mac', link: '#' },
  { name: 'Audio', icon: 'headphones', link: '#' },
  { name: 'Watches', icon: 'watch', link: '#' },
  { name: 'Gaming', icon: 'videogame_asset', link: '#' },
  { name: 'Cameras', icon: 'camera', link: '#' },
  { name: 'Tablets', icon: 'tablet', link: '#' },
  { name: 'Components', icon: 'memory', link: '#' }
];
</script>

<template>
  <!-- Popular Categories (Clean Icons & Hover Lifts) -->
  <section class="max-w-container-max mx-auto px-margin-desktop py-stack-lg">
    <div class="flex items-center justify-between mb-10">
      <h2 class="font-headline-lg text-headline-lg tracking-tight">Top Categories</h2>
      <a class="text-primary font-bold text-sm hover:underline flex items-center gap-1" href="#">
        All Categories <span class="material-symbols-outlined text-[18px]">arrow_outward</span>
      </a>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-gutter">
      <!-- Category Item -->
      <a 
        v-for="cat in categories" 
        :key="cat.name"
        :href="cat.link"
        class="group flex flex-col items-center gap-4 transition-all"
      >
        <div class="w-24 h-24 rounded-full bg-surface-container-low border border-outline-variant/30 flex items-center justify-center group-hover:-translate-y-2 group-hover:bg-white group-hover:shadow-xl group-hover:border-primary/20 transition-all duration-300">
          <span class="material-symbols-outlined text-[40px] text-on-surface group-hover:text-primary transition-colors">
            {{ cat.icon }}
          </span>
        </div>
        <span class="font-title-md text-sm text-center">{{ cat.name }}</span>
      </a>
    </div>
  </section>
</template>
