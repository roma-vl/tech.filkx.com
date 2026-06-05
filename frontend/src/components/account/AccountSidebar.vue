<script setup lang="ts">
import { computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useCartStore } from "@/entities/order/model/cartStore";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const cartStore = useCartStore();

const userName = computed(() => authStore.user?.name || "Клієнт");
const userEmail = computed(() => authStore.user?.email || "");
const userInitials = computed(() => {
  const name = authStore.user?.name || "";
  return (
    name
      .split(" ")
      .map((n) => n[0])
      .join("")
      .substring(0, 2)
      .toUpperCase() || "К"
  );
});

interface NavItem {
  name: string;
  icon: string;
  query?: { tab: string };
  routeName?: string;
  action?: () => void;
  badgeKey?:
    | "cartCount"
    | "wishlistCount"
    | "compareCount"
    | "unreadNotificationsCount";
  isGreenBadge?: boolean;
}

const navItems: NavItem[] = [
  { name: "Панель керування", icon: "dashboard", query: { tab: "dashboard" } },
  { name: "Історія замовлень", icon: "shopping_bag", query: { tab: "orders" } },
  {
    name: "Моє обране",
    icon: "favorite",
    query: { tab: "favorites" },
    badgeKey: "wishlistCount",
  },
  {
    name: "Порівняння товарів",
    icon: "compare_arrows",
    query: { tab: "compare" },
    badgeKey: "compareCount",
  },
  { name: "Історія переглядів", icon: "history", query: { tab: "viewed" } },
  {
    name: "Сповіщення",
    icon: "notifications",
    query: { tab: "notifications" },
    badgeKey: "unreadNotificationsCount",
    isGreenBadge: true,
  },
];

const footerItems: NavItem[] = [
  { name: "Налаштування", icon: "settings", query: { tab: "settings" } },
  { name: "Підтримка", icon: "help", query: { tab: "support" } },
];

const activeTab = computed(() => (route.query.tab as string) || "dashboard");

const isActive = (item: NavItem) => {
  if (item.action) return false;
  if (item.routeName && item.routeName !== "account") {
    return route.name === item.routeName;
  }
  return route.name === "account" && activeTab.value === item.query?.tab;
};

const navigate = (item: NavItem) => {
  if (item.action) {
    item.action();
  } else if (item.routeName && !item.query) {
    router.push({ name: item.routeName });
  } else {
    router.push({ name: "account", query: item.query });
  }
};

const handleLogout = async () => {
  await authStore.logout();
  router.push("/login");
};
</script>

