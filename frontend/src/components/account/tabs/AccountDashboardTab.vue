<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { useAuthStore } from "@/entities/user/model/authStore";
import api from "@/services/api";

const router = useRouter();
const authStore = useAuthStore();
const cartStore = useCartStore();

const userName = computed(() => authStore.user?.name || "Клієнт");
const userEmail = computed(() => authStore.user?.email || "");

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

// Get the last 3 orders
const recentOrders = computed(() => {
  return ordersList.value.slice(0, 3);
});

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
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
          </button>
        </div>
        <div class="space-y-4">
          <!-- Loading state -->
          <div
            v-if="isLoading"
            class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 p-8 rounded-xl flex flex-col items-center justify-center gap-3 shadow-sm"
          >
            <div
              class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-[#00a046]"
            />
            <p class="text-zinc-500 dark:text-zinc-400 text-sm font-medium">
              Завантаження...
            </p>
          </div>

          <!-- Orders list -->
          <template v-else-if="recentOrders.length > 0">
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
                      }}</span>
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
          </template>

          <!-- Empty state -->
          <div
            v-else
            class="bg-white dark:bg-zinc-900 border border-dashed border-zinc-200 dark:border-zinc-800 p-10 rounded-xl text-center shadow-xs"
          >
            <span
              class="material-symbols-outlined text-[40px] text-zinc-300 dark:text-zinc-700 mb-2"
              >shopping_bag</span
            >
            <p class="text-zinc-500 dark:text-zinc-400 text-sm font-bold">
              У вас ще немає замовлень
            </p>
            <p class="text-zinc-400 dark:text-zinc-500 text-xs mt-1">
              Оформіть своє перше замовлення в магазині!
            </p>
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
