<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <!-- Stats Grid -->
    <AffiliateStatsGrid :stats="stats" />

    <!-- Main Content Tabs -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
    >
      <div class="border-b border-gray-200 dark:border-gray-700 px-6">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabConfigs"
            :key="tab.id"
            :class="[
              'py-4 px-1 border-b-2 font-bold text-sm transition-all flex items-center gap-2 outline-none',
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            ]"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
            <span
              v-if="tab.badge"
              class="bg-primary-600 text-white text-[10px] px-2 py-0.5 rounded-full font-black min-w-[20px] text-center animate-pulse"
            >
              {{ tab.badge }}
            </span>
          </button>
        </nav>
      </div>

      <div class="p-6">
        <!-- Tab Content -->
        <keep-alive>
          <component
            :is="activeTabComponent"
            v-bind="tabProps"
            @search="fetchAffiliates"
            @filter="fetchFilteredData"
            @open-modal="openStatusModal"
            @approve="openApproveModal"
            @mark-paid="openMarkPaidModal"
            @reject="openRejectModal"
            @update:search-query="searchQuery = $event"
            @update:status-filter="statusFilter = $event"
            @update:payout-status-filter="payoutStatusFilter = $event"
          />
        </keep-alive>
      </div>
    </div>

    <!-- Modals -->
    <AffiliateStatusModal
      v-if="selectedAffiliate"
      :affiliate="selectedAffiliate"
      @close="selectedAffiliate = null"
      @updated="handleStatusUpdated"
    />

    <PayoutApproveModal
      v-if="payoutToApprove"
      :payout="payoutToApprove"
      @close="payoutToApprove = null"
      @approved="handlePayoutApproved"
    />

    <PayoutMarkPaidModal
      v-if="payoutToMarkPaid"
      :payout="payoutToMarkPaid"
      @close="payoutToMarkPaid = null"
      @marked="handlePayoutMarkedPaid"
    />

    <PayoutRejectModal
      v-if="payoutToReject"
      :payout="payoutToReject"
      @close="payoutToReject = null"
      @rejected="handlePayoutRejected"
    />
  </div>
</template>

<script setup>
import { computed, markRaw, onMounted, ref, watch } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import api from "@/services/api";

// Components
import AffiliateStatsGrid from "@/components/admin/features/affiliate/AffiliateStatsGrid.vue";
import AffiliateListTab from "@/components/admin/features/affiliate/AffiliateListTab.vue";
import PayoutListTab from "@/components/admin/features/affiliate/PayoutListTab.vue";
import ReferralListTab from "@/components/admin/features/affiliate/ReferralListTab.vue";
import CommissionListTab from "@/components/admin/features/affiliate/CommissionListTab.vue";
import FraudAlertListTab from "@/components/admin/features/affiliate/FraudAlertListTab.vue";
import AuditLogListTab from "@/components/admin/features/affiliate/AuditLogListTab.vue";

// Modals
import AffiliateStatusModal from "@/components/admin/features/affiliate/AffiliateStatusModal.vue";
import PayoutApproveModal from "@/components/admin/features/affiliate/PayoutApproveModal.vue";
import PayoutMarkPaidModal from "@/components/admin/features/affiliate/PayoutMarkPaidModal.vue";
import PayoutRejectModal from "@/components/admin/features/affiliate/PayoutRejectModal.vue";

const toast = useToast();
const { t } = useI18n();

// State
const activeTab = ref("affiliates");
const loading = ref(false);
const stats = ref({});
const affiliates = ref([]);
const payouts = ref([]);
const referrals = ref([]);
const commissions = ref([]);
const fraudAlerts = ref([]);
const auditLogs = ref([]);

const searchQuery = ref("");
const statusFilter = ref("");
const payoutStatusFilter = ref("requested");

const selectedAffiliate = ref(null);
const payoutToApprove = ref(null);
const payoutToMarkPaid = ref(null);
const payoutToReject = ref(null);

// Tab Mapping
const tabMap = {
  affiliates: markRaw(AffiliateListTab),
  payouts: markRaw(PayoutListTab),
  referrals: markRaw(ReferralListTab),
  commissions: markRaw(CommissionListTab),
  fraud: markRaw(FraudAlertListTab),
  logs: markRaw(AuditLogListTab),
};

const tabConfigs = computed(() => [
  { id: "affiliates", label: t("admin.affiliates.tabs.partners") },
  {
    id: "payouts",
    label: t("admin.affiliates.tabs.payouts"),
    badge: payouts.value.filter((p) => p.status === "requested").length || null,
  },
  { id: "referrals", label: t("admin.affiliates.tabs.referrals") },
  { id: "commissions", label: t("admin.affiliates.tabs.commissions") },
  {
    id: "fraud",
    label: t("admin.affiliates.tabs.fraud"),
    badge: fraudAlerts.value.length || null,
  },
  { id: "logs", label: t("admin.affiliates.tabs.logs") },
]);

const activeTabComponent = computed(() => tabMap[activeTab.value]);

