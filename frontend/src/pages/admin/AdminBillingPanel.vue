<template>
  <div class="space-y-8">
    <!-- Header Stats -->
    <BillingStats :stats="stats" />

    <!-- Main Content Card -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
    >
      <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
        <h3 class="font-bold text-lg text-gray-900 dark:text-white">
          {{ t("admin.billing.tabs.pending") }}
        </h3>
        <span
          v-if="pendingPayments.length"
          class="text-xs bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 px-2.5 py-1 rounded-full font-bold animate-pulse"
        >
          {{ pendingPayments.length }} {{ t("admin.billing.tabs.pending_count_label") || "new" }}
        </span>
      </div>

      <!-- Content -->
      <div class="p-6">
        <BillingPendingPayments
          :payments="pendingPayments"
          :loading="loading"
          @refresh="fetchPendingPayments"
          @view-proof="viewProof"
          @approve="openApproveModal"
          @reject="openRejectModal"
        />
      </div>
    </div>

    <!-- Modals -->
    <ProofModal
      v-if="showProofModal"
      :payment="selectedPayment"
      @close="showProofModal = false"
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
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import api from "@/services/api";

import BillingStats from "@/components/admin/features/billing/BillingStats.vue";
import BillingPendingPayments from "@/components/admin/features/billing/BillingPendingPayments.vue";
import ProofModal from "@/components/admin/features/billing/ProofModal.vue";
import BillingApproveModal from "@/components/admin/features/billing/BillingApproveModal.vue";
import BillingRejectModal from "@/components/admin/features/billing/BillingRejectModal.vue";

const toast = useToast();
const { t } = useI18n();

const loading = ref(false);
const modalsLoading = ref(false);
const stats = ref({});
const pendingPayments = ref([]);

const showProofModal = ref(false);
const selectedPayment = ref(null);

const paymentToApprove = ref(null);
const paymentToReject = ref(null);

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

onMounted(() => {
  fetchStats();
  fetchPendingPayments();
});
</script>
