<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-end sm:items-center gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="flex-1 w-full sm:w-auto">
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

      <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
        <div class="min-w-[180px]">
          <AppSelect
            v-model="productCategoryFilter"
            placeholder="Усі категорії"
            :options="[{ id: '', nameUk: 'Усі категорії' }, ...categories]"
            option-value="id"
            option-label="nameUk"
          />
        </div>

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

    <!-- Product Edit/Create Modal (using UI components) -->
    <AppModal
      v-model="showProductModal"
      :title="isEditing ? 'Редагувати товар' : 'Створити товар із варіантами'"
      max-width="3xl"
    >
      <form @submit.prevent="saveProduct" class="space-y-6">
        <!-- General Details section -->
        <div class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 space-y-4">
          <h4 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">1. Загальна інформація про товар</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <AppInput
              v-model="productForm.nameUk"
              required
              label="Назва товару (UK)"
              placeholder="напр. iPhone 15 Pro Max"
            />
            <AppInput
              v-model="productForm.nameEn"
              required
              label="Назва товару (EN)"
              placeholder="e.g. iPhone 15 Pro Max"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <AppTextarea
              v-model="productForm.descriptionUk"
              rows="3"
              label="Опис (UK)"
              placeholder="Опис українською..."
            />
            <AppTextarea
              v-model="productForm.descriptionEn"
              rows="3"
              label="Опис (EN)"
              placeholder="Description in English..."
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <AppSelect
              v-model="productForm.categoryId"
              required
              label="Категорія"
              placeholder="Оберіть категорію"
              :options="categories"
              option-value="id"
              option-label="nameUk"
            />
            <AppSelect
              v-model="productForm.brandId"
              label="Бренд"
              placeholder="Без бренду"
              :options="[{ id: null, name: 'Без бренду' }, ...brands]"
              option-value="id"
              option-label="name"
            />
            <AppSelect
              v-model="productForm.status"
              required
              label="Статус"
              :options="[
                { id: 'active', name: 'Активний' },
                { id: 'draft', name: 'Чернетка' },
                { id: 'hidden', name: 'Прихований' }
              ]"
              option-value="id"
              option-label="name"
            />
          </div>

          <!-- Promotion Tags -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <!-- Hot Offer Card Selector -->
            <button
              type="button"
              @click="productForm.isHot = !productForm.isHot"
              :class="productForm.isHot
                ? 'border-amber-500/50 bg-amber-500/10 text-amber-700 dark:text-amber-300 ring-2 ring-amber-500/20'
                : 'border-gray-200 dark:border-gray-700 hover:border-amber-500/30 text-gray-600 dark:text-gray-400 hover:bg-amber-50/30 dark:hover:bg-amber-950/5'"
              class="flex items-center justify-between p-3.5 rounded-xl border font-bold text-sm transition-all text-left cursor-pointer active:scale-98"
            >
              <div class="flex items-center gap-2.5">
                <span class="text-xl">🔥</span>
                <div>
                  <div class="font-bold text-gray-900 dark:text-white text-xs uppercase tracking-wider">Гаряча пропозиція</div>
                  <div class="text-[11px] text-gray-400 font-normal">Відображати бейдж вогника та висувати в топ</div>
                </div>
              </div>
              <div
                :class="productForm.isHot ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-700'"
                class="w-5 h-5 rounded-full flex items-center justify-center transition-all"
              >
                <svg v-if="productForm.isHot" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
              </div>
            </button>

            <!-- Recommended Card Selector -->
            <button
              type="button"
              @click="productForm.isRecommended = !productForm.isRecommended"
              :class="productForm.isRecommended
                ? 'border-indigo-500/50 bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 ring-2 ring-indigo-500/20'
                : 'border-gray-200 dark:border-gray-700 hover:border-indigo-500/30 text-gray-600 dark:text-gray-400 hover:bg-indigo-50/30 dark:hover:bg-indigo-950/5'"
              class="flex items-center justify-between p-3.5 rounded-xl border font-bold text-sm transition-all text-left cursor-pointer active:scale-98"
            >
              <div class="flex items-center gap-2.5">
                <span class="text-xl">👍</span>
                <div>
                  <div class="font-bold text-gray-900 dark:text-white text-xs uppercase tracking-wider">Рекомендовано</div>
                  <div class="text-[11px] text-gray-400 font-normal">Відображати рекомендацію та показувати на головній</div>
                </div>
              </div>
              <div
                :class="productForm.isRecommended ? 'bg-indigo-500 text-white' : 'bg-gray-200 dark:bg-gray-700'"
                class="w-5 h-5 rounded-full flex items-center justify-center transition-all"
              >
                <svg v-if="productForm.isRecommended" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
              </div>
            </button>
          </div>
        </div>

        <!-- Variants section -->
        <div class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 space-y-6">
          <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
            <h4 class="font-bold text-gray-900 dark:text-white">2. Варіанти товару та наявність</h4>
            <AppButton type="button" @click="addProductVariant" size="sm" variant="success" class="flex items-center gap-1">
              + Додати варіант
            </AppButton>
          </div>

          <div v-for="(v, index) in productForm.variants" :key="index" class="bg-white dark:bg-gray-800 p-5 rounded-xl border border-gray-100 dark:border-gray-800 space-y-4 shadow-sm relative">
            <AppButton v-if="productForm.variants.length > 1" type="button" @click="removeProductVariant(index)" variant="ghost" size="sm" class="absolute top-4 right-4 !text-red-500 hover:!bg-red-50 dark:hover:!bg-red-950/20">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </AppButton>

            <h5 class="text-base font-bold text-primary-500">Варіант #{{ index + 1 }}</h5>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
              <AppInput
                v-model="v.sku"
                required
                label="SKU артикул"
                placeholder="SKU"
              />
              <AppInput
                v-model.number="v.price"
                required
                type="number"
                step="0.01"
                label="Ціна (₴)"
              />
              <AppInput
                v-model.number="v.oldPrice"
                type="number"
                step="0.01"
                label="Стара ціна (₴)"
              />
              <AppInput
                v-model.number="v.stock"
                required
                type="number"
                label="Склад (шт)"
              />
              <AppInput
                v-model.number="v.weight"
                type="number"
                step="0.01"
                label="Вага (кг)"
              />
            </div>

            <!-- Images Section with Drag-and-Drop and Server Upload -->
            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 pt-4">
              <div class="flex justify-between items-center">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Фотогалерея варіанту (Перше фото автоматично стає головним)
                </label>
                <span class="text-xs text-gray-400">Перетягуйте фото мишкою для зміни порядку</span>
              </div>

              <!-- Image Grid with Drag and Drop -->
              <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                <div
                  v-for="(img, imgIdx) in v.images"
                  :key="imgIdx"
                  draggable="true"
                  @dragstart="onDragStart($event, imgIdx, v)"
                  @dragover.prevent
                  @drop="onDrop($event, imgIdx, v)"
                  @dragend="onDragEnd"
                  :class="img.isPrimary 
                    ? 'border-2 border-emerald-500 ring-2 ring-emerald-500/20 shadow-md scale-102' 
                    : 'border border-gray-200 dark:border-gray-700 hover:border-primary-500'"
                  class="relative aspect-square rounded-2xl overflow-hidden bg-gray-50 dark:bg-gray-900 group cursor-move transition-all duration-200"
                >
                  <img :src="img.url" alt="" class="w-full h-full object-cover" />
                  
                  <!-- Badges -->
                  <span v-if="img.isPrimary" class="absolute top-2 left-2 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full shadow">
                    ★ Головне
                  </span>
                  <span v-else class="absolute top-2 left-2 bg-black/60 backdrop-blur-xs text-white text-[8px] font-bold px-2 py-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                    #{{ imgIdx + 1 }}
                  </span>

                  <!-- Action buttons -->
                  <button
                    type="button"
                    @click="removeVariantImage(v, imgIdx)"
                    class="absolute top-2 right-2 p-1.5 bg-rose-500 text-white rounded-lg opacity-0 group-hover:opacity-100 hover:bg-rose-600 transition-all shadow-md"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>

                <!-- Upload Button / Area -->
                <label class="border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-primary-500 rounded-2xl flex flex-col items-center justify-center aspect-square cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/30 group">
                  <svg class="w-6 h-6 text-gray-400 group-hover:text-primary-500 mb-1 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 group-hover:text-primary-500 transition-colors">Завантажити</span>
                  <input
                    type="file"
                    multiple
                    accept="image/*"
                    class="hidden"
                    @change="onFileChange($event, v)"
                  />
                </label>
              </div>
            </div>

            <!-- Attributes for Variant section -->
            <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 pt-4">
              <div class="flex justify-between items-center">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Характеристики варіанту (Атрибути)</label>
                <AppButton type="button" @click="addVariantAttribute(v)" variant="text" size="sm">
                  + Додати характеристику
                </AppButton>
              </div>

              <div v-for="(attr, aIdx) in v.attributes" :key="aIdx" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-200/40">
                <div>
                  <AppSelect
                    v-model="attr.attributeId"
                    required
                    label="Атрибут"
                    placeholder="Оберіть характеристику"
                    :options="attributes"
                    option-value="id"
                    option-label="nameUk"
                    @change="onAttributeSelected(attr)"
                  />
                </div>
                
                <div>
                  <AppSelect
                    v-if="getAttributeType(attr.attributeId) === 'select' || getAttributeType(attr.attributeId) === 'color'"
                    v-model="attr.valueId"
                    required
                    label="Значення"
                    placeholder="Оберіть значення"
                    :options="getAttributeValues(attr.attributeId).map(val => ({ id: val.id, name: val.valueUk || val.value }))"
                    option-value="id"
                    option-label="name"
                  />
                  <AppInput
                    v-else
                    v-model="attr.value"
                    required
                    label="Значення"
                    placeholder="напр. 8GB чи M2"
                  />
                </div>

                <div class="flex justify-end">
                  <AppButton type="button" @click="removeVariantAttribute(v, aIdx)" variant="danger" size="sm">
                    Видалити
                  </AppButton>
                </div>
              </div>
            </div>

          </div>
        </div>
      </form>

      <template #footer>
        <AppButton variant="secondary" @click="showProductModal = false" class="mr-2">
          Скасувати
        </AppButton>
        <AppButton @click="saveProduct" variant="primary">
          Зберегти товар
        </AppButton>
      </template>
    </AppModal>
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

