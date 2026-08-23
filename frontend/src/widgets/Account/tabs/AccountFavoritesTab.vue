<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { useCartStore } from "@/entities/order/model/cartStore";
import { UiButton } from "@/shared/ui";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";

const cartStore = useCartStore();
const { t } = useI18n();

// The wishlist only stores a lean subset of product fields (see toggleWishlist
// in cartStore) — ProductCard renders catalog-shaped products, so fill in the
// rating/reviews/stock fields it expects with the same neutral defaults
// ProductDetailPage uses for its own recently-viewed/related rails.
const wishlistProducts = computed(() =>
  cartStore.wishlist.map((item: any) => ({
    ...item,
    rating: 0,
    reviews: 0,
    inStock: item.inStock !== false,
  })),
);
</script>

<template>
  <div class="space-y-6 animate-fade font-sans">
    <div
      v-if="wishlistProducts.length > 0"
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4"
    >
      <ProductCard
        v-for="product in wishlistProducts"
        :key="product.id"
        :product="product"
        view-mode="grid"
      />
    </div>

    <div
      v-else
      class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-12 text-center shadow-sm"
    >
      <div
        class="w-16 h-16 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4"
      >
        <span
          class="material-symbols-outlined text-[32px]"
          style="font-variation-settings: &quot;FILL&quot; 1"
          >favorite</span
        >
      </div>
      <h3 class="font-extrabold text-lg text-zinc-800 dark:text-zinc-200">
        {{ t("account.favorites.empty.title") }}
      </h3>
      <p
        class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 max-w-sm mx-auto mt-2"
      >
        {{ t("account.favorites.empty.subtitle") }}
      </p>
      <UiButton :to="{ name: 'catalog' }" class="mt-6">
        {{ t("account.favorites.empty.cta") }}
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
