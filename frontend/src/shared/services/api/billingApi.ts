import apiClient from "./apiClient";

export const billingApi = {
  getStats() {
    return apiClient.get("/admin/billing/stats");
  },

  getPendingPayments() {
    return apiClient.get("/admin/billing/payments/pending");
  },

  getPaymentProof(id: number | string) {
    return apiClient.get(`/admin/billing/payments/${id}/proof`);
  },

  confirmPayment(
    id: number | string,
    data: { approve: boolean; reason?: string },
  ) {
    return apiClient.post(`/admin/billing/payments/${id}/confirm`, data);
  },
};

export default billingApi;
