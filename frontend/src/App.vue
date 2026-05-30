<script setup>
import CartDrawer from "./components/ui/CartDrawer.vue";
import ToastNotification from "./components/ui/ToastNotification.vue";
</script>

<template>
  <div
    class="min-h-screen bg-background text-on-surface font-body-md overflow-x-hidden relative"
  >
    <router-view />

    <!-- Dynamic Overlay & Modal Components -->
    <CartDrawer />
    <ToastNotification />
  </div>
</template>
