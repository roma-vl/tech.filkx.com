<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { productApi } from "@/shared/services/api/productApi";
import { useCartStore } from "@/entities/order/model/cartStore";

interface Category {
  id: string | number;
  slug: string;
  name: any;
}

interface Product {
  id: string | number;
  slug: string;
  name: string;
  price: number;
  oldPrice?: number;
  image: string;
  category: string;
}

const router = useRouter();
const cartStore = useCartStore();

const categories = ref<Category[]>([]);
const products = ref<Product[]>([]);
const selectedSlug = ref("");
const isLoadingCats = ref(true);
const isLoadingProds = ref(false);

const getCategoryIcon = (slug: string) => {
  const s = slug ? slug.toLowerCase() : "";
  if (s.includes("phone") || s.includes("smartfon") || s.includes("telefon")) return "smartphone";
  if (s.includes("laptop") || s.includes("noutbuk") || s.includes("comp") || s.includes("komp")) return "laptop_mac";
  if (s.includes("audio") || s.includes("sound") || s.includes("navushn") || s.includes("headphone")) return "headphones";
  if (s.includes("watch") || s.includes("hodynn") || s.includes("braslet")) return "watch";
  if (s.includes("game") || s.includes("igr") || s.includes("heym") || s.includes("play")) return "sports_esports";
  if (s.includes("camera") || s.includes("foto") || s.includes("kamer")) return "photo_camera";
  if (s.includes("tab") || s.includes("plansh")) return "tablet_mac";
  if (s.includes("tv") || s.includes("telev")) return "tv";
  if (s.includes("home") || s.includes("dim") || s.includes("pobu")) return "home";
  return "grid_view";
};

const getCatName = (cat: Category) => {
  if (!cat) return "";
  if (typeof cat.name === "object") return cat.name?.uk || cat.name?.en || "";
  return String(cat.name || "");
};

const mapProduct = (p: any): Product | null => {
  try {
    const name = p.name?.uk || p.name?.en || p.name || "";
    const category =
      p.categories?.[0]?.name?.uk ||
      p.categories?.[0]?.name?.en ||
      p.categories?.[0]?.name ||
      "";
    const firstVariant = p.variants?.[0] || {};
    const price = parseFloat(firstVariant.price) || 0;
    const oldPriceRaw = firstVariant.oldPrice || firstVariant.old_price;
    const oldPrice =
      oldPriceRaw && parseFloat(oldPriceRaw) > price
        ? parseFloat(oldPriceRaw)
        : undefined;

    let image =
      "https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM";
    const images = firstVariant.dimensions?.images || [];
    if (images.length > 0) {
      const primary =
        images.find((img: any) => img.isPrimary || img.is_primary) || images[0];
      if (primary?.url) image = primary.url;
    } else if (firstVariant.dimensions?.image) {
      image = firstVariant.dimensions.image;
    }

    return { id: p.id, slug: p.slug, name, category, price, oldPrice, image };
  } catch {
    return null;
  }
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};

const selectCategory = async (slug: string) => {
  if (selectedSlug.value === slug || isLoadingProds.value) return;
  selectedSlug.value = slug;
  isLoadingProds.value = true;
  products.value = [];
  try {
    const response = await productApi.catalogGetProducts({ category: slug, per_page: 8 });
    const data = response.data;
    if (data && (data.success || data.status === "success")) {
      const list = data.data?.data || data.data || [];
      products.value = list.map(mapProduct).filter(Boolean) as Product[];
    }
  } catch (e) {
    console.error("BestSellers: load products failed:", e);
  } finally {
    isLoadingProds.value = false;
  }
};

