import { defineStore } from "pinia";
import api from "@/shared/services/api/apiClient";

export const useRunnerTranscodersStore = defineStore("admin-transcoder-nodes", {
  state: () => ({
    nodes: [],
    loading: false,
  }),

  actions: {
    async fetchNodes() {
      this.loading = true;
      try {
        const { data } = await api.get("/admin/transcoder-nodes");
        this.nodes = data.data;
      } finally {
        this.loading = false;
      }
    },

    async addNode(nodeData) {
      const { data } = await api.post("/admin/transcoder-nodes", nodeData);
      this.nodes.unshift(data.data);
      return data.data;
    },

    async updateNode(id, nodeData) {
      const { data } = await api.put(`/admin/transcoder-nodes/${id}`, nodeData);
      const index = this.nodes.findIndex((n) => n.id === id);
      if (index !== -1) {
        this.nodes[index] = data.data;
      }
      return data.data;
    },

    async deleteNode(id) {
      await api.delete(`/admin/transcoder-nodes/${id}`);
      this.nodes = this.nodes.filter((n) => n.id !== id);
    },

    async toggleNode(id) {
      const { data } = await api.post(`/admin/transcoder-nodes/${id}/toggle`);
      const index = this.nodes.findIndex((n) => n.id === id);
      if (index !== -1) {
        this.nodes[index] = data.data;
      }
      return data.data;
    },

    async pingNode(id) {
      try {
        const { data } = await api.post(`/admin/transcoder-nodes/${id}/ping`);
        const index = this.nodes.findIndex((n) => n.id === id);
        if (index !== -1) {
          this.nodes[index] = data.data;
        }
        return data.data;
      } catch (error) {
        // Backend returns 400 when node is unreachable but still sends back
        // the node data (with updated last_ping_at etc.) — update the card.
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
        // Re-throw so AdminTranscoderNodes.vue can show the error toast.
        throw error;
      }
    },
  },
});
