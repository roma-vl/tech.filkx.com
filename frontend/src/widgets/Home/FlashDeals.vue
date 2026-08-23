<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import { useI18n } from "vue-i18n";
import { useCartStore } from "@/entities/order/model/cartStore";
import UiSectionLink from "@/shared/ui/UiSectionLink.vue";

interface FlashProduct {
  id: string | number;
  slug: string;
  category: string;
  name: string;
  rating: number;
  reviews: number;
  price: number;
  oldPrice?: number;
  image: string;
  discount: string;
  leftCount?: number;
}

const props = defineProps<{
  products: FlashProduct[];
}>();

const emit = defineEmits<{
  (e: "refresh-products"): void;
}>();

const cartStore = useCartStore();
const { t } = useI18n();

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};

const hours = ref(0);
const minutes = ref(0);
const seconds = ref(0);
let timerId: ReturnType<typeof setInterval> | null = null;
let lastRefreshedHour = new Date().getHours();

const updateTimer = () => {
  const now = new Date();
  hours.value = 0;
  minutes.value = 59 - now.getMinutes();
  seconds.value = 59 - now.getSeconds();

  const currentHour = now.getHours();
  if (currentHour !== lastRefreshedHour) {
    lastRefreshedHour = currentHour;
    emit("refresh-products");
  }
};

const startCountdown = () => {
  updateTimer();
  timerId = setInterval(updateTimer, 1000);
};

onMounted(() => {
  startCountdown();
});

onUnmounted(() => {
  if (timerId) clearInterval(timerId);
});

const formatNumber = (num: number) => {
  return num < 10 ? `0${num}` : num;
};
</script>

