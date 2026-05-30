<template>
  <div class="space-y-4 animate-in fade-in slide-in-from-top-1 duration-200">
    <div
      v-if="youtubeStore.channels.length > 0"
      class="flex items-end gap-2"
    >
      <div class="flex-1">
        <AppSelect
          v-model="formData.youtubeChannelId"
          :label="$t('streams.addModal.youtubeSettings.channelLabel')"
          :options="youtubeStore.channels"
          option-value="id"
          option-label="title"
        >
          <template #option="{ option }">
            <div class="flex items-center gap-2">
              <img
                :src="option.thumbnail"
                class="w-6 h-6 rounded-full"
              >
              <span>{{ option.title }}</span>
            </div>
          </template>
        </AppSelect>
      </div>
      <AppButton
        variant="secondary"
        size="md"
        class="!px-3 !py-2.5"
        :title="$t('streams.addModal.youtubeSettings.connectButton')"
        @click="youtubeStore.connect()"
      >
        <PlusIcon class="w-5 h-5 !mr-1" />
        {{ $t("streams.addModal.youtubeSettings.connectButton") }}
      </AppButton>
    </div>
    <div
      v-else
      class="p-4 bg-yellow-50 dark:bg-yellow-900/10 rounded-lg text-center"
    >
      <p class="text-sm text-yellow-800 dark:text-yellow-200 mb-3">
        {{ $t("streams.addModal.youtubeSettings.noChannels") }}
      </p>
      <AppButton
        size="sm"
        @click="youtubeStore.connect()"
      >
        {{ $t("streams.addModal.youtubeSettings.connectButton") }}
      </AppButton>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <AppSelect
        v-model="formData.privacyStatus"
        :label="$t('streams.addModal.youtubeSettings.privacyLabel')"
        :options="[
          { id: 'public', name: 'Public' },
          { id: 'unlisted', name: 'Unlisted' },
          { id: 'private', name: 'Private' },
        ]"
        option-value="id"
        option-label="name"
      />
      <div class="flex flex-col justify-end pb-2">
        <label class="flex items-center gap-2 cursor-pointer select-none">
          <input
            v-model="formData.isMadeForKids"
            type="checkbox"
            class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
          >
          <span
            class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-widest opacity-50"
          >
            {{ $t("streams.addModal.youtubeSettings.madeForKids") }}
          </span>
        </label>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <AppInput
        v-model="formData.youtubeTags"
        :label="$t('streams.addModal.youtubeSettings.tagsLabel')"
        :placeholder="$t('streams.addModal.youtubeSettings.tagsPlaceholder')"
      />
      <AppSelect
        v-model="formData.youtubeCategoryId"
        :label="$t('streams.addModal.youtubeSettings.categoryLabel')"
        :options="youtubeCategories"
        option-value="id"
        option-label="name"
      />
    </div>

    <AppTextarea
      v-model="formData.youtubeDescription"
      :label="$t('streams.addModal.youtubeSettings.descriptionLabel')"
      :placeholder="
        $t('streams.addModal.youtubeSettings.descriptionPlaceholder')
      "
      rows="4"
      class="!text-base"
    />
  </div>
</template>

<script setup>
import { useYoutubeStore } from "@/stores/youtube";
import AppSelect from "@/components/application/ui/Form/AppSelect.vue";
import AppInput from "@/components/application/ui/Form/AppInput.vue";
import AppTextarea from "@/components/application/ui/Form/AppTextarea.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import { PlusIcon } from "lucide-vue-next";

defineProps({
  formData: {
    type: Object,
    required: true,
  },
  youtubeCategories: {
    type: Array,
    required: true,
  },
});

const youtubeStore = useYoutubeStore();
</script>
