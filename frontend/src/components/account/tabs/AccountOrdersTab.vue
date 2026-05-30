<script setup>
import { ref, computed } from "vue";
import { store } from "@/store.js";

const defaultOrders = [
  {
    id: "120934812",
    date: "24 Жов, 2025",
    total: 63149.0,
    shipTo: "Роман Шевченко",
    status: "В дорозі - прибуває завтра",
    statusIcon: "local_shipping",
    statusClass:
      "text-[#00a046] bg-emerald-500/10 border border-emerald-500/20",
    statusCode: "shipped",
    trackingSteps: [
      { name: "Замовлення створено", date: "24 Жов, 2025 10:00", done: true },
      {
        name: "Обробка та комплектування",
        date: "24 Жов, 2025 14:00",
        done: true,
      },
      { name: "Передано кур'єру", date: "25 Жов, 2025 09:00", done: true },
      {
        name: "Доставка отримувачу",
        date: "Очікується 28 Жов, 2025",
        done: false,
      },
      { name: "Доставлено", date: "Очікується 29 Жов, 2025", done: false },
    ],
    shippingAddress: {
      recipient: "Роман Шевченко",
      street: "вул. Хрещатик 22, кв. 14",
      city: "Київ",
      state: "Київська обл.",
      zip: "01001",
      country: "Україна",
    },
    paymentMethod: { type: "Visa", number: "•••• 4242" },
    items: [
      {
        id: 3,
        slug: "lenovo-legion-5-pro",
        name: "Lenovo Legion 5 Pro 16ARH7H Storm Grey",
        price: 62999.0,
        returnWindow: "24 Лис, 2025",
        image:
          "https://lh3.googleusercontent.com/aida-public/AB6AXuDr331B7FabLZcRGhJ_DbZowzkaew5s_GJfms-DS1LXHrCr9JrEM_qiTSvHHdcRLOQU4NygZqdg2vzSEP8qolpkbrEuPi83FukM8x4ZzJpflfXCL5i6WZw99Ro2W_kJSyPwSKmBh7aTJ89xk_sSMwhQZu0di9CfY_tYG8xsS9crK6wdrdWzCio8Ct_P6vzzIdKMqZSvWk-cI5tR8P_uuTugKKtObu44X83uzkFVwQ768UhPlN4P_9soMg2YidbSr7gU_mGJdorHV3E",
      },
    ],
  },
  {
    id: "992184021",
    date: "12 Вер, 2025",
    total: 15149.0,
    shipTo: "Роман Шевченко",
    status: "Доставлено 15 Вер, 2025",
    statusIcon: "check_circle",
    statusClass:
      "text-zinc-500 dark:text-zinc-455 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-700",
    statusCode: "delivered",
    trackingSteps: [
      { name: "Замовлення створено", date: "12 Вер, 2025 08:30", done: true },
      {
        name: "Обробка та комплектування",
        date: "12 Вер, 2025 11:00",
        done: true,
      },
      { name: "Передано кур'єру", date: "13 Вер, 2025 16:00", done: true },
      { name: "Доставка отримувачу", date: "15 Вер, 2025 08:00", done: true },
      { name: "Доставлено", date: "15 Вер, 2025 14:30", done: true },
    ],
    shippingAddress: {
      recipient: "Роман Шевченко",
      street: "вул. Хрещатик 22, кв. 14",
      city: "Київ",
      state: "Київська обл.",
      zip: "01001",
      country: "Україна",
    },
    paymentMethod: { type: "Mastercard", number: "•••• 9876" },
    items: [
      {
        id: 4,
        slug: "sony-wh-1000xm5-black",
        name: "Бездротові навушники Sony WH-1000XM5 Black",
        price: 14999.0,
        note: "Залишене біля дверей.",
        image:
          "https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA",
      },
    ],
  },
  {
    id: "483920194",
    date: "02 Тра, 2025",
    total: 55149.0,
    shipTo: "Роман Шевченко",
    status: "Скасовано 02 Тра, 2025",
    statusIcon: "cancel",
    statusClass: "text-rose-500 bg-rose-500/10 border border-rose-500/20",
    statusCode: "cancelled",
    trackingSteps: [
      { name: "Замовлення створено", date: "02 Тра, 2025 09:00", done: true },
      { name: "Скасовано", date: "02 Тра, 2025 09:30", done: true },
    ],
    shippingAddress: {
      recipient: "Роман Шевченко",
      street: "вул. Хрещатик 22, кв. 14",
      city: "Київ",
      state: "Київська обл.",
      zip: "01001",
      country: "Україна",
    },
    paymentMethod: { type: "Visa", number: "•••• 4242" },
    items: [
      {
        id: 1,
        slug: "iphone-15-pro-max",
        name: "Apple iPhone 15 Pro Max 256GB Natural Titanium",
        price: 54999.0,
        image:
          "https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM",
      },
    ],
  },
];

