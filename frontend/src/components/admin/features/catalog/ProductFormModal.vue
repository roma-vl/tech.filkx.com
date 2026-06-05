<template>
  <AppModal
    :model-value="modelValue"
    :title="isEditing ? 'Редагувати товар' : 'Створити товар із варіантами'"
    max-width="3xl"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <form class="space-y-6" @submit.prevent="saveProduct">
      <!-- General Details section -->
      <div
        class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 space-y-4"
      >
        <h4
          class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2"
        >
          1. Загальна інформація про товар
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <AppInput
              v-model="productForm.nameUk"
              :class="errors.nameUk ? 'ring-2 ring-red-500/60' : ''"
              label="Назва товару (UK)"
              placeholder="напр. iPhone 15 Pro Max"
            />
            <p
              v-if="errors.nameUk"
              class="mt-1 text-xs text-red-500 font-semibold"
            >
              {{ errors.nameUk }}
            </p>
          </div>
          <div>
            <AppInput
              v-model="productForm.nameEn"
              :class="errors.nameEn ? 'ring-2 ring-red-500/60' : ''"
              label="Назва товару (EN)"
              placeholder="e.g. iPhone 15 Pro Max"
            />
            <p
              v-if="errors.nameEn"
              class="mt-1 text-xs text-red-500 font-semibold"
            >
              {{ errors.nameEn }}
            </p>
          </div>
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
          <div>
            <AppSelect
              v-model="productForm.categoryId"
              :class="
                errors.categoryId ? 'ring-2 ring-red-500/60 rounded-lg' : ''
              "
              label="Категорія"
              placeholder="Оберіть категорію"
              :options="categories"
              option-value="id"
              option-label="nameUk"
            />
            <p
              v-if="errors.categoryId"
              class="mt-1 text-xs text-red-500 font-semibold"
            >
              {{ errors.categoryId }}
            </p>
          </div>
          <AppSelect
            v-model="productForm.brandId"
            label="Бренд"
            placeholder="Без бренду"
            :options="[{ id: null, name: 'Без бренду' }, ...brands]"
            option-value="id"
            option-label="name"
          />
          <div>
            <AppSelect
              v-model="productForm.status"
              :class="errors.status ? 'ring-2 ring-red-500/60 rounded-lg' : ''"
              label="Статус"
              placeholder="Оберіть статус"
              :options="[
                { id: 'active', name: 'Активний' },
                { id: 'draft', name: 'Чернетка' },
                { id: 'hidden', name: 'Прихований' },
              ]"
              option-value="id"
              option-label="name"
            />
            <p
              v-if="errors.status"
              class="mt-1 text-xs text-red-500 font-semibold"
            >
              {{ errors.status }}
            </p>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-6 pt-2">
          <!-- Promos (Hot / Recommended) -->
          <div
            class="flex items-center gap-6 p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50"
          >
            <label
              class="flex items-center gap-2 cursor-pointer select-none text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors"
            >
              <input
                v-model="productForm.isHot"
                type="checkbox"
                class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-700 dark:border-gray-600"
              />
              🔥 Гаряча пропозиція
            </label>
            <label
              class="flex items-center gap-2 cursor-pointer select-none text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors"
            >
              <input
                v-model="productForm.isRecommended"
                type="checkbox"
                class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-700 dark:border-gray-600"
              />
              👍 Рекомендовано
            </label>
          </div>
        </div>
      </div>

      <!-- Variants section -->
      <div class="space-y-4">
        <div
          class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2"
        >
          <h4 class="font-bold text-gray-900 dark:text-white">
            2. Варіанти товару та ціни
          </h4>
          <AppButton
            type="button"
            variant="secondary"
            size="sm"
            class="flex items-center gap-1.5"
            @click="addProductVariant"
          >
            <svg
              class="w-4 h-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 4v16m8-8H4"
              />
            </svg>
            Додати варіант
          </AppButton>
        </div>

        <div
          v-for="(v, vIdx) in productForm.variants"
          :key="vIdx"
          class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 space-y-4 relative"
        >
          <div class="absolute top-4 right-4 flex items-center gap-2">
            <span
              class="text-xs font-black uppercase text-gray-400 bg-gray-200/50 dark:bg-gray-800 px-2 py-0.5 rounded-md"
              >Варіант #{{ vIdx + 1 }}</span
            >
            <AppButton
              v-if="productForm.variants.length > 1"
              type="button"
              variant="ghost"
              class="!text-red-500 hover:!bg-red-50 dark:hover:!bg-red-900/20 !p-1.5"
              @click="removeProductVariant(vIdx)"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
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

          <div
            class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4 pt-4"
          >
            <div>
              <AppInput
                v-model="v.sku"
                :class="
                  variantErrors[vIdx]?.sku ? 'ring-2 ring-red-500/60' : ''
                "
                label="SKU (Артикул)"
                placeholder="напр. iphone-15-black"
              />
              <p
                v-if="variantErrors[vIdx]?.sku"
                class="mt-1 text-xs text-red-500 font-semibold"
              >
                {{ variantErrors[vIdx].sku }}
              </p>
            </div>
            <div>
              <AppInput
                :model-value="v.price ?? undefined"
                :class="
                  variantErrors[vIdx]?.price ? 'ring-2 ring-red-500/60' : ''
                "
                type="number"
                step="0.01"
                label="Ціна (UAH)"
                placeholder="0.00"
                @update:model-value="
                  (val) => ((v as any).price = val === '' ? null : Number(val))
                "
              />
              <p
                v-if="variantErrors[vIdx]?.price"
                class="mt-1 text-xs text-red-500 font-semibold"
              >
                {{ variantErrors[vIdx].price }}
              </p>
            </div>
            <AppInput
              :model-value="v.oldPrice ?? undefined"
              type="number"
              step="0.01"
              label="Стара ціна (UAH)"
              placeholder="0.00"
              @update:model-value="
                (val) => ((v as any).oldPrice = val === '' ? null : Number(val))
              "
            />
            <div>
              <AppInput
                :model-value="v.stock ?? undefined"
                :class="
                  variantErrors[vIdx]?.stock ? 'ring-2 ring-red-500/60' : ''
                "
                type="number"
                label="Кількість"
                placeholder="0"
              />
              <p
                v-if="variantErrors[vIdx]?.stock"
                class="mt-1 text-xs text-red-500 font-semibold"
              >
                {{ variantErrors[vIdx].stock }}
              </p>
            </div>
            <AppInput
              :model-value="v.weight ?? undefined"
              type="number"
              step="0.001"
              label="Вага (кг)"
              placeholder="0.000"
              @update:model-value="
                (val) => (v.weight = val === '' ? null : Number(val))
              "
            />
          </div>

          <!-- Image Upload / Management -->
          <div class="space-y-2">
            <label
              class="block text-xs font-bold text-gray-500 uppercase tracking-wider"
              >Зображення варіанту</label
            >
            <div class="flex flex-wrap items-center gap-4">
              <!-- Upload Image Button -->
              <label
                class="flex flex-col items-center justify-center w-24 h-24 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl cursor-pointer hover:border-primary-500 dark:hover:border-primary-500 hover:bg-gray-100/50 dark:hover:bg-gray-800/35 transition-all duration-200"
              >
                <div
                  class="flex flex-col items-center justify-center pt-5 pb-6"
                >
                  <svg
                    class="w-6 h-6 text-gray-400 mb-1"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 4v16m8-8H4"
                    />
                  </svg>
                  <span class="text-[9px] font-bold text-gray-500 uppercase"
                    >Додати</span
                  >
                </div>
                <input
                  type="file"
                  class="hidden"
                  accept="image/*"
                  multiple
                  @change="onFileChange($event, v)"
                />
              </label>

              <!-- Image Grid with Drag and Drop -->
              <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                <div
                  v-for="(img, imgIdx) in v.images"
                  :key="imgIdx"
                  draggable="true"
                  :class="
                    img.isPrimary
                      ? 'border-2 border-emerald-500 ring-2 ring-emerald-500/20 shadow-md scale-102'
                      : 'border border-gray-200 dark:border-gray-700 hover:border-primary-500'
                  "
                  class="relative aspect-square rounded-2xl overflow-hidden bg-gray-50 dark:bg-gray-900 group cursor-move transition-all duration-200"
                  @dragstart="onDragStart($event, imgIdx, v)"
                  @dragover.prevent
                  @drop="onDrop($event, imgIdx, v)"
                  @dragend="onDragEnd"
                >
                  <img
                    :src="img.url"
                    alt=""
                    class="w-full h-full object-cover"
                  />

                  <!-- Badges -->
                  <span
                    v-if="img.isPrimary"
                    class="absolute top-2 left-2 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full shadow"
                  >
                    ★ Головне
                  </span>
                  <span
                    v-else
                    class="absolute top-2 left-2 bg-black/60 backdrop-blur-xs text-white text-[8px] font-bold px-2 py-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    #{{ imgIdx + 1 }}
                  </span>

                  <!-- Delete Image overlay button -->
                  <button
                    type="button"
                    class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-200 shadow hover:bg-red-600"
                    @click="removeVariantImage(v, imgIdx)"
                  >
                    <svg
                      class="w-3.5 h-3.5"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Attributes configuration inside variant -->
          <div class="space-y-3 pt-2">
            <div class="flex justify-between items-center">
              <label
                class="block text-xs font-bold text-gray-500 uppercase tracking-wider"
                >Характеристики варіанту</label
              >
            </div>

            <div
              v-if="!productForm.categoryId"
              class="text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-900/50 p-3 rounded-xl"
            >
              ⚠️ Оберіть категорію товару в розділі 1, щоб налаштувати характеристики.
            </div>

            <div
              v-else-if="availableAttributes.length === 0"
              class="text-xs text-gray-500 dark:text-gray-400 italic bg-gray-50/50 dark:bg-gray-900/10 p-3 rounded-xl border border-dashed border-gray-200 dark:border-gray-800"
            >
              Немає доступних характеристик для обраної категорії.
            </div>

            <div
              v-if="productForm.categoryId && v.attributes && v.attributes.length > 0"
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"
            >
              <div
                v-for="(attr, attrIdx) in v.attributes"
                :key="attr.attributeId"
                class="bg-white dark:bg-gray-850 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-xs relative"
              >
                <!-- Display attribute name as a strong label -->
                <div class="space-y-2">
                  <span class="block text-xs font-black uppercase text-gray-400">
                    {{ getAttributeName(attr.attributeId) }}
                  </span>

                  <!-- Dynamic Attribute Value input based on its type -->
                  <!-- select dropdown type or color type -->
                  <AppSelect
                    v-if="
                      getAttributeType(attr.attributeId) === 'select' ||
                      getAttributeType(attr.attributeId) === 'color'
                    "
                    v-model="attr.valueId"
                    placeholder="Оберіть значення..."
                    :options="getAttributeValues(attr.attributeId)"
                    option-value="id"
                    option-label="valueUk"
                  />

                  <!-- text/number input type -->
                  <AppInput
                    v-else
                    v-model="attr.value"
                    placeholder="Введіть значення..."
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Додаткові налаштування -->
      <div
        class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 space-y-4"
      >
        <div
          class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2"
        >
          <h4 class="font-bold text-gray-900 dark:text-white">
            3. Додаткові параметри (SEO, супутні товари)
          </h4>
          <span
            class="bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full select-none"
          >
            Незабаром
          </span>
        </div>

        <div
          class="grid grid-cols-1 md:grid-cols-2 gap-6 opacity-60 pointer-events-none select-none"
        >
          <!-- SEO Settings Group -->
          <div
            class="space-y-3 p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
          >
            <h5
              class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500"
            >
              🔍 SEO Налаштування (Soon)
            </h5>
            <AppInput
              disabled
              label="SEO Заголовок (Title)"
              placeholder="напр. Придбати iPhone 15 Pro за найкращою ціною"
            />
            <AppTextarea
              disabled
              rows="2"
              label="SEO Опис (Meta Description)"
              placeholder="Короткий опис для пошукових систем..."
            />
          </div>

          <!-- Cross selling / Upselling -->
          <div
            class="space-y-3 p-4 rounded-xl border border-dashed border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
          >
            <h5
              class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500"
            >
              🔗 Супутні товари (Soon)
            </h5>
            <div
              class="p-3 bg-gray-50 dark:bg-gray-950/30 rounded-xl text-xs text-gray-400 dark:text-gray-500 italic"
            >
              Тут ви зможете зв'язати товари для рекомендацій «Разом дешевше» або
              «З цим товаром також купують».
            </div>
            <AppSelect
              disabled
              label="Рекомендовані аксесуари"
              placeholder="Оберіть товари..."
              :options="[]"
            />
          </div>
        </div>
      </div>

      <!-- Global validation error banner -->
      <div
        v-if="globalError"
        class="flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700/50 text-red-700 dark:text-red-400 rounded-xl px-4 py-3 text-sm font-semibold"
      >
        <svg
          class="w-5 h-5 flex-shrink-0 mt-0.5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <span style="white-space: pre-line">{{ globalError }}</span>
      </div>
    </form>

    <template #footer>
      <AppButton
        variant="secondary"
        class="mr-2"
        @click="$emit('update:modelValue', false)"
      >
        Скасувати
      </AppButton>
      <AppButton variant="primary" @click="saveProduct">
        Зберегти товар
      </AppButton>
    </template>
  </AppModal>
