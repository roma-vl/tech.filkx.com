<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="space-y-4">
      <div
        class="flex flex-col md:flex-row md:items-center justify-between gap-4"
      >
        <div class="flex flex-1 items-center gap-3">
          <div class="flex-1 max-w-md">
            <AppInput
              v-model="productSearch"
              :placeholder="t('admin.products.list.searchPlaceholder')"
            >
              <template #prepend>
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
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
            :title="t('admin.products.list.filtersTitle')"
            @click="showFilters = !showFilters"
          >
            <svg
              class="w-5 h-5 transition-colors"
              :class="showFilters ? 'text-primary-600' : 'text-gray-500'"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
              />
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
            class="flex items-center gap-2 shrink-0 h-[38px] !py-0"
            @click="showImportModal = true"
          >
            <svg
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
              />
            </svg>
            {{ t("admin.products.list.importCsv") }}
          </AppButton>

          <AppButton
            variant="secondary"
            class="flex items-center gap-2 shrink-0 h-[38px] !py-0"
            @click="exportCsv"
          >
            <svg
              class="w-4 h-4"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
              />
            </svg>
            {{ t("admin.products.list.exportCsv") }}
          </AppButton>

          <AppButton
            variant="primary"
            class="flex items-center gap-2 shrink-0 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
            @click="openAddProductModal"
          >
            <svg
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"
              />
            </svg>
            {{ t("admin.products.list.addProduct") }}
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
              :label="t('admin.products.list.filters.categoryLabel')"
              :placeholder="t('admin.products.list.filters.allCategories')"
              :options="[
                {
                  id: '',
                  nameUk: t('admin.products.list.filters.allCategories'),
                },
                ...categories,
              ]"
              option-value="id"
              option-label="nameUk"
            />

            <AppSelect
              v-model="productBrandFilter"
              :label="t('admin.products.list.filters.brandLabel')"
              :placeholder="t('admin.products.list.filters.allBrands')"
              :options="[
                { id: '', name: t('admin.products.list.filters.allBrands') },
                ...brands,
              ]"
              option-value="id"
              option-label="name"
            />

            <AppSelect
              v-model="productStatusFilter"
              :label="t('admin.products.list.filters.statusLabel')"
              :placeholder="t('admin.products.list.filters.allStatuses')"
              :options="[
                { id: '', name: t('admin.products.list.filters.allStatuses') },
                {
                  id: 'active',
                  name: t('admin.products.list.filters.statusActive'),
                },
                {
                  id: 'draft',
                  name: t('admin.products.list.filters.statusDraft'),
                },
                {
                  id: 'hidden',
                  name: t('admin.products.list.filters.statusHidden'),
                },
              ]"
              option-value="id"
              option-label="name"
            />

            <AppSelect
              v-model="productSortFilter"
              :label="t('admin.products.list.filters.sortLabel')"
              :placeholder="t('admin.products.list.filters.sortPlaceholder')"
              :options="[
                {
                  id: 'name-asc',
                  name: t('admin.products.list.filters.sortNameAsc'),
                },
                {
                  id: 'name-desc',
                  name: t('admin.products.list.filters.sortNameDesc'),
                },
                {
                  id: 'price-asc',
                  name: t('admin.products.list.filters.sortPriceAsc'),
                },
                {
                  id: 'price-desc',
                  name: t('admin.products.list.filters.sortPriceDesc'),
                },
                {
                  id: 'stock-desc',
                  name: t('admin.products.list.filters.sortStockDesc'),
                },
                {
                  id: 'stock-asc',
                  name: t('admin.products.list.filters.sortStockAsc'),
                },
              ]"
              option-value="id"
              option-label="name"
            />

            <AppSelect
              v-model="productImageFilter"
              :label="t('admin.products.list.filters.imageLabel')"
              :placeholder="t('admin.products.list.filters.allImages')"
              :options="[
                { id: '', name: t('admin.products.list.filters.allImages') },
                { id: 'with', name: t('admin.products.list.filters.withImage') },
                { id: 'without', name: t('admin.products.list.filters.withoutImage') },
              ]"
              option-value="id"
              option-label="name"
            />
          </div>

          <div
            class="flex items-center justify-between pt-6 border-t border-gray-150 dark:border-gray-700"
          >
            <div class="flex gap-6">
              <!-- Checkboxes / Toggles for promo filters -->
              <label
                class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase cursor-pointer select-none"
              >
                <input
                  v-model="productHotFilter"
                  type="checkbox"
                  class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-750 dark:border-gray-650"
                />
                {{ t("admin.products.list.filters.hot") }}
              </label>

              <label
                class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase cursor-pointer select-none"
              >
                <input
                  v-model="productRecommendedFilter"
                  type="checkbox"
                  class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-750 dark:border-gray-650"
                />
                {{ t("admin.products.list.filters.recommended") }}
              </label>
            </div>

            <AppButton
              variant="text"
              class="!text-red-500 hover:!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20 !px-4 !py-2 !rounded-xl font-bold"
              @click="resetFilters"
            >
              {{ t("admin.products.list.filters.reset") }}
            </AppButton>
          </div>
        </div>
      </transition>

      <!-- Bulk Actions Toolbar -->
      <transition name="expand">
        <div
          v-if="selectedIds.length > 0"
          class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-2xl border border-primary-200 dark:border-primary-800 flex flex-wrap items-center gap-3"
        >
          <span class="text-sm font-bold text-primary-700 dark:text-primary-300">
            {{ t("admin.products.list.bulk.selectedCount", { count: selectedIds.length }) }}
          </span>

          <AppButton
            variant="text"
            size="sm"
            class="!text-gray-500"
            @click="clearSelection"
          >
            {{ t("admin.products.list.bulk.clearSelection") }}
          </AppButton>

          <div class="flex-1"></div>

          <div class="flex items-center gap-2">
            <AppSelect
              v-model="bulkCategoryValue"
              class="min-w-[180px]"
              :placeholder="t('admin.products.list.bulk.categoryPlaceholder')"
              :options="categories"
              option-value="id"
              option-label="nameUk"
            />
            <AppButton
              variant="secondary"
              size="sm"
              :disabled="!bulkCategoryValue || bulkActionLoading"
              @click="applyBulkCategory"
            >
              {{ t("admin.products.list.bulk.apply") }}
            </AppButton>
          </div>

          <div class="flex items-center gap-2">
            <AppSelect
              v-model="bulkStatusValue"
              class="min-w-[160px]"
              :placeholder="t('admin.products.list.bulk.statusPlaceholder')"
              :options="[
                { id: 'active', name: t('admin.products.list.filters.statusActive') },
                { id: 'draft', name: t('admin.products.list.filters.statusDraft') },
                { id: 'hidden', name: t('admin.products.list.filters.statusHidden') },
              ]"
              option-value="id"
              option-label="name"
            />
            <AppButton
              variant="secondary"
              size="sm"
              :disabled="!bulkStatusValue || bulkActionLoading"
              @click="applyBulkStatus"
            >
              {{ t("admin.products.list.bulk.apply") }}
            </AppButton>
          </div>

          <AppButton
            variant="text"
            size="sm"
            class="!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20"
            :disabled="bulkActionLoading"
            @click="showBulkDeleteModal = true"
          >
            {{ t("admin.products.list.bulk.delete") }}
          </AppButton>
        </div>
      </transition>
    </div>

    <!-- Products Table -->
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
    >
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
              <th class="px-6 py-4 w-12">
                <input
                  type="checkbox"
                  class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-750 dark:border-gray-650"
                  :checked="allOnPageSelected"
                  :indeterminate="someOnPageSelected && !allOnPageSelected"
                  @change="toggleSelectAllOnPage"
                />
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.products.list.table.product") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.products.list.table.category") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.products.list.table.brand") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.products.list.table.variants") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.products.list.table.status") }}
              </th>
              <th
                class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.products.list.table.actions") }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="product in paginatedProducts"
              :key="product.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <td class="px-6 py-4">
                <input
                  type="checkbox"
                  class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-750 dark:border-gray-650"
                  :checked="selectedIds.includes(product.id)"
                  @change="toggleSelected(product.id)"
                />
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <img
                    :src="product.image"
                    alt=""
                    class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 bg-gray-100"
                  />
                  <div>
                    <div class="flex items-center gap-1.5">
                      <div class="font-bold text-gray-900 dark:text-white">
                        {{ product.nameUk }}
                      </div>
                      <span
                        v-if="product.isHot"
                        :title="t('admin.products.list.hotTitle')"
                        class="text-xs"
                        >🔥</span
                      >
                      <span
                        v-if="product.isRecommended"
                        :title="t('admin.products.list.recommendedTitle')"
                        class="text-xs"
                        >👍</span
                      >
                    </div>
                    <div class="text-xs text-gray-400">
                      {{ product.nameEn }}
                    </div>
                  </div>
                </div>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300"
              >
                {{
                  product.categoryName || t("admin.products.list.noCategory")
                }}
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300"
              >
                {{ product.brandName || t("admin.products.list.noBrand") }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="space-y-1">
                  <div
                    v-for="v in product.variants"
                    :key="v.id"
                    class="text-xs text-gray-700 dark:text-gray-300"
                  >
                    <span
                      class="font-mono bg-gray-100 dark:bg-gray-950 px-1 py-0.5 rounded text-[10px]"
                      >{{ v.sku }}</span
                    >:
                    <span class="font-bold text-gray-900 dark:text-white">{{
                      formatPrice(v.price)
                    }}</span>
                    <span class="text-gray-400">
                      ({{
                        t("admin.products.list.stockCount", {
                          count: v.stock,
                        })
                      }})</span
                    >
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400':
                      product.status === 'active',
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300':
                      product.status === 'draft',
                    'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400':
                      product.status === 'hidden',
                  }"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider"
                >
                  {{
                    product.status === "active"
                      ? t("admin.products.list.filters.statusActive")
                      : product.status === "draft"
                        ? t("admin.products.list.filters.statusDraft")
                        : t("admin.products.list.filters.statusHidden")
                  }}
                </span>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <div class="flex justify-end gap-2">
                  <AppButton
                    variant="ghost"
                    size="sm"
                    class="!p-2 text-blue-600 dark:text-blue-400"
                    @click="openEditProductModal(product)"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                      />
                    </svg>
                  </AppButton>
                  <AppButton
                    variant="ghost"
                    size="sm"
                    class="!p-2 text-red-600 dark:text-red-400"
                    @click="deleteProduct(product)"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </AppButton>
                </div>
              </td>
            </tr>
            <tr v-if="filteredProducts.length === 0">
              <td
                colspan="7"
                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
              >
                {{ t("admin.products.list.empty") }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div
        class="px-6 py-4 border-t border-gray-150 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col md:flex-row md:items-center gap-3"
      >
        <div class="flex items-center gap-2 shrink-0">
          <label class="text-sm text-gray-500 dark:text-gray-400">
            {{ t("admin.products.list.perPageLabel") }}
          </label>
          <AppSelect
            v-model="perPage"
            class="min-w-[90px]"
            :options="perPageOptions"
            option-value="id"
            option-label="name"
          />
        </div>
        <AppPagination
          class="flex-1"
          :pagination="paginationMeta"
          @page-change="onPageChange"
        />
      </div>
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <AppConfirmModal
      v-model="showBulkDeleteModal"
      :title="t('admin.products.list.bulk.deleteModal.title')"
      :message="
        t('admin.products.list.bulk.deleteModal.message', {
          count: selectedIds.length,
        })
      "
      :confirm-text="t('admin.products.list.deleteModal.confirm')"
      :cancel-text="t('admin.products.list.deleteModal.cancel')"
      :loading="bulkActionLoading"
      @confirm="confirmBulkDelete"
    />

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

    <!-- Delete Confirmation Modal -->
    <AppConfirmModal
      v-model="showDeleteModal"
      :title="t('admin.products.list.deleteModal.title')"
      :message="
        t('admin.products.list.deleteModal.message', {
          name: productToDelete?.nameUk || '',
        })
      "
      :confirm-text="t('admin.products.list.deleteModal.confirm')"
      :cancel-text="t('admin.products.list.deleteModal.cancel')"
      :loading="deletingProduct"
      @confirm="confirmDeleteProduct"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/shared/services/api/apiClient";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import AppPagination from "@/components/admin/ui/AppPagination.vue";
import ProductImportModal from "./ProductImportModal.vue";
import ProductFormModal from "./ProductFormModal.vue";
import AppConfirmModal from "@/components/admin/ui/AppConfirmModal.vue";

const { t } = useI18n();

const props = defineProps({
  products: { type: Array, required: true },
  categories: { type: Array, required: true },
  brands: { type: Array, required: true },
  attributes: { type: Array, required: true },
});

const emit = defineEmits(["refresh"]);

const productSearch = ref("");
const productCategoryFilter = ref("");
const productBrandFilter = ref("");
const productStatusFilter = ref("");
const productSortFilter = ref("name-asc");
const productHotFilter = ref(false);
const productRecommendedFilter = ref(false);
const productImageFilter = ref("");
const showFilters = ref(false);

const showProductModal = ref(false);
const editingProduct = ref(null);

const showImportModal = ref(false);

const showDeleteModal = ref(false);
const productToDelete = ref(null);
const deletingProduct = ref(false);

// Pagination logic
const currentPage = ref(1);
const perPage = ref(15);
const perPageOptions = [10, 15, 25, 50, 100].map((n) => ({ id: n, name: String(n) }));

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredProducts.value.slice(start, start + perPage.value);
});

