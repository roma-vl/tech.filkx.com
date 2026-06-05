import { defineStore } from "pinia";
import apiClient from "@/shared/services/api/apiClient";

export interface SystemState {
  maintenanceMode: boolean;
  version: string;
  lastChecked: Date | null;
}

export const useSystemStore = defineStore("system", {
  state: (): SystemState => ({
    maintenanceMode: false,
    version: "1.0.0",
    lastChecked: null,
  }),

  actions: {
    async checkStatus() {
      try {
        const { data } = await apiClient.get("/system/status");
        this.maintenanceMode = data.maintenance_mode;
        this.version = data.version;
        this.lastChecked = new Date();
        return data.maintenance_mode;
      } catch (error) {
        console.error("Failed to check system status", error);
        return false;
      }
    },
  },
});
export default useSystemStore;
