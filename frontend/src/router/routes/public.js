import HomePage from "@/pages/landing/HomePage.vue";
import PublicPricingPage from "@/pages/landing/PricingPage.vue";
import PublicFaqPage from "@/pages/landing/FaqPage.vue";
import AboutPage from "@/pages/landing/AboutPage.vue";
import ContactsPage from "@/pages/landing/ContactsPage.vue";
import TermsPage from "@/pages/landing/TermsPage.vue";
import PrivacyPage from "@/pages/landing/PrivacyPage.vue";

export default {
  path: "/:locale(en|uk)",
  meta: { auth: false, public: true },
  children: [
    {
      path: "",
      name: "home",
      component: HomePage,
    },
    {
      path: "pricing",
      name: "pricing",
      component: PublicPricingPage,
    },
    {
      path: "faq",
      name: "faq",
      component: PublicFaqPage,
    },
    {
      path: "about",
      name: "about",
      component: AboutPage,
    },
    {
      path: "contacts",
      name: "contacts",
      component: ContactsPage,
    },
    {
      path: "terms",
      name: "terms",
      component: TermsPage,
    },
    {
      path: "privacy",
      name: "privacy",
      component: PrivacyPage,
    },
    {
      path: "security",
      name: "security",
      component: () => import("@/pages/landing/SecurityPage.vue"),
    },
    {
      path: "accessibility",
      name: "accessibility",
      component: () => import("@/pages/landing/AccessibilityPage.vue"),
    },
  ],
};
