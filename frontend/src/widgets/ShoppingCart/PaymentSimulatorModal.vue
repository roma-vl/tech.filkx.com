<script setup lang="ts">
import { ref } from "vue";

const props = defineProps<{
  orderData: any;
  formatPrice: (p: number) => string;
}>();

const emit = defineEmits<{
  (e: "success"): void;
  (e: "close"): void;
}>();

const cardNumber = ref("");
const cardExpiry = ref("");
const cardCvv = ref("");
const cardHolder = ref("");
const errors = ref({
  cardNumber: "",
  cardExpiry: "",
  cardCvv: "",
  cardHolder: "",
});

const step = ref<"card" | "processing" | "success">("card");

const formatCardNumber = () => {
  let val = cardNumber.value.replace(/\D/g, "");
  if (val.length > 16) val = val.substring(0, 16);
  const matches = val.match(/\d{4,16}/g);
  const match = (matches && matches[0]) || "";
  const parts = [];

  for (let i = 0, len = match.length; i < len; i += 4) {
    parts.push(match.substring(i, i + 4));
  }

  if (parts.length > 0) {
    cardNumber.value = parts.join(" ");
  } else {
    cardNumber.value = val;
  }
};

const formatExpiry = () => {
  let val = cardExpiry.value.replace(/\D/g, "");
  if (val.length > 4) val = val.substring(0, 4);
  if (val.length >= 2) {
    cardExpiry.value = val.substring(0, 2) + "/" + val.substring(2, 4);
  } else {
    cardExpiry.value = val;
  }
};

const formatCvv = () => {
  let val = cardCvv.value.replace(/\D/g, "");
  if (val.length > 3) val = val.substring(0, 3);
  cardCvv.value = val;
};

const handlePay = () => {
  errors.value = { cardNumber: "", cardExpiry: "", cardCvv: "", cardHolder: "" };
  let hasError = false;

  const rawCard = cardNumber.value.replace(/\s/g, "");
  if (rawCard.length !== 16) {
    errors.value.cardNumber = "Неправильний номер картки";
    hasError = true;
  }

  if (!/^\d{2}\/\d{2}$/.test(cardExpiry.value)) {
    errors.value.cardExpiry = "Термін дії (ММ/РР)";
    hasError = true;
  }

  if (cardCvv.value.length !== 3) {
    errors.value.cardCvv = "CVV код";
    hasError = true;
  }

  if (!cardHolder.value.trim()) {
    errors.value.cardHolder = "Ім'я власника картки";
    hasError = true;
  }

  if (hasError) return;

  step.value = "processing";
  setTimeout(() => {
    step.value = "success";
    setTimeout(() => {
      emit("success");
    }, 1500);
  }, 2000);
};
</script>

