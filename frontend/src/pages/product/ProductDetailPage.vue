<template>
  <!-- Skeleton loading -->
  <div v-if="isLoading" class="max-w-container-max mx-auto px-4 md:px-8 py-8 font-sans">
    <!-- Breadcrumb skeleton -->
    <div class="flex items-center gap-2 mb-6">
      <div class="h-3 w-16 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
      <div class="h-3 w-3 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
      <div class="h-3 w-14 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
      <div class="h-3 w-3 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
      <div class="h-3 w-40 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <!-- Gallery skeleton -->
      <div class="lg:col-span-7 flex gap-4">
        <div class="flex flex-col gap-3 shrink-0">
          <div v-for="i in 3" :key="i" class="w-20 h-20 rounded-lg bg-zinc-100 dark:bg-zinc-800 animate-pulse" />
        </div>
        <div class="flex-1 aspect-square rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse" />
      </div>

      <!-- Purchase skeleton -->
      <div class="lg:col-span-5 space-y-4">
        <div class="h-3 w-24 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
        <div class="h-8 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
        <div class="h-5 w-2/3 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
        <div class="h-4 w-1/2 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
        <div class="h-px bg-zinc-100 dark:bg-zinc-800 mt-4" />
        <div class="h-44 rounded-xl bg-zinc-100 dark:bg-zinc-800 animate-pulse" />
        <div class="h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 animate-pulse" />
        <div class="h-12 rounded-lg bg-zinc-100 dark:bg-zinc-800 animate-pulse" />
      </div>
    </div>
  </div>

  <!-- Not found -->
  <div
    v-else-if="!product"
    class="min-h-[60vh] flex flex-col items-center justify-center text-center px-4 font-sans"
  >
    <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mb-4">
      <span class="material-symbols-outlined text-[32px] text-zinc-400">search_off</span>
    </div>
    <h1 class="text-xl font-extrabold mb-2 text-zinc-800 dark:text-zinc-200">Товар не знайдено</h1>
    <p class="text-sm text-zinc-500 mb-6 max-w-sm">
      Перевірте правильність посилання або скористайтесь каталогом.
    </p>
    <button
      class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-lg text-sm font-bold transition-all"
      @click="router.push('/catalog')"
    >
      Перейти до каталогу
    </button>
  </div>

  <!-- Product page -->
  <div v-else class="font-sans">
    <!-- Breadcrumbs -->
    <nav class="max-w-container-max mx-auto px-4 md:px-8 pt-6 flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
      <a class="hover:text-[#00a046] transition-colors flex items-center gap-1 font-semibold" href="#" @click.prevent="router.push('/')">
        <span class="material-symbols-outlined text-[15px]">home</span>
        Головна
      </a>
      <span class="material-symbols-outlined text-[13px] text-zinc-300 dark:text-zinc-700">chevron_right</span>
      <a class="hover:text-[#00a046] transition-colors font-semibold" href="#" @click.prevent="router.push('/catalog')">Каталог</a>
      <span class="material-symbols-outlined text-[13px] text-zinc-300 dark:text-zinc-700">chevron_right</span>
      <span class="text-zinc-700 dark:text-zinc-300 font-semibold line-clamp-1 max-w-[220px]">{{ product.name }}</span>
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
        :reviews="reviews"
        @change-tab="activeTab = $event"
      />
    </main>

    <!-- Sticky Buy Bar -->
    <div
      class="fixed right-0 bottom-0 left-0 z-40 border-t border-zinc-100 dark:border-zinc-800 bg-white/96 dark:bg-zinc-900/96 backdrop-blur-md shadow-xl transition-transform duration-300 ease-in-out translate-y-full"
      :class="{ 'translate-y-0': showStickyBar }"
    >
      <div class="max-w-container-max mx-auto px-4 md:px-8 h-[68px] flex items-center gap-4">
        <img
          alt="product"
          class="w-10 h-10 object-contain rounded-lg border border-zinc-100 dark:border-zinc-800 bg-white p-1 hidden sm:block shrink-0"
          :src="selectedImage"
        />
        <div class="flex-1 min-w-0 text-left">
          <p class="font-bold text-sm text-zinc-900 dark:text-white truncate leading-tight">{{ product.name }}</p>
          <p class="text-[#00a046] font-black text-sm mt-0.5">{{ formatPrice(product.price) }}</p>
        </div>
        <div class="flex gap-2 shrink-0">
          <button
            class="hidden sm:flex border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-200 px-4 py-2.5 rounded-lg font-bold text-xs transition-all items-center gap-1.5"
            @click="openQuickOrder"
          >
            <span class="material-symbols-outlined text-[15px]">bolt</span>
            Швидке замовлення
          </button>
          <button
            class="bg-[#00a046] hover:bg-[#00b050] text-white px-5 py-2.5 rounded-lg font-bold text-xs shadow-sm transition-all active:scale-[0.98] flex items-center gap-1.5"
            @click="cartStore.addToCart(product)"
          >
            <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
            В кошик
          </button>
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
import { useProductDetail } from "@/features/product/composables/useProductDetail";
import ProductGallery from "@/widgets/ProductDetail/ProductGallery.vue";
import ProductPurchase from "@/widgets/ProductDetail/ProductPurchase.vue";
import ComboDeal from "@/widgets/ProductDetail/ComboDeal.vue";
import ProductTabs from "@/widgets/ProductDetail/ProductTabs.vue";
import QuickOrderModal from "@/widgets/Catalog/QuickOrderModal.vue";

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
  reviews,
  formatPrice,
  bundleSubtotal,
  bundleSavings,
  bundleTotal,
  setSelectedImage,
  selectNextImage,
  selectPreviousImage,
  toggleBundleItem,
  addBundleToCart,
  isQuickOrderOpen,
  openQuickOrder,
  closeQuickOrder,
} = useProductDetail();
</script>
