<script setup lang="ts">
import { onMounted, onUnmounted } from "vue";
import { storeToRefs } from "pinia";
import { useI18n } from "vue-i18n";
import { useSupportStore } from "@/entities/support/model/supportStore";
import SupportHomeView from "./SupportHomeView.vue";
import SupportChatView from "./SupportChatView.vue";

const { t } = useI18n();
const supportStore = useSupportStore();
const { isOpen, view, unreadCount, activeTicket } = storeToRefs(supportStore);

const handleVisibilityChange = () => {
  if (document.hidden) {
    supportStore.stopPolling();
  } else {
    supportStore.startPolling();
  }
};

onMounted(() => {
  supportStore.startPolling();
  document.addEventListener("visibilitychange", handleVisibilityChange);
});

onUnmounted(() => {
  supportStore.stopPolling();
  document.removeEventListener("visibilitychange", handleVisibilityChange);
});
</script>

<template>
  <div
    class="fixed right-4 sm:right-6 z-[95]"
    style="bottom: calc(1rem + env(safe-area-inset-bottom))"
  >
    <div class="relative flex flex-col items-end">
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-3 opacity-0 scale-95"
        enter-to-class="translate-y-0 opacity-100 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100 scale-100"
        leave-to-class="translate-y-3 opacity-0 scale-95"
      >
        <div
          v-if="isOpen"
          class="mb-3 w-[min(380px,calc(100vw-2rem))] h-[min(560px,calc(100dvh-8rem))] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden origin-bottom-right"
        >
          <!-- Header -->
          <div
            class="shrink-0 flex items-center gap-3 p-4 border-b border-zinc-100 dark:border-zinc-800"
          >
            <button
              v-if="view === 'chat'"
              type="button"
              class="w-8 h-8 -ml-1 rounded-lg flex items-center justify-center text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors shrink-0"
              :aria-label="t('support.panel.back')"
              @click="supportStore.goHome"
            >
              <span class="material-symbols-outlined text-[20px]"
                >arrow_back</span
              >
            </button>
            <div
              v-else
              class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/30 flex items-center justify-center shrink-0"
            >
              <span class="material-symbols-outlined text-[#00a046] text-[20px]"
                >support_agent</span
              >
            </div>

            <div class="min-w-0 flex-1">
              <h3
                class="font-bold text-sm text-zinc-900 dark:text-white truncate"
              >
                {{
                  view === "chat"
                    ? activeTicket?.subject || t("support.home.startNewChat")
                    : t("support.panel.title")
                }}
              </h3>
              <p
                v-if="view === 'home'"
                class="text-xs text-zinc-500 dark:text-zinc-400 truncate"
              >
                {{ t("support.panel.subtitle") }}
              </p>
            </div>

            <button
              type="button"
              class="w-8 h-8 rounded-lg flex items-center justify-center text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors shrink-0"
              :aria-label="t('support.panel.close')"
              @click="supportStore.close"
            >
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <!-- Body -->
          <SupportHomeView
            v-if="view === 'home'"
            @select-ticket="supportStore.selectTicket"
            @start-new-chat="supportStore.startNewChat"
          />
          <SupportChatView v-else />
        </div>
      </Transition>

      <button
        type="button"
        class="w-14 h-14 rounded-full bg-[#00a046] hover:bg-[#00b050] shadow-lg shadow-emerald-900/20 flex items-center justify-center text-white transition-all hover:scale-105 active:scale-95 relative"
        :aria-label="t('support.bubble.ariaLabel')"
        @click="supportStore.toggleOpen"
      >
        <span class="material-symbols-outlined text-[26px]">{{
          isOpen ? "close" : "chat_bubble"
        }}</span>

        <span
          v-if="unreadCount > 0 && !isOpen"
          class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 rounded-full bg-red-500 ring-2 ring-white dark:ring-zinc-950 flex items-center justify-center text-[10px] font-bold text-white"
        >
          {{ unreadCount > 9 ? "9+" : unreadCount }}
        </span>
      </button>
    </div>
  </div>
</template>
