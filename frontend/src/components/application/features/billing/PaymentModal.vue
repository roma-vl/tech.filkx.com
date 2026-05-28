<template>
  <AppModal
    :model-value="true"
    :title="$t('pricing.order_confirmation')"
    max-width="4xl"
    :show-close="true"
    @update:model-value="$emit('close')"
  >
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <div class="lg:col-span-7 space-y-8">
        <div v-if="!isTrial && addons.length > 0">
          <label
            class="block text-sm font-semibold text-gray-900 dark:text-white mb-4 uppercase tracking-wider"
          >
            {{ $t("pricing.addons", "Recommended Add-ons") }}
          </label>
          <div class="space-y-3">
            <div
              v-for="addon in addons"
              :key="addon.id"
              class="group relative flex items-center justify-between p-4 border rounded-xl cursor-pointer transition-all duration-200"
              :class="
                selectedAddonIds.includes(addon.id)
                  ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-900/10 ring-1 ring-primary-500'
                  : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'
              "
              @click="toggleAddon(addon.id)"
            >
              <div class="flex items-center gap-4">
                <div
                  class="flex-shrink-0 w-5 h-5 border-2 rounded flex items-center justify-center transition-colors"
                  :class="
                    selectedAddonIds.includes(addon.id)
                      ? 'bg-primary-500 border-primary-500'
                      : 'border-gray-300 dark:border-gray-600'
                  "
                >
                  <svg
                    v-if="selectedAddonIds.includes(addon.id)"
                    class="w-3.5 h-3.5 text-white"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
                <div>
                  <p class="font-semibold text-gray-900 dark:text-white">
                    {{ addon.name }}
                  </p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ addon.description }}
                  </p>
                </div>
              </div>
              <div class="text-right">
                <span class="font-bold text-gray-900 dark:text-white">+${{ addon.priceMonthly }}</span>
                <p class="text-xs text-gray-400 uppercase tracking-tight">
                  /{{ t("pricing.billing_period_short", "mo") }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!isTrial">
          <label
            class="block text-sm font-semibold text-gray-900 dark:text-white mb-4 uppercase tracking-wider"
          >
            {{ t("pricing.payment_method") }}
          </label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label
              v-for="method in paymentMethods"
              :key="method.id"
              class="relative flex flex-col p-4 border-2 rounded-xl transition-all duration-200 group"
              :class="[
                selectedMethod === method.id
                  ? 'border-primary-500 bg-primary-50/50 dark:bg-primary-900/10'
                  : method.disabled
                    ? 'border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 cursor-not-allowed opacity-60'
                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 cursor-pointer bg-white dark:bg-gray-800',
              ]"
            >
              <div class="flex items-center justify-between mb-2">
                <div
                  class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                  :class="
                    selectedMethod === method.id
                      ? 'border-primary-500 bg-primary-500'
                      : 'border-gray-300 dark:border-gray-600'
                  "
                >
                  <div
                    v-if="selectedMethod === method.id"
                    class="w-2 h-2 rounded-full bg-white"
                  />
                </div>
                <input
                  v-model="selectedMethod"
                  type="radio"
                  :value="method.id"
                  :disabled="method.disabled"
                  class="hidden"
                >
                <span
                  v-if="method.disabled"
                  class="text-xs uppercase font-bold px-2 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400"
                >
                  {{ t("pricing.in_development") }}
                </span>
              </div>
              <p class="font-semibold text-gray-900 dark:text-white">
                {{ t(method.labelKey) }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                {{ t(method.descKey) }}
              </p>
            </label>
          </div>
        </div>

        <div
          v-if="!isTrial && selectedMethod === 'card'"
          class="p-6 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-700 space-y-4 animate-in fade-in slide-in-from-top-2"
        >
          <AppInput
            v-model="cardData.number"
            type="text"
            :label="$t('pricing.card_number')"
            placeholder="1234 5678 9012 3456"
          />
          <div class="grid grid-cols-2 gap-4">
            <AppInput
              v-model="cardData.expiry"
              type="text"
              :label="$t('pricing.expiry_date')"
              placeholder="MM/YY"
            />
            <AppInput
              v-model="cardData.cvv"
              type="text"
              :label="$t('pricing.cvv')"
              placeholder="123"
            />
          </div>
        </div>
      </div>

      <div class="lg:col-span-5">
        <div
          class="bg-gray-50 dark:bg-gray-800/80 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 sticky top-0"
        >
          <h4
            class="text-sm font-semibold text-gray-900 dark:text-white mb-6 uppercase tracking-wider"
          >
            {{ $t("pricing.order_summary", "Order Summary") }}
          </h4>

          <div class="flex items-start justify-between mb-6">
            <div>
              <p class="font-semibold text-lg text-gray-900 dark:text-white">
                {{ selectedPlan.name }}
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{
                  isTrial
                    ? $t("pricing.trial_duration", "3 Days")
                    : billingCycleLabel
                }}
              </p>
            </div>
            <div class="text-right">
              <p class="font-semibold text-lg text-gray-900 dark:text-white">
                {{
                  isTrial
                    ? t("pricing.free")
                    : `$${Math.round(pricePreview?.originalAmount || subtotal)}`
                }}
              </p>
            </div>
          </div>

          <div
            v-if="activeAddons.length > 0"
            class="mb-6 space-y-2 border-t border-gray-200 dark:border-gray-700 pt-4"
          >
            <div
              v-for="addon in activeAddons"
              :key="addon.id"
              class="flex justify-between text-sm"
            >
              <span class="text-gray-500 dark:text-gray-400">{{
                addon.name
              }}</span>
              <span class="font-medium text-gray-900 dark:text-white">+${{
                addon.priceMonthly * mapPeriodToMonths(props.period)
              }}</span>
            </div>
          </div>

          <div
            v-if="!isTrial"
            class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-4"
          >
            <div class="flex gap-2">
              <div class="relative flex-1">
                <AppInput
                  v-model="promoCode"
                  type="text"
                  size="sm"
                  :placeholder="t('pricing.enter_promo_code')"
                  :disabled="!!appliedPromo"
                />
                <button
                  v-if="appliedPromo"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-red-500 hover:text-red-600"
                  @click="removePromo"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>
              <AppButton
                v-if="!appliedPromo"
                :loading="validatingPromo"
                :disabled="!promoCode"
                variant="secondary"
                size="sm"
                @click="applyPromo"
              >
                {{ t("pricing.apply") }}
              </AppButton>
            </div>

            <p
              v-if="appliedPromo"
              class="mt-2 text-xs text-green-600 dark:text-green-400 font-medium flex items-center gap-1"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-green-500" />
              {{
                appliedPromo.discountType === "percent"
                  ? t("pricing.percent_off", {
                    value: appliedPromo.discountValue,
                  })
                  : t("pricing.amount_off", {
                    value: appliedPromo.discountValue,
                  })
              }}
            </p>
            <p
              v-else-if="autoAppliedCampaign"
              class="mt-2 text-xs text-blue-600 dark:text-blue-400 font-medium flex items-center gap-1"
            >
              <span class="w-1.5 h-1.5 rounded-full bg-blue-500" />
              {{ t("pricing.special_offer", "Special offer applied!") }}
            </p>
          </div>

          <div
            v-if="!isTrial"
            class="space-y-3 mb-8 pt-4 border-t border-gray-200 dark:border-gray-700"
          >
            <div class="flex justify-between text-sm">
              <span class="text-gray-500 dark:text-gray-400">{{
                t("pricing.subtotal")
              }}</span>
              <span class="font-medium text-gray-900 dark:text-white">${{
                Math.round(pricePreview?.originalAmount || subtotal)
              }}</span>
            </div>
            <div
              v-if="(pricePreview?.discountAmount || 0) > 0"
              class="flex justify-between text-sm text-green-600 dark:text-green-400"
            >
              <span class="flex items-center gap-1">
                {{ t("pricing.promo_discount") }}
              </span>
              <span class="font-bold">-${{ Math.round(pricePreview?.discountAmount) }}</span>
            </div>
            <div
              v-if="discountAmount > 0"
              class="flex justify-between text-sm text-green-600 dark:text-green-400"
            >
              <span>{{ t("pricing.coupon_discount") }}</span>
              <span class="font-bold">-${{ discountAmount }}</span>
            </div>
            <div class="flex justify-between items-end pt-2">
              <span
                class="text-base font-semibold text-gray-900 dark:text-white"
              >{{ t("pricing.total") }}</span>
              <div class="text-right">
                <span
                  class="text-3xl font-bold text-primary-600 dark:text-primary-400"
                >
                  ${{ Math.round(totalPrice) }}
                </span>
                <p
                  class="text-xs text-gray-400 uppercase font-bold tracking-widest mt-1"
                >
                  {{ t("pricing.one_time_payment") }}
                </p>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <div class="flex items-start gap-3">
              <AppCheckbox
                id="terms"
                v-model="acceptedTerms"
                class="mt-0.5"
              />
              <label
                for="terms"
                class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed"
              >
                <i18n-t
                  keypath="pricing.terms_agreement"
                  tag="span"
                >
                  <template #link>
                    <a
                      :href="`${landingUrl}/${$i18n.locale}/terms`"
                      target="_blank"
                      class="text-primary-600 hover:text-primary-700 underline font-medium"
                      @click.stop
                    >
                      {{ t("pricing.terms_link") }}
                    </a>
                  </template>
                </i18n-t>
              </label>
            </div>

            <AppButton
              :disabled="!acceptedTerms || processing"
              :loading="processing"
              variant="primary"
              class="w-full h-12 text-base font-semibold shadow-lg shadow-primary-500/20"
              @click="confirmPayment"
            >
              {{
                isTrial
                  ? t("pricing.activate_trial")
                  : t("pricing.confirm_payment")
              }}
            </AppButton>

            <button
              class="w-full py-2 text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              @click="$emit('close')"
            >
              {{ t("pricing.cancel") }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppModal>
</template>

<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {useRouter} from "vue-router";
import {useToast} from "vue-toastification";
import {useI18n} from "vue-i18n";
import api from "@/services/api";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppInput from "@/components/application/ui/Form/AppInput.vue";
import AppCheckbox from "@/components/application/ui/Form/AppCheckbox.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import {XMarkIcon} from "@heroicons/vue/24/outline";

const { t } = useI18n();
const landingUrl = import.meta.env.VITE_LANDING_URL || "https://live.filkx.com";

const props = defineProps({
  selectedPlan: {
    type: Object,
    required: true,
  },
  period: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["close"]);
const router = useRouter();
const toast = useToast();

const selectedMethod = ref("manual");
const acceptedTerms = ref(false);
const processing = ref(false);
const addons = ref([]);
const selectedAddonIds = ref([]);

const cardData = ref({
  number: "",
  expiry: "",
  cvv: "",
});

const promoCode = ref("");
const appliedPromo = ref(null);
const validatingPromo = ref(false);

const autoAppliedCampaign = ref(null);
const pricePreview = ref(null);
const loadingPreview = ref(false);

const paymentMethods = [
  {
    id: "manual",
    labelKey: "pricing.payment_methods.manual",
    descKey: "pricing.payment_methods.manual_desc",
  },
  {
    id: "liqpay",
    labelKey: "pricing.payment_methods.liqpay",
    descKey: "pricing.payment_methods.liqpay_desc",
    disabled: true,
  },
];

const toggleAddon = (addonId) => {
  const ids = [...selectedAddonIds.value];
  const index = ids.indexOf(addonId);
  if (index > -1) {
    ids.splice(index, 1);
  } else {
    ids.push(addonId);
  }
  selectedAddonIds.value = ids;
};

const applyPromo = async () => {
  if (!promoCode.value) return;

  validatingPromo.value = true;
  try {
    try {
      const { data } = await api.post("/billing/campaigns/validate", {
        slug: promoCode.value,
        planId: props.selectedPlan.backend_id,
        amount: subtotal.value,
      });

      if (data.data.eligible) {
        appliedPromo.value = {
          type: "campaign",
          name: data.data.campaign.name,
          discountType: data.data.campaign.type,
          discountValue: data.data.campaign.amount,
          slug: promoCode.value,
        };
        autoAppliedCampaign.value = null;
        toast.success(t("pricing.campaign_applied_success"));
        updatePricePreview();
        return;
      }
    } catch (campaignError) {}

    try {
      const { data } = await api.post("/billing/coupons/validate", {
        code: promoCode.value,
      });

      appliedPromo.value = {
        type: "coupon",
        name: null,
        discountType: data.data.type,
        discountValue: data.data.amount,
        code: promoCode.value,
      };
      toast.success(t("pricing.coupon_applied_success"));
      updatePricePreview();
    } catch (couponError) {
      toast.error(t("pricing.invalid_promo_code"));
    }
  } catch (e) {
    toast.error(e.response?.data?.message ?? t("pricing.invalid_promo_code"));
  } finally {
    validatingPromo.value = false;
  }
};

const removePromo = () => {
  appliedPromo.value = null;
  promoCode.value = "";
  updatePricePreview();
};

const updatePricePreview = async () => {
  if (loadingPreview.value) return;

  loadingPreview.value = true;
  try {
    const payload = {
      planId: props.selectedPlan.backend_id,
      billingCycle: mapPeriod(props.period),
      addons: selectedAddonIds.value,
    };

    if (appliedPromo.value && appliedPromo.value.type === "campaign") {
      payload.campaignSlug = appliedPromo.value.slug;
    }

    const { data } = await api.post("/billing/price/preview", payload);
    pricePreview.value = data.data;

    if (
      data.data.campaign &&
      (!appliedPromo.value || appliedPromo.value.type !== "campaign")
    ) {
      autoAppliedCampaign.value = {
        slug: data.data.campaign.slug,
        name: data.data.campaign.name,
        type: data.data.campaign.type,
        value: data.data.campaign.value,
      };
    } else if (!data.data.campaign) {
      autoAppliedCampaign.value = null;
    }
  } catch (e) {
    console.error("Failed to update price preview:", e);
    pricePreview.value = null;
    autoAppliedCampaign.value = null;
  } finally {
    loadingPreview.value = false;
  }
};

const billingCycleLabel = computed(() => {
  return t(
    `pricing.billing_cycles.${props.period}`,
    t("pricing.billing_cycles.default"),
  );
});

const activeAddons = computed(() => {
  return addons.value.filter((a) => selectedAddonIds.value.includes(a.id));
});

const subtotal = computed(() => {
  let price = Number(props.selectedPlan.finalPrice);
  const months = mapPeriodToMonths(props.period);
  const addonsTotal = activeAddons.value.reduce(
    (sum, addon) => sum + Number(addon.price_monthly) * months,
    0,
  );
  return Math.round(price + addonsTotal);
});

const discountAmount = computed(() => {
  if (!appliedPromo.value || appliedPromo.value.type !== "coupon") return 0;
  const baseAmount = pricePreview.value?.finalAmount || subtotal.value;
  if (appliedPromo.value.discountType === "percent") {
    return Math.round((baseAmount * appliedPromo.value.discountValue) / 100);
  }
  return Math.round(Number(appliedPromo.value.discountValue));
});

const totalPrice = computed(() => {
  const baseAmount = pricePreview.value?.finalAmount || subtotal.value;
  return Math.max(0, baseAmount - discountAmount.value);
});

const mapPeriodToMonths = (period) => {
  return { month: 1, "3months": 3, "6months": 6, year: 12 }[period] || 1;
};

const fetchAddons = async () => {
  try {
    const { data } = await api.get("/billing/addons");
    addons.value = data.data;
  } catch (e) {
    console.error(e);
  }
};

const isTrial = computed(() => {
  return (
    props.selectedPlan.price === 0 ||
    props.selectedPlan.name.toLowerCase().includes("trial")
  );
});

const confirmPayment = async () => {
  if (!acceptedTerms.value) {
    toast.error(t("pricing.accept_terms_error"));
    return;
  }

  processing.value = true;
  try {
    if (isTrial.value) {
      await api.post("/billing/subscription/trial");
      toast.success(t("pricing.trial_activated_success"));
      emit("close");
      window.location.href = "/dashboard";
      return;
    }

    const payload = {
      planId: props.selectedPlan.backend_id,
      billingCycle: mapPeriod(props.period),
      paymentMethod: selectedMethod.value,
    };

    if (appliedPromo.value) {
      if (appliedPromo.value.type === "campaign")
        payload.campaignSlug = appliedPromo.value.slug;
      else if (appliedPromo.value.type === "coupon")
        payload.couponCode = appliedPromo.value.code;
    }

    if (selectedAddonIds.value.length > 0)
      payload.addons = selectedAddonIds.value;
    if (selectedMethod.value === "card") payload.card_data = cardData.value;

    const { data } = await api.post("/billing/payments/initiate", payload);
    const { nextStep, payment } = data.data;

    if (nextStep === "upload_payment_proof") {
      toast.success(t("settings.payment_created_proof"));
      emit("close");
      router.push(`/account/payment/${payment.id}/proof`);
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

const mapPeriod = (period) => {
  return {
    month: "monthly",
    "3months": "quarterly",
    "6months": "semi_annual",
    year: "annual",
  }[period];
};

onMounted(() => {
  fetchAddons();
  updatePricePreview();
});

watch(
  [() => selectedAddonIds.value, () => props.period],
  () => {
    updatePricePreview();
  },
  { deep: true },
);
</script>
