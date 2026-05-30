<template>
  <div
    class="max-w-[1600px] mx-auto space-y-10 pb-20 animate-in fade-in duration-700"
  >
    <!-- Header KPI Stats -->
    <SupportKpiStats :stats="kpiStats" />

    <!-- Navigation & Global Actions -->
    <div
      class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 px-2"
    >
      <div
        class="flex items-center gap-3 bg-white dark:bg-gray-800 p-1.5 rounded-[1.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm"
      >
        <SupportTicketTabs v-model:active-tab="activeTab" />
        <button
          :class="[
            'px-6 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest cursor-pointer transition-all duration-300',
            activeTab === 'Stats'
              ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/25'
              : 'text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50',
          ]"
          @click="activeTab = 'Stats'"
        >
          {{ t("admin.support.tabs.stats") }}
        </button>
      </div>

      <div class="flex items-center gap-3">
        <button
          class="flex items-center gap-3 px-6 py-3.5 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl hover:border-primary-500 hover:text-primary-600 transition-all text-gray-400 font-black uppercase text-[10px] tracking-widest shadow-sm active:scale-95 group"
          @click="showSnippetsModal = true"
        >
          <CommandLineIcon
            class="w-4 h-4 group-hover:rotate-12 transition-transform"
          />
          {{ t("admin.support.actions.manage_snippets") }}
        </button>
      </div>
    </div>

    <!-- Main Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Left Sidebar: Ticket List -->
      <div
        v-if="activeTab !== 'Stats'"
        class="lg:col-span-3 xl:col-span-3"
      >
        <div
          class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden flex flex-col h-[850px]"
        >
          <SupportSidebarFilter
            v-model:search="searchQuery"
            v-model:tag="selectedTag"
            :available-tags="availableTags"
          />

          <SupportTicketList
            :tickets="sortedTickets"
            :selected-ticket-id="selectedTicket?.id"
            :loading="loading"
            class="flex-1"
            @select="fetchTicketDetails"
          />

          <!-- Pagination -->
          <div
            v-if="pagination.total > pagination.perPage"
            class="p-6 border-t border-gray-50 dark:border-gray-700/50 flex items-center justify-between gap-4 bg-gray-50/20"
          >
            <button
              :disabled="pagination.currentPage === 1"
              class="p-2.5 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl disabled:opacity-20 hover:border-primary-500 transition-all"
              @click="changePage(pagination.currentPage - 1)"
            >
              <ChevronLeftIcon class="w-4 h-4" />
            </button>
            <span
              class="text-[11px] font-black uppercase tracking-widest text-gray-400"
            >
              <span class="text-gray-900 dark:text-white">{{
                pagination.currentPage
              }}</span>
              / {{ pagination.lastPage }}
            </span>
            <button
              :disabled="pagination.currentPage === pagination.lastPage"
              class="p-2.5 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-xl disabled:opacity-20 hover:border-primary-500 transition-all"
              @click="changePage(pagination.currentPage + 1)"
            >
              <ChevronRightIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <!-- Center Panel: Chat Workspace -->
      <div
        v-if="activeTab !== 'Stats'"
        :class="[
          selectedTicket
            ? 'lg:col-span-6 xl:col-span-6'
            : 'lg:col-span-9 xl:col-span-9',
          'bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm flex flex-col h-[850px] overflow-hidden transition-all duration-500 ease-in-out',
        ]"
      >
        <template v-if="selectedTicket">
          <SupportTicketDetail
            :ticket="selectedTicket"
            @update-status="updateStatus"
            @update-tags="updateTags"
            @delete="deleteTicket"
            @transfer-to-ai="transferToAi"
          />

          <SupportChatMessages
            ref="chatMessages"
            :messages="messages"
            :user-name="selectedTicket.user?.name"
            :has-more="hasMoreMessages"
            :is-loading-more="isLoadingMoreMessages"
            @load-more="loadMoreMessages"
            @mark-read="handleMarkMessagesRead"
          />

          <SupportChatInput
            v-model:message="replyMessage"
            v-model:selected-file="selectedFile"
            :loading="loading"
            :snippets="snippets"
            @send="sendReply"
          />
        </template>
        <SupportPlaceholder v-else />
      </div>

      <!-- Stats View -->
      <div
        v-else
        class="lg:col-span-12"
      >
        <SupportStatsView />
      </div>

      <!-- Right Sidebar: User Context -->
      <transition
        enter-active-class="transition duration-500 ease-out"
        enter-from-class="translate-x-full opacity-0"
        enter-to-class="translate-x-0 opacity-100"
        leave-active-class="transition duration-300 ease-in"
        leave-from-class="translate-x-0 opacity-100"
        leave-to-class="translate-x-full opacity-0"
      >
        <div
          v-if="selectedTicket"
          class="lg:col-span-3 xl:col-span-3 h-[850px]"
        >
          <SupportUserSidebar
            :user="selectedTicket.user"
            :ticket="selectedTicket"
            @select-ticket="fetchTicketDetails"
          />
        </div>
      </transition>
    </div>

    <!-- Snippets Management Modal -->
    <SnippetsManagementModal
      :show="showSnippetsModal"
      :snippets="snippets"
      @close="showSnippetsModal = false"
      @add="addSnippet"
      @update="updateSnippet"
      @delete="deleteSnippet"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import {
  CheckCircleIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ClockIcon,
  CommandLineIcon,
  InboxIcon,
  UserGroupIcon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import axios from "@/services/api";
import { useAuthStore } from "@/stores/auth";
import { debounce } from "lodash";

// Feature Components
import SupportTicketTabs from "@/components/admin/features/support/SupportTicketTabs.vue";
import SupportTicketList from "@/components/admin/features/support/SupportTicketList.vue";
import SupportTicketDetail from "@/components/admin/features/support/SupportTicketDetail.vue";
import SupportChatMessages from "@/components/admin/features/support/SupportChatMessages.vue";
import SupportChatInput from "@/components/admin/features/support/SupportChatInput.vue";
import SupportUserSidebar from "@/components/admin/features/support/SupportUserSidebar.vue";
import SupportStatsView from "@/components/admin/features/support/SupportStatsView.vue";
import SnippetsManagementModal from "@/components/admin/features/support/SnippetsManagementModal.vue";

// Refactored UI Components
import SupportKpiStats from "@/components/admin/features/support/SupportKpiStats.vue";
import SupportSidebarFilter from "@/components/admin/features/support/SupportSidebarFilter.vue";
import SupportPlaceholder from "@/components/admin/features/support/SupportPlaceholder.vue";

const { t } = useI18n();
const authStore = useAuthStore();
const toast = useToast();

const activeTab = ref("All");
const selectedTicket = ref(null);
const tickets = ref([]);
const messages = ref([]);
const hasMoreMessages = ref(false);
const isLoadingMoreMessages = ref(false);
const loading = ref(false);
const replyMessage = ref("");
const selectedFile = ref(null);
const chatMessages = ref(null);
const searchQuery = ref("");
const selectedTag = ref("");
const availableTags = ref(["Bug", "Billing", "Feedback", "Urgent", "Question"]);
const snippets = ref([]);
const showSnippetsModal = ref(false);

const pagination = ref({
  total: 0,
  perPage: 20,
  currentPage: 1,
  lastPage: 1,
});

const sortedTickets = computed(() => {
  return [...tickets.value].sort((a, b) => {
    if (a.unreadCount !== b.unreadCount) {
      return (b.unreadCount || 0) - (a.unreadCount || 0);
    }
    return new Date(b.createdAt) - new Date(a.createdAt);
  });
});

const fetchTickets = async (page = 1) => {
  if (!authStore.token) return;
  loading.value = true;
  try {
    const response = await axios.get("/admin/support/tickets", {
      params: {
        status: activeTab.value,
        search: searchQuery.value,
        tag: selectedTag.value,
        page: page,
      },
    });
    tickets.value = response.data.data.data;
    pagination.value = {
      total: response.data.data.total,
      perPage: response.data.data.per_page,
      currentPage: response.data.data.current_page,
      lastPage: response.data.data.last_page,
    };

    if (selectedTicket.value) {
      const updatedTicket = tickets.value.find(
        (t) => t.id === selectedTicket.value.id,
      );
      if (updatedTicket && updatedTicket.unreadCount > 0) {
        fetchTicketDetails(selectedTicket.value.id);
      }
    }
  } catch (error) {
    console.error("Failed to fetch tickets", error);
    toast.error(t("admin.support.alerts.loadError"));
  } finally {
    loading.value = false;
  }
};

const debouncedFetch = debounce(() => fetchTickets(1), 500);

const changePage = (page) => {
  fetchTickets(page);
};

const fetchSnippets = async () => {
  try {
    const response = await axios.get("/admin/support/snippets");
    snippets.value = response.data.data;
  } catch (error) {
    console.error("Failed to fetch snippets", error);
  }
};

const addSnippet = async (data) => {
  try {
    const response = await axios.post("/admin/support/snippets", data);
    snippets.value.push(response.data.data);
    toast.success(t("admin.support.alerts.snippetAdded"));
  } catch (error) {
    console.error("Failed to add snippet", error);
    toast.error(t("admin.support.alerts.snippetError"));
  }
};

const updateSnippet = async (id, data) => {
  try {
    const response = await axios.put(`/admin/support/snippets/${id}`, data);
    const index = snippets.value.findIndex((s) => s.id === id);
    if (index !== -1) {
      snippets.value[index] = response.data.data;
    }
    toast.success(t("admin.support.alerts.snippetUpdated"));
  } catch (error) {
    console.error("Failed to update snippet", error);
    toast.error(t("admin.support.alerts.snippetError"));
  }
};

const deleteSnippet = async (id) => {
  try {
    await axios.delete(`/admin/support/snippets/${id}`);
    snippets.value = snippets.value.filter((s) => s.id !== id);
    toast.success(t("admin.support.alerts.snippetDeleted"));
  } catch (error) {
    console.error("Failed to delete snippet", error);
    toast.error(t("admin.support.alerts.snippetError"));
  }
};

const updateTags = async (tags) => {
  if (!selectedTicket.value) return;
  try {
    await axios.post(`/admin/support/tickets/${selectedTicket.value.id}/tags`, {
      tags,
    });
    selectedTicket.value.tags = tags;
    toast.success(t("admin.support.alerts.statusUpdated", { status: "Tags" }));
    fetchTickets(pagination.value.currentPage);
  } catch (error) {
    toast.error(t("admin.support.alerts.statusError"));
  }
};

const fetchTicketDetails = async (id) => {
  loading.value = true;

  try {
    const response = await axios.get(`/admin/support/tickets/${id}`);
    selectedTicket.value = response.data.data;

    // Initialize messages from ticket data
    // Backend returns latest 5 messages in chronological order
    if (selectedTicket.value.messages) {
      messages.value = selectedTicket.value.messages;
      hasMoreMessages.value = selectedTicket.value.messages.length >= 5;
    } else {
      messages.value = [];
      hasMoreMessages.value = false;
    }

    if (chatMessages.value) {
      chatMessages.value.scrollToBottom();
    }
  } catch (error) {
    console.error("Failed to fetch ticket details", error);
  } finally {
    loading.value = false;
  }
};

const loadMoreMessages = async () => {
  if (
    isLoadingMoreMessages.value ||
    !selectedTicket.value ||
    messages.value.length === 0
  )
    return;

  isLoadingMoreMessages.value = true;
  const oldestMessageId = messages.value[0].id;

  try {
    const response = await axios.get(
      `/admin/support/tickets/${selectedTicket.value.id}/messages`,
      {
        params: {
          before_id: oldestMessageId,
          limit: 5,
        },
      },
    );

    const newMessages = response.data.data;

    // Backend returns messages in default order (newest first).
    // We need to reverse them to match chronological order (oldest -> newest) for prepending
    const orderedMessages = newMessages.reverse();

    if (newMessages.length < 5) {
      hasMoreMessages.value = false;
    }

    messages.value = [...orderedMessages, ...messages.value];
  } catch (error) {
    console.error("Failed to load more messages", error);
  } finally {
    isLoadingMoreMessages.value = false;
  }
};

const handleMarkMessagesRead = async (ids) => {
  if (!selectedTicket.value || ids.length === 0) return;

  try {
    // Optimistic update
    ids.forEach((id) => {
      const msg = messages.value.find((m) => m.id === id);
      if (msg) msg.readAt = new Date().toISOString();
    });

    // Update unread count locally (approximate)
    if (selectedTicket.value.unreadCount > 0) {
      selectedTicket.value.unreadCount = Math.max(
        0,
        selectedTicket.value.unreadCount - ids.length,
      );
      // Also update in list
      const listTicket = tickets.value.find(
        (t) => t.id === selectedTicket.value.id,
      );
      if (listTicket) listTicket.unreadCount = selectedTicket.value.unreadCount;
    }

    await axios.post(
      `/admin/support/tickets/${selectedTicket.value.id}/read-messages`,
      { ids },
    );
  } catch (error) {
    console.error("Failed to mark messages as read", error);
  }
};

const sendReply = async (isInternal = false) => {
  if ((!replyMessage.value && !selectedFile.value) || !selectedTicket.value)
    return;
  loading.value = true;

  const formData = new FormData();
  if (replyMessage.value) formData.append("message", replyMessage.value);
  if (selectedFile.value) formData.append("file", selectedFile.value);
  if (isInternal) formData.append("is_internal", 1);

  try {
    const response = await axios.post(
      `/admin/support/tickets/${selectedTicket.value.id}/reply`,
      formData,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      },
    );
    messages.value.push(response.data.data);
    replyMessage.value = "";
    selectedFile.value = null;
  } catch (error) {
    console.error(error);
    toast.error(t("admin.support.alerts.replyError"));
  } finally {
    loading.value = false;
  }
};

