<template>
  <main
    class="max-w-container-max mx-auto px-margin-desktop py-stack-lg min-h-screen"
  >
    <!-- Success State -->
    <SuccessMessage
      v-if="isSuccessMode"
      :order-success-data="orderSuccessData"
      :format-price="formatPrice"
      @continue="router.push('/catalog')"
    />

    <!-- Cart / Checkout State -->
    <div v-else>
      <div
        class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-stack-lg mb-10"
      >
        <div>
          <h1
            class="font-headline-lg text-zinc-900 dark:text-white text-3xl font-bold"
          >
            {{ isCheckoutMode ? t("cart.checkoutTitle") : t("cart.title") }}
          </h1>
          <p class="mt-2 text-zinc-500 dark:text-zinc-400">
            {{
              isCheckoutMode
                ? t("cart.checkoutSubtitle")
                : t("cart.itemsReady", { count: cartStore.cartCount })
            }}
          </p>
        </div>
        <button
          class="font-bold text-sm hover:underline flex items-center gap-1 text-blue-600 dark:text-blue-400"
          type="button"
          @click="
            isCheckoutMode
              ? (isCheckoutMode = false)
              : router.push({ name: 'catalog' })
          "
        >
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          {{ isCheckoutMode ? t("cart.backToCart") : t("cart.continueShopping") }}
        </button>
      </div>

      <!-- Empty State -->
      <div
        v-if="cartStore.cart.length === 0"
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-12 text-center"
      >
        <div
          class="w-20 h-20 mx-auto rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 mb-5"
        >
          <span class="material-symbols-outlined text-[44px]">remove_shopping_cart</span>
        </div>
        <h2
          class="font-headline-md text-zinc-900 dark:text-white text-xl font-bold mb-2"
        >
          {{ t("cart.emptyTitle") }}
        </h2>
        <p class="mb-6 text-zinc-500 dark:text-zinc-400">
          {{ t("cart.emptySubtitle") }}
        </p>
        <button
          class="bg-[#00a046] text-white px-8 py-3 rounded-lg font-bold hover:bg-[#00b050] transition-all"
          type="button"
          @click="router.push({ name: 'catalog' })"
        >
          {{ t("cart.exploreCatalog") }}
        </button>
      </div>

      <!-- Cart Grid Layout -->
      <div
        v-else
        class="cart-layout"
      >
        <!-- Left Column -->
        <section class="space-y-stack-md">
          <!-- Checkout Details Form -->
          <CheckoutForm
            v-if="isCheckoutMode"
            v-model="checkoutForm"
          />

          <!-- Cart Item list -->
          <CartItemsList
            v-else
            :cart="cartStore.cart"
            :wishlist="cartStore.wishlist"
            :format-price="formatPrice"
            @update-quantity="cartStore.updateCartQuantity"
            @remove="cartStore.removeFromCart"
            @move-to-cart="moveToCart"
          />
        </section>

        <!-- Right Column (Summary Card) -->
        <aside>
          <CartSummary
            v-model:promo-code="promoCode"
            :cart-total="cartStore.cartTotal"
            :discount="discount"
            :shipping="shipping"
            :tax="tax"
            :total="total"
            :is-checkout-mode="isCheckoutMode"
            :is-submitting="isSubmitting"
            :applied-promo="appliedPromo"
            :has-out-of-stock-items="hasOutOfStockItems"
            :format-price="formatPrice"
            @apply-promo="applyPromo"
            @remove-promo="removePromo"
            @submit="
              isCheckoutMode ? handlePlaceOrder() : (isCheckoutMode = true)
            "
          />
        </aside>
      </div>

      <!-- Recommended section -->
      <section class="mt-20">
        <h2
          class="font-headline-md text-zinc-900 dark:text-white text-2xl font-bold mb-8"
        >
          {{ t("cart.recommended") }}
        </h2>
        <div class="recommended-grid">
          <ProductCard
            v-for="product in recommended"
            :key="product.id"
            :product="product"
            view-mode="grid"
          />
        </div>
      </section>

      <!-- Redirecting to LiqPay -->
      <div
        v-if="isRedirectingToPayment"
        class="fixed inset-0 z-[100] flex flex-col items-center justify-center gap-4 bg-black/60 backdrop-blur-sm"
      >
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-white/20 border-t-white" />
        <p class="text-white font-semibold">
          {{ t("cart.redirectingToPayment") }}
        </p>
      </div>
    </div>
  </main>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";
import { useShoppingCart } from "@/features/cart/composables/useShoppingCart";
import SuccessMessage from "@/widgets/ShoppingCart/SuccessMessage.vue";
import CheckoutForm from "@/widgets/ShoppingCart/CheckoutForm.vue";
import CartItemsList from "@/widgets/ShoppingCart/CartItemsList.vue";
import CartSummary from "@/widgets/ShoppingCart/CartSummary.vue";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";

const { t } = useI18n();

const {
  router,
  cartStore,
  promoCode,
  appliedPromo,
  discount,
  shipping,
  tax,
  total,
  isCheckoutMode,
  isSuccessMode,
  isSubmitting,
  orderSuccessData,
  checkoutForm,
  recommended,
  moveToCart,
  removePromo,
  hasOutOfStockItems,
  isRedirectingToPayment,
  formatPrice,
  applyPromo,
  addRecommended,
  handlePlaceOrder,
} = useShoppingCart();
</script>

<style scoped>
.cart-layout {
  display: grid;
  gap: 24px;
  grid-template-columns: 1fr;
}
.recommended-grid {
  display: grid;
  gap: 24px;
  grid-template-columns: 1fr;
}
@media (min-width: 768px) {
  .recommended-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (min-width: 1024px) {
  .cart-layout {
    grid-template-columns: minmax(0, 8fr) minmax(320px, 4fr);
  }
  .recommended-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}
</style>
