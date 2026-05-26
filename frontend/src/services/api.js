import axios from "axios";
import {useAuthStore} from "@/stores/auth";
// Remove static router import to avoid SSR crash

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "/api",
  timeout: 60000,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

/**
 * Request interceptor — додає Bearer token
 */
api.interceptors.request.use(
  (config) => {
    try {
      const store = useAuthStore();

      if (store?.token) {
        config.headers.Authorization = `Bearer ${store.token}`;
      }
    } catch (error) {
      console.warn("Auth store not available");
    }

    return config;
  },
  (error) => Promise.reject(error),
);

/**
 * Response interceptor — обробка 401 і auto-refresh
 */
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config;
    const status = error?.response?.status;

    if (
      status === 401 &&
      !originalRequest._retry &&
      !originalRequest.url?.includes("/auth/refresh")
    ) {
      originalRequest._retry = true;

      try {
        const errorMsg = error?.response?.data?.message || error?.message;
        console.warn(
          "401 Unauthorized encountered:",
          errorMsg,
          "Url:",
          originalRequest.url,
        );

        const store = useAuthStore();

        const result = await store.refreshToken();

        if (result.ok) {
          originalRequest.headers.Authorization = `Bearer ${store.token}`;
          return api(originalRequest);
        } else {
          throw new Error("Token refresh failed");
        }
      } catch (refreshError) {
        console.error("Token refresh failed with exception:", refreshError);
        const store = useAuthStore();
        store.clear();
        if (typeof window !== "undefined") {
          const { default: router } = await import("@/router");
          router.push("/login");
        }
        return Promise.reject(refreshError);
      }
    }

    if (status === 403) {
      const message = error?.response?.data?.message;

      if (message?.includes("email") && message?.includes("verified")) {
        if (typeof window !== "undefined") {
          const { default: router } = await import("@/router");
          router.push("/verify-email-notice");
        }
      }
    }

    if (status === 503) {
      if (typeof window !== "undefined") {
        const { default: router } = await import("@/router");
        router.push("/maintenance");
      }
    }

    return Promise.reject(error);
  },
);

export default api;
