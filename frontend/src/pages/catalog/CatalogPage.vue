<template>
  <!-- Breadcrumbs -->
  <nav class="max-w-container-max mx-auto px-4 md:px-8 pt-6 flex items-center gap-1.5 text-xs font-sans text-zinc-400 dark:text-zinc-500">
    <router-link
      :to="{ name: 'home' }"
      class="hover:text-[#00a046] transition-colors flex items-center gap-1 font-semibold"
    >
      <span class="material-symbols-outlined text-[15px]">home</span>
      Головна
    </router-link>
    <span class="material-symbols-outlined text-[13px] text-zinc-300 dark:text-zinc-700">chevron_right</span>
    <router-link :to="{ name: 'catalog' }" class="hover:text-[#00a046] transition-colors font-semibold">
      Каталог
    </router-link>
    <template v-if="route.query.category">
      <span class="material-symbols-outlined text-[13px] text-zinc-300 dark:text-zinc-700">chevron_right</span>
      <span class="text-zinc-800 dark:text-zinc-200 font-bold">{{ currentCategoryName }}</span>
    </template>
  </nav>

  <!-- Page Header -->
  <header class="max-w-container-max mx-auto px-4 md:px-8 pt-4 pb-2 font-sans flex items-end justify-between">
    <div>
      <h1 class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight">
        {{ currentCategoryName }}
      </h1>
      <p class="text-sm text-zinc-400 dark:text-zinc-500 mt-1">
        <template v-if="isLoading">Завантаження...</template>
        <template v-else>{{ pagination.total }} товарів</template>
      </p>
    </div>
  </header>

  <!-- Main Catalog Layout -->
  <main class="max-w-container-max mx-auto px-4 md:px-8 py-5 flex gap-6 font-sans">
    <!-- Sidebar (Desktop) -->
    <aside class="hidden lg:block w-64 flex-shrink-0">
      <div class="sticky top-24">
        <CatalogFiltersWidget
          v-model:price-min="priceMin"
          v-model:price-max="priceMax"
          v-model:selected-brands="selectedBrands"
          v-model:selected-attrs="selectedAttrs"
          v-model:selected-rating="selectedRating"
          v-model:only-discounts="onlyDiscounts"
          v-model:only-in-stock="onlyInStock"
          :products="rawProducts"
          :brands="brands"
          :dynamic-attributes="dynamicAttributes"
          :categories-list="categoriesList"
          :selected-category="(route.query.category as string) || ''"
          @select-category="selectCategory"
          @clear-filters="clearFilters"
        />
      </div>
    </aside>

    <!-- Products Workspace -->
    <section class="flex-1 min-w-0">

      <!-- Toolbar -->
      <div class="flex items-center gap-3 mb-4 flex-wrap">
        <!-- Products count -->
        <span class="text-sm font-semibold text-zinc-500 dark:text-zinc-400 mr-auto hidden sm:block">
          <template v-if="!isLoading">
            Показано {{ filteredProducts.length }} з {{ pagination.total }}
          </template>
        </span>

        <!-- View Toggle -->
        <div class="flex items-center bg-zinc-100 dark:bg-zinc-800 rounded-lg p-1 gap-0.5">
          <button
            :class="viewMode === 'grid' ? 'bg-white dark:bg-zinc-700 shadow text-[#00a046]' : 'text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'"
            class="w-8 h-8 rounded-md flex items-center justify-center transition-all"
            title="Сітка"
            @click="viewMode = 'grid'"
          >
            <span class="material-symbols-outlined text-[18px]">grid_view</span>
          </button>
          <button
            :class="viewMode === 'list' ? 'bg-white dark:bg-zinc-700 shadow text-[#00a046]' : 'text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'"
            class="w-8 h-8 rounded-md flex items-center justify-center transition-all"
            title="Список"
            @click="viewMode = 'list'"
          >
            <span class="material-symbols-outlined text-[18px]">view_list</span>
          </button>
        </div>

        <!-- Sort -->
        <UiSelect v-model="sortBy" :options="sortOptions" class="w-52" />

        <!-- Mobile Filter Button -->
        <UiButton
          variant="secondary"
          size="sm"
          class="lg:hidden relative"
          @click="isMobileFilterOpen = true"
        >
          <span class="material-symbols-outlined text-[18px]">tune</span>
          Фільтри
          <span
            v-if="activeFilters.length"
            class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-[#00a046] text-white text-[10px] font-black rounded-full flex items-center justify-center"
          >{{ activeFilters.length }}</span>
        </UiButton>
      </div>

      <!-- Active Filters Chips -->
      <div v-if="activeFilters.length" class="flex flex-wrap gap-2 items-center mb-4 p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
        <button
          v-for="filter in activeFilters"
          :key="`${filter.type}-${filter.label}`"
          class="flex items-center gap-1 bg-white dark:bg-zinc-800 border border-[#00a046]/20 text-[#00a046] px-2.5 py-1 rounded-full text-xs font-bold hover:bg-[#00a046]/5 transition-all"
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
          <span class="material-symbols-outlined text-[14px]">filter_list_off</span>
          Скинути все
        </UiButton>
      </div>

      <!-- Loading Skeleton -->
      <div
        v-if="isLoading"
        :class="viewMode === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5' : 'flex flex-col gap-4'"
      >
        <div
          v-for="i in 9"
          :key="i"
          class="animate-pulse bg-white dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800 overflow-hidden"
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
            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-between items-center">
              <div class="h-7 w-28 bg-zinc-100 dark:bg-zinc-800 rounded" />
              <div class="h-9 w-28 bg-zinc-100 dark:bg-zinc-800 rounded" />
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid / List -->
      <div
        v-else-if="filteredProducts.length"
        :class="viewMode === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5' : 'flex flex-col gap-4'"
      >
        <ProductCard
          v-for="product in filteredProducts"
          :key="product.id"
          :product="product"
          :view-mode="viewMode"
          @quick-view="openQuickView"
        />
      </div>

      <!-- Empty State -->
      <div
        v-else
        class="bg-white dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800 p-16 text-center"
      >
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center">
          <span class="material-symbols-outlined text-[32px] text-zinc-300 dark:text-zinc-600">search_off</span>
        </div>
        <h2 class="font-extrabold text-base text-zinc-900 dark:text-white mb-2">
          Товари не знайдено
        </h2>
        <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-6 max-w-xs mx-auto">
          Спробуйте змінити фільтри або скинути пошук
        </p>
        <UiButton @click="clearFilters">Скинути фільтри</UiButton>
      </div>

      <!-- Pagination -->
      <nav
        v-if="!isLoading && pagination.lastPage > 1"
        class="mt-10 flex items-center justify-center gap-1"
      >
        <button
          :disabled="pagination.page === 1"
          :class="pagination.page === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-[#00a046]'"
          class="w-9 h-9 flex items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 transition-all"
          @click="changePage(pagination.page - 1)"
        >
          <span class="material-symbols-outlined text-[18px]">chevron_left</span>
        </button>

        <template v-for="p in paginationPages" :key="p">
          <span
            v-if="p === '...'"
            class="w-9 h-9 flex items-center justify-center text-zinc-400 text-sm"
          >…</span>
          <button
            v-else
            :class="pagination.page === p ? 'bg-[#00a046] text-white border-[#00a046] shadow-sm' : 'text-zinc-600 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-600'"
            class="w-9 h-9 flex items-center justify-center rounded-lg border font-bold text-sm transition-all"
            @click="changePage(p as number)"
          >{{ p }}</button>
        </template>

        <button
          :disabled="pagination.page === pagination.lastPage"
          :class="pagination.page === pagination.lastPage ? 'opacity-40 cursor-not-allowed' : 'hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-[#00a046]'"
          class="w-9 h-9 flex items-center justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 transition-all"
          @click="changePage(pagination.page + 1)"
        >
          <span class="material-symbols-outlined text-[18px]">chevron_right</span>
        </button>
      </nav>
    </section>
  </main>

  <!-- Mobile Filter Drawer -->
  <Teleport to="body">
    <div
      v-if="isMobileFilterOpen"
      class="fixed inset-0 z-50 flex lg:hidden"
    >
      <!-- Backdrop -->
      <div
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        @click="isMobileFilterOpen = false"
      />
      <!-- Drawer -->
      <div class="relative bg-white dark:bg-zinc-900 w-72 max-w-[85vw] h-full shadow-2xl flex flex-col slide-in-left">
        <!-- Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-[#00a046]">tune</span>
            <h2 class="font-extrabold text-base text-zinc-900 dark:text-white">Фільтри</h2>
            <span
              v-if="activeFilters.length"
              class="bg-[#00a046] text-white text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center"
            >{{ activeFilters.length }}</span>
          </div>
          <button class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition-colors" @click="isMobileFilterOpen = false">
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
            :products="rawProducts"
            :brands="brands"
            :dynamic-attributes="dynamicAttributes"
            :categories-list="categoriesList"
            :selected-category="(route.query.category as string) || ''"
            @select-category="selectCategory"
            @clear-filters="clearFilters"
          />
        </div>

        <!-- Footer Buttons -->
        <div class="px-5 py-4 border-t border-zinc-100 dark:border-zinc-800 flex gap-3">
          <UiButton variant="secondary" class="flex-1" @click="clearFilters">Скинути</UiButton>
          <UiButton class="flex-1" @click="isMobileFilterOpen = false">Застосувати</UiButton>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Quick View Modal -->
  <QuickViewModal
    :is-open="isQuickViewOpen"
    :product="selectedProductForQuickView"
    @close="closeQuickView"
  />
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useCatalog } from "@/features/catalog/composables/useCatalog";
import CatalogFiltersWidget from "@/widgets/Catalog/CatalogFiltersWidget.vue";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";
import QuickViewModal from "@/widgets/Catalog/QuickViewModal.vue";
import UiButton from "@/shared/ui/UiButton.vue";
import UiSelect from "@/shared/ui/UiSelect.vue";

const {
  route,
  router,
  viewMode,
  sortBy,
  priceMin,
  priceMax,
  selectedBrands,
  selectedAttrs,
  selectedRating,
  onlyDiscounts,
  onlyInStock,
  isMobileFilterOpen,
  selectedProductForQuickView,
  isQuickViewOpen,
  isLoading,
  rawProducts,
  categoriesList,
  brands,
  dynamicAttributes,
  pagination,
  selectCategory,
  changePage,
  filteredProducts,
  activeFilters,
  removeFilter,
  clearFilters,
  openQuickView,
  closeQuickView,
  currentCategoryName,
} = useCatalog();

const sortOptions = [
  { value: "popularity", label: "За популярністю" },
  { value: "newest", label: "Новинки" },
  { value: "price-asc", label: "Ціна: дешевші спочатку" },
  { value: "price-desc", label: "Ціна: дорожчі спочатку" },
];

const paginationPages = computed(() => {
  const total = pagination.value.lastPage;
  const current = pagination.value.page;
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);

  const show = new Set([1, total, current, current - 1, current + 1].filter((p) => p >= 1 && p <= total));
  const sorted = [...show].sort((a, b) => a - b);

  const result: (number | string)[] = [];
  for (let i = 0; i < sorted.length; i++) {
    if (i > 0 && sorted[i] - sorted[i - 1] > 1) result.push("...");
    result.push(sorted[i]);
  }
  return result;
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
