<template>
  <main class="flex-grow pb-8 bg-zinc-50/50 dark:bg-zinc-950/50">
    <template v-if="loading">
      <div class="max-w-container-max mx-auto px-4 md:px-8 py-6 space-y-6">
        <!-- Hero Skeleton -->
        <div class="space-y-3">
          <!-- Catalog button row skeleton -->
          <div class="hidden lg:flex items-center gap-3">
            <div class="h-10 w-[200px] bg-zinc-200 dark:bg-zinc-800 rounded-xl animate-pulse" />
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
            <div class="hidden lg:block w-[220px] xl:w-[240px] shrink-0 h-[380px] md:h-[480px] bg-zinc-200 dark:bg-zinc-800 rounded-xl animate-pulse" />
            <div class="flex-1 h-[380px] md:h-[480px] bg-zinc-200 dark:bg-zinc-800 rounded-2xl animate-pulse" />
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

        <!-- CatalogSection skeleton -->
        <div class="space-y-4">
          <div class="h-8 w-56 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse" />
          <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
            <!-- Category chips row -->
            <div class="p-5 pb-4 border-b border-zinc-100 dark:border-zinc-800 flex gap-2">
              <div
                v-for="i in 10"
                :key="i"
                class="h-16 w-14 bg-zinc-100 dark:bg-zinc-800 rounded-xl animate-pulse shrink-0"
              />
            </div>
            <!-- Best sellers area -->
            <div class="flex">
              <div class="hidden md:block w-[200px] shrink-0 border-r border-zinc-100 dark:border-zinc-800">
                <div class="h-10 bg-zinc-200 dark:bg-zinc-800" />
                <div class="p-3 space-y-1.5">
                  <div
                    v-for="i in 8"
                    :key="i"
                    class="h-8 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"
                  />
                </div>
              </div>
              <div class="flex-1 p-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                  <div
                    v-for="i in 8"
                    :key="i"
                  >
                    <div class="aspect-square bg-zinc-100 dark:bg-zinc-800 animate-pulse rounded-xl mb-2" />
                    <div class="h-2.5 bg-zinc-100 dark:bg-zinc-800 animate-pulse rounded mb-1.5 w-4/5" />
                    <div class="h-2.5 bg-zinc-100 dark:bg-zinc-800 animate-pulse rounded w-1/2" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Flash Deals Skeleton -->
        <div class="space-y-6">
          <div class="h-8 w-64 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse" />
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div
              v-for="i in 4"
              :key="`deal-${i}`"
              class="h-[450px] bg-zinc-200 dark:bg-zinc-800 rounded-3xl animate-pulse"
            />
          </div>
        </div>
      </div>
    </template>

    <template v-else>
      <HeroSlider :categories="categories" />
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
      />
    </template>

    <TechBlog />
    <BrandPartners />
  </main>
</template>

<script setup lang="ts">
import { useHome } from "@/features/home/composables/useHome";
import HeroSlider from "@/widgets/Home/HeroSlider.vue";
import UspGrid from "@/widgets/Home/UspGrid.vue";
import CatalogSection from "@/widgets/Home/CatalogSection.vue";
import FlashDeals from "@/widgets/Home/FlashDeals.vue";
import RecommendedProducts from "@/widgets/Home/RecommendedProducts.vue";
import TechBlog from "@/widgets/Home/TechBlog.vue";
import BrandPartners from "@/widgets/Home/BrandPartners.vue";

const { categories, popularCategories, flashDeals, recommended, loading, loadHomeData } = useHome();
</script>
