<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { store } from '@/store.js';
import { useAuthStore } from '@/stores/auth.js';

const router = useRouter();
const authStore = useAuthStore();

const userName  = computed(() => authStore.user?.name  || 'User');
const userEmail = computed(() => authStore.user?.email || '');

const summaryStats = [
  { icon: 'local_shipping',      label: 'Active Shipments', value: '01',       color: 'text-primary',   progress: '75%', progressColor: 'bg-primary-container', tab: 'orders' },
  { icon: 'account_balance_wallet', label: 'Member Savings', value: '$428.50', color: 'text-secondary', trend: '+12% this year', trendIcon: 'trending_up', tab: 'dashboard' },
  { icon: 'verified',            label: 'Reward Points',    value: '15,400',   color: 'text-tertiary',  action: 'Redeem Now' },
];

const recentOrders = [
  {
    id: '120934812', date: 'Oct 24, 2024', total: 1299.00,
    status: 'In Transit - Arriving Tomorrow',
    statusIcon: 'local_shipping',
    statusClass: 'text-primary bg-primary/10 border border-primary/20',
    statusCode: 'shipped',
    items: [{ id: 101, name: 'FilkxTech ProBook 16" - M3 Max / 32GB RAM', price: 1299.00,
              image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBuVg-kXP_Dz4eDubPsXwLRjC9ddkd7vuALwu9d44EXhvUcmau1ettEqBuExcpfD_u05Iro8mRrCfjRLlEyElDWNK2XCXugMhg8BlFQmzkH5QXS1DmI_-nGJmj7Qj2nzbNxTaMQKQp0bQWxjEJSBQKmRMm8yVY7heCmjBY2zXzrTzybDqI72Tff2a3iARsbv4capMzqEVs456EoLHm-kOY-mlW9RKHwerz8Fm73OO4YjBf74fI_5VFz-bK8GP4E1iscPSLxhUpP1ho',
              returnWindow: 'Nov 24, 2024' }],
  },
  {
    id: '992184021', date: 'Sept 12, 2024', total: 349.99,
    status: 'Delivered Sept 15, 2024',
    statusIcon: 'check_circle',
    statusClass: 'text-on-surface-variant bg-surface-container-high border border-outline-variant',
    statusCode: 'delivered',
    items: [{ id: 105, name: 'FilkxTech SonicPro ANC Headphones', price: 349.99,
              image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA' }],
  },
];

const defaultAddress = { recipient: 'John Doe', street: '123 Emerald Ave, Suite 400', city: 'Seattle', zip: '98101' };

const handleRedeem = () => store.addToast('Successfully redeemed 1,000 points for a $10 discount!', 'success');
const go = (tab) => router.push({ name: 'account', query: { tab } });
</script>

<template>
  <div class="space-y-stack-lg animate-fade">
    <!-- Summary Grid -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
      <div v-for="stat in summaryStats" :key="stat.label"
           class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant flex flex-col gap-2 hover:shadow-md transition-shadow relative overflow-hidden group">
        <span class="material-symbols-outlined text-[32px] transition-transform group-hover:scale-110 duration-200" :class="stat.color">{{ stat.icon }}</span>
        <p class="font-label-md text-label-md text-on-surface-variant uppercase">{{ stat.label }}</p>
        <p class="font-display-lg text-headline-lg text-on-surface">{{ stat.value }}</p>

        <div v-if="stat.progress" class="h-1 w-full bg-surface-container rounded-full overflow-hidden mt-2">
          <div class="h-full" :class="stat.progressColor" :style="{ width: stat.progress }"></div>
        </div>
        <p v-if="stat.trend" class="text-[12px] text-primary font-semibold flex items-center gap-1">
          <span class="material-symbols-outlined text-[14px]">{{ stat.trendIcon }}</span> {{ stat.trend }}
        </p>
        <button v-if="stat.action" @click="handleRedeem" class="text-primary text-label-md font-bold uppercase tracking-wider flex items-center gap-1 mt-2 text-left hover:underline">
          {{ stat.action }} <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </button>
        <button v-else-if="stat.tab" @click="go(stat.tab)" class="absolute top-4 right-4 text-on-surface-variant hover:text-primary transition-colors">
          <span class="material-symbols-outlined text-[20px]">open_in_new</span>
        </button>
      </div>
    </section>

    <!-- Overview grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-gutter">
      <!-- Recent Orders -->
      <section class="lg:col-span-2 space-y-gutter">
        <div class="flex items-center justify-between">
          <h2 class="font-title-lg text-on-surface text-lg">Recent Orders</h2>
          <button @click="go('orders')" class="text-primary text-xs font-bold hover:underline flex items-center gap-1">
            View All Orders <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
          </button>
        </div>
        <div class="space-y-gutter">
          <div v-for="order in recentOrders" :key="order.id"
               class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="bg-surface-container-low px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
              <div class="flex gap-6 flex-wrap">
                <div><p class="text-label-md text-on-surface-variant uppercase text-[10px]">Order Placed</p><p class="font-title-md text-on-surface text-sm">{{ order.date }}</p></div>
                <div><p class="text-label-md text-on-surface-variant uppercase text-[10px]">Total</p><p class="font-title-md text-on-surface text-sm">${{ order.total.toFixed(2) }}</p></div>
                <div>
                  <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Status</p>
                  <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider" :class="order.statusClass">
                    <span class="material-symbols-outlined text-[12px]">{{ order.statusIcon }}</span>{{ order.status }}
                  </span>
                </div>
              </div>
              <button @click="$emit('details', order)" class="text-primary font-bold text-label-md hover:underline text-[11px]">View Details</button>
            </div>
            <div class="p-6">
              <div v-for="item in order.items" :key="item.name" class="flex gap-4 flex-col sm:flex-row items-center sm:items-start">
                <img class="w-16 h-16 object-cover rounded-lg border border-outline-variant bg-white p-1" :src="item.image" :alt="item.name" />
                <div class="flex-1 text-center sm:text-left">
                  <h3 class="font-title-md text-on-surface text-sm line-clamp-1">{{ item.name }}</h3>
                  <p v-if="item.returnWindow" class="text-[12px] text-on-surface-variant mt-0.5">Return closes on {{ item.returnWindow }}</p>
                </div>
                <div class="flex gap-2">
                  <button v-if="order.statusCode !== 'cancelled'" @click="$emit('track', order)" class="border border-outline text-on-surface px-4 py-1.5 rounded-lg text-xs font-semibold hover:bg-surface-container-high transition-all">Track</button>
                  <button @click="store.addToCart(item)" class="bg-primary text-on-primary px-4 py-1.5 rounded-lg text-xs font-semibold hover:bg-primary-container transition-all">Reorder</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- My Information -->
      <section class="space-y-gutter">
        <h2 class="font-title-lg text-on-surface text-lg">My Information</h2>
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm space-y-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-bold text-lg border border-primary">
              {{ userName.charAt(0) }}
            </div>
            <div>
              <h3 class="font-title-md text-on-surface text-base">{{ userName }}</h3>
              <p class="text-xs text-on-surface-variant line-clamp-1">{{ userEmail }}</p>
            </div>
          </div>
          <div class="border-t border-outline-variant pt-4 space-y-2">
            <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Default Address</p>
            <p class="font-semibold text-xs text-on-surface">{{ defaultAddress.recipient }}</p>
            <p class="text-xs text-on-surface-variant">{{ defaultAddress.street }}</p>
            <p class="text-xs text-on-surface-variant">{{ defaultAddress.city }}, {{ defaultAddress.zip }}</p>
          </div>
          <div class="border-t border-outline-variant pt-4 space-y-2">
            <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Quick Actions</p>
            <div class="grid grid-cols-2 gap-2">
              <button @click="go('settings')" class="flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-primary transition-colors bg-surface-container-low hover:bg-surface-container-high rounded-lg p-2 text-left font-medium">
                <span class="material-symbols-outlined text-[16px]">edit</span> Edit Profile
              </button>
              <button @click="go('favorites')" class="flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-primary transition-colors bg-surface-container-low hover:bg-surface-container-high rounded-lg p-2 text-left font-medium">
                <span class="material-symbols-outlined text-[16px]">favorite</span> Wishlist
              </button>
              <button @click="go('support')" class="flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-primary transition-colors bg-surface-container-low hover:bg-surface-container-high rounded-lg p-2 text-left font-medium">
                <span class="material-symbols-outlined text-[16px]">help</span> Help Center
              </button>
              <a href="/catalog" class="flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-primary transition-colors bg-surface-container-low hover:bg-surface-container-high rounded-lg p-2 text-left font-medium">
                <span class="material-symbols-outlined text-[16px]">grid_view</span> Catalog
              </a>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
