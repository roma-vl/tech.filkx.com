<script setup lang="ts">
import { computed } from "vue";
import { useRouter } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { useAuthStore } from "@/entities/user/model/authStore";

const router = useRouter();
const authStore = useAuthStore();
const cartStore = useCartStore();

const userName = computed(() => authStore.user?.name || "Клієнт");
const userEmail = computed(() => authStore.user?.email || "");

const recentOrders = [
  {
    id: "120934812",
    date: "24 Тра, 2025",
    total: 62999.0,
    status: "В дорозі - очікується завтра",
    statusIcon: "local_shipping",
    statusClass:
      "text-[#00a046] bg-emerald-500/10 border border-emerald-500/20",
    statusCode: "shipped",
    items: [
      {
        id: 3,
        slug: "lenovo-legion-5-pro",
        name: "Lenovo Legion 5 Pro 16ARH7H Storm Grey",
        price: 62999.0,
        image:
          "https://lh3.googleusercontent.com/aida-public/AB6AXuDr331B7FabLZcRGhJ_DbZowzkaew5s_GJfms-DS1LXHrCr9JrEM_qiTSvHHdcRLOQU4NygZqdg2vzSEP8qolpkbrEuPi83FukM8x4ZzJpflfXCL5i6WZw99Ro2W_kJSyPwSKmBh7aTJ89xk_sSMwhQZu0di9CfY_tYG8xsS9crK6wdrdWzCio8Ct_P6vzzIdKMqZSvWk-cI5tR8P_uuTugKKtObu44X83uzkFVwQ768UhPlN4P_9soMg2YidbSr7gU_mGJdorHV3E",
        returnWindow: "24 Чер, 2025",
      },
    ],
  },
  {
    id: "992184021",
    date: "12 Вер, 2024",
    total: 14999.0,
    status: "Доставлено 15 Вер, 2024",
    statusIcon: "check_circle",
    statusClass:
      "text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800",
    statusCode: "delivered",
    items: [
      {
        id: 4,
        slug: "sony-wh-1000xm5-black",
        name: "Бездротові навушники Sony WH-1000XM5 Black",
        price: 14999.0,
        image:
          "https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA",
      },
    ],
  },
];

const summaryStats = computed(() => [
  {
    icon: "local_shipping",
    label: "Активні доставки",
    value: "01",
    color: "text-[#00a046]",
    progress: "75%",
    progressColor: "bg-[#00a046]",
    tab: "orders",
    action: "",
    trend: "",
    trendIcon: "",
  },
  {
    icon: "shopping_bag",
    label: "Всього замовлень",
    value: String(recentOrders.length).padStart(2, "0"),
    color: "text-blue-500",
    tab: "orders",
    action: "",
    trend: "",
    trendIcon: "",
  },
  {
    icon: "favorite",
    label: "Товарів в обраному",
    value: String(cartStore.wishlist.length).padStart(2, "0"),
    color: "text-rose-500",
    tab: "favorites",
    action: "",
    trend: "",
    trendIcon: "",
  },
]);

const defaultAddress = {
  recipient: "Роман Шевченко",
  street: "вул. Хрещатик 22, кв. 14",
  city: "Київ",
  zip: "01001",
};

const go = (tab: string) => router.push({ name: "account", query: { tab } });
</script>

<template>
  <div class="space-y-6 animate-fade font-sans select-none">
    <!-- Summary Grid -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="stat in summaryStats"
        :key="stat.label"
        class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-zinc-100 dark:border-zinc-800 flex flex-col gap-2 hover:shadow-md transition-shadow relative overflow-hidden group"
      >
        <span
          class="material-symbols-outlined text-[32px] transition-transform group-hover:scale-110 duration-200"
          :class="stat.color"
          >{{ stat.icon }}</span
        >
        <p
          class="font-extrabold text-xs text-zinc-450 dark:text-zinc-500 uppercase tracking-wider mt-1"
        >
          {{ stat.label }}
        </p>
        <p
          class="font-black text-2xl md:text-3xl text-zinc-900 dark:text-white mt-1"
        >
          {{ stat.value }}
        </p>

        <div
          v-if="stat.progress"
          class="h-1.5 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden mt-3"
        >
          <div
            class="h-full rounded-full"
            :class="stat.progressColor"
            :style="{ width: stat.progress }"
          />
        </div>
        <p
          v-if="stat.trend"
          class="text-xs text-[#00a046] font-extrabold flex items-center gap-1 mt-2"
        >
          <span class="material-symbols-outlined text-[16px]">{{
            stat.trendIcon
          }}</span>
          {{ stat.trend }}
        </p>
        <button
          v-if="stat.tab"
          class="absolute top-4 right-4 text-zinc-400 hover:text-[#00a046] transition-colors"
          @click="go(stat.tab)"
        >
          <span class="material-symbols-outlined text-[20px]">open_in_new</span>
        </button>
      </div>
    </section>

    <!-- Overview grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Orders -->
      <section class="lg:col-span-2 space-y-6">
        <div class="flex items-center justify-between">
          <h2
            class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white"
          >
            Останні замовлення
          </h2>
          <button
            class="text-[#00a046] hover:text-[#00b050] text-xs md:text-sm font-extrabold hover:underline flex items-center gap-1"
            @click="go('orders')"
          >
            Усі замовлення
            <span class="material-symbols-outlined text-[16px]"
              >arrow_forward</span
            >
          </button>
        </div>
        <div class="space-y-4">
          <div
            v-for="order in recentOrders"
            :key="order.id"
            class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow"
          >
            <div
              class="bg-zinc-50 dark:bg-zinc-850 px-6 py-4 border-b border-zinc-100 dark:border-zinc-800 flex flex-wrap justify-between items-center gap-4"
            >
              <div class="flex gap-6 flex-wrap">
                <div>
                  <p
                    class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider"
                  >
                    Дата оформлення
                  </p>
                  <p
                    class="font-bold text-zinc-800 dark:text-zinc-200 text-sm mt-0.5"
                  >
                    {{ order.date }}
                  </p>
                </div>
                <div>
                  <p
                    class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider"
                  >
                    Сума замовлення
                  </p>
                  <p
                    class="font-black text-zinc-900 dark:text-white text-sm mt-0.5"
                  >
                    {{ order.total.toFixed(2) }} ₴
                  </p>
                </div>
                <div>
                  <p
                    class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-0.5"
                  >
                    Статус
                  </p>
                  <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider"
                    :class="order.statusClass"
                  >
                    <span class="material-symbols-outlined text-[12px]">{{
                      order.statusIcon
                    }}</span
                    >{{ order.status }}
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
                  <p
                    v-if="(item as any).returnWindow"
                    class="text-xs text-zinc-450 dark:text-zinc-500 mt-1.5"
                  >
                    Повернення можливе до {{ (item as any).returnWindow }}
                  </p>
                </div>
                <div class="flex gap-2.5">
                  <button
                    v-if="order.statusCode !== 'cancelled'"
                    class="border border-zinc-200 dark:border-zinc-850 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg text-xs md:text-sm font-extrabold hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all"
                    @click="go('orders')"
                  >
                    Відстежити
                  </button>
                  <button
                    class="bg-[#00a046] hover:bg-[#00b050] text-white px-4 py-2 rounded-lg text-xs md:text-sm font-extrabold transition-all"
                    @click="cartStore.addToCart(item as any)"
                  >
                    Повторити
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.25s ease-out forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
