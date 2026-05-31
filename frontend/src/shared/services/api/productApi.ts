import apiClient from "./apiClient";

export const productApi = {
  // Public product APIs
  getProducts(params?: Record<string, any>) {
    return apiClient.get("/v1/products", { params });
  },

  getProduct(slugOrId: string | number) {
    return apiClient.get(`/v1/products/${slugOrId}`);
  },

  getCategories() {
    return apiClient.get("/v1/categories");
  },

  getBrands() {
    return apiClient.get("/v1/brands");
  },

  getAttributes() {
    return apiClient.get("/v1/attributes");
  },

  // Admin Catalog Management APIs
  adminGetProducts(params?: Record<string, any>) {
    return apiClient.get("/admin/products", { params });
  },

  adminCreateProduct(data: Record<string, any>) {
    return apiClient.post("/admin/products", data);
  },

  adminUpdateProduct(id: number | string, data: Record<string, any>) {
    return apiClient.put(`/admin/products/${id}`, data);
  },

  adminDeleteProduct(id: number | string) {
    return apiClient.delete(`/admin/products/${id}`);
  },

  adminImportProducts(formData: FormData) {
    return apiClient.post("/admin/products/import", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
  },

  adminGetCategories(params?: Record<string, any>) {
    return apiClient.get("/admin/categories", { params });
  },

  adminCreateCategory(data: Record<string, any>) {
    return apiClient.post("/admin/categories", data);
  },

  adminUpdateCategory(id: number | string, data: Record<string, any>) {
    return apiClient.put(`/admin/categories/${id}`, data);
  },

  adminDeleteCategory(id: number | string) {
    return apiClient.delete(`/admin/categories/${id}`);
  },

  adminGetBrands(params?: Record<string, any>) {
    return apiClient.get("/admin/brands", { params });
  },

  adminCreateBrand(data: Record<string, any>) {
    return apiClient.post("/admin/brands", data);
  },

  adminUpdateBrand(id: number | string, data: Record<string, any>) {
    return apiClient.put(`/admin/brands/${id}`, data);
  },

  adminDeleteBrand(id: number | string) {
    return apiClient.delete(`/admin/brands/${id}`);
  },

  adminGetAttributes(params?: Record<string, any>) {
    return apiClient.get("/admin/attributes", { params });
  },

  adminCreateAttribute(data: Record<string, any>) {
    return apiClient.post("/admin/attributes", data);
  },

  adminUpdateAttribute(id: number | string, data: Record<string, any>) {
    return apiClient.put(`/admin/attributes/${id}`, data);
  },

  adminDeleteAttribute(id: number | string) {
    return apiClient.delete(`/admin/attributes/${id}`);
  },
};

export default productApi;
