<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { storeToRefs } from "pinia";
import { useSupportStore } from "@/entities/support/model/supportStore";
import UiButton from "@/shared/ui/UiButton.vue";
import type {
  SupportTicket,
  SupportTicketStatus,
} from "@/entities/support/types";

const { t, locale } = useI18n();
const supportStore = useSupportStore();
const { tickets, loadingTickets } = storeToRefs(supportStore);

const sortedTickets = computed(() =>
  [...tickets.value].sort(
    (a, b) => new Date(b.updatedAt).getTime() - new Date(a.updatedAt).getTime(),
  ),
);

const statusClasses: Record<SupportTicketStatus, string> = {
  new: "text-blue-500 bg-blue-500/10 border border-blue-500/20",
  accepted: "text-amber-500 bg-amber-500/10 border border-amber-500/20",
  done: "text-[#00a046] bg-emerald-500/10 border border-emerald-500/20",
  archived:
    "text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700",
  deleted:
    "text-zinc-500 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700",
};

const statusLabel = (status: SupportTicketStatus) =>
  t(`support.status.${status}`);
const statusClass = (status: SupportTicketStatus) => statusClasses[status];

const formatTimestamp = (dateStr: string) => {
  const date = new Date(dateStr);
  const isToday = date.toDateString() === new Date().toDateString();
  const localeTag = locale.value === "uk" ? "uk-UA" : "en-US";

  return isToday
    ? date.toLocaleTimeString(localeTag, { hour: "2-digit", minute: "2-digit" })
    : date.toLocaleDateString(localeTag, { day: "2-digit", month: "short" });
};

const previewText = (ticket: SupportTicket) =>
  ticket.lastMessage?.message || t("support.home.waitingForReply");

const productName = (product: NonNullable<SupportTicket["product"]>) =>
  typeof product.name === "object"
    ? product.name.uk || product.name.en
    : product.name;

const emit = defineEmits<{
  (e: "select-ticket", ticket: SupportTicket): void;
  (e: "start-new-chat"): void;
}>();
</script>

<template>
  <div class="flex-1 overflow-y-auto p-4 space-y-3">
    <div
      v-if="loadingTickets && tickets.length === 0"
      class="flex items-center justify-center py-10 text-zinc-400"
    >
      <span class="material-symbols-outlined animate-spin text-[22px]"
        >progress_activity</span
      >
    </div>

    <template v-else-if="sortedTickets.length > 0">
      <h4
        class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider px-1"
      >
        {{ t("support.home.yourConversations") }}
      </h4>
      <button
        v-for="ticket in sortedTickets"
        :key="ticket.id"
        type="button"
        class="w-full text-left p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-[#00a046]/40 hover:bg-emerald-50/50 dark:hover:bg-emerald-900/10 transition-colors"
        @click="emit('select-ticket', ticket)"
      >
        <div class="flex items-start justify-between gap-2">
          <span
            class="font-semibold text-sm text-zinc-800 dark:text-zinc-100 truncate"
            >{{ ticket.subject }}</span
          >
          <span
            v-if="ticket.unreadCount"
            class="shrink-0 w-2 h-2 rounded-full bg-red-500 mt-1.5"
          />
        </div>
        <p class="text-xs text-zinc-500 dark:text-zinc-400 truncate mt-0.5">
          {{ previewText(ticket) }}
        </p>
        <p
          v-if="ticket.product"
          class="flex items-center gap-1 text-[10px] font-semibold text-[#00a046] mt-1 truncate"
        >
          <span class="material-symbols-outlined text-[12px]">inventory_2</span>
          {{ productName(ticket.product) }}
        </p>
        <div class="flex items-center justify-between mt-2">
          <span
            class="inline-block px-2 py-0.5 rounded-md font-bold uppercase text-[9px] tracking-wide"
            :class="statusClass(ticket.status)"
            >{{ statusLabel(ticket.status) }}</span
          >
          <span
            class="text-[10px] text-zinc-400 dark:text-zinc-500 font-medium"
            >{{ formatTimestamp(ticket.updatedAt) }}</span
          >
        </div>
      </button>
    </template>

    <p
      v-else
      class="text-center text-xs text-zinc-400 dark:text-zinc-500 italic py-6"
    >
      {{ t("support.home.empty") }}
    </p>

    <UiButton class="w-full mt-2" @click="emit('start-new-chat')">
      <template #prefix>
        <span class="material-symbols-outlined text-[18px]">add_comment</span>
      </template>
      {{ t("support.home.startNewChat") }}
    </UiButton>
  </div>
</template>
