<template>
  <AppModal
    :model-value="modelValue"
    max-width="2xl"
    :title="isEditing ? t('admin.roles.modal.edit_title') : t('admin.roles.modal.create_title')"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <template #header>
      <div class="flex items-center gap-4">
        <div
          class="p-2.5 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 shadow-sm"
        >
          <ShieldCheckIcon class="w-6 h-6"/>
        </div>
        <div>
          <h2
            class="text-xl font-black text-gray-900 dark:text-white tracking-tight"
          >
            {{
              isEditing
                ? t("admin.roles.modal.edit_title")
                : t("admin.roles.modal.create_title")
            }}
          </h2>
          <p class="text-xs font-bold text-gray-400 dark:text-gray-500">
            {{ t("admin.roles.modal.subtitle") }}
          </p>
        </div>
      </div>
    </template>

    <div class="space-y-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
          <label
            class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
          >
            {{ t("admin.roles.modal.role_name") }}
          </label>
          <AppInput
            v-model="form.name"
            :placeholder="t('admin.roles.modal.role_name_placeholder')"
            class="w-full"
          />
        </div>
        <div class="md:col-span-2">
          <label
            class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest px-1"
          >
            {{ t("admin.roles.modal.description") }}
          </label>
          <AppTextarea
            v-model="form.description"
            rows="3"
            :placeholder="t('admin.roles.modal.description_placeholder')"
            class="w-full"
          />
        </div>
      </div>

      <div class="space-y-6">
        <div
          class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/50 pb-4"
        >
          <label
            class="text-[10px] font-black uppercase text-gray-400 tracking-widest px-1"
          >
            {{ t("admin.roles.modal.permissions_strategy") }}
          </label>
          <div class="flex items-center gap-4">
            <span class="text-xs font-bold text-gray-400">{{ selectedPermissions.length }}
              {{ t("admin.roles.modal.selected") }}</span>
            <button
              class="text-xs font-black text-primary-600 uppercase tracking-widest hover:text-primary-700 transition-colors"
              @click="$emit('toggle-all')"
            >
              {{
                selectedPermissions.length === availablePermissions.length
                  ? t("admin.roles.modal.deselect_all")
                  : t("admin.roles.modal.select_all")
              }}
            </button>
          </div>
        </div>

        <div class="space-y-6">
          <div
            v-for="(group, resource) in groupedPermissions"
            :key="resource"
            class="bg-gray-50 dark:bg-gray-900/30 p-6 rounded-3xl border border-gray-100 dark:border-gray-700/50 space-y-5"
          >
            <div
              class="flex items-center gap-3 font-black text-xs text-gray-900 dark:text-white uppercase tracking-widest"
            >
              <div
                class="p-1.5 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm"
              >
                <component
                  :is="getResourceIcon(resource)"
                  class="w-4 h-4 text-primary-500"
                />
              </div>
              {{ resource }}
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <label
                v-for="perm in group"
                :key="perm.slug"
                class="flex flex-col p-4 rounded-2xl border transition-all duration-300 cursor-pointer shadow-sm relative overflow-hidden group h-full"
                :class="
                  selectedPermissions.includes(perm.slug)
                    ? 'bg-primary-50/50 dark:bg-primary-900/10 border-primary-200 dark:border-primary-800 shadow-primary-500/5'
                    : 'bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 hover:border-primary-200 dark:hover:border-primary-800'
                "
              >
                <div class="flex items-start gap-3">
                  <div
                    class="w-5 h-5 rounded-lg border flex items-center justify-center transition-all shrink-0 mt-0.5"
                    :class="
                      selectedPermissions.includes(perm.slug)
                        ? 'bg-primary-600 border-primary-600 text-white'
                        : 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700'
                    "
                  >
                    <CheckIcon
                      v-if="selectedPermissions.includes(perm.slug)"
                      class="w-3.5 h-3.5 font-bold"
                    />
                  </div>

                  <div class="flex-1 min-w-0">
                    <p
                      class="text-[13px] font-black text-gray-900 dark:text-white group-hover:text-primary-500 transition-colors uppercase tracking-tight"
                    >
                      {{ perm.name }}
                    </p>
                    <p class="text-[10px] font-bold text-gray-500 mt-0.5">
                      {{ perm.action }}
                    </p>
                    <p
                      v-if="perm.description"
                      class="text-[11px] font-medium text-gray-400 mt-2 line-clamp-2 leading-relaxed"
                    >
                      {{ perm.description }}
                    </p>
                  </div>
                </div>

                <input
                  :checked="selectedPermissions.includes(perm.slug)"
                  type="checkbox"
                  :value="perm.slug"
                  class="sr-only"
                  @change="
                    $emit(
                      'update:selectedPermissions',
                      $event.target.checked
                        ? [...selectedPermissions, perm.slug]
                        : selectedPermissions.filter((s) => s !== perm.slug),
                    )
                  "
                >
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3 p-2">
        <AppButton
          variant="white"
          @click="$emit('update:modelValue', false)"
        >
          {{ t("admin.roles.modal.cancel") }}
        </AppButton>
        <AppButton
          variant="primary"
          :loading="saving"
          :disabled="saving || !form.name"
          @click="$emit('save')"
        >
          {{
            saving
              ? isEditing
                ? t("admin.roles.modal.saving_update")
                : t("admin.roles.modal.saving_create")
              : isEditing
                ? t("admin.roles.modal.save_update")
                : t("admin.roles.modal.save_create")
          }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import {computed} from "vue";
import {useI18n} from "vue-i18n";
import {
  CheckIcon,
  CreditCardIcon,
  PlayIcon,
  QueueListIcon,
  ShieldCheckIcon,
  UserGroupIcon,
  VideoCameraIcon,
} from "@heroicons/vue/24/outline";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";

const {t} = useI18n();

const props = defineProps({
  modelValue: Boolean,
  isEditing: Boolean,
  saving: Boolean,
  form: {
    type: Object,
    required: true,
  },
  selectedPermissions: {
    type: Array,
    required: true,
  },
  availablePermissions: {
    type: Array,
    required: true,
  },
});

defineEmits([
  "update:modelValue",
  "save",
  "toggle-all",
  "update:selectedPermissions",
]);

const groupedPermissions = computed(() => {
  if (!props.availablePermissions || !Array.isArray(props.availablePermissions))
    return {};
  return props.availablePermissions.reduce((acc, perm) => {
    if (!perm) return acc;
    const rawResource = perm.resource || "other";
    const resString = typeof rawResource === "string" ? rawResource : "other";
    const resource = resString.charAt(0).toUpperCase() + resString.slice(1);
    if (!acc[resource]) acc[resource] = [];
    acc[resource].push(perm);
    return acc;
  }, {});
});

const getResourceIcon = (resource) => {
  if (!resource || typeof resource !== "string") return ShieldCheckIcon;
  const r = resource.toLowerCase();
  if (r.includes("video")) return VideoCameraIcon;
  if (r.includes("playlist")) return QueueListIcon;
  if (r.includes("stream")) return PlayIcon;
  if (r.includes("billing") || r.includes("payment")) return CreditCardIcon;
  if (r.includes("user") || r.includes("admin")) return UserGroupIcon;
  return ShieldCheckIcon;
};
</script>
