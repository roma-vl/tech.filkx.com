<template>
  <main class="flex-grow pb-8 bg-zinc-50/50 dark:bg-zinc-950/50">
    <template v-if="loading">
      <div class="max-w-container-max mx-auto px-4 md:px-8 py-6 space-y-6">
        <!-- Hero Skeleton -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 h-[380px] md:h-[480px] bg-zinc-200 dark:bg-zinc-800 rounded-2xl animate-pulse" />
          <div class="flex flex-col gap-5">
            <div class="flex-1 bg-zinc-200 dark:bg-zinc-800 rounded-2xl animate-pulse" />
            <div class="flex-1 bg-zinc-200 dark:bg-zinc-800 rounded-2xl animate-pulse" />
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
      </div>

      <div class="max-w-container-max mx-auto px-4 md:px-8 py-10 space-y-16">
        <!-- Categories Skeleton -->
        <div class="space-y-6">
          <div class="h-8 w-48 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse" />
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5">
            <div
              v-for="i in 6"
              :key="`cat-${i}`"
              class="h-32 bg-zinc-200 dark:bg-zinc-800 rounded-2xl animate-pulse"
            />
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
      <HeroSlider />
      <UspGrid />
      <CategoriesGrid :categories="categories" />
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
import HeroSlider from "@/components/home/HeroSlider.vue";
import UspGrid from "@/components/home/UspGrid.vue";
import CategoriesGrid from "@/components/home/CategoriesGrid.vue";
import FlashDeals from "@/components/home/FlashDeals.vue";
import RecommendedProducts from "@/components/home/RecommendedProducts.vue";
import TechBlog from "@/components/home/TechBlog.vue";
import BrandPartners from "@/components/home/BrandPartners.vue";

const { categories, flashDeals, recommended, loading, loadHomeData } = useHome();
</script>
