<template>
  <div
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
    @click.self="$emit('close')"
  >
    <div
      class="bg-white dark:bg-gray-800 rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
    >
      <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-semibold">
              Деталі платежу #{{ payment.id }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ formatDate(payment.createdAt) }}
            </p>
          </div>
          <button
            class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
            @click="$emit('close')"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </div>
      </div>

      <div class="p-6 space-y-6">
        <div class="grid grid-cols-2 gap-6">
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
              Сума платежу
            </p>
            <p class="text-3xl font-bold">
              ${{ payment.amount }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ payment.currency }}
            </p>
          </div>
          <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
              Статус
            </p>
            <StatusBadge
              :status="payment.status"
              class="text-lg"
            />
            <p
              v-if="payment.processedAt"
              class="text-sm text-gray-500 dark:text-gray-400 mt-2"
            >
              Оброблено: {{ formatDate(payment.processedAt) }}
            </p>
          </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
          <h4 class="font-semibold mb-3">
            Інформація про платіж
          </h4>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-gray-600 dark:text-gray-400">
                Опис:
              </dt>
              <dd class="font-medium text-right">
                {{ payment.description }}
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-600 dark:text-gray-400">
                Метод оплати:
              </dt>
              <dd class="font-medium">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700"
                >
                  {{ paymentMethodLabel(payment.paymentMethod) }}
                </span>
              </dd>
            </div>
            <div
              v-if="payment.provider"
              class="flex justify-between"
            >
              <dt class="text-gray-600 dark:text-gray-400">
                Провайдер:
              </dt>
              <dd class="font-medium">
                {{ payment.provider }}
              </dd>
            </div>
            <div
              v-if="payment.providerTransactionId"
              class="flex justify-between"
            >
              <dt class="text-gray-600 dark:text-gray-400">
                ID транзакції:
              </dt>
              <dd class="font-mono text-sm">
                {{ payment.providerTransactionId }}
              </dd>
            </div>
            <div
              v-if="payment.subscriptionId"
              class="flex justify-between"
            >
              <dt class="text-gray-600 dark:text-gray-400">
                Підписка:
              </dt>
              <dd class="font-medium">
                #{{ payment.subscriptionId }}
              </dd>
            </div>
          </dl>
        </div>

        <div
          v-if="payment.purchaseDetails"
          class="border-t border-gray-200 dark:border-gray-700 pt-4"
        >
          <h4 class="font-semibold mb-3">
            Деталізація ціни
          </h4>
          <div class="space-y-2 text-sm">
            <div
              v-if="payment.purchaseDetails.plan"
              class="flex justify-between"
            >
              <span class="text-gray-600 dark:text-gray-400">
                {{ payment.purchaseDetails.plan.name }}
              </span>
              <span class="font-medium">
                ${{ payment.purchaseDetails.plan.price }}
              </span>
            </div>
            <div
              v-for="addon in payment.purchaseDetails.addons"
              :key="addon.name"
              class="flex justify-between pl-2"
            >
              <span class="text-gray-600 dark:text-gray-400">
                {{ addon.name }}
              </span>
              <span class="font-medium"> ${{ addon.price }} </span>
            </div>
            <div
              v-if="(payment.purchaseDetails.campaignDiscount || 0) > 0"
              class="flex justify-between text-blue-600 dark:text-blue-400 font-medium"
            >
              <span class="flex items-center">
                {{ t("pricing.promo_discount", "Promo Discount") }}
                <span
                  v-if="payment.purchaseDetails.campaign"
                  class="ml-1 text-[10px] bg-blue-100 dark:bg-blue-900/30 px-1.5 py-0.5 rounded"
                >
                  {{
                    payment.purchaseDetails.campaign.name ||
                      payment.purchaseDetails.campaign.slug
                  }}
                </span>
              </span>
              <span>-${{ payment.purchaseDetails.campaignDiscount }}</span>
            </div>
            <div
              v-if="(payment.purchaseDetails.couponDiscount || 0) > 0"
              class="flex justify-between text-green-600 dark:text-green-400 font-medium"
            >
              <span class="flex items-center">
                {{ t("pricing.coupon_discount", "Coupon Discount") }}
                <span
                  v-if="payment.purchaseDetails.coupon"
                  class="ml-1 text-[10px] bg-green-100 dark:bg-green-900/30 px-1.5 py-0.5 rounded"
                >
                  {{ payment.purchaseDetails.coupon.code }}
                </span>
              </span>
              <span>-${{ payment.purchaseDetails.couponDiscount }}</span>
            </div>
            <div
              class="pt-2 border-t border-gray-200 dark:border-gray-700 font-bold flex justify-between"
            >
              <span>{{ t("pricing.total") }}</span>
              <span>${{ payment.amount }}</span>
            </div>
          </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
          <h4 class="font-semibold mb-4">
            Хронологія
          </h4>
          <div class="space-y-4">
            <div class="flex items-start">
              <div
                class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center"
              >
                <svg
                  class="w-4 h-4 text-blue-600 dark:text-blue-400"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium">
                  Платіж створено
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatDate(payment.createdAt) }}
                </p>
              </div>
            </div>

            <div
              v-if="payment.processedAt"
              class="flex items-start"
            >
              <div
                class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center"
              >
                <svg
                  class="w-4 h-4 text-green-600 dark:text-green-400"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium">
                  Платіж оброблено
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ formatDate(payment.processedAt) }}
                </p>
              </div>
            </div>

            <div
              v-else-if="payment.status === 'pending'"
              class="flex items-start"
            >
              <div
                class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"
              >
                <svg
                  class="w-4 h-4 text-amber-600 dark:text-amber-400"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium">
                  Очікує підтвердження
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  В обробці
                </p>
              </div>
            </div>
          </div>
        </div>

        <div
          class="border-t border-gray-200 dark:border-gray-700 pt-4 flex gap-3"
        >
          <router-link
            v-if="
              payment.status === 'pending' && payment.paymentMethod === 'manual'
            "
            :to="`/account/payment/${payment.id}/proof`"
            class="flex-1 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg px-4 py-2 text-center transition-colors"
          >
            Завантажити підтвердження
          </router-link>
          <button
            class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-medium rounded-lg px-4 py-2 transition-colors flex items-center justify-center"
            @click="downloadInvoice"
          >
            <svg
              class="w-5 h-5 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
            Завантажити рахунок
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import StatusBadge from "@/components/application/features/billing/StatusBadge.vue";
import api from "@/services/api";

const props = defineProps({
  payment: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close"]);
const toast = useToast();
const { t } = useI18n();

const paymentMethodLabel = (method) => {
  const labels = {
    manual: "Ручна оплата",
    card: "Банківська картка",
    liqpay: "LiqPay",
    stripe: "Stripe",
  };
  return labels[method] || method;
};

const formatDate = (date) => {
  if (!date) return "N/A";
  return new Date(date).toLocaleDateString("uk-UA", {
    day: "2-digit",
    month: "long",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

const downloadInvoice = async () => {
  try {
    const { data } = await api.get(
      `/billing/payments/${props.payment.id}/invoice`,
      {
        responseType: "blob",
      },
    );

    const url = window.URL.createObjectURL(new Blob([data]));
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `invoice_${props.payment.id}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();

    toast.success("Рахунок завантажено");
  } catch (e) {
    toast.error("Помилка завантаження рахунку");
  }
};
</script>
