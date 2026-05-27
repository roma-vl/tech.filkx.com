<script setup>
import { computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth.js';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const userName = computed(() => authStore.user?.name || 'User');
const userEmail = computed(() => authStore.user?.email || '');
const userInitials = computed(() => {
  const name = authStore.user?.name || '';
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase() || 'U';
});

const navItems = [
  { name: 'Dashboard',      icon: 'dashboard',       query: { tab: 'dashboard' } },
  { name: 'Orders',         icon: 'shopping_bag',     query: { tab: 'orders' } },
  { name: 'Favorites',      icon: 'favorite',         query: { tab: 'favorites' } },
  { name: 'Compare',        icon: 'compare_arrows',   query: { tab: 'compare' } },
];

const footerItems = [
  { name: 'Settings', icon: 'settings', query: { tab: 'settings' } },
  { name: 'Support',  icon: 'help',     query: { tab: 'support' } },
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
  <aside class="hidden lg:flex flex-col sticky top-[80px] h-[calc(100vh-80px)] w-72 bg-surface-container-low p-6 gap-stack-lg border-r border-outline-variant">
    <!-- User info -->
    <div class="flex flex-col gap-1 pb-4 border-b border-outline-variant">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container text-xl font-bold border-2 border-primary select-none">
          {{ userInitials }}
        </div>
        <div class="min-w-0">
          <p class="font-title-lg text-on-surface leading-tight truncate">{{ userName }}</p>
          <p class="text-xs text-on-surface-variant truncate mt-0.5">{{ userEmail }}</p>
          <div class="flex items-center gap-1 mt-1">
            <span class="material-symbols-outlined text-[14px] text-primary">verified</span>
            <p class="font-label-md text-primary font-bold uppercase tracking-wider text-[10px]">Member</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main nav -->
    <nav class="flex flex-col gap-1.5">
      <button
        v-for="item in navItems"
        :key="item.name"
        @click="navigate(item)"
        class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-200 group w-full text-left"
        :class="isActive(item)
          ? 'bg-primary text-on-primary shadow-md shadow-primary/20'
          : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface'"
      >
        <span
          class="material-symbols-outlined"
          :style="isActive(item) ? 'font-variation-settings: \'FILL\' 1;' : ''"
        >{{ item.icon }}</span>
        <span class="font-title-md">{{ item.name }}</span>
        <span v-if="isActive(item)" class="ml-auto material-symbols-outlined text-[18px]">chevron_right</span>
      </button>
    </nav>

    <!-- Footer nav -->
    <div class="mt-auto flex flex-col gap-1.5 pt-4 border-t border-outline-variant">
      <button
        v-for="item in footerItems"
        :key="item.name"
        @click="navigate(item)"
        class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-200 w-full text-left"
        :class="isActive(item)
          ? 'bg-primary text-on-primary shadow-md shadow-primary/20'
          : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface'"
      >
        <span
          class="material-symbols-outlined"
          :style="isActive(item) ? 'font-variation-settings: \'FILL\' 1;' : ''"
        >{{ item.icon }}</span>
        <span class="font-title-md">{{ item.name }}</span>
        <span v-if="isActive(item)" class="ml-auto material-symbols-outlined text-[18px]">chevron_right</span>
      </button>

      <button
        @click="handleLogout"
        class="flex items-center gap-4 text-error hover:bg-error/10 rounded-xl px-4 py-3 transition-all duration-200 mt-2 w-full text-left"
      >
        <span class="material-symbols-outlined">logout</span>
        <span class="font-title-md font-bold">Sign Out</span>
      </button>
    </div>
  </aside>
</template>
