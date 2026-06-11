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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-2">
      <!-- Left Column: Basic Info & Roles -->
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
              >
              <span class="text-[11px] font-bold">{{ role.name }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Right Column: Overrides -->
      <div
        v-if="isEditing && internalForm.subscription"
        class="space-y-6"
      >
        <!-- Features Snapshot -->
        <div
          class="p-5 bg-amber-50/50 dark:bg-amber-900/5 rounded-2xl border border-amber-100 dark:border-amber-900/20 overflow-hidden"
        >
          <div class="flex items-center justify-between mb-2">
            <h3
              class="text-xs font-black text-amber-800 dark:text-amber-400 uppercase tracking-widest"
            >
              {{ $t("admin.users.modal.jsonOverrides") }}
            </h3>
            <button
              class="text-[10px] font-bold text-amber-600 underline"
              @click="toggleMode('features')"
            >
              {{
                editModes.features === "visual"
                  ? "Switch to JSON"
                  : "Switch to Visual"
              }}
            </button>
          </div>

          <div
            class="p-2 mb-4 bg-amber-100/50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-900/40"
          >
            <p
              class="text-[10px] leading-tight text-amber-900 dark:text-amber-200 font-medium"
            >
              {{ $t("admin.users.modal.sensitiveDataWarning") }}
            </p>
          </div>

          <div
            v-if="editModes.features === 'visual'"
            class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar"
          >
            <!-- Feature Toggles -->
            <div
              v-if="featuresData.features"
              class="grid grid-cols-1 sm:grid-cols-2 gap-3"
            >
              <div
                v-for="key in booleanFeatures"
                :key="key"
                class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700"
              >
                <span
                  class="text-[11px] font-medium text-gray-600 dark:text-gray-400 capitalize"
                >{{ key }}</span>
                <AppToggle v-model="featuresData.features[key]" />
              </div>
            </div>

            <!-- Platforms Selection -->
            <div
              v-if="featuresData.platforms"
              class="pt-2 border-t border-amber-100 dark:border-amber-900/20"
            >
              <h4
                class="text-[10px] font-bold text-amber-900/50 uppercase mb-2"
              >
                {{ $t("admin.plans.platforms") }}
              </h4>
              <div class="flex flex-wrap gap-x-4 gap-y-2">
                <label
                  v-for="platform in [
                    'youtube',
                    'twitch',
                    'tiktok',
                    'facebook',
                    'kick',
                    'local',
                  ]"
                  :key="platform"
                  class="flex items-center gap-2 cursor-pointer group"
                >
                  <input
                    v-model="featuresData.platforms"
                    type="checkbox"
                    :value="platform"
                    class="w-4 h-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                  >
                  <span
                    class="text-[10px] uppercase font-black text-amber-700 dark:text-amber-400 group-hover:text-amber-900 transition-colors"
                  >
                    {{ platform }}
                  </span>
                </label>
              </div>
            </div>

            <!-- Limits & Main Params -->
            <div
              class="grid grid-cols-2 gap-3 pt-2 border-t border-amber-100 dark:border-amber-900/20"
            >
              <AppInput
                v-model="featuresData.streamQuality"
                :label="$t('admin.users.modal.quality')"
              />
              <AppInput
                v-model="featuresData.watermarkType"
                :label="$t('admin.users.modal.watermark')"
              />
              <AppInput
                v-model.number="featuresData.concurrentStreams"
                :label="$t('admin.users.modal.streams')"
                type="number"
              />
              <AppInput
                v-model.number="featuresData.maxVideos"
                :label="$t('admin.users.modal.maxVideos')"
                type="number"
              />
              <div class="col-span-2">
                <AppInput
                  v-model.number="featuresData.storageLimit"
                  :label="$t('admin.users.modal.storageLimit')"
                  type="number"
                >
                  <template #append>
                    <span
                      class="text-[10px] text-amber-600 font-bold pr-2 bg-white dark:bg-gray-800"
                    >
                      {{
                        (
                          featuresData.storageLimit /
                          (1024 * 1024 * 1024)
                        ).toFixed(1)
                      }}
                      GB
                    </span>
                  </template>
                </AppInput>
              </div>
            </div>

            <div
              v-if="featuresData.limits"
              class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-amber-100 dark:border-amber-900/20"
            >
              <h4
                class="col-span-2 text-[10px] font-bold text-amber-900/50 uppercase"
              >
                {{ $t("admin.users.modal.limits") }}
              </h4>
              <AppInput
                v-for="(val, key) in featuresData.limits"
                :key="key"
                v-model.number="featuresData.limits[key]"
                :label="key"
                type="number"
              />
            </div>
          </div>

          <AppTextarea
            v-else
            v-model="featuresJson"
            rows="12"
            class="font-mono text-[10px]"
            :error="
              jsonError
                ? $t('admin.users.modal.invalidJson') + ': ' + jsonError
                : null
            "
          />
        </div>

        <!-- Effective Limits Summary (If addons exist) -->
        <div
          v-if="
            internalForm.subscription.addons &&
              internalForm.subscription.addons.length > 0
          "
          class="p-4 bg-primary-50/50 dark:bg-primary-900/10 rounded-2xl border border-primary-100 dark:border-primary-900/20 shadow-sm"
        >
          <h3
            class="text-[10px] font-black text-primary-800 dark:text-primary-400 uppercase tracking-widest mb-3"
          >
            {{ $t("admin.users.modal.effectiveLimits") }}
          </h3>
          <div class="grid grid-cols-2 gap-4 mb-3">
            <div
              class="p-2 rounded-xl bg-white dark:bg-gray-800 border border-primary-100 dark:border-primary-800"
            >
              <p class="text-[9px] text-gray-400 uppercase font-black">
                {{ $t("admin.users.modal.streams") }}
              </p>
              <p class="text-lg font-black text-primary-600">
                {{
                  internalForm.subscription.effectiveLimits
                    ?.concurrent_streams ||
                    internalForm.subscription.effectiveLimits
                      ?.concurrentStreams ||
                    "-"
                }}
              </p>
            </div>
            <div
              class="p-2 rounded-xl bg-white dark:bg-gray-800 border border-primary-100 dark:border-primary-800"
            >
              <p class="text-[9px] text-gray-400 uppercase font-black">
                {{ $t("admin.users.modal.storageLimit") }}
              </p>
              <p class="text-lg font-black text-primary-600">
                {{
                  formatStorageGB(
                    internalForm.subscription.effectiveLimits?.storage_limit ||
                      internalForm.subscription.effectiveLimits?.storageLimit,
                  )
                }}
                GB
              </p>
            </div>
          </div>

          <div class="pt-3 border-t border-primary-100 dark:border-primary-800">
            <h4
              class="text-[9px] font-black text-primary-400 uppercase tracking-widest mb-1"
            >
              {{ $t("admin.users.modal.addonsTitle") }}
            </h4>
            <div
              v-for="addon in internalForm.subscription.addons"
              :key="addon.id"
              class="flex items-center justify-between text-[11px] font-medium text-gray-700 dark:text-gray-300"
            >
              <span>{{ addon.name }}</span>
              <span class="text-primary-600">x{{ addon.quantity }}</span>
            </div>
          </div>
        </div>

        <!-- Usage Overrides -->
        <div
          class="p-5 bg-blue-50/50 dark:bg-blue-900/5 rounded-2xl border border-blue-100 dark:border-blue-900/20"
        >
          <div class="flex items-center justify-between mb-4">
            <h3
              class="text-xs font-black text-blue-800 dark:text-blue-400 uppercase tracking-widest"
            >
              {{ $t("admin.users.modal.usage.title") }}
            </h3>
            <button
              class="text-[10px] font-bold text-blue-600 underline"
              @click="toggleMode('usage')"
            >
              {{
                editModes.usage === "visual"
                  ? "Switch to JSON"
                  : "Switch to Visual"
              }}
            </button>
          </div>

          <div
            v-if="editModes.usage === 'visual'"
            class="grid grid-cols-1 sm:grid-cols-2 gap-4"
          >
            <AppInput
              v-model.number="usageData.streamsActive"
              :label="$t('admin.users.modal.usage.streamsActive')"
              type="number"
            />
            <AppInput
              v-model.number="usageData.videosUploaded"
              :label="$t('admin.users.modal.usage.videosUploaded')"
              type="number"
            />
            <AppInput
              v-model.number="usageData.storageUsed"
              :label="$t('admin.users.modal.usage.storageUsed')"
              class="sm:col-span-2"
              type="number"
            >
              <template #append>
                <span
                  class="text-[10px] text-blue-600 font-bold pr-2 bg-white dark:bg-gray-800"
                >
                  {{
                    (usageData.storageUsed / (1024 * 1024 * 1024)).toFixed(2)
                  }}
                  GB
                </span>
              </template>
            </AppInput>
            <AppInput
              v-model.number="usageData.videoDurationMinutes"
              :label="$t('admin.users.modal.usage.videoDurationMinutes')"
              class="sm:col-span-2"
              type="number"
            />
          </div>

          <AppTextarea
            v-else
            v-model="usageJson"
            rows="8"
            class="font-mono text-[10px]"
            :error="
              usageJsonError
                ? $t('admin.users.modal.invalidJson') + ': ' + usageJsonError
                : null
            "
          />
        </div>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <AppButton
          variant="secondary"
          @click="$emit('close')"
        >
          {{ $t("admin.users.modal.cancel") }}
        </AppButton>
        <AppButton
          variant="primary"
          :loading="saving"
          :disabled="isEditing && (!!jsonError || !!usageJsonError)"
          @click="handleSave"
        >
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
import AppTextarea from "@/components/admin/ui/AppTextarea.vue";
import AppToggle from "@/components/admin/ui/AppToggle.vue";
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
const featuresJson = ref("");
const jsonError = ref(null);
const usageJson = ref("");
const usageJsonError = ref(null);