const paginationMeta = computed(() => ({
  current_page: currentPage.value,
  last_page: Math.max(1, Math.ceil(filteredProducts.value.length / perPage.value)),
  per_page: perPage.value,
  total: filteredProducts.value.length,
}));

const onPageChange = (page) => {
  currentPage.value = page;
};

watch(
  [
    productSearch,
    productCategoryFilter,
    productBrandFilter,
    productStatusFilter,
    productSortFilter,
    productHotFilter,
    productRecommendedFilter,
    productImageFilter,
    perPage,
  ],
  () => {
    currentPage.value = 1;
  },
);

// Bulk selection - kept as an array of ids so a selection can span pages;
// pruned below whenever the underlying product list changes (delete, etc.)
// so it never holds ids for products that no longer exist.
const selectedIds = ref([]);

watch(
  () => props.products,
  (list) => {
    const existingIds = new Set(list.map((p) => p.id));
    selectedIds.value = selectedIds.value.filter((id) => existingIds.has(id));
  },
);

const allOnPageSelected = computed(
  () =>
    paginatedProducts.value.length > 0 &&
    paginatedProducts.value.every((p) => selectedIds.value.includes(p.id)),
);

const someOnPageSelected = computed(() =>
  paginatedProducts.value.some((p) => selectedIds.value.includes(p.id)),
);

