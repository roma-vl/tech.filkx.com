<template>
  <div class="space-y-stack-md">
    <article
      v-for="item in cart"
      :key="item.id"
      class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant flex flex-col md:flex-row gap-6"
    >
      <div
        class="w-full md:w-32 h-32 bg-surface-container rounded-lg overflow-hidden flex-shrink-0 p-3"
      >
        <img
          class="w-full h-full object-contain"
          :src="item.image"
          :alt="item.name"
        />
      </div>
      <div class="flex-grow flex flex-col justify-between gap-5">
        <div
          class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3"
        >
          <div>
            <h3
              class="font-title-lg text-title-lg text-zinc-900 dark:text-white font-bold text-lg"
            >
              {{ item.name }}
            </h3>
            <p
              class="font-body-md text-on-surface-variant text-sm text-gray-400 mt-1"
            >
              {{ item.sku }} | Premium Product
            </p>
          </div>
          <span
            class="font-price-lg text-price-lg text-xl font-bold text-[#00a046]"
            >{{ formatPrice(item.price * item.quantity) }}</span
          >
        </div>
        <div
          class="flex flex-col lg:flex-row lg:items-center justify-between gap-4"
        >
          <div class="flex flex-wrap items-center gap-4">
            <div
              class="flex items-center border border-outline-variant rounded-lg overflow-hidden"
            >
              <button
                class="px-3 py-1 hover:bg-surface-variant transition-colors text-zinc-700 dark:text-zinc-300"
                type="button"
                @click="updateCartQuantity(item.id, item.quantity - 1)"
              >
                <span class="material-symbols-outlined text-body-md"
                  >remove</span
                >
              </button>
              <span
                class="px-4 font-title-md text-zinc-900 dark:text-white font-bold"
                >{{ item.quantity }}</span
              >
              <button
                class="px-3 py-1 hover:bg-surface-variant transition-colors text-zinc-700 dark:text-zinc-300"
                type="button"
                @click="updateCartQuantity(item.id, item.quantity + 1)"
              >
                <span class="material-symbols-outlined text-body-md">add</span>
              </button>
            </div>
            <button
              class="text-red-500 font-label-md flex items-center gap-1 hover:underline"
              type="button"
              @click="removeFromCart(item.id)"
            >
              <span class="material-symbols-outlined text-[18px]">delete</span>
              Remove
            </button>
          </div>
          <div
            class="text-on-surface-variant font-label-md flex items-center gap-1 text-sm text-gray-500"
          >
            <span class="material-symbols-outlined text-[#00a046] text-[18px]"
              >check_circle</span
            >
            Available
          </div>
        </div>
      </div>
    </article>
  </div>

  <div class="mt-stack-lg pt-stack-lg border-t border-outline-variant mt-10">
    <h2
      class="font-headline-md text-headline-md mb-6 text-zinc-900 dark:text-white font-bold text-xl"
    >
      Saved for later (1)
    </h2>
    <div
      class="bg-surface-container-low p-4 rounded-xl border border-dashed border-outline-variant flex items-center gap-4 opacity-80"
    >
      <div
        class="w-16 h-16 bg-surface-variant rounded-lg flex-shrink-0 overflow-hidden"
      >
        <img
          class="w-full h-full object-cover"
          :src="savedItem.image"
          :alt="savedItem.name"
        />
      </div>
      <div class="flex-grow">
        <h4
          class="font-title-md text-title-md text-zinc-800 dark:text-zinc-200 font-bold"
        >
          {{ savedItem.name }}
        </h4>
        <p class="font-label-md text-on-surface-variant text-gray-500">
          {{ formatPrice(savedItem.price) }}
        </p>
      </div>
      <button
        class="px-4 py-2 bg-secondary-container text-on-secondary-container rounded-full font-label-md hover:opacity-90 transition-opacity"
        type="button"
        @click="$emit('moveToCart', savedItem)"
      >
        Move to Cart
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { CartItem } from "@/entities/order/types";

defineProps<{
  cart: CartItem[];
  savedItem: any;
  formatPrice: (p: number) => string;
}>();

const emit = defineEmits<{
  (e: "updateQuantity", id: number | string, qty: number): void;
  (e: "remove", id: number | string): void;
  (e: "moveToCart", item: any): void;
}>();

function updateCartQuantity(id: number | string, val: number) {
  if (val < 1) return;
  emit("updateQuantity", id, val);
}

function removeFromCart(id: number | string) {
  emit("remove", id);
}
</script>
