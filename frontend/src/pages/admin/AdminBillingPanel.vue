<template>
  <div class="space-y-8">
    <!-- Header Stats -->
    <BillingStats :stats="stats" />

    <!-- Main Content Card -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
    >
      <!-- Standardized Tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700 px-6">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            :class="[
              'py-5 px-1 border-b-2 font-bold text-sm transition-all flex items-center gap-2 group',
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200',
            ]"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
            <span
              v-if="tab.badge"
              :class="[
                'text-[10px] px-2 py-0.5 rounded-full font-black min-w-[20px] text-center transition-colors',
                activeTab === tab.id
                  ? 'bg-primary-600 text-white'
                  : 'bg-gray-100 text-gray-500 group-hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:group-hover:bg-gray-600',
              ]"
            >
              {{ tab.badge }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="p-6">
        <transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 translate-y-1"
          enter-to-class="opacity-100 translate-y-0"
          mode="out-in"
        >
          <div :key="activeTab">
            <BillingPendingPayments
              v-if="activeTab === 'pending'"
              :payments="pendingPayments"
              :loading="loading"
              @refresh="fetchPendingPayments"
              @view-proof="viewProof"
              @approve="openApproveModal"
              @reject="openRejectModal"
            />

            <BillingSubscriptionTable
              v-else-if="activeTab === 'subscriptions'"
              v-model:filter="subscriptionFilter"
              :subscriptions="subscriptions"
              :loading="loading"
              @refresh="fetchSubscriptions"
              @change-plan="openChangePlanModal"
              @cancel="openCancelModal"
              @expire="openExpireModal"
            />
          </div>
        </transition>
      </div>
    </div>

    <!-- Modals -->
    <ProofModal
      v-if="showProofModal"
      :payment="selectedPayment"
      @close="showProofModal = false"
    />

    <ChangePlanModal
      v-if="showChangePlanModal"
      :subscription="selectedSubscription"
      :plans="plans"
      @close="showChangePlanModal = false"
      @updated="handlePlanChanged"
    />

    <BillingApproveModal
      :is-open="!!paymentToApprove"
      :payment="paymentToApprove"
      :loading="modalsLoading"
      @close="paymentToApprove = null"
      @confirm="handleApproveConfirm"
    />

    <BillingRejectModal
      :is-open="!!paymentToReject"
      :payment="paymentToReject"
      :loading="modalsLoading"
      @close="paymentToReject = null"
      @confirm="handleRejectConfirm"
    />

    <BillingCancelModal
      :is-open="!!subscriptionToCancel"
      :subscription="subscriptionToCancel"
      :loading="modalsLoading"
      @close="subscriptionToCancel = null"
      @confirm="handleCancelConfirm"
    />

    <BillingExpireModal
      :is-open="!!subscriptionToExpire"
      :subscription="subscriptionToExpire"
      :loading="modalsLoading"
      @close="subscriptionToExpire = null"
      @confirm="handleExpireConfirm"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import api from "@/services/api";

import BillingStats from "@/components/admin/features/billing/BillingStats.vue";
import BillingPendingPayments from "@/components/admin/features/billing/BillingPendingPayments.vue";
import BillingSubscriptionTable from "@/components/admin/features/billing/BillingSubscriptionTable.vue";
import ProofModal from "@/components/admin/features/billing/ProofModal.vue";
import ChangePlanModal from "@/components/admin/features/billing/ChangePlanModal.vue";
import BillingApproveModal from "@/components/admin/features/billing/BillingApproveModal.vue";
import BillingRejectModal from "@/components/admin/features/billing/BillingRejectModal.vue";
import BillingCancelModal from "@/components/admin/features/billing/BillingCancelModal.vue";
import BillingExpireModal from "@/components/admin/features/billing/BillingExpireModal.vue";

const toast = useToast();
const { t } = useI18n();

const activeTab = ref("pending");
const loading = ref(false);
const modalsLoading = ref(false);
const stats = ref({});
const pendingPayments = ref([]);
const subscriptions = ref([]);
const subscriptionFilter = ref("");
const plans = ref([]);

const showProofModal = ref(false);
const showChangePlanModal = ref(false);
const selectedPayment = ref(null);
const selectedSubscription = ref(null);

const paymentToApprove = ref(null);
const paymentToReject = ref(null);
const subscriptionToCancel = ref(null);
const subscriptionToExpire = ref(null);

const tabs = computed(() => [
  {
    id: "pending",
    label: t("admin.billing.tabs.pending"),
    badge: pendingPayments.value.length,
  },
  {
    id: "subscriptions",
    label: t("admin.billing.tabs.subscriptions"),
  },
]);

const fetchStats = async () => {
  try {
    const { data } = await api.get("/admin/billing/stats");
    stats.value = data.data;
  } catch (err) {
    console.error("Failed to fetch stats:", err);
  }
};

const fetchPendingPayments = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/billing/payments/pending");
    pendingPayments.value = data.data.data;
  } catch (err) {
    toast.error(t("admin.billing.pending.alerts.loadError"));
  } finally {
    loading.value = false;
  }
};

