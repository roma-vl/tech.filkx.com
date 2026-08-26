<template>
  <div
    class="flex flex-col h-full overflow-y-auto custom-scrollbar bg-white dark:bg-gray-800"
  >
    <div
      v-for="ticket in tickets"
      :key="ticket.id"
      class="px-5 py-4 cursor-pointer transition-all duration-300 border-b relative group"
      :class="[
        ticket.unreadCount > 0
          ? 'bg-red-50/50 dark:bg-red-900/10 border-b-red-100 dark:border-b-red-900/30 border-l-4 border-l-red-500 hover:bg-red-50 dark:hover:bg-red-900/20'
          : 'border-b-gray-50 dark:border-b-gray-700/50 hover:bg-gray-50/50 dark:hover:bg-gray-900/30',
        selectedTicketId === ticket.id
          ? 'bg-primary-50/50 dark:bg-primary-900/10'
          : '',
      ]"
      @click="$emit('select', ticket.id)"
    >
      <!-- Selection Indicator -->
      <div
        v-if="selectedTicketId === ticket.id"
        class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-10 bg-primary-500 rounded-r-full shadow-lg shadow-primary-500/50"
      />

      <div class="flex items-start gap-4">
        <!-- Avatar -->
        <div class="flex-shrink-0 relative">
          <div
            class="w-12 h-12 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-black shadow-sm group-hover:scale-105 transition-transform duration-500 overflow-hidden"
          >
            <img
              v-if="ticket.user?.avatar"
              :src="ticket.user.avatar"
              class="w-full h-full object-cover"
              :alt="ticket.user.name"
            />
            <template v-else>
              {{
                ticket.user?.name?.charAt(0)?.toUpperCase() ||
                unknownUserLabel.charAt(0)
              }}
            </template>
          </div>
          <div
            v-if="ticket.unreadCount > 0"
            class="absolute -top-1 -right-1 min-w-[20px] h-[20px] px-1 bg-rose-500 text-white text-[9px] font-black rounded-full flex items-center justify-center shadow-lg border-2 border-white dark:border-gray-800 animate-in zoom-in duration-300"
          >
            {{ ticket.unreadCount }}
          </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 min-w-0">
          <!-- Header: Name and Time -->
          <div class="flex items-center justify-between gap-2 mb-1">
            <h4
              class="text-sm font-black text-gray-900 dark:text-white truncate tracking-tight"
            >
              {{ ticket.user?.name || unknownUserLabel }}
            </h4>
            <span
              class="text-[9px] font-black text-gray-400 uppercase tracking-widest flex-shrink-0"
            >
              {{ formatDate(ticket.createdAt) }}
            </span>
          </div>

          <!-- Last Message Preview -->
          <div class="flex items-center gap-2 mb-3">
            <p
              class="text-[11px] font-bold text-gray-500 dark:text-gray-400 truncate leading-relaxed"
            >
              <span
                v-if="ticket.lastMessage?.isAdmin"
                class="text-primary-500 font-black uppercase tracking-tighter mr-1"
                >{{ t("admin.support.list.you_prefix") }}</span
              >
              {{ getMessagePreview(ticket) }}
            </p>
          </div>

          <!-- Footer: Status, Tags, AI Badge -->
          <div class="flex items-center gap-2 flex-wrap">
            <span
              class="px-2 py-0.5 rounded-lg text-[8px] font-black uppercase tracking-widest border border-transparent shadow-sm"
              :class="getStatusStyle(ticket.status)"
            >
              {{ ticket.status }}
            </span>
            <span
              v-if="ticket.handledBy === 'ai'"
              class="px-2 py-0.5 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 text-[8px] font-black uppercase tracking-widest flex items-center gap-1 border border-purple-100 dark:border-purple-800/30"
            >
              <SparklesIcon class="w-3 h-3" />
              AI
            </span>
            <span
              v-for="tag in (ticket.tags || []).slice(0, 1)"
              :key="tag"
              class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700/50 text-gray-400 dark:text-gray-400 text-[8px] font-black uppercase tracking-widest"
            >
              #{{ tag }}
            </span>
            <span
              v-if="ticket.product"
              class="px-2 py-0.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[8px] font-black uppercase tracking-widest flex items-center gap-1 border border-emerald-100 dark:border-emerald-800/30 truncate max-w-[140px]"
            >
              <ShoppingBagIcon class="w-2.5 h-2.5 shrink-0" />
              {{ productLabel(ticket.product) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { ShoppingBagIcon, SparklesIcon } from "@heroicons/vue/24/outline";

const { t } = useI18n();

const unknownUserLabel = computed(() => t("admin.support.list.unknown_user"));

defineProps({
  tickets: {
    type: Array,
    required: true,
  },
  selectedTicketId: {
    type: [Number, String],
    default: null,
  },
});

defineEmits(["select"]);

const formatDate = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diff = now - date;
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));

  if (days === 0) {
    return date.toLocaleTimeString("uk-UA", {
      hour: "2-digit",
      minute: "2-digit",
    });
  } else if (days === 1) {
    return t("admin.support.list.yesterday");
  } else if (days < 7) {
    return date.toLocaleDateString("uk-UA", { weekday: "short" });
  }
  return date.toLocaleDateString("uk-UA", { day: "2-digit", month: "2-digit" });
};

const isImage = (fileType) => {
  return fileType?.startsWith("image/");
};

const isVideo = (fileType) => {
  return fileType?.startsWith("video/");
};

const productLabel = (product) =>
  typeof product.name === "object"
    ? product.name.uk || product.name.en
    : product.name;

const getMessagePreview = (ticket) => {
  if (!ticket.lastMessage) return t("admin.support.list.no_messages");

  if (ticket.lastMessage.filePath && !ticket.lastMessage.message) {
    if (isImage(ticket.lastMessage.fileType))
      return t("admin.support.list.photo");
    if (isVideo(ticket.lastMessage.fileType))
      return t("admin.support.list.video");
    return t("admin.support.list.file");
  }

  return ticket.lastMessage.message || t("admin.support.list.no_messages");
};

const getStatusStyle = (status) => {
  const styles = {
    new: "bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400",
    accepted:
      "bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400",
    done: "bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400",
    archived: "bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-500",
  };
  return styles[status] || styles.new;
};
</script>
