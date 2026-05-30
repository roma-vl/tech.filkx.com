<template>
  <div class="space-y-6 animate-in fade-in slide-in-from-bottom-4 duration-300">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div
        class="relative flex flex-col items-center justify-center p-8 border-2 rounded-2xl cursor-pointer transition-all duration-300 group hover:border-blue-400 bg-blue-50/50 dark:bg-blue-900/10 border-blue-100 dark:border-blue-800/50 shadow-sm hover:shadow-md"
        @click="$emit('select', true)"
      >
        <div
          class="absolute -top-3 right-4 px-3 py-1 bg-blue-500 text-white text-[10px] font-black uppercase tracking-widest rounded-full shadow-sm"
        >
          {{ $t("streams.addModal.youtubeSettings.recommended") }}
        </div>
        <div
          class="w-16 h-16 mb-4 rounded-full bg-blue-100 dark:bg-blue-800/30 flex items-center justify-center group-hover:scale-110 transition-transform"
        >
          <ZapIcon class="w-8 h-8 text-blue-600 dark:text-blue-400" />
        </div>
        <h4 class="font-black text-gray-900 dark:text-white mb-2">
          {{ $t("streams.addModal.youtubeSettings.automatic") }}
        </h4>
        <p
          class="text-xs text-gray-500 dark:text-gray-400 text-center leading-relaxed"
        >
          {{
            $t("streams.addModal.youtubeSettings.automaticDesc") ||
              "Connect via API. We create the broadcast for you."
          }}
        </p>
      </div>

      <div
        class="flex flex-col items-center justify-center p-8 border-2 rounded-2xl cursor-pointer transition-all duration-300 group hover:border-primary/50 bg-white dark:bg-gray-800 border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md"
        @click="$emit('select', false)"
      >
        <div
          class="w-16 h-16 mb-4 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center group-hover:scale-110 transition-transform"
        >
          <KeyIcon class="w-8 h-8 text-gray-400" />
        </div>
        <h4 class="font-black text-gray-900 dark:text-white mb-2">
          {{ $t("streams.addModal.youtubeSettings.manual") }}
        </h4>
        <p
          class="text-xs text-gray-500 dark:text-gray-400 text-center leading-relaxed"
        >
          {{
            $t("streams.addModal.youtubeSettings.manualDesc") ||
              "Standard RTMP. Just enter your YouTube Stream Key."
          }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { KeyIcon, ZapIcon } from "lucide-vue-next";

defineEmits(["select"]);
</script>
