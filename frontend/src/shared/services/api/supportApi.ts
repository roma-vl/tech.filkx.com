import apiClient from "./apiClient";

export const supportApi = {
  getTickets() {
    return apiClient.get("/support/tickets");
  },

  createTicket(subject: string, message: string) {
    return apiClient.post("/support/tickets", { subject, message });
  },

  getTicket(id: number | string) {
    return apiClient.get(`/support/tickets/${id}`);
  },

  sendMessage(id: number | string, message: string) {
    return apiClient.post(`/support/tickets/${id}/message`, { message });
  },

  markAsRead(id: number | string) {
    return apiClient.post(`/support/tickets/${id}/mark-as-read`);
  },
};

export default supportApi;
