<template>
  <AppModal
    :model-value="modelValue"
    :title="isEditing ? 'Редагувати товар' : 'Створити товар із варіантами'"
    max-width="3xl"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <form
      class="space-y-6"
      @submit.prevent="saveProduct"
    >
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
            placeholder="Оберіть статус"
            :options="[
              { id: 'active', name: 'Активний' },
              { id: 'draft', name: 'Чернетка' },
              { id: 'hidden', name: 'Прихований' },
            ]"
            option-value="id"
            option-label="name"
          />
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
              >
              🔥 Гаряча пропозиція
            </label>
            <label
              class="flex items-center gap-2 cursor-pointer select-none text-xs font-bold uppercase tracking-wider text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors"
            >
              <input
                v-model="productForm.isRecommended"
                type="checkbox"
                class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-700 dark:border-gray-600"
              >
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
            >Варіант #{{ vIdx + 1 }}</span>
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
            <AppInput
              v-model="v.sku"
              required
              label="SKU (Артикул)"
              placeholder="напр. iphone-15-black"
            />
            <AppInput
              v-model.number="v.price"
              required
              type="number"
              step="0.01"
              label="Ціна (UAH)"
              placeholder="0.00"
            />
            <AppInput
              v-model.number="v.oldPrice"
              type="number"
              step="0.01"
              label="Стара ціна (UAH)"
              placeholder="0.00"
            />
            <AppInput
              v-model.number="v.stock"
              required
              type="number"
              label="Кількість"
              placeholder="0"
            />
            <AppInput
              v-model.number="v.weight"
              type="number"
              step="0.001"
              label="Вага (кг)"
              placeholder="0.000"
            />
          </div>

          <!-- Image Upload / Management -->
          <div class="space-y-2">
            <label
              class="block text-xs font-bold text-gray-500 uppercase tracking-wider"
            >Зображення варіанту</label>
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
                  <span class="text-[9px] font-bold text-gray-500 uppercase">Додати</span>
                </div>
                <input
                  type="file"
                  class="hidden"
                  accept="image/*"
                  multiple
                  @change="onFileChange($event, v)"
                >
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
                  >

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
              >Характеристики варіанту</label>
              <AppButton
                type="button"
                variant="ghost"
                size="sm"
                class="!text-primary-600 hover:!bg-primary-50 dark:hover:!bg-primary-950/20 !px-2.5 !py-1 !text-xs font-bold"
                @click="addVariantAttribute(v)"
              >
                + Додати характеристику
              </AppButton>
            </div>

            <div
              v-if="v.attributes.length > 0"
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"
            >
              <div
                v-for="(attr, attrIdx) in v.attributes"
                :key="attrIdx"
                class="flex items-end gap-2 bg-white dark:bg-gray-900 p-3 rounded-xl border border-gray-150 dark:border-gray-800 shadow-xs relative group/attr"
              >
                <div class="flex-1 space-y-2">
                  <!-- Attribute Selection -->
                  <AppSelect
                    v-model="attr.attributeId"
                    label="Назва"
                    placeholder="Оберіть..."
                    :options="attributes"
                    option-value="id"
                    option-label="nameUk"
                    @change="onAttributeSelected(attr)"
                  />

                  <!-- Dynamic Attribute Value input -->
                  <div v-if="attr.attributeId">
                    <!-- select dropdown type -->
                    <AppSelect
                      v-if="
                        getAttributeType(attr.attributeId) === 'select' ||
                          getAttributeType(attr.attributeId) === 'color'
                      "
                      v-model="attr.valueId"
                      label="Значення"
                      placeholder="Оберіть значення..."
                      :options="getAttributeValues(attr.attributeId)"
                      option-value="id"
                      option-label="valueUk"
                    />

                    <!-- text input type -->
                    <AppInput
                      v-else
                      v-model="attr.value"
                      label="Значення"
                      placeholder="Введіть значення..."
                    />
                  </div>
                </div>

                <AppButton
                  type="button"
                  variant="ghost"
                  class="!text-red-500 hover:!bg-red-50 dark:hover:!bg-red-900/20 !p-1.5 mb-1 select-none"
                  @click="removeVariantAttribute(v, attrIdx)"
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
                </AppButton>
              </div>
            </div>
          </div>
        </div>
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
      <AppButton
        variant="primary"
        @click="saveProduct"
      >
        Зберегти товар
      </AppButton>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, watch } from "vue";
import api from "@/services/api";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  product: { type: Object, default: null },
  categories: { type: Array, required: true },
  brands: { type: Array, required: true },
  attributes: { type: Array, required: true },
});

const emit = defineEmits(["update:modelValue", "refresh"]);

const isEditing = ref(false);
const productForm = ref({
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

watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal) {
      initForm();
    }
  },
);

const initForm = () => {
  if (props.product) {
    isEditing.value = true;
    const product = props.product;
    const variantsCloned = (product.variants || []).map((v) => {
      const imagesMapped = (v.images || []).map((img) => ({
        url: img.url,
        isPrimary: !!img.isPrimary,
      }));
      if (imagesMapped.length === 0) {
        imagesMapped.push({
          url: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop",
          isPrimary: true,
        });
      }

      const attributesMapped = (v.attributes || []).map((a) => ({
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

const saveProduct = async () => {
  try {
    if (isEditing.value) {
      await api.put(
        `/admin/products/${productForm.value.id}`,
        productForm.value,
      );
    } else {
      await api.post("/admin/products", productForm.value);
    }
    emit("update:modelValue", false);
    emit("refresh");
  } catch (error) {
    console.error("Failed to save product:", error);
    alert(
      "Помилка при збереженні товару. Перевірте правильність заповнення полів.",
    );
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

const removeProductVariant = (index) => {
  productForm.value.variants.splice(index, 1);
};

const draggedIndex = ref(null);
const draggedVariant = ref(null);

const onDragStart = (event, index, variant) => {
  draggedIndex.value = index;
  draggedVariant.value = variant;
  event.dataTransfer.effectAllowed = "move";
};

const onDrop = (event, index, variant) => {
  if (
    draggedVariant.value === variant &&
    draggedIndex.value !== null &&
    draggedIndex.value !== index
  ) {
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
    formData.append("image", file);

    try {
      const response = await api.post("/admin/products/upload", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      if (response.data && response.data.data && response.data.data.url) {
        const imageUrl = response.data.data.url;
        const isPrimary = variant.images.length === 0;
        variant.images.push({ url: imageUrl, isPrimary });
      }
    } catch (error) {
      console.error("Failed to upload image:", error);
      alert(
        "Помилка при завантаженні зображення: " +
          (error.response?.data?.message || error.message),
      );
    }
  }
  event.target.value = "";
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
    attributeId: "",
    valueId: null,
    value: "",
  });
};

const removeVariantAttribute = (variant, index) => {
  variant.attributes.splice(index, 1);
};

const onAttributeSelected = (attr) => {
  const selected = props.attributes.find((a) => a.id === attr.attributeId);
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

const getAttributeType = (attrId) => {
  const attr = props.attributes.find((a) => a.id === attrId);
  return attr ? attr.type : "text";
};

const getAttributeValues = (attrId) => {
  const attr = props.attributes.find((a) => a.id === attrId);
  return attr ? attr.values : [];
};
</script>
