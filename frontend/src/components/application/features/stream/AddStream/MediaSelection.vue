<template>
  <div class="space-y-4">
    <AppSelect
      v-model="formData.selectionType"
      :label="$t('streams.addModal.videoLabel')"
      :options="selectionTypeOptions"
      :placeholder="$t('streams.addModal.notSelected')"
      option-value="id"
      option-label="name"
    />

    <AppSelect
      v-if="formData.selectionType === 'video'"
      v-model="formData.video"
      :options="videos"
      option-value="id"
      option-label="title"
    >
      <template #option="{ option }">
        <div class="flex items-center justify-between w-full">
          <span :class="{ 'opacity-50 line-through': option.disabled }">
            {{ option.title }}
          </span>
          <!-- Lock badge for preview-only originals -->
          <div
            v-if="option.isOriginalLocked"
            class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800/50 shrink-0"
          >
            <LockIcon class="w-2.5 h-2.5"/>
          </div>
          <div
            v-else-if="option.resolutionLabel"
            class="flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
            :class="getResolutionClass(option.resolutionLabel, option.disabled)"
          >
            <MonitorIcon class="w-2.5 h-2.5"/>
            {{ option.resolutionLabel }}
          </div>
        </div>
      </template>
    </AppSelect>

    <AppSelect
      v-if="formData.selectionType === 'playlist'"
      v-model="formData.playlist"
      :options="playlists"
      option-value="id"
      option-label="name"
      :hint="$t('streams.addModal.playlistHint')"
    >
      <template #option="{ option }">
        <div class="flex items-center justify-between w-full text-left">
          <span :class="{ 'opacity-50 line-through': option.disabled }">
            {{ option.name }}
          </span>
          <div
            v-if="option.disabled"
            class="text-[10px] text-red-500 font-bold uppercase tracking-widest pl-2"
          >
            {{ $t("common.notSupported") }}
          </div>
        </div>
      </template>
    </AppSelect>
  </div>
</template>

<script setup>
import AppSelect from "@/components/application/ui/Form/AppSelect.vue";
import {LockIcon, MonitorIcon} from "lucide-vue-next";

defineProps({
  formData: {
    type: Object,
    required: true,
  },
  videos: {
    type: Array,
    required: true,
  },
  playlists: {
    type: Array,
    required: true,
  },
  selectionTypeOptions: {
    type: Array,
    required: true,
  },
});

const getResolutionClass = (label, isDisabled) => {
  if (isDisabled) {
    return "bg-gray-100 text-gray-400 border-gray-200 dark:bg-gray-800 dark:text-gray-600 dark:border-gray-700";
  }
  if (label === "4K")
    return "bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800/50";
  if (label === "1080p")
    return "bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/50";
  if (label === "720p")
    return "bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/50";
  return "bg-gray-100 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700";
};
</script>
