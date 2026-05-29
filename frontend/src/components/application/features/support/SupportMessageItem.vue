<template>
  <div
    :class="[
      'flex w-full',
      message.isAdmin || message.isAi ? 'justify-start' : 'justify-end',
    ]"
  >
    <div class="flex flex-col max-w-[85%] gap-1.5">
      <div
        :class="[
          'rounded-2xl p-4 text-[13px] shadow-sm transition-all duration-300',
          message.isAdmin || message.isAi
            ? 'bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-bl-sm border border-black/5 dark:border-white/10'
            : 'bg-gradient-to-br from-primary-500 to-primary-600 text-white rounded-br-sm shadow-md shadow-primary-500/20 border border-primary-400/30 dark:border-primary-700/50',
          message.isAi
            ? 'border-primary-200/60 dark:border-primary-800/60 ring-1 ring-primary-100/50 dark:ring-primary-900/30'
            : '',
        ]"
      >
        <div
          v-if="message.message"
          class="whitespace-pre-wrap break-words leading-relaxed text-gray-800 dark:text-gray-100 font-medium"
          :class="{ '!text-white': !(message.isAdmin || message.isAi) }"
        >
          {{ message.message }}
        </div>

        <div
          v-if="message.filePath"
          class="mt-3"
        >
          <div
            v-if="isImage(message.fileType)"
            class="group relative overflow-hidden rounded-[1rem] border border-black/5 dark:border-white/10 shadow-sm"
          >
            <img
              :src="message.filePath"
              class="w-full h-auto cursor-pointer hover:scale-[1.03] transition-transform duration-500"
              @click="openFile(message.filePath)"
            >
          </div>
          <video
            v-else-if="isVideo(message.fileType)"
            :src="message.filePath"
            controls
            preload="metadata"
            class="rounded-[1rem] max-w-full border border-black/5 dark:border-white/10 shadow-sm"
          />
          <audio
            v-else-if="isAudio(message.fileType)"
            :src="message.filePath"
            controls
            class="w-full h-10 mt-2 rounded-full shadow-inner"
          />
          <a
            v-else
            :href="message.filePath"
            target="_blank"
            class="flex items-center gap-3 p-3 bg-black/5 dark:bg-white/5 rounded-2xl hover:bg-black/10 dark:hover:bg-white/10 transition-colors border border-transparent hover:border-black/5 dark:hover:border-white/5 group"
          >
            <div
              class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center shrink-0 border border-primary-200 dark:border-primary-800/50 group-hover:bg-primary-200 transition-colors"
            >
              <PaperClipIcon class="w-5 h-5 text-primary-600" />
            </div>
            <div class="flex-1 min-w-0">
              <span
                class="block truncate font-bold text-gray-800 dark:text-gray-200 text-xs group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"
                :class="{ '!text-white group-hover:!text-gray-200': !(message.isAdmin || message.isAi) }"
              >{{ message.fileName }}</span>
              <span
                class="text-[9px] text-gray-500 font-bold uppercase tracking-widest mt-0.5 block"
                :class="{ '!text-white/70': !(message.isAdmin || message.isAi) }"
              >{{ (message.fileSize / 1024).toFixed(1) }} KB</span>
            </div>
          </a>
        </div>
      </div>
      <div
        :class="[
          'flex items-center gap-2 px-1',
          message.isAdmin || message.isAi ? 'justify-start' : 'justify-end',
        ]"
      >
        <span
          v-if="message.isAi"
          class="text-[9px] font-black uppercase text-primary-500 tracking-wider"
        >AI Assistant</span>
        <span
          class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest"
        >{{ formatTime(message.createdAt) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { PaperClipIcon } from "@heroicons/vue/24/outline";

defineProps({
  message: Object,
});

const isImage = (type) => type?.startsWith("image/");
const isVideo = (type) => type?.startsWith("video/");
const isAudio = (type) => type?.startsWith("audio/");
const openFile = (url) => window.open(url, "_blank");

const formatTime = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
  });
};
</script>
