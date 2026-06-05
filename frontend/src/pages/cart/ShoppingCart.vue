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
            :saved-item="savedItem"
            :format-price="formatPrice"
            @update-quantity="cartStore.updateCartQuantity"
            @remove="cartStore.removeFromCart"
            @move-to-cart="cartStore.addToCart"
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
            :format-price="formatPrice"
            @apply-promo="applyPromo"
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
          <article
            v-for="product in recommended"
            :key="product.id"
            class="group bg-surface-container-lowest rounded-xl border border-outline-variant p-4 hover:shadow-lg transition-all"
          >
            <div
              class="aspect-square bg-surface-container rounded-lg mb-4 overflow-hidden"
            >
              <img
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                :src="product.image"
                :alt="product.name"
              >
            </div>
            <div class="flex justify-between items-start mb-2">
              <h4
                class="font-title-md line-clamp-1 text-zinc-800 dark:text-zinc-200 font-bold"
              >
                {{ product.name }}
              </h4>
              <button
                class="text-on-surface-variant hover:text-primary"
                type="button"
                @click="cartStore.toggleWishlist(product)"
              >
                <span
                  class="material-symbols-outlined"
                  :class="{
                    'text-red-500': cartStore.isInWishlist(product.id),
                  }"
                >favorite</span>
              </button>
            </div>
            <div class="flex items-center gap-1 mb-2">
              <span
                v-for="star in 5"
                :key="star"
                class="material-symbols-outlined text-[16px]"
                :class="
                  star <= product.rating
                    ? 'text-star-rating filled-symbol'
                    : 'text-outline-variant'
                "
              >star</span>
              <span
                class="text-label-md text-on-surface-variant ml-1 text-xs text-gray-500"
              >({{ product.reviews }})</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="font-title-lg text-lg font-bold text-[#00a046]">{{
                formatPrice(product.price)
              }}</span>
              <button
                class="bg-secondary-container text-on-secondary-container p-2 rounded-full hover:bg-primary hover:text-on-primary transition-colors"
                type="button"
                @click="addRecommended(product)"
              >
                <span class="material-symbols-outlined">add_shopping_cart</span>
              </button>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup lang="ts">
import { useShoppingCart } from "@/features/cart/composables/useShoppingCart";
import SuccessMessage from "@/widgets/ShoppingCart/SuccessMessage.vue";
import CheckoutForm from "@/widgets/ShoppingCart/CheckoutForm.vue";
import CartItemsList from "@/widgets/ShoppingCart/CartItemsList.vue";
import CartSummary from "@/widgets/ShoppingCart/CartSummary.vue";

const {
  router,
  cartStore,
  promoCode,
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
  savedItem,
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
