<template>
  <footer
    v-if="buildDate"
    class="text-xs text-gray-400"
  >
    Build: {{ version.version }} ({{ version.commit }}) —
    {{ buildDate }}
  </footer>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "@/services/api.js";

const version = ref({
  version: "dev",
  commit: null,
  timestamp: null,
});

const buildDate = computed(() => {
  if (!version.value.timestamp) return null;
  return new Date(Number(version.value.timestamp) * 1000).toLocaleString();
});

onMounted(async () => {
  try {
    const res = await api.get("/version");
    version.value = res.data.data.version;
  } catch (e) {
    console.error("Cannot fetch version", e);
  }
});
</script>
