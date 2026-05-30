<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700/50 p-6"
  >
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
          {{ $t("settings.youtube_channels.title") || "YouTube Channels" }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{
            $t("settings.youtube_channels.description") ||
              "Manage YouTube channels for automatic streaming"
          }}
        </p>
      </div>
      <AppButton
        size="sm"
        @click="youtubeStore.connect()"
      >
        {{ $t("settings.youtube_channels.connect_btn") || "Connect Channel" }}
      </AppButton>
    </div>

    <div
      v-if="youtubeStore.loading"
      class="flex justify-center py-8"
    >
      <div
        class="animate-spin rounded-full h-8 w-8 border-2 border-primary border-t-transparent"
      />
    </div>

    <div
      v-else-if="youtubeStore.channels.length === 0"
      class="text-center py-12 border-2 border-dashed border-gray-100 dark:border-gray-700 rounded-xl"
    >
      <div
        class="w-12 h-12 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4"
      >
        <YoutubeIcon class="w-6 h-6 text-gray-400" />
      </div>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        {{
          $t("settings.youtube_channels.no_channels") ||
            "No YouTube channels connected yet"
        }}
      </p>
    </div>

    <div
      v-else
      class="grid grid-cols-1 sm:grid-cols-2 gap-4"
    >
      <div
        v-for="channel in youtubeStore.channels"
        :key="channel.id"
        class="flex items-center justify-between p-4 border border-gray-100 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/50"
      >
        <div class="flex items-center gap-3">
          <img
            :src="channel.thumbnail"
            class="w-10 h-10 rounded-full border border-gray-200 dark:border-gray-700"
          >
          <div>
            <p class="font-bold text-gray-900 dark:text-white leading-tight">
              {{ channel.title }}
            </p>
            <p
              class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mt-0.5"
            >
              {{ channel.channelId }}
            </p>
          </div>
        </div>

        <button
          class="p-2 text-gray-400 hover:text-red-500 transition-colors"
          @click="confirmDisconnect(channel)"
        >
          <Trash2Icon class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useYoutubeStore } from "@/stores/youtube";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import { YoutubeIcon, Trash2Icon } from "lucide-vue-next";

const { t } = useI18n();
const youtubeStore = useYoutubeStore();
const toast = useToast();

const confirmDisconnect = async (channel) => {
  if (
    confirm(
      t("settings.youtube_channels.disconnect_confirm") ||
        `Disconnect ${channel.title}?`,
    )
  ) {
    try {
      await youtubeStore.disconnectChannel(channel.id);
      toast.success(
        t("settings.youtube_channels.disconnect_success") ||
          "Channel disconnected",
      );
    } catch (e) {
      toast.error(
        t("settings.youtube_channels.disconnect_error") ||
          "Failed to disconnect channel",
      );
    }
  }
};

onMounted(() => {
  youtubeStore.fetchChannels();
});
</script>
