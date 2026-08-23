<template>
  <AppModal
    :model-value="modelValue"
    :title="
      tag
        ? t('admin.blog.tags.modal.editTitle')
        : t('admin.blog.tags.modal.newTitle')
    "
    max-width="sm"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <form class="space-y-4" @submit.prevent="saveTag">
      <AppInput
        v-model="tagForm.nameUk"
        :label="t('admin.blog.tags.modal.nameUkLabel')"
        :placeholder="t('admin.blog.tags.modal.nameUkPlaceholder')"
        required
      />

      <AppInput
        v-model="tagForm.nameEn"
        :label="t('admin.blog.tags.modal.nameEnLabel')"
        :placeholder="t('admin.blog.tags.modal.nameEnPlaceholder')"
        required
      />

      <div class="flex justify-end gap-3 pt-2">
        <AppButton
          variant="white"
          class="!px-4 !py-2 text-sm"
          type="button"
          @click="$emit('update:modelValue', false)"
        >
          {{ t("admin.blog.tags.modal.cancel") }}
        </AppButton>
        <AppButton
          variant="primary"
          :loading="saving"
          class="!px-5 !py-2 text-sm !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046]"
          type="submit"
        >
          {{ t("admin.blog.tags.modal.save") }}
        </AppButton>
      </div>
    </form>
  </AppModal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import api from "@/shared/services/api/apiClient";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

const props = defineProps({
  modelValue: Boolean,
  tag: Object,
});

const emit = defineEmits(["update:modelValue", "refresh"]);

const toast = useToast();
const { t } = useI18n();
const saving = ref(false);

const defaultTagForm = () => ({
  nameUk: "",
  nameEn: "",
});

const tagForm = ref(defaultTagForm());

watch(
  () => props.tag,
  (newTag) => {
    if (newTag) {
      tagForm.value = {
        nameUk: newTag.nameUk || "",
        nameEn: newTag.nameEn || "",
      };
    } else {
      tagForm.value = defaultTagForm();
    }
  },
  { immediate: true },
);

const saveTag = async () => {
  if (!tagForm.value.nameUk || !tagForm.value.nameEn) {
    toast.warning(t("admin.blog.tags.modal.alerts.nameRequired"));
    return;
  }
  saving.value = true;
  try {
    if (props.tag) {
      await api.put(`/admin/blog/tags/${props.tag.id}`, tagForm.value);
    } else {
      await api.post("/admin/blog/tags", tagForm.value);
    }
    toast.success(t("admin.blog.tags.modal.alerts.saved"));
    emit("update:modelValue", false);
    emit("refresh");
  } catch (e) {
    toast.error(t("admin.blog.tags.modal.alerts.saveError"));
  } finally {
    saving.value = false;
  }
};
</script>
