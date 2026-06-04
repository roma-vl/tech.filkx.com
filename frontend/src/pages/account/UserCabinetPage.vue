<script setup lang="ts">
import { computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useCartStore } from "@/entities/order/model/cartStore";

import AccountSidebar from "@/components/account/AccountSidebar.vue";
import AccountDashboardTab from "@/components/account/tabs/AccountDashboardTab.vue";
import AccountOrdersTab from "@/components/account/tabs/AccountOrdersTab.vue";
import AccountFavoritesTab from "@/components/account/tabs/AccountFavoritesTab.vue";
import AccountCompareTab from "@/components/account/tabs/AccountCompareTab.vue";
import AccountViewedTab from "@/components/account/tabs/AccountViewedTab.vue";
import AccountSettingsTab from "@/components/account/tabs/AccountSettingsTab.vue";
import AccountSupportTab from "@/components/account/tabs/AccountSupportTab.vue";
import AccountNotificationsTab from "@/components/account/tabs/AccountNotificationsTab.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const cartStore = useCartStore();

const activeTab = computed(() => (route.query.tab as string) || "dashboard");
const userName = computed(() => authStore.user?.name || "Клієнт");

const tabTitles: Record<string, string> = {
  dashboard: "Панель керування",
  orders: "Історія замовлень",
  favorites: "Моє обране",
  compare: "Порівняння товарів",
  viewed: "Історія переглядів",
  settings: "Налаштування профілю",
  support: "Служба підтримки",
  notifications: "Сповіщення та новини",
};

const navTabs = [
  { label: "Панель", icon: "dashboard", tab: "dashboard" },
  { label: "Замовлення", icon: "shopping_bag", tab: "orders" },
  { label: "Обране", icon: "favorite", tab: "favorites" },
  { label: "Порівняння", icon: "compare_arrows", tab: "compare" },
  { label: "Перегляди", icon: "history", tab: "viewed" },
  { label: "Сповіщення", icon: "notifications", tab: "notifications" },
  { label: "Налаштування", icon: "settings", tab: "settings" },
  { label: "Підтримка", icon: "help", tab: "support" },
];

const selectTab = (tab: string) => router.push({ name: "account", query: { tab } });

const tabComponents: Record<string, any> = {
  dashboard: AccountDashboardTab,
  orders: AccountOrdersTab,
  favorites: AccountFavoritesTab,
  compare: AccountCompareTab,
  viewed: AccountViewedTab,
  settings: AccountSettingsTab,
  support: AccountSupportTab,
  notifications: AccountNotificationsTab,
};

const currentTab = computed(
  () => tabComponents[activeTab.value] ?? AccountDashboardTab,
);

onMounted(() => {
  if (authStore.isAuthenticated) {
    cartStore.fetchUnreadNotificationsCount();
    cartStore.syncUserLists();
  }
});
</script>

<template>
  <div
    class="max-w-container-max mx-auto flex min-h-screen bg-zinc-50 dark:bg-zinc-950 relative font-sans"
  >
    <!-- Desktop Sidebar -->
    <AccountSidebar />

    <!-- Content Workspace -->
    <div class="flex-1 px-4 md:px-8 py-10 min-w-0">
      <!-- Page Header -->
      <header class="mb-8 border-b border-zinc-200 dark:border-zinc-800 pb-6">
        <div
          class="flex flex-col md:flex-row md:items-center justify-between gap-4"
        >
          <div>
            <h1
              class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight"
            >
              {{ tabTitles[activeTab] || "Особистий кабінет" }}
            </h1>
            <p
              class="text-sm md:text-base text-zinc-500 dark:text-zinc-400 mt-1"
            >
              Вітаємо, {{ userName }}! Керуйте своїм профілем, замовленнями та
              налаштуваннями акаунту FilkxTech.
            </p>
          </div>
          <div
            class="lg:hidden self-start flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20"
          >
            <span class="material-symbols-outlined text-[16px] text-[#00a046]">verified</span>
            <span
              class="text-[11px] font-black text-[#00a046] uppercase tracking-widest"
            >Клієнт</span>
          </div>
        </div>
      </header>

      <!-- Mobile Navigation Scroll Bar -->
      <div
        class="lg:hidden mb-6 -mx-4 px-4 overflow-x-auto scrollbar-none flex gap-2 border-b border-zinc-200 dark:border-zinc-800 pb-4"
      >
        <button
          v-for="item in navTabs"
          :key="item.tab"
          class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs md:text-sm font-extrabold whitespace-nowrap transition-all shadow-sm border border-zinc-200 dark:border-zinc-850"
          :class="
            activeTab === item.tab
              ? 'bg-[#00a046] text-white border-[#00a046]'
              : 'bg-white dark:bg-zinc-900 text-zinc-650 dark:text-zinc-350 hover:bg-zinc-100 dark:hover:bg-zinc-800'
          "
          @click="selectTab(item.tab)"
        >
          <span class="material-symbols-outlined text-[16px] md:text-[18px]">{{
            item.icon
          }}</span>
          {{ item.label }}
        </button>
      </div>

      <!-- Tab Content — dynamic component swap -->
      <main>
        <component :is="currentTab" />
      </main>
    </div>
  </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
  display: none;
}
.scrollbar-none {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
