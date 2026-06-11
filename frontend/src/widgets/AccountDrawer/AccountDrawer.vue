<script setup lang="ts">
import { computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useCartStore } from "@/entities/order/model/cartStore";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const cartStore = useCartStore();

const userName = computed(() => authStore.user?.name || "Гість");
const userEmail = computed(
  () => authStore.user?.email || "Авторизуйтесь для доступу",
);
const userInitials = computed(() => {
  const name = authStore.user?.name || "";
  if (!name) return "Г";
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .substring(0, 2)
    .toUpperCase();
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

const navItems = computed<NavItem[]>(() => {
  const items: NavItem[] = [];

  if (authStore.isAuthenticated) {
    items.push(
      {
        name: "Панель керування",
        icon: "dashboard",
        query: { tab: "dashboard" },
      },
      {
        name: "Історія замовлень",
        icon: "shopping_bag",
        query: { tab: "orders" },
      },
    );
  }

  // These work for both guests and authenticated users
  items.push(
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
  );

  if (authStore.isAuthenticated) {
    items.push({
      name: "Сповіщення",
      icon: "notifications",
      query: { tab: "notifications" },
      badgeKey: "unreadNotificationsCount",
      isGreenBadge: true,
    });
  }

  return items;
});

const footerItems = computed<NavItem[]>(() => {
  const items: NavItem[] = [];
  if (authStore.isAuthenticated) {
    items.push({
      name: "Налаштування",
      icon: "settings",
      query: { tab: "settings" },
    });
  }
  items.push({ name: "Підтримка", icon: "help", query: { tab: "support" } });
  return items;
});

const activeTab = computed(() => (route.query.tab as string) || "");

const isActive = (item: NavItem) => {
  if (item.action) return false;
  if (item.routeName && item.routeName !== "account") {
    return route.name === item.routeName;
  }
  return route.name === "account" && activeTab.value === item.query?.tab;
};

const navigate = (item: NavItem) => {
  cartStore.closeDrawer();
  if (item.action) {
    item.action();
  } else if (item.routeName && !item.query) {
    router.push({ name: item.routeName });
  } else {
    router.push({ name: "account", query: item.query });
  }
};

const handleLogout = async () => {
  cartStore.closeDrawer();
  await authStore.logout();
  router.push("/login");
};

const closeDrawer = () => {
  cartStore.closeDrawer();
};
</script>

<template>
  <div
    v-if="cartStore.activeDrawer === 'account'"
    class="fixed inset-0 z-[100] flex justify-start"
  >
    <!-- Backdrop Overlay -->
    <div
      class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
      @click="closeDrawer()"
    />

    <!-- Drawer Panel (Slides in from the Left) -->
    <div
      class="relative w-full max-w-[290px] sm:max-w-[320px] bg-white dark:bg-zinc-900 h-full flex flex-col shadow-2xl border-r border-zinc-200 dark:border-zinc-800 animate-in slide-in-from-left duration-300 z-10"
    >
      <!-- Close Button -->
      <button
        class="absolute top-4 right-4 w-9 h-9 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 transition-colors z-20"
        @click="closeDrawer()"
      >
        <span class="material-symbols-outlined text-[20px]">close</span>
      </button>

      <!-- User Info Header -->
      <div
        class="pt-12 pb-6 px-6 border-b border-zinc-150 dark:border-zinc-850 flex flex-col gap-4 bg-zinc-50/50 dark:bg-zinc-950/20"
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
          <div class="min-w-0 flex-1">
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
            <div
              v-if="authStore.isAuthenticated"
              class="flex items-center gap-1 mt-1"
            >
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

        <!-- Auth Actions for Guests -->
        <div v-if="!authStore.isAuthenticated" class="flex gap-2.5 mt-2">
          <button
            class="flex-1 bg-[#00a046] hover:bg-[#00b050] text-white text-xs font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 shadow-sm"
            @click="
              closeDrawer();
              router.push('/login');
            "
          >
            <span class="material-symbols-outlined text-[16px]">login</span>
            Увійти
          </button>
          <button
            class="flex-1 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5"
            @click="
              closeDrawer();
              router.push('/register');
            "
          >
            <span class="material-symbols-outlined text-[16px]"
              >person_add</span
            >
            Реєстрація
          </button>
        </div>
      </div>

      <!-- Main Navigation Menu -->
      <nav
        class="flex-grow overflow-y-auto px-4 py-6 flex flex-col gap-1.5 custom-scrollbar"
      >
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
            :style="
              isActive(item) ? 'font-variation-settings: \'FILL\' 1;' : ''
            "
            >{{ item.icon }}</span
          >
          <span class="text-[14px] tracking-wide">{{ item.name }}</span>

          <!-- Badge Counts -->
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

      <!-- Footer Menu -->
      <div
        class="mt-auto flex flex-col gap-1.5 p-4 border-t border-zinc-150 dark:border-zinc-800 bg-zinc-50/30 dark:bg-zinc-950/10"
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
            :style="
              isActive(item) ? 'font-variation-settings: \'FILL\' 1;' : ''
            "
            >{{ item.icon }}</span
          >
          <span class="text-[14px] tracking-wide">{{ item.name }}</span>
          <span
            v-if="isActive(item)"
            class="ml-auto material-symbols-outlined text-[16px]"
            >chevron_right</span
          >
        </button>

        <!-- Logout Action -->
        <button
          v-if="authStore.isAuthenticated"
          class="flex items-center gap-3 text-rose-500 hover:bg-rose-500/8 dark:hover:bg-rose-500/12 rounded-xl px-3 py-2.5 transition-all duration-200 mt-2 w-full text-left font-black text-[14px]"
          @click="handleLogout"
        >
          <span class="material-symbols-outlined text-[20px]">logout</span>
          <span class="tracking-wide">Вийти з акаунту</span>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 20px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.05);
}
</style>
