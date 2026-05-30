<template>
  <div
    ref="chatContainer"
    class="flex-1 overflow-y-auto p-6 space-y-6 bg-gray-50/50 dark:bg-gray-900/10 relative"
  >
    <!-- Load More Trigger -->
    <div
      ref="loadMoreTrigger"
      class="h-1 w-full"
    />

    <div
      v-if="isLoadingMore"
      class="flex justify-center py-2"
    >
      <div
        class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-500"
      />
    </div>
    <div
      v-for="msg in messages"
      :key="msg.id"
      :ref="(el) => setRef(el, msg.id)"
      :data-id="msg.id"
      :class="[
        'flex w-full message-item',
        msg.type === 'system'
          ? 'justify-center py-4'
          : msg.isInternal
            ? 'justify-center py-2'
            : msg.isAdmin
              ? 'justify-end'
              : 'justify-start',
      ]"
    >
      <!-- System Message -->
      <div
        v-if="msg.type === 'system'"
        class="px-5 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-black uppercase tracking-widest text-gray-500 flex items-center gap-2"
      >
        <InfoIcon class="w-3 h-3" />
        {{ msg.message }}
      </div>

      <!-- Internal Note -->
      <div
        v-if="msg.isInternal"
        class="max-w-[90%] bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-900/30 rounded-2xl p-4 shadow-sm relative group flex items-start gap-4"
      >
        <div class="p-2 bg-amber-100 dark:bg-amber-900/40 rounded-xl">
          <EyeSlashIcon class="w-4 h-4 text-amber-600 dark:text-amber-400" />
        </div>
        <div class="flex-1">
          <p
            class="text-[10px] font-black uppercase text-amber-600 dark:text-amber-400 tracking-widest mb-1"
          >
            {{ t("admin.support.chat.internal_note") }}
          </p>
          <p
            class="text-sm font-medium text-amber-900 dark:text-amber-100 leading-relaxed"
          >
            {{ msg.message }}
          </p>
          <div class="flex items-center justify-end gap-2 mt-2 opacity-50">
            <span
              class="text-[8px] font-black uppercase text-amber-700/60 dark:text-amber-300/60"
            >{{ formatTime(msg.createdAt) }}</span>
          </div>
        </div>
      </div>

      <!-- Regular Message -->
      <div
        v-else-if="msg.type !== 'system'"
        :class="[
          'max-w-[85%] rounded-2xl p-4 shadow-sm transition-all relative group',
          msg.isAdmin
            ? 'bg-primary-600 text-white rounded-tr-none ml-auto'
            : !msg.readAt
              ? 'bg-white dark:bg-gray-800 rounded-tl-none border-2 border-red-500/50 dark:border-red-500/50 shadow-red-500/10 shadow-lg'
              : 'bg-white dark:bg-gray-800 rounded-tl-none border border-gray-100 dark:border-gray-700',
        ]"
      >
        <!-- Unread Indicator Badge -->
        <span
          v-if="!msg.isAdmin && !msg.readAt"
          class="absolute -top-2 -left-2 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center text-white shadow-lg ring-2 ring-white dark:ring-gray-900 animate-pulse"
        >
          <span class="text-[8px] font-black">!</span>
        </span>
        <div
          v-if="!msg.isAdmin"
          class="flex items-center gap-2 mb-1.5"
        >
          <div
            class="w-5 h-5 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden shadow-sm border border-gray-200 dark:border-gray-600"
          >
            <img
              v-if="msg.userAvatar"
              :src="msg.userAvatar"
              class="w-full h-full object-cover"
            >
            <UserIcon
              v-else
              class="w-3 h-3 text-gray-400"
            />
          </div>
          <p
            class="text-[10px] font-black opacity-40 uppercase tracking-widest"
          >
            {{ userName }}
          </p>
        </div>
        <p
          v-if="msg.message"
          class="text-sm leading-relaxed font-medium"
        >
          {{ msg.message }}
        </p>
        <div
          v-if="msg.fileUrl"
          class="mt-3"
        >
          <img
            v-if="isImage(msg.fileType)"
            :src="msg.fileUrl"
            class="rounded-xl max-w-full cursor-pointer hover:scale-105 transition-transform"
            @click="openFile(msg.fileUrl)"
          >
          <video
            v-else-if="isVideo(msg.fileType)"
            :src="msg.fileUrl"
            controls
            preload="metadata"
            class="rounded-xl max-w-full"
          />
          <a
            v-else
            :href="msg.fileUrl"
            target="_blank"
            :class="[
              'flex items-center gap-3 p-3 rounded-xl border transition-all group/file',
              msg.isAdmin
                ? 'bg-primary-700/20 border-primary-700/20 hover:bg-primary-700/30'
                : 'bg-black/5 dark:bg-white/5 border-black/5 dark:border-white/5 hover:bg-black/10 dark:hover:bg-white/10',
            ]"
          >
            <div
              :class="[
                'p-2 rounded-lg shadow-sm',
                msg.isAdmin ? 'bg-primary-500' : 'bg-white dark:bg-gray-800',
              ]"
            >
              <PaperClipIcon
                :class="[
                  'w-4 h-4',
                  msg.isAdmin ? 'text-white' : 'text-primary-500',
                ]"
              />
            </div>
            <div class="flex-1 min-w-0">
              <p
                :class="[
                  'text-[10px] font-bold truncate',
                  msg.isAdmin ? 'opacity-90' : 'opacity-80',
                ]"
              >
                {{ msg.fileName || "Attached File" }}
              </p>
              <p
                :class="[
                  'text-[8px] font-black uppercase tracking-tighter',
                  msg.isAdmin ? 'opacity-60' : 'opacity-40',
                ]"
              >
                {{ msg.fileType }}
              </p>
            </div>
          </a>
        </div>
        <div class="flex items-center justify-end gap-2 mt-2 opacity-50">
          <span class="text-[9px] font-bold uppercase">{{
            formatTime(msg.createdAt)
          }}</span>
        </div>
      </div>
      <div
        v-if="msg.isAi && msg.type !== 'system'"
        class="flex flex-col ml-2 self-end pb-1"
      >
        <div
          class="w-8 h-8 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center mb-1"
        >
          <SparklesIcon class="w-5 h-5 text-primary-600" />
        </div>
        <span
          class="text-[8px] font-black uppercase text-primary-500 text-center tracking-tighter"
        >AI</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import {
  EyeSlashIcon,
  InformationCircleIcon as InfoIcon,
  PaperClipIcon,
  SparklesIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import debounce from "lodash/debounce";

const { t } = useI18n();

const props = defineProps({
  messages: {
    type: Array,
    required: true,
  },
  userName: {
    type: String,
    default: "User",
  },
  hasMore: {
    type: Boolean,
    default: false,
  },
  isLoadingMore: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["loadMore", "markRead"]);

const chatContainer = ref(null);
const loadMoreTrigger = ref(null);
const messageRefs = ref(new Map());

// Scroll state management
let previousScrollHeight = 0;
let previousScrollTop = 0;
let isPrepending = false;

const scrollToBottom = () => {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
  });
};

