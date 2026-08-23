<template>
  <div class="space-y-6">
    <!-- LOADING OVERLAY (categories/brands/attributes only - ProductsTab
         fetches its own paginated product list independently) -->
    <div
      v-if="isLoading"
      class="relative min-h-[400px] flex items-center justify-center bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm"
    >
      <div class="flex flex-col items-center gap-3">
        <div
          class="animate-spin rounded-full h-10 w-10 border-t-4 border-b-4 border-primary-500"
        />
        <p class="text-gray-500 dark:text-gray-400 font-medium">
          {{ t("admin.common.loadingData") }}
        </p>
      </div>
    </div>

    <ProductsTab
      v-else
      :categories="dbCategories"
      :brands="dbBrands"
      :attributes="dbAttributes"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/shared/services/api/apiClient";
import ProductsTab from "@/components/admin/features/catalog/ProductsTab.vue";

const { t } = useI18n();
const isLoading = ref(true);
const dbCategories = ref([]);
const dbBrands = ref([]);
const dbAttributes = ref([]);

const fetchAllData = async () => {
  isLoading.value = true;
  try {
    const [catsRes, brandsRes, attrsRes] = await Promise.all([
      api.get("/admin/categories"),
      api.get("/admin/brands"),
      api.get("/admin/attributes"),
    ]);
    dbCategories.value = catsRes.data.data;
    dbBrands.value = brandsRes.data.data;
    dbAttributes.value = attrsRes.data.data;
  } catch (error) {
    console.error("Failed to load catalog data:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchAllData();
});
</script>
