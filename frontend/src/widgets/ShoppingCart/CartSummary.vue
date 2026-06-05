<template>
  <div
    class="bg-surface-container-low rounded-xl p-6 border border-outline-variant sticky top-24 shadow-sm"
  >
    <h2
      class="font-headline-md text-zinc-900 dark:text-white text-xl font-bold mb-6"
    >
      Order Summary
    </h2>
    <div class="space-y-4 mb-6">
      <div class="flex justify-between font-body-lg text-body-lg">
        <span class="text-on-surface-variant text-gray-500">Subtotal</span>
        <span class="font-semibold text-zinc-900 dark:text-white">{{
          formatPrice(cartTotal)
        }}</span>
      </div>
      <div
        v-if="discount > 0"
        class="flex justify-between font-body-lg text-body-lg text-primary text-blue-600"
      >
        <span>Promo Discount</span>
        <span class="font-semibold">-{{ formatPrice(discount) }}</span>
      </div>
      <div class="flex justify-between font-body-lg text-body-lg">
        <span class="text-on-surface-variant text-gray-500"
          >Shipping Estimate</span
        >
        <span class="font-semibold text-zinc-900 dark:text-white">{{
          shipping === 0 ? "FREE" : formatPrice(shipping)
        }}</span>
      </div>
      <div class="flex justify-between font-body-lg text-body-lg">
        <span class="text-on-surface-variant text-gray-500">Tax Estimate</span>
        <span class="font-semibold text-zinc-900 dark:text-white">{{
          formatPrice(tax)
        }}</span>
      </div>
      <div
        class="pt-4 border-t border-outline-variant flex justify-between font-headline-md text-headline-md text-[#00a046] font-bold text-lg"
      >
        <span>Total</span>
        <span>{{ formatPrice(total) }}</span>
      </div>
    </div>

    <!-- Promo Code input -->
    <div v-if="!isCheckoutMode" class="mb-6">
      <label
        class="block font-label-md text-on-surface-variant text-sm font-semibold mb-2"
        for="promo"
        >Promo Code</label
      >
      <div class="flex gap-2">
        <input
          id="promo"
          :value="promoCode"
          class="flex-grow bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
          placeholder="Enter code"
          type="text"
          @input="
            $emit('update:promoCode', ($event.target as HTMLInputElement).value)
          "
        />
        <button
          class="px-4 py-2 border border-on-surface text-on-surface rounded-lg font-label-md hover:bg-surface-variant transition-colors dark:text-white"
          type="button"
          @click="$emit('applyPromo')"
        >
          Apply
        </button>
      </div>
    </div>

    <!-- Primary Checkout Button -->
    <button
      class="w-full py-4 bg-[#00a046] hover:bg-[#00b050] text-white rounded-xl font-bold shadow-md active:scale-95 transition-all mb-4 uppercase tracking-wider flex items-center justify-center gap-2"
      type="button"
      :disabled="isSubmitting"
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
          ? "Processing..."
          : isCheckoutMode
            ? "Place Order"
            : "Proceed to Checkout"
      }}
    </button>

    <div
      class="flex items-center justify-center gap-2 text-on-surface-variant font-label-md text-xs text-gray-400"
    >
      <span class="material-symbols-outlined text-[18px]">lock</span>
      Secure Checkout with SSL Encryption
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  cartTotal: number;
  discount: number;
  shipping: number;
  tax: number;
  total: number;
  isCheckoutMode: boolean;
  isSubmitting: boolean;
  promoCode: string;
  formatPrice: (p: number) => string;
}>();

defineEmits<{
  (e: "update:promoCode", val: string): void;
  (e: "applyPromo"): void;
  (e: "submit"): void;
}>();
</script>
