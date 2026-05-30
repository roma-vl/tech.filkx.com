import { defineStore } from "pinia";
import api from "@/services/api";

export const useRunnerNodesStore = defineStore("admin-runner-nodes", {
  state: () => ({
    nodes: [],
    loading: false,
  }),

  actions: {
    async fetchNodes() {
      this.loading = true;
      try {
        const { data } = await api.get("/admin/runner-nodes");
        this.nodes = data.data;
      } finally {
        this.loading = false;
      }
    },

    async addNode(nodeData) {
      const { data } = await api.post("/admin/runner-nodes", nodeData);
      this.nodes.unshift(data.data);
      return data.data;
    },

    async updateNode(id, nodeData) {
      const { data } = await api.put(`/admin/runner-nodes/${id}`, nodeData);
      const index = this.nodes.findIndex((n) => n.id === id);
      if (index !== -1) {
        this.nodes[index] = data.data;
      }
      return data.data;
    },

    async deleteNode(id) {
      await api.delete(`/admin/runner-nodes/${id}`);
      this.nodes = this.nodes.filter((n) => n.id !== id);
    },

    async toggleNode(id) {
      const { data } = await api.post(`/admin/runner-nodes/${id}/toggle`);
      const index = this.nodes.findIndex((n) => n.id === id);
      if (index !== -1) {
        this.nodes[index] = data.data;
      }
      return data.data;
    },

    async pingNode(id) {
      try {
        const { data } = await api.post(`/admin/runner-nodes/${id}/ping`);
        const index = this.nodes.findIndex((n) => n.id === id);
        if (index !== -1) {
          this.nodes[index] = data.data;
        }
        return data.data;
      } catch (error) {
        const nodeData = error.response?.data?.data;
        if (nodeData) {
          const index = this.nodes.findIndex((n) => n.id === id);
          if (index !== -1) {
            this.nodes[index] = {
              ...this.nodes[index],
              ...nodeData,
              status: "offline",
            };
          }
        }
        throw error;
      }
    },
  },
});
