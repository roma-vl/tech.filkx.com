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
      Дякуємо за ваше замовлення!
    </h2>
    <p class="text-on-surface-variant mb-6 text-gray-500">
      Ваше замовлення
      <strong class="text-[#00a046] font-bold">{{
        orderSuccessData?.orderNumber || orderSuccessData?.order_number
      }}</strong>
      успішно створено.
    </p>

    <div
      class="bg-surface-container-low rounded-xl p-6 mb-8 text-left space-y-4 border border-outline-variant"
    >
      <div class="flex justify-between text-sm">
        <span class="text-on-surface-variant text-gray-500">Сума до сплати:</span>
        <span class="font-black text-[#00a046]">{{
          formatPrice(
            orderSuccessData?.totalPrice || orderSuccessData?.total_price || 0,
          )
        }}</span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-on-surface-variant text-gray-500">Покупець:</span>
        <span class="font-semibold text-zinc-900 dark:text-white">{{
          orderSuccessData?.customerName || orderSuccessData?.customer_name
        }}</span>
      </div>
      <div class="flex justify-between text-sm">
        <span class="text-on-surface-variant text-gray-500">Спосіб оплати:</span>
        <span class="font-semibold text-zinc-700 dark:text-zinc-300">
          {{
            getPaymentMethodLabel(
              orderSuccessData?.paymentMethod ||
                orderSuccessData?.payment_method,
            )
          }}
        </span>
      </div>

      <!-- Bank Transfer Manual Invoice Card -->
      <div
        v-if="
          (orderSuccessData?.paymentMethod || orderSuccessData?.payment_method) === 'bank'
        "
        class="mt-6 pt-6 border-t border-outline-variant space-y-4"
      >
        <div class="bg-[#00a046]/5 dark:bg-[#00a046]/10 border border-[#00a046]/20 p-4 rounded-xl">
          <h4 class="font-extrabold text-xs text-[#00a046] uppercase tracking-wider mb-3 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[18px]">account_balance</span>
            Реквізити для оплати (IBAN)
          </h4>
          
          <div class="space-y-3 text-xs">
            <!-- Receiver -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
              <span class="text-gray-500 dark:text-gray-400">Отримувач:</span>
              <span class="font-bold text-zinc-900 dark:text-white">ТОВ "ФІЛККС ТЕХНОЛОДЖІ"</span>
            </div>

            <!-- IBAN -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
              <span class="text-gray-500 dark:text-gray-400">Розрахунковий рахунок (IBAN):</span>
              <div class="flex items-center gap-2">
                <code class="font-mono font-bold bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded text-zinc-800 dark:text-zinc-200">UA893005260000026001234567890</code>
                <button
                  class="p-1 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded transition-colors text-[#00a046]"
                  type="button"
                  title="Скопіювати рахунок"
                  @click="copyToClipboard('UA893005260000026001234567890', 'iban')"
                >
                  <span class="material-symbols-outlined text-[16px]">{{ copiedField === 'iban' ? 'done' : 'content_copy' }}</span>
                </button>
              </div>
            </div>

            <!-- EDRPOU -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
              <span class="text-gray-500 dark:text-gray-400">Код ЄДРПОУ:</span>
              <div class="flex items-center gap-2">
                <code class="font-mono font-bold bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded text-zinc-800 dark:text-zinc-200">44012345</code>
                <button
                  class="p-1 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded transition-colors text-[#00a046]"
                  type="button"
                  title="Скопіювати ЄДРПОУ"
                  @click="copyToClipboard('44012345', 'edrpou')"
                >
                  <span class="material-symbols-outlined text-[16px]">{{ copiedField === 'edrpou' ? 'done' : 'content_copy' }}</span>
                </button>
              </div>
            </div>

            <!-- Purpose -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-1">
              <span class="text-gray-500 dark:text-gray-400">Призначення платежу:</span>
              <div class="flex items-center gap-2">
                <span class="font-bold text-zinc-900 dark:text-white">Оплата замовлення № {{ orderSuccessData?.orderNumber || orderSuccessData?.order_number }}</span>
                <button
                  class="p-1 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded transition-colors text-[#00a046]"
                  type="button"
                  title="Скопіювати призначення"
                  @click="copyToClipboard('Оплата замовлення № ' + (orderSuccessData?.orderNumber || orderSuccessData?.order_number), 'purpose')"
                >
                  <span class="material-symbols-outlined text-[16px]">{{ copiedField === 'purpose' ? 'done' : 'content_copy' }}</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <p class="text-xs text-amber-600 dark:text-amber-400 bg-amber-500/10 border border-amber-500/20 p-3 rounded-lg flex items-start gap-1.5 leading-normal">
          <span class="material-symbols-outlined text-[18px] shrink-0">info</span>
          <span>Будь ласка, здійсніть переказ за вказаними реквізитами. Замовлення буде передано в доставку після підтвердження оплати.</span>
        </p>
      </div>
    </div>

    <button
      class="bg-[#00a046] hover:bg-[#00b050] text-white px-8 py-3 rounded-lg font-bold transition-all shadow-sm"
      type="button"
      @click="$emit('continue')"
    >
      Продовжити покупки
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from "vue";

defineProps<{
  orderSuccessData: any;
  formatPrice: (p: number) => string;
}>();

defineEmits<{
  (e: "continue"): void;
}>();

const copiedField = ref<string | null>(null);

const copyToClipboard = (text: string, field: string) => {
  navigator.clipboard.writeText(text);
  copiedField.value = field;
  setTimeout(() => {
    if (copiedField.value === field) copiedField.value = null;
  }, 2000);
};

function getPaymentMethodLabel(method?: string) {
  if (!method) return "";
  if (method === "cod") return "Оплата при отриманні (післяплата)";
  if (method === "card") return "Онлайн-оплата (Visa/Mastercard, Monobank)";
  return "Банківський переказ (IBAN)";
}
</script>
