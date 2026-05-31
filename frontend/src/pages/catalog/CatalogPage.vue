<template>
  <!-- Main Catalog Area -->
  <main
    class="max-w-container-max mx-auto px-4 md:px-8 py-8 flex gap-8 font-sans text-zinc-800 dark:text-zinc-200"
  >
    <!-- Sidebar Filters (Desktop) -->
    <aside class="hidden lg:block w-72 flex-shrink-0">
      <div class="sticky top-24 space-y-6">
        <!-- Breadcrumbs -->
        <nav
          class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500 mb-4 font-bold"
        >
          <a
            class="hover:text-[#00a046] transition-colors"
            href="#"
            @click.prevent="router.push('/')"
          >Головна</a>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <a
            class="hover:text-[#00a046] transition-colors"
            href="#"
            @click.prevent="selectCategory('')"
          >Каталог</a>
          <span
            v-if="route.query.category"
            class="material-symbols-outlined text-[12px]"
          >chevron_right</span>
          <span
            v-if="route.query.category"
            class="text-zinc-800 dark:text-zinc-100 font-extrabold"
          >{{ currentCategoryName }}</span>
        </nav>

        <!-- Catalog Filters Component -->
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
      <!-- Top Action bar -->
      <div
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 p-4 mb-6 shadow-sm"
      >
        <div
          class="flex flex-col md:flex-row md:items-center justify-between gap-4"
        >
          <div>
            <h1
              class="font-extrabold text-xl md:text-2xl text-zinc-900 dark:text-white tracking-tight text-left font-bold"
            >
              {{ currentCategoryName }}
            </h1>
            <p class="text-xs text-zinc-400 dark:text-zinc-500 font-bold mt-1 text-left">
              Знайдено {{ pagination.total }} товарів
            </p>
          </div>
          <div class="flex items-center gap-3">
            <!-- View Mode toggle switcher -->
            <div
              class="flex items-center bg-zinc-50 dark:bg-zinc-800 rounded-lg p-0.5 border border-zinc-200 dark:border-zinc-700 mr-1.5"
            >
              <button
                :class="
                  viewMode === 'grid'
                    ? 'bg-white dark:bg-zinc-900 shadow-sm text-[#00a046]'
                    : 'text-zinc-400 dark:text-zinc-550 hover:text-zinc-800 dark:hover:text-zinc-200'
                "
                class="p-2 rounded-md transition-colors"
                title="Сітка"
                type="button"
                @click="viewMode = 'grid'"
              >
                <span class="material-symbols-outlined text-[18px]">grid_view</span>
              </button>
              <button
                :class="
                  viewMode === 'list'
                    ? 'bg-white dark:bg-zinc-900 shadow-sm text-[#00a046]'
                    : 'text-zinc-400 dark:text-zinc-550 hover:text-zinc-800 dark:hover:text-zinc-200'
                "
                class="p-2 rounded-md transition-colors"
                title="Список"
                type="button"
                @click="viewMode = 'list'"
              >
                <span class="material-symbols-outlined text-[18px]">view_list</span>
              </button>
            </div>

            <!-- Sorting Select Dropdown -->
            <div class="relative">
              <select
                v-model="sortBy"
                class="appearance-none bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg pl-3 pr-9 py-2.5 text-xs font-extrabold text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] w-48 cursor-pointer outline-none"
              >
                <option value="popularity">
                  За популярністю
                </option>
                <option value="newest">
                  Новинки
                </option>
                <option value="price-asc">
                  Ціна: від дешевих
                </option>
                <option value="price-desc">
                  Ціна: від дорогих
                </option>
              </select>
              <span
                class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-zinc-400 text-[18px]"
              >expand_more</span>
            </div>

            <!-- Mobile filter toggle button -->
            <button
              class="lg:hidden flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700"
              @click="isMobileFilterOpen = true"
            >
              <span class="material-symbols-outlined text-[18px]">filter_alt</span>
            </button>
          </div>
        </div>

        <!-- Active Applied Filters badges -->
        <div
          class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap gap-2 items-center"
        >
          <span
            class="text-[9px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mr-1"
          >Застосовано:</span>
          <template v-if="activeFilters.length">
            <button
              v-for="filter in activeFilters"
              :key="`${filter.type}-${filter.label}`"
              class="flex items-center gap-1 bg-[#00a046]/10 text-[#00a046] px-2.5 py-1 rounded-full text-[10px] hover:bg-[#00a046]/20 transition-all font-black border border-[#00a046]/20"
              type="button"
              @click="removeFilter(filter)"
            >
              {{ filter.label }}
              <span class="material-symbols-outlined text-[12px]">close</span>
            </button>
            <button
              class="text-[#00a046] hover:text-[#00b050] font-black text-[10px] ml-auto hover:underline flex items-center gap-1 uppercase tracking-wider"
              type="button"
              @click="clearFilters"
            >
              <span class="material-symbols-outlined text-[14px]">filter_list_off</span>
              Скинути все
            </button>
          </template>
          <span
            v-else
            class="text-[10px] text-zinc-400 dark:text-zinc-500 font-extrabold italic"
          >Фільтри не вибрано</span>
        </div>
      </div>

      <!-- Grid / List Products Display -->
      <div
        v-if="filteredProducts.length"
        :class="
          viewMode === 'grid'
            ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6'
            : 'flex flex-col gap-4'
        "
      >
        <ProductCard
          v-for="product in filteredProducts"
          :key="product.id"
          :product="product"
          :view-mode="viewMode"
          @quick-view="openQuickView"
        />
      </div>

      <!-- No products found placeholder -->
      <div
        v-else
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800 p-12 text-center shadow-sm"
      >
        <div
          class="w-16 h-16 mx-auto mb-4 rounded-full bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center text-zinc-400"
        >
          <span class="material-symbols-outlined text-[36px]">search_off</span>
        </div>
        <h2 class="font-extrabold text-zinc-900 dark:text-white mb-2 font-bold">
          Товари за вашим запитом не знайдені
        </h2>
        <p
          class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 mb-6 max-w-md mx-auto"
        >
          Спробуйте змінити значення цінового діапазону або приберіть зайві
          бренди чи параметри ОЗУ.
        </p>
        <button
          class="bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs py-2.5 px-6 rounded-lg transition-all font-bold"
          type="button"
          @click="clearFilters"
        >
          Скинути фільтри
        </button>
      </div>

      <!-- Pagination Block -->
      <nav
        v-if="pagination.lastPage > 1"
        class="mt-12 flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800 pt-6"
      >
        <button
          :disabled="pagination.page === 1"
          :class="
            pagination.page === 1
              ? 'opacity-50 cursor-not-allowed'
              : 'hover:text-[#00a046] hover:bg-zinc-50 dark:hover:bg-zinc-800'
          "
          class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-extrabold text-zinc-500 rounded-lg transition-all font-bold"
          @click="changePage(pagination.page - 1)"
        >
          <span class="material-symbols-outlined text-[16px]">arrow_back</span>
          НАЗАД
        </button>
        <div class="flex items-center gap-1">
          <button
            v-for="p in pagination.lastPage"
            :key="p"
            :class="
              pagination.page === p
                ? 'bg-[#00a046] text-white shadow-sm'
                : 'text-zinc-550 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-zinc-800 dark:hover:text-zinc-200'
            "
            class="w-8 h-8 flex items-center justify-center rounded-lg transition-all font-extrabold text-xs"
            @click="changePage(p)"
          >
            {{ p }}
          </button>
        </div>
        <button
          :disabled="pagination.page === pagination.lastPage"
          :class="
            pagination.page === pagination.lastPage
              ? 'opacity-50 cursor-not-allowed'
              : 'hover:text-[#00a046] hover:bg-zinc-50 dark:hover:bg-zinc-800'
          "
          class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-extrabold text-zinc-500 rounded-lg transition-all font-bold"
          @click="changePage(pagination.page + 1)"
        >
          ВПЕРЕД
          <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
        </button>
      </nav>
    </section>
  </main>

  <!-- Mobile Filters Bottom Sheet / Side Drawer Overlay -->
  <div
    v-if="isMobileFilterOpen"
    class="fixed inset-0 z-50 flex lg:hidden bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 w-80 max-w-[85vw] h-full shadow-2xl p-6 flex flex-col justify-between overflow-y-auto"
    >
      <div class="space-y-6">
        <div
          class="flex items-center justify-between border-b border-zinc-150 dark:border-zinc-800 pb-3"
        >
          <h2 class="font-extrabold text-base text-zinc-900 dark:text-white font-bold text-left">
            Фільтри товарів
          </h2>
          <button
            class="text-zinc-450 hover:text-zinc-600"
            @click="isMobileFilterOpen = false"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

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
      <div
        class="border-t border-zinc-150 dark:border-zinc-800 pt-4 mt-6 flex gap-3"
      >
        <button
          class="flex-1 border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 font-extrabold py-2.5 rounded-lg text-xs font-bold"
          @click="clearFilters"
        >
          Скинути
        </button>
        <button
          class="flex-1 bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold py-2.5 rounded-lg text-xs font-bold"
          @click="isMobileFilterOpen = false"
        >
          Показати
        </button>
      </div>
    </div>
  </div>

  <!-- Quick View Modal (For detailed product stats overview) -->
  <QuickViewModal
    :is-open="isQuickViewOpen"
    :product="selectedProductForQuickView"
    @close="closeQuickView"
  />
</template>

<script setup lang="ts">
import { useCatalog } from "@/features/catalog/composables/useCatalog";
import CatalogFiltersWidget from "@/widgets/Catalog/CatalogFiltersWidget.vue";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";
import QuickViewModal from "@/widgets/Catalog/QuickViewModal.vue";

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
