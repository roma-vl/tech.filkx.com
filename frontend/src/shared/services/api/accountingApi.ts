import apiClient from "./apiClient";

export const accountingApi = {
  getLedger(params?: Record<string, any>) {
    return apiClient.get("/admin/accounting/ledger", { params });
  },

  getInvoices(params?: Record<string, any>) {
    return apiClient.get("/admin/accounting/invoices", { params });
  },

  getStats() {
    return apiClient.get("/admin/accounting/stats");
  },

  downloadExport(format = "csv") {
    return apiClient.get("/admin/accounting/export", {
      params: { format },
      responseType: "blob",
    });
  },
};

export default accountingApi;
