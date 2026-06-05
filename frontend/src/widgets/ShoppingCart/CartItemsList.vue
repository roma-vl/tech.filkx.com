<template>
  <div class="space-y-stack-md">
    <article
      v-for="item in cart"
      :key="item.id"
      class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant flex flex-col md:flex-row gap-6 transition-all duration-300"
      :class="{ 'opacity-65 grayscale bg-zinc-50 dark:bg-zinc-900/40 border-dashed': item.stock !== undefined && item.stock <= 0 }"
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
              class="flex items-center border border-outline-variant rounded-lg overflow-hidden h-9 bg-white dark:bg-zinc-800"
              :class="{ 'opacity-50 pointer-events-none': item.stock !== undefined && item.stock <= 0 }"
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
                class="px-4 font-title-md text-zinc-900 dark:text-white font-bold text-sm"
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
            v-if="item.stock !== undefined && item.stock <= 0"
            class="text-red-500 font-label-md flex items-center gap-1 text-sm font-semibold"
          >
            <span class="material-symbols-outlined text-red-500 text-[18px]"
              >error</span
            >
            Немає в наявності
          </div>
        </div>
      </div>
    </article>
  </div>

  <div class="mt-stack-lg pt-stack-lg border-t border-outline-variant mt-10">
    <h2
      class="font-headline-md text-headline-md mb-6 text-zinc-900 dark:text-white font-bold text-xl"
    >
      Saved for later ({{ wishlist.length }})
    </h2>
    <div v-if="wishlist.length === 0" class="text-zinc-500 dark:text-zinc-400 text-sm p-6 text-center bg-zinc-50 dark:bg-zinc-900/10 rounded-xl border border-dashed border-outline-variant">
      No items saved for later.
    </div>
    <div v-else class="space-y-4">
      <div
        v-for="item in wishlist"
        :key="item.id"
        class="bg-surface-container-low p-4 rounded-xl border border-dashed border-outline-variant flex items-center gap-4 opacity-90 hover:opacity-100 transition-opacity"
      >
        <div
          class="w-16 h-16 bg-white dark:bg-zinc-800 rounded-lg flex-shrink-0 overflow-hidden p-1 border border-outline-variant/30 flex items-center justify-center"
        >
          <img
            class="w-full h-full object-contain"
            :src="item.image"
            :alt="item.name"
          />
        </div>
        <div class="flex-grow">
          <h4
            class="font-title-md text-title-md text-zinc-800 dark:text-zinc-200 font-bold line-clamp-1"
          >
            {{ item.name }}
          </h4>
          <p class="font-label-md text-on-surface-variant text-gray-500">
            {{ formatPrice(item.price) }}
          </p>
        </div>
        <button
          class="px-4 py-2 bg-[#00a046] text-white rounded-full font-semibold hover:bg-[#00b050] transition-colors text-sm"
          type="button"
          @click="$emit('moveToCart', item)"
        >
          Move to Cart
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { CartItem } from "@/entities/order/types";

defineProps<{
  cart: CartItem[];
  wishlist: any[];
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
