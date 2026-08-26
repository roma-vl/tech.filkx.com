<template>
  <div class="flex flex-col gap-5 text-left">
    <!-- Top row: category + actions -->
    <div class="flex items-center justify-between gap-3">
      <span
        v-if="product.category"
        class="text-xs font-bold text-[#00a046] bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/30 px-2.5 py-1 rounded uppercase tracking-wider"
      >
        {{ product.category }}
      </span>
      <div v-else />
      <div class="flex gap-1.5">
        <button
          class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-400 hover:text-rose-500 hover:border-rose-300 dark:hover:border-rose-700 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition-all"
          :title="t('product.purchase.wishlist')"
          @click="cartStore.toggleWishlist(product)"
        >
          <span
            class="material-symbols-outlined text-[19px]"
            :class="{ 'text-rose-500': cartStore.isInWishlist(product.id) }"
            :style="
              cartStore.isInWishlist(product.id)
                ? 'font-variation-settings: \'FILL\' 1;'
                : ''
            "
            >favorite</span
          >
        </button>
        <button
          class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 flex items-center justify-center text-zinc-400 hover:text-[#00a046] hover:border-[#00a046]/30 transition-all"
          :class="{
            'text-[#00a046] border-[#00a046]/40 bg-emerald-50 dark:bg-emerald-900/20':
              cartStore.isInCompare(product.id),
          }"
          :title="t('product.purchase.compare')"
          @click="cartStore.toggleCompare(product)"
        >
          <span class="material-symbols-outlined text-[19px]"
            >compare_arrows</span
          >
        </button>
      </div>
    </div>

    <!-- Product name -->
    <div class="space-y-2">
      <h1
        class="text-2xl md:text-[28px] font-extrabold text-zinc-900 dark:text-white tracking-tight leading-tight"
      >
        {{ product.name }}
      </h1>
      <p
        v-if="product.subtitle"
        class="text-sm text-zinc-500 dark:text-zinc-400"
      >
        {{ product.subtitle }}
      </p>
    </div>

    <!-- Rating + SKU -->
    <div
      class="flex flex-wrap items-center gap-3 text-sm pb-1 border-b border-zinc-100 dark:border-zinc-800"
    >
      <div class="flex items-center gap-1.5">
        <div class="flex">
          <span
            v-for="star in 5"
            :key="star"
            class="material-symbols-outlined text-[15px]"
            :class="
              star <= Math.round(product.rating)
                ? 'text-amber-400'
                : 'text-zinc-300 dark:text-zinc-600'
            "
            :style="
              star <= Math.round(product.rating)
                ? 'font-variation-settings: &quot;FILL&quot; 1'
                : ''
            "
            >star</span
          >
        </div>
        <span
          class="font-semibold text-zinc-700 dark:text-zinc-300 text-xs hover:text-[#00a046] transition-colors cursor-pointer"
        >
          {{ t("product.purchase.reviewsCount", { count: product.reviews }) }}
        </span>
      </div>
      <div class="w-px h-4 bg-zinc-200 dark:bg-zinc-700" />
      <span class="text-xs font-mono text-zinc-400 dark:text-zinc-500">
        {{
          t("product.purchase.skuLabel", {
            code: product.productId || product.id,
          })
        }}
      </span>
    </div>

    <!-- Price box -->
    <div
      class="p-5 bg-zinc-50 dark:bg-zinc-900/80 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-5"
    >
      <!-- Price -->
      <div class="space-y-1">
        <div class="flex flex-wrap items-end gap-3">
          <span
            class="text-3xl md:text-4xl font-black tracking-tight text-[#00a046] leading-none"
          >
            {{ formatPrice(product.price) }}
          </span>
          <div v-if="product.oldPrice" class="flex items-center gap-2 mb-0.5">
            <span class="text-base text-zinc-400 line-through font-semibold">{{
              formatPrice(product.oldPrice)
            }}</span>
            <span
              class="bg-rose-100 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wide"
            >
              {{ t("product.purchase.saleBadge") }}
            </span>
          </div>
        </div>
        <p class="text-xs text-zinc-400 dark:text-zinc-500">
          {{ t("product.purchase.installmentNote") }}
        </p>
      </div>

      <!-- Color -->
      <div v-if="availableColors.length > 0" class="space-y-2.5">
        <span
          class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider"
        >
          {{ t("product.purchase.colorLabel") }}
          <span
            class="text-zinc-800 dark:text-zinc-200 normal-case tracking-normal"
            >{{ selectedColor }}</span
          >
        </span>
        <div class="flex gap-2.5">
          <button
            v-for="color in availableColors"
            :key="color"
            :title="color"
            class="w-8 h-8 rounded-full transition-all border-2"
            :class="[
              selectedColor === color
                ? 'ring-2 ring-offset-2 ring-[#00a046] dark:ring-offset-zinc-900 scale-110'
                : 'ring-0 hover:scale-105',
              color.toLowerCase().includes('black') ||
              color.toLowerCase().includes('чорн')
                ? 'bg-[#1c1c1e] border-zinc-600'
                : color.toLowerCase().includes('silver') ||
                    color.toLowerCase().includes('срібл')
                  ? 'bg-[#c8c8c8] border-zinc-300'
                  : color.toLowerCase().includes('emerald') ||
                      color.toLowerCase().includes('зелен')
                    ? 'bg-[#004d40] border-emerald-600'
                    : color.toLowerCase().includes('blue') ||
                        color.toLowerCase().includes('синь')
                      ? 'bg-[#1a56db] border-blue-600'
                      : color.toLowerCase().includes('white') ||
                          color.toLowerCase().includes('біл')
                        ? 'bg-white border-zinc-300'
                        : 'bg-zinc-400 border-zinc-300',
            ]"
            @click="$emit('select-variant', 'color', color)"
          />
        </div>
      </div>

      <!-- Storage/Config -->
      <div v-if="availableStorage.length > 0" class="space-y-2.5">
        <span
          class="text-xs font-bold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider"
          >{{ t("product.purchase.configLabel") }}</span
        >
        <div class="flex flex-wrap gap-2">
          <button
            v-for="storage in availableStorage"
            :key="storage"
            :class="
              selectedStorage === storage
                ? 'border-2 border-[#00a046] text-[#00a046] bg-emerald-50 dark:bg-emerald-900/20 font-bold shadow-sm'
                : 'border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:border-[#00a046]/50 hover:text-[#00a046] font-semibold'
            "
            class="py-2 px-3.5 rounded-lg transition-all text-sm min-w-[72px] text-center"
            @click="$emit('select-variant', 'memory', storage)"
          >
            {{ storage }}
          </button>
        </div>
      </div>

      <!-- Action buttons -->
      <div class="space-y-2.5 pt-1">
        <template v-if="product.inStock">
          <UiButton
            size="lg"
            class="w-full"
            @click="cartStore.addToCart(product)"
          >
            <template #prefix>
              <span class="material-symbols-outlined text-[19px]"
                >shopping_cart</span
              >
            </template>
            {{ t("product.purchase.addToCart") }}
          </UiButton>
          <UiButton
            variant="secondary"
            size="lg"
            class="w-full"
            @click="$emit('quick-order')"
          >
            <template #prefix>
              <span class="material-symbols-outlined text-[17px]">bolt</span>
            </template>
            {{ t("product.purchase.quickOrder") }}
          </UiButton>
        </template>
        <UiButton
          v-else
          size="lg"
          class="w-full"
          :disabled="notifyRequested"
          @click="handleNotifyRestock"
        >
          <template #prefix>
            <span class="material-symbols-outlined text-[19px]">{{
              notifyRequested ? "check" : "notifications"
            }}</span>
          </template>
          {{
            notifyRequested
              ? t("product.purchase.notifyMeSubscribed")
              : t("product.purchase.notifyMe")
          }}
        </UiButton>
      </div>
    </div>

    <!-- Delivery info -->
    <div
      class="flex items-stretch gap-0 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden divide-x divide-zinc-200 dark:divide-zinc-800"
    >
      <div
        class="flex-1 flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900"
      >
        <span
          class="material-symbols-outlined text-[22px] shrink-0"
          :class="product.inStock ? 'text-[#00a046]' : 'text-zinc-400'"
          >inventory_2</span
        >
        <div>
          <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
            {{
              product.inStock
                ? t("product.purchase.delivery.inStockTitle")
                : t("product.purchase.delivery.outOfStockTitle")
            }}
          </p>
          <p class="text-[11px] text-zinc-400 dark:text-zinc-500">
            {{
              product.inStock
                ? t("product.purchase.delivery.inStockSubtitle")
                : t("product.purchase.delivery.outOfStockSubtitle")
            }}
          </p>
        </div>
      </div>
      <div
        class="flex-1 flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900 min-w-0"
      >
        <span
          class="material-symbols-outlined text-[#00a046] text-[22px] shrink-0"
          >local_shipping</span
        >
        <div class="min-w-0">
          <p
            class="text-xs font-bold text-zinc-800 dark:text-zinc-200 truncate"
          >
            {{
              deliveryEstimate
                ? t("product.purchase.delivery.estimateTitle", {
                    city: deliveryEstimate.cityName,
                  })
                : t("product.purchase.delivery.shippingTitle")
            }}
          </p>
          <p class="text-[11px] text-zinc-400 dark:text-zinc-500 truncate">
            {{
              deliveryEstimate
                ? t("product.purchase.delivery.estimateSubtitle", {
                    date: deliveryEstimate.formattedDate,
                  })
                : t("product.purchase.delivery.shippingSubtitle")
            }}
          </p>
          <button
            type="button"
            class="text-[10px] font-semibold text-[#00a046] hover:underline mt-0.5"
            @click="isCityModalOpen = true"
          >
            {{
              deliveryStore.city
                ? t("product.purchase.delivery.changeCityLink")
                : t("product.purchase.delivery.setCityLink")
            }}
          </button>
        </div>
      </div>
      <div
        class="flex-1 flex items-center gap-3 px-4 py-3 bg-white dark:bg-zinc-900"
      >
        <span
          class="material-symbols-outlined text-[#00a046] text-[22px] shrink-0"
          >assignment_return</span
        >
        <div>
          <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200">
            {{ t("product.purchase.delivery.returnsTitle") }}
          </p>
          <p class="text-[11px] text-zinc-400 dark:text-zinc-500">
            {{ t("product.purchase.delivery.returnsSubtitle") }}
          </p>
        </div>
      </div>
    </div>

    <DeliveryCityPickerModal
      :is-open="isCityModalOpen"
      @close="isCityModalOpen = false"
      @select="handleCitySelected"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useRoute, useRouter } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { useAuthStore } from "@/entities/user/model/authStore";
