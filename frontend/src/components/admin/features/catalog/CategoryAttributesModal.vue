<template>
  <AppModal
    :model-value="modelValue"
    :title="`Характеристики категорії: ${category?.nameUk || ''}`"
    max-width="3xl"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div class="space-y-6">
      <!-- VIEW 1: ATTRIBUTES LIST & BINDINGS -->
      <div
        v-if="currentView === 'list'"
        class="space-y-6"
      >
        <!-- Top Toolbar: Bind Existing / Create New -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-150 dark:border-gray-800">
          <!-- Bind existing attribute -->
          <div class="flex-1 flex items-end gap-2">
            <AppSelect
              v-model="selectedAttrToBind"
              label="Додати наявну характеристику"
              placeholder="Оберіть характеристику..."
              :options="bindableAttributes"
              option-value="id"
              option-label="nameUk"
              class="max-w-xs"
            />
            <AppButton
              type="button"
              variant="secondary"
              class="h-[46px] whitespace-nowrap"
              :disabled="!selectedAttrToBind"
              @click="bindAttribute"
            >
              Додати
            </AppButton>
          </div>

          <!-- Create new attribute button -->
          <AppButton
            type="button"
            variant="primary"
            class="sm:self-end h-[46px]"
            @click="goToCreate"
          >
            + Створити нову
          </AppButton>
        </div>

        <!-- Category Attributes Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    ID
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Код
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Назва (UK)
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Тип
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Варіанти
                  </th>
                  <th class="px-4 py-3 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Дії
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="attr in categoryAttributes"
                  :key="attr.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                >
                  <td class="px-4 py-3 text-sm font-bold text-gray-950 dark:text-white">
                    {{ attr.id }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-mono font-bold">
                    {{ attr.code }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-950 dark:text-white">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1.5">
                      <span class="font-semibold">{{ attr.nameUk }}</span>
                      <span
                        v-if="attr.isInherited"
                        class="inline-flex items-center w-fit px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400"
                        :title="`Ця характеристика є успадкованою від категорії: ${attr.sourceCategoryName}`"
                      >
                        Успадковано від {{ attr.sourceCategoryName }}
                      </span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-sm">
                    <span
                      :class="{
                        'bg-purple-155 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400': attr.type === 'select',
                        'bg-blue-155 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': attr.type === 'color',
                        'bg-gray-155 text-gray-800 dark:bg-gray-750 dark:text-gray-300': attr.type === 'text',
                      }"
                      class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider"
                    >
                      {{ attr.type }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
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
                  <td class="px-4 py-3 text-right text-sm font-medium">
                    <div
                      v-if="attr.isInherited"
                      class="flex justify-end items-center gap-1 text-[11px] text-gray-400 dark:text-gray-500 italic pr-2"
                    >
                      <svg
                        class="w-3.5 h-3.5 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                        />
                      </svg>
                      Керування в {{ attr.sourceCategoryName }}
                    </div>
                    <div
                      v-else
                      class="flex justify-end gap-1.5"
                    >
                      <AppButton
                        variant="ghost"
                        size="sm"
                        class="!p-1.5 text-blue-600 dark:text-blue-400"
                        title="Редагувати"
                        @click="goToEdit(attr)"
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
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                          />
                        </svg>
                      </AppButton>
                      <AppButton
                        variant="ghost"
                        size="sm"
                        class="!p-1.5 text-amber-600 dark:text-amber-400"
                        title="Відв'язати від категорії"
                        @click="unbindAttribute(attr)"
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
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                          />
                        </svg>
                      </AppButton>
                      <AppButton
                        variant="ghost"
                        size="sm"
                        class="!p-1.5 text-red-600 dark:text-red-400"
                        title="Видалити повністю"
                        @click="deleteAttribute(attr.id)"
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
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          />
                        </svg>
                      </AppButton>
                    </div>
                  </td>
                </tr>
                <tr v-if="categoryAttributes.length === 0">
                  <td
                    colspan="6"
                    class="px-4 py-8 text-center text-gray-500 dark:text-gray-400 italic"
                  >
                    Немає характеристик, прив'язаних до цієї категорії.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- VIEW 2: CREATE / EDIT ATTRIBUTE FORM -->
      <div
        v-else
        class="space-y-6"
      >
        <div class="flex items-center justify-between border-b pb-3 border-gray-150 dark:border-gray-800">
          <h4 class="font-bold text-gray-950 dark:text-white">
            {{ isEditing ? 'Редагувати характеристику' : 'Створити характеристику' }}
          </h4>
          <AppButton
            type="button"
            variant="text"
            size="sm"
            class="flex items-center gap-1"
            @click="goToList"
          >
            ← Назад до списку
          </AppButton>
        </div>

        <form
          class="grid grid-cols-1 gap-4"
          @submit.prevent="saveAttribute"
        >
          <AppInput
            v-model="attributeForm.code"
            required
            label="Код атрибуту (системний)"
            placeholder="напр. color, ram, screen_size"
          />

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
          </div>

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
            v-if="attributeForm.type === 'select' || attributeForm.type === 'color'"
            class="space-y-2 mt-2 pt-4 border-t border-gray-150 dark:border-gray-800"
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
              class="flex gap-2 items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border border-gray-200 dark:border-gray-800"
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
                  class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-2 py-1 text-xs text-gray-950 dark:text-white"
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
                  class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-2 py-1 text-xs text-gray-950 dark:text-white"
                >
                <input
                  v-model="val.valueEn"
                  required
                  type="text"
                  placeholder="Value (EN)"
                  class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg px-2 py-1 text-xs text-gray-950 dark:text-white"
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
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-2">
        <AppButton
          variant="secondary"
          @click="$emit('update:modelValue', false)"
        >
          Закрити
        </AppButton>
        <AppButton
          v-if="currentView === 'form'"
          variant="primary"
          @click="saveAttribute"
        >
          Зберегти характеристику
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, computed } from "vue";
import api from "@/services/api";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  category: { type: Object, default: null },
  categories: { type: Array, default: () => [] },
  attributes: { type: Array, required: true },
});

