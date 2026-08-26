<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useSupportStore } from "@/entities/support/model/supportStore";
import { storeToRefs } from "pinia";
import { mapCatalogProduct } from "@/entities/product/lib/mapCatalogProduct";
import SupportMessageItem from "./SupportMessageItem.vue";
import SupportChatInput from "./SupportChatInput.vue";

const { t } = useI18n();
const supportStore = useSupportStore();
const {
  activeTicket,
  activeTicketMessages,
  loadingTicket,
  sending,
  pendingProduct,
} = storeToRefs(supportStore);

// Either the product the shopper is about to ask about (new, uncreated
// ticket) or the one an existing ticket was already opened about - shown so
// whoever answers (customer re-reading their own ticket, or a support agent)
// never has to ask "which product do you mean?".
const linkedProduct = computed(() => {
  if (pendingProduct.value) return pendingProduct.value;

  const mapped = mapCatalogProduct(activeTicket.value?.product);
  return mapped
    ? {
        id: mapped.id,
        slug: mapped.slug,
        name: mapped.name,
        image: mapped.image,
      }
    : null;
});

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
    <router-link
      v-if="linkedProduct"
      :to="{ name: 'product-detail', params: { id: linkedProduct.slug } }"
      class="shrink-0 flex items-center gap-2.5 px-4 py-2.5 border-b border-zinc-100 dark:border-zinc-800 bg-emerald-50/50 dark:bg-emerald-900/10 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors"
    >
      <img
        :src="linkedProduct.image"
        :alt="linkedProduct.name"
        class="w-9 h-9 rounded-lg object-contain bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shrink-0"
      />
      <div class="min-w-0">
        <p
          class="text-[10px] font-bold text-[#00a046] uppercase tracking-wider"
        >
          {{ t("support.chat.aboutProduct") }}
        </p>
        <p
          class="text-xs font-semibold text-zinc-700 dark:text-zinc-200 truncate"
        >
          {{ linkedProduct.name }}
        </p>
      </div>
      <span
        class="material-symbols-outlined text-[16px] text-zinc-400 ml-auto shrink-0"
        >open_in_new</span
      >
    </router-link>

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
