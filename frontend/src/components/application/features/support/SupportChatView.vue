<template>
  <div
    class="flex-1 flex flex-col overflow-hidden bg-gradient-to-b from-gray-50/50 to-white/80 dark:from-gray-900/40 dark:to-gray-800/60"
  >
    <div
      ref="chatContainer"
      class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar"
    >
      <div
        v-if="activeTicket?.handled_by === 'ai'"
        class="p-4 rounded-[2rem] bg-gradient-to-r from-primary-50 to-white dark:from-primary-900/20 dark:to-gray-800/40 border border-primary-100/50 dark:border-primary-800/30 mb-2 shadow-sm relative overflow-hidden group"
      >
        <div
          class="absolute -right-4 -top-4 w-16 h-16 bg-primary-500/10 rounded-full blur-xl group-hover:bg-primary-500/20 transition-colors"
        />
        <div class="flex gap-4 relative z-10">
          <div
            class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/40 flex items-center justify-center shrink-0 shadow-inner"
          >
            <SparklesIcon
              class="w-4 h-4 text-primary-600 dark:text-primary-400"
            />
          </div>
          <div
            class="text-[11px] font-medium leading-relaxed text-gray-600 dark:text-gray-300 pt-0.5"
          >
            {{ t("support.ai_info") }}
          </div>
        </div>
      </div>

      <div
        v-if="!activeTicket"
        class="flex flex-col items-center justify-center py-16 text-center"
      >
        <div
          class="w-20 h-20 rounded-[2rem] bg-gradient-to-br from-primary-100 to-primary-50 dark:from-primary-900/30 dark:to-primary-800/10 flex items-center justify-center mb-6 shadow-sm border border-primary-200/50 dark:border-primary-700/30"
        >
          <SparklesIcon
            class="w-10 h-10 text-primary-600 dark:text-primary-500 animate-pulse"
          />
        </div>
        <h4
          class="font-black text-xl mb-3 text-gray-800 dark:text-gray-100 tracking-tight"
        >
          {{ t("support.hi") }}
        </h4>
        <p
          class="text-xs font-medium px-8 leading-relaxed text-gray-500 dark:text-gray-400"
        >
          {{ t("support.new_convo_desc") }}
        </p>
      </div>

      <SupportMessageItem
        v-for="msg in activeTicket?.messages"
        v-if="activeTicket"
        :key="msg.id"
        :message="msg"
      />

      <div
        v-if="activeTicket?.handled_by === 'ai'"
        class="flex justify-center pt-6 pb-2"
      >
        <button
          class="px-6 py-2.5 rounded-full bg-white/60 dark:bg-gray-800/60 backdrop-blur-md border border-gray-200/50 dark:border-gray-700/50 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 hover:border-primary-300 dark:hover:border-primary-700 hover:text-primary-600 dark:hover:text-primary-400 hover:shadow-md transition-all duration-300"
          :disabled="loading"
          @click="$emit('transferToHuman')"
        >
          {{ t("support.talk_to_human") }}
        </button>
      </div>
    </div>

    <slot name="footer" />
  </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { SparklesIcon } from "@heroicons/vue/24/outline";
import SupportMessageItem from "./SupportMessageItem.vue";

const { t } = useI18n();

const props = defineProps({
  activeTicket: Object,
  loading: Boolean,
});

defineEmits(["transferToHuman"]);

const chatContainer = ref(null);

const scrollToBottom = () => {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
  });
};

watch(
  () => props.activeTicket?.messages,
  () => {
    scrollToBottom();
  },
  { deep: true },
);

onMounted(() => {
  scrollToBottom();
});

defineExpose({ scrollToBottom });
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
