<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { store } from '../store.js';

const searchInput = ref(null);
const searchQuery = ref('');

const handleKeydown = (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault();
    searchInput.value?.focus();
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
  <!-- Main Sticky Header -->
  <header class="sticky top-0 z-50 w-full bg-surface-container-lowest/95 backdrop-blur-md shadow-sm border-b border-surface-variant">
    <div class="max-w-container-max mx-auto h-20 px-margin-desktop flex items-center justify-between gap-gutter">
      <!-- Brand -->
      <a class="font-display-lg text-display-lg font-bold text-primary flex-shrink-0 tracking-tight" href="/">
        ElectroLux
      </a>
      
      <!-- Search Bar (Refined & Large) -->
      <div class="flex-grow max-w-2xl">
        <div class="relative group">
          <input 
            ref="searchInput"
            v-model="searchQuery"
            class="w-full h-12 pl-12 pr-4 bg-surface-container-low border-none ring-1 ring-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all font-body-md shadow-inner" 
            placeholder="Search premium electronics..." 
            type="text"
          />
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant group-focus-within:text-primary transition-colors">search</span>
          <div class="absolute right-3 top-1/2 -translate-y-1/2 hidden md:flex items-center gap-1.5 px-2 py-1 bg-surface-container-highest rounded text-[10px] font-bold text-on-surface-variant border border-outline-variant/30">
            <span>⌘</span><span>K</span>
          </div>
        </div>
      </div>
      
      <!-- Actions -->
      <div class="flex items-center gap-8 text-on-surface">
        <button class="flex flex-col items-center gap-1 hover:text-primary transition-colors">
          <span class="material-symbols-outlined">person</span>
          <span class="text-[10px] font-bold uppercase tracking-wider">Account</span>
        </button>
        
        <button 
          @click="store.incrementCompare()"
          class="flex flex-col items-center gap-1 hover:text-primary transition-colors hidden xl:flex relative"
        >
          <span class="material-symbols-outlined">compare_arrows</span>
          <span class="text-[10px] font-bold uppercase tracking-wider">Compare</span>
          <span 
            v-if="store.compareCount > 0"
            class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold"
          >
            {{ store.compareCount }}
          </span>
        </button>
        
        <button class="flex flex-col items-center gap-1 hover:text-primary transition-colors relative">
          <span class="material-symbols-outlined">favorite</span>
          <span class="text-[10px] font-bold uppercase tracking-wider">Wishlist</span>
          <span 
            v-if="store.wishlistCount > 0"
            class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold"
          >
            {{ store.wishlistCount }}
          </span>
        </button>
        
        <button class="flex flex-col items-center gap-1 hover:text-primary transition-colors relative">
          <span class="material-symbols-outlined">shopping_cart</span>
          <span class="text-[10px] font-bold uppercase tracking-wider">Cart</span>
          <span 
            v-if="store.cartCount > 0"
            class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold"
          >
            {{ store.cartCount }}
          </span>
        </button>
      </div>
    </div>
  </header>
</template>
