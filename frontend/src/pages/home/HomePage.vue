<script setup>
import HeroSlider from '@/components/home/HeroSlider.vue';
import UspGrid from '@/components/home/UspGrid.vue';
import CategoriesGrid from '@/components/home/CategoriesGrid.vue';
import FlashDeals from '@/components/home/FlashDeals.vue';
import RecommendedProducts from '@/components/home/RecommendedProducts.vue';
import TechBlog from '@/components/home/TechBlog.vue';
import BrandPartners from '@/components/home/BrandPartners.vue';
</script>

<template>
  <main class="flex-grow pb-8 bg-zinc-50/50 dark:bg-zinc-950/50">
    <HeroSlider />
    <UspGrid />
    <CategoriesGrid />
    <FlashDeals />
    <RecommendedProducts />
    <TechBlog />
    <BrandPartners />
  </main>
</template>
