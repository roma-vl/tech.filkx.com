<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useCartStore } from "@/entities/order/model/cartStore";
import api from "@/shared/services/api/apiClient";
import UiDropdown from "@/shared/ui/UiDropdown.vue";
import { mapCatalogProduct } from "@/entities/product/lib/mapCatalogProduct";

const cartStore = useCartStore();
const { t } = useI18n();

interface FaqItem {
  q: string;
  a: string;
}

interface TicketItem {
  id: string | number;
  subject: string;
  status: string;
  createdAt: string;
  [key: string]: any;
}

interface TicketMessage {
  id: string | number;
  message: string;
  isAdmin: boolean;
  createdAt: string;
  filePath?: string;
  fileName?: string;
}

const activeFaqIndex = ref<number | null>(null);
const faqs = computed<FaqItem[]>(() => [
  { q: t("account.support.faq.q1"), a: t("account.support.faq.a1") },
  { q: t("account.support.faq.q2"), a: t("account.support.faq.a2") },
  { q: t("account.support.faq.q3"), a: t("account.support.faq.a3") },
  { q: t("account.support.faq.q4"), a: t("account.support.faq.a4") },
]);

const toggleFaq = (i: number) => {
  activeFaqIndex.value = activeFaqIndex.value === i ? null : i;
};

// Ticket categories are persisted as a literal Ukrainian label embedded in the
// ticket subject (see submitTicket's fullSubject below), so the option values
// below must stay untranslated to avoid changing what gets stored — only the
// dropdown's displayed labels are localized.
const TICKET_CATEGORY_VALUES = [
  "Проблеми із замовленням",
  "Гарантія та повернення",
  "Технічна підтримка",
  "Оплата та рахунки",
] as const;

const ticketForm = ref({
  subject: "",
  category: TICKET_CATEGORY_VALUES[0] as string,
  message: "",
});

const ticketCategoryLabels: Record<string, string> = {
  "Проблеми із замовленням": "account.support.categories.orderIssues",
  "Гарантія та повернення": "account.support.categories.warrantyReturns",
  "Технічна підтримка": "account.support.categories.technicalSupport",
  "Оплата та рахунки": "account.support.categories.paymentBilling",
};

const ticketCategoryOptions = computed(() =>
  TICKET_CATEGORY_VALUES.map((value) => ({
    value,
    label: t(ticketCategoryLabels[value]),
  })),
);

const ticketsList = ref<TicketItem[]>([]);
const loadingTickets = ref(false);
const isSubmitting = ref(false);

const selectedTicket = ref<TicketItem | null>(null);
const linkedTicketProduct = computed(() =>
  mapCatalogProduct(selectedTicket.value?.product),
);
const selectedTicketMessages = ref<TicketMessage[]>([]);
const replyText = ref("");
const replying = ref(false);

const getStatusLabel = (status: string) => {
  switch (status) {
    case "new":
      return t("account.support.statuses.new");
    case "accepted":
      return t("account.support.statuses.accepted");
    case "done":
      return t("account.support.statuses.done");
    case "archived":
      return t("account.support.statuses.archived");
    default:
      return status;
  }
};

const getStatusClass = (status: string) => {
  switch (status) {
    case "new":
      return "text-blue-500 bg-blue-500/10 border border-blue-500/20";
    case "accepted":
      return "text-amber-500 bg-amber-500/10 border border-amber-500/20";
    case "done":
      return "text-[#00a046] bg-emerald-500/10 border border-emerald-500/20";
    default:
      return "text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700";
  }
};

const parseTicketSubject = (ticket: TicketItem) => {
  const match = ticket.subject.match(/^\[(.*?)\] (.*)$/);
  if (match) {
    return { category: match[1], subject: match[2] };
  }
  return {
    category: t("account.support.categoryFallback"),
    subject: ticket.subject,
  };
};

const formatDate = (dateStr?: string) => {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleDateString("uk-UA", {
    month: "short",
    day: "2-digit",
    year: "numeric",
  });
};

const formatTime = (dateStr?: string) => {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleTimeString("uk-UA", {
    hour: "2-digit",
    minute: "2-digit",
  });
};