const toggleSelected = (id) => {
  const index = selectedIds.value.indexOf(id);
  if (index === -1) {
    selectedIds.value.push(id);
  } else {
    selectedIds.value.splice(index, 1);
  }
};

const toggleSelectAllOnPage = () => {
  const pageIds = paginatedProducts.value.map((p) => p.id);
  if (allOnPageSelected.value) {
    selectedIds.value = selectedIds.value.filter((id) => !pageIds.includes(id));
  } else {
    selectedIds.value = [...new Set([...selectedIds.value, ...pageIds])];
  }
};

const clearSelection = () => {
  selectedIds.value = [];
};

const bulkActionLoading = ref(false);
const bulkStatusValue = ref("");
const bulkCategoryValue = ref("");
const showBulkDeleteModal = ref(false);

const confirmBulkDelete = async () => {
  bulkActionLoading.value = true;
  try {
    await api.delete("/admin/products/bulk-delete", {
      data: { ids: selectedIds.value },
    });
    clearSelection();
    showBulkDeleteModal.value = false;
    emit("refresh");
  } catch (error) {
    console.error("Failed to bulk delete products:", error);
  } finally {
    bulkActionLoading.value = false;
  }
};

const applyBulkStatus = async () => {
  if (!bulkStatusValue.value) return;
  bulkActionLoading.value = true;
  try {
    await api.put("/admin/products/bulk-status", {
      ids: selectedIds.value,
      status: bulkStatusValue.value,
    });
    clearSelection();
    bulkStatusValue.value = "";
    emit("refresh");
  } catch (error) {
    console.error("Failed to bulk update product status:", error);
  } finally {
    bulkActionLoading.value = false;
  }
};