<template>
  <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
    <!-- Backdrop Overlay -->
    <div
      class="absolute inset-0 bg-black/85 backdrop-blur-md transition-opacity"
      @click="step === 'card' && $emit('close')"
    />

    <!-- Modal Panel -->
    <div
      class="relative w-full max-w-[420px] bg-zinc-950 text-white rounded-3xl shadow-2xl border border-zinc-800/80 overflow-hidden flex flex-col transition-all duration-300 font-sans"
    >
      <!-- Header -->
      <div class="p-6 border-b border-zinc-900 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <!-- Monobank signature cat / logo style -->
          <div class="w-8 h-8 rounded-full bg-pink-600 flex items-center justify-center font-bold text-sm tracking-tighter">
            <span>mono</span>
          </div>
          <div>
            <h3 class="font-extrabold text-sm tracking-wide uppercase text-zinc-300">
              mono | Checkout
            </h3>
            <p class="text-[10px] text-zinc-500 font-medium tracking-wider">
              ТОВ "ФІЛККС ТЕХНОЛОДЖІ"
            </p>
          </div>
        </div>
        <button
          v-if="step === 'card'"
          class="w-8 h-8 rounded-full hover:bg-zinc-900 flex items-center justify-center text-zinc-400 hover:text-white transition-colors"
          @click="$emit('close')"
        >
          <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 flex-grow flex flex-col justify-between min-h-[380px]">
        <!-- STEP 1: Card Input Details -->
        <div v-if="step === 'card'" class="space-y-6 flex-grow">
          <!-- Amount Banner -->
          <div class="bg-zinc-900/60 p-4 rounded-2xl border border-zinc-850 flex justify-between items-center">
            <div>
              <p class="text-xs text-zinc-500 font-semibold">До сплати:</p>
              <p class="text-sm font-bold text-zinc-400">
                Замовлення № {{ orderData?.orderNumber || orderData?.order_number }}
              </p>
            </div>
            <span class="text-2xl font-black text-pink-500 tracking-tight">
              {{ formatPrice(orderData?.totalPrice || orderData?.total_price || 0) }}
            </span>
          </div>

          <!-- Card Form Wrapper -->
          <div class="space-y-4">
            <!-- Card Number -->
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] font-black uppercase text-zinc-400 tracking-wider">Номер картки</label>
              <div class="relative">
                <input
                  v-model="cardNumber"
                  class="w-full bg-zinc-900 border border-zinc-805 rounded-xl px-4 py-3 text-sm focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all tracking-widest placeholder-zinc-700"
                  placeholder="0000 0000 0000 0000"
                  type="text"
                  @input="formatCardNumber"
                />
                <span class="absolute right-4 top-3.5 text-zinc-500 material-symbols-outlined text-[20px]">credit_card</span>
              </div>
              <p v-if="errors.cardNumber" class="text-xs text-red-500 font-semibold">{{ errors.cardNumber }}</p>
            </div>

            <!-- Expiry & CVV -->
            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col gap-1.5">
                <label class="text-[11px] font-black uppercase text-zinc-400 tracking-wider">Термін дії</label>
                <input
                  v-model="cardExpiry"
                  class="w-full bg-zinc-900 border border-zinc-805 rounded-xl px-4 py-3 text-sm text-center focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all placeholder-zinc-700"
                  placeholder="ММ/РР"
                  type="text"
                  @input="formatExpiry"
                />
                <p v-if="errors.cardExpiry" class="text-xs text-red-500 font-semibold text-center">{{ errors.cardExpiry }}</p>
              </div>
              <div class="flex flex-col gap-1.5">
                <label class="text-[11px] font-black uppercase text-zinc-400 tracking-wider">CVC2 / CVV</label>
                <input
                  v-model="cardCvv"
                  class="w-full bg-zinc-900 border border-zinc-805 rounded-xl px-4 py-3 text-sm text-center focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all placeholder-zinc-700 tracking-widest"
                  placeholder="***"
                  type="password"
                  @input="formatCvv"
                />
                <p v-if="errors.cardCvv" class="text-xs text-red-500 font-semibold text-center">{{ errors.cardCvv }}</p>
              </div>
            </div>

            <!-- Cardholder Name -->
            <div class="flex flex-col gap-1.5">
              <label class="text-[11px] font-black uppercase text-zinc-400 tracking-wider">Власник картки</label>
              <input
                v-model="cardHolder"
                class="w-full bg-zinc-900 border border-zinc-805 rounded-xl px-4 py-3 text-sm focus:border-pink-500 focus:ring-1 focus:ring-pink-500 outline-none transition-all placeholder-zinc-700 uppercase"
                placeholder="IVAN IVANOV"
                type="text"
              />
              <p v-if="errors.cardHolder" class="text-xs text-red-500 font-semibold">{{ errors.cardHolder }}</p>
            </div>
          </div>

          <!-- Pay Button -->
          <button
            class="w-full py-4 mt-6 bg-pink-600 hover:bg-pink-500 text-white rounded-xl font-bold tracking-wider uppercase shadow-lg shadow-pink-600/20 active:scale-98 transition-all flex items-center justify-center gap-2 text-sm"
            type="button"
            @click="handlePay"
          >
            <span class="material-symbols-outlined text-[18px]">lock</span>
            Сплатити {{ formatPrice(orderData?.totalPrice || orderData?.total_price || 0) }}
          </button>
        </div>

        <!-- STEP 2: Processing Payment Simulation -->
        <div v-else-if="step === 'processing'" class="flex-grow flex flex-col items-center justify-center text-center space-y-6">
          <div class="relative flex items-center justify-center">
            <!-- Spinner -->
            <div class="animate-spin rounded-full h-20 w-20 border-4 border-zinc-850 border-t-pink-500" />
            <span class="absolute material-symbols-outlined text-[36px] text-pink-500">sync_saved_locally</span>
          </div>
          <div>
            <h4 class="text-lg font-black tracking-wide">Обробка транзакції...</h4>
            <p class="text-xs text-zinc-500 mt-2 max-w-[260px] mx-auto">
              Ми з'єднуємося з банком та авторизуємо платіж. Це займе лише кілька секунд.
            </p>
          </div>
        </div>

        <!-- STEP 3: Payment Success screen -->
        <div v-else class="flex-grow flex flex-col items-center justify-center text-center space-y-6 animate-in zoom-in-95 duration-200">
          <div class="w-20 h-20 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-500">
            <span class="material-symbols-outlined text-[48px] animate-bounce">check_circle</span>
          </div>
          <div>
            <h4 class="text-lg font-black text-emerald-400 tracking-wide">Оплата успішна!</h4>
            <p class="text-xs text-zinc-400 mt-2">
              Дякуємо! Кошти успішно списано з картки.
            </p>
            <p class="text-[10px] text-zinc-650 mt-1">
              Сесія оплати закривається...
            </p>
          </div>
        </div>

        <!-- Security Badge -->
        <div class="pt-4 border-t border-zinc-900 flex justify-center items-center gap-6 text-[10px] text-zinc-600 font-bold uppercase tracking-widest mt-6">
          <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">shield</span>PCI DSS Compliant</span>
          <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">verified</span>3D Secure</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.font-sans {
  font-family: 'Outfit', 'Inter', system-ui, -apple-system, sans-serif;
}
.border-zinc-850 {
  border-color: #27272a;
}
.border-zinc-805 {
  border-color: #3f3f46;
}
</style>
