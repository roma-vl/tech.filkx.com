import { defineStore } from "pinia";
import api from "./services/api";

const TOKEN_KEY = "filkx_auth";
const ADMIN_TOKEN_KEY = "filkx_admin_auth";
const TOKEN_EXPIRES_KEY = "token_expires_at";
const ADMIN_TOKEN_EXPIRES_KEY = "admin_token_expires_at";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    token: null,
    user: null,
    tokenExpiresAt: null,
    adminToken: null,
    adminTokenExpiresAt: null,
    failedAttempts: 0,
    _initialized: false,
  }),

  getters: {
    isAuthenticated: (state) => {
      return !!state.token && !!state.user;
    },
    isEmailVerified: (state) => !!state.user?.emailVerifiedAt,
    isImpersonating: (state) => !!state.adminToken,
  },

  actions: {
    init() {
      const token = localStorage.getItem(TOKEN_KEY);
      const expiresAt = localStorage.getItem(TOKEN_EXPIRES_KEY);
      const adminToken = localStorage.getItem(ADMIN_TOKEN_KEY);
      const adminExpiresAt = localStorage.getItem(ADMIN_TOKEN_EXPIRES_KEY);

      if (token && expiresAt) {
        const now = Date.now();
        const expires = parseInt(expiresAt, 10);

        if (expires < now && !adminToken) {
          this.clear();
          return;
        }

        this.token = token;
        this.tokenExpiresAt = expires;
      }

      if (adminToken && adminExpiresAt) {
        this.adminToken = adminToken;
        this.adminTokenExpiresAt = parseInt(adminExpiresAt, 10);
      }

      const failedAttempts = localStorage.getItem("failed_login_attempts");
      if (failedAttempts) {
        this.failedAttempts = parseInt(failedAttempts, 10);
      }
    },

    /**
     * Save token to state + localStorage
     */
    setToken(accessToken, expiresIn) {
      this.token = accessToken;

      const expiresAt = Date.now() + expiresIn * 1000;
      this.tokenExpiresAt = expiresAt;

      localStorage.setItem(TOKEN_KEY, accessToken);
      localStorage.setItem(TOKEN_EXPIRES_KEY, expiresAt.toString());
    },

    /**
     * Clear auth state
     */
    clear() {
      this.token = null;
      this.user = null;
      this.tokenExpiresAt = null;
      this.adminToken = null;
      this.adminTokenExpiresAt = null;
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(TOKEN_EXPIRES_KEY);
      localStorage.removeItem(ADMIN_TOKEN_KEY);
      localStorage.removeItem(ADMIN_TOKEN_EXPIRES_KEY);
    },

    async updateLocale(locale) {
      // 1. Update API
      try {
        await api.post("/user/locale", { locale });
      } catch (e) {
        console.error("Failed to update locale on server", e);
      }

      // 2. Update Local State
      if (this.user) {
        this.user.locale = locale;
      }

      // 3. Sync Global State
      this._syncLocale(locale);
    },

    /**
     * Register new user
     */
    async register(data) {
      const subscription = useSubscriptionStore();
      try {
        const response = await api.post("/v1/auth/register", data);
        const { token, user } = response.data.data;

        this.setToken(token.accessToken, token.expiresIn);
        this.user = user;
        this._syncLocale(user.locale);
        await subscription.fetch();
        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },

    async login(credentials) {
      try {
        const response = await api.post("/v1/auth/login", credentials);
        const { token, user } = response.data.data;

        this.setToken(token.accessToken, token.expiresIn);
        this.user = user;
        this._syncLocale(user.locale);

        // Reset failed attempts on success
        this.failedAttempts = 0;
        localStorage.removeItem("failed_login_attempts");

        return { ok: true };
      } catch (error) {
        // Increment failed attempts
        this.failedAttempts = (this.failedAttempts || 0) + 1;
        localStorage.setItem("failed_login_attempts", this.failedAttempts.toString());

        return this._handleError(error);
      }
    },

    /**
     * Impersonate User
     */
    async impersonate(userId) {
      try {
        const response = await api.post(`/admin/users/${userId}/impersonate`);
        const { token, user } = response.data.data;

        this.adminToken = this.token;
        this.adminTokenExpiresAt = this.tokenExpiresAt;
        localStorage.setItem(ADMIN_TOKEN_KEY, this.token);
        localStorage.setItem(
          ADMIN_TOKEN_EXPIRES_KEY,
          this.tokenExpiresAt.toString(),
        );

        this.setToken(token.accessToken, token.expiresIn);
        this.user = user;

        return { ok: true };
      } catch (error) {
        console.error("Impersonation error:", error);
        return this._handleError(error);
      }
    },

    /**
     * Leave Impersonation
     */
    async leaveImpersonation() {
      try {
        await api.post("/impersonation/leave");
      } catch (error) {
        console.error("Leave impersonation error:", error);
      } finally {
        const adminToken = localStorage.getItem(ADMIN_TOKEN_KEY);
        const adminExpiresAt = localStorage.getItem(ADMIN_TOKEN_EXPIRES_KEY);

        this.clear();

        if (adminToken && adminExpiresAt) {
          this.setToken(adminToken, 0);
          this.tokenExpiresAt = parseInt(adminExpiresAt, 10);
          localStorage.setItem(TOKEN_EXPIRES_KEY, adminExpiresAt);
          this.adminToken = null;
          this.adminTokenExpiresAt = null;
        }

        await this.fetchUser();
      }
    },

    /**
     * Logout user
     */
    async logout() {
      try {
        await api.post("/v1/auth/logout");
      } catch (error) {
        console.error("Logout error:", error);
      } finally {
        this.clear();
      }
    },

    /**
     * Fetch current user
     */
    async fetchUser() {
      try {
        const response = await api.get("/user/me");
        this.user = response.data.data;
        if (this.user?.locale) {
          this._syncLocale(this.user.locale);
        }
        return { ok: true };
      } catch (error) {
        console.warn("Fetch user failed, clearing session:", error.message);
        this.clear();
        return this._handleError(error);
      }
    },

    /**
     * Refresh access token
     */
    async refreshToken() {
      try {
        const response = await api.post("/v1/auth/refresh");
        const { token } = response.data.data;

        this.setToken(token.accessToken, token.expiresIn);

        return { ok: true };
      } catch (error) {
        this.clear();
        return this._handleError(error);
      }
    },

    async verifyEmailByParams({ id, hash, expires, signature }) {
      try {
        await api.get("/v1/auth/verify-email-by-params", {
          params: { id, hash, expires, signature },
        });

        await this.fetchUser();
        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },
    /**
     * Resend verification email
     */
    async resendVerification(email) {
      try {
        await api.post("/v1/auth/resend-verification", { email });
        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },

    /**
     * Send password reset link
     */
    async forgotPassword(email) {
      try {
        await api.post("/v1/auth/forgot-password", { email });
        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },

    /**
     * Reset password
     */
    async resetPassword(data) {
      try {
        await api.post("/v1/auth/reset-password", data);
        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },

    /**
     * Handle API errors with validation support
     */
    _handleError(error) {
      const response = error?.response;

      if (response?.status === 422) {
        const errors = response.data?.errors || {};
        const message = response.data?.message || "Validation failed";

        return {
          ok: false,
          error: message,
          errors: errors,
        };
      }

      const message =
        response?.data?.message || error?.message || "An error occurred";
      return { ok: false, error: message };
    },

    /**
     * Internal helper to synchronize global state with a given locale
     */
    _syncLocale(locale) {
      if (!locale) return;

      // 1. Update Global i18n
      import("@/i18n").then(({ i18n }) => {
        if (i18n.global.locale.value !== locale) {
          i18n.global.locale.value = locale;
        }

        if (typeof document !== "undefined") {
          document.documentElement.lang = locale;
        }
      });

      // 2. Persist to localStorage
      localStorage.setItem("locale", locale);
    },
  },
});
