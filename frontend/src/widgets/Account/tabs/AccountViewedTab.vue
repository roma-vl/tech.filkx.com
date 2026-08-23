<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useCartStore } from "@/entities/order/model/cartStore";
import { UiButton } from "@/shared/ui";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";

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
const { t } = useI18n();
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

// History entries only carry a lean product subset — ProductCard renders
// catalog-shaped products, so fill in the rating/reviews it expects with the
// same neutral defaults ProductDetailPage uses for its own viewed-products rail.
const catalogProducts = computed(() =>
  sortedProducts.value.map((item) => ({
    ...item,
    rating: 0,
    reviews: 0,
    inStock: item.inStock !== false,
  })),
);

const removeItem = (productId: string | number) => {
  cartStore.removeViewedItem(productId);
  cartStore.addToast(t("account.viewed.toasts.removed"), "info");
};

const clearAll = () => {
  cartStore.clearViewedHistory();
  cartStore.addToast(t("account.viewed.toasts.cleared"), "success");
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
          {{ t("account.viewed.sortLabel") }}
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
            {{ t("account.viewed.sortRecent") }}
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
            {{ t("account.viewed.sortCount") }}
          </button>
        </div>
      </div>

      <button
        class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-zinc-200 dark:border-zinc-800 hover:bg-rose-50 dark:hover:bg-rose-950/20 text-zinc-500 hover:text-rose-500 dark:text-zinc-400 dark:hover:text-rose-450 text-xs font-bold transition-all self-end sm:self-center"
        type="button"
        @click="clearAll"
      >
        <span class="material-symbols-outlined text-[16px]">delete_sweep</span>
        <span>{{ t("account.viewed.clearHistory") }}</span>
      </button>
    </div>

    <!-- Product History Grid -->
    <div
      v-if="viewedProducts.length > 0"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4"
    >
      <div
        v-for="product in catalogProducts"
        :key="product.id"
        class="group/history relative"
      >
        <ProductCard :product="product" view-mode="grid" />

        <!-- Remove-from-history button, overlaid so ProductCard itself stays
             identical to the catalog card everywhere else it's reused. -->
        <button
          class="absolute top-2 left-2 z-10 w-8 h-8 flex items-center justify-center bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm hover:bg-rose-500/10 hover:text-rose-500 text-zinc-400 dark:text-zinc-500 rounded-full shadow-sm opacity-0 group-hover/history:opacity-100 transition-all"
          :title="t('account.viewed.removeTitle')"
          type="button"
          @click="removeItem(product.id)"
        >
          <span class="material-symbols-outlined text-[16px]">close</span>
        </button>
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
        {{ t("account.viewed.empty.title") }}
      </h3>
      <p
        class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400 max-w-sm mx-auto mt-2"
      >
        {{ t("account.viewed.empty.subtitle") }}
      </p>
      <UiButton :to="{ name: 'catalog' }" class="mt-6">
        {{ t("account.viewed.empty.cta") }}
      </UiButton>
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
