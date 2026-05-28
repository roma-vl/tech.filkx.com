<template>
  <div
    class="bg-white dark:bg-gray-800 p-8 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm sticky top-6"
  >
    <div class="flex items-center gap-3 mb-8">
      <RocketLaunchIcon class="w-6 h-6 text-primary-600" />
      <h3
        class="font-black text-xl text-gray-900 dark:text-white tracking-tight"
      >
        {{ t("admin.emails.broadcast_settings") }}
      </h3>
    </div>

    <div
      v-if="selectedCampaign"
      class="space-y-8"
    >
      <div class="space-y-3">
        <label
          class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block"
        >
          {{ t("admin.emails.target_audience") }}
        </label>
        <AppSelect
          :model-value="targetAudience"
          :options="[
            { value: 'all', label: t('admin.emails.audience.all') },
            { value: 'active_subscribers', label: t('admin.emails.audience.active_subscribers') },
            { value: 'trial_expired', label: t('admin.emails.audience.trial_expired') },
            { value: 'new_users', label: t('admin.emails.audience.new_users') }
          ]"
          class="!rounded-2xl"
          @update:model-value="$emit('update:targetAudience', $event)"
        />
      </div>

      <div
        class="p-5 rounded-2xl bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800/30"
      >
        <div class="flex items-start gap-4">
          <ExclamationTriangleIcon
            class="w-5 h-5 text-orange-600 shrink-0 mt-0.5"
          />
          <p
            class="text-[11px] font-bold text-orange-700 dark:text-orange-400 leading-relaxed uppercase tracking-tight"
          >
            {{ t("admin.emails.dispatch_warning") }}
          </p>
        </div>
      </div>

      <div class="flex flex-col gap-4">
        <AppButton
          variant="secondary"
          size="lg"
          :loading="loadingPreview"
          @click="$emit('preview')"
        >
          <template #prefix>
            <EyeIcon
              v-if="!loadingPreview"
              class="w-5 h-5 mr-2"
            />
          </template>
          <span>{{ t("admin.emails.preview") }}</span>
        </AppButton>

        <AppButton
          :loading="loading"
          size="lg"
          @click="$emit('broadcast')"
        >
          <template #prefix>
            <PaperAirplaneIcon
              v-if="!loading"
              class="w-5 h-5 -rotate-45 mr-2"
            />
          </template>
          <span>{{
            loading
              ? t("admin.emails.dispatching")
              : t("admin.emails.broadcast")
          }}</span>
        </AppButton>
      </div>
    </div>

    <div
      v-else
      class="text-center py-20"
    >
      <div
        class="w-20 h-20 bg-gray-50 dark:bg-gray-900/50 rounded-full flex items-center justify-center mx-auto mb-6 border border-dashed border-gray-200 dark:border-gray-700"
      >
        <CursorArrowRaysIcon class="w-10 h-10 text-gray-300" />
      </div>
      <p
        class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest leading-loose"
      >
        {{ t("admin.emails.select_template") }}
      </p>
    </div>
  </div>
</template>

<script setup>
import {
  RocketLaunchIcon,
  ExclamationTriangleIcon,
  EyeIcon,
  PaperAirplaneIcon,
  CursorArrowRaysIcon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();

defineProps({
  selectedCampaign: {
    type: Object,
    default: null,
  },
  targetAudience: {
    type: String,
    default: "all",
  },
  loading: {
    type: Boolean,
    default: false,
  },
  loadingPreview: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["update:targetAudience", "preview", "broadcast"]);
</script>
