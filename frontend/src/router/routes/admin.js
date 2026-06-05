import AdminBillingPanel from "@/pages/admin/AdminBillingPanel.vue";
import AdminDashboard from "@/pages/admin/AdminDashboard.vue";
import AdminClients from "@/pages/admin/AdminClients.vue";
import AdminSettings from "@/pages/admin/AdminSettings.vue";
import AdminAnalytics from "@/pages/admin/AdminAnalytics.vue";
import AdminSupport from "@/pages/admin/AdminSupport.vue";
import AdminCoupons from "@/pages/admin/AdminCoupons.vue";
import AdminPromotions from "@/pages/admin/AdminPromotions.vue";
import AdminTeam from "@/pages/admin/AdminTeam.vue";
import AdminLogs from "@/pages/admin/AdminLogs.vue";
import AdminSystem from "@/pages/admin/AdminSystem.vue";
import AdminRoles from "@/pages/admin/AdminRoles.vue";
import AdminNotifications from "@/pages/admin/AdminNotifications.vue";
import AdminLayoutWrapper from "@/pages/admin/AdminLayoutWrapper.vue";
import AdminServerLogs from "@/pages/admin/AdminServerLogs.vue";
import AdminAccountingLedger from "@/pages/admin/accounting/AdminAccountingLedger.vue";
import AdminAccountingInvoices from "@/pages/admin/accounting/AdminAccountingInvoices.vue";
import AdminProducts from "@/pages/admin/AdminProducts.vue";
import AdminOrders from "@/pages/admin/AdminOrders.vue";
import AdminCategories from "@/pages/admin/AdminCategories.vue";
import AdminBrands from "@/pages/admin/AdminBrands.vue";

export default {
  path: "/admin",
  component: AdminLayoutWrapper,
  meta: {
    auth: true,
    verified: true,
    requiresSubscription: true,
  },
  children: [
    {
      path: "dashboard",
      name: "admin-dashboard",
      component: AdminDashboard,
      meta: {
        titleKey: "admin.dashboard.title",
        descriptionKey: "admin.dashboard.description",
      },
    },
    {
      path: "products",
      name: "admin-products",
      component: AdminProducts,
      meta: {
        titleKey: "admin.products.title",
        descriptionKey: "admin.products.description",
      },
    },
    {
      path: "categories",
      name: "admin-categories",
      component: AdminCategories,
      meta: {
        titleKey: "admin.categories.title",
        descriptionKey: "admin.categories.description",
      },
    },
    {
      path: "brands",
      name: "admin-brands",
      component: AdminBrands,
      meta: {
        titleKey: "admin.brands.title",
        descriptionKey: "admin.brands.description",
      },
    },

    {
      path: "orders",
      name: "admin-orders",
      component: AdminOrders,
      meta: {
        titleKey: "admin.orders.title",
        descriptionKey: "admin.orders.description",
      },
    },
    {
      path: "users",
      name: "admin-clients",
      component: AdminClients,
      meta: {
        titleKey: "admin.users.title",
        descriptionKey: "admin.users.description",
      },
    },
    {
      path: "roles",
      name: "admin-roles",
      component: AdminRoles,
      meta: {
        titleKey: "admin.roles.title",
        descriptionKey: "admin.roles.description",
      },
    },
    {
      path: "billing",
      name: "admin-billing",
      component: AdminBillingPanel,
      meta: {
        titleKey: "admin.billing.description",
        descriptionKey: "admin.billing.description",
      },
    },
    {
      path: "accounting/ledger",
      name: "admin-accounting-ledger",
      component: AdminAccountingLedger,
      meta: {
        titleKey: "admin.accounting.ledger.title",
        descriptionKey: "admin.accounting.ledger.description",
      },
    },
    {
      path: "accounting/invoices",
      name: "admin-accounting-invoices",
      component: AdminAccountingInvoices,
      meta: {
        titleKey: "admin.accounting.invoices.title",
        descriptionKey: "admin.accounting.invoices.description",
      },
    },
    {
      path: "analytics",
      name: "admin-analytics",
      component: AdminAnalytics,
      meta: {
        titleKey: "admin.analytics.title",
        descriptionKey: "admin.analytics.description",
      },
    },
    {
      path: "support",
      name: "admin-support",
      component: AdminSupport,
      meta: {
        titleKey: "admin.support.title",
        descriptionKey: "admin.support.description",
      },
    },
    {
      path: "coupons",
      name: "admin-coupons",
      component: AdminCoupons,
      meta: {
        titleKey: "admin.marketing.coupons.title",
        descriptionKey: "admin.marketing.coupons.description",
      },
    },
    {
      path: "promotions",
      name: "admin-promotions",
      component: AdminPromotions,
      meta: {
        titleKey: "admin.promotions.title",
        descriptionKey: "admin.promotions.description",
      },
    },
    {
      path: "team",
      name: "admin-team",
      component: AdminTeam,
      meta: {
        titleKey: "admin.team.title",
        descriptionKey: "admin.team.description",
      },
    },
    {
      path: "logs",
      name: "admin-logs",
      component: AdminLogs,
      meta: {
        titleKey: "admin.logs.title",
        descriptionKey: "admin.logs.description",
      },
    },
    {
      path: "server-logs",
      name: "server-logs",
      component: AdminServerLogs,
      meta: {
        titleKey: "admin.logs.title",
        descriptionKey: "admin.logs.description",
      },
    },
    {
      path: "system",
      name: "admin-system",
      component: AdminSystem,
      meta: {
        titleKey: "admin.system.title",
        descriptionKey: "admin.system.description",
      },
    },
    {
      path: "settings",
      name: "admin-settings",
      component: AdminSettings,
      meta: {
        titleKey: "admin.settings.title",
        descriptionKey: "admin.settings.description",
      },
    },
    {
      path: "notifications",
      name: "admin-notifications",
      component: AdminNotifications,
      meta: {
        titleKey: "admin.notifications.title",
        descriptionKey: "admin.notifications.description",
      },
    },
    {
      path: "",
      redirect: { name: "admin-dashboard" },
    },
  ],
};