import {
  useDeliveryStore,
  type DeliveryCity,
} from "@/entities/delivery/model/deliveryStore";
import DeliveryCityPickerModal from "@/features/delivery/ui/DeliveryCityPickerModal.vue";
import { deliveryApi } from "@/shared/services/api/deliveryApi";
import { UiButton } from "@/shared/ui";

const props = defineProps<{
  product: any;
  availableColors: string[];
  selectedColor: string;
  availableStorage: string[];
  selectedStorage: string;
  formatPrice: (p: number) => string;
}>();

defineEmits<{
  (e: "select-variant", attributeCode: string, value: string): void;
  (e: "quick-order"): void;
}>();

const cartStore = useCartStore();
const authStore = useAuthStore();
const deliveryStore = useDeliveryStore();
const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n();

const notifyRequested = ref(false);
const isCityModalOpen = ref(false);

// null unless the shopper has a city set AND the backend confirmed a real estimate for it -
// any other outcome (no city, Nova Poshta not configured, request failure) keeps the tile on
// the original static delivery text further down in the template.
const estimatedDateIso = ref<string | null>(null);

async function refreshDeliveryEstimate() {
  const city = deliveryStore.city;
  if (!city) {
    estimatedDateIso.value = null;
    return;
  }

  try {
    const response = await deliveryApi.getEstimate(city.ref);
    const data = response.data?.data;
    estimatedDateIso.value = data?.available && data?.date ? data.date : null;
  } catch {
    estimatedDateIso.value = null;
  }
}

watch(() => deliveryStore.city, refreshDeliveryEstimate, { immediate: true });

const deliveryEstimate = computed(() => {
  if (!deliveryStore.city || !estimatedDateIso.value) {
    return null;
  }

  const formattedDate = new Intl.DateTimeFormat(
    locale.value === "uk" ? "uk-UA" : "en-US",
    { day: "numeric", month: "long" },
  ).format(new Date(estimatedDateIso.value));

  return { cityName: deliveryStore.city.name, formattedDate };
});

function handleCitySelected(city: DeliveryCity) {
  deliveryStore.setCity(city);
}

const handleNotifyRestock = async () => {
  if (!authStore.isAuthenticated) {
    router.push({ path: "/login", query: { redirect: route.fullPath } });
    return;
  }

  const subscribed = await cartStore.subscribeRestock(props.product.productId);
  if (subscribed) {
    notifyRequested.value = true;
    cartStore.addToast(
      t("product.purchase.notifyMeSuccessToast", { name: props.product.name }),
    );
  } else {
    cartStore.addToast(t("product.purchase.notifyMeErrorToast"), "error");
  }
};
</script>