const applyBulkCategory = async () => {
  if (!bulkCategoryValue.value) return;
  bulkActionLoading.value = true;
  try {
    await api.put("/admin/products/bulk-category", {
      ids: selectedIds.value,
      categoryId: bulkCategoryValue.value,
    });
    clearSelection();
    bulkCategoryValue.value = "";
    emit("refresh");
  } catch (error) {
    console.error("Failed to bulk update product category:", error);
  } finally {
    bulkActionLoading.value = false;
  }
};

const activeFiltersCount = computed(() => {
  let count = 0;
  if (productCategoryFilter.value) count++;
  if (productBrandFilter.value) count++;
  if (productStatusFilter.value) count++;
  if (productSortFilter.value && productSortFilter.value !== "name-asc")
    count++;
  if (productHotFilter.value) count++;
  if (productRecommendedFilter.value) count++;
  if (productImageFilter.value) count++;
  return count;
});

const resetFilters = () => {
  productSearch.value = "";
  productCategoryFilter.value = "";
  productBrandFilter.value = "";
  productStatusFilter.value = "";
  productSortFilter.value = "name-asc";
  productHotFilter.value = false;
  productRecommendedFilter.value = false;
  productImageFilter.value = "";
};

const filteredProducts = computed(() => {
  let list = props.products.filter((product) => {
    const query = (productSearch.value || "").toLowerCase().trim();
    const nameMatch =
      !query ||
      (product.nameUk || "").toLowerCase().includes(query) ||
      (product.nameEn || "").toLowerCase().includes(query) ||
      (product.variants || []).some((v) =>
        (v.sku || "").toLowerCase().includes(query),
      );

    const catMatch =
      !productCategoryFilter.value ||
      product.categoryId === parseInt(productCategoryFilter.value);
    const brandMatch =
      !productBrandFilter.value ||
      product.brandId === parseInt(productBrandFilter.value);
    const statusMatch =
      !productStatusFilter.value ||
      product.status === productStatusFilter.value;
    const hotMatch = !productHotFilter.value || product.isHot;
    const recMatch = !productRecommendedFilter.value || product.isRecommended;
    const imageMatch =
      !productImageFilter.value ||
      (productImageFilter.value === "with"
        ? product.hasImage
        : !product.hasImage);

    return (
      nameMatch &&
      catMatch &&
      brandMatch &&
      statusMatch &&
      hotMatch &&
      recMatch &&
      imageMatch
    );
  });

  if (productSortFilter.value) {
    list.sort((a, b) => {
      if (productSortFilter.value === "name-asc") {
        return (a.nameUk || "").localeCompare(b.nameUk || "");
      } else if (productSortFilter.value === "name-desc") {
        return (b.nameUk || "").localeCompare(a.nameUk || "");
      } else if (productSortFilter.value === "price-asc") {
        const aMin = Math.min(
          ...(a.variants || []).map((v) => v.price),
          Infinity,
        );
        const bMin = Math.min(
          ...(b.variants || []).map((v) => v.price),
          Infinity,
        );
        return aMin - bMin;
      } else if (productSortFilter.value === "price-desc") {
        const aMax = Math.max(
          ...(a.variants || []).map((v) => v.price),
          -Infinity,
        );
        const bMax = Math.max(
          ...(b.variants || []).map((v) => v.price),
          -Infinity,
        );
        return bMax - aMax;
      } else if (productSortFilter.value === "stock-desc") {
        const aStock = (a.variants || []).reduce(
          (sum, v) => sum + (v.stock || 0),
          0,
        );
        const bStock = (b.variants || []).reduce(
          (sum, v) => sum + (v.stock || 0),
          0,
        );
        return bStock - aStock;
      } else if (productSortFilter.value === "stock-asc") {
        const aStock = (a.variants || []).reduce(
          (sum, v) => sum + (v.stock || 0),
          0,
        );
        const bStock = (b.variants || []).reduce(
          (sum, v) => sum + (v.stock || 0),
          0,
        );
        return aStock - bStock;
      }
      return 0;
    });
  }

  return list;
});

