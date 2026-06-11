<template>
  <div
    class="min-h-screen bg-[#fcfcfd] dark:bg-[#0b0c10] text-gray-900 dark:text-gray-100 flex flex-col font-sans relative"
  >
    <!--    <PromoBar />-->
    <Header />

    <!-- Background accents -->
    <div
      class="fixed top-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none"
    />
    <div
      class="fixed bottom-[-10%] left-[-10%] w-[30%] h-[30%] bg-primary-500/5 rounded-full blur-[100px] pointer-events-none"
    />

    <div class="flex-1 flex flex-col min-w-0 relative z-10">
      <main class="flex-1 overflow-y-auto custom-scrollbar">
        <div class="mx-auto w-full">
          <router-view />
        </div>
      </main>
    </div>

    <Footer />
  </div>
</template>

<script setup lang="ts">
import Header from "@/widgets/Header/Header.vue";
import Footer from "@/widgets/Footer/Footer.vue";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useLayout } from "@/layouts/main/useLayout.js";

const { isMobile } = useLayout();
const auth = useAuthStore();
</script>
