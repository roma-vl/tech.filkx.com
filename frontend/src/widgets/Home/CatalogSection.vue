<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { productApi } from "@/shared/services/api/productApi";
import { useCartStore } from "@/entities/order/model/cartStore";

interface Product {
  id: string | number;
  slug: string;
  name: string;
  price: number;
  oldPrice?: number;
  image: string;
  category: string;
  rating: number;
  reviews: number;
}

const router = useRouter();
const cartStore = useCartStore();

const bestsellers = ref<Product[]>([]);
const selectedSlug = ref("smartphones");
const isLoadingProds = ref(false);

const chips = [
  { name: "Смартфони, Телефони", slug: "smartphones" },
  { name: "Навушники", slug: "audio" },
  { name: "Планшети", slug: "tablets" },
  { name: "Смарт-годинники", slug: "smart-gadgets" },
  { name: "Зарядні пристрої", slug: "pc-accessories" },
  { name: "Ноутбуки", slug: "laptops" }
];

const mapProduct = (p: any): Product | null => {
  try {
    const name = p.name?.uk || p.name?.en || p.name || "";
    const category =
      p.categories?.[0]?.name?.uk || p.categories?.[0]?.name?.en || p.categories?.[0]?.name || "Товар";
    const firstVariant = p.variants?.[0] || {};
    const price = parseFloat(firstVariant.price) || 0;
    const oldPriceRaw = firstVariant.oldPrice || firstVariant.old_price;
    const oldPrice =
      oldPriceRaw && parseFloat(oldPriceRaw) > price ? parseFloat(oldPriceRaw) : undefined;

    let image = "https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM";
    const images = firstVariant.dimensions?.images || [];
    if (images.length > 0) {
      const primary = images.find((img: any) => img.isPrimary || img.is_primary) || images[0];
      if (primary?.url) image = primary.url;
    } else if (firstVariant.dimensions?.image) {
      image = firstVariant.dimensions.image;
    }

    const rating = p.approvedReviewsAvgRating != null ? parseFloat(p.approvedReviewsAvgRating) : (4.5 + (p.id % 6) * 0.1);
    const reviews = p.approvedReviewsCount != null ? Number(p.approvedReviewsCount) : (3 + (p.id % 8));

    return { id: p.id, slug: p.slug, name, category, price, oldPrice, image, rating, reviews };
  } catch {
    return null;
  }
};

const formatPrice = (price: number) =>
  new Intl.NumberFormat("uk-UA", { style: "currency", currency: "UAH", maximumFractionDigits: 0 }).format(price);

const selectCategory = async (slug: string) => {
  if (isLoadingProds.value) return;
  selectedSlug.value = slug;
  isLoadingProds.value = true;
  bestsellers.value = [];
  try {
    const res = await productApi.catalogGetProducts({ category: slug, per_page: 10 });
    if (res.data?.success || res.data?.status === "success") {
      const list = res.data?.data?.data || res.data?.data || [];
      bestsellers.value = list.map(mapProduct).filter(Boolean) as Product[];
    }
  } catch (e) {
    console.error("CatalogSection: load products failed:", e);
  } finally {
    isLoadingProds.value = false;
  }
};

