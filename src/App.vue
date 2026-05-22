<script setup>
import PromoBar from './components/PromoBar.vue';
import Header from './components/Header.vue';
import Navbar from './components/Navbar.vue';
import HeroSlider from './components/HeroSlider.vue';
import CategoriesGrid from './components/CategoriesGrid.vue';
import FlashDeals from './components/FlashDeals.vue';
import RecommendedProducts from './components/RecommendedProducts.vue';
import BrandPartners from './components/BrandPartners.vue';
import Footer from './components/Footer.vue';
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background text-on-surface font-body-md overflow-x-hidden">
    <PromoBar />
    <Header />
    <Navbar />
    <main class="flex-grow">
      <HeroSlider />
      <CategoriesGrid />
      <FlashDeals />
      <RecommendedProducts />
      <BrandPartners />
    </main>
    <Footer />
  </div>
</template>
