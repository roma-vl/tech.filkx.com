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
        name: "Filkx - Premium Streaming",
        short_name: "Filkx",
        description:
          "Experience high-quality streaming and digital content on Filkx.",
        theme_color: "#4f46e5",
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
        maximumFileSizeToCacheInBytes: 5 * 1024 * 1024,
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
