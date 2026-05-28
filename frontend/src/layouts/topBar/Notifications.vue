<script setup>
import { ref, onMounted } from "vue";
import Dropdown from "@/components/ui/Dropdown.vue";
import BellIcon from "@/components/Icon/BellIcon.vue";
// import { useNotifications } from "@/composables/useNotifications";

const show = ref(false);
const expandedIds = ref(new Set());

const notifications = ref([]);
const unreadCount = ref(0);
const fetchNotifications = () => Promise.resolve();
const markAllAsRead = () => Promise.resolve();
const markAsRead = () => Promise.resolve();
const initEcho = () => {};

const toggleDropdown = () => (show.value = !show.value);

onMounted(async () => {
  await fetchNotifications();
  initEcho();
});

const markAllRead = async () => {
  await markAllAsRead();
};

const toggleExpand = async (notification) => {
  const id = notification.id;
  if (expandedIds.value.has(id)) {
    expandedIds.value.delete(id);
  } else {
    expandedIds.value.add(id);
    if (!notification.readAt) {
      await markAsRead(id);
    }
  }
};

const isExpanded = (id) => expandedIds.value.has(id);

const truncateText = (text, length = 100) => {
  if (!text) return "";
  if (text.length <= length) return text;
  return text.substring(0, length) + "...";
};
</script>

<template>
  <Dropdown
    align="right"
    width="80"
    :dropdown-classes="[
      'bg-white dark:bg-gray-800 shadow-lg rounded-md ring-1 ring-black ring-opacity-5 dark:ring-gray-700 w-80 sm:w-[20rem]',
    ]"
  >
    <template #trigger>
      <button
        type="button"
        class="relative rounded-md p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors"
        :aria-label="
          unreadCount > 0
            ? $t('notifications.title_with_count', { count: unreadCount })
            : $t('notifications.title')
        "
        @click="toggleDropdown"
      >
        <BellIcon class="h-6 w-6" />
       
      </button>
    </template>

    <template #content>
      <div class="max-h-[32rem] flex flex-col w-full sm:w-[20rem]">
        <div
          class="border-b border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50"
        >
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
            {{ $t("notifications.title") }}
          </h3>
          <button
            v-if="unreadCount > 0"
            class="text-xs font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors"
            @click="markAllRead"
          >
            {{ $t("notifications.mark_all_read") }}
          </button>
        </div>

        <div
          v-if="notifications.length"
          class="flex-1 overflow-y-auto"
        >
          <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            <li
              v-for="notification in notifications"
              :key="notification.id"
              :class="[
                'p-4 transition-colors cursor-pointer',
                !notification.readAt
                  ? 'bg-blue-50/50 dark:bg-blue-900/10 hover:bg-blue-100/50 dark:hover:bg-blue-900/20'
                  : 'bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-750',
              ]"
              @click="!notification.readAt && markAsRead(notification.id)"
            >
              <div class="flex items-start gap-3">
                <div class="flex-shrink-0 mt-1">
                  <span
                    v-if="notification.data.type === 'error'"
                    class="text-red-500"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </span>
                  <span
                    v-else-if="notification.data.type === 'warning'"
                    class="text-yellow-500"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </span>
                  <span
                    v-else-if="notification.data.type === 'success'"
                    class="text-green-500"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </span>
                  <span
                    v-else
                    class="text-indigo-500"
                  >
                    <BellIcon class="h-5 w-5" />
                  </span>
                </div>

                <div class="flex-1 min-w-0">
                  <div class="flex justify-between items-start">
                    <p
                      class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                    >
                      {{
                        notification.data.title ||
                          $t("notifications.new_notification")
                      }}
                    </p>
                    <time
                      :datetime="notification.createdAt"
                      class="text-xs text-gray-400 whitespace-nowrap ml-2"
                    >
                      {{
                        new Date(notification.createdAt).toLocaleTimeString(
                          [],
                          { hour: "2-digit", minute: "2-digit" },
                        )
                      }}
                    </time>
                  </div>

                  <div
                    class="mt-1 text-sm text-gray-600 dark:text-gray-300 break-words"
                  >
                    <p v-if="!isExpanded(notification.id)">
                      {{
                        truncateText(
                          notification.data.text || notification.data.message,
                          80,
                        )
                      }}
                    </p>
                    <div
                      v-else
                      class="whitespace-pre-wrap"
                    >
                      {{ notification.data.text || notification.data.message }}
                    </div>
                  </div>

                  <div class="mt-2 flex items-center justify-between">
                    <button
                      v-if="
                        notification.data.text?.length > 80 ||
                          notification.data.message?.length > 80
                      "
                      class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 focus:outline-none"
                      @click.stop="toggleExpand(notification)"
                    >
                      {{
                        isExpanded(notification.id)
                          ? $t("notifications.read_less")
                          : $t("notifications.read_more")
                      }}
                    </button>

                    <a
                      v-if="notification.data.action_url"
                      :href="notification.data.action_url"
                      target="_blank"
                      class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline ml-auto"
                      @click.stop="
                        !notification.readAt && markAsRead(notification.id)
                      "
                    >
                      {{ $t("notifications.open_verify_link") }} &rarr;
                    </a>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <div
          v-else
          class="flex flex-col items-center justify-center py-12 px-4 text-center"
        >
          <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-3 mb-3">
            <BellIcon class="h-6 w-6 text-gray-400" />
          </div>
          <p class="text-sm font-medium text-gray-900 dark:text-gray-200">
            {{ $t("notifications.empty_title") || $t("notifications.title") }}
          </p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ $t("notifications.empty") }}
          </p>
        </div>
      </div>
    </template>
  </Dropdown>
</template>
