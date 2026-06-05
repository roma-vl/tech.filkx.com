<template>
  <div class="space-y-6">
    <div
      class="flex justify-between items-center bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm"
    >
      <h2 class="text-lg font-bold text-gray-900 dark:text-white">
        Управління категоріями
      </h2>
      <AppButton
        variant="primary"
        class="flex items-center gap-2"
        @click="openAddCategoryModal"
      >
        Додати категорію
      </AppButton>
    </div>

    <div
      class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
    >
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                ID
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Назва (UK)
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Назва (EN)
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Slug
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Характеристики
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Батьківська категорія
              </th>
              <th
                class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Дії
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="cat in categories"
              :key="cat.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <td
                class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white"
              >
                {{ cat.id }}
              </td>
              <td
                class="px-6 py-4 text-sm text-gray-900 dark:text-white font-bold"
              >
                {{ cat.nameUk }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ cat.nameEn }}
              </td>
              <td
                class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono"
              >
                {{ cat.slug }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-650 dark:text-gray-350">
                <div class="flex flex-col gap-1">
                  <span
                    v-if="getOwnAttributesCount(cat.id) > 0"
                    class="inline-flex items-center w-fit px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400"
                  >
                    Власних: {{ getOwnAttributesCount(cat.id) }}
                  </span>
                  <span
                    v-if="getInheritedAttributesCount(cat.id) > 0"
                    class="inline-flex items-center w-fit px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400"
                  >
                    Успадкованих: {{ getInheritedAttributesCount(cat.id) }}
                  </span>
                  <span
                    v-if="getOwnAttributesCount(cat.id) === 0 && getInheritedAttributesCount(cat.id) === 0"
                    class="text-gray-400 dark:text-gray-500 text-xs italic"
                  >
                    —
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                {{ cat.parentName || "—" }}
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <div class="flex justify-end gap-2">
                  <AppButton
                    variant="ghost"
                    size="sm"
                    class="!p-2 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/20"
                    title="Характеристики"
                    @click="openCategoryAttributesModal(cat)"
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
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                      />
                    </svg>
                  </AppButton>
                  <AppButton
                    variant="ghost"
                    size="sm"
                    class="!p-2 text-blue-600 dark:text-blue-400"
                    @click="openEditCategoryModal(cat)"
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
                    @click="deleteCategory(cat.id)"
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
            <tr v-if="categories.length === 0">
              <td
                colspan="6"
                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
              >
                Категорій не створено.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Category Modal -->
    <AppModal
      v-model="showCategoryModal"
      :title="isEditing ? 'Редагувати категорію' : 'Додати категорію'"
      max-width="md"
    >
      <form
        class="space-y-4"
        @submit.prevent="saveCategory"
      >
        <AppInput
          v-model="categoryForm.nameUk"
          required
          label="Назва категорії (UK)"
          placeholder="напр. Планшети"
        />

        <AppInput
          v-model="categoryForm.nameEn"
          required
          label="Назва категорії (EN)"
          placeholder="e.g. Tablets"
        />

        <AppSelect
          v-model="categoryForm.parentId"
          label="Батьківська категорія"
          placeholder="Немає (Головна категорія)"
          :options="[
            { id: null, nameUk: 'Немає (Головна категорія)' },
            ...categories.filter((c) => c.id !== categoryForm.id),
          ]"
          option-value="id"
          option-label="nameUk"
        />

        <AppInput
          v-model.number="categoryForm.order"
          type="number"
          label="Порядок сортування"
        />
      </form>

      <template #footer>
        <AppButton
          variant="secondary"
          class="mr-2"
          @click="showCategoryModal = false"
        >
          Скасувати
        </AppButton>
        <AppButton
          variant="primary"
          @click="saveCategory"
        >
          Зберегти
        </AppButton>
      </template>
    </AppModal>

    <!-- Category Attributes Modal -->
    <CategoryAttributesModal
      v-model="showCategoryAttributesModal"
      :category="selectedCategoryForAttributes"
      :categories="categories"
      :attributes="attributes"
      @refresh="emit('refresh')"
    />
  </div>
</template>

<script setup>
import { ref } from "vue";
import api from "@/services/api";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import CategoryAttributesModal from "./CategoryAttributesModal.vue";

const props = defineProps({
  categories: { type: Array, required: true },
  attributes: { type: Array, required: true },
});

const emit = defineEmits(["refresh"]);

const showCategoryAttributesModal = ref(false);
const selectedCategoryForAttributes = ref(null);

const openCategoryAttributesModal = (cat) => {
  selectedCategoryForAttributes.value = cat;
  showCategoryAttributesModal.value = true;
};

const getOwnAttributesCount = (catId) => {
  return props.attributes.filter(a => a.categoryIds?.includes(catId)).length;
};

const getInheritedAttributesCount = (catId) => {
  const cat = props.categories.find(c => c.id === catId);
  const ancestorIds = [];
  let currentParentId = cat ? cat.parentId : null;
  while (currentParentId) {
    ancestorIds.push(currentParentId);
    const parent = props.categories.find(c => c.id === currentParentId);
    currentParentId = parent ? parent.parentId : null;
  }
  if (ancestorIds.length === 0) return 0;
  return props.attributes.filter(a => 
    !a.categoryIds?.includes(catId) && 
    a.categoryIds?.some(id => ancestorIds.includes(id))
  ).length;
};

const showCategoryModal = ref(false);
const isEditing = ref(false);

const categoryForm = ref({
  id: null,
  nameUk: "",
  nameEn: "",
  parentId: null,
  order: 0,
});

const openAddCategoryModal = () => {
  isEditing.value = false;
  categoryForm.value = {
    id: null,
    nameUk: "",
    nameEn: "",
    parentId: null,
    order: 0,
  };
  showCategoryModal.value = true;
};

const openEditCategoryModal = (cat) => {
  isEditing.value = true;
  categoryForm.value = {
    id: cat.id,
    nameUk: cat.nameUk,
    nameEn: cat.nameEn,
    parentId: cat.parentId,
    order: cat.order,
  };
  showCategoryModal.value = true;
};

const saveCategory = async () => {
  try {
    if (isEditing.value) {
      await api.put(
        `/admin/categories/${categoryForm.value.id}`,
        categoryForm.value,
      );
    } else {
      await api.post("/admin/categories", categoryForm.value);
    }
    showCategoryModal.value = false;
    emit("refresh");
  } catch (error) {
    console.error("Failed to save category:", error);
  }
};

const deleteCategory = async (id) => {
  if (confirm("Ви впевнені, що хочете видалити цю категорію?")) {
    try {
      await api.delete(`/admin/categories/${id}`);
      emit("refresh");
    } catch (error) {
      console.error("Failed to delete category:", error);
    }
  }
};
</script>
