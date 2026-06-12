<script setup lang="ts">
import { useCartStore } from "@/entities/order/model/cartStore";
import { UiButton } from "@/shared/ui";

const cartStore = useCartStore();
</script>

<template>
  <div class="space-y-6 animate-fade font-sans">
    <div
      v-if="cartStore.wishlist.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
    >
      <div
        v-for="product in cartStore.wishlist"
        :key="product.id"
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all group flex flex-col justify-between"
      >
        <div
          class="p-4 bg-white dark:bg-zinc-800 relative flex justify-center items-center aspect-square border-b border-zinc-200 dark:border-zinc-800"
        >
          <router-link
            :to="{ name: 'product-detail', params: { id: (product as any).slug || product.id } }"
            class="w-full h-full flex items-center justify-center"
          >
            <img
              :src="product.image"
              :alt="product.name"
              class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300"
            >
          </router-link>
          <button
            class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 hover:bg-rose-500/10 hover:text-rose-500 text-zinc-400 dark:text-zinc-500 rounded-full transition-all"
            @click="cartStore.toggleWishlist(product as any)"
          >
            <span class="material-symbols-outlined text-[16px]">close</span>
          </button>
        </div>
        <div class="p-5 flex-1 flex flex-col justify-between gap-4">
          <div>
            <p
              class="text-[10px] text-[#00a046] font-extrabold uppercase tracking-wider mb-1.5"
            >
              {{ product.category }}
            </p>
            <router-link
              :to="{ name: 'product-detail', params: { id: (product as any).slug || product.id } }"
              class="block font-extrabold text-zinc-800 dark:text-zinc-200 text-sm md:text-base line-clamp-2 leading-snug group-hover:text-[#00a046] transition-colors"
            >
              {{ product.name }}
            </router-link>
          </div>
          <div class="flex items-center justify-between gap-2 mt-auto">
            <span class="font-black text-[#00a046] text-lg">{{ product.price.toFixed(2) }} ₴</span>
            <UiButton
              size="sm"
              @click="cartStore.addToCart(product as any)"
            >
              <template #prefix>
                <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
              </template>
              Додати
            </UiButton>
          </div>
        </div>
      </div>
    </div>

    <div
      v-else
      class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-12 text-center shadow-sm"
    >
      <div
        class="w-16 h-16 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4"
      >
        <span
          class="material-symbols-outlined text-[32px]"
          style="font-variation-settings: &quot;FILL&quot; 1"
        >favorite</span>
      </div>
      <h3 class="font-extrabold text-lg text-zinc-800 dark:text-zinc-200">
        Ваш список обраного порожній
      </h3>
      <p
        class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 max-w-sm mx-auto mt-2"
      >
        Натисніть на іконку серця біля будь-якого товару, щоб зберегти його тут.
      </p>
      <UiButton
        :to="{ name: 'catalog' }"
        class="mt-6"
      >
        Перейти до товарів
      </UiButton>
    </div>
  </div>
</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.25s ease-out forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
