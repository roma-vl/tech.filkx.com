<template>
  <!-- Skeleton loading -->
  <div
    v-if="isLoading"
    class="max-w-container-max mx-auto px-4 md:px-8 py-8 font-sans"
  >
    <!-- Breadcrumb skeleton -->
    <div class="flex items-center gap-2 mb-6">
      <div
        class="h-3 w-16 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"
      />
      <div class="h-3 w-3 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
      <div
        class="h-3 w-14 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"
      />
      <div class="h-3 w-3 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
      <div
        class="h-3 w-40 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"
      />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <!-- Gallery skeleton -->
      <div class="lg:col-span-7 flex gap-4">
        <div class="flex flex-col gap-3 shrink-0">
          <div
            v-for="i in 3"
            :key="i"
            class="w-20 h-20 rounded-lg bg-zinc-100 dark:bg-zinc-800 animate-pulse"
          />
        </div>
        <div
          class="flex-1 aspect-square rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse"
        />
      </div>

      <!-- Purchase skeleton -->
      <div class="lg:col-span-5 space-y-4">
        <div
          class="h-3 w-24 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"
        />
        <div class="h-8 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
        <div
          class="h-5 w-2/3 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"
        />
        <div
          class="h-4 w-1/2 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse"
        />
        <div class="h-px bg-zinc-100 dark:bg-zinc-800 mt-4" />
        <div
          class="h-44 rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse"
        />
        <div
          class="h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 animate-pulse"
        />
        <div
          class="h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 animate-pulse"
        />
      </div>
    </div>
  </div>

  <!-- Not found -->
  <div
    v-else-if="!product"
    class="min-h-[60vh] flex flex-col items-center justify-center text-center px-4 font-sans"
  >
    <div
      class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4"
    >
      <span class="material-symbols-outlined text-[32px] text-zinc-400"
        >search_off</span
      >
    </div>
    <h1 class="text-xl font-extrabold mb-2 text-zinc-800 dark:text-zinc-200">
      Товар не знайдено
    </h1>
    <p class="text-sm text-zinc-500 mb-6 max-w-sm">
      Перевірте правильність посилання або скористайтесь каталогом.
    </p>
    <UiButton :to="{ name: 'catalog' }"> Перейти до каталогу </UiButton>
  </div>

  <!-- Product page -->
  <div v-else class="font-sans">
    <!-- Breadcrumbs -->
    <nav
      class="max-w-container-max mx-auto px-4 md:px-8 pt-6 flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500"
    >
      <router-link
        :to="{ name: 'home' }"
        class="hover:text-[#00a046] transition-colors flex items-center gap-1 font-semibold"
      >
        <span class="material-symbols-outlined text-[15px]">home</span>
        Головна
      </router-link>
      <span
        class="material-symbols-outlined text-[13px] text-zinc-300 dark:text-zinc-700"
        >chevron_right</span
      >
      <router-link
        :to="{ name: 'catalog' }"
        class="hover:text-[#00a046] transition-colors font-semibold"
      >
        Каталог
      </router-link>
      <span
        class="material-symbols-outlined text-[13px] text-zinc-300 dark:text-zinc-700"
        >chevron_right</span
      >
      <span
        class="text-zinc-700 dark:text-zinc-300 font-semibold line-clamp-1 max-w-[220px]"
        >{{ product.name }}</span
      >
    </nav>

    <main class="max-w-container-max mx-auto px-4 md:px-8 py-6">
      <!-- Hero block -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <div class="lg:col-span-7">
          <ProductGallery
            :gallery-images="galleryImages"
            :selected-image-index="selectedImageIndex"
            :zoom-style="zoomStyle"
            :is-zoomed="isZoomed"
            :selected-image="selectedImage"
            :product-name="product.name"
            @select-image="setSelectedImage"
            @next-image="selectNextImage"
            @prev-image="selectPreviousImage"
            @mouse-move="handleMouseMove"
            @mouse-leave="handleMouseLeave"
          />
        </div>
        <div class="lg:col-span-5">
          <ProductPurchase
            :product="product"
            :available-colors="availableColors"
            :selected-color="selectedColor"
            :available-storage="availableStorage"
            :selected-storage="selectedStorage"
            :format-price="formatPrice"
            @select-variant="selectVariantByAttributes"
            @quick-order="openQuickOrder"
          />
        </div>
      </div>

      <!-- Combo Bundle -->
      <ComboDeal
        :bundle-items="bundleItems"
        :selected-bundle-ids="selectedBundleIds"
        :bundle-total="bundleTotal"
        :bundle-subtotal="bundleSubtotal"
        :bundle-savings="bundleSavings"
        :format-price="formatPrice"
        @toggle-item="toggleBundleItem"
        @add-bundle="addBundleToCart"
      />

      <!-- Tabs -->
      <ProductTabs
        :active-tab="activeTab"
        :tabs="tabs"
        :product="product"
        :gallery-images="galleryImages"
        :quality-guarantees="qualityGuarantees"
        @change-tab="activeTab = $event"
      />

      <!-- Related products -->
      <section v-if="relatedProducts.length > 0" class="mt-14">
        <div class="flex items-center justify-between gap-4 mb-6">
          <h2
            class="font-extrabold text-xl md:text-2xl text-zinc-900 dark:text-white tracking-tight"
          >
            Схожі товари
          </h2>
          <div v-if="relatedProducts.length > 5" class="flex gap-2 shrink-0">
            <button
              class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all"
              type="button"
              aria-label="Прокрутити ліворуч"
              @click="scrollRelated('left')"
            >
              <span
                class="material-symbols-outlined text-[20px] text-zinc-600 dark:text-zinc-400"
                >chevron_left</span
              >
            </button>
            <button
              class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all"
              type="button"
              aria-label="Прокрутити праворуч"
              @click="scrollRelated('right')"
            >
              <span
                class="material-symbols-outlined text-[20px] text-zinc-600 dark:text-zinc-400"
                >chevron_right</span
              >
            </button>
          </div>
        </div>
        <div
          ref="relatedScrollRef"
          class="flex overflow-x-auto gap-4 hide-scrollbar scroll-smooth snap-x snap-mandatory py-2"
        >
          <div
            v-for="related in relatedProducts"
            :key="related.id"
            class="w-[calc(50%-8px)] sm:w-[calc(33.333%-11px)] lg:w-[calc(20%-13px)] min-w-[180px] shrink-0 snap-start border-t border-l border-zinc-200 dark:border-zinc-800"
          >
            <ProductCard :product="related" view-mode="grid" />
          </div>
        </div>
      </section>

      <!-- Recently viewed -->
      <section v-if="recentlyViewed.length > 0" class="mt-14">
        <div class="flex items-center justify-between gap-4 mb-6">
          <h2
            class="font-extrabold text-xl md:text-2xl text-zinc-900 dark:text-white tracking-tight"
          >
            Нещодавно переглянуті
          </h2>
          <div v-if="recentlyViewed.length > 5" class="flex gap-2 shrink-0">
            <button
              class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all"
              type="button"
              aria-label="Прокрутити ліворуч"
              @click="scrollViewed('left')"
            >
              <span
                class="material-symbols-outlined text-[20px] text-zinc-600 dark:text-zinc-400"
                >chevron_left</span
              >
            </button>
            <button
              class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all"
              type="button"
              aria-label="Прокрутити праворуч"
              @click="scrollViewed('right')"
            >
              <span
                class="material-symbols-outlined text-[20px] text-zinc-600 dark:text-zinc-400"
                >chevron_right</span
              >
            </button>
          </div>
        </div>
        <div
          ref="viewedScrollRef"
          class="flex overflow-x-auto gap-4 hide-scrollbar scroll-smooth snap-x snap-mandatory py-2"
        >
          <div
            v-for="viewed in recentlyViewed"
            :key="viewed.id"
            class="w-[calc(50%-8px)] sm:w-[calc(33.333%-11px)] lg:w-[calc(20%-13px)] min-w-[180px] shrink-0 snap-start border-t border-l border-zinc-200 dark:border-zinc-800"
          >
            <ProductCard :product="viewed" view-mode="grid" />
          </div>
        </div>
      </section>
    </main>

    <!-- Sticky Buy Bar -->
    <div
      class="fixed right-0 bottom-0 left-0 z-40 border-t border-zinc-100 dark:border-zinc-800 bg-white/96 dark:bg-zinc-900/96 backdrop-blur-md shadow-xl transition-transform duration-300 ease-in-out"
      :class="showStickyBar ? 'translate-y-0' : 'translate-y-full'"
    >
      <div
        class="max-w-container-max mx-auto px-4 md:px-8 h-[68px] flex items-center gap-4"
      >
        <img
          alt="product"
          class="w-10 h-10 object-contain rounded-lg border border-zinc-100 dark:border-zinc-800 bg-white p-1 hidden sm:block shrink-0"
          :src="selectedImage"
        />
        <div class="flex-1 min-w-0 text-left">
          <p
            class="font-bold text-sm text-zinc-900 dark:text-white truncate leading-tight"
          >
            {{ product.name }}
          </p>
          <p class="text-[#00a046] font-black text-sm mt-0.5">
            {{ formatPrice(product.price) }}
          </p>
        </div>
        <div class="flex gap-2 shrink-0">
          <UiButton
            variant="secondary"
            size="sm"
            class="hidden sm:inline-flex"
            @click="openQuickOrder"
          >
            <template #prefix>
              <span class="material-symbols-outlined text-[15px]">bolt</span>
            </template>
            Швидке замовлення
          </UiButton>
          <UiButton size="sm" @click="cartStore.addToCart(product)">
            <template #prefix>
              <span class="material-symbols-outlined text-[16px]"
                >shopping_cart</span
              >
            </template>
            В кошик
          </UiButton>
        </div>
      </div>
    </div>

    <!-- Quick Order Modal -->
    <QuickOrderModal
      :is-open="isQuickOrderOpen"
      :product="product"
      @close="closeQuickOrder"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from "vue";