const GB = 1024 * 1024 * 1024;

const booleanFeatures = computed(() => {
  if (!featuresData.value.features) return [];
  return Object.keys(featuresData.value.features).filter(
    (key) => typeof featuresData.value.features[key] === "boolean",
  );
});

const editModes = ref({
  features: "visual",
  usage: "visual",
});

const featuresData = ref({});
const usageData = ref({});

const toggleMode = (type) => {
  if (editModes.value[type] === "visual") {
    // Switching to JSON
    if (type === "features") {
      featuresJson.value = JSON.stringify(featuresData.value, null, 2);
    } else {
      usageJson.value = JSON.stringify(usageData.value, null, 2);
    }
    editModes.value[type] = "json";
  } else {
    // Switching to Visual
    try {
      if (type === "features") {
        featuresData.value = JSON.parse(featuresJson.value);
      } else {
        usageData.value = JSON.parse(usageJson.value);
      }
      editModes.value[type] = "visual";
    } catch (e) {
      alert("Invalid JSON, cannot switch to visual mode");
    }
  }
};

const formatStorageGB = (bytes) => {
  if (!bytes) return "0";
  return (bytes / (1024 * 1024 * 1024)).toFixed(1);
};

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
    if (newForm.subscription?.featuresSnapshot) {
      featuresData.value = JSON.parse(
        JSON.stringify(newForm.subscription.featuresSnapshot),
      );
      featuresJson.value = JSON.stringify(featuresData.value, null, 2);
    } else {
      featuresData.value = {};
      featuresJson.value = "";
    }

    if (newForm.subscription?.usage) {
      const uData = { ...newForm.subscription.usage };
      delete uData.id;
      delete uData.subscriptionId;
      delete uData.createdAt;
      delete uData.updatedAt;

      usageData.value = uData;
      usageJson.value = JSON.stringify(uData, null, 2);
    } else {
      usageData.value = {};
      usageJson.value = "";
    }
  },
  { deep: true, immediate: true },
);

