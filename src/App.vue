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

// New interactive components
import CartDrawer from './components/CartDrawer.vue';
import WishlistDrawer from './components/WishlistDrawer.vue';
import CompareDrawer from './components/CompareDrawer.vue';
import AccountModal from './components/AccountModal.vue';
import ToastNotification from './components/ToastNotification.vue';
import Catalog from './components/Catalog.vue';
import ProductDetail from './components/ProductDetail.vue';
import ShoppingCart from './components/ShoppingCart.vue';
import { store } from './store.js';
</script>

<template>
  <div class="min-h-screen flex flex-col bg-background text-on-surface font-body-md overflow-x-hidden relative">
    <PromoBar />
    <Header />
    <Navbar />
    <main class="flex-grow text-on-background">
      <template v-if="store.currentPage === 'home'">
        <HeroSlider />
        <CategoriesGrid />
        <FlashDeals />
        <RecommendedProducts />
        <BrandPartners />
      </template>
      <template v-else-if="store.currentPage === 'catalog'">
        <Catalog />
      </template>
      <template v-else-if="store.currentPage === 'product'">
        <ProductDetail />
      </template>
      <template v-else-if="store.currentPage === 'cart'">
        <ShoppingCart />
      </template>
    </main>
    <Footer />

    <!-- Dynamic Overlay & Modal Components -->
    <CartDrawer />
    <WishlistDrawer />
    <CompareDrawer />
    <AccountModal />
    <ToastNotification />
  </div>
</template>
