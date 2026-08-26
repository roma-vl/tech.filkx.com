import apiClient from "./apiClient";

export const deliveryApi = {
  getAvailability() {
    return apiClient.get("/v1/delivery/availability");
  },

  searchCities(query: string) {
    return apiClient.get("/v1/delivery/cities", { params: { query } });
  },

  searchWarehouses(cityRef: string, query?: string) {
    return apiClient.get("/v1/delivery/warehouses", {
      params: { cityRef, query: query || undefined },
    });
  },

  getEstimate(cityRef: string) {
    return apiClient.get("/v1/delivery/estimate", { params: { cityRef } });
  },
};

export default deliveryApi;
