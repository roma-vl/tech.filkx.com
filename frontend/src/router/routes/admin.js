import AdminBillingPanel from "@/pages/admin/AdminBillingPanel.vue";
import AdminDashboard from "@/pages/admin/AdminDashboard.vue";
import AdminClients from "@/pages/admin/AdminClients.vue";
import AdminSettings from "@/pages/admin/AdminSettings.vue";
import AdminAnalytics from "@/pages/admin/AdminAnalytics.vue";
import AdminVideos from "@/pages/admin/AdminVideos.vue";
import AdminStreams from "@/pages/admin/AdminStreams.vue";
import AdminPlaylists from "@/pages/admin/AdminPlaylists.vue";
import AdminSupport from "@/pages/admin/AdminSupport.vue";
import AdminCoupons from "@/pages/admin/AdminCoupons.vue";
import AdminPromotions from "@/pages/admin/AdminPromotions.vue";
import AdminTeam from "@/pages/admin/AdminTeam.vue";
import AdminLogs from "@/pages/admin/AdminLogs.vue";
import AdminSystem from "@/pages/admin/AdminSystem.vue";
import AdminRoles from "@/pages/admin/AdminRoles.vue";
import AdminPlanBuilder from "@/pages/admin/AdminPlanBuilder.vue";
import AdminNotifications from "@/pages/admin/AdminNotifications.vue";
import AdminEmailTemplates from "@/pages/admin/AdminEmailTemplates.vue";
import AdminLayoutWrapper from "@/pages/admin/AdminLayoutWrapper.vue";
import AdminDocs from "@/pages/admin/AdminDocs.vue";
import AdminServerLogs from "@/pages/admin/AdminServerLogs.vue";
import AdminAffiliates from "@/pages/admin/AdminAffiliates.vue";
import AdminGrowth from "@/pages/admin/AdminGrowth.vue";
import AdminAccountingLedger from "@/pages/admin/accounting/AdminAccountingLedger.vue";
import AdminAccountingInvoices from "@/pages/admin/accounting/AdminAccountingInvoices.vue";
import AdminRunnerNodes from "@/pages/admin/AdminRunnerNodes.vue";
import AdminTranscoderNodes from "@/pages/admin/AdminTranscoderNodes.vue";
import AdminConverter from "@/pages/admin/AdminConverter.vue";

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
      path: "users",
      name: "admin-clients",
      component: AdminClients,
      meta: {
        titleKey: "admin.users.title",
        descriptionKey: "admin.users.description",
      },
    },
    {
      path: "affiliates",
      name: "admin-affiliates",
      component: AdminAffiliates,
      meta: {
        titleKey: "admin.affiliates.title",
        descriptionKey: "admin.affiliates.description",
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
      path: "videos",
      name: "admin-videos",
      component: AdminVideos,
      meta: {
        titleKey: "admin.videos.title",
        descriptionKey: "admin.videos.description",
      },
    },
    {
      path: "streams",
      name: "admin-streams",
      component: AdminStreams,
      meta: {
        titleKey: "admin.streams.title",
        descriptionKey: "admin.streams.description",
      },
    },
    {
      path: "runner-nodes",
      name: "admin-runner-nodes",
      component: AdminRunnerNodes,
      meta: {
        titleKey: "admin.runnerNodes.title",
        descriptionKey: "admin.runnerNodes.description",
      },
    },
    {
      path: "transcoder-nodes",
      name: "admin-transcoder-nodes",
      component: AdminTranscoderNodes,
      meta: {
        titleKey: "admin.transcoderNodes.title",
        descriptionKey: "admin.transcoderNodes.description",
      },
    },
    {
      path: "playlists",
      name: "admin-playlists",
      component: AdminPlaylists,
      meta: {
        titleKey: "admin.playlists.title",
        descriptionKey: "admin.playlists.description",
      },
    },
    {
      path: "converter",
      name: "admin-converter",
      component: AdminConverter,
      meta: {
        titleKey: "admin.converter.title",
        descriptionKey: "admin.converter.subtitle",
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
      path: "growth",
      name: "admin-growth",
      component: AdminGrowth,
      meta: {
        titleKey: "admin.growth.title",
        descriptionKey: "admin.growth.description",
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
      path: "emails",
      name: "admin-emails",
      component: AdminEmailTemplates,
      meta: {
        titleKey: "admin.emails.title",
        descriptionKey: "admin.emails.description",
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
      path: "plans",
      name: "admin-plans",
      component: AdminPlanBuilder,
      meta: {
        titleKey: "admin.plans.title",
        descriptionKey: "admin.plans.description",
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
      path: "docs",
      name: "admin-docs",
      component: AdminDocs,
      meta: {
        titleKey: "admin.docs.title",
        descriptionKey: "admin.docs.description",
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
