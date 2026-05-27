<script setup>
import { store } from '@/store.js';
</script>

<template>
  <div class="space-y-stack-md animate-fade">
    <div v-if="store.compare.length > 0" class="overflow-x-auto bg-surface-container-lowest rounded-xl border border-outline-variant shadow-sm">
      <table class="w-full min-w-[700px] border-collapse text-left text-xs">
        <thead>
          <tr class="bg-surface-container-low border-b border-outline-variant text-on-surface-variant font-bold uppercase text-[10px]">
            <th class="p-4 w-1/4">Product details</th>
            <th v-for="product in store.compare" :key="product.id" class="p-4 relative">
              <button @click="store.removeFromCompare(product.id)" class="absolute top-2 right-2 text-on-surface-variant hover:text-error transition-colors">
                <span class="material-symbols-outlined text-[16px]">close</span>
              </button>
              <span class="inline-block py-2">Item</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-outline-variant text-on-surface">
          <tr>
            <td class="p-4 font-semibold bg-surface-container-low/30">Preview</td>
            <td v-for="product in store.compare" :key="product.id" class="p-4">
              <div class="flex flex-col gap-2">
                <img :src="product.image" :alt="product.name" class="w-20 h-20 object-contain mx-auto bg-white rounded border p-1" />
                <h4 class="font-bold text-center line-clamp-2 text-[11px]">{{ product.name }}</h4>
              </div>
            </td>
          </tr>
          <tr>
            <td class="p-4 font-semibold bg-surface-container-low/30">Price</td>
            <td v-for="product in store.compare" :key="product.id" class="p-4 font-bold text-primary text-sm text-center">${{ product.price.toFixed(2) }}</td>
          </tr>
          <tr>
            <td class="p-4 font-semibold bg-surface-container-low/30">Brand</td>
            <td v-for="product in store.compare" :key="product.id" class="p-4 text-center">{{ product.brand }}</td>
          </tr>
          <tr>
            <td class="p-4 font-semibold bg-surface-container-low/30">RAM</td>
            <td v-for="product in store.compare" :key="product.id" class="p-4 text-center">{{ product.ram || 'N/A' }}</td>
          </tr>
          <tr>
            <td class="p-4 font-semibold bg-surface-container-low/30">Rating</td>
            <td v-for="product in store.compare" :key="product.id" class="p-4 text-center">
              <div class="flex items-center justify-center gap-1 text-star-rating">
                <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="font-bold text-on-surface-variant text-[11px]">{{ product.rating }} ({{ product.reviews }})</span>
              </div>
            </td>
          </tr>
          <tr>
            <td class="p-4 font-semibold bg-surface-container-low/30">Description</td>
            <td v-for="product in store.compare" :key="product.id" class="p-4 text-on-surface-variant text-[11px] leading-relaxed">{{ product.description }}</td>
          </tr>
          <tr>
            <td class="p-4 bg-surface-container-low/30"></td>
            <td v-for="product in store.compare" :key="product.id" class="p-4 text-center">
              <button @click="store.addToCart(product)" class="bg-primary text-on-primary px-4 py-2 rounded-lg font-bold text-[10px] uppercase hover:bg-primary-container transition-all tracking-wider inline-flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">shopping_cart</span> Add to Cart
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="bg-surface-container-lowest rounded-xl border border-outline-variant p-12 text-center shadow-sm">
      <span class="material-symbols-outlined text-[48px] text-on-surface-variant mb-4">compare_arrows</span>
      <h3 class="font-title-lg text-on-surface text-lg">No Items to Compare</h3>
      <p class="text-xs text-on-surface-variant max-w-sm mx-auto mt-2">Add products via the "Compare" checkbox on the catalog page.</p>
      <a href="/catalog" class="inline-block bg-primary text-on-primary font-bold text-xs py-2.5 px-6 rounded-lg hover:bg-primary-container transition-all mt-6">View Catalog</a>
    </div>
  </div>
</template>

<style scoped>
.animate-fade { animation: fadeIn .25s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
</style>
