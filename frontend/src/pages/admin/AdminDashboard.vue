<template>
  <div class="space-y-8 relative min-h-[400px]">
    <AppLoadingOverlay
      :loading="loading"
      :text="$t('admin.dashboard.loading')"
    />

    <AdminStatsOverview :stats="formattedStats" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <AdminRecentUsers :users="recentUsers" />

      <AdminQuickActions
        :actions="translatedQuickActions"
        @action="(to) => router.push(to)"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import axios from "@/shared/services/api/apiClient";

import AppLoadingOverlay from "@/components/admin/ui/AppLoadingOverlay.vue";
import AdminStatsOverview from "@/components/admin/features/dashboard/AdminStatsOverview.vue";
import AdminRecentUsers from "@/components/admin/features/dashboard/AdminRecentUsers.vue";
import AdminQuickActions from "@/components/admin/features/dashboard/AdminQuickActions.vue";

import {
  UsersIcon,
  ShoppingCartIcon,
  ShoppingBagIcon,
  BanknotesIcon,
  UserPlusIcon,
  EnvelopeIcon,
  Cog6ToothIcon,
} from "@heroicons/vue/24/outline";

const { t } = useI18n();
const router = useRouter();

const iconMap = {
  UsersIcon,
  ShoppingCartIcon,
  ShoppingBagIcon,
  BanknotesIcon,
};

const quickStats = ref([]);
const recentUsers = ref([]);
const loading = ref(true);

const formattedStats = computed(() => {
  return quickStats.value.map((stat) => ({
    ...stat,
    label: t(
      `admin.dashboard.stats.${stat.label.toLowerCase().replace(" ", "_")}`,
      stat.label,
    ),
    icon: iconMap[stat.icon] || UsersIcon,
  }));
});

const translatedQuickActions = computed(() => [
  {
    label: t("admin.dashboard.quickActions.clientManagement.label"),
    desc: t("admin.dashboard.quickActions.clientManagement.desc"),
    icon: UserPlusIcon,
    bg: "bg-blue-500",
    to: "/admin/users",
  },
  {
    label: "Керування товарами",
    desc: "Додавання, редагування та огляд товарів",
    icon: ShoppingBagIcon,
    bg: "bg-red-500",
    to: "/admin/products",
  },
  {
    label: t("admin.dashboard.quickActions.supportTickets.label"),
    desc: t("admin.dashboard.quickActions.supportTickets.desc"),
    icon: EnvelopeIcon,
    bg: "bg-green-500",
    to: "/admin/support",
  },
  {
    label: t("admin.dashboard.quickActions.systemHealth.label"),
    desc: t("admin.dashboard.quickActions.systemHealth.desc"),
    icon: Cog6ToothIcon,
    bg: "bg-purple-500",
    to: "/admin/system",
  },
]);

const fetchStats = async () => {
  try {
    const response = await axios.get("/admin/stats");
    quickStats.value = response.data.data.stats;
    recentUsers.value =
      response.data.data.recent_users || response.data.data.recentUsers || [];
  } catch (error) {
    console.error("Failed to fetch dashboard stats", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStats();
});
</script>
