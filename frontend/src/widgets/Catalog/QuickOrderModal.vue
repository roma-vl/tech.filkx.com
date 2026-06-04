<template>
  <div
    v-if="isOpen && product"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg max-w-md w-full shadow-2xl overflow-hidden flex flex-col relative"
    >
      <!-- Header -->
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 px-6 py-4 flex justify-between items-center"
      >
        <div class="text-left">
          <span
            class="text-[9px] font-black text-[#00a046] uppercase bg-emerald-500/10 px-2.5 py-0.5 rounded tracking-wider"
          >Швидке замовлення</span>
          <h3 class="font-extrabold text-base text-zinc-900 dark:text-white mt-1">
            Замовлення в один клік
          </h3>
        </div>
        <button
          class="text-zinc-400 hover:text-zinc-650 dark:hover:text-zinc-250 flex items-center justify-center"
          @click="closeModal"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6">
        <!-- Success State -->
        <div v-if="isSuccess" class="text-center py-6 space-y-4">
          <div
            class="w-16 h-16 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-950/30 flex items-center justify-center text-[#00a046]"
          >
            <span class="material-symbols-outlined text-[36px]">check_circle</span>
          </div>
          <div class="space-y-1">
            <h4 class="font-extrabold text-lg text-zinc-900 dark:text-white">
              Замовлення успішно створено!
            </h4>
            <p v-if="orderNumber" class="text-xs text-[#00a046] font-bold">
              Номер замовлення: {{ orderNumber }}
            </p>
          </div>
          <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed max-w-sm mx-auto">
            Дякуємо за покупку! Наш менеджер зв'яжеться з вами найближчим часом за телефоном <span class="font-extrabold">{{ phone }}</span> для підтвердження деталей відправки.
          </p>
          <button
            class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-2.5 rounded-md font-extrabold text-xs transition-all uppercase tracking-wider shadow-sm font-bold"
            @click="closeModal"
          >
            Зрозуміло
          </button>
        </div>

        <!-- Form State -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-5 text-left">
          <!-- Product Preview -->
          <div class="flex gap-4 p-3 bg-zinc-50 dark:bg-zinc-850 rounded-lg border border-zinc-150 dark:border-zinc-800">
            <img
              :src="product.image"
              :alt="product.name"
              class="w-16 h-16 object-contain rounded bg-white p-1 border border-zinc-100"
            >
            <div class="flex-1 min-w-0 flex flex-col justify-center">
              <h5 class="text-xs font-extrabold text-zinc-900 dark:text-white truncate">
                {{ product.name }}
              </h5>
              <p class="text-[10px] text-zinc-400 dark:text-zinc-500 font-bold mt-0.5 truncate">
                {{ product.subtitle || product.category }}
              </p>
              <p class="text-sm font-black text-[#00a046] mt-1 font-bold">
                {{ formatPrice(product.price) }}
              </p>
            </div>
          </div>

          <!-- Error Alert -->
          <div
            v-if="errorMsg"
            class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-500 rounded text-xs font-bold"
          >
            {{ errorMsg }}
          </div>

          <!-- Fields -->
          <div class="space-y-4">
            <div class="space-y-1.5">
              <label class="block text-[11px] font-black text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">
                Ваше ім'я <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="name"
                type="text"
                required
                placeholder="Введіть ваше ім'я"
                class="w-full h-10 px-3.5 border border-zinc-200 dark:border-zinc-700 rounded-md bg-zinc-50 dark:bg-zinc-800 text-xs font-bold focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none text-zinc-800 dark:text-zinc-200"
              >
            </div>

            <div class="space-y-1.5">
              <label class="block text-[11px] font-black text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">
                Номер телефону <span class="text-rose-500">*</span>
              </label>
              <input
                v-model="phone"
                type="tel"
                required
                placeholder="+380"
                class="w-full h-10 px-3.5 border border-zinc-200 dark:border-zinc-700 rounded-md bg-zinc-50 dark:bg-zinc-800 text-xs font-bold focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none text-zinc-800 dark:text-zinc-200"
              >
            </div>
          </div>

          <!-- Submit Button -->
          <button
            :disabled="isSubmitting"
            type="submit"
            class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-3 rounded-md font-extrabold text-xs transition-all uppercase tracking-wider shadow-sm font-bold flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="isSubmitting" class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent" />
            <span>{{ isSubmitting ? "Надсилання..." : "Оформити замовлення" }}</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from "vue";
import { orderApi } from "@/shared/services/api/orderApi";
import { useAuthStore } from "@/entities/user/model/authStore";

const props = defineProps<{
  isOpen: boolean;
  product: any;
}>();

const emit = defineEmits(["close", "success"]);

const authStore = useAuthStore();

const name = ref("");
const phone = ref("");
const isSubmitting = ref(false);
const isSuccess = ref(false);
const orderNumber = ref("");
const errorMsg = ref("");

// Pre-fill user data if logged in
watch(
  () => props.isOpen,
  (open) => {
    if (open) {
      name.value = authStore.user?.name || "";
      phone.value = authStore.user?.phone || "";
      isSubmitting.value = false;
      isSuccess.value = false;
      orderNumber.value = "";
      errorMsg.value = "";
    }
  }
);

const closeModal = () => {
  emit("close");
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};

const handleSubmit = async () => {
  if (!name.value.trim() || !phone.value.trim()) {
    errorMsg.value = "Будь ласка, заповніть обов'язкові поля.";
    return;
  }

  isSubmitting.value = true;
  errorMsg.value = "";

  try {
    const response = await orderApi.placeQuickOrder(
      name.value.trim(),
      phone.value.trim(),
      props.product.id
    );

    if (response.data && response.data.status === "success") {
      isSuccess.value = true;
      orderNumber.value = response.data.data?.orderNumber || response.data.data?.order_number || "";
      emit("success", response.data.data);
    }
  } catch (error: any) {
    console.error("Quick order failed:", error);
    errorMsg.value = error.response?.data?.message || "Не вдалося оформити замовлення. Спробуйте пізніше.";
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
.animate-fade {
  animation: fadeIn 0.22s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(3px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
