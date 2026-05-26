import { createApp as createVueApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import { i18n } from "@/i18n";
import { useAuthStore } from "@/stores/auth";
import { useUiStore } from "@/stores/ui.js";
import { createHead } from "@vueuse/head";
import router from "@/router";
import "@/assets/style.css";
import { VueReCaptcha } from "vue-recaptcha-v3";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";

// Export a factory function for creating the app instance
export function createApp(routerInstance) {
  const app = createVueApp(App);
  const pinia = createPinia();
  const head = createHead();

  app.use(pinia);
  app.use(routerInstance);
  app.use(i18n);
  app.use(head);
  app.use(Toast, {
    position: "top-right",
    timeout: 3000,
    closeOnClick: true,
    pauseOnFocusLoss: true,
    pauseOnHover: true,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: true,
    rtl: false,
  });

  // Initialize reCAPTCHA
  if (import.meta.env.VITE_RECAPTCHA_SITE_KEY) {
    app.use(VueReCaptcha, {
      siteKey: import.meta.env.VITE_RECAPTCHA_SITE_KEY,
      loaderOptions: {
        autoHideBadge: true,
      },
    });
  }

  // Initialize stores BUT DO NOT CALL INIT methods that touch DOM/LocalStorage here
  const auth = useAuthStore(pinia);
  const subscription = useSubscriptionStore(pinia);
  const ui = useUiStore(pinia);

  // Return the instance components
  return { app, router: routerInstance, pinia, head, auth, subscription, ui };
}

// Client-side entry point
if (typeof window !== "undefined") {
  const { app } = createApp(router);
  app.mount("#app");
}
