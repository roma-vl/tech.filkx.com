<template>
  <AppModal
    :model-value="modelValue"
    max-width="lg"
    :title="t('admin.roles.permission_modal.title')"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <template #header>
      <div class="flex items-center gap-4">
        <div
          class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 shadow-sm"
        >
          <ShieldCheckIcon class="w-6 h-6"/>
        </div>
        <div>
          <h2
            class="text-xl font-black text-gray-900 dark:text-white tracking-tight"
          >
            {{ t("admin.roles.permission_modal.title") }}
          </h2>
          <p class="text-xs font-bold text-gray-400 dark:text-gray-500">
            {{ t("admin.roles.permission_modal.subtitle") }}
          </p>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <div>
        <label
          class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
        >
          {{ t("admin.roles.permission_modal.name") }}
        </label>
        <AppInput
          :model-value="form.name"
          :placeholder="t('admin.roles.permission_modal.name_placeholder')"
          @update:model-value="
            $emit('update-field', { field: 'name', value: $event })
          "
        />
      </div>
      <div>
        <label
          class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
        >
          {{ t("admin.roles.permission_modal.slug") }}
        </label>
        <AppInput
          :model-value="form.slug"
          :placeholder="t('admin.roles.permission_modal.slug_placeholder')"
          @update:model-value="
            $emit('update-field', { field: 'slug', value: $event })
          "
        />
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label
            class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
          >
            {{ t("admin.roles.permission_modal.resource") }}
          </label>
          <AppInput
            :model-value="form.resource"
            :placeholder="
              t('admin.roles.permission_modal.resource_placeholder')
            "
            @update:model-value="
              $emit('update-field', { field: 'resource', value: $event })
            "
          />
        </div>
        <div>
          <label
            class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
          >
            {{ t("admin.roles.permission_modal.action") }}
          </label>
          <AppInput
            :model-value="form.action"
            :placeholder="t('admin.roles.permission_modal.action_placeholder')"
            @update:model-value="
              $emit('update-field', { field: 'action', value: $event })
            "
          />
        </div>
      </div>
      <div>
        <label
          class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
        >
          {{ t("admin.roles.permission_modal.description") }}
        </label>
        <AppTextarea
          :model-value="form.description"
          rows="3"
          :placeholder="
            t('admin.roles.permission_modal.description_placeholder')
          "
          @update:model-value="
            $emit('update-field', { field: 'description', value: $event })
          "
        />
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3 p-2">
        <AppButton
          variant="white"
          @click="$emit('update:modelValue', false)"
        >
          {{ t("admin.roles.permission_modal.cancel") }}
        </AppButton>
        <AppButton
          :loading="saving"
          :disabled="saving || !form.name || !form.slug"
          @click="$emit('save')"
        >
          {{
            saving
              ? t("admin.roles.permission_modal.creating")
              : t("admin.roles.permission_modal.create")
          }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import {ShieldCheckIcon} from "@heroicons/vue/24/outline";
import {useI18n} from "vue-i18n";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";

const {t} = useI18n();

defineProps({
  modelValue: Boolean,
  saving: Boolean,
  form: {
    type: Object,
    required: true,
  },
});

defineEmits(["update:modelValue", "save", "update-field"]);
</script>
