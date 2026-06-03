<template>
  <div
    v-if="isOpen && product"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg max-w-3xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 px-6 py-4.5 flex justify-between items-center"
      >
        <div>
          <span
            class="text-[9px] font-black text-[#00a046] uppercase bg-emerald-500/10 px-2.5 py-0.5 rounded tracking-wider"
          >Швидкий перегляд</span>
          <h3
            class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white mt-1"
          >
            Детальні характеристики
          </h3>
        </div>
        <button
          class="text-zinc-400 hover:text-zinc-650 dark:hover:text-zinc-250"
          @click="emit('close')"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <div
        class="p-6 overflow-y-auto space-y-6 text-xs md:text-sm grid grid-cols-1 md:grid-cols-2 gap-8 items-start"
      >
        <!-- Left: Product Image block -->
        <div class="flex flex-col gap-4 items-center">
          <div
            class="bg-white rounded-lg border border-zinc-100 dark:border-zinc-800 p-6 flex items-center justify-center w-full aspect-square relative select-none"
          >
            <img
              :src="product.image"
              :alt="product.name"
              class="max-h-[220px] object-contain"
            >
            <span
              v-if="product.badge"
              :class="product.badgeClass"
              class="absolute top-3 left-3 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest shadow-sm"
            >{{ product.badge }}</span>
          </div>
          <div class="flex items-center gap-1.5 text-zinc-400">
            <span class="material-symbols-outlined text-[16px]">info</span>
            <span class="text-[10px] font-extrabold uppercase tracking-wide">Зображення слугує для демонстрації</span>
          </div>
        </div>

        <!-- Right: Specs list -->
        <div class="space-y-5 text-left">
          <div>
            <h4
              class="font-black text-zinc-900 dark:text-white text-base leading-snug font-bold"
            >
              {{ product.name }}
            </h4>
            <div class="flex items-center gap-3 mt-2.5">
              <span
                class="text-xs font-black text-[#00a046] bg-emerald-500/5 border border-emerald-500/10 px-2 py-0.5 rounded"
              >{{ product.brand }}</span>
              <div class="flex items-center gap-1 text-amber-400">
                <span
                  class="material-symbols-outlined text-[16px]"
                  style="font-variation-settings: &quot;FILL&quot; 1"
                >star</span>
                <span
                  class="font-extrabold text-zinc-800 dark:text-zinc-200 text-xs mt-0.5"
                >{{ product.rating }} ({{ product.reviews }} відгуків)</span>
              </div>
            </div>
          </div>

          <!-- Price Block -->
          <div
            class="bg-zinc-50 dark:bg-zinc-850 p-4.5 rounded-lg border border-zinc-100 dark:border-zinc-800 flex items-center justify-between"
          >
            <div class="flex flex-col">
              <span
                v-if="product.oldPrice"
                class="text-xs text-zinc-400 line-through font-extrabold"
              >{{ formatPrice(product.oldPrice) }}</span>
              <span class="text-xl font-black text-[#00a046] font-bold">{{
                formatPrice(product.price)
              }}</span>
            </div>
            <span
              v-if="product.inStock"
              class="text-xs text-[#00a046] font-extrabold bg-[#00a046]/10 border border-[#00a046]/20 px-3 py-1 rounded-md"
            >В наявності</span>
            <span
              v-else
              class="text-xs text-zinc-550 font-extrabold bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-3 py-1 rounded-md"
            >Немає в наявності</span>
          </div>

          <!-- Description -->
          <div class="space-y-1.5">
            <span
              class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider"
            >Короткий опис</span>
            <p class="text-zinc-650 dark:text-zinc-350 text-xs leading-relaxed">
              {{ product.description }}
            </p>
          </div>

          <!-- Tech Specs Table -->
          <div class="space-y-2">
            <span
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >Основні параметри</span>
            <div
              class="border border-zinc-100 dark:border-zinc-800 rounded-lg overflow-hidden divide-y divide-zinc-100 dark:divide-zinc-800 text-xs"
            >
              <div class="flex p-2.5 bg-zinc-50/50 dark:bg-zinc-850/50">
                <span class="w-1/3 text-zinc-400 font-bold">Процесор</span><span
                  class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold"
                >{{ product.specs?.processor || "" }}</span>
              </div>
              <div class="flex p-2.5">
                <span class="w-1/3 text-zinc-400 font-bold">Екран</span><span
                  class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold"
                >{{ product.specs?.screen || "" }}</span>
              </div>
              <div class="flex p-2.5 bg-zinc-50/50 dark:bg-zinc-850/50">
                <span class="w-1/3 text-zinc-400 font-bold">Пам'ять</span><span
                  class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold"
                >{{ product.ram }} RAM / {{ product.specs?.storage || "" }}</span>
              </div>
              <div class="flex p-2.5">
                <span class="w-1/3 text-zinc-400 font-bold">Операційна система</span><span
                  class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold"
                >{{ product.specs?.os || "" }}</span>
              </div>
              <div class="flex p-2.5 bg-zinc-50/50 dark:bg-zinc-850/50">
                <span class="w-1/3 text-zinc-400 font-bold">Вага</span><span
                  class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold"
                >{{ product.specs?.weight || "" }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Buttons -->
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-t border-zinc-100 dark:border-zinc-800 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3"
      >
        <button
          class="border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 px-5 py-2.5 rounded-md font-extrabold text-xs md:text-sm transition-colors flex items-center justify-center gap-1.5"
          @click="cartStore.toggleCompare(product)"
        >
          <span class="material-symbols-outlined text-[16px] md:text-[18px]">compare_arrows</span>
          {{ cartStore.isInCompare(product.id) ? "У порівнянні" : "Порівняти" }}
        </button>
        <button
          :disabled="!product.inStock"
          :class="
            product.inStock
              ? 'bg-[#00a046] hover:bg-[#00b050] text-white'
              : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed'
          "
          class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-md font-extrabold text-xs md:text-sm transition-all flex items-center justify-center gap-2 shadow-sm uppercase tracking-wider"
          @click="
            cartStore.addToCart(product);
            emit('close');
          "
        >
          <span class="material-symbols-outlined text-[16px] md:text-[18px]">shopping_cart</span>
          Додати в кошик
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useCartStore } from "@/entities/order/model/cartStore";

defineProps<{
  isOpen: boolean;
  product: any;
}>();

const emit = defineEmits(["close"]);

const cartStore = useCartStore();

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};
</script>

<style scoped>
.animate-fade {
  animation: fadeIn 0.22s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(3px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