<template>
  <aside
    class="hidden lg:flex flex-col sticky h-[calc(100vh-80px)] w-72 bg-white dark:bg-zinc-900/60 backdrop-blur-md pt-10 px-5 pb-6 gap-5 border-r border-zinc-200 dark:border-zinc-800/80 overflow-y-auto scrollbar-thin"
  >
    <!-- Promo Banners (Rozetka style) -->
    <div class="flex flex-col gap-3">
      <!-- Green Card Banner -->
      <!--      <div class="bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/15 dark:border-emerald-500/20 rounded-lg p-4 flex gap-3 items-start relative overflow-hidden group hover:bg-emerald-500/8 dark:hover:bg-emerald-500/15 transition-all">-->
      <!--        <span class="material-symbols-outlined text-[22px] text-[#00a046] shrink-0 mt-0.5">credit_card</span>-->
      <!--        <div class="min-w-0">-->
      <!--          <h4 class="font-black text-[11px] text-zinc-900 dark:text-zinc-200">-->
      <!--            Картка FilkxTech-->
      <!--          </h4>-->
      <!--          <p class="text-[10px] text-zinc-500 dark:text-zinc-400 leading-snug mt-0.5 font-medium">-->
      <!--            Персональні знижки та накопичувальні бонуси-->
      <!--          </p>-->
      <!--        </div>-->
      <!--      </div>-->

      <!-- Yellow Card Banner -->
      <!--      <div class="bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/15 dark:border-amber-500/20 rounded-lg p-4 flex gap-3 items-start relative overflow-hidden group hover:bg-amber-500/8 dark:hover:bg-amber-500/15 transition-all">-->
      <!--        <span class="material-symbols-outlined text-[22px] text-amber-500 shrink-0 mt-0.5">star</span>-->
      <!--        <div class="min-w-0">-->
      <!--          <h4 class="font-black text-[11px] text-zinc-900 dark:text-zinc-200 font-sans">-->
      <!--            Підписка Smart-->
      <!--          </h4>-->
      <!--          <p class="text-[10px] text-zinc-500 dark:text-zinc-400 leading-snug mt-0.5 font-medium">-->
      <!--            Безкоштовна доставка та пріоритетна підтримка-->
      <!--          </p>-->
      <!--        </div>-->
      <!--      </div>-->
    </div>

    <!-- User info -->
    <div
      class="flex flex-col gap-1 pb-4 border-b border-zinc-150 dark:border-zinc-800"
    >
      <div class="flex items-center gap-3">
        <img
          v-if="authStore.user?.avatarUrl"
          :src="authStore.user.avatarUrl"
          class="w-12 h-12 rounded-full object-cover border border-emerald-500/20 shrink-0 select-none"
        />
        <div
          v-else
          class="w-12 h-12 rounded-full bg-emerald-500/10 text-[#00a046] flex items-center justify-center text-lg font-black border border-emerald-500/20 select-none shrink-0"
        >
          {{ userInitials }}
        </div>
        <div class="min-w-0">
          <p
            class="font-black text-zinc-850 dark:text-zinc-150 leading-tight truncate text-sm"
          >
            {{ userName }}
          </p>
          <p
            class="text-[11px] text-zinc-400 dark:text-zinc-500 truncate mt-0.5"
          >
            {{ userEmail }}
          </p>
          <div class="flex items-center gap-1.5 mt-1">
            <span class="material-symbols-outlined text-[13px] text-[#00a046]"
              >verified</span
            >
            <p
              class="font-black text-[#00a046] uppercase tracking-widest text-[9px]"
            >
              Клієнт
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main nav -->
    <nav class="flex flex-col gap-1.5">
      <button
        v-for="item in navItems"
        :key="item.name"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group w-full text-left relative"
        :class="
          isActive(item)
            ? 'bg-[#00a046]/8 dark:bg-[#00a046]/12 text-[#00a046] font-black border-l-4 border-[#00a046] rounded-l-none pl-2.5'
            : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:text-zinc-900 dark:hover:text-white font-extrabold'
        "
        @click="navigate(item)"
      >
        <span
          class="material-symbols-outlined text-[20px]"
          :style="isActive(item) ? 'font-variation-settings: \'FILL\' 1;' : ''"
          >{{ item.icon }}</span
        >
        <span class="text-[15px] tracking-wide">{{ item.name }}</span>

        <!-- Badges (Rozetka style) -->
        <span
          v-if="item.badgeKey && cartStore[item.badgeKey] > 0"
          class="ml-auto text-[10px] w-5 h-5 rounded-full flex items-center justify-center font-black leading-none shrink-0"
          :class="[
            item.isGreenBadge
              ? 'bg-[#00a046] text-white'
              : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400',
          ]"
        >
          {{ cartStore[item.badgeKey] }}
        </span>
        <span
          v-else-if="isActive(item)"
          class="ml-auto material-symbols-outlined text-[16px]"
          >chevron_right</span
        >
      </button>
    </nav>

    <!-- Footer nav -->
    <div
      class="mt-auto flex flex-col gap-1.5 pt-4 border-t border-zinc-150 dark:border-zinc-800"
    >
      <button
        v-for="item in footerItems"
        :key="item.name"
        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 w-full text-left"
        :class="
          isActive(item)
            ? 'bg-[#00a046]/8 dark:bg-[#00a046]/12 text-[#00a046] font-black border-l-4 border-[#00a046] rounded-l-none pl-2.5'
            : 'text-zinc-650 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:text-zinc-900 dark:hover:text-white font-extrabold'
        "
        @click="navigate(item)"
      >
        <span
          class="material-symbols-outlined text-[20px]"
          :style="isActive(item) ? 'font-variation-settings: \'FILL\' 1;' : ''"
          >{{ item.icon }}</span
        >
        <span class="text-[15px] tracking-wide">{{ item.name }}</span>
        <span
          v-if="isActive(item)"
          class="ml-auto material-symbols-outlined text-[16px]"
          >chevron_right</span
        >
      </button>

      <button
        class="flex items-center gap-3 text-rose-500 hover:bg-rose-500/8 dark:hover:bg-rose-500/12 rounded-xl px-3 py-2.5 transition-all duration-200 mt-2 w-full text-left font-black text-[15px]"
        @click="handleLogout"
      >
        <span class="material-symbols-outlined text-[20px]">logout</span>
        <span class="tracking-wide">Вийти з акаунту</span>
      </button>
    </div>
  </aside>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
  width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 20px;
}
.dark .scrollbar-thin::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.05);
}
</style>
