import apiClient from "./apiClient";

export const newsletterApi = {
  subscribe(email: string) {
    return apiClient.post("/v1/newsletter/subscribe", { email });
  },
};

export default newsletterApi;
