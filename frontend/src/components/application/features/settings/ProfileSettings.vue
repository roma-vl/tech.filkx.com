<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-200"
  >
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <div
          class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center"
        >
          <UserIcon class="w-5 h-5 text-primary-600 dark:text-primary-400" />
        </div>
        <div>
          <h3 class="text-lg font-semibold">
            {{ $t("settings.profile") }}
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $t("settings.profile_description") }}
          </p>
        </div>
      </div>
    </div>

    <div class="p-6 space-y-6">
      <div class="flex items-center space-x-6">
        <div class="relative group">
          <div
            v-if="localForm.avatarUrl || previewAvatar"
            class="w-24 h-24 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 ring-4 ring-gray-100 dark:ring-gray-700 transition-all duration-200 group-hover:ring-primary-500/50"
          >
            <img
              :src="previewAvatar || localForm.avatarUrl"
              alt="Avatar"
              class="w-full h-full object-cover"
            >
          </div>
          <div
            v-else
            class="w-24 h-24 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white text-3xl font-bold ring-4 ring-gray-100 dark:ring-gray-700 transition-all duration-200 group-hover:ring-primary-500/50"
          >
            {{ getInitials(localForm.name) }}
          </div>
          <AppButton
            v-if="localForm.avatarUrl"
            variant="danger"
            size="sm"
            class="!absolute -top-1 -right-1 !w-8 !h-8 !p-0 !rounded-full flex items-center justify-center shadow-lg transition-all duration-200 hover:scale-110"
            :title="$t('settings.delete_avatar')"
            @click="$emit('delete-avatar')"
          >
            <TrashIcon class="w-4 h-4" />
          </AppButton>
        </div>
        <div class="flex flex-col gap-2">
          <input
            ref="avatarInput"
            type="file"
            accept="image/jpeg,image/png,image/jpg"
            class="hidden"
            @change="handleAvatarChange"
          >
          <AppButton
            variant="primary"
            @click="$refs.avatarInput.click()"
          >
            {{
              localForm.avatarUrl
                ? $t("settings.change_avatar")
                : $t("settings.upload_avatar")
            }}
          </AppButton>
          <AppButton
            v-if="selectedAvatar"
            :disabled="uploadingAvatar"
            variant="success"
            :loading="uploadingAvatar"
            @click="$emit('upload-avatar', selectedAvatar)"
          >
            {{ $t("settings.save_avatar") }}
          </AppButton>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <AppInput
          v-model="localForm.name"
          :label="$t('settings.name') + ' *'"
          :error="errors.name"
        >
          <template #prepend>
            <UserIcon class="h-5 w-5 text-gray-400" />
          </template>
        </AppInput>

        <AppInput
          v-model="localForm.email"
          type="email"
          :label="$t('settings.email') + ' *'"
          :error="errors.email"
        >
          <template #prepend>
            <svg
              class="h-5 w-5 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
          </template>
        </AppInput>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <AppSelect
          v-model="localForm.timezone"
          :label="$t('settings.timezone')"
          :options="timezoneOptions"
          option-label="label"
          option-value="value"
          searchable
          search-placeholder="Search timezone..."
        >
          <template #prepend>
            <ClockIcon class="h-5 w-5 text-gray-400" />
          </template>
        </AppSelect>

        <AppSelect
          v-model="localForm.locale"
          :label="$t('settings.language')"
          :options="languageOptions"
          option-label="label"
          option-value="value"
        >
          <template #prepend>
            <svg
              class="h-5 w-5 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"
              />
            </svg>
          </template>
        </AppSelect>
      </div>

      <AppTextarea
        v-model="localForm.description"
        :label="$t('settings.description')"
        :error="errors.description"
        rows="3"
        maxlength="500"
      >
        <template #hint>
          <div class="flex justify-end">
            {{ localForm.description?.length || 0 }} / 500
          </div>
        </template>
      </AppTextarea>

      <AppButton
        :disabled="savingProfile"
        :loading="savingProfile"
        variant="primary"
        @click="$emit('save', localForm)"
      >
        {{ $t("settings.save_changes") }}
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import AppInput from "@/components/application/ui/Form/AppInput.vue";
import AppSelect from "@/components/application/ui/Form/AppSelect.vue";
import AppTextarea from "@/components/application/ui/Form/AppTextarea.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import UserIcon from "@/components/Icon/UserIcon.vue";
import TrashIcon from "@/components/Icon/TrashIcon.vue";
import ClockIcon from "@/components/Icon/ClockIcon.vue";

const props = defineProps({
  profileForm: {
    type: Object,
    required: true,
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  savingProfile: {
    type: Boolean,
    default: false,
  },
  uploadingAvatar: {
    type: Boolean,
    default: false,
  },
  previewAvatar: {
    type: String,
    default: null,
  },
});

const emit = defineEmits([
  "save",
  "upload-avatar",
  "delete-avatar",
  "avatar-change",
]);

const localForm = ref({ ...props.profileForm });
const selectedAvatar = ref(null);
const avatarInput = ref(null);

const getTimezoneOptions = () => {
  const timezones = Intl.supportedValuesOf("timeZone");
  const options = timezones.map((tz) => {
    let offset = "";
    try {
      const now = new Date();
      const parts = new Intl.DateTimeFormat("en-US", {
        timeZone: tz,
        timeZoneName: "longOffset",
      }).formatToParts(now);
      const offsetPart = parts.find((p) => p.type === "timeZoneName");
      offset = offsetPart ? offsetPart.value : "";
    } catch (e) {
      offset = "";
    }

    return {
      value: tz,
      label: `(${offset}) ${tz.replace(/_/g, " ")}`,
      offset: offset,
    };
  });

  options.sort((a, b) => {
    if (a.offset === b.offset) {
      return a.label.localeCompare(b.label);
    }

    return a.label.localeCompare(b.label);
  });

  const kyivIndex = options.findIndex(
    (o) => o.value === "Europe/Kyiv" || o.value === "Europe/Kiev",
  );
  if (kyivIndex > -1) {
    const kyiv = options.splice(kyivIndex, 1)[0];
    kyiv.value = "Europe/Kyiv";
    kyiv.label = kyiv.label.replace("Europe/Kiev", "Europe/Kyiv");
    options.unshift(kyiv);
  } else {
    options.unshift({
      value: "Europe/Kyiv",
      label: "(GMT+02:00) Europe/Kyiv",
    });
  }

  return options;
};

const timezoneOptions = getTimezoneOptions();

const languageOptions = [
  { value: "uk", label: "Українська" },
  { value: "en", label: "English" },
];

watch(
  () => props.profileForm,
  (newVal) => {
    localForm.value = { ...newVal };
  },
  { deep: true },
);

const getInitials = (name) => {
  if (!name) return "U";
  return name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
};

const handleAvatarChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedAvatar.value = file;
    emit("avatar-change", file);
  }
};
</script>