import { useHead } from "@vueuse/head";
import { useProductDetail } from "@/features/product/composables/useProductDetail";
import ProductGallery from "@/widgets/ProductDetail/ProductGallery.vue";
import ProductPurchase from "@/widgets/ProductDetail/ProductPurchase.vue";
import ComboDeal from "@/widgets/ProductDetail/ComboDeal.vue";
import ProductTabs from "@/widgets/ProductDetail/ProductTabs.vue";
import QuickOrderModal from "@/widgets/Catalog/QuickOrderModal.vue";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";
import { UiButton } from "@/shared/ui";

const {
  router,
  cartStore,
  isLoading,
  product,
  galleryImages,
  availableColors,
  availableStorage,
  selectedImageIndex,
  selectedColor,
  selectedStorage,
  selectVariantByAttributes,
  activeTab,
  showStickyBar,
  selectedBundleIds,
  isZoomed,
  zoomStyle,
  handleMouseMove,
  handleMouseLeave,
  selectedImage,
  tabs,
  bundleItems,
  qualityGuarantees,
  relatedProducts,
  formatPrice,
  bundleSubtotal,
  bundleSavings,
  bundleTotal,
  recentlyViewed,
  setSelectedImage,
  selectNextImage,
  selectPreviousImage,
  toggleBundleItem,
  addBundleToCart,
  isQuickOrderOpen,
  openQuickOrder,
  closeQuickOrder,
} = useProductDetail();

