<template>
  <div class="space-y-6">
    <!-- LOADING OVERLAY (initial load only - refreshes keep ProductsTab mounted) -->
    <div
      v-if="isInitialLoading"
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
      :products="dbProducts"
      :categories="dbCategories"
      :brands="dbBrands"
      :attributes="dbAttributes"
      @refresh="fetchProducts"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/shared/services/api/apiClient";
import ProductsTab from "@/components/admin/features/catalog/ProductsTab.vue";

const { t } = useI18n();
const isInitialLoading = ref(true);
const dbProducts = ref([]);
const dbCategories = ref([]);
const dbBrands = ref([]);
const dbAttributes = ref([]);

// Products change far more often than categories/brands/attributes (every
// delete, status change, etc.) - refetching only the list keeps ProductsTab
// mounted across a refresh instead of losing its local pagination/selection
// state, which is what used to reset the admin back to page 1.
const fetchProducts = async () => {
  try {
    const productsRes = await api.get("/admin/products");
    dbProducts.value = productsRes.data.data;
  } catch (error) {
    console.error("Failed to load products:", error);
  }
};

const fetchAllData = async () => {
  isInitialLoading.value = true;
  try {
    const [productsRes, catsRes, brandsRes, attrsRes] = await Promise.all([
      api.get("/admin/products"),
      api.get("/admin/categories"),
      api.get("/admin/brands"),
      api.get("/admin/attributes"),
    ]);
    dbProducts.value = productsRes.data.data;
    dbCategories.value = catsRes.data.data;
    dbBrands.value = brandsRes.data.data;
    dbAttributes.value = attrsRes.data.data;
  } catch (error) {
    console.error("Failed to load catalog data:", error);
  } finally {
    isInitialLoading.value = false;
  }
};

onMounted(() => {
  fetchAllData();
});
</script>
