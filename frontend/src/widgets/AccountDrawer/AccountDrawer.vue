<script setup lang="ts">
import { computed } from "vue";
import { useRouter, useRoute, RouterLink } from "vue-router";
import { useI18n } from "vue-i18n";
import { useAuthStore } from "@/entities/user/model/authStore";
import { useCartStore } from "@/entities/order/model/cartStore";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const cartStore = useCartStore();
const { t } = useI18n();

const userName = computed(
  () => authStore.user?.name || t("account.drawer.guestName"),
);
const userEmail = computed(
  () => authStore.user?.email || t("account.drawer.guestEmailPrompt"),
);
const userInitials = computed(() => {
  const name = authStore.user?.name || "";
  if (!name) return t("account.drawer.guestInitial");
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
  const items: NavItem[] = [
    {
      name: t("account.drawer.cart"),
      icon: "shopping_cart",
      action: () => cartStore.openDrawer("cart"),
      badgeKey: "cartCount",
      isGreenBadge: true,
    },
  ];

  if (authStore.isAuthenticated) {
    const allowedRoles = [
      "admin",
      "administrator",
      "support",
      "owner",
      "moderator",
    ];
    const userRoles = authStore.user?.roles || [];
    const hasAdminAccess = allowedRoles.some((role) =>
      userRoles.includes(role),
    );

    if (hasAdminAccess) {
      items.push({
        name: t("account.nav.adminPanel"),
        icon: "admin_panel_settings",
        routeName: "admin-dashboard",
      });
    }

    items.push(
      {
        name: t("account.nav.dashboard"),
        icon: "dashboard",
        query: { tab: "dashboard" },
      },
      {
        name: t("account.nav.orders"),
        icon: "shopping_bag",
        query: { tab: "orders" },
      },
    );
  }

  // These work for both guests and authenticated users
  items.push(
    {
      name: t("account.nav.favorites"),
      icon: "favorite",
      query: { tab: "favorites" },
      badgeKey: "wishlistCount",
    },
    {
      name: t("account.nav.compare"),
      icon: "compare_arrows",
      query: { tab: "compare" },
      badgeKey: "compareCount",
    },
    {
      name: t("account.nav.viewed"),
      icon: "history",
      query: { tab: "viewed" },
    },
  );

  if (authStore.isAuthenticated) {
    items.push({
      name: t("account.nav.notifications"),
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
      name: t("account.nav.settings"),
      icon: "settings",
      query: { tab: "settings" },
    });
  }
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

const getRouteTo = (item: NavItem) => {
  if (item.routeName) {
    return { name: item.routeName };
  }
  return { name: "account", query: item.query };
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
      class="relative w-full max-w-[300px] sm:max-w-[320px] bg-zinc-50 dark:bg-zinc-950 h-full flex flex-col shadow-2xl animate-in slide-in-from-left duration-300 z-10"
    >
      <!-- Branded Header -->
      <div
        class="shrink-0 bg-[#1c2229] px-5 py-4 flex items-center justify-between"
      >
        <RouterLink
          to="/"
          class="flex items-center gap-2 hover:opacity-90 transition-opacity"
          @click="closeDrawer()"
        >
          <span class="font-extrabold text-lg tracking-tight text-white"
            >FilkxTech</span
          >
        </RouterLink>
        <button
          class="w-8 h-8 rounded-full hover:bg-white/10 flex items-center justify-center text-white transition-colors"
          @click="closeDrawer()"
        >
          <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
      </div>

      <div class="flex-grow overflow-y-auto custom-scrollbar">
        <div class="p-4 space-y-3">
          <!-- Prominent Catalog CTA -->
          <RouterLink
            :to="{ name: 'catalog' }"
            class="w-full bg-[#00a046] hover:bg-[#00b050] text-white font-bold text-sm rounded-lg py-3 px-4 flex items-center justify-center gap-2 shadow-sm transition-colors"
            @click="closeDrawer()"
          >
            <span class="material-symbols-outlined text-[20px]">category</span>
            {{ t("account.drawer.catalogCta") }}
          </RouterLink>

          <!-- Perk Cards (same promos as the desktop account sidebar) -->
          <div class="flex flex-col gap-2">
            <div
              class="bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/15 dark:border-emerald-500/20 rounded-lg p-3 flex gap-3 items-center"
            >
              <div
                class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center shrink-0"
              >
                <span
                  class="material-symbols-outlined text-[17px] text-[#00a046]"
                  >verified_user</span
                >
              </div>
              <div class="min-w-0">
                <h4
                  class="font-black text-[11px] text-zinc-900 dark:text-zinc-200 leading-tight"
                >
                  {{ t("account.sidebar.perks.verifiedClient.title") }}
                </h4>
                <p
                  class="text-[10px] text-zinc-500 dark:text-zinc-400 leading-snug mt-0.5"
                >
                  {{ t("account.sidebar.perks.verifiedClient.subtitle") }}
                </p>
              </div>
            </div>
            <div
              class="bg-amber-500/5 dark:bg-amber-500/10 border border-amber-500/15 dark:border-amber-500/20 rounded-lg p-3 flex gap-3 items-center"
            >
              <div
                class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0"
              >
                <span
                  class="material-symbols-outlined text-[17px] text-amber-500"
                  >star</span
                >
              </div>
              <div class="min-w-0">
                <h4
                  class="font-black text-[11px] text-zinc-900 dark:text-zinc-200 leading-tight"
                >
                  {{ t("account.sidebar.perks.loyaltyProgram.title") }}
                </h4>
                <p
                  class="text-[10px] text-zinc-500 dark:text-zinc-400 leading-snug mt-0.5"
                >
                  {{ t("account.sidebar.perks.loyaltyProgram.subtitle") }}
                </p>
              </div>
            </div>
          </div>

          <!-- Support -->
          <RouterLink
            :to="{ name: 'account', query: { tab: 'support' } }"
            class="w-full bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-lg p-3.5 flex items-center gap-3 hover:border-zinc-200 dark:hover:border-zinc-700 transition-colors"
            @click="closeDrawer()"
          >
            <span class="material-symbols-outlined text-[20px] text-zinc-500"
              >help</span
            >
            <span class="font-bold text-sm text-zinc-800 dark:text-zinc-200">{{
              t("account.drawer.supportCard")
            }}</span>
          </RouterLink>

          <!-- User Profile Card -->
          <div
            v-if="authStore.isAuthenticated"
            class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-lg p-3.5 flex items-center gap-3"
          >
            <img
              v-if="authStore.user?.avatarUrl"
              :src="authStore.user.avatarUrl"
              class="w-11 h-11 rounded-full object-cover border border-emerald-500/20 shrink-0 select-none"
            />
            <div
              v-else
              class="w-11 h-11 rounded-full bg-emerald-500/10 text-[#00a046] flex items-center justify-center text-base font-black border border-emerald-500/20 select-none shrink-0"
            >
              {{ userInitials }}
            </div>
            <div class="min-w-0 flex-1">
              <p
                class="font-black text-zinc-800 dark:text-zinc-200 leading-tight truncate text-sm"
              >
                {{ userName }}
              </p>
              <p
                class="text-[11px] text-zinc-400 dark:text-zinc-500 truncate mt-0.5"
              >
                {{ userEmail }}
              </p>
            </div>
          </div>

          <!-- Auth Actions for Guests -->
          <div v-else class="flex gap-2.5">
            <RouterLink
              to="/login"
              class="flex-1 bg-[#00a046] hover:bg-[#00b050] text-white text-xs font-bold py-2.5 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 shadow-sm"
              @click="closeDrawer()"
            >
              <span class="material-symbols-outlined text-[16px]">login</span>
              {{ t("account.drawer.guestLogin") }}
            </RouterLink>
            <RouterLink
              to="/register"
              class="flex-1 border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-xs font-bold py-2.5 px-3 rounded-lg transition-colors flex items-center justify-center gap-1.5 bg-white dark:bg-zinc-900"
              @click="closeDrawer()"
            >
              <span class="material-symbols-outlined text-[16px]"
                >person_add</span
              >
              {{ t("account.drawer.guestRegister") }}
            </RouterLink>
          </div>

          <!-- Main Navigation List -->
          <nav
            class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-lg overflow-hidden divide-y divide-zinc-100 dark:divide-zinc-800"
          >
            <template v-for="item in navItems" :key="item.name">
              <button
                v-if="item.action"
                class="flex items-center gap-3 px-3.5 py-3 w-full text-left transition-colors"
                :class="
                  isActive(item)
                    ? 'text-[#00a046] font-black'
                    : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 font-extrabold'
                "
                @click="navigate(item)"
              >
                <span class="material-symbols-outlined text-[20px]">{{
                  item.icon
                }}</span>
                <span class="text-[14px] tracking-wide">{{ item.name }}</span>
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
              </button>
              <RouterLink
                v-else
                :to="getRouteTo(item)"
                class="flex items-center gap-3 px-3.5 py-3 w-full text-left transition-colors"
                :class="
                  isActive(item)
                    ? 'bg-[#00a046]/8 dark:bg-[#00a046]/12 text-[#00a046] font-black'
                    : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 font-extrabold'
                "
                @click="closeDrawer"
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
              </RouterLink>
            </template>
          </nav>
        </div>
      </div>

      <!-- Footer Menu -->
      <div
        v-if="footerItems.length > 0 || authStore.isAuthenticated"
        class="mt-auto shrink-0 flex flex-col gap-1.5 p-4 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/30 dark:bg-zinc-900/20"
      >
        <RouterLink
          v-for="item in footerItems"
          :key="item.name"
          :to="getRouteTo(item)"
          class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 w-full text-left"
          :class="
            isActive(item)
              ? 'bg-[#00a046]/8 dark:bg-[#00a046]/12 text-[#00a046] font-black'
              : 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-white font-extrabold'
          "
          @click="closeDrawer"
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
        </RouterLink>

        <!-- Logout Action -->
        <button
          v-if="authStore.isAuthenticated"
          class="flex items-center gap-3 text-rose-500 hover:bg-rose-500/8 dark:hover:bg-rose-500/12 rounded-lg px-3 py-2.5 transition-all duration-200 mt-2 w-full text-left font-black text-[14px]"
          @click="handleLogout"
        >
          <span class="material-symbols-outlined text-[20px]">logout</span>
          <span class="tracking-wide">{{ t("account.nav.logout") }}</span>
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
