<script setup>
import { store } from '@/store.js';
import AccountSidebar from '@/components/account/AccountSidebar.vue';

const summaryStats = [
  {
    icon: 'local_shipping',
    label: 'Active Shipments',
    value: '02',
    color: 'text-primary',
    progress: '66%',
    progressColor: 'bg-primary-container'
  },
  {
    icon: 'account_balance_wallet',
    label: 'Member Savings',
    value: '$428.50',
    color: 'text-secondary',
    trend: '+12% this year',
    trendIcon: 'trending_up'
  },
  {
    icon: 'verified',
    label: 'Reward Points',
    value: '15,400',
    color: 'text-tertiary',
    action: 'Redeem Now'
  }
];

const orders = [
  {
    id: '120934812',
    date: 'Oct 24, 2024',
    total: 1299.00,
    shipTo: 'John Doe',
    status: 'In Transit - Arriving Tomorrow',
    statusIcon: 'local_shipping',
    statusClass: 'text-on-primary-fixed-variant bg-primary-fixed/20',
    items: [
      {
        name: 'ElectroLux ProBook 16" - M3 Max / 32GB RAM',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBuVg-kXP_Dz4eDubPsXwLRjC9ddkd7vuALwu9d44EXhvUcmau1ettEqBuExcpfD_u05Iro8mRrCfjRLlEyElDWNK2XCXugMhg8BlFQmzkH5QXS1DmI_-nGJmj7Qj2nzbNxTaMQKQp0bQWxjEJSBQKmRMm8yVY7heCmjBY2zXzrTzybDqI72Tff2a3iARsbv4capMzqEVs456EoLHm-kOY-mlW9RKHwerz8Fm73OO4YjBf74fI_5VFz-bK8GP4E1iscPSLxhUpP1ho',
        returnWindow: 'Nov 24, 2024'
      }
    ]
  },
  {
    id: '992184021',
    date: 'Sept 12, 2024',
    total: 349.99,
    shipTo: 'John Doe',
    status: 'Delivered Sept 15, 2024',
    statusIcon: 'check_circle',
    statusClass: 'text-on-surface-variant bg-surface-container-high',
    opacity: 'opacity-90',
    items: [
      {
        name: 'ElectroLux SonicPro ANC Headphones',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA',
        note: 'Order was delivered to the front porch.'
      }
    ]
  }
];
</script>