const exportCsv = () => {
  const headers = [
    t("admin.products.list.export.headerId"),
    t("admin.products.list.export.headerNameUk"),
    t("admin.products.list.export.headerNameEn"),
    t("admin.products.list.export.headerCategory"),
    t("admin.products.list.export.headerBrand"),
    t("admin.products.list.export.headerVariants"),
    t("admin.products.list.export.headerStatus"),
    t("admin.products.list.export.headerHot"),
    t("admin.products.list.export.headerRecommended"),
  ];
  const rows = filteredProducts.value.map((p) => {
    const variantsStr = (p.variants || [])
      .map(
        (v) =>
          `${v.sku} (${v.price} UAH, ${t("admin.products.list.stockCount", { count: v.stock })})`,
      )
      .join(" | ");
    const noneValue = t("admin.products.list.export.noneValue");
    return [
      p.id,
      p.nameUk,
      p.nameEn,
      p.categoryName || noneValue,
      p.brandName || noneValue,
      variantsStr,
      p.status,
      // "Так"/"Ні" are the CSV import format's expected literals for these
      // columns (see ProductImportModal.vue's parser), not UI copy — keep
      // them fixed regardless of the admin's locale so round-tripping an
      // exported file back through the importer keeps working.
      p.isHot ? "Так" : "Ні",
      p.isRecommended ? "Так" : "Ні",
    ];
  });

  const csvContent =
    "\uFEFF" +
    [headers, ...rows]
      .map((e) =>
        e.map((val) => `"${String(val).replace(/"/g, '""')}"`).join(","),
      )
      .join("\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", `products-export-${new Date().getTime()}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Deleting (or bulk-deleting) products can shrink the list below the page
// the admin was on - e.g. removing the last product on page 16 - so clamp
// back to the new last page instead of the table silently rendering empty.
// Placed after filteredProducts (which paginationMeta depends on) since
// watch() evaluates its getter synchronously on creation.
watch(
  () => paginationMeta.value.last_page,
  (lastPage) => {
    if (currentPage.value > lastPage) {
      currentPage.value = lastPage;
    }
  },
);

const openAddProductModal = () => {
  editingProduct.value = null;
  showProductModal.value = true;
};

const openEditProductModal = (product) => {
  editingProduct.value = product;
  showProductModal.value = true;
};

const deleteProduct = (product) => {
  productToDelete.value = product;
  showDeleteModal.value = true;
};

const confirmDeleteProduct = async () => {
  if (!productToDelete.value) return;
  deletingProduct.value = true;
  try {
    await api.delete(`/admin/products/${productToDelete.value.id}`);
    emit("refresh");
    showDeleteModal.value = false;
  } catch (error) {
    console.error("Failed to delete product:", error);
  } finally {
    deletingProduct.value = false;
  }
};

const formatPrice = (val) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(val);
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
