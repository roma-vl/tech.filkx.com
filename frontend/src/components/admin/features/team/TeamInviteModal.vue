<template>
  <AppModal
    :model-value="isOpen"
    max-width="xl"
    :title="t('admin.team.invite.title')"
    @update:model-value="$emit('close')"
  >
    <template #header>
      <div class="flex items-center gap-4">
        <div
          class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600"
        >
          <UserPlusIcon class="w-7 h-7" />
        </div>
        <div>
          <h3
            class="text-2xl font-black text-gray-900 dark:text-white tracking-tight leading-tight"
          >
            {{ t("admin.team.invite.title") }}
          </h3>
          <p class="text-sm font-bold text-gray-400 dark:text-gray-500 mt-1">
            {{ t("admin.team.invite.description") }}
          </p>
        </div>
      </div>
    </template>

    <div class="p-1">
      <form
        class="space-y-6"
        @submit.prevent="$emit('submit')"
      >
        <div>
          <label
            class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
          >
            {{ t("admin.team.invite.full_name") }}
          </label>
          <AppInput
            :model-value="form.name"
            required
            placeholder="Ivan Ivanov"
            @update:model-value="$emit('update:name', $event)"
          />
        </div>

        <div>
          <label
            class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
          >
            {{ t("admin.team.invite.email_address") }}
          </label>
          <AppInput
            :model-value="form.email"
            type="email"
            required
            placeholder="ivan@filkx.com"
            @update:model-value="$emit('update:email', $event)"
          />
        </div>

        <div>
          <label
            class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
          >
            {{ t("admin.team.invite.assigned_role") }}
          </label>
          <AppSelect
            :model-value="form.roleId"
            :options="roleOptions"
            required
            @update:model-value="$emit('update:roleId', $event)"
          />
        </div>

        <div class="pt-6 flex justify-end gap-3">
          <AppButton
            variant="white"
            @click="$emit('close')"
          >
            {{ t("admin.team.invite.cancel") }}
          </AppButton>
          <AppButton
            type="submit"
            variant="primary"
            :loading="loading"
            :disabled="loading"
          >
            {{
              loading
                ? t("admin.team.invite.sending")
                : t("admin.team.invite.send_invite")
            }}
          </AppButton>
        </div>
      </form>
    </div>
  </AppModal>
</template>

<script setup>
import { ArrowPathIcon, UserPlusIcon } from "@heroicons/vue/24/outline";
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  isOpen: Boolean,
  form: {
    type: Object,
    required: true,
  },
  roles: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

defineEmits([
  "close",
  "submit",
  "update:name",
  "update:email",
  "update:roleId",
]);

const roleOptions = computed(() => {
  return [
    { id: "", name: t("admin.team.invite.select_role") },
    ...props.roles.map((role) => ({
      id: role.id,
      name: role.name,
    })),
  ];
});
</script>
