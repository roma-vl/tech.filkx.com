<template>
  <div
    class="flex flex-col border-b border-gray-50 dark:border-gray-700/50 bg-white dark:bg-gray-800 animate-in slide-in-from-top-4 duration-500"
  >
    <div class="p-6 flex items-center justify-between gap-6">
      <div class="flex items-center gap-4 min-w-0">
        <div
          class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/10 flex items-center justify-center flex-shrink-0 shadow-sm border border-primary-100/50 dark:border-primary-500/10"
        >
          <TicketIcon class="w-6 h-6 text-primary-600 dark:text-primary-400" />
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-3 mb-1">
            <span
              class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]"
            >Ticket #{{ ticket.id }}</span>
            <div class="w-1 h-1 rounded-full bg-gray-200 dark:bg-gray-700" />
            <span
              class="text-[11px] font-black text-gray-400 uppercase tracking-widest"
            >{{ formatDate(ticket.createdAt) }}</span>
          </div>
          <h2
            class="text-xl font-black text-gray-900 dark:text-white truncate tracking-tight uppercase leading-none"
          >
            {{ ticket.subject }}
          </h2>
        </div>
      </div>

      <div class="flex items-center gap-3 flex-shrink-0">
        <!-- AI Handling Toggle -->
        <AppButton
          v-if="ticket.handledBy === 'ai'"
          @click="$emit('updateStatus', 'accepted')"
        >
          <template #prefix>
            <SparklesIcon
              class="w-4 h-4 group-hover:rotate-12 transition-transform mr-2"
            />
          </template>
          {{ t("admin.support.ticket.take_over") }}
        </AppButton>
        <AppButton
          v-else
          variant="secondary"
          @click="$emit('transferToAi')"
        >
          <template #prefix>
            <SparklesIcon
              class="w-4 h-4 group-hover:rotate-12 transition-transform mr-2"
            />
          </template>
          {{ t("admin.support.ticket.return_to_ai") }}
        </AppButton>

        <!-- Resolve Button -->
        <AppButton
          v-if="ticket.status !== 'done'"
          variant="success"
          @click="$emit('updateStatus', 'done')"
        >
          {{ t("admin.support.actions.done") }}
        </AppButton>

        <!-- Delete Button -->
        <AppButton
          variant="ghost"
          :title="t('admin.support.actions.delete')"
          @click="$emit('delete')"
        >
          <TrashIcon class="w-5 h-5" />
        </AppButton>
      </div>
    </div>

    <!-- User Info & Tags Strip -->
    <div
      class="px-8 py-4 bg-gray-50/30 dark:bg-gray-900/10 border-t border-gray-50 dark:border-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >
      <div class="flex items-center gap-4">
        <span
          class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
        >{{ t("admin.support.ticket.opened_by") }}</span>
        <div
          class="flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700/50 shadow-sm"
        >
          <div
            class="w-5 h-5 rounded-lg bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-[9px] font-black text-primary-600 overflow-hidden"
          >
            <img
              v-if="ticket.user?.avatar"
              :src="ticket.user.avatar"
              class="w-full h-full object-cover"
            >
            <template v-else>
              {{ ticket.user?.name?.charAt(0) || "U" }}
            </template>
          </div>
          <span
            class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-tight"
          >{{ ticket.user?.name }}</span>
        </div>
      </div>

      <!-- Tags Section -->
      <SupportTicketTags
        :tags="ticket.tags"
        @update:tags="emit('updateTags', $event)"
      />
    </div>
  </div>
</template>

<script setup>
import { SparklesIcon, TicketIcon, TrashIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import SupportTicketTags from "@/components/admin/features/support/SupportTicketTags.vue";

const { t } = useI18n();

const props = defineProps({
  ticket: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits([
  "updateStatus",
  "delete",
  "transferToAi",
  "updateTags",
]);

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};
</script>
