<script setup>
import { ref } from 'vue';
import { store } from '@/store.js';

const isExpanded = ref(false);

const handleAddToCart = (product) => {
  store.addToCart(product);
  store.removeFromCompare(product.id);
};
</script>

<template>
  <div v-if="store.compare.length > 0" class="fixed bottom-0 left-0 right-0 z-50 transition-all duration-300">

    <!-- Collapsed Bottom Banner -->
    <div
      v-if="!isExpanded"
      class="bg-inverse-surface text-inverse-on-surface shadow-2xl border-t border-outline-variant/30 px-6 py-4 flex items-center justify-between max-w-4xl mx-auto rounded-t-2xl"
    >
      <div class="flex items-center gap-4">
        <span class="material-symbols-outlined text-primary-fixed-dim">compare_arrows</span>
        <div class="text-sm font-semibold">
          Compare Products <span class="text-xs text-primary-fixed-dim">({{ store.compare.length }} / 3 selected)</span>
        </div>

        <!-- Previews -->
        <div class="hidden sm:flex items-center gap-2">
          <div
            v-for="item in store.compare"
            :key="item.id"
            class="relative w-10 h-10 bg-white rounded-lg p-1 border border-white/20 group"
          >
            <img class="w-full h-full object-contain" :src="item.image" :alt="item.name" />
            <button
              @click="store.removeFromCompare(item.id)"
              class="absolute -top-1.5 -right-1.5 bg-error text-white w-4 h-4 rounded-full flex items-center justify-center text-[10px] shadow"
            >
              ×
            </button>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button
          @click="store.compare = []"
          class="text-xs text-surface-variant hover:text-white transition-colors uppercase font-bold tracking-wider"
        >
          Clear
        </button>
        <button
          @click="isExpanded = true"
          class="bg-primary text-white text-xs font-bold px-6 py-2.5 rounded-lg hover:bg-primary-container transition-all flex items-center gap-1 shadow-md shadow-primary/20"
        >
          Compare Now <span class="material-symbols-outlined text-[16px]">open_in_new</span>
        </button>
      </div>
    </div>

    <!-- Expanded Compare Modal Overlay -->
    <div
      v-else
      class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50 animate-in fade-in duration-200"
    >
      <div
        class="bg-white rounded-2xl max-w-5xl w-full p-8 relative shadow-2xl border border-surface-variant max-h-[90vh] overflow-y-auto flex flex-col gap-6"
      >
        <!-- Modal Header -->
        <div class="flex justify-between items-center border-b border-surface-variant pb-4">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[28px]">compare_arrows</span>
            <h2 class="font-headline-md text-headline-md text-on-surface">Product Comparison</h2>
          </div>
          <button
            @click="isExpanded = false"
            class="w-10 h-10 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <!-- Compare Table -->
        <div class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="border-b border-surface-variant/30">
                <th class="w-1/4 py-4 px-6 text-left font-title-md text-on-surface-variant text-xs uppercase tracking-wider bg-surface-container-low rounded-tl-xl">Spec</th>
                <th
                  v-for="prod in store.compare"
                  :key="prod.id"
                  class="w-1/4 py-4 px-6 text-center font-bold text-on-surface relative bg-surface-container-low"
                >
                  <button
                    @click="store.removeFromCompare(prod.id)"
                    class="absolute top-2 right-2 text-on-surface-variant hover:text-error transition-colors"
                    title="Remove from comparison"
                  >
                    <span class="material-symbols-outlined text-[18px]">close</span>
                  </button>
                  <div class="h-24 flex items-center justify-center mb-3">
                    <img class="max-h-full object-contain p-2" :src="prod.image" :alt="prod.name" />
                  </div>
                  <h4 class="font-title-md text-sm leading-snug line-clamp-2 max-w-[200px] mx-auto">{{ prod.name }}</h4>
                </th>
                <!-- Placeholder columns if comparing less than 3 -->
                <th
                  v-for="n in (3 - store.compare.length)"
                  :key="'empty-' + n"
                  class="w-1/4 py-4 px-6 text-center text-on-surface-variant/40 bg-surface-container-low font-medium text-xs rounded-tr-xl"
                >
                  <div class="h-24 flex flex-col items-center justify-center border border-dashed border-outline-variant/50 rounded-xl m-2">
                    <span class="material-symbols-outlined text-[28px] opacity-40 mb-1">add</span>
                    <span>Add product</span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Price row -->
              <tr class="border-b border-surface-variant/30">
                <td class="py-4 px-6 font-bold text-xs text-on-surface-variant uppercase bg-surface-container-low/30">Price</td>
                <td v-for="prod in store.compare" :key="prod.id" class="py-4 px-6 text-center font-bold text-primary text-lg">
                  ${{ prod.price.toFixed(2) }}
                </td>
                <td v-for="n in (3 - store.compare.length)" :key="'price-empty-' + n" class="py-4 px-6"></td>
              </tr>

              <!-- Category row -->
              <tr class="border-b border-surface-variant/30">
                <td class="py-4 px-6 font-bold text-xs text-on-surface-variant uppercase bg-surface-container-low/30">Category</td>
                <td v-for="prod in store.compare" :key="prod.id" class="py-4 px-6 text-center text-sm font-semibold text-on-surface">
                  {{ prod.category }}
                </td>
                <td v-for="n in (3 - store.compare.length)" :key="'cat-empty-' + n" class="py-4 px-6"></td>
              </tr>

              <!-- Rating row -->
              <tr class="border-b border-surface-variant/30">
                <td class="py-4 px-6 font-bold text-xs text-on-surface-variant uppercase bg-surface-container-low/30">Rating</td>
                <td v-for="prod in store.compare" :key="prod.id" class="py-4 px-6 text-center">
                  <div class="flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[16px] text-amber-400 fill" style="font-variation-settings: 'FILL' 1;">star</span>
                    <span class="text-sm font-bold">{{ prod.rating }}</span>
                    <span class="text-xs text-on-surface-variant">({{ prod.reviews }} reviews)</span>
                  </div>
                </td>
                <td v-for="n in (3 - store.compare.length)" :key="'rating-empty-' + n" class="py-4 px-6"></td>
              </tr>

              <!-- Description row -->
              <tr class="border-b border-surface-variant/30">
                <td class="py-4 px-6 font-bold text-xs text-on-surface-variant uppercase bg-surface-container-low/30">Specs & Info</td>
                <td v-for="prod in store.compare" :key="prod.id" class="py-4 px-6 text-center text-xs text-on-surface-variant leading-relaxed max-w-[240px]">
                  {{ prod.description }}
                </td>
                <td v-for="n in (3 - store.compare.length)" :key="'desc-empty-' + n" class="py-4 px-6"></td>
              </tr>

              <!-- Action row -->
              <tr>
                <td class="py-6 px-6 font-bold text-xs text-on-surface-variant bg-surface-container-low/30 rounded-bl-xl"></td>
                <td v-for="prod in store.compare" :key="prod.id" class="py-6 px-6 text-center">
                  <button
                    @click="handleAddToCart(prod)"
                    class="bg-primary text-white text-xs font-bold px-4 py-2.5 rounded-lg hover:bg-primary-container transition-all shadow-md shadow-primary/10 w-full flex items-center justify-center gap-1.5"
                  >
                    Add to Cart <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
                  </button>
                </td>
                <td v-for="n in (3 - store.compare.length)" :key="'action-empty-' + n" class="py-6 px-6 rounded-br-xl"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
