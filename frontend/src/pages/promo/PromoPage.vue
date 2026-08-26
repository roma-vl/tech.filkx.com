<template>
  <main class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <!-- Loading state -->
    <div v-if="loading" class="max-w-container-max mx-auto px-4 md:px-8 py-10">
      <div class="animate-pulse flex gap-6 items-start">
        <div class="hidden lg:block w-64 h-96 flex-shrink-0 bg-zinc-200 dark:bg-zinc-800" />
        <div class="flex-1 min-w-0 space-y-6">
          <div
            class="aspect-video md:aspect-auto md:h-64 bg-zinc-200 dark:bg-zinc-800 rounded-xl"
          />
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div
              v-for="i in 10"
              :key="i"
              class="h-72 bg-zinc-200 dark:bg-zinc-800 rounded-xl"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Not found -->
    <div
      v-else-if="!promoPage"
      class="max-w-4xl mx-auto px-4 py-32 text-center"
    >
      <span class="text-6xl mb-4 block">🏷️</span>
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
        {{ t("promoPage.notFound.title") }}
      </h1>
      <RouterLink
        :to="{ name: 'catalog' }"
        class="text-emerald-600 hover:underline"
      >
        {{ t("promoPage.notFound.backToCatalog") }}
      </RouterLink>
    </div>

    <!-- Content -->
    <template v-else>
      <div class="max-w-container-max mx-auto px-4 md:px-8 py-6 lg:py-10">
        <!-- Category strip (Mobile) - above everything, including the banner.
             Top level only (a horizontal strip has no room to nest). -->
        <div
          v-if="categoryTree.length > 1"
          class="lg:hidden mb-5 -mx-1 px-1 flex gap-2 overflow-x-auto hide-scrollbar"
        >
          <button
            :class="[
              'flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold transition-all',
              !selectedCategory
                ? 'bg-[#00a046] text-white shadow-sm'
                : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700/50',
            ]"
            @click="selectCategory('')"
          >
            {{ t("promoPage.categories.all") }}
            <span class="opacity-70">({{ rawProducts.length }})</span>
          </button>
          <button
            v-for="node in categoryTree"
            :key="node.slug"
            :class="[
              'flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold transition-all',
              selectedCategory === node.slug
                ? 'bg-[#00a046] text-white shadow-sm'
                : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700/50',
            ]"
            @click="selectCategory(node.slug)"
          >
            <span class="material-symbols-outlined text-[15px]">{{
              getCategoryIcon(node.slug)
            }}</span>
            {{ pickLocalized(node.name, locale) }}
            <span class="opacity-70">({{ node.count }})</span>
          </button>
        </div>

        <div class="flex gap-6 items-start">
          <!-- Sidebar (Desktop) - starts from the very top of the page, level
               with the banner, not below it. First level gets a category
               icon; everything under it is a plain nested tree. -->
          <aside
            v-if="categoryTree.length > 1"
            class="hidden lg:block w-64 flex-shrink-0"
          >
            <div class="sticky top-24 bg-white dark:bg-zinc-900 p-5">
              <h3
                class="font-extrabold text-zinc-900 dark:text-white mb-4 text-xs uppercase tracking-wider"
              >
                {{ t("promoPage.categories.title") }}
              </h3>
              <button
                type="button"
                class="w-full flex items-center justify-between py-2 px-3 text-sm font-medium transition-all mb-1"
                :class="
                  !selectedCategory
                    ? 'bg-emerald-50 dark:bg-emerald-950/20 text-[#00a046] font-extrabold'
                    : 'text-zinc-650 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800'
                "
                @click="selectCategory('')"
              >
                <span>{{ t("promoPage.categories.all") }}</span>
                <span
                  class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 font-bold"
                  >{{ rawProducts.length }}</span
                >
              </button>
              <PromoCategoryTree
                :nodes="categoryTree"
                :selected-category="selectedCategory"
                :expanded-slugs="expandedSlugs"
                :locale="locale"
                @select="selectCategory"
                @toggle="toggleExpanded"
              />
            </div>
          </aside>

          <!-- Content column: banner sits only above the products, not
               full-page-width above the sidebar too -->
          <div class="flex-1 min-w-0 space-y-6">
            <section
              class="relative rounded-xl overflow-hidden aspect-video md:aspect-auto md:h-64"
            >
              <img
                class="absolute inset-0 w-full h-full object-cover opacity-60"
                :src="promoPage.imageUrl"
                :alt="promoPage.title"
              />
              <div
                class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/40 to-transparent"
              />
              <div
                class="relative z-10 h-full flex flex-col justify-center text-white px-6 md:px-10"
              >
                <div
                  v-if="promoPage.badge"
                  class="mb-3 inline-flex w-fit items-center bg-[#00a046] text-white font-bold uppercase tracking-wider px-3 py-1 text-[10px]"
                >
                  {{ promoPage.badge }}
                </div>
                <h1 class="font-extrabold text-2xl md:text-4xl mb-3 leading-tight">
                  {{ promoPage.title }}
                </h1>
                <p
                  v-if="promoPage.subtitle"
                  class="text-zinc-200 font-bold text-sm uppercase tracking-widest mb-2"
                >
                  {{ promoPage.subtitle }}
                </p>
                <p
                  v-if="promoPage.description"
                  class="text-sm md:text-[15px] text-zinc-300 max-w-2xl leading-relaxed"
                >
                  {{ promoPage.description }}
                </p>
              </div>
            </section>

            <section>
              <div
                v-if="products.length"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5"
              >
                <ProductCard
                  v-for="product in products"
                  :key="product.id"
                  :product="product"
                  view-mode="grid"
                />
              </div>
              <div
                v-else
                class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-100 dark:border-zinc-800 p-16 text-center text-zinc-500 dark:text-zinc-400"
              >
                {{ t("promoPage.empty") }}
              </div>
            </section>
          </div>
        </div>
      </div>
    </template>
  </main>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useHead } from "@vueuse/head";
