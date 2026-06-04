import apiClient from "./apiClient";

export interface RegisterDto {
  name: string;
  email: string;
  password?: string;
  passwordConfirmation?: string;
  [key: string]: any;
}

export interface LoginDto {
  email: string;
  password?: string;
  [key: string]: any;
}

export interface ResetPasswordDto {
  token: string;
  email: string;
  password?: string;
  passwordConfirmation?: string;
  [key: string]: any;
}

export const authApi = {
  register(data: RegisterDto) {
    return apiClient.post("/v1/auth/register", data);
  },

  login(credentials: LoginDto) {
    return apiClient.post("/v1/auth/login", credentials);
  },

  logout() {
    return apiClient.post("/v1/auth/logout");
  },

  me() {
    return apiClient.get("/v1/auth/me");
  },

  refreshToken() {
    return apiClient.post("/v1/auth/refresh");
  },

  verifyEmail(params: Record<string, any>) {
    return apiClient.get("/v1/auth/verify-email", { params });
  },

  resendVerification(email: string) {
    return apiClient.post("/v1/auth/email/resend", { email });
  },

  forgotPassword(email: string) {
    return apiClient.post("/v1/auth/password/forgot", { email });
  },

  resetPassword(data: ResetPasswordDto) {
    return apiClient.post("/v1/auth/password/reset", data);
  },

  updateLocale(locale: string) {
    return apiClient.post("/user/locale", { locale });
  },

  impersonate(userId: number | string) {
    return apiClient.post(`/admin/users/${userId}/impersonate`);
  },

  leaveImpersonation() {
    return apiClient.post("/impersonation/leave");
  },

  getSessions() {
    return apiClient.get("/user/sessions");
  },

  revokeSession(id: string) {
    return apiClient.delete(`/user/sessions/${id}`);
  },

  updateNotificationPreferences(preferences: Record<string, any>) {
    return apiClient.put("/user/notification-preferences", preferences);
  },

  updateProfile(profile: Record<string, any>) {
    return apiClient.put("/user/profile", profile);
  },

  updatePassword(data: Record<string, any>) {
    return apiClient.put("/user/password", data);
  },

  setPassword(data: Record<string, any>) {
    return apiClient.post("/user/password/set", data);
  },

  uploadAvatar(formData: FormData) {
    return apiClient.post("/user/avatar", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
  },

  getNotifications(params?: Record<string, any>) {
    return apiClient.get("/notifications", { params });
  },

  markNotificationsRead(ids?: (string | number)[]) {
    return apiClient.post("/notifications/read", { ids });
  },

  deleteAccount() {
    return apiClient.delete("/user/account");
  },

  // Favorites endpoints
  getFavorites() {
    return apiClient.get("/user/favorites");
  },
  toggleFavorite(productId: number | string) {
    return apiClient.post("/user/favorites/toggle", { product_id: productId });
  },
  syncFavorites(productIds: (number | string)[]) {
    return apiClient.post("/user/favorites/sync", { product_ids: productIds });
  },

  // Compare endpoints
  getCompares() {
    return apiClient.get("/user/compares");
  },
  toggleCompare(productId: number | string) {
    return apiClient.post("/user/compares/toggle", { product_id: productId });
  },
  syncCompares(productIds: (number | string)[]) {
    return apiClient.post("/user/compares/sync", { product_ids: productIds });
  },

  // Viewed products history endpoints
  getViewedProducts() {
    return apiClient.get("/user/viewed-products");
  },
  trackViewedProduct(productId: number | string) {
    return apiClient.post("/user/viewed-products/track", { product_id: productId });
  },
  syncViewedProducts(items: { id: number | string; viewCount?: number; lastViewedAt?: string }[]) {
    const mappedItems = items.map(item => ({
      id: item.id,
      view_count: (item as any).viewCount ?? 1,
      last_viewed_at: (item as any).lastViewedAt ?? new Date().toISOString()
    }));
    return apiClient.post("/user/viewed-products/sync", { items: mappedItems });
  },
  clearViewedProducts() {
    return apiClient.delete("/user/viewed-products");
  },
};

export default authApi;
