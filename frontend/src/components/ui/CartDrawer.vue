<script setup>
import { computed } from 'vue';
import { store } from '@/store.js';

const shippingThreshold = 500;
const shippingProgress = computed(() => {
  const total = store.cartTotal;
  return Math.min(100, (total / shippingThreshold) * 100);
});

const remainingForFreeShipping = computed(() => {
  const total = store.cartTotal;
  return Math.max(0, shippingThreshold - total);
});

const checkout = () => {
  if (store.cart.length === 0) return;
  store.openCartPage();
};
</script>

<template>
  <div v-if="store.activeDrawer === 'cart'" class="fixed inset-0 z-[90] flex justify-end">
    <!-- Backdrop Overlay -->
    <div
      @click="store.closeDrawer()"
      class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
    ></div>

    <!-- Drawer Panel -->
    <div
      class="relative w-full max-w-md bg-white h-full flex flex-col shadow-2xl border-l border-outline-variant/30 animate-in slide-in-from-right duration-300 z-10"
    >
      <!-- Header -->
      <div class="p-6 border-b border-surface-variant flex items-center justify-between">
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-primary text-[28px]">shopping_cart</span>
          <h2 class="font-headline-md text-headline-md text-on-surface">Your Shopping Cart</h2>
        </div>
        <button
          @click="store.closeDrawer()"
          class="w-10 h-10 rounded-full hover:bg-surface-container flex items-center justify-center text-on-surface-variant transition-colors"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Shipping Goal Tracker -->
      <div v-if="store.cart.length > 0" class="px-6 py-4 bg-surface-container-low border-b border-surface-variant flex flex-col gap-2">
        <div class="flex justify-between items-center text-xs font-semibold text-on-surface-variant">
          <span v-if="remainingForFreeShipping > 0">
            Spend <span class="text-primary font-bold">${{ remainingForFreeShipping.toFixed(2) }}</span> more for FREE shipping!
          </span>
          <span v-else class="text-primary flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px] text-primary">local_shipping</span>
            You qualify for Free Express Shipping!
          </span>
          <span>{{ Math.round(shippingProgress) }}%</span>
        </div>
        <div class="w-full bg-surface-container-highest h-2 rounded-full overflow-hidden">
          <div
            class="bg-primary h-full rounded-full transition-all duration-500"
            :style="{ width: shippingProgress + '%' }"
          ></div>
        </div>
      </div>

      <!-- Content (Scrollable list or Empty State) -->
      <div class="flex-grow overflow-y-auto p-6 flex flex-col gap-4">
        <!-- Empty State -->
        <div v-if="store.cart.length === 0" class="flex-grow flex flex-col items-center justify-center text-center gap-4 py-12">
          <div class="w-20 h-20 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant border border-outline-variant/30">
            <span class="material-symbols-outlined text-[48px]">remove_shopping_cart</span>
          </div>
          <h3 class="font-title-lg text-title-lg text-on-surface">Your cart is empty</h3>
          <p class="text-on-surface-variant text-xs max-w-[240px]">
            Explore our curated collections of premium electronics and start adding products.
          </p>
          <button
            @click="store.closeDrawer()"
            class="bg-primary text-white px-8 py-3 rounded-lg font-bold hover:scale-105 active:scale-95 transition-all shadow-md shadow-primary/10 mt-2"
          >
            Start Shopping
          </button>
        </div>

        <!-- Cart Items -->
        <div
          v-else
          v-for="item in store.cart"
          :key="item.id"
          class="flex gap-4 p-4 bg-surface-container-lowest border border-outline-variant/20 rounded-xl relative group hover:shadow-md transition-shadow"
        >
          <!-- Thumbnail -->
          <div class="w-20 h-20 bg-surface-container-low rounded-lg p-2 flex items-center justify-center shrink-0">
            <img class="w-full h-full object-contain" :src="item.image" :alt="item.name" />
          </div>

          <!-- Details -->
          <div class="flex-grow flex flex-col justify-between">
            <div>
              <span class="text-[10px] font-bold text-primary uppercase tracking-wider">{{ item.category }}</span>
              <h4 class="font-title-md text-sm text-on-surface line-clamp-1 leading-tight">{{ item.name }}</h4>
            </div>

            <div class="flex items-center justify-between mt-2">
              <span class="font-bold text-on-surface text-sm">${{ (item.price * item.quantity).toFixed(2) }}</span>

              <!-- Quantity Selector -->
              <div class="flex items-center border border-outline-variant/50 rounded-lg overflow-hidden h-8 bg-white">
                <button
                  @click="store.updateCartQuantity(item.id, item.quantity - 1)"
                  class="w-8 h-full flex items-center justify-center hover:bg-surface-container transition-colors text-on-surface"
                >
                  <span class="material-symbols-outlined text-[16px]">remove</span>
                </button>
                <span class="px-3 text-xs font-bold text-on-surface">{{ item.quantity }}</span>
                <button
                  @click="store.updateCartQuantity(item.id, item.quantity + 1)"
                  class="w-8 h-full flex items-center justify-center hover:bg-surface-container transition-colors text-on-surface"
                >
                  <span class="material-symbols-outlined text-[16px]">add</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Delete button -->
          <button
            @click="store.removeFromCart(item.id)"
            class="absolute top-2 right-2 text-on-surface-variant hover:text-error transition-colors"
          >
            <span class="material-symbols-outlined text-[18px]">delete</span>
          </button>
        </div>
      </div>

      <!-- Footer (Summary + Checkout button) -->
      <div v-if="store.cart.length > 0" class="p-6 border-t border-surface-variant bg-surface-container-lowest">
        <div class="flex flex-col gap-3 mb-6">
          <div class="flex justify-between items-center text-xs text-on-surface-variant font-semibold">
            <span>Subtotal</span>
            <span class="text-on-surface font-bold">${{ store.cartTotal.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between items-center text-xs text-on-surface-variant font-semibold">
            <span>Estimated Taxes</span>
            <span class="text-on-surface font-bold">${{ (store.cartTotal * 0.1).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between items-center text-xs text-on-surface-variant font-semibold">
            <span>Shipping</span>
            <span class="font-bold" :class="remainingForFreeShipping === 0 ? 'text-primary' : 'text-on-surface'">
              {{ remainingForFreeShipping === 0 ? 'FREE' : '$25.00' }}
            </span>
          </div>
          <div class="h-px bg-surface-variant my-1"></div>
          <div class="flex justify-between items-center font-title-lg text-lg text-on-surface">
            <span>Total</span>
            <span class="font-bold text-primary">
              ${{ (store.cartTotal + (store.cartTotal * 0.1) + (remainingForFreeShipping === 0 ? 0 : 25)).toFixed(2) }}
            </span>
          </div>
        </div>

        <button
          @click="checkout()"
          class="w-full bg-primary text-white py-4 rounded-xl font-bold hover:bg-primary-container transition-all flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-95 duration-200"
        >
          Proceed to Checkout <span class="material-symbols-outlined">arrow_forward</span>
        </button>
      </div>
    </div>
  </div>
</template>