const props = defineProps({
  products: { type: Array, required: true },
  categories: { type: Array, required: true },
  brands: { type: Array, required: true },
  attributes: { type: Array, required: true },
});

const emit = defineEmits(['refresh']);

const productSearch = ref('');
const productCategoryFilter = ref('');

const showProductModal = ref(false);
const isEditing = ref(false);

const productForm = ref({
  id: null,
  nameUk: '',
  nameEn: '',
  descriptionUk: '',
  descriptionEn: '',
  categoryId: '',
  brandId: null,
  status: 'active',
  isHot: false,
  isRecommended: false,
  variants: []
});

const filteredProducts = computed(() => {
  return props.products.filter(product => {
    const nameMatch = (product.nameUk || '').toLowerCase().includes(productSearch.value.toLowerCase()) ||
                      (product.nameEn || '').toLowerCase().includes(productSearch.value.toLowerCase()) ||
                      (product.variants || []).some(v => (v.sku || '').toLowerCase().includes(productSearch.value.toLowerCase()));
    
    const catMatch = !productCategoryFilter.value || product.categoryId === parseInt(productCategoryFilter.value);
    
    return nameMatch && catMatch;
  });
});

const openAddProductModal = () => {
  isEditing.value = false;
  productForm.value = {
    id: null,
    nameUk: '',
    nameEn: '',
    descriptionUk: '',
    descriptionEn: '',
    categoryId: props.categories[0]?.id || '',
    brandId: null,
    status: 'active',
    isHot: false,
    isRecommended: false,
    variants: [
      {
        id: null,
        sku: '',
        price: 0,
        oldPrice: null,
        stock: 10,
        weight: null,
        images: [{ url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop', isPrimary: true }],
        attributes: []
      }
    ]
  };
  showProductModal.value = true;
};

const openEditProductModal = (product) => {
  isEditing.value = true;
  
  const variantsCloned = (product.variants || []).map(v => {
    const imagesMapped = (v.images || []).map(img => ({ url: img.url, isPrimary: !!img.isPrimary }));
    if (imagesMapped.length === 0) {
      imagesMapped.push({ url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop', isPrimary: true });
    }
    
    const attributesMapped = (v.attributes || []).map(a => ({
      attributeId: a.attributeId,
      valueId: a.valueId,
      value: a.value
    }));

    return {
      id: v.id,
      sku: v.sku,
      price: v.price,
      oldPrice: v.oldPrice,
      stock: v.stock,
      weight: v.weight,
      images: imagesMapped,
      attributes: attributesMapped
    };
  });

  productForm.value = {
    id: product.id,
    nameUk: product.nameUk,
    nameEn: product.nameEn,
    descriptionUk: product.descriptionUk,
    descriptionEn: product.descriptionEn,
    categoryId: product.categoryId || '',
    brandId: product.brandId,
    status: product.status,
    isHot: !!product.isHot,
    isRecommended: !!product.isRecommended,
    variants: variantsCloned.length > 0 ? variantsCloned : [
      {
        id: null,
        sku: '',
        price: 0,
        oldPrice: null,
        stock: 10,
        weight: null,
        images: [{ url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop', isPrimary: true }],
        attributes: []
      }
    ]
  };
  showProductModal.value = true;
};

const saveProduct = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/admin/products/${productForm.value.id}`, productForm.value);
    } else {
      await api.post('/admin/products', productForm.value);
    }
    showProductModal.value = false;
    emit('refresh');
  } catch (error) {
    console.error('Failed to save product:', error);
    alert('Помилка при збереженні товару. Перевірте правильність заповнення полів.');
  }
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

const addProductVariant = () => {
  productForm.value.variants.push({
    id: null,
    sku: '',
    price: 0,
    oldPrice: null,
    stock: 10,
    weight: null,
    images: [{ url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop', isPrimary: true }],
    attributes: []
  });
};

const removeProductVariant = (index) => {
  productForm.value.variants.splice(index, 1);
};

const draggedIndex = ref(null);
const draggedVariant = ref(null);

const onDragStart = (event, index, variant) => {
  draggedIndex.value = index;
  draggedVariant.value = variant;
  event.dataTransfer.effectAllowed = 'move';
};

const onDrop = (event, index, variant) => {
  if (draggedVariant.value === variant && draggedIndex.value !== null && draggedIndex.value !== index) {
    const images = variant.images;
    const draggedItem = images[draggedIndex.value];
    
    images.splice(draggedIndex.value, 1);
    images.splice(index, 0, draggedItem);

    images.forEach((img, idx) => {
      img.isPrimary = idx === 0;
    });
  }
  draggedIndex.value = null;
  draggedVariant.value = null;
};

const onDragEnd = () => {
  draggedIndex.value = null;
  draggedVariant.value = null;
};

const onFileChange = async (event, variant) => {
  const files = event.target.files;
  if (!files || files.length === 0) return;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const formData = new FormData();
    formData.append('image', file);

    try {
      const response = await api.post('/admin/products/upload', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      if (response.data && response.data.data && response.data.data.url) {
        const imageUrl = response.data.data.url;
        const isPrimary = variant.images.length === 0;
        variant.images.push({ url: imageUrl, isPrimary });
      }
    } catch (error) {
      console.error('Failed to upload image:', error);
      alert('Помилка при завантаженні зображення: ' + (error.response?.data?.message || error.message));
    }
  }
  event.target.value = '';
};

const removeVariantImage = (variant, index) => {
  variant.images.splice(index, 1);
  if (variant.images.length > 0) {
    variant.images.forEach((img, idx) => {
      img.isPrimary = idx === 0;
    });
  }
};

const addVariantAttribute = (variant) => {
  variant.attributes.push({
    attributeId: '',
    valueId: null,
    value: ''
  });
};

const removeVariantAttribute = (variant, index) => {
  variant.attributes.splice(index, 1);
};

const onAttributeSelected = (attr) => {
  const selected = props.attributes.find(a => a.id === attr.attributeId);
  if (selected) {
    attr.valueId = null;
    attr.value = '';
    if ((selected.type === 'select' || selected.type === 'color') && selected.values.length > 0) {
      attr.valueId = selected.values[0].id;
    }
  }
};

const getAttributeType = (attrId) => {
  const attr = props.attributes.find(a => a.id === attrId);
  return attr ? attr.type : 'text';
};

const getAttributeValues = (attrId) => {
  const attr = props.attributes.find(a => a.id === attrId);
  return attr ? attr.values : [];
};

const formatPrice = (val) => {
  return new Intl.NumberFormat('uk-UA', { style: 'currency', currency: 'UAH', maximumFractionDigits: 0 }).format(val);
};
</script>
