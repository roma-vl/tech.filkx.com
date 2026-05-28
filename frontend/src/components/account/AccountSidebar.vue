<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth.js';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const userName = computed(() => authStore.user?.name || 'Клієнт');
const userEmail = computed(() => authStore.user?.email || '');
const userInitials = computed(() => {
  const name = authStore.user?.name || '';
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase() || 'К';
});

const navItems = [
  { name: 'Панель керування', icon: 'dashboard',       query: { tab: 'dashboard' } },
  { name: 'Історія замовлень', icon: 'shopping_bag',     query: { tab: 'orders' } },
  { name: 'Моє обране',       icon: 'favorite',         query: { tab: 'favorites' } },
  { name: 'Порівняння товарів', icon: 'compare_arrows',   query: { tab: 'compare' } },
];

const footerItems = [
  { name: 'Налаштування',     icon: 'settings', query: { tab: 'settings' } },
  { name: 'Підтримка',        icon: 'help',     query: { tab: 'support' } },
];

const activeTab = computed(() => route.query.tab || 'dashboard');

const isActive = (item) => {
  if (item.routeName && item.routeName !== 'account') {
    return route.name === item.routeName;
  }
  return route.name === 'account' && activeTab.value === item.query?.tab;
};

const navigate = (item) => {
  if (item.routeName && !item.query) {
    router.push({ name: item.routeName });
  } else {
    router.push({ name: 'account', query: item.query });
  }
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<template>
  <aside class="hidden lg:flex flex-col sticky top-[80px] h-[calc(100vh-80px)] w-72 bg-zinc-50 dark:bg-zinc-955 pt-10 px-6 pb-6 gap-6 border-r border-zinc-200 dark:border-zinc-850">
    <!-- User info -->
    <div class="flex flex-col gap-1 pb-5 border-b border-zinc-200 dark:border-zinc-800">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-emerald-500/10 text-[#00a046] flex items-center justify-center text-xl font-black border border-emerald-500/20 select-none">
          {{ userInitials }}
        </div>
        <div class="min-w-0">
          <p class="font-extrabold text-zinc-900 dark:text-white leading-tight truncate text-base">{{ userName }}</p>
          <p class="text-xs text-zinc-400 dark:text-zinc-500 truncate mt-1">{{ userEmail }}</p>
          <div class="flex items-center gap-1.5 mt-1.5">
            <span class="material-symbols-outlined text-[14px] text-[#00a046]">verified</span>
            <p class="font-black text-[#00a046] uppercase tracking-widest text-[10px]">Клієнт</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main nav -->
    <nav class="flex flex-col gap-2">
      <button
        v-for="item in navItems"
        :key="item.name"
        @click="navigate(item)"
        class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 group w-full text-left"
        :class="isActive(item)
          ? 'bg-[#00a046] text-white shadow-md shadow-emerald-500/10'
          : 'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 hover:text-zinc-900 dark:hover:text-white'"
      >
        <span
          class="material-symbols-outlined text-[20px]"
          :style="isActive(item) ? 'font-variation-settings: \'FILL\' 1;' : ''"
        >{{ item.icon }}</span>
        <span class="font-extrabold text-xs md:text-sm">{{ item.name }}</span>
        <span v-if="isActive(item)" class="ml-auto material-symbols-outlined text-[18px]">chevron_right</span>
      </button>
    </nav>

    <!-- Footer nav -->
    <div class="mt-auto flex flex-col gap-2 pt-5 border-t border-zinc-200 dark:border-zinc-800">
      <button
        v-for="item in footerItems"
        :key="item.name"
        @click="navigate(item)"
        class="flex items-center gap-3.5 px-4 py-3 rounded-xl transition-all duration-200 w-full text-left"
        :class="isActive(item)
          ? 'bg-[#00a046] text-white shadow-md shadow-emerald-500/10'
          : 'text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-900 hover:text-zinc-900 dark:hover:text-white'"
      >
        <span
          class="material-symbols-outlined text-[20px]"
          :style="isActive(item) ? 'font-variation-settings: \'FILL\' 1;' : ''"
        >{{ item.icon }}</span>
        <span class="font-extrabold text-xs md:text-sm">{{ item.name }}</span>
        <span v-if="isActive(item)" class="ml-auto material-symbols-outlined text-[18px]">chevron_right</span>
      </button>

      <button
        @click="handleLogout"
        class="flex items-center gap-3.5 text-rose-500 hover:bg-rose-500/10 rounded-xl px-4 py-3 transition-all duration-200 mt-2 w-full text-left font-extrabold text-xs md:text-sm"
      >
        <span class="material-symbols-outlined text-[20px]">logout</span>
        <span>Вийти з акаунту</span>
      </button>
    </div>
  </aside>
</template>

<style scoped>
</style>
