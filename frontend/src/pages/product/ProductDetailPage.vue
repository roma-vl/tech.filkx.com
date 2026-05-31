<template>
  <div
    v-if="isLoading"
    class="min-h-screen flex items-center justify-center bg-white dark:bg-zinc-950"
  >
    <div
      class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-emerald-500"
    />
  </div>
  <div
    v-else-if="!product"
    class="min-h-screen flex flex-col items-center justify-center text-center bg-white dark:bg-zinc-950 px-4"
  >
    <span class="material-symbols-outlined text-[64px] text-zinc-400 mb-4">search_off</span>
    <h1 class="text-xl font-extrabold mb-2 text-zinc-800 dark:text-zinc-200">
      Товар не знайдено
    </h1>
    <p class="text-zinc-550 dark:text-zinc-500 mb-6">
      Будь ласка, перевірте правильність посилання або скористайтесь каталогом.
    </p>
    <button
      class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-lg text-sm font-extrabold transition-all"
      @click="router.push('/catalog')"
    >
      Перейти до каталогу
    </button>
  </div>
  <div v-else>
    <main
      class="max-w-container-max mx-auto px-4 md:px-8 py-8 text-zinc-800 dark:text-zinc-200 font-sans"
    >
      <!-- Breadcrumbs -->
      <nav
        class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-550 mb-8 font-bold text-left"
      >
        <a
          class="hover:text-[#00a046] transition-colors"
          href="#"
          @click.prevent="router.push('/')"
        >Головна</a>
        <span class="material-symbols-outlined text-[12px]">chevron_right</span>
        <a
          class="hover:text-[#00a046] transition-colors"
          href="#"
          @click.prevent="router.push('/catalog')"
        >{{ product.category }}</a>
        <span class="material-symbols-outlined text-[12px]">chevron_right</span>
        <span class="text-zinc-800 dark:text-zinc-100 font-extrabold">{{
          product.name
        }}</span>
      </nav>

      <!-- Hero block -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Gallery Column -->
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

        <!-- Purchase Column -->
        <div class="lg:col-span-5">
          <ProductPurchase
            :product="product"
            :available-colors="availableColors"
            :selected-color="selectedColor"
            :available-storage="availableStorage"
            :selected-storage="selectedStorage"
            :format-price="formatPrice"
            @select-variant="selectVariantByAttributes"
          />
        </div>
      </div>

      <!-- Combo Bundle section -->
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

      <!-- Tabs and specs review panel -->
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

    <!-- Sticky Bottom Buy Bar -->
    <div
      class="fixed right-0 bottom-0 left-0 z-40 border-t border-zinc-200 dark:border-zinc-800 bg-white/95 dark:bg-zinc-900/95 shadow-lg backdrop-blur-md transition-transform duration-300 ease-in-out translate-y-full"
      :class="{ 'translate-y-0': showStickyBar }"
    >
      <div
        class="max-w-container-max mx-auto px-4 md:px-8 h-20 flex items-center justify-between gap-6"
      >
        <div class="flex items-center gap-4 overflow-hidden min-w-0">
          <img
            alt="Компактне прев'ю товару"
            class="w-12 h-12 object-contain rounded-lg border border-zinc-200 dark:border-zinc-800 hidden sm:block p-1 bg-white"
            :src="selectedImage"
          >
          <div class="truncate text-left">
            <p
              class="font-black text-zinc-900 dark:text-white text-sm truncate tracking-tight font-bold"
            >
              {{ product.name }}
            </p>
            <div class="flex items-center gap-3 text-xs mt-0.5">
              <span class="text-[#00a046] font-black font-bold">{{
                formatPrice(product.price)
              }}</span>
              <span
                class="text-[9px] font-black text-white bg-[#00a046] px-1.5 py-0.5 rounded uppercase tracking-wider font-bold"
              >В наявності</span>
            </div>
          </div>
        </div>
        <button
          class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-3 rounded-lg font-extrabold text-xs shadow-md transition-all active:scale-[0.98] shrink-0 uppercase tracking-wider font-bold"
          type="button"
          @click="cartStore.addToCart(product)"
        >
          Додати в кошик
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useProductDetail } from "@/features/product/composables/useProductDetail";
import ProductGallery from "@/widgets/ProductDetail/ProductGallery.vue";
import ProductPurchase from "@/widgets/ProductDetail/ProductPurchase.vue";
import ComboDeal from "@/widgets/ProductDetail/ComboDeal.vue";
import ProductTabs from "@/widgets/ProductDetail/ProductTabs.vue";

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
} = useProductDetail();
</script>

<style scoped>
</style>
