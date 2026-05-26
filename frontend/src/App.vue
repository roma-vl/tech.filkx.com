<script setup>
import CartDrawer from './components/ui/CartDrawer.vue';
import WishlistDrawer from './components/ui/WishlistDrawer.vue';
import CompareDrawer from './components/ui/CompareDrawer.vue';
import ToastNotification from './components/ui/ToastNotification.vue';
</script>

<template>
  <div class="min-h-screen bg-background text-on-surface font-body-md overflow-x-hidden relative">
    <router-view />

    <!-- Dynamic Overlay & Modal Components -->
    <CartDrawer />
    <WishlistDrawer />
    <CompareDrawer />
    <ToastNotification />
  </div>
</template>
