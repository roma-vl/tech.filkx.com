<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { useAuthStore } from "@/entities/user/model/authStore";
import api from "@/shared/services/api/apiClient";

const router = useRouter();
const authStore = useAuthStore();
const cartStore = useCartStore();

const ordersList = ref<any[]>([]);
const isLoading = ref(false);

const fetchOrders = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/user/orders");
    ordersList.value = response.data.data || [];
  } catch (error) {
    console.error("Failed to fetch user orders:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchOrders();
});

const recentOrders = computed(() => ordersList.value.slice(0, 3));

const go = (tab: string) => router.push({ name: "account", query: { tab } });
</script>

<template>
  <div class="space-y-6 animate-fade font-sans select-none">

    <!-- Stats row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
      <button
        v-for="stat in [
          { label: 'Замовлень', value: isLoading ? null : ordersList.length, icon: 'shopping_bag', tab: 'orders', color: 'text-[#00a046]', bg: 'bg-emerald-50 dark:bg-emerald-900/20' },
          { label: 'В обраному', value: cartStore.wishlistCount, icon: 'favorite', tab: 'favorites', color: 'text-rose-500', bg: 'bg-rose-50 dark:bg-rose-900/20' },
          { label: 'В порівнянні', value: cartStore.compareCount, icon: 'compare_arrows', tab: 'compare', color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-900/20' },
          { label: 'Сповіщень', value: cartStore.unreadNotificationsCount, icon: 'notifications', tab: 'notifications', color: 'text-amber-500', bg: 'bg-amber-50 dark:bg-amber-900/20' },
        ]"
        :key="stat.label"
        class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-4 flex items-center gap-3 cursor-pointer hover:shadow-md hover:border-zinc-200 dark:hover:border-zinc-700 transition-all group text-left w-full"
        @click="go(stat.tab)"
      >
        <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" :class="stat.bg">
          <span class="material-symbols-outlined text-[20px]" :class="stat.color">{{ stat.icon }}</span>
        </div>
        <div class="min-w-0 flex-1">
          <div v-if="stat.value === null" class="h-5 w-10 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse mb-0.5" />
          <p v-else class="text-xl font-black text-zinc-900 dark:text-white leading-none">{{ stat.value }}</p>
          <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5 font-medium truncate">{{ stat.label }}</p>
        </div>
        <span class="material-symbols-outlined text-[15px] text-zinc-300 dark:text-zinc-700 group-hover:text-zinc-400 dark:group-hover:text-zinc-500 transition-colors shrink-0">chevron_right</span>
      </button>
    </div>

    <!-- Quick actions -->
    <div class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-5">
      <h3 class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mb-4">Швидкий доступ</h3>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <button
          v-for="action in [
            { label: 'До каталогу', icon: 'storefront', color: 'text-[#00a046]', bg: 'bg-emerald-50 dark:bg-emerald-900/20', fn: () => router.push('/catalog') },
            { label: 'Мої замовлення', icon: 'receipt_long', color: 'text-[#00a046]', bg: 'bg-emerald-50 dark:bg-emerald-900/20', fn: () => go('orders') },
            { label: 'Налаштування', icon: 'manage_accounts', color: 'text-zinc-500 dark:text-zinc-400', bg: 'bg-zinc-100 dark:bg-zinc-800', fn: () => go('settings') },
            { label: 'Підтримка', icon: 'support_agent', color: 'text-blue-500', bg: 'bg-blue-50 dark:bg-blue-900/20', fn: () => go('support') },
          ]"
          :key="action.label"
          class="flex flex-col items-center gap-2.5 p-4 rounded-xl border border-zinc-100 dark:border-zinc-800 hover:border-[#00a046]/25 hover:bg-emerald-50/40 dark:hover:bg-emerald-900/10 transition-all group"
          @click="action.fn()"
        >
          <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="action.bg">
            <span class="material-symbols-outlined text-[20px] group-hover:scale-110 transition-transform" :class="action.color">{{ action.icon }}</span>
          </div>
          <span class="text-xs font-bold text-zinc-600 dark:text-zinc-400 leading-tight text-center">{{ action.label }}</span>
        </button>
      </div>
    </div>

    <!-- Recent Orders -->
    <section class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white">
          Останні замовлення
        </h2>
        <button
          class="text-[#00a046] hover:text-[#00b050] text-xs md:text-sm font-extrabold hover:underline flex items-center gap-1"
          @click="go('orders')"
        >
          Усі замовлення
          <span class="material-symbols-outlined text-[16px]">chevron_right</span>
        </button>
      </div>

      <!-- Skeleton loading -->
      <div v-if="isLoading" class="space-y-3">
        <div
          v-for="i in 2"
          :key="i"
          class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl overflow-hidden"
        >
          <div class="bg-zinc-50 dark:bg-zinc-850 px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex gap-6">
            <div class="h-3 w-24 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse" />
            <div class="h-3 w-20 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse" />
            <div class="h-4 w-16 bg-zinc-200 dark:bg-zinc-800 rounded-lg animate-pulse" />
          </div>
          <div class="p-6 flex gap-4 items-center">
            <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-lg animate-pulse shrink-0" />
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse w-3/4" />
              <div class="h-3 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse w-1/3" />
            </div>
            <div class="flex gap-2 shrink-0">
              <div class="h-8 w-20 bg-zinc-100 dark:bg-zinc-800 rounded-lg animate-pulse" />
              <div class="h-8 w-20 bg-zinc-100 dark:bg-zinc-800 rounded-lg animate-pulse" />
            </div>
          </div>
        </div>
      </div>

      <!-- Orders list -->
      <template v-else-if="recentOrders.length > 0">
        <div
          v-for="order in recentOrders"
          :key="order.id"
          class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="bg-zinc-50 dark:bg-zinc-850 px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex flex-wrap justify-between items-center gap-4">
            <div class="flex gap-6 flex-wrap">
              <div>
                <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Дата оформлення</p>
                <p class="font-bold text-zinc-800 dark:text-zinc-200 text-sm mt-0.5">{{ order.date }}</p>
              </div>
              <div>
                <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Сума замовлення</p>
                <p class="font-black text-zinc-900 dark:text-white text-sm mt-0.5">{{ order.total.toFixed(2) }} ₴</p>
              </div>
              <div>
                <p class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5">Статус</p>
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider"
                  :class="order.statusClass"
                >
                  <span class="material-symbols-outlined text-[12px]">{{ order.statusIcon }}</span>
                  {{ order.status }}
                </span>
              </div>
            </div>
            <button
              class="text-[#00a046] hover:text-[#00b050] font-extrabold text-xs hover:underline"
              @click="go('orders')"
            >
              Детальніше
            </button>
          </div>
          <div class="p-6">
            <div
              v-for="item in order.items"
              :key="item.name"
              class="flex gap-4 flex-col sm:flex-row items-center sm:items-start"
            >
              <img
                class="w-16 h-16 object-contain rounded-lg border border-zinc-100 dark:border-zinc-800 bg-white p-1 cursor-pointer hover:border-[#00a046]/40 transition-colors"
                :src="item.image"
                :alt="item.name"
                @click="cartStore.viewProduct(item as any)"
              />
              <div class="flex-1 text-center sm:text-left">
                <h3
                  class="font-extrabold text-zinc-850 dark:text-zinc-200 text-sm md:text-base leading-snug line-clamp-2 cursor-pointer hover:text-[#00a046] transition-colors"
                  @click="cartStore.viewProduct(item as any)"
                >
                  {{ item.name }}
                </h3>
                <p v-if="(item as any).returnWindow" class="text-xs text-zinc-450 dark:text-zinc-500 mt-1.5">
                  Повернення можливе до {{ (item as any).returnWindow }}
                </p>
              </div>
              <div class="flex gap-2.5">
                <button
                  v-if="order.statusCode !== 'cancelled'"
                  class="border border-zinc-200 dark:border-zinc-850 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg text-xs font-extrabold hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-all"
                  @click="go('orders')"
                >
                  Відстежити
                </button>
                <button
                  class="bg-[#00a046] hover:bg-[#00b050] text-white px-4 py-2 rounded-lg text-xs font-extrabold transition-all"
                  @click="cartStore.addToCart(item as any)"
                >
                  Повторити
                </button>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- Empty state -->
      <div
        v-else
        class="bg-white dark:bg-zinc-900 border border-dashed border-zinc-200 dark:border-zinc-800 p-10 rounded-xl text-center"
      >
        <span class="material-symbols-outlined text-[40px] text-zinc-300 dark:text-zinc-700 mb-2">shopping_bag</span>
        <p class="text-zinc-500 dark:text-zinc-400 text-sm font-bold">У вас ще немає замовлень</p>
        <p class="text-zinc-400 dark:text-zinc-500 text-xs mt-1">Оформіть своє перше замовлення в магазині!</p>
        <button
          class="mt-4 bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2.5 rounded-lg text-xs font-bold transition-all"
          @click="router.push('/catalog')"
        >
          Перейти до каталогу
        </button>
      </div>
    </section>
  </div>
</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.25s ease-out forwards;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
