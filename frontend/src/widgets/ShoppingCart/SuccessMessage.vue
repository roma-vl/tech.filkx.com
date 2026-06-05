<template>
  <div
    class="max-w-2xl mx-auto bg-surface-container-lowest rounded-2xl border border-outline-variant p-8 md:p-12 text-center shadow-lg my-12"
  >
    <div
      class="w-20 h-20 mx-auto rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-600 mb-6"
    >
      <span class="material-symbols-outlined text-[44px]">check_circle</span>
    </div>
    <h2
      class="font-headline-md text-zinc-900 dark:text-white text-2xl font-bold mb-2"
    >
      Thank you for your order!
    </h2>
    <p class="text-on-surface-variant mb-6 text-gray-500">
      Your order
      <strong class="text-[#00a046] font-bold">{{
        orderSuccessData?.orderNumber || orderSuccessData?.order_number
      }}</strong>
      has been successfully placed.
    </p>
    <div
      class="bg-surface-container-low rounded-xl p-6 mb-8 text-left space-y-3 border border-outline-variant"
    >
      <div class="flex justify-between text-sm">
        <span class="text-on-surface-variant">Amount to Pay:</span>
        <span class="font-black text-[#00a046]">{{
          formatPrice(
            orderSuccessData?.totalPrice || orderSuccessData?.total_price || 0,
          )
        }}</span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-on-surface-variant">Customer Name:</span>
        <span class="font-semibold text-zinc-900 dark:text-white">{{
          orderSuccessData?.customerName || orderSuccessData?.customer_name
        }}</span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-on-surface-variant">Payment Method:</span>
        <span class="font-semibold uppercase text-zinc-700 dark:text-zinc-300">
          {{
            getPaymentMethodLabel(
              orderSuccessData?.paymentMethod ||
                orderSuccessData?.payment_method,
            )
          }}
        </span>
      </div>
    </div>
    <button
      class="bg-[#00a046] hover:bg-[#00b050] text-white px-8 py-3 rounded-lg font-bold transition-all shadow-sm"
      type="button"
      @click="$emit('continue')"
    >
      Continue Shopping
    </button>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  orderSuccessData: any;
  formatPrice: (p: number) => string;
}>();

defineEmits<{
  (e: "continue"): void;
}>();

function getPaymentMethodLabel(method?: string) {
  if (!method) return "";
  if (method === "cod") return "Cash on Delivery";
  if (method === "card") return "Online Card";
  return "Bank Transfer";
}
</script>