const preserveScrollPosition = () => {
  nextTick(() => {
    if (chatContainer.value) {
      const newScrollHeight = chatContainer.value.scrollHeight;
      const heightDifference = newScrollHeight - previousScrollHeight;
      chatContainer.value.scrollTop = previousScrollTop + heightDifference;
    }
  });
};

// Intersection Observer for Infinite Scroll
let scrollObserver = null;

const setupScrollObserver = () => {
  if (scrollObserver) scrollObserver.disconnect();

  scrollObserver = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && props.hasMore && !props.isLoadingMore) {
        // Save current scroll state before loading more
        if (chatContainer.value) {
          previousScrollHeight = chatContainer.value.scrollHeight;
          previousScrollTop = chatContainer.value.scrollTop;
          isPrepending = true;
        }
        emit("loadMore");
      }
    },
    { root: chatContainer.value, threshold: 0.1 },
  );

  if (loadMoreTrigger.value) {
    scrollObserver.observe(loadMoreTrigger.value);
  }
};

// Intersection Observer for Read Tracking
let readObserver = null;
const pendingReadIds = new Set();

const flushReadIds = debounce(() => {
  if (pendingReadIds.size > 0) {
    emit("markRead", Array.from(pendingReadIds));
    pendingReadIds.clear();
  }
}, 1000);

const setupReadObserver = () => {
  if (readObserver) readObserver.disconnect();

  readObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const msgId = parseInt(entry.target.dataset.id);
          if (msgId) {
            pendingReadIds.add(msgId);
            flushReadIds();
            // Stop observing once marked
            readObserver.unobserve(entry.target);
          }
        }
      });
    },
    { root: chatContainer.value, threshold: 0.5 },
  );
};

const observeUnreadMessages = () => {
  if (!readObserver) return;

  // Observe all unread messages that are NOT from admin
  // We need to use nextTick to ensure DOM is updated
  nextTick(() => {
    props.messages.forEach((msg) => {
      if (!msg.isAdmin && !msg.readAt) {
        const el = messageRefs.value.get(msg.id);
        if (el) readObserver.observe(el);
      }
    });
  });
};

// Watchers
watch(
  () => props.messages,
  (newVal, oldVal) => {
    if (isPrepending) {
      preserveScrollPosition();
      isPrepending = false;
    } else {
      // If it's a new message (length increased and last message is different)
      // or initial load
      const isNewMessage =
        newVal.length > oldVal.length &&
        (oldVal.length === 0 ||
          newVal[newVal.length - 1].id !== oldVal[oldVal.length - 1].id);

      if (isNewMessage) {
        scrollToBottom();
      }
    }

    observeUnreadMessages();
  },
  { deep: true },
);

onMounted(() => {
  setupScrollObserver();
  setupReadObserver();
  observeUnreadMessages();
});

onUnmounted(() => {
  if (scrollObserver) scrollObserver.disconnect();
  if (readObserver) readObserver.disconnect();
  flushReadIds.cancel();
});

const formatTime = (date) => {
  if (!date) return "";
  return new Date(date).toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
  });
};

const isImage = (type) => type?.startsWith("image/");
const isVideo = (type) => type?.startsWith("video/");
const openFile = (url) => window.open(url, "_blank");
const setRef = (el, id) => {
  if (el) messageRefs.value.set(id, el);
  else messageRefs.value.delete(id);
};

defineExpose({ scrollToBottom });
</script>
