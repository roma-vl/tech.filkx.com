<script setup lang="ts">
import { computed } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useCartStore } from "@/entities/order/model/cartStore";

const { t } = useI18n();
const router = useRouter();
const cartStore = useCartStore();

// Mirrors the free-shipping threshold, flat fee and tax rate used in the real
// checkout flow (see useShoppingCart.ts) so the drawer preview never disagrees
// with the totals shown at checkout.
const shippingThreshold = 5000;
const shippingFee = 250;
const taxRate = 0.075;

const shipping = computed(() =>
  cartStore.cartTotal >= shippingThreshold || cartStore.cart.length === 0
    ? 0
    : shippingFee,
);
const tax = computed(() => cartStore.cartTotal * taxRate);
const total = computed(
  () => cartStore.cartTotal + shipping.value + tax.value,
);

const shippingProgress = computed(() =>
  Math.min(100, (cartStore.cartTotal / shippingThreshold) * 100),
);

const remainingForFreeShipping = computed(() =>
  Math.max(0, shippingThreshold - cartStore.cartTotal),
);

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};

const checkout = () => {
  if (cartStore.cart.length === 0) return;
  cartStore.closeDrawer();
  router.push({ name: "cart" });
};
</script>

<template>
  <div
    v-if="cartStore.activeDrawer === 'cart'"
    class="fixed inset-0 z-[90] flex justify-end"
  >
    <!-- Backdrop Overlay -->
    <div
      class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
      @click="cartStore.closeDrawer()"
    />

    <!-- Drawer Panel -->
    <div
      class="relative w-full max-w-md bg-white dark:bg-zinc-900 h-full flex flex-col shadow-2xl border-l border-zinc-200 dark:border-zinc-800 animate-in slide-in-from-right duration-300 z-10"
    >
      <!-- Header -->
      <div
        class="p-6 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between"
      >
        <div class="flex items-center gap-2">
          <span class="material-symbols-outlined text-[#00a046] text-[28px]">shopping_cart</span>
          <h2 class="text-zinc-900 dark:text-white text-xl font-bold">
            {{ t("cart.title") }}
          </h2>
        </div>
        <button
          class="w-10 h-10 rounded-full hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 transition-colors"
          type="button"
          @click="cartStore.closeDrawer()"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Shipping Goal Tracker -->
      <div
        v-if="cartStore.cart.length > 0"
        class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/40 border-b border-zinc-200 dark:border-zinc-800 flex flex-col gap-2"
      >
        <div
          class="flex justify-between items-center text-xs font-semibold text-zinc-500 dark:text-zinc-400"
        >
          <span v-if="remainingForFreeShipping > 0">
            {{ t("cart.drawer.freeShippingRemaining", { amount: formatPrice(remainingForFreeShipping) }) }}
          </span>
          <span
            v-else
            class="text-[#00a046] flex items-center gap-1"
          >
            <span class="material-symbols-outlined text-[16px] text-[#00a046]">local_shipping</span>
            {{ t("cart.drawer.freeShippingQualified") }}
          </span>
          <span>{{ Math.round(shippingProgress) }}%</span>
        </div>
        <div
          class="w-full bg-zinc-200 dark:bg-zinc-700 h-2 rounded-full overflow-hidden"
        >
          <div
            class="bg-[#00a046] h-full rounded-full transition-all duration-500"
            :style="{ width: shippingProgress + '%' }"
          />
        </div>
      </div>

      <!-- Content (Scrollable list or Empty State) -->
      <div class="flex-grow overflow-y-auto p-6 flex flex-col gap-4">
        <!-- Empty State -->
        <div
          v-if="cartStore.cart.length === 0"
          class="flex-grow flex flex-col items-center justify-center text-center gap-4 py-12"
        >
          <div
            class="w-20 h-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-500 dark:text-zinc-400 border border-zinc-200 dark:border-zinc-700"
          >
            <span class="material-symbols-outlined text-[48px]">remove_shopping_cart</span>
          </div>
          <h3 class="text-zinc-900 dark:text-white text-lg font-bold">
            {{ t("cart.emptyTitle") }}
          </h3>
          <p class="text-zinc-500 dark:text-zinc-400 text-xs max-w-[240px]">
            {{ t("cart.drawer.emptyDescription") }}
          </p>
          <button
            class="bg-[#00a046] text-white px-8 py-3 rounded-lg font-bold hover:scale-105 active:scale-95 transition-all shadow-md shadow-[#00a046]/10 mt-2"
            type="button"
            @click="cartStore.closeDrawer()"
          >
            {{ t("cart.drawer.startShopping") }}
          </button>
        </div>

        <!-- Cart Items -->
        <div
          v-for="item in cartStore.cart"
          v-else
          :key="item.id"
          class="flex gap-4 p-4 bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/50 rounded-xl relative group hover:shadow-md dark:hover:shadow-black/30 transition-shadow"
        >
          <!-- Thumbnail -->
          <div
            class="w-20 h-20 bg-white dark:bg-zinc-900 rounded-lg p-2 flex items-center justify-center shrink-0"
          >
            <img
              class="w-full h-full object-contain"
              :src="item.image"
              :alt="item.name"
            >
          </div>

          <!-- Details -->
          <div class="flex-grow flex flex-col justify-between">
            <div>
              <span
                class="text-[10px] font-bold text-[#00a046] uppercase tracking-wider"
              >{{ (item as any).category || t("common.product") }}</span>
              <h4
                class="text-sm text-zinc-900 dark:text-white line-clamp-1 leading-tight font-bold"
              >
                {{ item.name }}
              </h4>
            </div>

            <div class="flex items-center justify-between mt-2">
              <span class="font-bold text-[#00a046] text-sm">{{ formatPrice(item.price * item.quantity) }}</span>

              <!-- Quantity Selector -->
              <div
                class="flex items-center border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden h-8 bg-white dark:bg-zinc-800"
              >
                <button
                  class="w-8 h-full flex items-center justify-center hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors text-zinc-700 dark:text-zinc-300"
                  type="button"
                  @click="
                    cartStore.updateCartQuantity(item.id, item.quantity - 1)
                  "
                >
                  <span class="material-symbols-outlined text-[16px]">remove</span>
                </button>
                <span class="px-3 text-xs font-bold text-zinc-900 dark:text-white">{{
                  item.quantity
                }}</span>
                <button
                  class="w-8 h-full flex items-center justify-center hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors text-zinc-700 dark:text-zinc-300"
                  type="button"
                  @click="
                    cartStore.updateCartQuantity(item.id, item.quantity + 1)
                  "
                >
                  <span class="material-symbols-outlined text-[16px]">add</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Delete button -->
          <button
            class="absolute top-2 right-2 text-zinc-400 dark:text-zinc-500 hover:text-red-500 dark:hover:text-red-400 transition-colors"
            type="button"
            @click="cartStore.removeFromCart(item.id)"
          >
            <span class="material-symbols-outlined text-[18px]">delete</span>
          </button>
        </div>
      </div>

      <!-- Footer (Summary + Checkout button) -->
      <div
        v-if="cartStore.cart.length > 0"
        class="p-6 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900"
      >
        <div class="flex flex-col gap-3 mb-6">
          <div
            class="flex justify-between items-center text-xs text-zinc-500 dark:text-zinc-400 font-semibold"
          >
            <span>{{ t("cart.summary.subtotal") }}</span>
            <span class="text-zinc-900 dark:text-white font-bold">{{ formatPrice(cartStore.cartTotal) }}</span>
          </div>
          <div
            class="flex justify-between items-center text-xs text-zinc-500 dark:text-zinc-400 font-semibold"
          >
            <span>{{ t("cart.summary.taxEstimate") }}</span>
            <span class="text-zinc-900 dark:text-white font-bold">{{ formatPrice(tax) }}</span>
          </div>
          <div
            class="flex justify-between items-center text-xs text-zinc-500 dark:text-zinc-400 font-semibold"
          >
            <span>{{ t("cart.summary.shippingEstimate") }}</span>
            <span
              class="font-bold"
              :class="shipping === 0 ? 'text-[#00a046]' : 'text-zinc-900 dark:text-white'"
            >
              {{ shipping === 0 ? t("cart.summary.free") : formatPrice(shipping) }}
            </span>
          </div>
          <div class="h-px bg-zinc-200 dark:bg-zinc-800 my-1" />
          <div
            class="flex justify-between items-center text-lg text-zinc-900 dark:text-white font-bold"
          >
            <span>{{ t("cart.summary.total") }}</span>
            <span class="font-bold text-[#00a046]">
              {{ formatPrice(total) }}
            </span>
          </div>
        </div>

        <button
          class="w-full bg-[#00a046] text-white py-4 rounded-xl font-bold hover:bg-[#00b050] transition-all flex items-center justify-center gap-2 shadow-lg shadow-[#00a046]/20 hover:scale-[1.02] active:scale-95 duration-200"
          type="button"
          @click="checkout()"
        >
          {{ t("cart.summary.proceedToCheckout") }}
          <span class="material-symbols-outlined">arrow_forward</span>
        </button>
      </div>
    </div>
  </div>
</template>