const ordersList = ref([...defaultOrders]);
const ordersFilter = ref("all");
const ordersSearchQuery = ref("");

const selectedOrder = ref(null);
const isDetailsOpen = ref(false);
const trackingOrder = ref(null);
const isTrackingOpen = ref(false);
const reviewProduct = ref(null);
const reviewRating = ref(5);
const reviewComment = ref("");
const isReviewOpen = ref(false);

const filteredOrders = computed(() =>
  ordersList.value.filter((o) => {
    if (ordersFilter.value !== "all" && o.statusCode !== ordersFilter.value)
      return false;
    if (ordersSearchQuery.value.trim()) {
      const q = ordersSearchQuery.value.toLowerCase();
      return (
        o.id.toLowerCase().includes(q) ||
        o.items.some((i) => i.name.toLowerCase().includes(q))
      );
    }
    return true;
  }),
);

const openDetails = (o) => {
  selectedOrder.value = o;
  isDetailsOpen.value = true;
};
const openTracking = (o) => {
  trackingOrder.value = o;
  isTrackingOpen.value = true;
};
const openReview = (i) => {
  reviewProduct.value = i;
  reviewRating.value = 5;
  reviewComment.value = "";
  isReviewOpen.value = true;
};
const buyItAgain = (i) => store.addToCart(i);
const submitReview = () => {
  store.addToast(
    `Дякуємо! Ваш відгук із оцінкою ${reviewRating.value} зірок успішно опубліковано.`,
    "success",
  );
  isReviewOpen.value = false;
};

const filterBtns = [
  { key: "all", label: "Усі" },
  { key: "shipped", label: "В дорозі" },
  { key: "delivered", label: "Доставлені" },
  { key: "cancelled", label: "Скасовані" },
];
</script>

