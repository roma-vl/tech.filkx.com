<template>
  <div
    :class="[
      'flex gap-3',
      vertical ? 'flex-col items-stretch w-full' : 'flex-wrap items-center'
    ]"
  >
    <UsageBadge
      label="usage.storage"
      :value="storage.usedLabel"
      :max="storage.limitLabel"
      :color="storage.color"
      :icon="StorageIcon"
      unit="GB"
      :full-width="vertical"
    />

    <!-- Hide videos on mobile vertical drawer if requested -->
    <UsageBadge
      v-if="!vertical"
      label="usage.videos"
      :value="videos.used"
      :max="videos.limit"
      :color="videos.color"
      :icon="ScreenPlayIcon"
      :full-width="vertical"
    />

    <UsageBadge
      label="usage.streams"
      :value="streams.used"
      :max="streams.limit"
      :color="streams.color"
      :icon="StreamingIcon"
      :full-width="vertical"
    />

    <UpgradeCta v-if="shouldSuggestUpgrade && !vertical" />
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useSubscriptionStore } from "@/stores/subscription";
import UsageBadge from "./UsageBadge.vue";
import UpgradeCta from "./UpgradeCta.vue";
import StorageIcon from "@/components/Icon/StorageIcon.vue";
import ScreenPlayIcon from "@/components/Icon/ScreenPlayIcon.vue";
import StreamingIcon from "@/components/Icon/StreamingIcon.vue";

const props = defineProps({
  vertical: {
    type: Boolean,
    default: false,
  },
});

const sub = useSubscriptionStore();
const GB = 1024 * 1024 * 1024;

const storage = computed(() => {
  const usedBytes = sub.storageUsedBytes;
  const limitBytes = sub.storageLimitBytes;
  const ratio = limitBytes ? usedBytes / limitBytes : 0;

  return {
    usedLabel: (usedBytes / GB).toFixed(1),
    limitLabel: (limitBytes / GB).toFixed(0),
    ratio,
    color: ratio >= 1 ? "red" : ratio >= 0.8 ? "orange" : "blue",
  };
});

const videos = computed(() => {
  const used = sub.isActive
    ? (sub.subscription?.usage?.videosUploaded ?? 0)
    : 0;
  const limit = sub.isActive ? (sub.subscription?.features?.maxVideos ?? 0) : 0;

  return {
    used,
    limit,
    color:
      used >= limit && limit > 0
        ? "red"
        : limit > 0 && used / limit >= 0.8
          ? "orange"
          : "gray",
  };
});

const streams = computed(() => {
  const used = sub.isActive ? (sub.subscription?.usage?.streamsActive ?? 0) : 0;
  const limit = sub.isActive
    ? (sub.subscription?.features?.concurrentStreams ?? 0)
    : 0;

  return {
    used,
    limit,
    color: used >= limit && limit > 0 ? "red" : "purple",
  };
});

const shouldSuggestUpgrade = computed(
  () =>
    storage.value.ratio >= 0.8 ||
    videos.value.used >= videos.value.limit ||
    streams.value.used >= streams.value.limit,
);
</script>
