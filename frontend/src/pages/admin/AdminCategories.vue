<template>
  <div class="space-y-6 relative">
    <!-- LOADING OVERLAY (initial load only) -->
    <div
      v-if="isLoading && dbCategories.length === 0"
      class="relative min-h-[400px] flex items-center justify-center bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm"
    >
      <div class="flex flex-col items-center gap-3">
        <div
          class="animate-spin rounded-full h-10 w-10 border-t-4 border-b-4 border-primary-500"
        />
        <p class="text-gray-500 dark:text-gray-400 font-medium">
          Завантаження даних...
        </p>
      </div>
    </div>

    <!-- Content overlay when refreshing -->
    <div
      v-else
      :class="{ 'opacity-60 pointer-events-none': isLoading }"
      class="transition-opacity duration-200"
    >
      <CategoriesTab
        :categories="dbCategories"
        :attributes="dbAttributes"
        @refresh="fetchData"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";
import CategoriesTab from "@/components/admin/features/catalog/CategoriesTab.vue";

const isLoading = ref(false);
const dbCategories = ref([]);
const dbAttributes = ref([]);

const fetchData = async () => {
  isLoading.value = true;
  try {
    const [catsRes, attrsRes] = await Promise.all([
      api.get("/admin/categories"),
      api.get("/admin/attributes")
    ]);
    dbCategories.value = catsRes.data.data;
    dbAttributes.value = attrsRes.data.data;
  } catch (error) {
    console.error("Failed to load admin categories data:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchData();
});
</script>
