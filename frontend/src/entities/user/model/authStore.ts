import { defineStore } from "pinia";
import { authApi, RegisterDto, LoginDto, ResetPasswordDto } from "@/shared/services/api/authApi";
import { User } from "../types";
import { useCartStore } from "@/entities/order/model/cartStore";

const TOKEN_KEY = "filkx_auth";
const ADMIN_TOKEN_KEY = "filkx_admin_auth";
const TOKEN_EXPIRES_KEY = "token_expires_at";
const ADMIN_TOKEN_EXPIRES_KEY = "admin_token_expires_at";

export interface AuthState {
  token: string | null;
  user: User | null;
  tokenExpiresAt: number | null;
  adminToken: string | null;
  adminTokenExpiresAt: number | null;
  failedAttempts: number;
  _initialized: boolean;
}

export const useAuthStore = defineStore("auth", {
  state: (): AuthState => ({
    token: null,
    user: null,
    tokenExpiresAt: null,
    adminToken: null,
    adminTokenExpiresAt: null,
    failedAttempts: 0,
    _initialized: false,
  }),

  getters: {
    isAuthenticated: (state): boolean => {
      return !!state.token && !!state.user;
    },
    isEmailVerified: (state): boolean => !!state.user?.emailVerifiedAt,
    isImpersonating: (state): boolean => !!state.adminToken,
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

    setToken(accessToken: string, expiresIn: number) {
      this.token = accessToken;

      const expiresAt = Date.now() + expiresIn * 1000;
      this.tokenExpiresAt = expiresAt;

      localStorage.setItem(TOKEN_KEY, accessToken);
      localStorage.setItem(TOKEN_EXPIRES_KEY, expiresAt.toString());
    },

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

      const cartStore = useCartStore();
      cartStore.clearCart();
    },

    async updateLocale(locale: string) {
      try {
        await authApi.updateLocale(locale);
      } catch (e) {
        console.error("Failed to update locale on server", e);
      }

      if (this.user) {
        this.user.locale = locale;
      }

      this._syncLocale(locale);
    },

    async register(data: RegisterDto) {
      try {
        const response = await authApi.register(data);
        const { token, user } = response.data.data;

        this.setToken(token.accessToken, token.expiresIn);
        this.user = user;
        this._syncLocale(user.locale);

        const cartStore = useCartStore();
        cartStore.fetchCart();

        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },

    async login(credentials: LoginDto) {
      try {
        const response = await authApi.login(credentials);
        const { token, user } = response.data.data;

        this.setToken(token.accessToken, token.expiresIn);
        this.user = user;
        this._syncLocale(user.locale);

        this.failedAttempts = 0;
        localStorage.removeItem("failed_login_attempts");

        const cartStore = useCartStore();
        cartStore.fetchCart();

        return { ok: true };
      } catch (error) {
        this.failedAttempts = (this.failedAttempts || 0) + 1;
        localStorage.setItem("failed_login_attempts", this.failedAttempts.toString());

        return this._handleError(error);
      }
    },

    async impersonate(userId: number | string) {
      try {
        const response = await authApi.impersonate(userId);
        const { token, user } = response.data.data;

        this.adminToken = this.token;
        this.adminTokenExpiresAt = this.tokenExpiresAt;
        if (this.token) {
          localStorage.setItem(ADMIN_TOKEN_KEY, this.token);
        }
        if (this.tokenExpiresAt) {
          localStorage.setItem(ADMIN_TOKEN_EXPIRES_KEY, this.tokenExpiresAt.toString());
        }

        this.setToken(token.accessToken, token.expiresIn);
        this.user = user;

        return { ok: true };
      } catch (error) {
        console.error("Impersonation error:", error);
        return this._handleError(error);
      }
    },

    async leaveImpersonation() {
      try {
        await authApi.leaveImpersonation();
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

    async logout() {
      try {
        await authApi.logout();
      } catch (error) {
        console.error("Logout error:", error);
      } finally {
        this.clear();

        const cartStore = useCartStore();
        cartStore.clearCart();
      }
    },

    async fetchUser() {
      try {
        const response = await authApi.me();
        this.user = response.data.data;
        if (this.user?.locale) {
          this._syncLocale(this.user.locale);
        }
        return { ok: true };
      } catch (error: any) {
        console.warn("Fetch user failed, clearing session:", error.message);
        this.clear();
        return this._handleError(error);
      }
    },

    async refreshToken() {
      try {
        const response = await authApi.refreshToken();
        const { token } = response.data.data;

        this.setToken(token.accessToken, token.expiresIn);

        return { ok: true };
      } catch (error) {
        this.clear();
        return this._handleError(error);
      }
    },

    async verifyEmailByParams(params: Record<string, any>) {
      try {
        await authApi.verifyEmail(params);
        await this.fetchUser();
        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },

    async resendVerification(email: string) {
      try {
        await authApi.resendVerification(email);
        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },

    async forgotPassword(email: string) {
      try {
        await authApi.forgotPassword(email);
        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },

    async resetPassword(data: ResetPasswordDto) {
      try {
        await authApi.resetPassword(data);
        return { ok: true };
      } catch (error) {
        return this._handleError(error);
      }
    },

    _handleError(error: any) {
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

      const message = response?.data?.message || error?.message || "An error occurred";
      return { ok: false, error: message };
    },

    _syncLocale(locale: string) {
      if (!locale) return;

      import("@/i18n").then(({ i18n }) => {
        if (i18n.global.locale.value !== locale) {
          (i18n.global.locale.value as any) = locale;
        }

        if (typeof document !== "undefined") {
          document.documentElement.lang = locale;
        }
      });

      localStorage.setItem("locale", locale);
    },
  },
});
export default useAuthStore;