watch(
  featuresData,
  (val) => {
    if (editModes.value.features === "visual") {
      featuresJson.value = JSON.stringify(val, null, 2);
    }
  },
  { deep: true },
);

watch(
  usageData,
  (val) => {
    if (editModes.value.usage === "visual") {
      usageJson.value = JSON.stringify(val, null, 2);
    }
  },
  { deep: true },
);

watch(featuresJson, (val) => {
  if (!val) {
    jsonError.value = null;
    return;
  }
  try {
    const parsed = JSON.parse(val);
    jsonError.value = null;
    if (editModes.value.features === "json") {
      featuresData.value = parsed;
    }
  } catch (e) {
    jsonError.value = e.message;
  }
});

watch(usageJson, (val) => {
  if (!val) {
    usageJsonError.value = null;
    return;
  }
  try {
    const parsed = JSON.parse(val);
    usageJsonError.value = null;
    if (editModes.value.usage === "json") {
      usageData.value = parsed;
    }
  } catch (e) {
    usageJsonError.value = e.message;
  }
});

const handleSave = () => {
  let featuresSnapshot = null;
  if (props.isEditing) {
    try {
      featuresSnapshot = JSON.parse(featuresJson.value);
    } catch (e) {
      jsonError.value = e.message;
      return;
    }
  }

  let usageSnapshot = null;
  if (props.isEditing) {
    try {
      usageSnapshot = JSON.parse(usageJson.value);
    } catch (e) {
      usageJsonError.value = e.message;
      return;
    }
  }

  emit("save", {
    ...internalForm.value,
    featuresSnapshot: featuresSnapshot,
    subscriptionUsage: usageSnapshot,
  });
};
</script>