const relatedScrollRef = ref<HTMLElement | null>(null);
const scrollRelated = (direction: "left" | "right") => {
  const el = relatedScrollRef.value;
  if (!el) return;
  const cardWidth = el.firstElementChild?.clientWidth ?? 320;
  const amount = (cardWidth + 16) * (direction === "left" ? -1 : 1);
  el.scrollBy({ left: amount, behavior: "smooth" });
};

const viewedScrollRef = ref<HTMLElement | null>(null);
const scrollViewed = (direction: "left" | "right") => {
  const el = viewedScrollRef.value;
  if (!el) return;
  const cardWidth = el.firstElementChild?.clientWidth ?? 320;
  const amount = (cardWidth + 16) * (direction === "left" ? -1 : 1);
  el.scrollBy({ left: amount, behavior: "smooth" });
};

const metaDescription = computed(() =>
  product.value?.description
    ? String(product.value.description).replace(/\s+/g, " ").slice(0, 160)
    : "",
);

useHead({
  title: computed(() =>
    product.value ? `${product.value.name} — FilkxTech` : "FilkxTech",
  ),
  meta: computed(() =>
    product.value
      ? [
          { name: "description", content: metaDescription.value },
          { property: "og:type", content: "product" },
          { property: "og:title", content: product.value.name },
          { property: "og:description", content: metaDescription.value },
          { property: "og:image", content: product.value.image },
        ]
      : [],
  ),
  // Product/Offer schema.org structured data so search engines can render
  // rich results (price, availability, rating) directly in listings.
  script: computed(() =>
    product.value
      ? [
          {
            type: "application/ld+json",
            children: JSON.stringify({
              "@context": "https://schema.org",
              "@type": "Product",
              name: product.value.name,
              image: product.value.image ? [product.value.image] : undefined,
              description: metaDescription.value || undefined,
              category: product.value.category || undefined,
              sku: String(product.value.id),
              offers: {
                "@type": "Offer",
                priceCurrency: "UAH",
                price: product.value.price,
                availability: product.value.inStock
                  ? "https://schema.org/InStock"
                  : "https://schema.org/OutOfStock",
                url:
                  typeof window !== "undefined"
                    ? window.location.href
                    : undefined,
              },
              ...(product.value.reviews > 0
                ? {
                    aggregateRating: {
                      "@type": "AggregateRating",
                      ratingValue: product.value.rating,
                      reviewCount: product.value.reviews,
                    },
                  }
                : {}),
            }),
          },
        ]
      : [],
  ),
});
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
