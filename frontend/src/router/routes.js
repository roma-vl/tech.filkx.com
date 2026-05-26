// Page Components
import NotFoundPage from "@/pages/errors/NotFoundPage.vue";
import MaintenancePage from "@/pages/errors/MaintenancePage.vue";

// Route Modules
// import authRoutes from "./routes/auth";
import appRoutes from "./routes/application";
// import adminRoutes from "./routes/admin";
import ApplicationLayoutWrapper from "@/pages/application/ApplicationLayoutWrapper.vue";

// Add redirects for /auth/login -> /login etc.
const authRedirects = [
  { path: "/auth/login", redirect: "/login" },
  { path: "/auth/register", redirect: "/register" },
  { path: "/auth/forgot-password", redirect: "/forgot-password" },
  { path: "/auth/reset-password", redirect: "/reset-password" },
];

export const routes = [
  {
    path: "/",
  },
  // ...authRedirects,

  {
    path: "/maintenance",
    name: "maintenance",
    component: MaintenancePage,
    meta: { auth: false },
  },

  // ...authRoutes,
  // adminRoutes,

  // 404
  {
    path: "/:pathMatch(.*)*",
    component: NotFoundPage,
  },
];