onMounted(async () => {
  try {
    const response = await productApi.catalogGetCategories();
    const data = response.data;
    if (data && (data.success || data.status === "success")) {
      categories.value = data.data || [];
      if (categories.value.length > 0) {
        await selectCategory(categories.value[0].slug);
      }
    }
  } catch (e) {
    console.error("BestSellers: load categories failed:", e);
  } finally {
    isLoadingCats.value = false;
  }
});
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-12 font-sans">
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <span class="text-[#00a046] font-extrabold text-xs uppercase tracking-widest">Топ товари</span>
        <h2 class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight mt-1">
          Лідери продажу
        </h2>
      </div>
      <button
        class="hidden sm:flex items-center gap-1 text-xs font-black text-[#00a046] hover:text-[#00b050] uppercase tracking-wider transition-colors"
        @click="router.push({ name: 'catalog', query: { category: selectedSlug } })"
      >
        Всі товари
        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
      </button>
    </div>

    <!-- Layout: categories left + products right -->
    <div class="flex bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl overflow-hidden shadow-sm">

      <!-- Left: Categories list -->
      <div class="w-[180px] md:w-[200px] xl:w-[220px] shrink-0 border-r border-zinc-100 dark:border-zinc-800">
        <!-- Header -->
        <div class="bg-[#00a046] px-4 py-3">
          <span class="text-white font-extrabold text-[11px] uppercase tracking-widest">Категорії</span>
        </div>

        <!-- Loading skeleton -->
        <div v-if="isLoadingCats" class="p-3 space-y-1.5">
          <div v-for="i in 8" :key="i" class="h-8 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
        </div>

        <nav v-else class="overflow-y-auto cat-scroll" style="max-height: 440px">
          <button
            v-for="cat in categories"
            :key="cat.slug"
            class="w-full flex items-center gap-2.5 px-3 py-2.5 text-left transition-colors group/cat"
            :class="
              selectedSlug === cat.slug
                ? 'bg-[#f0faf6] dark:bg-zinc-800 text-[#00a046] border-r-2 border-[#00a046]'
                : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 hover:text-[#00a046]'
            "
            @click="selectCategory(cat.slug)"
          >
            <span
              class="material-symbols-outlined text-[15px] shrink-0 transition-colors"
              :class="selectedSlug === cat.slug ? 'text-[#00a046]' : 'text-zinc-400 group-hover/cat:text-[#00a046]'"
            >
              {{ getCategoryIcon(cat.slug) }}
            </span>
            <span class="text-xs font-semibold line-clamp-1 flex-1">{{ getCatName(cat) }}</span>
            <span
              class="material-symbols-outlined text-[12px] shrink-0 transition-all"
              :class="selectedSlug === cat.slug ? 'opacity-100 text-[#00a046]' : 'opacity-0 group-hover/cat:opacity-75'"
            >
              chevron_right
            </span>
          </button>
        </nav>
      </div>

      <!-- Right: Products grid -->
      <div class="flex-1 min-w-0 p-4 md:p-5">
        <!-- Loading skeleton -->
        <div v-if="isLoadingProds" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
          <div v-for="i in 8" :key="i">
            <div class="aspect-square bg-zinc-100 dark:bg-zinc-800 animate-pulse rounded-xl mb-3" />
            <div class="h-2.5 bg-zinc-100 dark:bg-zinc-800 animate-pulse rounded mb-2 w-4/5" />
            <div class="h-2.5 bg-zinc-100 dark:bg-zinc-800 animate-pulse rounded w-1/2" />
          </div>
        </div>

        <!-- Products grid -->
        <div
          v-else-if="products.length > 0"
          class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4"
        >
          <div
            v-for="prod in products"
            :key="prod.id"
            class="group/card relative flex flex-col rounded-xl border border-zinc-100 dark:border-zinc-800 hover:border-zinc-200 dark:hover:border-zinc-700 hover:shadow-md transition-all duration-300 overflow-hidden bg-zinc-50/50 dark:bg-zinc-950/30"
          >
            <!-- Wishlist button -->
            <button
              class="absolute top-2 right-2 z-10 w-7 h-7 rounded-full bg-white/90 dark:bg-zinc-800/90 shadow flex items-center justify-center text-zinc-400 hover:text-rose-500 transition-colors opacity-0 group-hover/card:opacity-100"
              @click.stop="cartStore.toggleWishlist(prod as any)"
            >
              <span
                class="material-symbols-outlined text-[15px]"
                :class="{ 'text-rose-500': cartStore.isInWishlist(prod.id as any) }"
                :style="cartStore.isInWishlist(prod.id as any) ? 'font-variation-settings: \'FILL\' 1' : ''"
              >favorite</span>
            </button>

            <!-- Image -->
            <router-link
              :to="{ name: 'product-detail', params: { id: prod.slug } }"
              class="block p-3 aspect-square bg-white dark:bg-zinc-900 flex items-center justify-center overflow-hidden"
            >
              <img
                :src="prod.image"
                :alt="prod.name"
                class="w-full h-full object-contain group-hover/card:scale-105 transition-transform duration-500"
              />
            </router-link>

            <!-- Details -->
            <div class="flex flex-col flex-1 p-3 gap-2">
              <router-link
                :to="{ name: 'product-detail', params: { id: prod.slug } }"
                class="text-xs font-bold text-zinc-700 dark:text-zinc-200 line-clamp-2 leading-snug hover:text-[#00a046] transition-colors min-h-[32px]"
              >
                {{ prod.name }}
              </router-link>

              <div class="mt-auto flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                <span class="font-black text-sm text-[#00a046]">{{ formatPrice(prod.price) }}</span>
                <span v-if="prod.oldPrice" class="text-[10px] text-zinc-400 line-through">{{ formatPrice(prod.oldPrice) }}</span>
              </div>

              <button
                class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-1.5 rounded-lg text-[11px] font-extrabold transition-colors flex items-center justify-center gap-1 mt-1"
                @click="cartStore.addToCart(prod as any)"
              >
                <span class="material-symbols-outlined text-[14px]">shopping_cart</span>
                В кошик
              </button>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div
          v-else
          class="flex flex-col items-center justify-center py-20 text-zinc-400"
        >
          <span class="material-symbols-outlined text-5xl mb-3">inventory_2</span>
          <p class="text-sm font-bold">Товарів не знайдено</p>
          <p class="text-xs mt-1">Оберіть іншу категорію</p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.cat-scroll::-webkit-scrollbar {
  width: 3px;
}
.cat-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.cat-scroll::-webkit-scrollbar-thumb {
  background: #e4e4e7;
  border-radius: 2px;
}
.dark .cat-scroll::-webkit-scrollbar-thumb {
  background: #3f3f46;
}
</style>