</template>

<script setup lang="ts">
import { ref, watch, computed } from "vue";
import apiClient from "@/shared/services/api/apiClient";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";

interface VariantErrors {
  sku?: string;
  price?: string;
  stock?: string;
}

interface FormErrors {
  nameUk?: string;
  nameEn?: string;
  categoryId?: string;
  status?: string;
}

const props = defineProps<{
  modelValue: boolean;
  product?: any;
  categories: any[];
  brands: any[];
  attributes: any[];
}>();

const emit = defineEmits<{
  (e: "update:modelValue", val: boolean): void;
  (e: "refresh"): void;
}>();

// Validation state
const errors = ref<FormErrors>({});
const variantErrors = ref<VariantErrors[]>([]);
const globalError = ref<string | null>(null);

// availableAttributes and attributes watcher are moved down below productForm initialization to prevent ReferenceError

const validate = (): boolean => {
  const e: FormErrors = {};
  const ve: VariantErrors[] = [];
  let valid = true;

  if (!productForm.value.nameUk.trim()) {
    e.nameUk = "Назва товару (UK) є обов'язковою";
    valid = false;
  }
  if (!productForm.value.nameEn.trim()) {
    e.nameEn = "Назва товару (EN) є обов'язковою";
    valid = false;
  }
  if (!productForm.value.categoryId) {
    e.categoryId = "Категорія є обов'язковою";
    valid = false;
  }
  if (!productForm.value.status) {
    e.status = "Статус є обов'язковим";
    valid = false;
  }

  productForm.value.variants.forEach((v: any, i: number) => {
    const ve_: VariantErrors = {};
    if (!v.sku?.trim()) {
      ve_.sku = "SKU є обов'язковим";
      valid = false;
    }
    if (v.price === null || v.price === "" || Number(v.price) < 0) {
      ve_.price = "Вкажіть коректну ціну";
      valid = false;
    }
    if (v.stock === null || v.stock === "" || Number(v.stock) < 0) {
      ve_.stock = "Кількість не може бути від'ємною";
      valid = false;
    }
    ve[i] = ve_;
  });

  errors.value = e;
  variantErrors.value = ve;
  return valid;
};

