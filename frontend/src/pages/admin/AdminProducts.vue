<template>
  <div class="space-y-6">
    <!-- Tabs Header -->
    <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="activeTab === tab.id 
          ? 'bg-primary text-white dark:bg-white dark:text-primary font-bold shadow-md' 
          : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
        class="px-5 py-2.5 rounded-xl text-sm transition-all duration-300 flex items-center gap-2"
      >
        <span v-html="tab.icon"></span>
        {{ tab.name }}
      </button>
    </div>

    <!-- LOADING OVERLAY -->
    <div v-if="isLoading" class="relative min-h-[400px] flex items-center justify-center bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
      <div class="flex flex-col items-center gap-3">
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-b-4 border-primary-500"></div>
        <p class="text-gray-500 dark:text-gray-400 font-medium">Завантаження даних...</p>
      </div>
    </div>

    <div v-else>
      <!-- TAB 1: PRODUCTS -->
      <ProductsTab
        v-if="activeTab === 'products'"
        :products="dbProducts"
        :categories="dbCategories"
        :brands="dbBrands"
        :attributes="dbAttributes"
        @refresh="fetchAllData"
      />

      <!-- TAB 2: CATEGORIES -->
      <CategoriesTab
        v-if="activeTab === 'categories'"
        :categories="dbCategories"
        @refresh="fetchAllData"
      />

      <!-- TAB 3: BRANDS -->
      <BrandsTab
        v-if="activeTab === 'brands'"
        :brands="dbBrands"
        @refresh="fetchAllData"
      />

      <!-- TAB 4: ATTRIBUTES -->
      <AttributesTab
        v-if="activeTab === 'attributes'"
        :attributes="dbAttributes"
        @refresh="fetchAllData"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';

import ProductsTab from '@/components/admin/features/catalog/ProductsTab.vue';
import CategoriesTab from '@/components/admin/features/catalog/CategoriesTab.vue';
import BrandsTab from '@/components/admin/features/catalog/BrandsTab.vue';
import AttributesTab from '@/components/admin/features/catalog/AttributesTab.vue';

const tabs = [
  { id: 'products', name: 'Товари', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>' },
  { id: 'categories', name: 'Категорії', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>' },
  { id: 'brands', name: 'Бренди', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>' },
  { id: 'attributes', name: 'Характеристики', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>' }
];

const activeTab = ref('products');
const isLoading = ref(false);

const dbProducts = ref([]);
const dbCategories = ref([]);
const dbBrands = ref([]);
const dbAttributes = ref([]);

const fetchAllData = async () => {
  isLoading.value = true;
  try {
    const productsRes = await api.get('/admin/products');
    dbProducts.value = productsRes.data.data;

    const catsRes = await api.get('/admin/categories');
    dbCategories.value = catsRes.data.data;

    const brandsRes = await api.get('/admin/brands');
    dbBrands.value = brandsRes.data.data;

    const attrsRes = await api.get('/admin/attributes');
    dbAttributes.value = attrsRes.data.data;
  } catch (error) {
    console.error('Failed to load catalog data:', error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchAllData();
});
</script>
