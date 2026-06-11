<template>
  <div class="fixed bottom-6 right-6 z-50">
    <div class="relative">
      <button
        class="w-16 h-16 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 dark:from-primary-600 dark:to-primary-800 shadow-[0_8px_32px_rgba(var(--color-primary-500),0.4)] flex items-center justify-center transition-all duration-300 hover:scale-[1.05] active:scale-95 group relative border border-white/40 dark:border-white/10 overflow-hidden"
        @click="supportStore.toggleOpen"
      >
        <span
          class="absolute inset-0 bg-white/20 dark:bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
        />

        <SupportIcon
          v-if="!isOpen"
          class="w-7 h-7 text-white relative z-10 drop-shadow-md transition-transform duration-300 group-hover:-translate-y-0.5"
        />
        <CrossIcon
          v-else
          class="w-7 h-7 text-white relative z-10 drop-shadow-md transition-transform duration-300 rotate-90"
        />
      </button>

      <span
        v-if="unreadCount > 0 && !isOpen"
        class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 rounded-full flex items-center justify-center text-[10px] font-black text-white shadow-[0_4px_12px_rgba(239,68,68,0.5)] ring-2 ring-white dark:ring-gray-900 animate-bounce"
      >
        {{ unreadCount }}
      </span>
    </div>

    <transition
      enter-active-class="transition duration-500 cubic-bezier(0.34, 1.56, 0.64, 1)"
      enter-from-class="translate-y-8 opacity-0 scale-90 blur-xl"
      enter-to-class="translate-y-0 opacity-100 scale-100 blur-0"
      leave-active-class="transition duration-300 ease-in"
      leave-from-class="translate-y-0 opacity-100 scale-100 blur-0"
      leave-to-class="translate-y-8 opacity-0 scale-90 blur-xl"
    >
      <div
        v-if="isOpen"
        class="absolute bottom-20 right-0 w-[400px] h-[650px] bg-white/70 dark:bg-gray-900/70 backdrop-blur-3xl rounded-[2.5rem] border border-white/50 dark:border-white/10 shadow-[0_32px_128px_-16px_rgba(0,0,0,0.2)] dark:shadow-[0_32px_128px_-16px_rgba(0,0,0,0.8)] flex flex-col overflow-hidden ring-1 ring-black/5 dark:ring-white/5"
      >
        <SupportHeader
          :current-view="currentView"
          :active-ticket="activeTicket"
          :user="authStore.user"
          @go-home="supportStore.goHome"
          @close="supportStore.isOpen = false"
        />

        <div class="flex-1 overflow-hidden flex flex-col relative">
          <SupportHomeView
            v-if="currentView === 'home'"
            :tickets="tickets"
            @select-ticket="supportStore.selectTicket"
            @start-new-chat="supportStore.startNewChat"
          />

          <SupportChatView
            v-else-if="currentView === 'chat'"
            :active-ticket="activeTicket"
            :loading="loading"
            @transfer-to-human="supportStore.transferToHuman"
          >
            <template #footer>
              <SupportChatInput
                :loading="loading"
                @send-message="supportStore.sendMessage"
              />
            </template>
          </SupportChatView>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { storeToRefs } from "pinia";
import { XMarkIcon as CrossIcon } from "@heroicons/vue/24/outline";
import { useAuthStore } from "@/stores/auth";
import { useSupportStore } from "@/stores/support";

import SupportHeader from "./SupportHeader.vue";
import SupportHomeView from "./SupportHomeView.vue";
import SupportChatView from "./SupportChatView.vue";
import SupportChatInput from "./SupportChatInput.vue";
import SupportIcon from "@/components/Icon/SupportIcon.vue";

const authStore = useAuthStore();
const supportStore = useSupportStore();

const { isOpen, currentView, loading, tickets, activeTicket, unreadCount } =
  storeToRefs(supportStore);

onMounted(() => {
  supportStore.fetchTickets();
  setInterval(() => supportStore.fetchTickets(), 30000);
});
</script>
