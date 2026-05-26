import SubscriptionManagementPage from "@/pages/billing/SubscriptionManagementPage.vue";
import PaymentProofUploadPage from "@/pages/billing/PaymentProofUploadPage.vue";
import BillingHistoryPage from "@/pages/billing/BillingHistoryPage.vue";
import TrialExpiredPage from "@/pages/billing/TrialExpiredPage.vue";
import PaymentGatewayPage from "@/pages/billing/PaymentGatewayPage.vue";

import ApplicationLayoutWrapper from "@/pages/application/ApplicationLayoutWrapper.vue";

export default [
  {
    path: "/account",
    children: [
      {
        path: "trial-expired",
        component: TrialExpiredPage,
        meta: { auth: true, verified: true },
      },
      {
        path: "subscription",
        component: SubscriptionManagementPage,
        meta: { auth: true, verified: true },
      },
      {
        path: "billing/history",
        component: BillingHistoryPage,
        meta: { auth: true, verified: true },
      },
      {
        path: "payment/:id/proof",
        component: PaymentProofUploadPage,
        meta: { auth: true, verified: true },
        props: true,
      },
      {
        path: "payment/:id/gateway",
        component: PaymentGatewayPage,
        meta: { auth: true, verified: true },
        props: true,
      },
    ],
  },
];
