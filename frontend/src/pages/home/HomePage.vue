<script setup>
import HeroSlider from '@/components/home/HeroSlider.vue';
import CategoriesGrid from '@/components/home/CategoriesGrid.vue';
import FlashDeals from '@/components/home/FlashDeals.vue';
import RecommendedProducts from '@/components/home/RecommendedProducts.vue';
import BrandPartners from '@/components/home/BrandPartners.vue';
</script>

<template>
  <main class="flex-grow">
    <HeroSlider />
    <CategoriesGrid />
    <FlashDeals />
    <RecommendedProducts />
    <BrandPartners />
  </main>
</template>