const tabProps = computed(() => {
  switch (activeTab.value) {
    case "affiliates":
      return {
        affiliates: affiliates.value,
        loading: loading.value,
        searchQuery: searchQuery.value,
        statusFilter: statusFilter.value,
      };
    case "payouts":
      return {
        payouts: payouts.value,
        loading: loading.value,
        statusFilter: payoutStatusFilter.value,
      };
    case "referrals":
      return { referrals: referrals.value, loading: loading.value };
    case "commissions":
      return { commissions: commissions.value, loading: loading.value };
    case "fraud":
      return { alerts: fraudAlerts.value };
    case "logs":
      return { logs: auditLogs.value, loading: loading.value };
    default:
      return {};
  }
});

// Data Fetching
const fetchStats = async () => {
  try {
    const { data } = await api.get("/admin/affiliates/stats");
    stats.value = data.data || {};
  } catch (err) {
    console.error("Failed to fetch stats:", err);
  }
};

const fetchAffiliates = async () => {
  loading.value = true;
  try {
    const params = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (statusFilter.value) params.status = statusFilter.value;

    const { data } = await api.get("/admin/affiliates", { params });
    const responseData = data.data;
    affiliates.value = Array.isArray(responseData)
      ? responseData
      : responseData.data || [];
  } catch (err) {
    toast.error(t("admin.affiliates.messages.load_error_partners"));
  } finally {
    loading.value = false;
  }
};

const fetchPayouts = async () => {
  loading.value = true;
  try {
    const params = {};
    if (payoutStatusFilter.value) params.status = payoutStatusFilter.value;

    const { data } = await api.get("/admin/affiliates/payouts/list", {
      params,
    });
    const responseData = data.data;
    payouts.value = Array.isArray(responseData)
      ? responseData
      : responseData.data || [];
  } catch (err) {
    toast.error(t("admin.affiliates.messages.load_error_payouts"));
  } finally {
    loading.value = false;
  }
};

const fetchReferrals = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/affiliates/referrals");
    const responseData = data.data;
    referrals.value = Array.isArray(responseData)
      ? responseData
      : responseData.data || [];
  } catch (err) {
    toast.error(t("admin.affiliates.messages.load_error_referrals"));
  } finally {
    loading.value = false;
  }
};

const fetchCommissions = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/affiliates/commissions");
    const responseData = data.data;
    commissions.value = Array.isArray(responseData)
      ? responseData
      : responseData.data || [];
  } catch (err) {
    toast.error(t("admin.affiliates.messages.load_error_commissions"));
  } finally {
    loading.value = false;
  }
};

const fetchFraudAlerts = async () => {
  try {
    const { data } = await api.get("/admin/affiliates/fraud-alerts");
    fraudAlerts.value = data.data || [];
  } catch (err) {
    console.error("Failed to fetch fraud alerts:", err);
  }
};

const fetchAuditLogs = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/logs", {
      params: { domain: "affiliate" },
    });
    const responseData = data.data;
    auditLogs.value = Array.isArray(responseData)
      ? responseData
      : responseData.data || [];
  } catch (err) {
    toast.error(t("admin.affiliates.messages.load_error_logs"));
  } finally {
    loading.value = false;
  }
};

const fetchFilteredData = () => {
  if (activeTab.value === "affiliates") fetchAffiliates();
  if (activeTab.value === "payouts") fetchPayouts();
};

// Modal Handlers
const openStatusModal = (affiliate) => {
  selectedAffiliate.value = affiliate;
};

const handleStatusUpdated = () => {
  selectedAffiliate.value = null;
  fetchAffiliates();
  fetchStats();
  toast.success(t("admin.affiliates.messages.status_updated"));
};

const openApproveModal = (payout) => {
  payoutToApprove.value = payout;
};

const handlePayoutApproved = () => {
  payoutToApprove.value = null;
  fetchPayouts();
  fetchStats();
  toast.success(t("admin.affiliates.messages.payout_approved"));
};

const openMarkPaidModal = (payout) => {
  payoutToMarkPaid.value = payout;
};

const handlePayoutMarkedPaid = () => {
  payoutToMarkPaid.value = null;
  fetchPayouts();
  fetchStats();
  toast.success(t("admin.affiliates.messages.payout_marked_paid"));
};

const openRejectModal = (payout) => {
  payoutToReject.value = payout;
};

const handlePayoutRejected = () => {
  payoutToReject.value = null;
  fetchPayouts();
  fetchStats();
  toast.success(t("admin.affiliates.messages.payout_rejected"));
};

// Lifecycle
onMounted(() => {
  fetchStats();
  fetchAffiliates();
  fetchPayouts();
  fetchFraudAlerts();
});

watch(activeTab, (newTab) => {
  if (newTab === "affiliates" && !affiliates.value.length) fetchAffiliates();
  if (newTab === "payouts" && !payouts.value.length) fetchPayouts();
  if (newTab === "referrals" && !referrals.value.length) fetchReferrals();
  if (newTab === "commissions" && !commissions.value.length) fetchCommissions();
  if (newTab === "fraud" && !fraudAlerts.value.length) fetchFraudAlerts();
  if (newTab === "logs" && !auditLogs.value.length) fetchAuditLogs();
});
</script>
