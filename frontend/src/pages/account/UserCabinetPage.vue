<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

import AccountSidebar      from '@/components/account/AccountSidebar.vue';
import AccountDashboardTab from '@/components/account/tabs/AccountDashboardTab.vue';
import AccountOrdersTab    from '@/components/account/tabs/AccountOrdersTab.vue';
import AccountFavoritesTab from '@/components/account/tabs/AccountFavoritesTab.vue';
import AccountCompareTab   from '@/components/account/tabs/AccountCompareTab.vue';
import AccountSettingsTab  from '@/components/account/tabs/AccountSettingsTab.vue';
import AccountSupportTab   from '@/components/account/tabs/AccountSupportTab.vue';

const route     = useRoute();
const router    = useRouter();
const authStore = useAuthStore();

const activeTab = computed(() => route.query.tab || 'dashboard');
const userName  = computed(() => authStore.user?.name || 'User');

const tabTitles = {
  dashboard: 'Account Dashboard',
  orders:    'Order History',
  favorites: 'My Favorites',
  compare:   'Compare Products',
  settings:  'Account Settings',
  support:   'Help & Support',
};

const navTabs = [
  { label: 'Dashboard', icon: 'dashboard',     tab: 'dashboard' },
  { label: 'Orders',    icon: 'shopping_bag',   tab: 'orders' },
  { label: 'Favorites', icon: 'favorite',       tab: 'favorites' },
  { label: 'Compare',   icon: 'compare_arrows', tab: 'compare' },
  { label: 'Settings',  icon: 'settings',       tab: 'settings' },
  { label: 'Support',   icon: 'help',           tab: 'support' },
];

const selectTab = (tab) => router.push({ name: 'account', query: { tab } });

const tabComponents = {
  dashboard: AccountDashboardTab,
  orders:    AccountOrdersTab,
  favorites: AccountFavoritesTab,
  compare:   AccountCompareTab,
  settings:  AccountSettingsTab,
  support:   AccountSupportTab,
};

const currentTab = computed(() => tabComponents[activeTab.value] ?? AccountDashboardTab);
</script>

<template>
  <div class="max-w-container-max mx-auto flex min-h-screen bg-surface relative">

    <!-- Desktop Sidebar -->
    <AccountSidebar />

    <!-- Content Workspace -->
    <div class="flex-1 px-margin-mobile md:px-margin-desktop py-stack-lg min-w-0">

      <!-- Page Header -->
      <header class="mb-6 border-b border-outline-variant pb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface capitalize">
              {{ tabTitles[activeTab] || 'Account' }}
            </h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant">
              Welcome back, {{ userName }}! Manage your store account and order status.
            </p>
          </div>
          <div class="lg:hidden self-start flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-container/20 border border-primary/20">
            <span class="material-symbols-outlined text-[16px] text-primary">verified</span>
            <span class="text-[11px] font-bold text-primary uppercase tracking-widest">Member</span>
          </div>
        </div>
      </header>

      <!-- Mobile Navigation Scroll Bar -->
      <div class="lg:hidden mb-6 -mx-margin-mobile px-margin-mobile overflow-x-auto scrollbar-none flex gap-2 border-b border-outline-variant pb-4">
        <button
          v-for="item in navTabs"
          :key="item.tab"
          @click="selectTab(item.tab)"
          class="flex items-center gap-2 px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all shadow-sm border border-outline-variant"
          :class="activeTab === item.tab
            ? 'bg-primary text-on-primary border-primary'
            : 'bg-surface-container-lowest text-on-surface-variant hover:bg-surface-container-low'"
        >
          <span class="material-symbols-outlined text-[16px]">{{ item.icon }}</span>
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
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