onMounted(() => {
  selectCategory("smartphones");
});
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-8 font-sans">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-5">
      <h2 class="font-extrabold text-2xl text-zinc-900 dark:text-white tracking-tight">
        Лідери продажу
      </h2>
      <div class="flex items-center gap-2">
        <button class="w-8 h-8 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors">
          <span class="material-symbols-outlined text-sm">chevron_left</span>
        </button>
        <button class="w-8 h-8 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors">
          <span class="material-symbols-outlined text-sm">chevron_right</span>
        </button>
      </div>
    </div>

    <!-- Category Pill Chips -->
    <div class="flex items-center gap-2 overflow-x-auto pb-5 no-scrollbar">
      <button
        v-for="chip in chips"
        :key="chip.slug"
        class="px-5 py-2 rounded-full text-xs font-bold transition-all duration-200 border whitespace-nowrap"
        :class="
          selectedSlug === chip.slug
            ? 'bg-[#00a046] text-white border-transparent shadow-md shadow-[#00a046]/10'
            : 'bg-[#23242e] dark:bg-[#1a1b24] hover:bg-[#2e2f3d] dark:hover:bg-zinc-800/80 text-zinc-300 border-zinc-800'
        "
        @click="selectCategory(chip.slug)"
      >
        {{ chip.name }}
      </button>
    </div>

    <!-- Products Grid -->
    <div v-if="isLoadingProds" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
      <div v-for="i in 5" :key="i" class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-4 space-y-4 animate-pulse">
        <div class="aspect-square bg-zinc-200 dark:bg-zinc-800 rounded-lg" />
        <div class="h-3 w-16 bg-zinc-250 dark:bg-zinc-800 rounded" />
        <div class="h-4 bg-zinc-250 dark:bg-zinc-800 rounded w-full" />
        <div class="h-4 bg-zinc-250 dark:bg-zinc-800 rounded w-5/6" />
        <div class="flex justify-between items-center pt-2">
          <div class="h-6 bg-zinc-250 dark:bg-zinc-800 rounded w-20" />
          <div class="w-9 h-9 rounded-full bg-zinc-250 dark:bg-zinc-800" />
        </div>
      </div>
    </div>

    <div v-else-if="bestsellers.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 animate-in fade-in duration-300">
      <div
        v-for="prod in bestsellers"
        :key="prod.id"
        class="group flex flex-col p-4 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl hover:shadow-lg hover:border-zinc-200 dark:hover:border-zinc-700 transition-all duration-300 relative overflow-hidden"
      >
        <!-- Image & Wishlist Container -->
        <router-link
          :to="{ name: 'product-detail', params: { id: prod.slug } }"
          class="block aspect-square bg-zinc-50 dark:bg-zinc-850 rounded-lg mb-3 overflow-hidden relative flex items-center justify-center cursor-pointer"
        >
          <img
            class="w-full h-full object-contain p-1 group-hover:scale-105 transition-transform duration-500"
            :src="prod.image"
            :alt="prod.name"
          />
          <!-- Wishlist -->
          <button
            class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/95 dark:bg-zinc-800/95 shadow hover:scale-110 active:scale-95 transition-all flex items-center justify-center text-zinc-400 hover:text-rose-600 z-10"
            @click.stop.prevent="cartStore.toggleWishlist(prod as any)"
          >
            <span
              class="material-symbols-outlined text-[17px]"
              :class="{ 'fill text-rose-600': cartStore.isInWishlist(prod.id as any) }"
              :style="cartStore.isInWishlist(prod.id as any) ? 'font-variation-settings: \'FILL\' 1;' : ''"
            >
              favorite
            </span>
          </button>
        </router-link>

        <!-- Info -->
        <div class="flex flex-col flex-grow">
          <!-- Category -->
          <span class="text-zinc-400 dark:text-zinc-500 font-extrabold text-[10px] mb-1 uppercase tracking-wider select-none">
            {{ prod.category }}
          </span>
          
          <!-- Title -->
          <router-link
            :to="{ name: 'product-detail', params: { id: prod.slug } }"
            class="font-bold text-sm text-zinc-800 dark:text-zinc-200 hover:text-[#00a046] transition-colors line-clamp-2 leading-snug min-h-[40px] block"
          >
            {{ prod.name }}
          </router-link>

          <!-- Rating -->
          <div class="flex items-center gap-1 my-2">
            <div class="flex">
              <span
                v-for="star in 5"
                :key="star"
                class="material-symbols-outlined text-[12px]"
                :class="star <= Math.round(prod.rating) ? 'text-amber-400' : 'text-zinc-300 dark:text-zinc-650'"
                :style="star <= Math.round(prod.rating) ? 'font-variation-settings: &quot;FILL&quot; 1' : ''"
              >star</span>
            </div>
            <span class="text-zinc-400 text-[10px] font-bold ml-0.5">({{ prod.reviews }})</span>
          </div>

          <!-- Price -->
          <div class="flex flex-wrap items-baseline gap-1.5 mt-auto">
            <span class="font-black text-base text-[#00a046]">{{ formatPrice(prod.price) }}</span>
            <span v-if="prod.oldPrice" class="text-xs text-zinc-400 line-through font-bold">{{ formatPrice(prod.oldPrice) }}</span>
          </div>

          <!-- Actions -->
          <div class="mt-3 flex gap-2">
            <button
              class="flex-grow bg-[#00a046] hover:bg-[#00b050] text-white py-2 rounded-lg text-xs font-extrabold shadow-sm transition-colors flex items-center justify-center gap-1.5"
              @click.stop="cartStore.addToCart(prod as any)"
            >
              <span>В кошик</span>
              <span class="material-symbols-outlined text-[14px]">shopping_cart</span>
            </button>
            <button
              class="w-8 h-8 border border-zinc-200 dark:border-zinc-800 text-zinc-500 dark:text-zinc-400 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all flex items-center justify-center shrink-0"
              :class="{ 'bg-emerald-500/10 border-emerald-500/20 text-[#00a046]': cartStore.isInCompare(prod.id as any) }"
              title="Порівняти"
              @click.stop.prevent="cartStore.toggleCompare(prod as any)"
            >
              <span
                class="material-symbols-outlined text-[15px]"
                :class="{ fill: cartStore.isInCompare(prod.id as any) }"
              >compare_arrows</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center py-16 text-zinc-500 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 shadow-sm">
      <span class="material-symbols-outlined text-5xl mb-3 text-zinc-400 dark:text-zinc-650">inventory_2</span>
      <p class="text-sm font-bold">У цій категорії поки що немає лідерів продажу</p>
    </div>

  </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