const fetchTickets = async () => {
  loadingTickets.value = true;
  try {
    const response = await api.get("/support/tickets");
    if (response.data && response.data.status === "success") {
      ticketsList.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to load tickets:", error);
    cartStore.addToast(t("account.support.toasts.loadTicketsFailed"), "error");
  } finally {
    loadingTickets.value = false;
  }
};

const openTicket = async (ticket: TicketItem) => {
  selectedTicket.value = ticket;
  try {
    const response = await api.get(`/support/tickets/${ticket.id}`);
    if (response.data && response.data.status === "success") {
      selectedTicketMessages.value =
        response.data.data.publicMessages || response.data.data.messages || [];
      api.post(`/support/tickets/${ticket.id}/mark-as-read`).catch(() => {});
    }
  } catch (error) {
    console.error("Failed to load ticket details:", error);
    cartStore.addToast(
      t("account.support.toasts.loadTicketDetailsFailed"),
      "error",
    );
  }
};

const sendTicketReply = async () => {
  if (!replyText.value.trim() || !selectedTicket.value || replying.value)
    return;
  replying.value = true;
  try {
    const response = await api.post(
      `/support/tickets/${selectedTicket.value.id}/message`,
      {
        message: replyText.value,
      },
    );
    if (response.data && response.data.status === "success") {
      selectedTicketMessages.value.push(response.data.data);
      replyText.value = "";
    }
  } catch (error) {
    console.error("Failed to reply to ticket:", error);
    cartStore.addToast(t("account.support.toasts.sendReplyFailed"), "error");
  } finally {
    replying.value = false;
  }
};

const submitTicket = async () => {
  if (!ticketForm.value.subject || !ticketForm.value.message) {
    cartStore.addToast(
      t("account.support.toasts.fillRequiredFields"),
      "warning",
    );
    return;
  }
  isSubmitting.value = true;
  try {
    const fullSubject = `[${ticketForm.value.category}] ${ticketForm.value.subject}`;
    const response = await api.post("/support/tickets", {
      subject: fullSubject,
      message: ticketForm.value.message,
    });
    if (response.data && response.data.status === "success") {
      cartStore.addToast(t("account.support.toasts.ticketCreated"), "success");
      ticketForm.value.subject = "";
      ticketForm.value.message = "";
      fetchTickets();
    }
  } catch (error) {
    console.error("Failed to submit ticket:", error);
    cartStore.addToast(t("account.support.toasts.ticketCreateFailed"), "error");
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchTickets();
});
</script>

<template>
  <div class="space-y-6 animate-fade font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: FAQ + History -->
      <div class="lg:col-span-2 space-y-6">
        <section
          class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4"
        >
          <h3
            class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-800 pb-3"
          >
            {{ t("account.support.faq.title") }}
          </h3>
          <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            <div
              v-for="(faq, idx) in faqs"
              :key="idx"
              class="py-4.5 first:pt-0 last:pb-0"
            >
              <button
                class="w-full flex items-center justify-between text-left font-extrabold text-xs md:text-sm text-zinc-800 dark:text-zinc-200 hover:text-[#00a046] transition-colors py-1"
                @click="toggleFaq(idx)"
              >
                <span>{{ faq.q }}</span>
                <span
                  class="material-symbols-outlined text-[20px] transition-transform duration-300"
                  :class="
                    activeFaqIndex === idx
                      ? 'rotate-180 text-[#00a046]'
                      : 'text-zinc-400'
                  "
                  >expand_more</span
                >
              </button>
              <div
                v-show="activeFaqIndex === idx"
                class="mt-2.5 text-xs md:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed"
              >
                {{ faq.a }}
              </div>
            </div>
          </div>
        </section>

        <section
          class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4"
        >
          <h3
            class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-800 pb-3"
          >
            {{ t("account.support.history.title") }}
          </h3>
          <div
            v-if="loadingTickets"
            class="py-10 text-center text-zinc-400 text-xs italic"
          >
            {{ t("account.support.history.loading") }}
          </div>
          <div
            v-else-if="ticketsList.length > 0"
            class="divide-y divide-zinc-100 dark:divide-zinc-800"
          >
            <div
              v-for="ticket in ticketsList"
              :key="ticket.id"
              class="py-4.5 first:pt-0 last:pb-0 flex flex-wrap justify-between items-center gap-4 text-xs md:text-sm cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 px-2 rounded-xl transition-all"
              @click="openTicket(ticket)"
            >
              <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                  <span
                    class="font-extrabold text-zinc-800 dark:text-zinc-200 text-sm md:text-base hover:text-[#00a046]"
                    >{{ parseTicketSubject(ticket).subject }}</span
                  >
                  <span
                    class="text-[9px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 px-2.5 py-1 rounded-lg border border-zinc-100 dark:border-zinc-700"
                    >{{ parseTicketSubject(ticket).category }}</span
                  >
                </div>
                <p
                  class="text-zinc-400 dark:text-zinc-500 text-xs mt-1.5 flex items-center gap-2"
                >
                  <span>{{
                    t("account.support.history.idLabel", { id: ticket.id })
                  }}</span>
                  <span>•</span>
                  <span>{{
                    t("account.support.history.createdLabel", {
                      date: formatDate(ticket.createdAt),
                    })
                  }}</span>
                </p>
              </div>
              <span
                class="inline-block px-3.5 py-1.5 rounded-lg font-extrabold uppercase text-[10px] tracking-wider"
                :class="getStatusClass(ticket.status)"
                >{{ getStatusLabel(ticket.status) }}</span
              >
            </div>
          </div>
          <p
            v-else
            class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 italic text-center py-4"
          >
            {{ t("account.support.history.empty") }}
          </p>
        </section>
      </div>

      <!-- Right: Submit Ticket -->
      <section
        class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4 h-fit"
      >
        <h3
          class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-800 pb-3"
        >
          {{ t("account.support.form.title") }}
        </h3>
        <form class="space-y-4" @submit.prevent="submitTicket">
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-455 dark:text-zinc-500 uppercase tracking-wider"
              >{{ t("account.support.form.categoryLabel") }}</label
            >
            <UiDropdown
              v-model="ticketForm.category"
              :options="ticketCategoryOptions"
              trigger-class="w-full"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >{{ t("account.support.form.subjectLabel") }}</label
            >
            <input
              v-model="ticketForm.subject"
              type="text"
              :placeholder="t('account.support.form.subjectPlaceholder')"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-xs md:text-sm text-zinc-850 dark:text-zinc-150 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none"
            />
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
              >{{ t("account.support.form.descriptionLabel") }}</label
            >
            <textarea
              v-model="ticketForm.message"
              rows="4"
              :placeholder="t('account.support.form.descriptionPlaceholder')"
              required
              class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-xs md:text-sm text-zinc-850 dark:text-zinc-150 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none resize-none"
            />
          </div>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs md:text-sm py-3 rounded-lg transition-all uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm disabled:opacity-50"
          >
            <span
              v-if="isSubmitting"
              class="animate-spin material-symbols-outlined text-[16px] md:text-[18px]"
              >progress_activity</span
            >
            <span
              v-else
              class="material-symbols-outlined text-[16px] md:text-[18px]"
              >send</span
            >
            {{ t("account.support.form.submit") }}
          </button>
        </form>
      </section>
    </div>

    <!-- Ticket Chat Modal -->
    <div
      v-if="selectedTicket"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
      @click.self="selectedTicket = null"
    >
      <div
        class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-2xl w-full max-w-2xl overflow-hidden shadow-2xl flex flex-col max-h-[85vh] animate-fade"
      >
        <!-- Modal Header -->
        <div
          class="p-6 border-b border-zinc-100 dark:border-zinc-800 flex justify-between items-center bg-zinc-50/50 dark:bg-zinc-900/50"
        >
          <div>
            <div class="flex items-center gap-3 flex-wrap">
              <h3
                class="font-extrabold text-sm md:text-base text-zinc-900 dark:text-white"
              >
                {{ parseTicketSubject(selectedTicket).subject }}
              </h3>
              <span
                class="inline-block px-3 py-1 rounded-lg font-extrabold uppercase text-[10px] tracking-wider"
                :class="getStatusClass(selectedTicket.status)"
              >
                {{ getStatusLabel(selectedTicket.status) }}
              </span>
            </div>
            <p class="text-[11px] text-zinc-400 mt-1">
              {{
                t("account.support.history.ticketIdLabel", {
                  id: selectedTicket.id,
                  date: formatDate(selectedTicket.createdAt),
                })
              }}
            </p>
            <router-link
              v-if="linkedTicketProduct"
              :to="{
                name: 'product-detail',
                params: { id: linkedTicketProduct.slug },
              }"
              class="inline-flex items-center gap-1.5 mt-1.5 text-[11px] font-bold text-[#00a046] hover:underline"
            >
              <span class="material-symbols-outlined text-[13px]"
                >inventory_2</span
              >
              {{ linkedTicketProduct.name }}
            </router-link>
          </div>
          <button
            class="text-zinc-400 hover:text-zinc-600 dark:hover:text-white p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors flex items-center justify-center"
            @click="selectedTicket = null"
          >
            <span class="material-symbols-outlined text-[24px]">close</span>
          </button>
        </div>

        <!-- Chat Messages -->
        <div
          class="flex-1 overflow-y-auto p-6 space-y-4 bg-zinc-50/30 dark:bg-zinc-950/20 min-h-[300px]"
        >
          <div
            v-for="msg in selectedTicketMessages"
            :key="msg.id"
            class="flex flex-col"
            :class="msg.isAdmin ? 'items-start' : 'items-end'"
          >
            <div class="flex items-center gap-2 mb-1">
              <span class="text-[10px] font-extrabold uppercase text-zinc-400">
                {{
                  msg.isAdmin
                    ? t("account.support.chat.support")
                    : t("account.support.chat.you")
                }}
              </span>
              <span class="text-[9px] text-zinc-400">
                {{ formatTime(msg.createdAt) }}
              </span>
            </div>
            <div
              class="max-w-[85%] px-4 py-3 rounded-2xl text-xs md:text-sm shadow-sm"
              :class="
                msg.isAdmin
                  ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 rounded-tl-none'
                  : 'bg-[#00a046] text-white rounded-tr-none'
              "
            >
              <p class="whitespace-pre-line leading-relaxed">
                {{ msg.message }}
              </p>

              <!-- Attachment if any -->
              <div
                v-if="msg.filePath"
                class="mt-2 pt-2 border-t border-white/10 dark:border-zinc-700/50 flex items-center gap-2 text-xs"
              >
                <span class="material-symbols-outlined text-[16px]"
                  >attachment</span
                >
                <a
                  :href="msg.filePath"
                  target="_blank"
                  class="underline hover:opacity-80 truncate max-w-[150px]"
                >
                  {{ msg.fileName || t("account.support.chat.file") }}
                </a>
              </div>
            </div>
          </div>
          <div
            v-if="selectedTicketMessages.length === 0"
            class="text-center text-zinc-400 py-10 italic text-xs"
          >
            {{ t("account.support.chat.noMessages") }}
          </div>
        </div>

        <!-- Chat Input Footer -->
        <div
          class="p-4 border-t border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900"
        >
          <form class="flex gap-3 items-end" @submit.prevent="sendTicketReply">
            <div class="flex-1 relative">
              <textarea
                v-model="replyText"
                rows="2"
                :placeholder="t('account.support.chat.inputPlaceholder')"
                required
                class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-800 rounded-xl px-4 py-3 text-xs md:text-sm text-zinc-850 dark:text-zinc-150 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none resize-none"
                @keydown.enter.prevent="sendTicketReply"
              />
            </div>
            <button
              type="submit"
              :disabled="replying || !replyText.trim()"
              class="bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs py-3.5 px-6 rounded-xl transition-all uppercase tracking-wider flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed h-fit shadow-sm"
            >
              <span
                v-if="replying"
                class="animate-spin material-symbols-outlined text-[18px]"
                >progress_activity</span
              >
              <span v-else class="material-symbols-outlined text-[18px]"
                >send</span
              >
              <span>{{ t("account.support.chat.send") }}</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.25s ease-out forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
