import { defineConfig } from "vitest/config";
import vue from "@vitejs/plugin-vue";
import { fileURLToPath, URL } from "node:url";

// Standalone from vite.config.js on purpose: the app's build config wires in
// VitePWA and an SSR-only alias swap, neither of which vitest needs to load
// to unit-test components/composables/stores.
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },
  test: {
    environment: "jsdom",
    globals: true,
    css: false,
    include: ["src/**/*.{test,spec}.{js,ts}"],
  },
});
