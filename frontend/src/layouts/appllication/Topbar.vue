<template>
  <header
    class="bg-white/40 dark:bg-gray-800/40 backdrop-blur-xl border-b border-white/60 dark:border-white/10 px-6 py-4 relative z-30 shadow-sm"
  >
    <div class="flex items-center justify-between">
      <div class="flex items-center">
        <div class="flex items-center space-x-3">
          <button
            v-if="isMobile"
            class="p-2.5 rounded-xl bg-white/50 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 border border-white/60 dark:border-white/10 shadow-sm transition-all md:hidden"
            @click="$emit('open-mobile-drawer')"
          >
            <Bars3Icon class="h-7 w-7 text-gray-700 dark:text-gray-300" />
          </button>

<!--          <UsageSummary v-if="!isMobile" />-->
        </div>
      </div>
      <div class="flex items-center space-x-5 relative">
        <Notifications />
        <ProfileMenuHeader />
      </div>
    </div>
  </header>
</template>

<script setup>
import Notifications from "@/layouts/topBar/Notifications.vue";
import ProfileMenuHeader from "@/layouts/topBar/ProfileMenuHeader.vue";
import { useLayout } from "@/layouts/appllication/useLayout.js";
// import UsageSummary from "@/layouts/appllication/components/UsageSummary.vue";
import { Bars3Icon } from "@heroicons/vue/24/outline";

defineEmits(["toggleSidebar", "open-mobile-drawer"]);

const { isMobile } = useLayout();
</script>

<style scoped></style>
