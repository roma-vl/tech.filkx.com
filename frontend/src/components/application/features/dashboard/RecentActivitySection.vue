<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold">
        {{ t("dashboard.recentActivity.title") }}
      </h3>
      <AppButton
        variant="text"
        to="/activities"
        class="text-sm font-medium"
      >
        {{ t("dashboard.recentActivity.viewAll") }}
      </AppButton>
    </div>

    <div v-if="loading" class="p-6">
      <AppSkeleton type="table" :rows="5" />
    </div>

    <AppTable
      v-else
      :headings="tableHeadings"
      :items="activities"
      :header-enabled="false"
      :pagination-enabled="false"
      :searchable="false"
      :sortable="false"
      class="border-none shadow-none bg-transparent backdrop-blur-none"
    >
      <template #column-activity="{ row }">
        <div class="flex items-center gap-3">
          <div
            class="w-12 h-12 rounded-xl flex items-center justify-center border shadow-sm"
            :class="getActivityColorClass(row.type)"
          >
            <component
              :is="getActivityIcon(row.type)"
              class="w-6 h-6"
            />
          </div>
          <span
            class="font-semibold text-gray-900 dark:text-white"
            :title="row.title"
          >
            {{ truncate(row.title, 30) }}
          </span>
        </div>
      </template>

      <template #column-description="{ row }">
        <span
          class="text-gray-600 dark:text-gray-400 font-medium"
          :title="row.description"
        >
          {{ truncate(row.description, 70) }}
        </span>
      </template>

      <template #column-date="{ row }">
        <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 whitespace-nowrap">
          {{ row.date }}
        </span>
      </template>
    </AppTable>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import AppSkeleton from "@/components/application/ui/Feedback/AppSkeleton.vue";
import AppTable from "@/components/application/ui/Data/AppTable.vue";
import VideosIcon from "@/components/Icon/VideosIcon.vue";
import PlaylistIcon from "@/components/Icon/PlaylistsIcon.vue";
import StreamIcon from "@/components/Icon/StreamingIcon.vue";
import ActivityIcon from "@/components/Icon/ActivityIcon.vue";

const { t } = useI18n();

defineProps({
  activities: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["activity-click"]);

const tableHeadings = [
  { key: "activity", value: t("dashboard.recentActivity.table.activity") },
  { key: "description", value: t("dashboard.recentActivity.table.description") },
  { key: "date", value: t("dashboard.recentActivity.table.date") },
];

const handleActivityClick = (activityId) => {
  emit("activity-click", activityId);
};

function getActivityIcon(type) {
  if (type?.includes("video")) return VideosIcon;
  if (type?.includes("playlist")) return PlaylistIcon;
  if (type?.includes("stream")) return StreamIcon;
  return ActivityIcon;
}

function getActivityColorClass(type) {
  if (type?.includes("video")) return "bg-blue-50 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-500/30";
  if (type?.includes("playlist")) return "bg-purple-50 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-500/30";
  if (type?.includes("stream")) return "bg-green-50 text-green-600 border-green-200 dark:bg-green-500/20 dark:text-green-400 dark:border-green-500/30";
  if (type?.includes("deleted")) return "bg-red-50 text-red-600 border-red-200 dark:bg-red-500/20 dark:text-red-400 dark:border-red-500/30";
  return "bg-gray-50 text-gray-600 border-gray-200 dark:bg-gray-500/20 dark:text-gray-400 dark:border-gray-500/30";
}

function truncate(text, length) {
  if (!text) return "";
  if (text.length <= length) return text;
  return text.substring(0, length) + "...";
}
</script>
