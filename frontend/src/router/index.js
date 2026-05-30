import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useSystemStore } from "@/stores/system";
import { i18n } from "@/i18n";
import { routes } from "./routes";

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  if (to.meta.public && to.params.locale) {
    i18n.global.locale.value = to.params.locale;
    document.documentElement.lang = to.params.locale;
  }

  const auth = useAuthStore();
  const system = useSystemStore();

  if (to.name !== "maintenance") {
    if (!system.lastChecked || Date.now() - system.lastChecked > 60000) {
      await system.checkStatus();
    }

    if (system.maintenanceMode) {
      return next({ name: "maintenance" });
    }
  }

  if (!auth._initialized) {
    auth.init();
    auth._initialized = true;

    if (auth.token && !auth.user) {
      try {
        await auth.fetchUser();
      } catch {}
    }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return next("/dashboard");
  }

  if (to.meta.auth && !auth.isAuthenticated) {
    console.warn(
      "Unauthorized access to protected route:",
      to.fullPath,
      "Redirecting to /login",
    );
    return next({ path: "/login", query: { redirect: to.fullPath } });
  }

  if (to.meta.verified && auth.isAuthenticated && !auth.isEmailVerified) {
    // Exempt OAuth users from email verification requirement
    const hasOAuthAccounts =
      auth.user?.oauth_accounts && auth.user.oauth_accounts.length > 0;

    if (!hasOAuthAccounts && to.path !== "/verify-email-notice") {
      return next("/verify-email-notice");
    }
  }

  // Removed featureKey guard to allow access to locked features with FeatureLockOverlay

  if (to.path.startsWith("/admin")) {
    const adminRoles = ["admin", "administrator", "moderator", "support"];
    const userRoles = auth.user?.roles || [];
    const hasAdminAccess = adminRoles.some((role) => userRoles.includes(role));

    if (!hasAdminAccess) {
      return next("/dashboard");
    }
  }

  return next();
});

export default router;
