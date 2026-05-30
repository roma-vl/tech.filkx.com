<script setup>
import { store } from "@/store.js";

const moveToCart = (product) => {
  store.addToCart(product);
  // Optional: remove from wishlist once moved
  store.toggleWishlist(product);
};
</script>

<template>
  <div
    v-if="store.activeDrawer === 'wishlist'"
    class="fixed inset-0 z-[90] flex justify-end"
  >
    <!-- Backdrop Overlay -->
    <div
      class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
      @click="store.closeDrawer()"
    />

    <!-- Drawer Panel -->
    <div
      class="relative w-full max-w-md bg-white h-full flex flex-col shadow-2xl border-l border-outline-variant/30 animate-in slide-in-from-right duration-300 z-10"
    >
      <!-- Header -->
      <div
        class="p-6 border-b border-surface-variant flex items-center justify-between"
      >
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[28px]">favorite</span>
          <h2 class="font-headline-md text-headline-md text-on-surface">
            Your Wishlist
          </h2>
        </div>
        <button
          class="w-10 h-10 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors"
          @click="store.closeDrawer()"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Content (Scrollable list or Empty State) -->
      <div class="flex-grow overflow-y-auto p-6 flex flex-col gap-4">
        <!-- Empty State -->
        <div
          v-if="store.wishlist.length === 0"
          class="flex-grow flex flex-col items-center justify-center text-center gap-4 py-12"
        >
          <div
            class="w-20 h-20 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant border border-outline-variant/30"
          >
            <span class="material-symbols-outlined text-[48px]">favorite_border</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface">
            Wishlist is empty
          </h3>
          <p class="text-on-surface-variant text-xs max-w-[240px]">
            Keep track of premium products you love. Tap the heart icons to save
            items here.
          </p>
          <button
            class="bg-primary text-white px-8 py-3 rounded-lg font-bold hover:scale-105 active:scale-95 transition-all shadow-md shadow-primary/10 mt-2"
            @click="store.closeDrawer()"
          >
            Explore Catalog
          </button>
        </div>

        <!-- Wishlist Items -->
        <div
          v-for="item in store.wishlist"
          v-else
          :key="item.id"
          class="flex gap-4 p-4 bg-surface-container-lowest border border-outline-variant/20 rounded-xl relative group hover:shadow-md transition-shadow"
        >
          <!-- Thumbnail -->
          <div
            class="w-20 h-20 bg-surface-container-low rounded-lg p-2 flex items-center justify-center shrink-0"
          >
            <img
              class="w-full h-full object-contain"
              :src="item.image"
              :alt="item.name"
            >
          </div>

          <!-- Details -->
          <div class="flex-grow flex flex-col justify-between pr-6">
            <div>
              <span
                class="text-[10px] font-bold text-primary uppercase tracking-wider"
              >{{ item.category }}</span>
              <h4
                class="font-title-md text-sm text-on-surface line-clamp-2 leading-tight"
              >
                {{ item.name }}
              </h4>
            </div>

            <div class="flex items-center justify-between mt-2">
              <span class="font-bold text-on-surface text-sm">${{ item.price.toFixed(2) }}</span>
              <button
                class="bg-primary text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:bg-primary-container transition-all flex items-center gap-1.5 shadow-md shadow-primary/5 active:scale-95"
                @click="moveToCart(item)"
              >
                Add to Cart
                <span class="material-symbols-outlined text-[14px]">shopping_cart</span>
              </button>
            </div>
          </div>

          <!-- Delete button -->
          <button
            class="absolute top-2 right-2 text-on-surface-variant hover:text-error transition-colors"
            @click="store.toggleWishlist(item)"
          >
            <span class="material-symbols-outlined text-[18px]">close</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
