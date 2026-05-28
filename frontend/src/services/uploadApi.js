import axios from "axios";
import { useAuthStore } from "@/stores/auth.js";

const uploadApi = axios.create({
  baseURL: import.meta.env.VITE_UPLOAD_BASE_URL,
  timeout: 0,
  maxContentLength: Infinity,
  maxBodyLength: Infinity,
});

uploadApi.interceptors.request.use(
  (config) => {
    try {
      const store = useAuthStore();
      if (store?.token) {
        config.headers.Authorization = `Bearer ${store.token}`;
      }
    } catch (e) {}
    return config;
  },
  (err) => Promise.reject(err),
);

uploadApi.interceptors.response.use(
  (r) => r,
  async (error) => {
    const status = error?.response?.status;
    const store = useAuthStore();
    if (status === 401 && error.config.url !== "/user") {
      store.clear();
    }

    return Promise.reject(error);
  },
);

export default uploadApi;
