import api from "@/services/api";

export default {
    getLedger(params) {
        return api.get("/admin/accounting/ledger", { params });
    },

    getInvoices(params) {
        return api.get("/admin/accounting/invoices", { params });
    },

    getStats() {
        return api.get("/admin/accounting/stats");
    },

    downloadExport(format = 'csv') {
        return api.get("/admin/accounting/export", {
            params: { format },
            responseType: 'blob'
        });
    }
};