<template>
  <section
    class="bg-zinc-50 dark:bg-zinc-950 py-14 border-y border-zinc-100 dark:border-zinc-900 font-sans"
  >
    <div class="max-w-container-max mx-auto px-4 md:px-8">
      <!-- Section Header -->
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10"
      >
        <div class="space-y-2">
          <span
            class="text-rose-600 font-extrabold text-xs uppercase tracking-widest"
            >{{ t("home.flashDeals.badge") }}</span
          >
          <div class="flex flex-wrap items-center gap-4">
            <h2
              class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight"
            >
              {{ t("home.flashDeals.title") }}
            </h2>
            <!-- Countdown timer -->
            <div
              class="flex items-center gap-2 bg-rose-600 text-white px-4 py-1.5 rounded-lg text-xs font-black shadow-sm shadow-rose-600/20"
            >
              <span class="material-symbols-outlined text-[16px] animate-pulse"
                >schedule</span
              >
              <span class="font-mono text-sm tracking-widest">
                {{ formatNumber(hours) }}:{{ formatNumber(minutes) }}:{{
                  formatNumber(seconds)
                }}
              </span>
            </div>
          </div>
        </div>
        <UiSectionLink :to="{ name: 'catalog', query: { discounts: '1' } }">
          {{ t("home.flashDeals.allDeals") }}
        </UiSectionLink>
      </div>

      <!-- Products Grid -->
      <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border-t border-l border-zinc-200 dark:border-zinc-800"
      >
        <div
          v-for="prod in products"
          :key="prod.id"
          class="bg-white dark:bg-zinc-900 group relative flex flex-col border-r border-b border-zinc-200 dark:border-zinc-800 hover:border hover:z-20 hover:scale-[1.1] hover:bg-[#fcfcfd] dark:hover:bg-[#0b0c10] hover:shadow-2xl transition-all duration-200"
        >
          <!-- Sale Badge -->
          <div class="absolute top-3 left-3 z-10">
            <span
              class="bg-rose-600 text-white px-2.5 py-0.5 rounded font-black text-xs shadow-sm"
            >
              {{ prod.discount }}
            </span>
          </div>

          <!-- Wishlist Button -->
          <button
            class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 dark:bg-zinc-800/90 backdrop-blur-sm shadow hover:scale-110 active:scale-95 transition-all flex items-center justify-center text-zinc-400 hover:text-rose-600 z-10"
            @click.stop="cartStore.toggleWishlist(prod)"
          >
            <span
              class="material-symbols-outlined text-[18px]"
              :class="{
                'fill text-rose-600': cartStore.isInWishlist(prod.id as any),
              }"
              :style="
                cartStore.isInWishlist(prod.id as any)
                  ? 'font-variation-settings: \'FILL\' 1;'
                  : ''
              "
            >
              favorite
            </span>
          </button>

          <div class="p-4 md:p-5 flex-grow flex flex-col">
            <!-- Product Image -->
            <router-link
              :to="{ name: 'product-detail', params: { id: prod.slug } }"
              class="block aspect-square bg-zinc-50 dark:bg-zinc-850 mb-3 overflow-hidden relative flex items-center justify-center"
            >
              <img
                class="w-full h-full object-contain"
                :src="prod.image"
                :alt="prod.name"
              />
            </router-link>

            <p
              class="text-zinc-400 dark:text-zinc-500 font-extrabold text-[10px] mb-1 uppercase tracking-wider"
            >
              {{ prod.category }}
            </p>
            <router-link
              :to="{ name: 'product-detail', params: { id: prod.slug } }"
              class="block font-bold text-[15px] text-zinc-800 dark:text-zinc-100 line-clamp-3 mb-2 leading-snug min-h-[66px] hover:text-[#00a046] transition-colors"
            >
              {{ prod.name }}
            </router-link>

            <!-- Rating -->
            <div class="flex items-center gap-1 mb-2.5 mt-auto">
              <div class="flex text-amber-400">
                <span
                  v-for="star in 5"
                  :key="star"
                  class="material-symbols-outlined text-[14px]"
                  :class="
                    star <= Math.round(prod.rating)
                      ? 'text-amber-400'
                      : 'text-zinc-300 dark:text-zinc-600'
                  "
                  :style="
                    star <= Math.round(prod.rating)
                      ? 'font-variation-settings: &quot;FILL&quot; 1'
                      : ''
                  "
                  >star</span
                >
              </div>
              <span
                class="text-zinc-500 dark:text-zinc-400 text-[11px] font-bold ml-1"
                >({{ prod.reviews }})</span
              >
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-2">
              <span class="font-black text-xl text-[#00a046]">{{
                formatPrice(prod.price)
              }}</span>
              <span
                v-if="prod.oldPrice"
                class="text-xs text-zinc-400 line-through"
                >{{ formatPrice(prod.oldPrice) }}</span
              >
            </div>
          </div>

          <!-- Low stock notice (only shown for real, low remaining quantity) -->
          <div
            v-if="
              prod.leftCount != null &&
              prod.leftCount > 0 &&
              prod.leftCount <= 10
            "
            class="px-4 md:px-5 pb-3"
          >
            <p
              class="text-[11px] font-extrabold text-rose-600 uppercase tracking-wider"
            >
              {{ t("home.flashDeals.lowStock", { count: prod.leftCount }) }}
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="px-4 md:px-5 pb-5 mt-auto flex flex-col gap-2">
            <button
              class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-2.5 rounded-lg text-sm font-extrabold shadow-sm transition-colors flex items-center justify-center gap-2"
              @click="cartStore.addToCart(prod as any)"
            >
              {{ t("common.addToCart") }}
              <span class="material-symbols-outlined text-[17px]"
                >shopping_cart</span
              >
            </button>
            <button
              class="w-full py-2 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 rounded-lg font-bold hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all text-xs flex items-center justify-center gap-1.5"
              :class="{
                'bg-emerald-500/10 border-emerald-500/20 text-[#00a046]':
                  cartStore.isInCompare(prod.id as any),
              }"
              @click="cartStore.toggleCompare(prod)"
            >
              <span
                class="material-symbols-outlined text-[16px]"
                :class="{ fill: cartStore.isInCompare(prod.id as any) }"
                >compare_arrows</span
              >
              {{ t("common.compare") }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
