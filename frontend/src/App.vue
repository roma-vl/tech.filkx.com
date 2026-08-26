<script setup lang="ts">
import CartDrawer from "@/widgets/CartDrawer/CartDrawer.vue";
import AccountDrawer from "@/widgets/AccountDrawer/AccountDrawer.vue";
import ToastNotification from "@/shared/ui/ToastNotification.vue";
import CookieConsent from "@/widgets/CookieConsent/CookieConsent.vue";
import SupportWidget from "@/widgets/Support/SupportWidget.vue";
import { useAuthStore } from "@/entities/user/model/authStore";

const authStore = useAuthStore();
</script>

<template>
  <div
    class="min-h-screen bg-background text-on-surface font-body-md overflow-x-hidden relative"
  >
    <router-view />

    <!-- Dynamic Overlay & Modal Components -->
    <CartDrawer />
    <AccountDrawer />
    <ToastNotification />
    <CookieConsent />
    <SupportWidget v-if="authStore.isAuthenticated" />
  </div>
</template>