const clearErrors = () => {
  errors.value = {};
  variantErrors.value = [];
  globalError.value = null;
};

const isEditing = ref(false);

interface ProductVariantForm {
  id: number | null;
  sku: string;
  price: number | null;
  oldPrice: number | null;
  stock: number;
  weight: number | null;
  images: { url: string; isPrimary: boolean }[];
  attributes: { attributeId: any; valueId: any; value: string }[];
}

const productForm = ref<{
  id: number | null;
  nameUk: string;
  nameEn: string;
  descriptionUk: string;
  descriptionEn: string;
  categoryId: any;
  brandId: any;
  status: string;
  isHot: boolean;
  isRecommended: boolean;
  variants: ProductVariantForm[];
}>({
  id: null,
  nameUk: "",
  nameEn: "",
  descriptionUk: "",
  descriptionEn: "",
  categoryId: "",
  brandId: null,
  status: "active",
  isHot: false,
  isRecommended: false,
  variants: [],
});

const availableAttributes = computed(() => {
  const catId = Number(productForm.value.categoryId);
  if (!catId) {
    return props.attributes.filter(attr => !attr.categoryIds || attr.categoryIds.length === 0);
  }

  const ancestorIds: number[] = [];
  let currentId: number | null = catId;
  while (currentId) {
    ancestorIds.push(currentId);
    const cat = props.categories.find(c => c.id === currentId);
    currentId = cat ? cat.parentId : null;
  }

  return props.attributes.filter(attr => {
    return !attr.categoryIds || attr.categoryIds.length === 0 || attr.categoryIds.some((id: number) => ancestorIds.includes(id));
  });
});

