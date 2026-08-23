<template>
  <main class="flex-grow pb-8 bg-zinc-50/50 dark:bg-zinc-950/50">
    <template v-if="loading">
      <div class="max-w-container-max mx-auto px-4 md:px-8 py-6 space-y-6">
        <!-- Hero Skeleton -->
        <div class="space-y-3">
          <!-- Catalog button row skeleton -->
          <div class="hidden lg:flex items-center gap-3">
            <div
              class="h-10 w-[200px] bg-zinc-200 dark:bg-zinc-800 rounded-xl animate-pulse"
            />
            <div class="flex-1 flex gap-2">
              <div
                v-for="i in 6"
                :key="i"
                class="h-10 w-28 bg-zinc-200 dark:bg-zinc-800 rounded-lg animate-pulse shrink-0"
              />
            </div>
          </div>
          <!-- Slider skeleton -->
          <div class="flex gap-4 items-stretch">
            <div
              class="hidden lg:block w-[220px] xl:w-[240px] shrink-0 h-[380px] md:h-[480px] bg-zinc-200 dark:bg-zinc-800 rounded-xl animate-pulse"
            />
            <div
              class="flex-1 h-[380px] md:h-[480px] bg-zinc-200 dark:bg-zinc-800 rounded-2xl animate-pulse"
            />
          </div>
        </div>

        <!-- USP Skeleton -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="i in 4"
            :key="`usp-${i}`"
            class="h-20 bg-zinc-200 dark:bg-zinc-800 rounded-2xl animate-pulse"
          />
        </div>

        <!-- CatalogSection skeleton - mirrors its real category-pill row + single-row
             flat bordered grid (no sidebar, no rounded cards - that layout is stale) -->
        <div class="space-y-5">
          <div
            class="h-8 w-56 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse"
          />
          <div class="flex gap-2">
            <div
              v-for="i in 6"
              :key="`cat-pill-${i}`"
              class="h-9 w-28 bg-zinc-200 dark:bg-zinc-800 rounded-full animate-pulse shrink-0"
            />
          </div>
          <div
            class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
          >
            <div
              v-for="i in 5"
              :key="`bestseller-${i}`"
              class="flex flex-col border border-zinc-200 dark:border-zinc-800 p-4 space-y-2.5 animate-pulse"
            >
              <div class="aspect-square bg-zinc-200 dark:bg-zinc-800" />
              <div class="h-3 w-16 bg-zinc-200 dark:bg-zinc-800 rounded" />
              <div class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded w-full" />
              <div class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded w-5/6" />
              <div
                class="flex justify-between items-center pt-3 mt-auto border-t border-zinc-100 dark:border-zinc-800"
              >
                <div class="h-5 bg-zinc-200 dark:bg-zinc-800 rounded w-20" />
                <div class="flex gap-1.5">
                  <div
                    class="w-8 h-8 rounded-md bg-zinc-200 dark:bg-zinc-800"
                  />
                  <div
                    class="w-8 h-8 rounded-md bg-zinc-200 dark:bg-zinc-800"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Flash Deals Skeleton - mirrors its real flat bordered grid (4 items, 1 row) -->
        <div class="space-y-5">
          <div
            class="h-8 w-64 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse"
          />
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
            <div
              v-for="i in 4"
              :key="`deal-${i}`"
              class="flex flex-col border border-zinc-200 dark:border-zinc-800 p-4 space-y-2.5 animate-pulse"
            >
              <div class="aspect-square bg-zinc-200 dark:bg-zinc-800 mb-1" />
              <div class="h-3 w-16 bg-zinc-200 dark:bg-zinc-800 rounded" />
              <div class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded w-full" />
              <div class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded w-5/6" />
              <div class="h-6 bg-zinc-200 dark:bg-zinc-800 rounded w-24" />
              <div class="h-9 bg-zinc-200 dark:bg-zinc-800 rounded-lg" />
              <div class="h-9 bg-zinc-200 dark:bg-zinc-800 rounded-lg" />
            </div>
          </div>
        </div>

        <!-- Recommended Products Skeleton - mirrors its real single-row card strip -->
        <div class="space-y-5">
          <div
            class="h-8 w-64 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse"
          />
          <div class="flex overflow-hidden">
            <div
              v-for="i in 5"
              :key="`rec-${i}`"
              class="flex flex-col border border-zinc-200 dark:border-zinc-800 p-4 space-y-2.5 animate-pulse w-1/2 sm:w-1/3 lg:w-1/5 shrink-0"
            >
              <div class="aspect-square bg-zinc-200 dark:bg-zinc-800" />
              <div class="h-3 w-16 bg-zinc-200 dark:bg-zinc-800 rounded" />
              <div class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded w-full" />
              <div class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded w-5/6" />
              <div
                class="flex justify-between items-center pt-3 mt-auto border-t border-zinc-100 dark:border-zinc-800"
              >
                <div class="h-5 bg-zinc-200 dark:bg-zinc-800 rounded w-20" />
                <div class="flex gap-1.5">
                  <div
                    class="w-8 h-8 rounded-md bg-zinc-200 dark:bg-zinc-800"
                  />
                  <div
                    class="w-8 h-8 rounded-md bg-zinc-200 dark:bg-zinc-800"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <HeroSlider :categories="categories" :banners="banners" />
      <UspGrid />
      <CatalogSection :categories="popularCategories" />
      <FlashDeals
        v-if="flashDeals.length > 0"
        :products="flashDeals"
        @refresh-products="loadHomeData"
      />
      <RecommendedProducts
        v-if="recommended.length > 0"
        :products="recommended"
        :personalized="isRecommendedPersonalized"
      />
    </template>

    <TechBlog />
  </main>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useHead } from "@vueuse/head";
import { useI18n } from "vue-i18n";
import { useHome } from "@/features/home/composables/useHome";
import HeroSlider from "@/widgets/Home/HeroSlider.vue";
import UspGrid from "@/widgets/Home/UspGrid.vue";
import CatalogSection from "@/widgets/Home/CatalogSection.vue";
import FlashDeals from "@/widgets/Home/FlashDeals.vue";
import RecommendedProducts from "@/widgets/Home/RecommendedProducts.vue";
import TechBlog from "@/widgets/Home/TechBlog.vue";

const {
  banners,
  categories,
  popularCategories,
  flashDeals,
  recommended,
  isRecommendedPersonalized,
  loading,
  loadHomeData,
} = useHome();

const { t } = useI18n();

useHead({
  title: computed(() => t("meta.homeTitle")),
  meta: computed(() => [
    { name: "description", content: t("meta.homeDescription") },
    { property: "og:title", content: t("meta.homeTitle") },
    { property: "og:description", content: t("meta.homeDescription") },
  ]),
});
</script>
