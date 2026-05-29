<template>
  <div class="flex-1 overflow-y-auto p-6 space-y-6 custom-scrollbar">
    <section v-if="tickets.length > 0">
      <h4
        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mx-1 mb-4"
      >
        {{ t("support.your_conversations") }}
      </h4>
      <div class="space-y-3">
        <div
          v-for="ticket in tickets"
          :key="ticket.id"
          class="p-4 rounded-3xl bg-white/60 dark:bg-gray-800/40 hover:bg-white dark:hover:bg-gray-800 hover:shadow-lg hover:shadow-black/5 dark:hover:shadow-black/20 border border-transparent hover:border-primary-200 dark:hover:border-primary-800 transition-all duration-300 cursor-pointer group backdrop-blur-sm"
          @click="$emit('selectTicket', ticket)"
        >
          <div class="flex items-center gap-4">
            <div
              class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-50 to-primary-100 dark:from-primary-900/40 dark:to-primary-800/20 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0 shadow-inner border border-primary-200/50 dark:border-primary-700/50 group-hover:scale-105 transition-transform"
            >
              <SparklesIcon
                v-if="ticket.handled_by === 'ai'"
                class="w-6 h-6"
              />
              <UserIcon
                v-else
                class="w-6 h-6"
              />
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between mb-1">
                <span class="font-bold text-sm text-gray-800 dark:text-gray-200 truncate pr-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{
                  ticket.subject
                }}</span>
                <span
                  class="text-[10px] text-gray-400 dark:text-gray-500 font-bold whitespace-nowrap"
                >{{ formatRelativeTime(ticket.updatedAt) }}</span>
              </div>
              <p
                class="text-xs text-gray-500 dark:text-gray-400 truncate leading-snug font-medium"
              >
                {{ ticket.lastMessage?.message || t("support.waiting") }}
              </p>
            </div>
            <div
              v-if="ticket.unreadCount > 0"
              class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0 shadow-[0_0_8px_rgba(239,68,68,0.6)] animate-pulse"
            />
          </div>
        </div>
      </div>
    </section>

    <button
      class="w-full p-4 rounded-3xl bg-gradient-to-r from-primary-500 to-primary-600 dark:from-primary-600 dark:to-primary-700 text-white font-bold flex items-center justify-center gap-3 transition-all duration-300 hover:scale-[1.02] active:scale-95 shadow-lg shadow-primary-500/25 border border-white/20 dark:border-white/10"
      @click="$emit('startNewChat')"
    >
      <PlusIcon class="w-5 h-5 flex-shrink-0" />
      <span class="truncate">{{ t("support.start_new_chat") }}</span>
    </button>

    <div class="grid grid-cols-2 gap-4">
      <div
        class="p-5 rounded-[2.5rem] bg-white/40 dark:bg-gray-800/20 backdrop-blur-md border border-white/60 dark:border-white/5 shadow-sm overflow-hidden relative group"
      >
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-orange-500/10 rounded-full blur-xl group-hover:bg-orange-500/20 transition-colors" />
        <ClockIcon class="w-7 h-7 text-orange-500 mb-3 relative z-10" />
        <p
          class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tighter mb-1 relative z-10"
        >
          {{ t("support.avg_response_time") }}
        </p>
        <p class="text-sm font-black text-gray-700 dark:text-gray-200 relative z-10">
          {{ t("support.minutes") }}
        </p>
      </div>

      <router-link
        to="/faq"
        class="p-5 rounded-[2.5rem] bg-white/40 dark:bg-gray-800/20 backdrop-blur-md border border-white/60 dark:border-white/5 shadow-sm overflow-hidden relative cursor-pointer hover:border-primary-300 dark:hover:border-primary-700 group transition-all duration-300 block"
      >
        <div class="absolute -right-4 -top-4 w-16 h-16 bg-primary-500/10 rounded-full blur-xl group-hover:bg-primary-500/20 transition-colors" />
        <HelperCenterIcon
          class="w-7 h-7 text-primary-500 mb-3 relative z-10 group-hover:scale-110 transition-transform duration-300"
        />
        <p
          class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tighter mb-1 relative z-10"
        >
          {{ t("support.help_center") }}
        </p>
        <p class="text-sm font-black text-gray-700 dark:text-gray-200 relative z-10">
          {{ t("support.find_answer") }}
        </p>
      </router-link>
    </div>

    <div class="text-center pt-6 pb-2">
      <p
        class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4"
      >
        {{ t("support.other_ways") }}
      </p>
      <div class="flex justify-center gap-6">
        <a
          href="viber://pa?chatURI=filkx"
          target="_blank"
          class="flex flex-col items-center gap-2.5 group"
        >
          <div
            class="w-14 h-14 rounded-2xl bg-white/60 dark:bg-white/5 border border-white/50 dark:border-white/10 flex items-center justify-center group-hover:bg-[#7360f2] group-hover:border-[#7360f2] transition-all duration-300 group-hover:shadow-[0_8px_16px_rgba(115,96,242,0.3)] shadow-sm"
          >
            <ViberIcon
              class="w-7 h-7 text-[#7360f2] dark:text-[#8b7cf7] group-hover:text-white transition-colors duration-300"
            />
          </div>
          <span
            class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors"
          >{{ t("support.viber") }}</span>
        </a>
        <a
          href="https://t.me/filkx_support"
          target="_blank"
          class="flex flex-col items-center gap-2.5 group"
        >
          <div
            class="w-14 h-14 rounded-2xl bg-white/60 dark:bg-white/5 border border-white/50 dark:border-white/10 flex items-center justify-center group-hover:bg-[#0088cc] group-hover:border-[#0088cc] transition-all duration-300 group-hover:shadow-[0_8px_16px_rgba(0,136,204,0.3)] shadow-sm"
          >
            <TelegramIcon
              class="w-7 h-7 text-[#0088cc] dark:text-[#33a8e6] group-hover:text-white transition-colors duration-300"
            />
          </div>
          <span
            class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors"
          >{{ t("support.telegram") }}</span>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import {useI18n} from "vue-i18n";
import {
  ClockIcon,
  PlusIcon,
  QuestionMarkCircleIcon as HelperCenterIcon,
  SparklesIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";
import TelegramIcon from "@/components/Icon/TelegramIcon.vue";
import ViberIcon from "@/components/Icon/ViberIcon.vue";

const { t } = useI18n();

defineProps({
  tickets: Array,
});

defineEmits(["selectTicket", "startNewChat"]);

const formatRelativeTime = (date) => {
  if (!date) return "";
  const d = new Date(date);
  const now = new Date();
  const diff = now - d;
  if (diff < 60000) return "щойно";
  if (diff < 3600000) return `${Math.floor(diff / 60000)}хв`;
  if (diff < 86400000) return `${Math.floor(diff / 3600000)}год`;
  return d.toLocaleDateString();
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
