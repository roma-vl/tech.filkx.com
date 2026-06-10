<template>
  <div class="space-y-6">
    <div
      class="flex justify-between items-center bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm"
    >
      <h2 class="text-lg font-bold text-gray-900 dark:text-white">
        Характеристики та атрибути
      </h2>
      <AppButton
        variant="primary"
        class="flex items-center gap-2"
        @click="openAddAttributeModal"
      >
        Додати атрибут
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
                Код атрибуту
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Назва (UK)
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Тип поля
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Категорії
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Варіанти значень
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
              v-for="attr in attributes"
              :key="attr.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <td
                class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white"
              >
                {{ attr.id }}
              </td>
              <td
                class="px-6 py-4 text-sm text-gray-900 dark:text-white font-mono font-bold"
              >
                {{ attr.code }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                {{ attr.nameUk }}
              </td>
              <td
                class="px-6 py-4 text-sm font-semibold uppercase tracking-wider text-xs"
              >
                <span
                  :class="{
                    'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400':
                      attr.type === 'select',
                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400':
                      attr.type === 'color',
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300':
                      attr.type === 'text',
                  }"
                  class="px-2 py-0.5 rounded"
                >
                  {{ attr.type }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                <div class="flex flex-wrap gap-1 max-w-[240px]">
                  <span
                    v-for="catId in attr.categoryIds"
                    :key="catId"
                    class="bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded text-[10px] font-bold"
                  >
                    {{ getCategoryName(catId) }}
                  </span>
                  <span
                    v-if="!attr.categoryIds || attr.categoryIds.length === 0"
                    class="text-gray-400 dark:text-gray-500 text-xs italic"
                  >
                    Усі категорії
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="val in attr.values"
                    :key="val.id"
                    class="bg-gray-100 dark:bg-gray-900 px-2 py-0.5 rounded text-xs"
                  >
                    {{ val.valueUk || val.value }}
                  </span>
                </div>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <div class="flex justify-end gap-2">
                  <AppButton
                    variant="ghost"
                    size="sm"
                    class="!p-2 text-blue-600 dark:text-blue-400"
                    @click="openEditAttributeModal(attr)"
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
                    @click="deleteAttribute(attr.id)"
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
            <tr v-if="attributes.length === 0">
              <td
                colspan="6"
                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
              >
                Характеристик не створено.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Attribute Modal -->
    <AppModal
      v-model="showAttributeModal"
      :title="isEditing ? 'Редагувати атрибут' : 'Додати атрибут'"
      max-width="md"
    >
      <form
        class="space-y-4"
        @submit.prevent="saveAttribute"
      >
        <AppInput
          v-model="attributeForm.code"
          required
          label="Код атрибуту (системний)"
          placeholder="напр. color, ram, screen_size"
        />

        <AppInput
          v-model="attributeForm.nameUk"
          required
          label="Назва атрибуту (UK)"
          placeholder="напр. Колір чи ОЗП"
        />

        <AppInput
          v-model="attributeForm.nameEn"
          required
          label="Назва атрибуту (EN)"
          placeholder="e.g. Color or RAM"
        />

        <AppSelect
          v-model="attributeForm.type"
          required
          label="Тип поля"
          :options="[
            { id: 'text', name: 'Текст (Вільне введення)' },
            { id: 'select', name: 'Випадаючий список варіантів' },
            { id: 'color', name: 'Кольоровий вибір' },
            { id: 'number', name: 'Число' },
            { id: 'boolean', name: 'Так / Ні (Булеве)' },
          ]"
          option-value="id"
          option-label="name"
        />

        <!-- Attributes preset values list -->
        <div
          v-if="
            attributeForm.type === 'select' || attributeForm.type === 'color'
          "
          class="space-y-2 mt-4 pt-4 border-t border-gray-150 dark:border-gray-700"
        >
          <div class="flex justify-between items-center">
            <label class="block text-xs font-bold text-gray-500 uppercase">Список можливих значень</label>
            <AppButton
              type="button"
              variant="text"
              size="sm"
              @click="addAttributeValue"
            >
              + Додати значення
            </AppButton>
          </div>

          <div
            v-for="(val, vIdx) in attributeForm.values"
            :key="vIdx"
            class="flex gap-2 items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border"
          >
            <div
              v-if="attributeForm.type === 'color'"
              class="flex-1 flex gap-2"
            >
              <input
                v-model="val.value"
                required
                type="text"
                placeholder="#FF0000"
                class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs"
              >
              <input
                v-model="val.value"
                type="color"
                class="w-8 h-8 rounded border cursor-pointer bg-transparent"
              >
            </div>
            <div
              v-else
              class="flex-1 flex gap-2"
            >
              <input
                v-model="val.valueUk"
                required
                type="text"
                placeholder="Значення (UK)"
                class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs"
              >
              <input
                v-model="val.valueEn"
                required
                type="text"
                placeholder="Value (EN)"
                class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs"
              >
            </div>

            <AppButton
              type="button"
              variant="ghost"
              size="sm"
              class="!text-red-500 hover:!bg-red-50 dark:hover:!bg-red-950/20"
              @click="removeAttributeValue(vIdx)"
            >
              Х
            </AppButton>
          </div>
        </div>
      </form>

      <template #footer>
        <AppButton
          variant="secondary"
          class="mr-2"
          @click="showAttributeModal = false"
        >
          Скасувати
        </AppButton>
        <AppButton
          variant="primary"
          @click="saveAttribute"
        >
          Зберегти
        </AppButton>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import api from "@/shared/services/api/apiClient";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";

