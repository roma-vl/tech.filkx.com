<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useCartStore } from "@/entities/order/model/cartStore";
import { UiButton } from "@/shared/ui";

interface ViewedItem {
  id: string | number;
  slug?: string;
  name: string;
  brand: string;
  image: string;
  price: number;
  category: string;
  inStock: boolean;
  viewCount: number;
  lastViewedAt: string;
}

const cartStore = useCartStore();
const sortBy = ref<"recent" | "count">("recent");

const viewedProducts = computed<ViewedItem[]>(
  () => cartStore.viewedDetailed || [],
);

const sortedProducts = computed(() => {
  const items = [...viewedProducts.value];
  if (sortBy.value === "recent") {
    return items.sort(
      (a, b) =>
        new Date(b.lastViewedAt).getTime() - new Date(a.lastViewedAt).getTime(),
    );
  } else {
    return items.sort((a, b) => (b.viewCount || 0) - (a.viewCount || 0));
  }
});

const removeItem = (productId: string | number) => {
  cartStore.removeViewedItem(productId);
  cartStore.addToast("Товар видалено з історії переглядів", "info");
};

const clearAll = () => {
  cartStore.clearViewedHistory();
  cartStore.addToast("Історію переглядів повністю очищено", "success");
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};

const formatDate = (isoString: string) => {
  if (!isoString) return "";
  try {
    const date = new Date(isoString);
    if (isNaN(date.getTime())) return "";
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMin = Math.floor(diffMs / 60_000);
    const diffHours = Math.floor(diffMs / 3_600_000);
    const diffDays = Math.floor(diffMs / 86_400_000);

    if (diffMin < 1) return "щойно";
    if (diffMin < 60) return `${diffMin} хв тому`;
    if (diffHours < 24) return `${diffHours} год тому`;
    if (diffDays === 1) return "вчора";
    if (diffDays < 7) return `${diffDays} дн тому`;
    return date.toLocaleDateString("uk-UA", { day: "numeric", month: "short" });
  } catch (e) {
    return "";
  }
};
</script>

<template>
  <div class="space-y-6 animate-fade font-sans">
    <!-- Filters & Actions Header -->
    <div
      v-if="viewedProducts.length > 0"
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200 dark:border-zinc-800"
    >
      <div class="flex items-center gap-3">
        <span
          class="text-xs font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider"
        >
          Сортування:
        </span>
        <div
          class="flex bg-zinc-100 dark:bg-zinc-800 p-1 rounded-lg border border-zinc-200/50 dark:border-zinc-800/50"
        >
          <button
            class="px-3 py-1.5 rounded-md text-xs font-extrabold transition-all"
            :class="
              sortBy === 'recent'
                ? 'bg-white dark:bg-zinc-700 text-[#00a046] shadow-sm'
                : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'
            "
            type="button"
            @click="sortBy = 'recent'"
          >
            Нещодавні
          </button>
          <button
            class="px-3 py-1.5 rounded-md text-xs font-extrabold transition-all"
            :class="
              sortBy === 'count'
                ? 'bg-white dark:bg-zinc-700 text-[#00a046] shadow-sm'
                : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200'
            "
            type="button"
            @click="sortBy = 'count'"
          >
            Часто переглянуті
          </button>
        </div>
      </div>

      <button
        class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-800 hover:bg-rose-50 dark:hover:bg-rose-950/20 text-zinc-500 hover:text-rose-500 dark:text-zinc-400 dark:hover:text-rose-450 text-xs font-bold transition-all self-end sm:self-center"
        type="button"
        @click="clearAll"
      >
        <span class="material-symbols-outlined text-[16px]">delete_sweep</span>
        <span>Очистити історію</span>
      </button>
    </div>

    <!-- Product History Grid -->
    <div
      v-if="viewedProducts.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
    >
      <div
        v-for="product in sortedProducts"
        :key="product.id"
        class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all group flex flex-col justify-between"
      >
        <!-- Card Header / Image Section -->
        <div
          class="p-4 bg-white dark:bg-zinc-800 relative flex justify-center items-center aspect-square border-b border-zinc-200 dark:border-zinc-800"
        >
          <router-link :to="{ name: 'product-detail', params: { id: product.slug || product.id } }" class="w-full h-full flex items-center justify-center">
            <img
              :src="product.image"
              :alt="product.name"
              class="w-full h-full object-contain p-4 group-hover:scale-105 transition-transform duration-300"
            />
          </router-link>

          <!-- Delete button (cross) -->
          <button
            class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 hover:bg-rose-500/10 hover:text-rose-500 text-zinc-400 dark:text-zinc-500 rounded-full transition-all"
            title="Видалити з історії"
            type="button"
            @click="removeItem(product.id)"
          >
            <span class="material-symbols-outlined text-[16px]">close</span>
          </button>

          <!-- View Counter & Last Viewed badge -->
          <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between gap-1">
            <span
              class="bg-zinc-900/80 backdrop-blur-sm text-white text-[9px] font-black px-2 py-0.5 rounded flex items-center gap-1 select-none shadow-sm shrink-0"
              :title="`Переглянуто разів: ${product.viewCount}`"
            >
              <span class="material-symbols-outlined text-[10px]">visibility</span>
              <span>{{ product.viewCount }}×</span>
            </span>
            <span
              v-if="formatDate(product.lastViewedAt)"
              class="bg-emerald-500/90 backdrop-blur-sm text-white text-[9px] font-black px-2 py-0.5 rounded flex items-center gap-1 select-none shadow-sm min-w-0 truncate"
              :title="`Останній перегляд: ${new Date(product.lastViewedAt).toLocaleString('uk-UA')}`"
            >
              <span class="material-symbols-outlined text-[10px] shrink-0">schedule</span>
              <span class="truncate">{{ formatDate(product.lastViewedAt) }}</span>
            </span>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-5 flex-1 flex flex-col justify-between gap-4">
          <div>
            <div class="flex items-center justify-between gap-2 mb-1.5">
              <span
                class="text-[10px] text-[#00a046] font-extrabold uppercase tracking-wider"
              >
                {{ product.category }}
              </span>
              <span
                class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold"
              >
                {{ product.brand }}
              </span>
            </div>
            <router-link
              :to="{ name: 'product-detail', params: { id: product.slug || product.id } }"
              class="block font-extrabold text-zinc-800 dark:text-zinc-200 text-sm md:text-base line-clamp-2 leading-snug group-hover:text-[#00a046] transition-colors"
            >
              {{ product.name }}
            </router-link>
          </div>

          <!-- Bottom Price & Cart -->
          <div class="flex items-center justify-between gap-2 mt-auto">
            <span class="font-black text-[#00a046] text-lg">
              {{ formatPrice(product.price) }}
            </span>
            <UiButton size="sm" @click="cartStore.addToCart(product as any)">
              <template #prefix><span class="material-symbols-outlined text-[16px]">shopping_cart</span></template>
              Додати
            </UiButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div
      v-else
      class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-12 text-center shadow-sm"
    >
      <div
        class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800/80 text-zinc-400 dark:text-zinc-500 rounded-full flex items-center justify-center mx-auto mb-4"
      >
        <span class="material-symbols-outlined text-[32px]">history</span>
      </div>
      <h3 class="font-extrabold text-lg text-zinc-800 dark:text-zinc-200">
        Історія переглядів порожня
      </h3>
      <p
        class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto mt-2"
      >
        Ви ще не переглянули жодного товару. Ваша історія переглядів
        відображатиметься тут.
      </p>
      <UiButton :to="{ name: 'catalog' }" class="mt-6">Перейти до товарів</UiButton>
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
