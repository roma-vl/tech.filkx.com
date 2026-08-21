<template>
  <div
    class="bg-zinc-50 dark:bg-zinc-900 rounded-xl p-6 border border-zinc-200 dark:border-zinc-800 sticky top-24 shadow-sm"
  >
    <h2 class="text-zinc-900 dark:text-white text-xl font-bold mb-6">
      {{ t("cart.summary.orderSummary") }}
    </h2>
    <div class="space-y-4 mb-6">
      <div class="flex justify-between">
        <span class="text-zinc-500 dark:text-zinc-400">{{
          t("cart.summary.subtotal")
        }}</span>
        <span class="font-semibold text-zinc-900 dark:text-white">{{
          formatPrice(cartTotal)
        }}</span>
      </div>
      <div
        v-if="appliedPromo"
        class="flex justify-between items-center text-[#00a046] font-bold"
      >
        <span class="flex items-center gap-1">
          <span class="material-symbols-outlined text-[18px]">sell</span>
          {{ t("cart.summary.promoCode") }} ({{ appliedPromo }})
          <button
            class="text-red-500 hover:text-red-700 ml-1 flex items-center"
            type="button"
            :title="t('cart.summary.removePromoCode')"
            @click="$emit('removePromo')"
          >
            <span class="material-symbols-outlined text-[16px]">close</span>
          </button>
        </span>
        <span class="font-semibold">-{{ formatPrice(discount) }}</span>
      </div>
      <div class="flex justify-between">
        <span class="text-zinc-500 dark:text-zinc-400">{{
          t("cart.summary.shippingEstimate")
        }}</span>
        <span class="font-semibold text-zinc-900 dark:text-white">{{
          shipping === 0 ? t("cart.summary.free") : formatPrice(shipping)
        }}</span>
      </div>
      <div class="flex justify-between">
        <span class="text-zinc-500 dark:text-zinc-400">{{
          t("cart.summary.taxEstimate")
        }}</span>
        <span class="font-semibold text-zinc-900 dark:text-white">{{
          formatPrice(tax)
        }}</span>
      </div>
      <div
        class="pt-4 border-t border-zinc-200 dark:border-zinc-800 flex justify-between text-[#00a046] font-bold text-lg"
      >
        <span>{{ t("cart.summary.total") }}</span>
        <span>{{ formatPrice(total) }}</span>
      </div>
    </div>

    <!-- Promo Code input -->
    <div v-if="!isCheckoutMode" class="mb-6">
      <label
        class="block text-zinc-500 dark:text-zinc-400 text-sm font-semibold mb-2"
        for="promo"
        >{{ t("cart.summary.promoCode") }}</label
      >
      <div class="flex gap-2">
        <input
          id="promo"
          :value="promoCode"
          class="flex-grow bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
          :placeholder="t('cart.summary.enterCode')"
          type="text"
          @input="
            $emit('update:promoCode', ($event.target as HTMLInputElement).value)
          "
        />
        <button
          class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-white rounded-lg font-semibold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
          type="button"
          @click="$emit('applyPromo')"
        >
          {{ t("cart.summary.apply") }}
        </button>
      </div>
    </div>

    <!-- Out of Stock Warning -->
    <div
      v-if="hasOutOfStockItems"
      class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/20 text-xs font-semibold p-3 rounded-lg border border-red-200 dark:border-red-900/50 mb-4 flex items-start gap-1.5"
    >
      <span class="material-symbols-outlined text-[18px] shrink-0 text-red-500"
        >warning</span
      >
      <span>{{ t("cart.summary.outOfStockWarning") }}</span>
    </div>

    <!-- Primary Checkout Button -->
    <button
      class="w-full py-4 bg-[#00a046] hover:bg-[#00b050] text-white rounded-xl font-bold shadow-md active:scale-95 transition-all mb-4 uppercase tracking-wider flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
      type="button"
      :disabled="isSubmitting || hasOutOfStockItems"
      @click="$emit('submit')"
    >
      <span
        v-if="isSubmitting"
        class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"
      />
      <span v-else class="material-symbols-outlined text-[20px]"
        >shopping_cart_checkout</span
      >
      {{
        isSubmitting
          ? t("cart.summary.processing")
          : isCheckoutMode
            ? t("cart.summary.placeOrder")
            : t("cart.summary.proceedToCheckout")
      }}
    </button>

    <div
      class="flex items-center justify-center gap-2 text-zinc-400 dark:text-zinc-500 text-xs"
    >
      <span class="material-symbols-outlined text-[18px]">lock</span>
      {{ t("cart.summary.secureCheckout") }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";

const { t } = useI18n();

defineProps<{
  cartTotal: number;
  discount: number;
  shipping: number;
  tax: number;
  total: number;
  isCheckoutMode: boolean;
  isSubmitting: boolean;
  promoCode: string;
  appliedPromo: string;
  hasOutOfStockItems: boolean;
  formatPrice: (p: number) => string;
}>();

defineEmits<{
  (e: "update:promoCode", val: string): void;
  (e: "applyPromo"): void;
  (e: "removePromo"): void;
  (e: "submit"): void;
}>();
</script>