const emit = defineEmits(["update:modelValue", "refresh"]);

const currentView = ref("list"); // 'list' | 'form'
const isEditing = ref(false);
const selectedAttrToBind = ref("");

const attributeForm = ref({
  id: null,
  code: "",
  nameUk: "",
  nameEn: "",
  type: "text",
  values: [],
  categoryIds: [],
});

const categoryAncestors = computed(() => {
  if (!props.category) return [];
  const ancestors = [];
  let currentParentId = props.category.parentId;
  while (currentParentId) {
    const parent = props.categories.find(c => c.id === currentParentId);
    if (parent) {
      ancestors.push(parent);
      currentParentId = parent.parentId;
    } else {
      currentParentId = null;
    }
  }
  return ancestors;
});

// Attributes assigned to the active category or inherited from ancestors
const categoryAttributes = computed(() => {
  if (!props.category) return [];
  
  // Direct attributes
  const own = props.attributes
    .filter(a => a.categoryIds?.includes(props.category.id))
    .map(a => ({
      ...a,
      isInherited: false,
      sourceCategoryName: ""
    }));

  // Inherited attributes
  const ancestorIds = categoryAncestors.value.map(a => a.id);
  const inherited = props.attributes
    .filter(a => !a.categoryIds?.includes(props.category.id) && a.categoryIds?.some(id => ancestorIds.includes(id)))
    .map(a => {
      const sourceCatId = a.categoryIds?.find(id => ancestorIds.includes(id));
      const sourceCat = categoryAncestors.value.find(c => c.id === sourceCatId);
      return {
        ...a,
        isInherited: true,
        sourceCategoryName: sourceCat ? sourceCat.nameUk : "Батьківська категорія"
      };
    });

  return [...own, ...inherited];
});

// Attributes NOT assigned to the active category or its ancestors
const bindableAttributes = computed(() => {
  if (!props.category) return [];
  const ancestorIds = categoryAncestors.value.map(a => a.id);
  return props.attributes.filter(a => 
    !a.categoryIds?.includes(props.category.id) && 
    !a.categoryIds?.some(id => ancestorIds.includes(id))
  );
});

const goToList = () => {
  currentView.value = "list";
};

const goToCreate = () => {
  isEditing.value = false;
  attributeForm.value = {
    id: null,
    code: "",
    nameUk: "",
    nameEn: "",
    type: "text",
    values: [],
    categoryIds: [props.category.id],
  };
  currentView.value = "form";
};

const goToEdit = (attr) => {
  isEditing.value = true;
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
  currentView.value = "form";
};

const bindAttribute = async () => {
  if (!selectedAttrToBind.value) return;
  const attr = props.attributes.find(a => a.id === Number(selectedAttrToBind.value));
  if (!attr) return;

  const payload = {
    code: attr.code,
    nameUk: attr.nameUk,
    nameEn: attr.nameEn,
    type: attr.type,
    values: (attr.values || []).map(v => ({
      id: v.id,
      value: v.value || "",
      valueUk: v.valueUk || "",
      valueEn: v.valueEn || "",
    })),
    categoryIds: [...(attr.categoryIds || []), props.category.id],
  };

  try {
    await api.put(`/admin/attributes/${attr.id}`, payload);
    selectedAttrToBind.value = "";
    emit("refresh");
  } catch (error) {
    console.error("Failed to bind attribute:", error);
  }
};

const unbindAttribute = async (attr) => {
  const payload = {
    code: attr.code,
    nameUk: attr.nameUk,
    nameEn: attr.nameEn,
    type: attr.type,
    values: (attr.values || []).map(v => ({
      id: v.id,
      value: v.value || "",
      valueUk: v.valueUk || "",
      valueEn: v.valueEn || "",
    })),
    categoryIds: (attr.categoryIds || []).filter(id => id !== props.category.id),
  };

  try {
    await api.put(`/admin/attributes/${attr.id}`, payload);
    emit("refresh");
  } catch (error) {
    console.error("Failed to unbind attribute:", error);
  }
};

const deleteAttribute = async (id) => {
  if (confirm("Ви впевнені, що хочете остаточно видалити цей атрибут із системи?")) {
    try {
      await api.delete(`/admin/attributes/${id}`);
      emit("refresh");
    } catch (error) {
      console.error("Failed to delete attribute:", error);
    }
  }
};

const saveAttribute = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/admin/attributes/${attributeForm.value.id}`, attributeForm.value);
    } else {
      await api.post("/admin/attributes", attributeForm.value);
    }
    goToList();
    emit("refresh");
  } catch (error) {
    console.error("Failed to save attribute:", error);
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
