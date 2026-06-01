<template>
  <footer
    v-if="buildDate"
    class="text-xs text-gray-400"
  >
    Build: {{ version.version }} ({{ version.commit }}) —
    {{ buildDate }}
  </footer>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import apiClient from "@/shared/services/api/apiClient";

interface VersionInfo {
  version: string;
  commit: string | null;
  timestamp: string | null;
}

const version = ref<VersionInfo>({
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
    const res = await apiClient.get("/version");
    version.value = res.data.data.version;
  } catch (e) {
    console.error("Cannot fetch version", e);
  }
});
</script>