watch(
  [() => productForm.value.categoryId, availableAttributes],
  () => {
    if (!productForm.value.variants) return;
    
    productForm.value.variants.forEach((v: any) => {
      if (!v.attributes) {
        v.attributes = [];
      }
      
      const currentAttrs = [...v.attributes];
      const syncedAttrs: any[] = [];
      
      availableAttributes.value.forEach((avail: any) => {
        const existing = currentAttrs.find(a => Number(a.attributeId) === Number(avail.id));
        if (existing) {
          syncedAttrs.push(existing);
        } else {
          syncedAttrs.push({
            attributeId: avail.id,
            valueId: null,
            value: ""
          });
        }
      });
      
      v.attributes = syncedAttrs;
    });
  },
  { immediate: true, deep: true }
);

watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal) {
      initForm();
    }
  },
);

const initForm = () => {
  clearErrors();
  if (props.product) {
    isEditing.value = true;
    const product = props.product;
    const variantsCloned = (product.variants || []).map((v: any) => {
      const imagesMapped = (v.images || []).map((img: any) => ({
        url: img.url,
        isPrimary: !!img.isPrimary,
      }));
      if (imagesMapped.length === 0) {
        imagesMapped.push({
          url: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop",
          isPrimary: true,
        });
      }

      const attributesMapped = (v.attributes || []).map((a: any) => ({
        attributeId: a.attributeId,
        valueId: a.valueId,
        value: a.value,
      }));

      return {
        id: v.id,
        sku: v.sku,
        price: v.price,
        oldPrice: v.oldPrice,
        stock: v.stock,
        weight: v.weight,
        images: imagesMapped,
        attributes: attributesMapped,
      };
    });

    productForm.value = {
      id: product.id,
      nameUk: product.nameUk,
      nameEn: product.nameEn,
      descriptionUk: product.descriptionUk,
      descriptionEn: product.descriptionEn,
      categoryId: product.categoryId || "",
      brandId: product.brandId,
      status: product.status,
      isHot: !!product.isHot,
      isRecommended: !!product.isRecommended,
      variants:
        variantsCloned.length > 0
          ? variantsCloned
          : [
              {
                id: null,
                sku: "",
                price: 0,
                oldPrice: null,
                stock: 10,
                weight: null,
                images: [],
                attributes: [],
              },
            ],
    };
  } else {
    isEditing.value = false;
    productForm.value = {
      id: null,
      nameUk: "",
      nameEn: "",
      descriptionUk: "",
      descriptionEn: "",
      categoryId: props.categories[0]?.id || "",
      brandId: null,
      status: "active",
      isHot: false,
      isRecommended: false,
      variants: [
        {
          id: null,
          sku: "",
          price: 0,
          oldPrice: null,
          stock: 10,
          weight: null,
          images: [],
          attributes: [],
        },
      ],
    };
  }
};

