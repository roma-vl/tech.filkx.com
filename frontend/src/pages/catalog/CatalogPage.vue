<template>
  <!-- Breadcrumbs -->
  <nav
    class="max-w-container-max mx-auto px-4 md:px-8 pt-6 flex items-center flex-wrap gap-1.5 text-xs font-sans text-zinc-400 dark:text-zinc-500"
  >
    <router-link
      :to="{ name: 'home' }"
      class="hover:text-[#00a046] transition-colors flex items-center gap-1 font-semibold"
    >
      <span class="material-symbols-outlined text-[15px]">home</span>
      {{ t("catalog.breadcrumbs.home") }}
    </router-link>
    <template v-if="currentCategoryPath.length">
      <template
        v-for="(cat, idx) in currentCategoryPath"
        :key="cat.slug || cat.id"
      >
        <span
          class="material-symbols-outlined text-[13px] text-zinc-300 dark:text-zinc-700"
          >chevron_right</span
        >
        <router-link
          v-if="idx < currentCategoryPath.length - 1"
          :to="{ name: 'category', params: { slug: cat.slug } }"
          class="hover:text-[#00a046] transition-colors font-semibold"
        >
          {{ pickLocalized(cat.name, locale) }}
        </router-link>
        <span v-else class="text-zinc-800 dark:text-zinc-200 font-bold">
          {{ pickLocalized(cat.name, locale) }}
        </span>
      </template>
    </template>
    <template v-else>
      <span
        class="material-symbols-outlined text-[13px] text-zinc-300 dark:text-zinc-700"
        >chevron_right</span
      >
      <span class="text-zinc-800 dark:text-zinc-200 font-bold">{{
        t("catalog.breadcrumbs.catalog")
      }}</span>
    </template>
  </nav>

  <!-- Category Quick Nav -->
  <CatalogCategoryNav
    :selected-category="categorySlug"
    :current-category-path="currentCategoryPath"
    @select-category="selectCategory"
  />

  <!-- Toolbar -->
  <div
    class="max-w-container-max mx-auto px-4 md:px-8 pb-4 flex items-center gap-3 flex-wrap font-sans"
  >
    <!-- Products count -->
    <span
      class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 mr-auto hidden sm:block"
    >
      <template v-if="!isLoading">
        {{ t("catalog.toolbar.resultsCount", { count: pagination.total }) }}
      </template>
    </span>

    <!-- View Toggle: 4-per-row grid / 5-per-row grid / list - grid density and
         list-vs-grid are both meaningless on a single-column mobile layout,
         so this only shows once there's room for it to matter. -->
    <div
      class="hidden sm:flex items-center bg-zinc-100 dark:bg-zinc-800 rounded-md p-1 gap-0.5"
    >
      <button
        :class="
          viewMode === 'grid' && gridDensity === 4
            ? 'bg-white dark:bg-zinc-700 shadow text-[#00a046]'
            : 'text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'
        "
        class="w-8 h-8 rounded-md flex items-center justify-center transition-all"
        :title="t('catalog.toolbar.grid4Title')"
        @click="
          viewMode = 'grid';
          gridDensity = 4;
        "
      >
        <span class="material-symbols-outlined text-[18px]">view_comfy</span>
      </button>
      <button
        :class="
          viewMode === 'grid' && gridDensity === 5
            ? 'bg-white dark:bg-zinc-700 shadow text-[#00a046]'
            : 'text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'
        "
        class="w-8 h-8 rounded-md flex items-center justify-center transition-all"
        :title="t('catalog.toolbar.grid5Title')"
        @click="
          viewMode = 'grid';
          gridDensity = 5;
        "
      >
        <span class="material-symbols-outlined text-[18px]">grid_on</span>
      </button>
      <button
        :class="
          viewMode === 'list'
            ? 'bg-white dark:bg-zinc-700 shadow text-[#00a046]'
            : 'text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'
        "
        class="w-8 h-8 rounded-md flex items-center justify-center transition-all"
        :title="t('catalog.toolbar.listViewTitle')"
        @click="viewMode = 'list'"
      >
        <span class="material-symbols-outlined text-[18px]">view_list</span>
      </button>
    </div>

    <!-- Sort -->
    <UiDropdown v-model="sortBy" :options="sortOptions" align-right />

    <!-- Mobile Filter Button -->
    <UiButton
      variant="secondary"
      size="sm"
      class="lg:hidden relative"
      @click="isMobileFilterOpen = true"
    >
      <span class="material-symbols-outlined text-[18px]">tune</span>
      {{ t("catalog.filters.title") }}
      <span
        v-if="activeFilters.length"
        class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-[#00a046] text-white text-[10px] font-black rounded-full flex items-center justify-center"
        >{{ activeFilters.length }}</span
      >
    </UiButton>
  </div>

  <!-- Main Catalog Layout -->
  <main
    class="max-w-container-max mx-auto px-4 md:px-8 py-5 flex gap-6 font-sans"
  >
    <!-- Sidebar (Desktop) -->
    <aside class="hidden lg:block w-80 flex-shrink-0">
      <div class="sticky top-24">
        <CatalogFiltersWidget
          v-model:price-min="priceMin"
          v-model:price-max="priceMax"
          v-model:selected-brands="selectedBrands"
          v-model:selected-attrs="selectedAttrs"
          v-model:selected-rating="selectedRating"
          v-model:only-discounts="onlyDiscounts"
          v-model:only-in-stock="onlyInStock"
          :initial-price-min="initialPriceMin"
          :initial-price-max="initialPriceMax"
          :products="rawProducts"
          :brands="brands"
          :dynamic-attributes="dynamicAttributes"
          :categories-list="categoriesList"
          :selected-category="categorySlug"
          @select-category="selectCategory"
          @clear-filters="clearFilters"
        />
      </div>
    </aside>

    <!-- Products Workspace -->
    <section class="flex-1 min-w-0">
      <!-- Active Filters Chips -->
      <div
        v-if="activeFilters.length"
        class="flex flex-wrap gap-2 items-center mb-4 p-3 bg-zinc-50 dark:bg-zinc-900 rounded-md border border-zinc-100 dark:border-zinc-800"
      >
        <button
          v-for="filter in activeFilters"
          :key="`${filter.type}-${filter.label}`"
          class="flex items-center gap-1 bg-white dark:bg-zinc-800 border border-[#00a046]/20 text-[#00a046] px-2.5 py-1 rounded-md text-xs font-bold hover:bg-[#00a046]/5 transition-all"
          @click="removeFilter(filter)"
        >
          {{ filter.label }}
          <span class="material-symbols-outlined text-[11px]">close</span>
        </button>
        <UiButton
          variant="ghost"
          size="sm"
          class="ml-auto !text-zinc-400 hover:!text-rose-500"
          @click="clearFilters"
        >
          <span class="material-symbols-outlined text-[14px]"
            >filter_list_off</span
          >
          {{ t("catalog.activeFilters.clearAll") }}
        </UiButton>
      </div>

      <!-- Loading Skeleton -->
      <div
        v-if="isLoading"
        :class="viewMode === 'grid' ? gridClass : 'flex flex-col gap-4'"
      >
        <div
          v-for="i in 9"
          :key="i"
          :class="
            viewMode === 'grid'
              ? 'border border-zinc-200 dark:border-zinc-800'
              : 'rounded-md border border-zinc-100 dark:border-zinc-800'
          "
          class="animate-pulse bg-white dark:bg-zinc-900 overflow-hidden"
        >
          <div class="aspect-[1.15/1] bg-zinc-100 dark:bg-zinc-800" />
          <div class="p-5 space-y-3">
            <div class="flex justify-between">
              <div class="h-3 w-16 bg-zinc-100 dark:bg-zinc-800 rounded" />
              <div class="h-3 w-20 bg-zinc-100 dark:bg-zinc-800 rounded" />
            </div>
            <div class="h-4 bg-zinc-100 dark:bg-zinc-800 rounded" />
            <div class="h-4 w-3/4 bg-zinc-100 dark:bg-zinc-800 rounded" />
            <div class="flex gap-2">
              <div class="h-6 w-16 bg-zinc-100 dark:bg-zinc-800 rounded" />
              <div class="h-6 w-16 bg-zinc-100 dark:bg-zinc-800 rounded" />
              <div class="h-6 w-16 bg-zinc-100 dark:bg-zinc-800 rounded" />
            </div>
            <div
              class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center"
            >
              <div class="h-7 w-28 bg-zinc-100 dark:bg-zinc-800 rounded" />
              <div class="h-9 w-28 bg-zinc-100 dark:bg-zinc-800 rounded" />
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid / List -->
      <div
        v-else-if="filteredProducts.length"
        :class="viewMode === 'grid' ? gridClass : 'flex flex-col gap-4'"
      >
        <ProductCard
          v-for="product in filteredProducts"
          :key="product.id"
          :product="product"
          :view-mode="viewMode"
        />
      </div>

      <!-- Empty State -->
      <div
        v-else
        class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-100 dark:border-zinc-800 p-16 text-center"
      >
        <div
          class="w-14 h-14 mx-auto mb-4 rounded-full bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center"
        >
          <span
            class="material-symbols-outlined text-[32px] text-zinc-300 dark:text-zinc-600"
            >search_off</span
          >
        </div>
        <h2 class="font-extrabold text-base text-zinc-900 dark:text-white mb-2">
          {{ t("catalog.empty.title") }}
        </h2>
        <p
          class="text-sm text-zinc-400 dark:text-zinc-500 mb-6 max-w-xs mx-auto"
        >
          {{ t("catalog.empty.description") }}
        </p>
        <UiButton @click="clearFilters">
          {{ t("catalog.empty.resetButton") }}
        </UiButton>
      </div>

      <!-- Infinite Scroll: loading-more skeleton (same card style as the
           initial-load skeleton above) plus the sentinel that triggers the
           next page fetch once it scrolls into view. -->
      <template v-if="!isLoading && filteredProducts.length">
        <div
          v-if="isLoadingMore"
          class="mt-4"
          :class="viewMode === 'grid' ? gridClass : 'flex flex-col gap-4'"
        >
          <div
            v-for="i in gridDensity"
            :key="i"
            :class="
              viewMode === 'grid'
                ? 'border border-zinc-200 dark:border-zinc-800'
                : 'rounded-md border border-zinc-100 dark:border-zinc-800'
            "
            class="animate-pulse bg-white dark:bg-zinc-900 overflow-hidden"
          >
            <div class="aspect-[1.15/1] bg-zinc-100 dark:bg-zinc-800" />
            <div class="p-5 space-y-3">
              <div class="flex justify-between">
                <div class="h-3 w-16 bg-zinc-100 dark:bg-zinc-800 rounded" />
                <div class="h-3 w-20 bg-zinc-100 dark:bg-zinc-800 rounded" />
              </div>
              <div class="h-4 bg-zinc-100 dark:bg-zinc-800 rounded" />
              <div class="h-4 w-3/4 bg-zinc-100 dark:bg-zinc-800 rounded" />
              <div class="flex gap-2">
                <div class="h-6 w-16 bg-zinc-100 dark:bg-zinc-800 rounded" />
                <div class="h-6 w-16 bg-zinc-100 dark:bg-zinc-800 rounded" />
                <div class="h-6 w-16 bg-zinc-100 dark:bg-zinc-800 rounded" />
              </div>
              <div
                class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center"
              >
                <div class="h-7 w-28 bg-zinc-100 dark:bg-zinc-800 rounded" />
                <div class="h-9 w-28 bg-zinc-100 dark:bg-zinc-800 rounded" />
              </div>
            </div>
          </div>
        </div>
        <div v-if="hasMore" ref="loadMoreSentinel" class="h-1 w-full" />
      </template>
    </section>
  </main>

  <!-- Mobile Filter Drawer -->
  <Teleport to="body">
    <div v-if="isMobileFilterOpen" class="fixed inset-0 z-50 flex lg:hidden">
      <!-- Backdrop -->
      <div
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        @click="isMobileFilterOpen = false"
      />
      <!-- Drawer -->
      <div
        class="relative bg-white dark:bg-zinc-900 w-72 max-w-[85vw] h-full shadow-2xl flex flex-col slide-in-left"
      >
        <!-- Header -->
        <div
          class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800"
        >
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-[#00a046]"
              >tune</span
            >
            <h2 class="font-extrabold text-base text-zinc-900 dark:text-white">
              {{ t("catalog.filters.title") }}
            </h2>
            <span
              v-if="activeFilters.length"
              class="bg-[#00a046] text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center"
              >{{ activeFilters.length }}</span
            >
          </div>
          <button
            class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors"
            @click="isMobileFilterOpen = false"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <!-- Filters -->
        <div class="flex-1 overflow-y-auto">
          <CatalogFiltersWidget
            v-model:price-min="priceMin"
            v-model:price-max="priceMax"
            v-model:selected-brands="selectedBrands"
            v-model:selected-attrs="selectedAttrs"
            v-model:selected-rating="selectedRating"
            v-model:only-discounts="onlyDiscounts"
            v-model:only-in-stock="onlyInStock"
            :initial-price-min="initialPriceMin"
            :initial-price-max="initialPriceMax"
            :products="rawProducts"
            :brands="brands"
            :dynamic-attributes="dynamicAttributes"
            :categories-list="categoriesList"
            :selected-category="categorySlug"
            @select-category="selectCategory"
            @clear-filters="clearFilters"
          />
        </div>

        <!-- Footer Buttons -->
        <div
          class="px-5 py-4 border-t border-zinc-100 dark:border-zinc-800 flex gap-3"
        >
          <UiButton variant="secondary" class="flex-1" @click="clearFilters">
            {{ t("catalog.filters.resetButton") }}
          </UiButton>
          <UiButton class="flex-1" @click="isMobileFilterOpen = false">
            {{ t("catalog.filters.applyButton") }}
          </UiButton>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, ref, watch, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { useHead } from "@vueuse/head";
import { useCatalog } from "@/features/catalog/composables/useCatalog";
import { pickLocalized } from "@/shared/utils/categoryMapper";
import CatalogFiltersWidget from "@/widgets/Catalog/CatalogFiltersWidget.vue";
import CatalogCategoryNav from "@/widgets/Catalog/CatalogCategoryNav.vue";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";
import UiButton from "@/shared/ui/UiButton.vue";
import UiDropdown from "@/shared/ui/UiDropdown.vue";

const { t, locale } = useI18n();

const {
  route,
  router,
  viewMode,
  gridDensity,
  sortBy,
  priceMin,
  priceMax,
  initialPriceMin,
  initialPriceMax,
  selectedBrands,
  selectedAttrs,
  selectedRating,
  onlyDiscounts,
  onlyInStock,
  isMobileFilterOpen,
  isLoading,
  isLoadingMore,
  rawProducts,
  categorySlug,
  categoriesList,
  brands,
  dynamicAttributes,
  pagination,
  selectCategory,
  loadMore,
  filteredProducts,
  activeFilters,
  removeFilter,
  clearFilters,
  currentCategoryName,
  currentCategoryPath,
} = useCatalog();

const pageTitle = computed(() =>
  categorySlug.value ? currentCategoryName.value : t("meta.catalogTitle"),
);

useHead({
  title: pageTitle,
  meta: computed(() => [
    { name: "description", content: t("meta.catalogDescription") },
    { property: "og:title", content: pageTitle.value },
    { property: "og:description", content: t("meta.catalogDescription") },
  ]),
});

// Tailwind's build-time scanner needs each class written out literally
// somewhere in the source, so the column count can't be interpolated
// directly into the class string (e.g. `lg:grid-cols-${n}`) - this lookup
// keeps every combination as a real string the scanner can find.
const densityColumnsClass: Record<number, string> = {
  4: "lg:grid-cols-4",
  5: "lg:grid-cols-5",
};

const gridClass = computed(
  () =>
    `grid grid-cols-1 sm:grid-cols-2 ${densityColumnsClass[gridDensity.value]}`,
);

const sortOptions = computed(() => [
  { value: "popularity", label: t("catalog.sort.byPopularity") },
  { value: "newest", label: t("catalog.sort.newest") },
  { value: "price-asc", label: t("catalog.sort.priceAsc") },
  { value: "price-desc", label: t("catalog.sort.priceDesc") },
]);

const hasMore = computed(
  () => pagination.value.page < pagination.value.lastPage,
);

// Infinite scroll: observes the sentinel rendered right after the product
// grid and asks the composable for the next page once it enters the
// viewport. The sentinel only exists in the DOM while hasMore is true (see
// template), so watching the template ref itself is enough to know when to
// (dis)connect - no separate "should we still be observing" bookkeeping.
const loadMoreSentinel = ref<HTMLElement | null>(null);
let sentinelObserver: IntersectionObserver | null = null;

watch(loadMoreSentinel, (el) => {
  sentinelObserver?.disconnect();
  sentinelObserver = null;
  if (!el) return;

  sentinelObserver = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) loadMore();
    },
    { rootMargin: "400px" },
  );
  sentinelObserver.observe(el);
});

onBeforeUnmount(() => {
  sentinelObserver?.disconnect();
});
</script>

<style scoped>
.slide-in-left {
  animation: slideInLeft 0.25s ease-out forwards;
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
