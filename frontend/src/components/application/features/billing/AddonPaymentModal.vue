<template>
  <AppModal
    :model-value="true"
    :title="t('subscription.buy_addons', 'Buy Addons')"
    max-width="3xl"
    :show-close="true"
    @update:model-value="$emit('close')"
  >
    <div class="space-y-6">
      <div
        class="bg-white/50 dark:bg-gray-800/30 backdrop-blur-md rounded-[1.5rem] p-6 shadow-sm border border-white/60 dark:border-white/10"
      >
        <label
          class="flex items-center justify-between w-full text-sm font-bold text-gray-700 dark:text-gray-300 mb-6"
        >
          <span>{{ t("subscription.buy_for_months", "Buy for") }}</span>
          <div
            class="flex items-baseline gap-1 bg-white/60 dark:bg-gray-700/50 px-3 py-1 rounded-lg"
          >
            <span
              class="text-xl font-black text-primary-600 dark:text-primary-400"
            >{{ duration }}</span>
            <span
              class="text-xs font-bold text-gray-500 uppercase tracking-widest"
            >{{ t("pricing.billing_period_short", "mo") }}</span>
          </div>
        </label>

        <div class="relative flex flex-col items-center px-2">
          <div class="flex items-center gap-4 w-full">
            <span class="text-xs font-bold text-gray-400 w-4 text-right">1</span>
            <div class="relative flex-1 flex items-center h-8">
              <input
                v-model.number="duration"
                type="range"
                min="1"
                max="12"
                step="1"
                class="absolute w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-primary-500 hover:accent-primary-600 transition-all z-10"
              >
            </div>
            <span class="text-xs font-bold text-gray-400 w-4 text-left">12</span>
          </div>
        </div>

        <transition
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 -translate-y-4 scale-95"
          enter-to-class="opacity-100 translate-y-0 scale-100"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0 scale-100"
          leave-to-class="opacity-0 -translate-y-4 scale-95"
        >
          <div
            v-if="showDurationWarning"
            class="mt-4 p-4 bg-yellow-50/50 dark:bg-yellow-900/10 backdrop-blur-md border border-yellow-200/50 dark:border-yellow-800/50 rounded-xl flex items-start gap-3 shadow-sm"
          >
            <svg
              class="w-6 h-6 text-yellow-600 dark:text-yellow-500 shrink-0"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
              />
            </svg>
            <div>
              <p class="text-sm font-bold text-yellow-800 dark:text-yellow-400">
                {{
                  t(
                    "subscription.addon_exceeds_duration",
                    "Addon duration exceeds remaining subscription time.",
                  )
                }}
              </p>
              <div class="mt-2 flex items-center">
                <AppCheckbox
                  id="extend-sub"
                  v-model="extendSubscription"
                  size="sm"
                />
                <label
                  for="extend-sub"
                  class="ml-2 text-xs font-bold text-yellow-700 dark:text-yellow-500 cursor-pointer"
                >
                  {{
                    t(
                      "subscription.extend_for_renewal",
                      "Extend subscription for current period",
                    )
                  }}
                </label>
              </div>
            </div>
          </div>
        </transition>
      </div>

      <div v-if="addons.length > 0">
        <label
          class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3"
        >
          {{ t("pricing.addons", "Available Add-ons") }}
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div
            v-for="addon in addons"
            :key="addon.id"
            class="flex items-center justify-between p-4 bg-white/40 dark:bg-gray-800/20 backdrop-blur-md rounded-xl cursor-pointer transition-all border"
            :class="
              selectedAddonIds.includes(addon.id)
                ? 'border-primary-500 shadow-sm bg-primary-50/10 dark:bg-primary-900/10'
                : 'border-white/60 dark:border-white/10 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-white/60 dark:hover:bg-gray-800/40'
            "
            @click="toggleAddon(addon.id)"
          >
            <div class="flex items-start gap-3">
              <AppCheckbox
                :model-value="selectedAddonIds.includes(addon.id)"
                readonly
                class="mt-0.5"
              />
              <div class="flex flex-col h-full justify-between">
                <div>
                  <p
                    class="font-bold text-sm text-gray-900 dark:text-white leading-tight"
                  >
                    {{ addon.name }}
                  </p>
                  <p
                    class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1 line-clamp-2"
                  >
                    {{ addon.description }}
                  </p>
                </div>
                <div class="mt-3 flex items-end justify-between w-full gap-2">
                  <p class="text-[10px] uppercase font-bold text-gray-400">
                    ${{ addon.priceMonthly }}/{{
                      t("pricing.billing_period_short", "mo")
                    }}
                  </p>
                  <p
                    class="font-black text-sm text-primary-600 dark:text-primary-400"
                  >
                    ${{ (addon.priceMonthly * duration).toFixed(2) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div>
        <label
          class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3"
        >
          {{ t("pricing.payment_method") }}
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <label
            v-for="method in paymentMethods"
            :key="method.id"
            class="flex flex-col p-4 bg-white/40 dark:bg-gray-800/20 backdrop-blur-md rounded-xl transition-all border relative"
            :class="[
              selectedMethod === method.id
                ? 'border-primary-500 shadow-sm cursor-pointer bg-primary-50/10 dark:bg-primary-900/10'
                : method.disabled
                  ? 'border-white/20 dark:border-white/5 opacity-50 cursor-not-allowed'
                  : 'border-white/60 dark:border-white/10 hover:border-gray-300 dark:hover:border-gray-600 cursor-pointer hover:bg-white/60 dark:hover:bg-gray-800/40',
            ]"
          >
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <AppRadio
                  v-model="selectedMethod"
                  :value="method.id"
                  :disabled="method.disabled"
                  name="payment_method"
                  class="shrink-0"
                />
                <span class="font-bold text-gray-900 dark:text-white text-sm">{{
                  t(method.labelKey)
                }}</span>
              </div>
              <span
                v-if="method.disabled"
                class="absolute top-3 right-3 text-[9px] uppercase font-black px-1.5 py-0.5 rounded bg-gray-200/50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400"
              >
                {{
                  method.id === "stripe"
                    ? t("pricing.coming_soon")
                    : t("pricing.in_development")
                }}
              </span>
            </div>
            <p
              class="text-xs font-medium text-gray-500 dark:text-gray-400 leading-relaxed ml-7"
            >
              {{ t(method.descKey) }}
            </p>
          </label>
        </div>
      </div>

      <div class="border-t border-gray-200 dark:border-gray-700/50 pt-5 mt-2">
        <div
          class="flex items-center justify-between font-black text-xl text-gray-900 dark:text-white tracking-tight"
        >
          <span>{{ t("pricing.total") }}</span>
          <div class="text-right">
            <span>${{ totalPrice.toFixed(2) }}</span>
            <p
              v-if="extendSubscription"
              class="text-xs font-bold text-gray-500 uppercase tracking-widest mt-1"
            >
              (Includes {{ t("subscription.renewal", "Renewal") }}: ${{
                totalRenewalPrice.toFixed(2)
              }})
            </p>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3 mt-4">
        <AppCheckbox
          id="terms"
          v-model="acceptedTerms"
          class="shrink-0"
        />
        <label
          for="terms"
          class="block text-sm font-medium text-gray-700 dark:text-gray-300 leading-snug cursor-pointer pt-0.5"
        >
          <i18n-t
            keypath="pricing.terms_agreement"
            tag="span"
          >
            <template #link>
              <a
                :href="`${landingUrl}/${$i18n.locale}/terms`"
                target="_blank"
                class="text-primary-600 dark:text-primary-400 hover:text-primary-700 font-bold hover:underline"
                @click.stop
              >
                {{ t("pricing.terms_link") }}
              </a>
            </template>
          </i18n-t>
        </label>
      </div>
    </div>

    <template #footer>
      <div class="flex space-x-3 w-full">
        <AppButton
          variant="secondary"
          class="flex-1 font-bold shadow-sm"
          @click="$emit('close')"
        >
          {{ t("pricing.cancel") }}
        </AppButton>
        <AppButton
          :disabled="
            !acceptedTerms || processing || selectedAddonIds.length === 0
          "
          :loading="processing"
          variant="primary"
          class="flex-1 font-bold shadow-md shadow-primary-500/20"
          @click="confirmPayment"
        >
          {{ t("pricing.confirm_payment") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppCheckbox from "@/components/application/ui/Form/AppCheckbox.vue";
import AppRadio from "@/components/application/ui/Form/AppRadio.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const props = defineProps({
  subscription: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close"]);
const { t } = useI18n();
const landingUrl = import.meta.env.VITE_LANDING_URL || "https://live.filkx.com";
const router = useRouter();
const toast = useToast();

const duration = ref(1);
const extendSubscription = ref(false);
const selectedMethod = ref("manual");
const acceptedTerms = ref(false);
const processing = ref(false);
const addons = ref([]);
const selectedAddonIds = ref([]);

const paymentMethods = [
  {
    id: "manual",
    labelKey: "pricing.payment_methods.manual",
    descKey: "pricing.payment_methods.manual_desc",
    disabled: false,
  },
  {
    id: "liqpay",
    labelKey: "pricing.payment_methods.liqpay",
    descKey: "pricing.payment_methods.liqpay_desc",
    disabled: true,
  },
  {
    id: "stripe",
    labelKey: "pricing.payment_methods.stripe",
    descKey: "pricing.payment_methods.stripe_desc",
    disabled: true,
  },
];

const toggleAddon = (addonId) => {
  if (selectedAddonIds.value.includes(addonId)) {
    selectedAddonIds.value = selectedAddonIds.value.filter(
      (id) => id !== addonId,
    );
  } else {
    selectedAddonIds.value.push(addonId);
  }
};

const remainingSubscriptionMonths = computed(() => {
  if (!props.subscription?.currentPeriodEnd) return 0;
  const end = new Date(props.subscription.currentPeriodEnd);
  const now = new Date();
  return Math.max(
    0,
    (end.getFullYear() - now.getFullYear()) * 12 +
      (end.getMonth() - now.getMonth()),
  );
});

const requiredExtensionMonths = computed(() => {
  return Math.max(0, duration.value - remainingSubscriptionMonths.value);
});

const showDurationWarning = computed(() => {
  return requiredExtensionMonths.value > 0;
});

const renewalPrice = computed(() => {
  if (!props.subscription?.plan) return 0;

  const plan = props.subscription.plan;
  const cycle =
    props.subscription.billingCycle ||
    props.subscription.billing_cycle ||
    "monthly";

  const basePrice =
    Number(plan.priceMonthly) ||
    Number(plan.price_monthly) ||
    Number(plan.price) ||
    0;

  if (cycle === "annual")
    return (
      Number(plan.priceAnnual) || Number(plan.price_annual) || basePrice * 12
    );
  if (cycle === "semi_annual")
    return (
      Number(plan.priceSemiAnnual) ||
      Number(plan.price_semi_annual) ||
      basePrice * 6
    );
  if (cycle === "quarterly")
    return (
      Number(plan.priceQuarterly) ||
      Number(plan.price_quarterly) ||
      basePrice * 3
    );

  return basePrice;
});

const totalRenewalPrice = computed(() => {
  return renewalPrice.value * requiredExtensionMonths.value;
});

const totalPrice = computed(() => {
  const addonsPrice = addons.value
    .filter((a) => selectedAddonIds.value.includes(a.id))
    .reduce((sum, a) => sum + Number(a.priceMonthly) * duration.value, 0);

  let total = addonsPrice;
  if (extendSubscription.value) {
    total += totalRenewalPrice.value;
  }
  return total;
});

const fetchAddons = async () => {
  try {
    const { data } = await api.get("/billing/addons");
    addons.value = data.data;
  } catch (e) {
    console.error(e);
  }
};

const confirmPayment = async () => {
  if (!acceptedTerms.value) {
    toast.error(t("errors.accept_terms"));
    return;
  }

  processing.value = true;
  try {
    const payload = {
      paymentMethod: selectedMethod.value,
      addons: selectedAddonIds.value,
      addonDuration: duration.value,
      extendSubscription: extendSubscription.value,
    };

    const { data } = await api.post("/billing/payments/initiate", payload);
    const { nextStep, payment } = data.data;

    if (nextStep === "upload_payment_proof") {
      toast.success(t("settings.payment_created_proof"));
      emit("close");
      router.push(`/account/payment/${payment.id}/proof`);
    } else if (nextStep === "gateway") {
      emit("close");
      router.push(`/account/payment/${payment.id}/gateway`);
    } else {
      toast.success(t("settings.payment_success"));
      emit("close");
      router.push("/account/subscription");
    }
  } catch (e) {
    console.error(e);
    toast.error(
      e.response?.data?.message ?? t("errors.payment_processing_error"),
    );
  } finally {
    processing.value = false;
  }
};

onMounted(() => {
  fetchAddons();
});
</script>
