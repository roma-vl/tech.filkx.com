<template>
  <div class="px-6 pb-6 pt-2 bg-gradient-to-t from-white/90 to-transparent dark:from-gray-900/90 relative z-10">
    <div
      class="relative bg-white/60 dark:bg-gray-800/40 backdrop-blur-xl rounded-[2rem] border border-white/60 dark:border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.05)] focus-within:shadow-[0_8px_32px_rgba(var(--color-primary-500),0.15)] focus-within:border-primary-300 dark:focus-within:border-primary-700 transition-all duration-300 group"
    >
      <textarea
        v-model="replyMessage"
        :placeholder="t('support.reply_placeholder')"
        rows="1"
        class="w-full bg-transparent border-none focus:ring-0 pt-4 pb-4 pl-5 pr-28 text-[13px] font-medium custom-scrollbar dark:text-white text-gray-700 placeholder:text-gray-400 dark:placeholder:text-gray-500 resize-none min-h-[52px] max-h-[160px]"
        @keydown.enter.prevent="handleSend"
      />
      <div class="absolute right-2 bottom-2 max-h-min flex items-center gap-1">
        <button
          class="p-2.5 rounded-full text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 transition-all transform active:scale-90"
          @click="fileInputRef?.click()"
        >
          <PaperClipIcon class="w-5 h-5" />
        </button>
        <button
          class="w-10 h-10 flex items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/30 hover:shadow-primary-600/50 transition-all transform hover:scale-105 active:scale-95 disabled:opacity-30 disabled:grayscale disabled:scale-100 disabled:shadow-none"
          :disabled="(!replyMessage && !selectedFile) || loading"
          @click="handleSend"
        >
          <PaperAirplaneIcon class="w-4 h-4 ml-0.5" />
        </button>
      </div>
    </div>
    <input
      ref="fileInputRef"
      type="file"
      class="hidden"
      @change="handleFileUpload"
    >

    <div
      v-if="selectedFile"
      class="absolute bottom-full left-6 right-6 mb-3 flex items-center gap-3 p-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur-md rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg shadow-black/5"
    >
      <div
        class="w-10 h-10 rounded-xl bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center shrink-0 border border-primary-100 dark:border-primary-800/30"
      >
        <PaperClipIcon class="w-5 h-5 text-primary-600 dark:text-primary-400" />
      </div>
      <div class="flex-1 min-w-0">
        <p
          class="text-[11px] font-bold truncate dark:text-gray-200 text-gray-800"
        >
          {{ selectedFile.name }}
        </p>
        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
          {{ (selectedFile.size / 1024).toFixed(1) }} KB
        </p>
      </div>
      <button
        class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all"
        @click="selectedFile = null"
      >
        <CrossIcon class="w-4 h-4" />
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import {
  PaperClipIcon,
  PaperAirplaneIcon,
  XMarkIcon as CrossIcon,
} from "@heroicons/vue/24/outline";

const { t } = useI18n();

defineProps({
  loading: Boolean,
});

const emit = defineEmits(["sendMessage"]);

const replyMessage = ref("");
const selectedFile = ref(null);
const fileInputRef = ref(null);

const handleSend = () => {
  if (!replyMessage.value && !selectedFile.value) return;

  emit("sendMessage", {
    message: replyMessage.value,
    file: selectedFile.value,
  });

  replyMessage.value = "";
  selectedFile.value = null;
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) selectedFile.value = file;
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.2);
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.4);
}
</style>