<template>
  <div class="space-y-6 animate-fade font-sans select-none">
    <!-- Filters & Search -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-4 rounded-xl border border-zinc-100 dark:border-zinc-800 shadow-sm"
    >
      <div
        class="flex items-center bg-zinc-50 dark:bg-zinc-850 p-1 rounded-lg w-fit"
      >
        <button
          v-for="btn in filterBtns"
          :key="btn.key"
          :class="
            ordersFilter === btn.key
              ? 'bg-[#00a046] text-white shadow-sm'
              : 'text-zinc-550 hover:text-zinc-800 dark:hover:text-zinc-200'
          "
          class="py-2 px-4 rounded-lg font-extrabold text-xs md:text-sm transition-all"
          @click="ordersFilter = btn.key"
        >
          {{ btn.label }}
        </button>
      </div>
      <div class="relative flex-1 max-w-md">
        <span
          class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-[18px]"
        >search</span>
        <input
          v-model="ordersSearchQuery"
          type="text"
          placeholder="Пошук замовлень за номером або назвою товару..."
          class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg pl-10 pr-4 py-2.5 text-xs md:text-sm focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] text-zinc-800 dark:text-zinc-200 outline-none"
        >
        <button
          v-if="ordersSearchQuery"
          class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-650"
          @click="ordersSearchQuery = ''"
        >
          <span class="material-symbols-outlined text-[16px]">close</span>
        </button>
      </div>
    </div>

    <!-- Orders -->
    <div
      v-if="filteredOrders.length > 0"
      class="space-y-6"
    >
      <div
        v-for="order in filteredOrders"
        :key="order.id"
        class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow"
      >
        <div
          class="bg-zinc-50 dark:bg-zinc-850 px-6 py-5 border-b border-zinc-100 dark:border-zinc-800 flex flex-wrap justify-between items-center gap-4"
        >
          <div class="flex gap-8 flex-wrap">
            <div>
              <p
                class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider"
              >
                Оформлено
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
                class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider"
              >
                Отримувач
              </p>
              <p
                class="font-bold text-zinc-800 dark:text-zinc-200 text-sm mt-0.5"
              >
                {{ order.shipTo }}
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
                }}</span>{{ order.status }}
              </span>
            </div>
          </div>
          <div class="flex flex-col items-end gap-1">
            <p
              class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider"
            >
              Замовлення №{{ order.id }}
            </p>
            <button
              class="text-[#00a046] hover:text-[#00b050] font-extrabold text-xs md:text-sm hover:underline"
              @click="openDetails(order)"
            >
              Детальніше
            </button>
          </div>
        </div>
        <div class="p-6 space-y-6">
          <div
            v-for="item in order.items"
            :key="item.name"
            class="flex gap-6 flex-col sm:flex-row"
          >
            <img
              class="w-24 h-24 object-contain rounded-lg border border-zinc-100 dark:border-zinc-800 bg-white p-2 cursor-pointer hover:border-[#00a046]/40 transition-colors"
              :src="item.image"
              :alt="item.name"
              @click="store.viewProduct(item)"
            >
            <div class="flex-1">
              <h3
                class="font-extrabold text-zinc-800 dark:text-zinc-200 text-base md:text-lg leading-tight cursor-pointer hover:text-[#00a046] transition-colors"
                @click="store.viewProduct(item)"
              >
                {{ item.name }}
              </h3>
              <p
                v-if="item.returnWindow"
                class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 mt-1.5"
              >
                Повернення можливе до {{ item.returnWindow }}
              </p>
              <p
                v-if="item.note"
                class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 mt-1.5"
              >
                {{ item.note }}
              </p>
              <div class="mt-5 flex gap-3 flex-wrap">
                <button
                  class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2.5 rounded-lg font-extrabold text-xs md:text-sm transition-all shadow-sm"
                  @click="buyItAgain(item)"
                >
                  Купити знову
                </button>
                <button
                  v-if="order.statusCode === 'delivered'"
                  class="border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-5 py-2.5 rounded-lg font-extrabold hover:bg-zinc-55 dark:hover:bg-zinc-850 transition-all text-xs md:text-sm"
                  @click="openReview(item)"
                >
                  Написати відгук
                </button>
                <button
                  v-if="order.statusCode !== 'cancelled'"
                  class="border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-5 py-2.5 rounded-lg font-extrabold hover:bg-zinc-55 dark:hover:bg-zinc-850 transition-all text-xs md:text-sm flex items-center gap-1.5"
                  @click="openTracking(order)"
                >
                  <span
                    class="material-symbols-outlined text-[16px] md:text-[18px]"
                  >track_changes</span>
                  Відстежити
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      v-else
      class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 p-12 text-center shadow-sm"
    >
      <span
        class="material-symbols-outlined text-[48px] text-zinc-350 dark:text-zinc-600 mb-4"
      >shopping_bag</span>
      <h3 class="font-extrabold text-lg text-zinc-850 dark:text-zinc-150">
        Замовлень не знайдено
      </h3>
      <p
        class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 max-w-sm mx-auto mt-2"
      >
        Спробуйте змінити фільтр або пошуковий запит.
      </p>
      <a
        href="/catalog"
        class="inline-block bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs md:text-sm py-3 px-6 rounded-lg transition-all mt-6 shadow-sm"
      >Перейти до каталогу</a>
    </div>
  </div>

  <!-- Details Modal -->
  <div
    v-if="isDetailsOpen && selectedOrder"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-2xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[85vh]"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 px-6 py-5 flex justify-between items-center"
      >
        <div>
          <h3 class="font-black text-lg text-zinc-900 dark:text-white">
            Деталі замовлення
          </h3>
          <p
            class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mt-0.5"
          >
            Замовлення №{{ selectedOrder.id }} • {{ selectedOrder.date }}
          </p>
        </div>
        <button
          class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
          @click="isDetailsOpen = false"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div
        class="p-6 overflow-y-auto space-y-6 text-xs md:text-sm divide-y divide-zinc-100 dark:divide-zinc-800"
      >
        <div class="space-y-3">
          <h4
            class="font-extrabold text-zinc-400 dark:text-zinc-500 uppercase text-[10px] tracking-wider mb-2"
          >
            Придбані товари
          </h4>
          <div
            v-for="item in selectedOrder.items"
            :key="item.name"
            class="flex gap-4 items-center"
          >
            <img
              :src="item.image"
              :alt="item.name"
              class="w-12 h-12 object-contain rounded-lg bg-white border border-zinc-100 dark:border-zinc-800 p-1 cursor-pointer hover:border-[#00a046]/40 transition-colors"
              @click="store.viewProduct(item)"
            >
            <div class="flex-1">
              <p
                class="font-extrabold text-zinc-800 dark:text-zinc-200 line-clamp-1 cursor-pointer hover:text-[#00a046] transition-colors"
                @click="store.viewProduct(item)"
              >
                {{ item.name }}
              </p>
              <p
                class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mt-0.5"
              >
                1x • {{ selectedOrder.total.toFixed(2) }} ₴
              </p>
            </div>
            <button
              class="text-[#00a046] hover:text-[#00b050] font-extrabold"
              @click="buyItAgain(item)"
            >
              Купити знову
            </button>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-5">
          <div>
            <h4
              class="font-extrabold text-zinc-400 dark:text-zinc-500 uppercase text-[10px] tracking-wider mb-2"
            >
              Адреса доставки
            </h4>
            <p class="font-extrabold text-zinc-800 dark:text-zinc-200">
              {{ selectedOrder.shippingAddress.recipient }}
            </p>
            <p class="text-zinc-500 dark:text-zinc-400 mt-1">
              {{ selectedOrder.shippingAddress.street }}
            </p>
            <p class="text-zinc-500 dark:text-zinc-400">
              м. {{ selectedOrder.shippingAddress.city }},
              {{ selectedOrder.shippingAddress.state }}
              {{ selectedOrder.shippingAddress.zip }}
            </p>
          </div>
          <div>
            <h4
              class="font-extrabold text-zinc-400 dark:text-zinc-500 uppercase text-[10px] tracking-wider mb-2"
            >
              Спосіб оплати
            </h4>
            <p
              class="flex items-center gap-1.5 font-extrabold text-zinc-850 dark:text-zinc-150 mt-1"
            >
              <span class="material-symbols-outlined text-[18px]">credit_card</span>{{ selectedOrder.paymentMethod.type }}
              {{ selectedOrder.paymentMethod.number }}
            </p>
          </div>
        </div>
        <div class="pt-5 space-y-2">
          <h4
            class="font-extrabold text-zinc-400 dark:text-zinc-500 uppercase text-[10px] tracking-wider mb-2"
          >
            Сума
          </h4>
          <div class="flex justify-between text-zinc-500 dark:text-zinc-400">
            <span>Вартість товарів</span><span>{{ (selectedOrder.total - 150).toFixed(2) }} ₴</span>
          </div>
          <div class="flex justify-between text-zinc-500 dark:text-zinc-400">
            <span>Доставка</span><span>150.00 ₴</span>
          </div>
          <div
            class="flex justify-between font-black text-sm md:text-base pt-3 border-t border-zinc-100 dark:border-zinc-800 mt-2"
          >
            <span>Всього</span><span class="text-[#00a046]">{{ selectedOrder.total.toFixed(2) }} ₴</span>
          </div>
        </div>
      </div>
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-t border-zinc-100 dark:border-zinc-800 px-6 py-4 text-right"
      >
        <button
          class="bg-zinc-200 dark:bg-zinc-800 hover:bg-zinc-300 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 px-5 py-2 rounded-lg font-extrabold text-xs md:text-sm transition-colors"
          @click="isDetailsOpen = false"
        >
          Закрити
        </button>
      </div>
    </div>
  </div>

  <!-- Tracking Modal -->
  <div
    v-if="isTrackingOpen && trackingOrder"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-md w-full shadow-2xl overflow-hidden"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 px-6 py-5 flex justify-between items-center"
      >
        <div>
          <h3
            class="font-black text-base md:text-lg text-zinc-900 dark:text-white"
          >
            Відстеження доставки
          </h3>
          <p
            class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mt-0.5"
          >
            Замовлення №{{ trackingOrder.id }}
          </p>
        </div>
        <button
          class="text-zinc-400 hover:text-zinc-650 dark:hover:text-zinc-255"
          @click="isTrackingOpen = false"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div class="p-6 space-y-5">
        <div
          v-for="(step, idx) in trackingOrder.trackingSteps"
          :key="idx"
          class="flex gap-4 relative"
        >
          <div
            v-if="idx < trackingOrder.trackingSteps.length - 1"
            class="absolute left-3 top-6 bottom-0 w-0.5 -translate-x-1/2"
            :class="
              step.done && trackingOrder.trackingSteps[idx + 1].done
                ? 'bg-[#00a046]'
                : 'bg-zinc-200 dark:bg-zinc-800'
            "
          />
          <div
            class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 z-10"
            :class="
              step.done
                ? 'bg-[#00a046] text-white'
                : 'bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-400'
            "
          >
            <span class="material-symbols-outlined text-[14px]">{{
              step.done ? "done" : "hourglass_empty"
            }}</span>
          </div>
          <div class="text-xs md:text-sm">
            <p
              class="font-extrabold"
              :class="
                step.done
                  ? 'text-zinc-850 dark:text-zinc-150'
                  : 'text-zinc-400 dark:text-zinc-550'
              "
            >
              {{ step.name }}
            </p>
            <p class="text-[10px] text-zinc-400 dark:text-zinc-500 mt-0.5">
              {{ step.date }}
            </p>
          </div>
        </div>
      </div>
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-t border-zinc-100 dark:border-zinc-800 px-6 py-4 text-right"
      >
        <button
          class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2 rounded-lg font-extrabold text-xs md:text-sm transition-colors"
          @click="isTrackingOpen = false"
        >
          Готово
        </button>
      </div>
    </div>
  </div>

  <!-- Review Modal -->
  <div
    v-if="isReviewOpen && reviewProduct"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-md w-full shadow-2xl overflow-hidden"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 px-6 py-5 flex justify-between items-center"
      >
        <h3
          class="font-black text-base md:text-lg text-zinc-900 dark:text-white"
        >
          Написати відгук
        </h3>
        <button
          class="text-zinc-400 hover:text-zinc-650 dark:hover:text-zinc-255"
          @click="isReviewOpen = false"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <form
        class="p-6 space-y-4 text-xs md:text-sm"
        @submit.prevent="submitReview"
      >
        <div class="flex items-center gap-3">
          <img
            :src="reviewProduct.image"
            :alt="reviewProduct.name"
            class="w-12 h-12 object-contain rounded-lg border border-zinc-100 dark:border-zinc-800 bg-white p-1"
          >
          <p class="font-extrabold text-zinc-800 dark:text-zinc-200">
            {{ reviewProduct.name }}
          </p>
        </div>
        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
          >Оцінка</label>
          <div class="flex gap-1 text-amber-400 cursor-pointer">
            <span
              v-for="star in 5"
              :key="star"
              class="material-symbols-outlined text-[26px]"
              :style="
                star <= reviewRating
                  ? 'font-variation-settings: \'FILL\' 1;'
                  : ''
              "
              @click="reviewRating = star"
            >star</span>
          </div>
        </div>
        <div class="space-y-1.5">
          <label
            class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
          >Коментар</label>
          <textarea
            v-model="reviewComment"
            rows="4"
            placeholder="Поділіться вашими враженнями про товар..."
            required
            class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-3.5 py-2.5 text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none resize-none"
          />
        </div>
        <div
          class="bg-zinc-50 dark:bg-zinc-850 border-t border-zinc-100 dark:border-zinc-800 -mx-6 -mb-6 px-6 py-4 flex justify-end gap-3 mt-6"
        >
          <button
            type="button"
            class="border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 px-4 py-2 rounded-lg font-extrabold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all text-xs md:text-sm"
            @click="isReviewOpen = false"
          >
            Скасувати
          </button>
          <button
            type="submit"
            class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2 rounded-lg font-extrabold transition-all text-xs md:text-sm"
          >
            Надіслати
          </button>
        </div>
      </form>
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
