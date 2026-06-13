<template>
  <AppModal
    :model-value="modelValue"
    :title="category ? 'Редагувати категорію' : 'Нова категорія'"
    max-width="md"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <form class="space-y-4" @submit.prevent="saveCategory">
      <AppInput
        v-model="categoryForm.nameUk"
        label="Назва (УК) *"
        placeholder="напр. Огляди"
        required
      />

      <AppInput
        v-model="categoryForm.nameEn"
        label="Name (EN) *"
        placeholder="e.g. Reviews"
        required
      />

      <AppTextarea
        v-model="categoryForm.descriptionUk"
        label="Опис (УК)"
        placeholder="Опис категорії українською..."
        rows="2"
      />

      <AppTextarea
        v-model="categoryForm.descriptionEn"
        label="Description (EN)"
        placeholder="Description in English..."
        rows="2"
      />

      <AppInput
        v-model.number="categoryForm.order"
        type="number"
        label="Порядок"
        placeholder="0"
      />

      <div class="flex justify-end gap-3 pt-2">
        <AppButton
          variant="white"
          class="!px-4 !py-2 text-sm"
          type="button"
          @click="$emit('update:modelValue', false)"
        >
          Скасувати
        </AppButton>
        <AppButton
          variant="primary"
          :loading="saving"
          class="!px-5 !py-2 text-sm !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046]"
          type="submit"
        >
          Зберегти
        </AppButton>
      </div>
    </form>
  </AppModal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppTextarea from "@/components/admin/ui/AppTextarea.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

const props = defineProps({
  modelValue: Boolean,
  category: Object
});

const emit = defineEmits(["update:modelValue", "refresh"]);

const toast = useToast();
const saving = ref(false);

const defaultCategoryForm = () => ({
  nameUk: "",
  nameEn: "",
  descriptionUk: "",
  descriptionEn: "",
  order: 0
});

const categoryForm = ref(defaultCategoryForm());

watch(
  () => props.category,
  (newCat) => {
    if (newCat) {
      categoryForm.value = {
        nameUk: newCat.nameUk || "",
        nameEn: newCat.nameEn || "",
        descriptionUk: newCat.descriptionUk || "",
        descriptionEn: newCat.descriptionEn || "",
        order: newCat.order || 0
      };
    } else {
      categoryForm.value = defaultCategoryForm();
    }
  },
  { immediate: true }
);

const saveCategory = async () => {
  if (!categoryForm.value.nameUk || !categoryForm.value.nameEn) {
    toast.warning("Назва обов'язкова для обох мов");
    return;
  }
  saving.value = true;
  try {
    if (props.category) {
      await api.put(`/admin/blog/categories/${props.category.id}`, categoryForm.value);
    } else {
      await api.post("/admin/blog/categories", categoryForm.value);
    }
    toast.success("Категорію збережено");
    emit("update:modelValue", false);
    emit("refresh");
  } catch (e) {
    toast.error("Помилка збереження категорії");
  } finally {
    saving.value = false;
  }
};
</script>
