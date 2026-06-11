<script setup lang="ts">
import { computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useCartStore } from "@/entities/order/model/cartStore";

import AccountSidebar from "@/widgets/Account/AccountSidebar.vue";
import AccountDashboardTab from "@/widgets/Account/tabs/AccountDashboardTab.vue";
import AccountOrdersTab from "@/widgets/Account/tabs/AccountOrdersTab.vue";
import AccountFavoritesTab from "@/widgets/Account/tabs/AccountFavoritesTab.vue";
import AccountCompareTab from "@/widgets/Account/tabs/AccountCompareTab.vue";
import AccountViewedTab from "@/widgets/Account/tabs/AccountViewedTab.vue";
import AccountSettingsTab from "@/widgets/Account/tabs/AccountSettingsTab.vue";
import AccountSupportTab from "@/widgets/Account/tabs/AccountSupportTab.vue";
import AccountNotificationsTab from "@/widgets/Account/tabs/AccountNotificationsTab.vue";

const route = useRoute();
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
      <header class="mb-8">
        <div class="flex items-start justify-between gap-4 pb-5 border-b border-zinc-200 dark:border-zinc-800">
          <div class="min-w-0">
            <p class="text-[10px] font-extrabold text-[#00a046] uppercase tracking-widest mb-1">Особистий кабінет</p>
            <h1 class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight leading-tight">
              {{ tabTitles[activeTab] || "Кабінет" }}
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1.5">
              Вітаємо, <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ userName }}</span>! Керуйте профілем та замовленнями.
            </p>
          </div>
          <div class="lg:hidden flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 shrink-0">
            <span class="material-symbols-outlined text-[15px] text-[#00a046]">verified</span>
            <span class="text-[10px] font-black text-[#00a046] uppercase tracking-widest">Клієнт</span>
          </div>
        </div>
      </header>

      <!-- Mobile Navigation Scroll Bar -->
      <div class="lg:hidden mb-6 -mx-4 px-4 overflow-x-auto scrollbar-none flex gap-2 pb-1">
        <router-link
          v-for="item in navTabs"
          :key="item.tab"
          :to="{ name: 'account', query: { tab: item.tab } }"
          class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-extrabold whitespace-nowrap transition-all shrink-0"
          :class="
            activeTab === item.tab
              ? 'bg-[#00a046] text-white shadow-sm'
              : 'bg-white dark:bg-zinc-900 text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-800'
          "
        >
          <span
            class="material-symbols-outlined text-[15px]"
            :style="activeTab === item.tab ? 'font-variation-settings: \'FILL\' 1' : ''"
          >{{ item.icon }}</span>
          {{ item.label }}
        </router-link>
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