const buildPayload = () => {
  const f = productForm.value;
  return {
    nameUk: f.nameUk,
    nameEn: f.nameEn,
    descriptionUk: f.descriptionUk || null,
    descriptionEn: f.descriptionEn || null,
    status: f.status,
    isHot: f.isHot,
    isRecommended: f.isRecommended,
    brandId: f.brandId || null,
    categoryId: Number(f.categoryId),
    variants: f.variants.map((v) => ({
      id: v.id || null,
      sku: v.sku,
      price: Number(v.price),
      oldPrice: v.oldPrice != null ? Number(v.oldPrice) : null,
      stock: Number(v.stock),
      weight: v.weight != null ? Number(v.weight) : null,
      images: v.images ?? [],
      attributes: (v.attributes ?? [])
        .filter((a: any) => {
          if (!a.attributeId) return false;
          const type = getAttributeType(a.attributeId);
          if (type === "select" || type === "color") {
            return a.valueId !== null && a.valueId !== undefined && a.valueId !== "";
          } else {
            return a.value !== null && a.value !== undefined && String(a.value).trim() !== "";
          }
        })
        .map((a: any) => ({
          attributeId: Number(a.attributeId),
          valueId: a.valueId ? Number(a.valueId) : null,
          value: a.value !== null && a.value !== undefined ? String(a.value).trim() : null,
        })),
    })),
  };
};