const updateStatus = async (status) => {
  if (!selectedTicket.value) return;
  try {
    await axios.post(
      `/admin/support/tickets/${selectedTicket.value.id}/status`,
      { status },
    );
    selectedTicket.value.status = status;
    if (status === "accepted" && selectedTicket.value.handled_by === "ai") {
      selectedTicket.value.handled_by = "human";
    }
    toast.success(t("admin.support.alerts.statusUpdated", { status }));
    fetchTickets(pagination.value.currentPage);
  } catch (error) {
    toast.error(t("admin.support.alerts.statusError"));
  }
};

const transferToAi = async () => {
  if (!selectedTicket.value) return;
  try {
    const response = await axios.post(
      `/admin/support/tickets/${selectedTicket.value.id}/transfer-to-ai`,
    );
    selectedTicket.value = response.data.data;
    toast.success(t("admin.support.alerts.statusUpdated", { status: "AI" }));
    fetchTickets(pagination.value.currentPage);
  } catch (error) {
    toast.error(t("admin.support.alerts.statusError"));
  }
};

const deleteTicket = async () => {
  if (!selectedTicket.value) return;
  if (!confirm(t("admin.support.alerts.deleteConfirm"))) return;

  const ticketId = selectedTicket.value.id;

  try {
    await axios.delete(`/admin/support/tickets/${ticketId}`);

    tickets.value = tickets.value.filter((t) => t.id !== ticketId);
    selectedTicket.value = null;

    toast.success(t("admin.support.alerts.deleteSuccess"));
  } catch (error) {
    console.error("Failed to delete ticket", error);
    toast.error(t("admin.support.alerts.deleteError"));
  }
};

