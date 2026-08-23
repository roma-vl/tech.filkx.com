<template>
  <AppModal
    :model-value="isOpen"
    max-width="3xl"
    :title="
      isEditing
        ? $t('admin.users.modal.editTitle')
        : $t('admin.users.modal.addTitle')
    "
    @update:model-value="(val) => !val && $emit('close')"
  >
    <div class="pt-2">
      <div class="space-y-6">
        <div>
          <h3
            class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4"
          >
            {{ $t("admin.users.modal.basicInfo") || "Basic Information" }}
          </h3>
          <div class="grid grid-cols-1 gap-4">
            <AppInput
              v-model="internalForm.name"
              :label="$t('admin.users.modal.fullName')"
            />
            <AppInput
              v-model="internalForm.email"
              type="email"
              :label="$t('admin.users.modal.email')"
            />
            <div v-if="!isEditing">
              <AppInput
                v-model="internalForm.password"
                type="password"
                :label="$t('admin.users.modal.password')"
              />
            </div>
            <div v-if="isEditing">
              <AppSelect
                v-model="internalForm.status"
                :label="$t('admin.users.modal.status')"
                :options="statusOptions"
              />
            </div>
          </div>
        </div>

        <!-- Roles -->
        <div>
          <label
            class="block text-xs font-black text-primary-600 uppercase tracking-widest mb-3 ml-1"
          >
            {{ $t("admin.users.modal.assignRoles") }}
          </label>
          <div
            class="flex flex-wrap gap-2 p-4 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700"
          >
            <label
              v-for="role in availableRoles"
              :key="role.slug"
              class="group flex items-center gap-2 px-3 py-1.5 rounded-xl border cursor-pointer transition-all"
              :class="
                internalForm.roles.includes(role.slug)
                  ? 'bg-primary-50 border-primary-200 text-primary-700 ring-2 ring-primary-100 dark:bg-primary-900/20 dark:border-primary-800'
                  : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 text-gray-500 hover:border-gray-300'
              "
            >
              <input
                v-model="internalForm.roles"
                type="checkbox"
                :value="role.slug"
                class="sr-only"
              />
              <span class="text-[11px] font-bold">{{ role.name }}</span>
            </label>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <AppButton variant="secondary" @click="$emit('close')">
          {{ $t("admin.users.modal.cancel") }}
        </AppButton>
        <AppButton variant="primary" :loading="saving" @click="handleSave">
          {{
            saving
              ? $t("admin.users.modal.saving")
              : $t("admin.users.modal.save")
          }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  isOpen: Boolean,
  isEditing: Boolean,
  saving: Boolean,
  form: {
    type: Object,
    required: true,
  },
  availableRoles: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "save"]);

const internalForm = ref({ ...props.form });

const statusOptions = computed(() => {
  const options = [
    { id: "active", name: t("admin.users.status.active") },
    { id: "suspended", name: t("admin.users.status.suspended") },
  ];

  if (internalForm.value.status === "deleted") {
    options.push({
      id: "deleted",
      name:
        t("admin.users.status.deleted") +
        " (" +
        t("admin.users.modal.softDeleteNote") +
        ")",
      disabled: true,
    });
  }

  return options;
});

watch(
  () => props.form,
  (newForm) => {
    internalForm.value = { ...newForm };
  },
  { deep: true, immediate: true },
);

const handleSave = () => {
  emit("save", { ...internalForm.value });
};
</script>