const saveProduct = async () => {
  clearErrors();
  if (!validate()) {
    globalError.value = "Заповніть усі обов'язкові поля, позначені червоним.";
    return;
  }
  const payload = buildPayload();
  console.log(
    "[ProductForm] Sending payload:",
    JSON.stringify(payload, null, 2),
  );
  try {
    if (isEditing.value) {
      await apiClient.put(`/admin/products/${productForm.value.id}`, payload);
    } else {
      await apiClient.post("/admin/products", payload);
    }
    emit("update:modelValue", false);
    emit("refresh");
  } catch (error: any) {
    console.error("Failed to save product:", error);
    const serverErrors = error.response?.data?.errors;
    const serverMessage = error.response?.data?.message;
    console.error("[ProductForm] Server errors:", serverErrors);

    if (serverErrors) {
      // Map top-level field errors
      if (serverErrors["nameUk"])
        errors.value.nameUk = serverErrors["nameUk"][0];
      if (serverErrors["nameEn"])
        errors.value.nameEn = serverErrors["nameEn"][0];
      if (serverErrors["categoryId"])
        errors.value.categoryId = serverErrors["categoryId"][0];
      if (serverErrors["status"])
        errors.value.status = serverErrors["status"][0];

      // Collect all errors into readable list
      const allMessages: string[] = [];
      Object.entries(serverErrors).forEach(([field, msgs]: [string, any]) => {
        allMessages.push(`${field}: ${Array.isArray(msgs) ? msgs[0] : msgs}`);
      });
      globalError.value = "Помилки сервера:\n" + allMessages.join("\n");
    } else {
      globalError.value =
        serverMessage || "Помилка при збереженні товару. Спробуйте ще раз.";
    }
  }
};

const addProductVariant = () => {
  productForm.value.variants.push({
    id: null,
    sku: "",
    price: 0,
    oldPrice: null,
    stock: 10,
    weight: null,
    images: [],
    attributes: [],
  });
};

const removeProductVariant = (index: number) => {
  productForm.value.variants.splice(index, 1);
  variantErrors.value.splice(index, 1);
};

const draggedIndex = ref<number | null>(null);
const draggedVariant = ref<any>(null);

const onDragStart = (event: DragEvent, index: number, variant: any) => {
  draggedIndex.value = index;
  draggedVariant.value = variant;
  if (event.dataTransfer) event.dataTransfer.effectAllowed = "move";
};

const onDrop = (event: DragEvent, index: number, variant: any) => {
  if (
    draggedVariant.value === variant &&
    draggedIndex.value !== null &&
    draggedIndex.value !== index
  ) {
    const images = variant.images;
    const draggedItem = images[draggedIndex.value];
    images.splice(draggedIndex.value, 1);
    images.splice(index, 0, draggedItem);
    images.forEach((img: any, idx: number) => {
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

const onFileChange = async (event: Event, variant: any) => {
  const input = event.target as HTMLInputElement;
  const files = input.files;
  if (!files || files.length === 0) return;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const formData = new FormData();
    formData.append("image", file);
    try {
      const response = await apiClient.post(
        "/admin/products/upload",
        formData,
        {
          headers: { "Content-Type": "multipart/form-data" },
        },
      );
      if (response.data?.data?.url) {
        variant.images.push({
          url: response.data.data.url,
          isPrimary: variant.images.length === 0,
        });
      }
    } catch (error: any) {
      console.error("Failed to upload image:", error);
      globalError.value =
        "Помилка завантаження зображення: " +
        (error.response?.data?.message || error.message);
    }
  }
  input.value = "";
};

const removeVariantImage = (variant: any, index: number) => {
  variant.images.splice(index, 1);
  if (variant.images.length > 0) {
    variant.images.forEach((img: any, idx: number) => {
      img.isPrimary = idx === 0;
    });
  }
};

const addVariantAttribute = (variant: any) => {
  variant.attributes.push({ attributeId: "", valueId: null, value: "" });
};

const removeVariantAttribute = (variant: any, index: number) => {
  variant.attributes.splice(index, 1);
};

const onAttributeSelected = (attr: any) => {
  const selected = props.attributes.find((a: any) => a.id === attr.attributeId);
  if (selected) {
    attr.valueId = null;
    attr.value = "";
    if (
      (selected.type === "select" || selected.type === "color") &&
      selected.values.length > 0
    ) {
      attr.valueId = selected.values[0].id;
    }
  }
};

const getAttributeName = (attrId: any) => {
  const attr = props.attributes.find((a: any) => a.id === attrId);
  return attr ? attr.nameUk : "Характеристика";
};

const getAttributeType = (attrId: any) => {
  const attr = props.attributes.find((a: any) => a.id === attrId);
  return attr ? attr.type : "text";
};

const getAttributeValues = (attrId: any) => {
  const attr = props.attributes.find((a: any) => a.id === attrId);
  if (!attr) return [];
  return [
    { id: null, valueUk: "— Не обрано —", valueEn: "— Not Selected —" },
    ...(attr.values || []),
  ];
};
</script>