const fetchSubscriptions = async () => {
  loading.value = true;
  try {
    const params = subscriptionFilter.value
      ? { status: subscriptionFilter.value }
      : {};
    const { data } = await api.get("/admin/billing/subscriptions", { params });
    subscriptions.value = data.data.data;
  } catch (err) {
    toast.error(t("admin.billing.subscriptions.alerts.loadError"));
  } finally {
    loading.value = false;
  }
};

const fetchPlans = async () => {
  try {
    const { data } = await api.get("/subscription-plans");
    plans.value = data.data;
  } catch (err) {
    console.error("Failed to fetch plans:", err);
  }
};

const viewProof = async (payment) => {
  try {
    const { data } = await api.get(
      `/admin/billing/payments/${payment.id}/proof`,
    );
    selectedPayment.value = data.data;
    showProofModal.value = true;
  } catch (e) {
    toast.error("Failed to fetch payment proof");
  }
};

const openApproveModal = (payment) => {
  paymentToApprove.value = payment;
};

const handleApproveConfirm = async (id) => {
  modalsLoading.value = true;
  try {
    await api.post(`/admin/billing/payments/${id}/confirm`, { approve: true });
    toast.success(t("admin.billing.pending.alerts.approveSuccess"));
    paymentToApprove.value = null;
    await fetchPendingPayments();
    await fetchStats();
  } catch (err) {
    toast.error(
      err.response?.data?.message ??
        t("admin.billing.pending.alerts.processError"),
    );
  } finally {
    modalsLoading.value = false;
  }
};

const openRejectModal = (payment) => {
  paymentToReject.value = payment;
};

const handleRejectConfirm = async ({ id, reason }) => {
  modalsLoading.value = true;
  try {
    await api.post(`/admin/billing/payments/${id}/confirm`, {
      approve: false,
      reason,
    });
    toast.success(t("admin.billing.pending.alerts.rejectSuccess"));
    paymentToReject.value = null;
    await fetchPendingPayments();
    await fetchStats();
  } catch (err) {
    toast.error(
      err.response?.data?.message ??
        t("admin.billing.pending.alerts.processError"),
    );
  } finally {
    modalsLoading.value = false;
  }
};

const openChangePlanModal = (subscription) => {
  selectedSubscription.value = subscription;
  showChangePlanModal.value = true;
};

const handlePlanChanged = () => {
  fetchSubscriptions();
  showChangePlanModal.value = false;
  toast.success(t("admin.billing.subscriptions.alerts.planChangeSuccess"));
};

const openCancelModal = (subscription) => {
  subscriptionToCancel.value = subscription;
};

const handleCancelConfirm = async ({ id, reason }) => {
  modalsLoading.value = true;
  try {
    await api.post(`/admin/billing/subscriptions/${id}/cancel`, { reason });
    toast.success(t("admin.billing.subscriptions.alerts.cancelSuccess"));
    subscriptionToCancel.value = null;
    await fetchSubscriptions();
    await fetchStats();
  } catch (err) {
    toast.error(t("admin.billing.subscriptions.alerts.cancelError"));
  } finally {
    modalsLoading.value = false;
  }
};

const openExpireModal = (subscription) => {
  subscriptionToExpire.value = subscription;
};

const handleExpireConfirm = async ({ id, reason }) => {
  modalsLoading.value = true;
  try {
    await api.post(`/admin/billing/subscriptions/${id}/expire`, { reason });
    toast.success(t("admin.billing.subscriptions.alerts.expireSuccess"));
    subscriptionToExpire.value = null;
    await fetchSubscriptions();
    await fetchStats();
  } catch (err) {
    toast.error(t("admin.billing.subscriptions.alerts.expireError"));
  } finally {
    modalsLoading.value = false;
  }
};

watch(subscriptionFilter, () => {
  fetchSubscriptions();
});

onMounted(() => {
  fetchStats();
  fetchPendingPayments();
  fetchSubscriptions();
  fetchPlans();
});
</script>
