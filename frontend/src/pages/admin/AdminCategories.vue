<template>
  <div class="space-y-6">
    <!-- LOADING OVERLAY -->
    <div
      v-if="isLoading"
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

    <div v-else>
      <CategoriesTab
        :categories="dbCategories"
        @refresh="fetchCategories"
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

const fetchCategories = async () => {
  isLoading.value = true;
  try {
    const catsRes = await api.get("/admin/categories");
    dbCategories.value = catsRes.data.data;
  } catch (error) {
    console.error("Failed to load categories:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchCategories();
});
</script>
