import { defineStore } from "pinia";
import axios from "@/services/api";

export const useSystemStore = defineStore("system", {
  state: () => ({
    maintenanceMode: false,
    version: "1.0.0",
    lastChecked: null,
  }),

  actions: {
    async checkStatus() {
      try {
        const { data } = await axios.get("/system/status");
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