const props = defineProps({
  attributes: { type: Array, required: true },
  categories: { type: Array, default: () => [] },
});

const emit = defineEmits(["refresh"]);

const showAttributeModal = ref(false);
const isEditing = ref(false);
const categorySearchQuery = ref("");

const attributeForm = ref({
  id: null,
  code: "",
  nameUk: "",
  nameEn: "",
  type: "text",
  values: [],
  categoryIds: [],
});

const getCategoryName = (id) => {
  const cat = props.categories.find(c => c.id === id);
  return cat ? cat.nameUk : `ID: ${id}`;
};

const buildIndentedCategories = (cats, parentId = null, depth = 0) => {
  let result = [];
  const children = cats.filter(c => c.parentId === parentId);
  children.forEach(c => {
    result.push({
      ...c,
      depth
    });
    result = result.concat(buildIndentedCategories(cats, c.id, depth + 1));
  });
  return result;
};

const indentedCategories = computed(() => {
  const rootIds = props.categories.filter(c => !c.parentId || !props.categories.some(parent => parent.id === c.parentId)).map(c => c.id);
  
  let result = [];
  const rootCats = props.categories.filter(c => rootIds.includes(c.id));
  rootCats.forEach(c => {
    result.push({ ...c, depth: 0 });
    result = result.concat(buildIndentedCategories(props.categories, c.id, 1));
  });

  const seen = new Set();
  return result.filter(item => {
    if (seen.has(item.id)) return false;
    seen.add(item.id);
    return true;
  });
});

const filteredFormCategories = computed(() => {
  if (!categorySearchQuery.value.trim()) {
    return indentedCategories.value;
  }
  const query = categorySearchQuery.value.toLowerCase();
  return props.categories.filter(c => 
    c.nameUk.toLowerCase().includes(query) || 
    c.nameEn.toLowerCase().includes(query)
  ).map(c => ({ ...c, depth: 0 }));
});

const openAddAttributeModal = () => {
  isEditing.value = false;
  categorySearchQuery.value = "";
  attributeForm.value = {
    id: null,
    code: "",
    nameUk: "",
    nameEn: "",
    type: "text",
    values: [],
    categoryIds: [],
  };
  showAttributeModal.value = true;
};

const openEditAttributeModal = (attr) => {
  isEditing.value = true;
  categorySearchQuery.value = "";

  const valuesCloned = (attr.values || []).map((v) => ({
    id: v.id,
    value: v.value || "",
    valueUk: v.valueUk || "",
    valueEn: v.valueEn || "",
  }));

  attributeForm.value = {
    id: attr.id,
    code: attr.code,
    nameUk: attr.nameUk,
    nameEn: attr.nameEn,
    type: attr.type,
    values: valuesCloned,
    categoryIds: attr.categoryIds || [],
  };
  showAttributeModal.value = true;
};

const saveAttribute = async () => {
  try {
    if (isEditing.value) {
      await api.put(
        `/admin/attributes/${attributeForm.value.id}`,
        attributeForm.value,
      );
    } else {
      await api.post("/admin/attributes", attributeForm.value);
    }
    showAttributeModal.value = false;
    emit("refresh");
  } catch (error) {
    console.error("Failed to save attribute:", error);
  }
};

const deleteAttribute = async (id) => {
  if (confirm("Ви впевнені, що хочете видалити цей атрибут?")) {
    try {
      await api.delete(`/admin/attributes/${id}`);
      emit("refresh");
    } catch (error) {
      console.error("Failed to delete attribute:", error);
    }
  }
};

const addAttributeValue = () => {
  attributeForm.value.values.push({
    id: null,
    value: "",
    valueUk: "",
    valueEn: "",
  });
};

const removeAttributeValue = (index) => {
  attributeForm.value.values.splice(index, 1);
};
</script>
