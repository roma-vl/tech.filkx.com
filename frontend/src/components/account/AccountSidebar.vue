<script setup>
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();

const navItems = [
  { name: 'Dashboard', icon: 'dashboard', routeName: 'account', query: { tab: 'dashboard' } },
  { name: 'Catalog', icon: 'grid_view', routeName: 'catalog' },
  { name: 'Orders', icon: 'shopping_bag', routeName: 'account', query: { tab: 'orders' } },
  { name: 'Favorites', icon: 'favorite', routeName: 'account', query: { tab: 'favorites' } },
  { name: 'Compare', icon: 'compare_arrows', routeName: 'account', query: { tab: 'compare' } },
];

const footerItems = [
  { name: 'Settings', icon: 'settings', routeName: 'account', query: { tab: 'settings' } },
  { name: 'Support', icon: 'help', routeName: 'account', query: { tab: 'support' } },
];

const isActive = (item) => {
  if (item.routeName !== route.name) return false;
  if (item.query && item.query.tab) {
    const activeTab = route.query.tab || 'dashboard';
    return activeTab === item.query.tab;
  }
  return !route.query.tab && item.routeName === route.name;
};
</script>

<template>
  <aside class="hidden lg:flex flex-col sticky top-[80px] h-[calc(100vh-80px)] w-72 bg-surface-container-low p-6 gap-stack-lg border-r border-outline-variant">
    <div class="flex flex-col gap-1 pb-4 border-b border-outline-variant">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container text-xl font-bold border-2 border-primary">
          JD
        </div>
        <div>
          <p class="font-title-lg text-on-surface leading-tight">Welcome back</p>
          <div class="flex items-center gap-1 mt-1">
            <span class="material-symbols-outlined text-[14px] text-primary">verified</span>
            <p class="font-label-md text-primary font-bold uppercase tracking-wider text-[10px]">Premium Member</p>
          </div>
        </div>
      </div>
    </div>

    <nav class="flex flex-col gap-1.5">
      <router-link
        v-for="item in navItems"
        :key="item.name"
        :to="item.query ? { name: item.routeName, query: item.query } : { name: item.routeName }"
        class="flex items-center gap-4 px-4 py-3 rounded-xl transition-all duration-200 group"
        :class="isActive(item) ? 'bg-primary text-on-primary shadow-md shadow-primary/20' : 'text-on-surface-variant hover:bg-surface-variant hover:text-on-surface'"
      >
        <span class="material-symbols-outlined" :style="isActive(item) ? 'font-variation-settings: \'FILL\' 1;' : ''">{{ item.icon }}</span>
        <span class="font-title-md">{{ item.name }}</span>
        <span v-if="isActive(item)" class="ml-auto material-symbols-outlined text-[18px]">chevron_right</span>
      </router-link>
    </nav>

    <div class="mt-auto flex flex-col gap-1.5 pt-4 border-t border-outline-variant">
      <router-link
        v-for="item in footerItems"
        :key="item.name"
        :to="item.query ? { name: item.routeName, query: item.query } : { name: item.routeName }"
        class="flex items-center gap-4 text-on-surface-variant hover:bg-surface-variant hover:text-on-surface rounded-xl px-4 py-3 transition-all duration-200"
      >
        <span class="material-symbols-outlined">{{ item.icon }}</span>
        <span class="font-title-md">{{ item.name }}</span>
      </router-link>

      <button class="flex items-center gap-4 text-error hover:bg-error/10 rounded-xl px-4 py-3 transition-all duration-200 mt-2">
        <span class="material-symbols-outlined">logout</span>
        <span class="font-title-md font-bold">Sign Out</span>
      </button>
    </div>
  </aside>
</template>
