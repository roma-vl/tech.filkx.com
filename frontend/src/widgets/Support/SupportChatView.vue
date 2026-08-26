<script setup lang="ts">
import { nextTick, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useSupportStore } from "@/entities/support/model/supportStore";
import { storeToRefs } from "pinia";
import SupportMessageItem from "./SupportMessageItem.vue";
import SupportChatInput from "./SupportChatInput.vue";

const { t } = useI18n();
const supportStore = useSupportStore();
const { activeTicket, activeTicketMessages, loadingTicket, sending } =
  storeToRefs(supportStore);

const scrollContainer = ref<HTMLElement | null>(null);

const scrollToBottom = () => {
  nextTick(() => {
    if (scrollContainer.value) {
      scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
    }
  });
};

watch(activeTicketMessages, scrollToBottom, { deep: true });
onMounted(scrollToBottom);

const handleSend = (message: string) => {
  supportStore.sendMessage(message);
};
</script>

<template>
  <div class="flex-1 flex flex-col min-h-0">
    <div ref="scrollContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
      <div
        v-if="loadingTicket"
        class="flex items-center justify-center py-10 text-zinc-400"
      >
        <span class="material-symbols-outlined animate-spin text-[22px]"
          >progress_activity</span
        >
      </div>

      <div
        v-else-if="!activeTicket"
        class="flex flex-col items-center justify-center text-center py-12 px-4"
      >
        <div
          class="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/30 flex items-center justify-center mb-4"
        >
          <span class="material-symbols-outlined text-[#00a046] text-[26px]"
            >chat_bubble</span
          >
        </div>
        <h4 class="font-bold text-sm text-zinc-800 dark:text-zinc-100 mb-1.5">
          {{ t("support.chat.emptyTitle") }}
        </h4>
        <p
          class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed max-w-[85%]"
        >
          {{ t("support.chat.emptyText") }}
        </p>
      </div>

      <template v-else>
        <SupportMessageItem
          v-for="message in activeTicketMessages"
          :key="message.id"
          :message="message"
        />
      </template>
    </div>

    <SupportChatInput :sending="sending" @send="handleSend" />
  </div>
</template>
