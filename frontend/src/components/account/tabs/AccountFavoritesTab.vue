<script setup>
import { store } from '@/store.js';
</script>

<template>
  <div class="space-y-stack-md animate-fade">
    <div v-if="store.wishlist.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="product in store.wishlist" :key="product.id"
           class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all group flex flex-col justify-between">
        <div class="p-4 bg-white relative flex justify-center items-center aspect-square border-b border-outline-variant">
          <img :src="product.image" :alt="product.name" class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300" />
          <button @click="store.toggleWishlist(product)" class="absolute top-3 right-3 p-1.5 bg-surface-container-low hover:bg-error-container hover:text-error text-on-surface-variant rounded-full transition-colors">
            <span class="material-symbols-outlined text-[18px]">close</span>
          </button>
        </div>
        <div class="p-4 flex-1 flex flex-col justify-between gap-4">
          <div>
            <p class="text-[10px] text-on-surface-variant font-bold uppercase tracking-wider mb-1">{{ product.category }}</p>
            <h3 class="font-title-md text-on-surface text-sm line-clamp-2 leading-snug group-hover:text-primary transition-colors">{{ product.name }}</h3>
          </div>
          <div class="flex items-center justify-between gap-2 mt-auto">
            <span class="font-bold text-primary text-lg">${{ product.price.toFixed(2) }}</span>
            <button @click="store.addToCart(product)" class="bg-primary text-on-primary px-4 py-2 rounded-lg font-bold text-xs hover:bg-primary-container transition-all uppercase tracking-wider flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">shopping_cart</span> Add
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="bg-surface-container-lowest rounded-xl border border-outline-variant p-12 text-center shadow-sm">
      <div class="w-16 h-16 bg-error-container/10 border border-error-container text-error rounded-full flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">favorite</span>
      </div>
      <h3 class="font-title-lg text-on-surface text-lg">Your Wishlist is Empty</h3>
      <p class="text-xs text-on-surface-variant max-w-sm mx-auto mt-2">Click the heart icon on any product to save it here.</p>
      <a href="/catalog" class="inline-block bg-primary text-on-primary font-bold text-xs py-2.5 px-6 rounded-lg hover:bg-primary-container transition-all mt-6">Explore Products</a>
    </div>
  </div>
</template>

<style scoped>
.animate-fade { animation: fadeIn .25s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
</style>
