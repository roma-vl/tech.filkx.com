import apiClient from "./apiClient";

export const orderApi = {
  // Cart API
  getCart() {
    return apiClient.get("/v1/cart");
  },

  addToCart(variantId: number | string, quantity = 1) {
    return apiClient.post("/v1/cart", { variantId, quantity });
  },

  updateCartItem(id: number | string, quantity: number) {
    return apiClient.put(`/v1/cart/items/${id}`, { quantity });
  },

  removeFromCart(id: number | string) {
    return apiClient.delete(`/v1/cart/items/${id}`);
  },

  mergeCart(sessionId: string) {
    return apiClient.post("/v1/cart/merge", { sessionId });
  },

  // Checkout API
  validateCoupon(code: string, cartTotal: number) {
    return apiClient.post("/v1/coupons/validate", { code, cartTotal });
  },

  placeOrder(checkoutForm: Record<string, any>) {
    return apiClient.post("/v1/checkout", checkoutForm);
  },

  placeQuickOrder(customerName: string, customerPhone: string, variantId: number | string) {
    return apiClient.post("/v1/checkout/quick", { customerName, customerPhone, variantId });
  },

  // Admin Orders Management APIs
  adminGetOrders(params?: Record<string, any>) {
    return apiClient.get("/admin/orders", { params });
  },

  adminUpdateOrderStatus(id: number | string, status: string) {
    return apiClient.put(`/admin/orders/${id}/status`, { status });
  },

  // Admin Coupons Management APIs
  adminGetCoupons(params?: Record<string, any>) {
    return apiClient.get("/admin/coupons", { params });
  },

  adminCreateCoupon(data: Record<string, any>) {
    return apiClient.post("/admin/coupons", data);
  },

  adminUpdateCoupon(id: number | string, data: Record<string, any>) {
    return apiClient.put(`/admin/coupons/${id}`, data);
  },

  adminDeleteCoupon(id: number | string) {
    return apiClient.delete(`/admin/coupons/${id}`);
  },

  // Admin Promotions Management APIs
  adminGetPromotions(params?: Record<string, any>) {
    return apiClient.get("/admin/promotions", { params });
  },

  adminCreatePromotion(data: Record<string, any>) {
    return apiClient.post("/admin/promotions", data);
  },

  adminUpdatePromotion(id: number | string, data: Record<string, any>) {
    return apiClient.put(`/admin/promotions/${id}`, data);
  },

  adminDeletePromotion(id: number | string) {
    return apiClient.delete(`/admin/promotions/${id}`);
  },
};

export default orderApi;
