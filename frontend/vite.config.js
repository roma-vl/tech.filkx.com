import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import { fileURLToPath, URL } from "node:url";
import { VitePWA } from "vite-plugin-pwa";

export default defineConfig(({ mode, isSsrBuild }) => ({
  plugins: [
    vue(),
    VitePWA({
      registerType: "autoUpdate",
      injectRegister: "auto",
      includeAssets: ["favicon.ico", "apple-touch-icon.png"],
      manifest: {
        name: "FilkxTech",
        short_name: "FilkxTech",
        description:
          "Інтернет-магазин електроніки FilkxTech: каталог, кошик і швидке оформлення замовлення.",
        theme_color: "#00a046",
        background_color: "#030712",
        display: "standalone",
        start_url: "/",
        icons: [
          {
            src: "/icons/icon-192x192.png",
            sizes: "192x192",
            type: "image/png",
          },
          {
            src: "/icons/icon-512x512.png",
            sizes: "512x512",
            type: "image/png",
          },
          {
            src: "/icons/icon-512x512.png",
            sizes: "512x512",
            type: "image/png",
            purpose: "any maskable",
          },
        ],
      },
      workbox: {
        globPatterns: ["**/*.{js,css,ico,png,svg,json}"], // Removed html from precache
        // The whole app (storefront + admin) ships as one unsplit bundle (Vite already
        // warns "chunks larger than 500 kB" on every build) - it crossed the previous
        // 5 MB limit here as more admin/catalog features were added, breaking the build
        // outright (workbox refuses to precache an asset over the limit). Bumped with
        // headroom; the real fix is route-based code splitting (at minimum, keeping the
        // admin back-office out of the public storefront's bundle) - not done here, out
        // of scope for unblocking the build.
        maximumFileSizeToCacheInBytes: 8 * 1024 * 1024,
        cleanupOutdatedCaches: true,
        clientsClaim: true,
        skipWaiting: true,
        runtimeCaching: [
          {
            urlPattern: ({ request }) => request.mode === "navigate",
            handler: "NetworkFirst",
            options: {
              cacheName: "pages-cache",
              networkTimeoutSeconds: 5,
              cacheableResponse: {
                statuses: [0, 200],
              },
            },
          },
        ],
      },
    }),
  ],
  css: {
    postcss: "./postcss.config.cjs",
  },
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
      ...(isSsrBuild
        ? {
            "vue-toastification": fileURLToPath(
              new URL("./src/utils/toast-mock.js", import.meta.url),
            ),
          }
        : {}),
    },
  },
  build: {
    emptyOutDir: false,
  },
  server: {
    host: "0.0.0.0",
    port: 5173,
    allowedHosts: ["dev.tech.filkx.com", "dev.tech.filkx.com"],
    hmr: {
      host: "dev.tech.filkx.com",
      clientPort: 443,
      protocol: "wss",
    },
  },
}));
