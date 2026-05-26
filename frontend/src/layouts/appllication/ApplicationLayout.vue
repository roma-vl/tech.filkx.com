<template>
  <div
    class="min-h-screen bg-[#fcfcfd] dark:bg-[#0b0c10] text-gray-900 dark:text-gray-100 flex overflow-hidden font-sans relative"
  >
    <!-- Background accents -->
    <div class="fixed top-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none" />
    <div class="fixed bottom-[-10%] left-[-10%] w-[30%] h-[30%] bg-primary-500/5 rounded-full blur-[100px] pointer-events-none" />

    <AppSidebar
      v-if="!isMobile"
      :collapsed="sidebarCollapsed"
      @toggle="toggleSidebar"
      @start-tour="handleStartTour"
    />

    <MobileNavDrawer
      v-if="isMobile"
      :open="mobileDrawerOpen"
      @close="mobileDrawerOpen = false"
    />

    <div class="flex-1 flex flex-col min-w-0 relative z-10">
      <Topbar
        :sidebar-collapsed="sidebarCollapsed"
        @toggle-sidebar="toggleSidebar"
        @open-mobile-drawer="mobileDrawerOpen = true"
      />

      <main class="flex-1 overflow-y-auto custom-scrollbar">
        <div class="py-6 sm:py-10">
          <div class="mx-auto w-full max-w-[1500px]">
            <slot />
          </div>
        </div>
      </main>

    </div>
  </div>
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import Topbar from "@/layouts/appllication/Topbar.vue";
// import TrialWarningBanner from "@/components/application/features/billing/TrialWarningBanner.vue";
import AppSidebar from "@/layouts/appllication/components/AppSidebar.vue";
import MobileNavDrawer from "@/layouts/appllication/components/MobileNavDrawer.vue";
import {useAuthStore} from "@/stores/auth";
import {useLayout} from "@/layouts/appllication/useLayout.js";
// import OnboardingTour from "@/components/application/features/onboarding/OnboardingTour.vue";
// import TourPromptModal from "@/components/application/features/onboarding/TourPromptModal.vue";

const { sidebarCollapsed, toggleSidebar, isMobile } = useLayout();
const auth = useAuthStore();

const mobileDrawerOpen = ref(false);
const tourRef = ref(null);
const tourCompleted = ref(false);

onMounted(() => {
  tourCompleted.value = localStorage.getItem("tour_completed") === "true";
});

watch(isMobile, (newVal) => {
  if (!newVal) {
    mobileDrawerOpen.value = false;
  }
});

const handleStartTour = (choice = "restart") => {
  localStorage.setItem("tour_prompt_seen", "true");
  localStorage.removeItem("tour_reminder_dismissed");

  window.dispatchEvent(new Event("tour-state-changed"));

  if (tourRef.value) {
    if (
      choice === "continue" &&
      typeof tourRef.value.resumeTour === "function"
    ) {
      tourRef.value.resumeTour();
    } else if (typeof tourRef.value.startTour === "function") {
      tourRef.value.startTour();
    }
  } else {
    console.error("[BaseLayout] tourRef is null!", tourRef.value);
  }
};

const handleDismissPrompt = () => {
  // localStorage is set in TourPromptModal, just log here
};

const isTrialWarningVisible = computed(() => {
  const sub = auth.user?.subscription;

  if (!sub?.isTrial || !sub?.trialEndsAt) return false;

  const nowUtc = Date.now();
  const endsAtUtc = new Date(sub.trialEndsAt).getTime();

  const diffMs = endsAtUtc - nowUtc;
  const threeDaysMs = 3 * 24 * 60 * 60 * 1000;

  // Показуємо банер, якщо тріал закінчився (diffMs <= 0)
  // АБО якщо залишилося менше 3 днів (diffMs <= threeDaysMs)
  return diffMs <= threeDaysMs;
});
</script>
