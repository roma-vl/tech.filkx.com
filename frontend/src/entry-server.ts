// Minimal SSR entry used only by scripts/prerender.mjs (build-time static
// prerendering — there is no server.js / long-running SSR process, see
// docs/PROJECT_MAP.md "SSR"). Deliberately mirrors only the parts of
// main.js's createApp() that are needed to *render* a page: pinia, router,
// i18n, @vueuse/head. Sentry, reCAPTCHA and vue-toastification are
// client-interactivity concerns that don't affect prerendered markup and
// each touches browser globals (or is a CJS module vite-node can't SSR
// import cleanly) at plugin-install time, so they are skipped here rather
// than guarded.
import { createApp } from "vue";
import { createPinia } from "pinia";
import { createHead } from "@vueuse/head";
import {
  createRouter,
  createMemoryHistory,
  type RouteRecordRaw,
} from "vue-router";
import App from "./App.vue";
import { i18n } from "@/i18n";
import MainLayout from "@/layouts/main/MainLayout.vue";
import HomePage from "@/pages/home/HomePage.vue";
import CatalogPage from "@/pages/catalog/CatalogPage.vue";
import ProductDetailPage from "@/pages/product/ProductDetailPage.vue";
import BlogPage from "@/pages/blog/BlogPage.vue";
import BlogPostPage from "@/pages/blog/BlogPostPage.vue";

// Intentionally only the storefront routes this prerender pipeline actually
// renders (see scripts/prerender.mjs) — NOT the full app route table
// (@/router/routes), which also pulls in admin/auth pages. Those statically
// import vue-toastification (a CJS module Vite's SSR module runner can't
// import as named exports) and other browser-only admin widgets that have
// no bearing on the public, crawlable pages this script produces.
const routes: RouteRecordRaw[] = [
  {
    path: "/",
    component: MainLayout,
    children: [
      { path: "", name: "home", component: HomePage },
      { path: "catalog", name: "catalog", component: CatalogPage },
      { path: "category/:slug", name: "category", component: CatalogPage },
      {
        path: "product/:id",
        name: "product-detail",
        component: ProductDetailPage,
      },
      { path: "blog", name: "blog", component: BlogPage },
      { path: "blog/:slug", name: "blog-post", component: BlogPostPage },
    ],
  },
];

export async function renderPage(url: string) {
  const app = createApp(App);
  const pinia = createPinia();
  const head = createHead();

  // A fresh, unguarded router (no beforeEach auth/maintenance/cart-sync
  // guard from @/router/index.js) — that guard reads localStorage and hits
  // the API on every navigation, which is unnecessary for a static SEO
  // snapshot and unsafe outside a browser (see
  // @/entities/user/model/authStore.ts init()).
  const router = createRouter({
    history: createMemoryHistory(),
    routes,
  });

  app.use(pinia);
  app.use(router);
  app.use(i18n);
  app.use(head);

  router.push(url);
  await router.isReady();

  return { app, router, head };
}
