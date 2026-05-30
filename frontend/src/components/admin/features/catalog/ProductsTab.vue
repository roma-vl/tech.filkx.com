<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="space-y-4">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-1 items-center gap-3">
          <div class="flex-1 max-w-md">
            <AppInput
              v-model="productSearch"
              placeholder="Пошук товарів за назвою чи SKU..."
            >
              <template #prepend>
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </template>
            </AppInput>
          </div>
          
          <AppButton
            variant="secondary"
            class="!p-2.5 relative"
            :class="{
              'ring-2 ring-primary-500 !bg-primary-50 dark:!bg-primary-900/20 !border-primary-200 dark:!border-primary-800':
                showFilters,
            }"
            title="Фільтри"
            @click="showFilters = !showFilters"
          >
            <svg class="w-5 h-5 transition-colors" :class="showFilters ? 'text-primary-600' : 'text-gray-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span
              v-if="activeFiltersCount > 0"
              class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-[10px] flex items-center justify-center rounded-full font-black shadow-lg shadow-primary-500/30 ring-2 ring-white dark:ring-gray-800"
            >
              {{ activeFiltersCount }}
            </span>
          </AppButton>
        </div>

        <div class="flex items-center gap-3">
          <AppButton
            variant="secondary"
            @click="showImportModal = true"
            class="flex items-center gap-2 shrink-0 h-[46px]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
            </svg>
            Імпорт CSV
          </AppButton>

          <AppButton
            variant="secondary"
            @click="exportCsv"
            class="flex items-center gap-2 shrink-0 h-[46px]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Експорт CSV
          </AppButton>

          <AppButton
            @click="openAddProductModal"
            variant="primary"
            class="flex items-center gap-2 shrink-0 h-[46px]"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Додати товар
          </AppButton>
        </div>
      </div>

      <!-- Toggleable Filters Panel -->
      <transition name="expand">
        <div
          v-if="showFilters"
          class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl space-y-6 animate-in slide-in-from-top-2 duration-300"
        >
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <AppSelect
              v-model="productCategoryFilter"
              label="Категорія"
              placeholder="Усі категорії"
              :options="[{ id: '', nameUk: 'Усі категорії' }, ...categories]"
              option-value="id"
              option-label="nameUk"
            />

            <AppSelect
              v-model="productBrandFilter"
              label="Бренд"
              placeholder="Усі бренди"
              :options="[{ id: '', name: 'Усі бренди' }, ...brands]"
              option-value="id"
              option-label="name"
            />

            <AppSelect
              v-model="productStatusFilter"
              label="Статус"
              placeholder="Усі статуси"
              :options="[
                { id: '', name: 'Усі статуси' },
                { id: 'active', name: 'Активний' },
                { id: 'draft', name: 'Чернетка' },
                { id: 'hidden', name: 'Прихований' }
              ]"
              option-value="id"
              option-label="name"
            />

            <AppSelect
              v-model="productSortFilter"
              label="Сортування"
              placeholder="Сортування за замовчуванням"
              :options="[
                { id: 'name-asc', name: 'Назва (А-Я)' },
                { id: 'name-desc', name: 'Назва (Я-А)' },
                { id: 'price-asc', name: 'Ціна (Від дешевих)' },
                { id: 'price-desc', name: 'Ціна (Від дорогих)' },
                { id: 'stock-desc', name: 'Наявність (Спочатку багато)' },
                { id: 'stock-asc', name: 'Наявність (Спочатку мало)' }
              ]"
              option-value="id"
              option-label="name"
            />
          </div>

          <div class="flex items-center justify-between pt-6 border-t border-gray-150 dark:border-gray-700">
            <div class="flex gap-6">
              <!-- Checkboxes / Toggles for promo filters -->
              <label class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase cursor-pointer select-none">
                <input type="checkbox" v-model="productHotFilter" class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-750 dark:border-gray-650" />
                Гарячі 🔥
              </label>

              <label class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase cursor-pointer select-none">
                <input type="checkbox" v-model="productRecommendedFilter" class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-750 dark:border-gray-650" />
                Рекомендовані 👍
              </label>
            </div>

            <AppButton
              variant="text"
              class="!text-red-500 hover:!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20 !px-4 !py-2 !rounded-xl font-bold"
              @click="resetFilters"
            >
              Скинути фільтри
            </AppButton>
          </div>
        </div>
      </transition>
    </div>

    <!-- Products Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Товар</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Категорія</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Бренд</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Варіанти (Ціна / Залишок)</th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
              <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дії</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <img :src="product.image" alt="" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 bg-gray-100" />
                  <div>
                    <div class="flex items-center gap-1.5">
                      <div class="font-bold text-gray-900 dark:text-white">{{ product.nameUk }}</div>
                      <span v-if="product.isHot" title="Гаряча пропозиція" class="text-xs">🔥</span>
                      <span v-if="product.isRecommended" title="Рекомендовано" class="text-xs">👍</span>
                    </div>
                    <div class="text-xs text-gray-400">{{ product.nameEn }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                {{ product.categoryName || 'Без категорії' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                {{ product.brandName || 'Без бренду' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="space-y-1">
                  <div v-for="v in product.variants" :key="v.id" class="text-xs text-gray-700 dark:text-gray-300">
                    <span class="font-mono bg-gray-100 dark:bg-gray-950 px-1 py-0.5 rounded text-[10px]">{{ v.sku }}</span>:
                    <span class="font-bold text-gray-900 dark:text-white">{{ formatPrice(v.price) }}</span>
                    <span class="text-gray-400"> ({{ v.stock }} шт)</span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="{
                  'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400': product.status === 'active',
                  'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': product.status === 'draft',
                  'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400': product.status === 'hidden'
                }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider">
                  {{ product.status === 'active' ? 'Активний' : product.status === 'draft' ? 'Чернетка' : 'Прихований' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <AppButton @click="openEditProductModal(product)" variant="ghost" size="sm" class="!p-2 text-blue-600 dark:text-blue-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </AppButton>
                  <AppButton @click="deleteProduct(product.id)" variant="ghost" size="sm" class="!p-2 text-red-600 dark:text-red-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </AppButton>
                </div>
              </td>
            </tr>
            <tr v-if="filteredProducts.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                Товарів не знайдено за вашим запитом.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Product Edit/Create Modal Component -->
    <ProductFormModal
      v-model="showProductModal"
      :product="editingProduct"
      :categories="categories"
      :brands="brands"
      :attributes="attributes"
      @refresh="emit('refresh')"
    />

    <!-- Import Modal Component -->
    <ProductImportModal
      v-model="showImportModal"
      :products="products"
      :categories="categories"
      :brands="brands"
      @refresh="emit('refresh')"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import api from '@/services/api';
import AppInput from '@/components/admin/ui/Form/AppInput.vue';
import AppTextarea from '@/components/admin/ui/Form/AppTextarea.vue';
import AppSelect from '@/components/admin/ui/Form/AppSelect.vue';
import AppCheckbox from '@/components/admin/ui/Form/AppCheckbox.vue';
import AppButton from '@/components/admin/ui/Button/AppButton.vue';
import AppModal from '@/components/admin/ui/Feedback/AppModal.vue';
import ProductImportModal from './ProductImportModal.vue';
import ProductFormModal from './ProductFormModal.vue';

const props = defineProps({
  products: { type: Array, required: true },
  categories: { type: Array, required: true },
  brands: { type: Array, required: true },
  attributes: { type: Array, required: true },
});

const emit = defineEmits(['refresh']);

const productSearch = ref('');
const productCategoryFilter = ref('');
const productBrandFilter = ref('');
const productStatusFilter = ref('');
const productSortFilter = ref('name-asc');
const productHotFilter = ref(false);
const productRecommendedFilter = ref(false);
const showFilters = ref(false);

const showProductModal = ref(false);
const editingProduct = ref(null);

const showImportModal = ref(false);

const activeFiltersCount = computed(() => {
  let count = 0;
  if (productCategoryFilter.value) count++;
  if (productBrandFilter.value) count++;
  if (productStatusFilter.value) count++;
  if (productSortFilter.value && productSortFilter.value !== 'name-asc') count++;
  if (productHotFilter.value) count++;
  if (productRecommendedFilter.value) count++;
  return count;
});

const resetFilters = () => {
  productSearch.value = '';
  productCategoryFilter.value = '';
  productBrandFilter.value = '';
  productStatusFilter.value = '';
  productSortFilter.value = 'name-asc';
  productHotFilter.value = false;
  productRecommendedFilter.value = false;
};

const filteredProducts = computed(() => {
  let list = props.products.filter(product => {
    const query = (productSearch.value || '').toLowerCase().trim();
    const nameMatch = !query ||
                      (product.nameUk || '').toLowerCase().includes(query) ||
                      (product.nameEn || '').toLowerCase().includes(query) ||
                      (product.variants || []).some(v => (v.sku || '').toLowerCase().includes(query));
    
    const catMatch = !productCategoryFilter.value || product.categoryId === parseInt(productCategoryFilter.value);
    const brandMatch = !productBrandFilter.value || product.brandId === parseInt(productBrandFilter.value);
    const statusMatch = !productStatusFilter.value || product.status === productStatusFilter.value;
    const hotMatch = !productHotFilter.value || product.isHot;
    const recMatch = !productRecommendedFilter.value || product.isRecommended;
    
    return nameMatch && catMatch && brandMatch && statusMatch && hotMatch && recMatch;
  });

  if (productSortFilter.value) {
    list.sort((a, b) => {
      if (productSortFilter.value === 'name-asc') {
        return (a.nameUk || '').localeCompare(b.nameUk || '');
      } else if (productSortFilter.value === 'name-desc') {
        return (b.nameUk || '').localeCompare(a.nameUk || '');
      } else if (productSortFilter.value === 'price-asc') {
        const aMin = Math.min(...(a.variants || []).map(v => v.price), Infinity);
        const bMin = Math.min(...(b.variants || []).map(v => v.price), Infinity);
        return aMin - bMin;
      } else if (productSortFilter.value === 'price-desc') {
        const aMax = Math.max(...(a.variants || []).map(v => v.price), -Infinity);
        const bMax = Math.max(...(b.variants || []).map(v => v.price), -Infinity);
        return bMax - aMax;
      } else if (productSortFilter.value === 'stock-desc') {
        const aStock = (a.variants || []).reduce((sum, v) => sum + (v.stock || 0), 0);
        const bStock = (b.variants || []).reduce((sum, v) => sum + (v.stock || 0), 0);
        return bStock - aStock;
      } else if (productSortFilter.value === 'stock-asc') {
        const aStock = (a.variants || []).reduce((sum, v) => sum + (v.stock || 0), 0);
        const bStock = (b.variants || []).reduce((sum, v) => sum + (v.stock || 0), 0);
        return aStock - bStock;
      }
      return 0;
    });
  }

  return list;
});

const exportCsv = () => {
  const headers = ['ID', 'Назва (UK)', 'Назва (EN)', 'Категорія', 'Бренд', 'SKU / Ціна / Кількість', 'Статус', 'Гаряча', 'Рекомендовано'];
  const rows = filteredProducts.value.map(p => {
    const variantsStr = (p.variants || []).map(v => `${v.sku} (${v.price} UAH, ${v.stock} шт)`).join(' | ');
    return [
      p.id,
      p.nameUk,
      p.nameEn,
      p.categoryName || '—',
      p.brandName || '—',
      variantsStr,
      p.status,
      p.isHot ? 'Так' : 'Ні',
      p.isRecommended ? 'Так' : 'Ні'
    ];
  });

  const csvContent = "\uFEFF" + [headers, ...rows].map(e => e.map(val => `"${String(val).replace(/"/g, '""')}"`).join(",")).join("\n");
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", `products-export-${new Date().getTime()}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const openAddProductModal = () => {
  editingProduct.value = null;
  showProductModal.value = true;
};

const openEditProductModal = (product) => {
  editingProduct.value = product;
  showProductModal.value = true;
};

const deleteProduct = async (id) => {
  if (confirm('Ви впевнені, що хочете видалити цей товар?')) {
    try {
      await api.delete(`/admin/products/${id}`);
      emit('refresh');
    } catch (error) {
      console.error('Failed to delete product:', error);
    }
  }
};



const formatPrice = (val) => {
  return new Intl.NumberFormat('uk-UA', { style: 'currency', currency: 'UAH', maximumFractionDigits: 0 }).format(val);
};
</script>

<style scoped>
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  max-height: 400px;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-10px);
}
</style>
