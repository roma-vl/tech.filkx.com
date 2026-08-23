<template>
  <div class="space-y-stack-md">
    <article
      v-for="item in cart"
      :key="item.id"
      class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-800 flex flex-col md:flex-row gap-6 transition-all duration-300"
      :class="{
        'opacity-65 grayscale bg-zinc-50 dark:bg-zinc-900/40 border-dashed':
          item.stock !== undefined && item.stock <= 0,
      }"
    >
      <div
        class="w-full md:w-32 h-32 bg-zinc-100 dark:bg-zinc-800 rounded-lg overflow-hidden flex-shrink-0 p-3"
      >
        <img
          class="w-full h-full object-contain"
          :src="item.image"
          :alt="item.name"
        />
      </div>
      <div class="flex-grow flex flex-col justify-between gap-5">
        <div
          class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3"
        >
          <div>
            <h3 class="text-zinc-900 dark:text-white font-bold text-lg">
              {{ item.name }}
            </h3>
            <p class="text-sm text-zinc-400 dark:text-zinc-500 mt-1">
              {{ item.sku }}
            </p>
          </div>
          <span class="text-xl font-bold text-[#00a046]">{{
            formatPrice(item.price * item.quantity)
          }}</span>
        </div>
        <div
          class="flex flex-col lg:flex-row lg:items-center justify-between gap-4"
        >
          <div class="flex flex-wrap items-center gap-4">
            <div
              class="flex items-center border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden h-9 bg-white dark:bg-zinc-800"
              :class="{
                'opacity-50 pointer-events-none':
                  item.stock !== undefined && item.stock <= 0,
              }"
            >
              <button
                class="px-3 py-1 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors text-zinc-700 dark:text-zinc-300"
                type="button"
                @click="decreaseQuantity(item)"
              >
                <span class="material-symbols-outlined text-body-md"
                  >remove</span
                >
              </button>
              <span
                class="px-4 text-zinc-900 dark:text-white font-bold text-sm"
                >{{ item.quantity }}</span
              >
              <button
                class="px-3 py-1 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors text-zinc-700 dark:text-zinc-300"
                type="button"
                @click="updateCartQuantity(item.id, item.quantity + 1)"
              >
                <span class="material-symbols-outlined text-body-md">add</span>
              </button>
            </div>
          </div>
          <div
            v-if="item.stock !== undefined && item.stock <= 0"
            class="text-red-500 flex items-center gap-1 text-sm font-semibold"
          >
            <span class="material-symbols-outlined text-red-500 text-[18px]"
              >error</span
            >
            {{ t("cart.items.outOfStock") }}
          </div>
        </div>
      </div>
    </article>
  </div>

  <div
    class="mt-stack-lg pt-stack-lg border-t border-zinc-200 dark:border-zinc-800 mt-10"
  >
    <h2 class="mb-6 text-zinc-900 dark:text-white font-bold text-xl">
      {{ t("cart.items.savedForLater", { count: wishlist.length }) }}
    </h2>
    <div
      v-if="wishlist.length === 0"
      class="text-zinc-500 dark:text-zinc-400 text-sm p-6 text-center bg-zinc-50 dark:bg-zinc-900/10 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800"
    >
      {{ t("cart.items.noSavedItems") }}
    </div>
    <div v-else class="space-y-4">
      <div
        v-for="item in wishlist"
        :key="item.id"
        class="bg-zinc-50 dark:bg-zinc-900/60 p-4 rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 flex items-center gap-4 opacity-90 hover:opacity-100 transition-opacity"
      >
        <div
          class="w-16 h-16 bg-white dark:bg-zinc-800 rounded-lg flex-shrink-0 overflow-hidden p-1 border border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-center"
        >
          <img
            class="w-full h-full object-contain"
            :src="item.image"
            :alt="item.name"
          />
        </div>
        <div class="flex-grow">
          <h4 class="text-zinc-800 dark:text-zinc-200 font-bold line-clamp-1">
            {{ item.name }}
          </h4>
          <p class="text-zinc-500 dark:text-zinc-400">
            {{ formatPrice(item.price) }}
          </p>
        </div>
        <button
          class="px-4 py-2 bg-[#00a046] text-white rounded-full font-semibold hover:bg-[#00b050] transition-colors text-sm"
          type="button"
          @click="$emit('moveToCart', item)"
        >
          {{ t("cart.items.moveToCart") }}
        </button>
      </div>
    </div>
  </div>

  <UiConfirmModal
    :open="pendingRemoveId !== null"
    :title="t('cart.items.confirmRemoveTitle')"
    :message="t('cart.items.confirmRemoveMessage')"
    :confirm-label="t('cart.items.confirmRemoveYes')"
    :cancel-label="t('cart.items.confirmRemoveNo')"
    @confirm="confirmRemove"
    @cancel="pendingRemoveId = null"
  />
</template>

<script setup lang="ts">
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { CartItem } from "@/entities/order/types";
import { UiConfirmModal } from "@/shared/ui";

const { t } = useI18n();

defineProps<{
  cart: CartItem[];
  wishlist: any[];
  formatPrice: (p: number) => string;
}>();

const emit = defineEmits<{
  (e: "updateQuantity", id: number | string, qty: number): void;
  (e: "remove", id: number | string): void;
  (e: "moveToCart", item: any): void;
}>();

const pendingRemoveId = ref<number | string | null>(null);

function updateCartQuantity(id: number | string, val: number) {
  if (val < 1) return;
  emit("updateQuantity", id, val);
}

function decreaseQuantity(item: CartItem) {
  if (item.quantity <= 1) {
    pendingRemoveId.value = item.id;
    return;
  }
  updateCartQuantity(item.id, item.quantity - 1);
}

function confirmRemove() {
  if (pendingRemoveId.value !== null) {
    emit("remove", pendingRemoveId.value);
  }
  pendingRemoveId.value = null;
}
</script>