<template>
  <div class="max-w-container-max mx-auto flex min-h-screen bg-surface relative">
    <AccountSidebar />
    <div class="flex-1 px-margin-mobile md:px-margin-desktop py-stack-lg">
      <header class="mb-stack-lg border-b border-outline-variant pb-6">
        <h1 class="font-headline-lg text-headline-lg text-on-surface">Account Dashboard</h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant">Welcome back, John! Here's what's happening with your account.</p>
      </header>

    <!-- Dashboard Summary Grid -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-gutter mb-stack-lg">
      <div v-for="stat in summaryStats" :key="stat.label"
           class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant flex flex-col gap-2 hover:shadow-md transition-shadow">
        <span class="material-symbols-outlined text-[32px]" :class="stat.color">{{ stat.icon }}</span>
        <p class="font-label-md text-label-md text-on-surface-variant uppercase">{{ stat.label }}</p>
        <p class="font-display-lg text-headline-lg text-on-surface">{{ stat.value }}</p>

        <div v-if="stat.progress" class="h-1 w-full bg-surface-container rounded-full overflow-hidden mt-2">
          <div class="h-full" :class="stat.progressColor" :style="{ width: stat.progress }"></div>
        </div>

        <p v-if="stat.trend" class="text-[12px] text-primary font-semibold flex items-center gap-1">
          <span class="material-symbols-outlined text-[14px]">{{ stat.trendIcon }}</span> {{ stat.trend }}
        </p>

        <button v-if="stat.action" class="text-primary text-label-md font-bold uppercase tracking-wider flex items-center gap-1 mt-2 text-left">
          {{ stat.action }} <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </button>
      </div>
    </section>

    <!-- Filters & Section Title -->
    <div class="flex items-center justify-between mb-stack-md flex-wrap gap-4 mt-stack-lg">
      <h2 class="font-title-lg text-on-surface">Recent Orders</h2>
      <div class="flex items-center gap-4 flex-wrap">
        <div class="flex bg-surface-container-high rounded-xl p-1">
          <button class="bg-on-surface text-surface py-2 px-6 rounded-lg font-label-md shadow-sm transition-all">All</button>
          <button class="text-on-surface-variant py-2 px-6 rounded-lg font-label-md hover:text-on-surface transition-colors">Shipped</button>
          <button class="text-on-surface-variant py-2 px-6 rounded-lg font-label-md hover:text-on-surface transition-colors">Delivered</button>
        </div>
        <button class="flex items-center gap-2 text-on-surface-variant font-label-md border border-outline-variant rounded-xl px-4 py-2 hover:bg-surface-container-high transition-all">
          <span class="material-symbols-outlined text-[18px]">filter_list</span> Filter
        </button>
      </div>
    </div>

    <!-- Order Cards List -->
    <div class="space-y-gutter">
      <div v-for="order in orders" :key="order.id"
           class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow"
           :class="order.opacity">

        <div class="bg-surface-container-low px-6 py-4 border-b border-outline-variant flex flex-wrap justify-between items-center gap-4">
          <div class="flex gap-stack-lg flex-wrap">
            <div>
              <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Order Placed</p>
              <p class="font-title-md text-on-surface text-sm">{{ order.date }}</p>
            </div>
            <div>
              <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Total</p>
              <p class="font-title-md text-on-surface text-sm">${{ order.total.toFixed(2) }}</p>
            </div>
            <div>
              <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Ship To</p>
              <button class="font-title-md text-primary flex items-center gap-1 text-sm">
                {{ order.shipTo }} <span class="material-symbols-outlined text-[16px]">keyboard_arrow_down</span>
              </button>
            </div>
          </div>
          <div class="flex flex-col items-end">
            <p class="text-label-md text-on-surface-variant uppercase text-[10px]">Order #{{ order.id }}</p>
            <a class="text-primary font-bold text-label-md hover:underline text-[11px]" href="#">View Order Details</a>
          </div>
        </div>

        <div class="p-6">
          <div class="flex items-start justify-between mb-6 flex-wrap gap-4">
            <div class="flex items-center gap-2 px-3 py-1 rounded-full" :class="order.statusClass">
              <span class="material-symbols-outlined text-[18px]">{{ order.statusIcon }}</span>
              <span class="font-label-md font-bold uppercase text-[11px]">{{ order.status }}</span>
            </div>
            <button class="text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2 text-label-md font-semibold text-xs">
              <span class="material-symbols-outlined text-[18px]">track_changes</span> Track Shipment
            </button>
          </div>

          <div v-for="item in order.items" :key="item.name" class="flex gap-stack-lg flex-col sm:flex-row">
            <img class="w-24 h-24 object-cover rounded-lg border border-outline-variant" :src="item.image" :alt="item.name" />
            <div class="flex-1">
              <h3 class="font-title-lg text-title-lg text-on-surface text-lg">{{ item.name }}</h3>
              <p v-if="item.returnWindow" class="text-body-md text-on-surface-variant mt-1 text-sm text-balance">Return window closes on {{ item.returnWindow }}</p>
              <p v-if="item.note" class="text-body-md text-on-surface-variant mt-1 text-sm">{{ item.note }}</p>
              <div class="mt-4 flex gap-4 flex-wrap">
                <button class="bg-primary-container text-on-primary-container px-6 py-2 rounded-lg font-title-md hover:brightness-95 transition-all text-sm">Buy It Again</button>
                <button class="border border-outline text-on-surface px-6 py-2 rounded-lg font-title-md hover:bg-surface-container-high transition-all text-sm">
                  {{ item.note ? 'Write a Review' : 'Product Support' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="mt-stack-lg flex justify-center">
      <nav class="flex items-center gap-2">
        <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-container-high">
          <span class="material-symbols-outlined">chevron_left</span>
        </button>
        <button class="w-10 h-10 flex items-center justify-center rounded-lg bg-primary text-on-primary font-bold">1</button>
        <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-container-high font-medium">2</button>
        <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-container-high font-medium">3</button>
        <button class="w-10 h-10 flex items-center justify-center rounded-lg border border-outline-variant hover:bg-surface-container-high">
          <span class="material-symbols-outlined">chevron_right</span>
        </button>
      </nav>
    </div>
    </div>
  </div>
</template>