import { productApi } from "@/shared/services/api/productApi";
import { mapCatalogProduct } from "@/entities/product/lib/mapCatalogProduct";
import {
  buildPromoCategoryTree,
  categoryPathSlugs,
  filterProductsByCategoryPath,
} from "@/entities/product/lib/derivePromoCategories";
import { pickLocalized, getCategoryIcon } from "@/shared/utils/categoryMapper";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";
import PromoCategoryTree from "@/widgets/Promo/PromoCategoryTree.vue";

const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n();

const loading = ref(true);
const promoPage = ref(null);
const selectedCategory = ref(route.query.category || "");
// The full site category tree (same one breadcrumbs/mega-menu use) - needed
// to resolve each promo product's leaf category up to its ancestors.
const fullCategoryTree = ref([]);
const expandedSlugs = ref(new Set());

const rawProducts = computed(() => promoPage.value?.products || []);

const categoryTree = computed(() =>
  buildPromoCategoryTree(rawProducts.value, fullCategoryTree.value),
);

const products = computed(() =>
  filterProductsByCategoryPath(
    rawProducts.value,
    selectedCategory.value,
    fullCategoryTree.value,
  )
    .map(mapCatalogProduct)
    .filter(Boolean),
);

const toggleExpanded = (slug) => {
  const next = new Set(expandedSlugs.value);
  if (next.has(slug)) {
    next.delete(slug);
  } else {
    next.add(slug);
  }
  expandedSlugs.value = next;
};

const selectCategory = (slug) => {
  selectedCategory.value = slug;
  // Reveal where the newly selected category actually sits in the tree
  // instead of leaving its ancestors collapsed.
  expandedSlugs.value = new Set(categoryPathSlugs(slug, fullCategoryTree.value));

  const query = { ...route.query };
  if (slug) {
    query.category = slug;
  } else {
    delete query.category;
  }
  router.replace({ query });
};

useHead({
  title: computed(
    () => promoPage.value?.title || t("promoPage.notFound.title"),
  ),
});

const fetchCategoryTree = async () => {
  try {
    const response = await productApi.catalogGetCategories();
    fullCategoryTree.value = response.data.data || [];
  } catch (e) {
    console.error("Failed to load categories for the promo page filter:", e);
  }
};

const fetchPromoPage = async () => {
  loading.value = true;
  promoPage.value = null;
  selectedCategory.value = route.query.category || "";
  try {
    const [{ data }] = await Promise.all([
      productApi.getPromoPage(route.params.slug),
      fullCategoryTree.value.length ? null : fetchCategoryTree(),
    ]);
    promoPage.value = data.data;
    expandedSlugs.value = new Set(
      categoryPathSlugs(selectedCategory.value, fullCategoryTree.value),
    );
  } catch (e) {
    console.error("Failed to load promo page:", e);
  } finally {
    loading.value = false;
  }
};

watch(() => route.params.slug, fetchPromoPage, { immediate: true });
</script>
