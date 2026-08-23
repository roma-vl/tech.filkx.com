<template>
  <article
    :class="
      viewMode === 'grid'
        ? 'flex-col border border-zinc-200 dark:border-zinc-800 hover:z-20 hover:scale-[1.1] hover:bg-[#fcfcfd] dark:hover:bg-[#0b0c10]'
        : 'flex-col sm:flex-row rounded-md border border-transparent hover:border-zinc-200 dark:hover:border-zinc-800 hover:bg-white dark:hover:bg-zinc-900 hover:-translate-y-0.5'
    "
    class="group flex relative hover:shadow-2xl transition-all duration-200"
  >
    <!-- Image Section -->
    <div
      :class="viewMode === 'grid' ? 'w-full' : 'w-full sm:w-56 shrink-0'"
      class="relative flex justify-center items-center aspect-square overflow-hidden"
    >
      <router-link
        :to="{
          name: 'product-detail',
          params: { id: product.slug || product.id },
        }"
        class="w-full h-full flex items-center justify-center"
      >
        <img
          :alt="product.name"
          class="w-full h-full object-contain"
          :src="product.image"
        />
      </router-link>

      <!-- Discount Badge -->
      <span
        v-if="product.badge"
        :class="product.badgeClass"
        class="absolute top-2.5 left-2.5 text-white text-[11px] font-black px-1.5 py-0.5 rounded"
        >{{ product.badge }}</span
      >

      <!-- Wishlist Icon Button -->
      <button
        class="absolute top-1 right-1 w-8 h-8 flex items-center justify-center text-zinc-400 dark:text-zinc-500 hover:text-rose-500 transition-all hover:scale-110 active:scale-95"
        type="button"
        @click.stop="cartStore.toggleWishlist(product)"
      >
        <span
          class="material-symbols-outlined text-[19px] drop-shadow-sm"
          :class="{ 'text-rose-500': cartStore.isInWishlist(product.id) }"
          :style="
            cartStore.isInWishlist(product.id)
              ? 'font-variation-settings: \'FILL\' 1;'
              : ''
          "
          >favorite</span
        >
      </button>
    </div>

    <!-- Info Body -->
    <div
      :class="
        viewMode === 'grid'
          ? 'p-4 flex-col'
          : 'p-5 flex-1 flex-col justify-between'
      "
      class="flex flex-col justify-between flex-1"
    >
      <div class="space-y-2.5">
        <!-- Brand + Rating row -->
        <div class="flex items-center justify-between gap-2">
          <span
            v-if="product.brand"
            class="text-[11px] font-extrabold text-[#00a046] uppercase bg-emerald-500/8 dark:bg-emerald-500/10 px-2 py-0.5 rounded"
          >
            {{ product.brand }}
          </span>
          <div class="flex items-center gap-1">
            <div class="flex">
              <span
                v-for="star in 5"
                :key="star"
                class="material-symbols-outlined text-[13px]"
                :class="
                  star <= Math.round(product.rating)
                    ? 'text-amber-400'
                    : 'text-zinc-300 dark:text-zinc-600'
                "
                :style="
                  star <= Math.round(product.rating)
                    ? 'font-variation-settings: \'FILL\' 1;'
                    : ''
                "
                >star</span
              >
            </div>
            <span class="text-[11px] font-semibold text-zinc-400"
              >({{ product.reviews }})</span
            >
          </div>
        </div>

        <!-- Product Name -->
        <router-link
          :to="{
            name: 'product-detail',
            params: { id: product.slug || product.id },
          }"
          class="block"
        >
          <h2
            :class="
              viewMode === 'grid'
                ? 'text-sm line-clamp-2 min-h-[40px]'
                : 'text-[15px]'
            "
            class="font-medium text-zinc-900 dark:text-white group-hover:text-[#00a046] transition-colors leading-snug"
          >
            {{ product.name }}
          </h2>
        </router-link>

        <!-- Spec Pills -->
        <div class="flex flex-wrap gap-1.5">
          <span
            v-if="product.ram"
            class="inline-flex items-center gap-1 bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 rounded text-[11px] font-semibold text-zinc-600 dark:text-zinc-400"
          >
            <span class="material-symbols-outlined text-[12px]">memory</span>
            {{ product.ram }}
          </span>
          <span
            v-if="product.specs?.screen"
            class="inline-flex items-center gap-1 bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 rounded text-[11px] font-semibold text-zinc-600 dark:text-zinc-400"
          >
            <span class="material-symbols-outlined text-[12px]">monitor</span>
            {{ product.specs.screen.split(" ")[0] }}
          </span>
          <span
            v-if="product.specs?.storage"
            class="inline-flex items-center gap-1 bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 rounded text-[11px] font-semibold text-zinc-600 dark:text-zinc-400"
          >
            <span class="material-symbols-outlined text-[12px]">storage</span>
            {{ product.specs.storage.split(" ")[0] }}
          </span>
          <span
            :class="
              product.inStock
                ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700/30 text-[#00a046]'
                : 'bg-zinc-50 dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 text-zinc-400'
            "
            class="inline-flex items-center gap-1 border px-2 py-0.5 rounded text-[11px] font-bold"
          >
            <span class="material-symbols-outlined text-[11px]">{{
              product.inStock ? "check_circle" : "cancel"
            }}</span>
            {{
              product.inStock
                ? t("catalog.filters.inStock")
                : t("catalog.productCard.outOfStock")
            }}
          </span>
        </div>

        <!-- Description (list mode) -->
        <p
          v-if="viewMode === 'list'"
          class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed line-clamp-2"
        >
          {{ product.description }}
        </p>
      </div>

      <!-- Price + Actions -->
      <div
        class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-3"
      >
        <!-- Price -->
        <div class="min-w-0">
          <div
            v-if="product.oldPrice"
            class="text-xs text-zinc-400 line-through font-semibold"
          >
            {{ formatPrice(product.oldPrice) }}
          </div>
          <div class="text-lg font-black text-[#00a046] tracking-tight">
            {{ formatPrice(product.price) }}
          </div>
        </div>

        <!-- Cart + Compare -->
        <div class="flex items-center gap-1.5 shrink-0">
          <div class="relative group/cart">
            <button
              :disabled="!product.inStock"
              :class="
                product.inStock
                  ? 'bg-[#00a046] hover:bg-[#00b050] text-white active:scale-[0.98]'
                  : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed'
              "
              class="w-8 h-8 flex items-center justify-center rounded-md transition-all shadow-sm shrink-0"
              @click="cartStore.addToCart(product)"
            >
              <span class="material-symbols-outlined text-[17px]"
                >shopping_cart</span
              >
            </button>
            <div
              class="absolute bottom-full right-0 mb-1.5 px-2 py-1 bg-zinc-800 dark:bg-zinc-700 text-white text-[11px] rounded opacity-0 pointer-events-none group-hover/cart:opacity-100 transition-opacity whitespace-nowrap z-10 font-semibold"
            >
              {{
                product.inStock
                  ? t("catalog.productCard.buyTooltip")
                  : t("catalog.productCard.outOfStockTooltip")
              }}
            </div>
          </div>

          <!-- Compare -->
          <div class="relative group/cmp">
            <button
              :class="
                cartStore.isInCompare(product.id)
                  ? 'bg-emerald-50 dark:bg-emerald-900/20 text-[#00a046] border-[#00a046]/30'
                  : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-500 border-zinc-200 dark:border-zinc-700 hover:text-[#00a046] hover:border-[#00a046]/30'
              "
              class="w-8 h-8 flex items-center justify-center rounded-md border transition-all shrink-0"
              @click="cartStore.toggleCompare(product)"
            >
              <span class="material-symbols-outlined text-[16px]"
                >compare_arrows</span
              >
            </button>
            <div
              class="absolute bottom-full right-0 mb-1.5 px-2 py-1 bg-zinc-800 dark:bg-zinc-700 text-white text-[11px] rounded opacity-0 pointer-events-none group-hover/cmp:opacity-100 transition-opacity whitespace-nowrap z-10 font-semibold"
            >
              {{
                cartStore.isInCompare(product.id)
                  ? t("catalog.productCard.inCompareTooltip")
                  : t("common.compare")
              }}
            </div>
          </div>
        </div>
      </div>

      <!-- Extra parameters (grid mode, revealed on hover) -->
      <!-- Absolutely positioned so it never adds to the card's own document-flow
           height - a Grid row auto-sizes to its tallest cell, so if this sat in
           normal flow, hovering one card would grow the whole row and shove
           every card in it around instead of only the hovered one. -->
      <div
        v-if="viewMode === 'grid' && hasExtraSpecs"
        class="absolute left-0 right-0 top-full px-4 py-3 opacity-0 -translate-y-1 pointer-events-none group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-200 bg-[#fcfcfd] dark:bg-[#0b0c10] border-x border-b border-zinc-200 dark:border-zinc-800"
      >
        <dl class="space-y-1 text-xs">
          <div
            v-if="product.specs?.processor"
            class="flex justify-between gap-3"
          >
            <dt class="text-zinc-400">
              {{ t("catalog.productCard.specs.processor") }}
            </dt>
            <dd
              class="text-zinc-700 dark:text-zinc-300 font-semibold text-right truncate"
            >
              {{ product.specs.processor }}
            </dd>
          </div>
          <div v-if="product.specs?.os" class="flex justify-between gap-3">
            <dt class="text-zinc-400">
              {{ t("catalog.productCard.specs.os") }}
            </dt>
            <dd
              class="text-zinc-700 dark:text-zinc-300 font-semibold text-right truncate"
            >
              {{ product.specs.os }}
            </dd>
          </div>
          <div v-if="product.specs?.weight" class="flex justify-between gap-3">
            <dt class="text-zinc-400">
              {{ t("catalog.productCard.specs.weight") }}
            </dt>
            <dd
              class="text-zinc-700 dark:text-zinc-300 font-semibold text-right truncate"
            >
              {{ product.specs.weight }}
            </dd>
          </div>
        </dl>
      </div>
    </div>
  </article>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { useCartStore } from "@/entities/order/model/cartStore";

const props = defineProps<{
  product: any;
  viewMode?: string;
}>();

const { t } = useI18n();
const cartStore = useCartStore();

const hasExtraSpecs = computed(() =>
  Boolean(
    props.product?.specs?.processor ||
    props.product?.specs?.os ||
    props.product?.specs?.weight,
  ),
);

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};
</script>