const kpiStats = computed(() => [
  {
    label: t("admin.support.stats.open_tickets"),
    value: tickets.value.filter(
      (t) => t.status === "new" || t.status === "accepted",
    ).length,
    icon: InboxIcon,
    colorClass:
      "bg-blue-50 dark:bg-blue-900/10 text-blue-600 dark:text-blue-400",
    glowClass: "bg-blue-500",
  },
  {
    label: t("admin.support.stats.waiting_reply"),
    value: tickets.value.filter((t) => t.unreadCount > 0).length,
    icon: ClockIcon,
    colorClass:
      "bg-orange-50 dark:bg-orange-900/10 text-orange-600 dark:text-orange-400",
    glowClass: "bg-orange-500",
  },
  {
    label: t("admin.support.stats.solved_today"),
    value: tickets.value.filter((t) => t.status === "done").length,
    icon: CheckCircleIcon,
    colorClass:
      "bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400",
    glowClass: "bg-green-500",
  },
  {
    label: t("admin.support.stats.total_users"),
    value: new Set(tickets.value.map((t) => t.userId)).size,
    icon: UserGroupIcon,
    colorClass:
      "bg-purple-50 dark:bg-purple-900/10 text-purple-600 dark:text-purple-400",
    glowClass: "bg-purple-500",
  },
]);

watch(activeTab, () => {
  if (activeTab.value !== "Stats") {
    fetchTickets(1);
  }
});

watch(searchQuery, debouncedFetch);
watch(selectedTag, () => fetchTickets(1));

onMounted(() => {
  fetchTickets();
  fetchSnippets();

  // Auto-refresh every 10 seconds
  const pollingInterval = setInterval(() => {
    fetchTickets();
  }, 10000);

  // Cleanup on unmount
  onUnmounted(() => {
    clearInterval(pollingInterval);
  });
});
</script>
