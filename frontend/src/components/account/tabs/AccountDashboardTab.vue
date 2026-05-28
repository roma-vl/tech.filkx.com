<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { store } from '@/store.js';
import { useAuthStore } from '@/stores/auth.js';

const router = useRouter();
const authStore = useAuthStore();

const userName  = computed(() => authStore.user?.name  || 'Клієнт');
const userEmail = computed(() => authStore.user?.email || '');

const summaryStats = [
  { icon: 'local_shipping',      label: 'Активні доставки', value: '01',       color: 'text-[#00a046]',   progress: '75%', progressColor: 'bg-[#00a046]', tab: 'orders' },
  { icon: 'account_balance_wallet', label: 'Заощаджено коштів', value: '4 280 ₴', color: 'text-amber-500', trend: '+12% цього року', trendIcon: 'trending_up', tab: 'dashboard' },
  { icon: 'verified',            label: 'Бонусні бали',    value: '15 400',   color: 'text-blue-500',  action: 'Використати зараз' },
];

const recentOrders = [
  {
    id: '120934812', date: '24 Тра, 2025', total: 12990.00,
    status: 'В дорозі - очікується завтра',
    statusIcon: 'local_shipping',
    statusClass: 'text-[#00a046] bg-emerald-500/10 border border-emerald-500/20',
    statusCode: 'shipped',
    items: [{ id: 101, name: 'Ноутбук FilkxTech ProBook 16" - M3 Max / 32GB RAM / 1TB SSD', price: 12990.00,
              image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBuVg-kXP_Dz4eDubPsXwLRjC9ddkd7vuALwu9d44EXhvUcmau1ettEqBuExcpfD_u05Iro8mRrCfjRLlEyElDWNK2XCXugMhg8BlFQmzkH5QXS1DmI_-nGJmj7Qj2nzbNxTaMQKQp0bQWxjEJSBQKmRMm8yVY7heCmjBY2zXzrTzybDqI72Tff2a3iARsbv4capMzqEVs456EoLHm-kOY-mlW9RKHwerz8Fm73OO4YjBf74fI_5VFz-bK8GP4E1iscPSLxhUpP1ho',
              returnWindow: '24 Чер, 2025' }],
  },
  {
    id: '992184021', date: '12 Вер, 2024', total: 3499.00,
    status: 'Доставлено 15 Вер, 2024',
    statusIcon: 'check_circle',
    statusClass: 'text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700',
    statusCode: 'delivered',
    items: [{ id: 105, name: 'Бездротові навушники FilkxTech SonicPro ANC Black', price: 3499.00,
              image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA' }],
  },
];

const defaultAddress = { recipient: 'Роман Шевченко', street: 'вул. Хрещатик 22, кв. 14', city: 'Київ', zip: '01001' };

const handleRedeem = () => store.addToast('Успішно використано 1000 бонусів для отримання знижки 100 ₴!', 'success');
const go = (tab) => router.push({ name: 'account', query: { tab } });
</script>

<template>
  <div class="space-y-6 animate-fade font-sans select-none">
    <!-- Summary Grid -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="stat in summaryStats" :key="stat.label"
           class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-zinc-100 dark:border-zinc-800 flex flex-col gap-2 hover:shadow-md transition-shadow relative overflow-hidden group">
        <span class="material-symbols-outlined text-[32px] transition-transform group-hover:scale-110 duration-200" :class="stat.color">{{ stat.icon }}</span>
        <p class="font-extrabold text-xs text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mt-1">{{ stat.label }}</p>
        <p class="font-black text-2xl md:text-3xl text-zinc-900 dark:text-white mt-1">{{ stat.value }}</p>

        <div v-if="stat.progress" class="h-1.5 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden mt-3">
          <div class="h-full rounded-full" :class="stat.progressColor" :style="{ width: stat.progress }"></div>
        </div>
        <p v-if="stat.trend" class="text-xs text-[#00a046] font-extrabold flex items-center gap-1 mt-2">
          <span class="material-symbols-outlined text-[16px]">{{ stat.trendIcon }}</span> {{ stat.trend }}
        </p>
        <button v-if="stat.action" @click="handleRedeem" class="text-[#00a046] hover:text-[#00b050] text-xs font-extrabold uppercase tracking-wider flex items-center gap-1 mt-3 text-left hover:underline">
          {{ stat.action }} <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </button>
        <button v-else-if="stat.tab" @click="go(stat.tab)" class="absolute top-4 right-4 text-zinc-400 hover:text-[#00a046] transition-colors">
          <span class="material-symbols-outlined text-[20px]">open_in_new</span>
        </button>
      </div>
    </section>

    <!-- Overview grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Orders -->
      <section class="lg:col-span-2 space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white">Останні замовлення</h2>
          <button @click="go('orders')" class="text-[#00a046] hover:text-[#00b050] text-xs md:text-sm font-extrabold hover:underline flex items-center gap-1">
            Усі замовлення <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </button>
        </div>
        <div class="space-y-4">
          <div v-for="order in recentOrders" :key="order.id"
               class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow">
            <div class="bg-zinc-50 dark:bg-zinc-850 px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex flex-wrap justify-between items-center gap-4">
              <div class="flex gap-6 flex-wrap">
                <div><p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Дата оформлення</p><p class="font-bold text-zinc-800 dark:text-zinc-200 text-sm mt-0.5">{{ order.date }}</p></div>
                <div><p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Сума замовлення</p><p class="font-black text-zinc-900 dark:text-white text-sm mt-0.5">{{ order.total.toFixed(2) }} ₴</p></div>
                <div>
                  <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Статус</p>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider" :class="order.statusClass">
                    <span class="material-symbols-outlined text-[12px]">{{ order.statusIcon }}</span>{{ order.status }}
                  </span>
                </div>
              </div>
              <button @click="go('orders')" class="text-[#00a046] hover:text-[#00b050] font-extrabold text-xs hover:underline">Детальніше</button>
            </div>
            <div class="p-6">
              <div v-for="item in order.items" :key="item.name" class="flex gap-4 flex-col sm:flex-row items-center sm:items-start">
                <img class="w-16 h-16 object-contain rounded-lg border border-zinc-100 dark:border-zinc-800 bg-white p-1" :src="item.image" :alt="item.name" />
                <div class="flex-1 text-center sm:text-left">
                  <h3 class="font-extrabold text-zinc-800 dark:text-zinc-200 text-sm md:text-base leading-snug line-clamp-2">{{ item.name }}</h3>
                  <p v-if="item.returnWindow" class="text-xs text-zinc-450 dark:text-zinc-500 mt-1.5">Повернення можливе до {{ item.returnWindow }}</p>
                </div>
                <div class="flex gap-2.5">
                  <button v-if="order.statusCode !== 'cancelled'" @click="go('orders')" class="border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg text-xs md:text-sm font-extrabold hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all">Відстежити</button>
                  <button @click="store.addToCart(item)" class="bg-[#00a046] hover:bg-[#00b050] text-white px-4 py-2 rounded-lg text-xs md:text-sm font-extrabold transition-all">Повторити</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- My Information -->
      <section class="space-y-6">
        <h2 class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white">Особисті дані</h2>
        <div class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-6">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-emerald-500/10 text-[#00a046] flex items-center justify-center font-black text-xl border border-emerald-500/20">
              {{ userName.charAt(0) }}
            </div>
            <div>
              <h3 class="font-extrabold text-zinc-855 dark:text-zinc-150 text-base leading-snug">{{ userName }}</h3>
              <p class="text-xs md:text-sm text-zinc-400 dark:text-zinc-500 line-clamp-1 mt-0.5">{{ userEmail }}</p>
            </div>
          </div>
          <div class="border-t border-zinc-100 dark:border-zinc-800 pt-5 space-y-2">
            <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Адреса доставки за замовчуванням</p>
            <p class="font-extrabold text-sm text-zinc-800 dark:text-zinc-200">{{ defaultAddress.recipient }}</p>
            <p class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400 mt-1">{{ defaultAddress.street }}</p>
            <p class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400">м. {{ defaultAddress.city }}, {{ defaultAddress.zip }}</p>
          </div>
          <div class="border-t border-zinc-100 dark:border-zinc-800 pt-5 space-y-2.5">
            <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Швидкі дії</p>
            <div class="grid grid-cols-2 gap-2">
              <button @click="go('settings')" class="flex items-center gap-2 text-xs md:text-sm text-zinc-650 dark:text-zinc-350 hover:text-[#00a046] dark:hover:text-[#00b050] transition-colors bg-zinc-50 dark:bg-zinc-850 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg p-2.5 text-left font-extrabold">
                <span class="material-symbols-outlined text-[18px]">edit</span> Профіль
              </button>
              <button @click="go('favorites')" class="flex items-center gap-2 text-xs md:text-sm text-zinc-650 dark:text-zinc-350 hover:text-[#00a046] dark:hover:text-[#00b050] transition-colors bg-zinc-50 dark:bg-zinc-850 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg p-2.5 text-left font-extrabold">
                <span class="material-symbols-outlined text-[18px]">favorite</span> Обране
              </button>
              <button @click="go('support')" class="flex items-center gap-2 text-xs md:text-sm text-zinc-650 dark:text-zinc-350 hover:text-[#00a046] dark:hover:text-[#00b050] transition-colors bg-zinc-50 dark:bg-zinc-850 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg p-2.5 text-left font-extrabold">
                <span class="material-symbols-outlined text-[18px]">help</span> Підтримка
              </button>
              <a href="/catalog" class="flex items-center gap-2 text-xs md:text-sm text-zinc-650 dark:text-zinc-350 hover:text-[#00a046] dark:hover:text-[#00b050] transition-colors bg-zinc-50 dark:bg-zinc-850 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg p-2.5 text-left font-extrabold">
                <span class="material-symbols-outlined text-[18px]">grid_view</span> Каталог
              </a>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.animate-fade { animation: fadeIn .25s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
</style>
