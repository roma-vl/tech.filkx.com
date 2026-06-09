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
            {{ isCheckoutMode ? "Secure Checkout" : "Your Shopping Cart" }}
          </h1>
          <p class="text-on-surface-variant mt-2 text-gray-500">
            {{
              isCheckoutMode
                ? "Please fill in your delivery details to finalize the order."
                : `${cartStore.cartCount} items ready for secure checkout.`
            }}
          </p>
        </div>
        <button
          class="text-primary font-bold text-sm hover:underline flex items-center gap-1 text-blue-650"
          type="button"
          @click="
            isCheckoutMode
              ? (isCheckoutMode = false)
              : router.push({ name: 'catalog' })
          "
        >
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          {{ isCheckoutMode ? "Back to Cart" : "Continue Shopping" }}
        </button>
      </div>

      <!-- Empty State -->
      <div
        v-if="cartStore.cart.length === 0"
        class="bg-surface-container-lowest rounded-xl border border-outline-variant p-12 text-center"
      >
        <div
          class="w-20 h-20 mx-auto rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant mb-5"
        >
          <span class="material-symbols-outlined text-[44px]">remove_shopping_cart</span>
        </div>
        <h2
          class="font-headline-md text-zinc-900 dark:text-white text-xl font-bold mb-2"
        >
          Your cart is empty
        </h2>
        <p class="text-on-surface-variant mb-6 text-gray-500">
          Add premium electronics to start checkout.
        </p>
        <button
          class="bg-[#00a046] text-white px-8 py-3 rounded-lg font-bold hover:bg-emerald-600 transition-all"
          type="button"
          @click="router.push({ name: 'catalog' })"
        >
          Explore Catalog
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
          Recommended for you
        </h2>
        <div class="recommended-grid">
          <ProductCard
            v-for="product in recommended"
            :key="product.id"
            :product="product"
            view-mode="grid"
            @quick-view="openQuickView"
          />
        </div>
      </section>

      <!-- Quick View Modal -->
      <QuickViewModal
        v-if="isQuickViewOpen && quickViewProduct"
        :product="quickViewProduct"
        @close="closeQuickView"
      />

      <!-- Payment Simulator Modal -->
      <PaymentSimulatorModal
        v-if="isPaymentSimulatorOpen && pendingSuccessData"
        :order-data="pendingSuccessData"
        :format-price="formatPrice"
        @success="confirmSimulatedPayment"
        @close="isPaymentSimulatorOpen = false"
      />
    </div>
  </main>
</template>

<script setup lang="ts">
import { useShoppingCart } from "@/features/cart/composables/useShoppingCart";
import SuccessMessage from "@/widgets/ShoppingCart/SuccessMessage.vue";
import CheckoutForm from "@/widgets/ShoppingCart/CheckoutForm.vue";
import CartItemsList from "@/widgets/ShoppingCart/CartItemsList.vue";
import CartSummary from "@/widgets/ShoppingCart/CartSummary.vue";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";
import QuickViewModal from "@/widgets/Catalog/QuickViewModal.vue";
import PaymentSimulatorModal from "@/widgets/ShoppingCart/PaymentSimulatorModal.vue";

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
  isQuickViewOpen,
  quickViewProduct,
  openQuickView,
  closeQuickView,
  isPaymentSimulatorOpen,
  pendingSuccessData,
  confirmSimulatedPayment,
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
.filled-symbol {
  font-variation-settings: "FILL" 1;
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
