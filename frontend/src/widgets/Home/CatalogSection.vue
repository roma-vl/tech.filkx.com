<script setup lang="ts">
import { ref, computed, onMounted, onServerPrefetch, watch } from "vue";
import { useI18n } from "vue-i18n";
import { productApi } from "@/shared/services/api/productApi";
import {
  mapHomeProduct,
  type HomeProduct,
} from "@/entities/product/lib/mapHomeProduct";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});

const { locale, t } = useI18n();

// Kept raw so the grid re-translates immediately on language switch, without refetching.
const rawBestsellers = ref<any[]>([]);
const bestsellers = computed(
  () =>
    rawBestsellers.value
      .map((p) => mapHomeProduct(p, locale.value))
      .filter(Boolean) as HomeProduct[],
);
const selectedSlug = ref("");
const isLoadingProds = ref(false);
const page = ref(1);
const lastPage = ref(1);

const categoryName = (cat: any) =>
  cat.name?.[locale.value] || cat.name?.uk || cat.name?.en || cat.name || "";

const fetchProducts = async () => {
  if (!selectedSlug.value) return;
  isLoadingProds.value = true;
  try {
    const res = await productApi.catalogGetProducts({
      category: selectedSlug.value,
      per_page: 10,
      page: page.value,
    });
    if (res.data?.success || res.data?.status === "success") {
      rawBestsellers.value = res.data?.data?.data || res.data?.data || [];
      lastPage.value = res.data?.data?.lastPage || 1;
    }
  } catch (e) {
    console.error("CatalogSection: load products failed:", e);
  } finally {
    isLoadingProds.value = false;
  }
};

const selectCategory = (slug: string) => {
  if (isLoadingProds.value || slug === selectedSlug.value) return;
  selectedSlug.value = slug;
  page.value = 1;
  rawBestsellers.value = [];
  fetchProducts();
};

const prevPage = () => {
  if (isLoadingProds.value || page.value <= 1) return;
  page.value -= 1;
  fetchProducts();
};

const nextPage = () => {
  if (isLoadingProds.value || page.value >= lastPage.value) return;
  page.value += 1;
  fetchProducts();
};

watch(
  () => props.categories,
  (newCats) => {
    if (newCats && newCats.length > 0 && !selectedSlug.value) {
      const firstCat = newCats[0] as any;
      selectCategory(firstCat.slug);
    }
  },
  { immediate: true },
);

onMounted(() => {
  if (props.categories.length > 0 && !selectedSlug.value) {
    const firstCat = props.categories[0] as any;
    selectCategory(firstCat.slug);
  }
});

// Prerendering has no DOM, so onMounted never runs — fetch the first
// category's bestsellers here so the static build captures real content.
// The immediate watch() above already fires synchronously during setup
// (props.categories is populated by the time this component is created,
// since the parent's own onServerPrefetch resolves before Vue descends
// into its subtree) and kicks off an untracked fetchProducts() call — so
// selectedSlug may already be set here. Fetch again regardless and await
// it directly, rather than relying on that untracked call, since only a
// promise awaited from inside onServerPrefetch is guaranteed to finish
// before the page is serialized.
onServerPrefetch(async () => {
  if (props.categories.length > 0) {
    if (!selectedSlug.value)
      selectedSlug.value = (props.categories[0] as any).slug;
    await fetchProducts();
  }
});
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-8 font-sans">
    <!-- Header -->
    <div class="flex items-center justify-between mb-5">
      <h2
        class="font-extrabold text-2xl text-zinc-900 dark:text-white tracking-tight"
      >
        {{ t("home.catalogSection.title") }}
      </h2>
      <div class="flex items-center gap-2">
        <button
          class="w-8 h-8 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors disabled:opacity-30 disabled:pointer-events-none"
          :disabled="page <= 1 || isLoadingProds"
          :aria-label="t('home.catalogSection.prevPage')"
          @click="prevPage"
        >
          <span class="material-symbols-outlined text-sm">chevron_left</span>
        </button>
        <button
          class="w-8 h-8 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors disabled:opacity-30 disabled:pointer-events-none"
          :disabled="page >= lastPage || isLoadingProds"
          :aria-label="t('home.catalogSection.nextPage')"
          @click="nextPage"
        >
          <span class="material-symbols-outlined text-sm">chevron_right</span>
        </button>
      </div>
    </div>

    <!-- Category Pill Chips -->
    <div class="flex items-center gap-2 overflow-x-auto pb-5 no-scrollbar">
      <button
        v-for="cat in categories as any[]"
        :key="cat.slug"
        class="px-5 py-2 rounded-full text-xs font-bold transition-all duration-200 border whitespace-nowrap"
        :class="
          selectedSlug === cat.slug
            ? 'bg-[#00a046] text-white border-transparent shadow-md shadow-[#00a046]/10'
            : 'bg-[#23242e] dark:bg-[#1a1b24] hover:bg-[#2e2f3d] dark:hover:bg-zinc-800/80 text-zinc-300 border-zinc-800'
        "
        @click="selectCategory(cat.slug)"
      >
        {{ categoryName(cat) }}
      </button>
    </div>

    <!-- Products Grid -->
    <div
      v-if="isLoadingProds"
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"
    >
      <div
        v-for="i in 5"
        :key="i"
        class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-4 space-y-4 animate-pulse"
      >
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

    <div
      v-else-if="bestsellers.length > 0"
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 border-t border-l border-zinc-200 dark:border-zinc-800 animate-in fade-in duration-300"
    >
      <ProductCard
        v-for="prod in bestsellers"
        :key="prod.id"
        :product="prod"
        view-mode="grid"
      />
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="flex flex-col items-center justify-center py-16 text-zinc-500 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 shadow-sm"
    >
      <span
        class="material-symbols-outlined text-5xl mb-3 text-zinc-400 dark:text-zinc-650"
        >inventory_2</span
      >
      <p class="text-sm font-bold">{{ t("home.catalogSection.empty") }}</p>
    </div>
  </section>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
