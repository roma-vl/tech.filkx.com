import axios from "axios";
import { useAuthStore } from "@/stores/auth";

const apiClient = axios.create({
  baseURL: (import.meta.env.VITE_API_BASE_URL as string) || "/api",
  timeout: 60000,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

/**
 * Request interceptor — adds Bearer token and Cart Session ID
 */
apiClient.interceptors.request.use(
  (config) => {
    try {
      const store = useAuthStore();

      if (store?.token) {
        config.headers.Authorization = `Bearer ${store.token}`;
      }

      if (typeof window !== "undefined") {
        let cartSessionId = localStorage.getItem("cart_session_id");
        if (!cartSessionId) {
          cartSessionId =
            "anon_" +
            Math.random().toString(36).substring(2, 15) +
            Math.random().toString(36).substring(2, 15);
          localStorage.setItem("cart_session_id", cartSessionId);
        }
        config.headers["X-Cart-Session-ID"] = cartSessionId;
      }
    } catch (error) {
      console.warn("Auth / localStorage not available in interceptor");
    }

    return config;
  },
  (error) => Promise.reject(error)
);

/**
 * Response interceptor — handles 401 token refresh, 403, and 503 errors
 */
apiClient.interceptors.response.use(
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
          originalRequest.url
        );

        const store = useAuthStore();
        const result = await store.refreshToken();

        if (result.ok) {
          originalRequest.headers.Authorization = `Bearer ${store.token}`;
          return apiClient(originalRequest);
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
  }
);

export default apiClient;
