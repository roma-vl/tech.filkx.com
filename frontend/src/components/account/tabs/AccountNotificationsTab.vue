<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";
import { store } from "@/store.js";

const notifications = ref([]);
const isLoading = ref(true);
const error = ref(null);

const fetchNotifications = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const { data } = await api.get("/notifications");
    notifications.value = data.data?.data || data.data || [];
    console.log("Loaded notifications:", JSON.parse(JSON.stringify(notifications.value)));
  } catch (err) {
    console.error("Failed to load notifications:", err);
    error.value = "Не вдалося завантажити сповіщення";
  } finally {
    isLoading.value = false;
  }
};

const markAsRead = async (notification) => {
  if (notification.read_at) return;
  try {
    const { data } = await api.post(`/notifications/${notification.id}/read`);
    // update local state
    const index = notifications.value.findIndex(n => n.id === notification.id);
    if (index !== -1) {
      notifications.value[index] = data.data?.data || data.data;
    }
    // Update store counter if needed
    store.addToast("Сповіщення прочитано", "success");
  } catch (err) {
    console.error("Failed to mark notification as read:", err);
  }
};

const markAllRead = async () => {
  if (notifications.value.every(n => n.read_at)) return;
  try {
    await api.post("/notifications/mark-all-read");
    notifications.value = notifications.value.map(n => ({
      ...n,
      read_at: new Date().toISOString()
    }));
    store.addToast("Всі сповіщення позначено як прочитані", "success");
  } catch (err) {
    console.error("Failed to mark all notifications as read:", err);
  }
};

const getNotificationStyles = (type) => {
  const styles = {
    success: {
      bg: "bg-emerald-50 dark:bg-emerald-500/10 border-emerald-100 dark:border-emerald-500/20",
      text: "text-emerald-800 dark:text-emerald-400",
      icon: "check_circle",
      iconColor: "text-emerald-500"
    },
    error: {
      bg: "bg-rose-50 dark:bg-rose-500/10 border-rose-100 dark:border-rose-500/20",
      text: "text-rose-800 dark:text-rose-400",
      icon: "error",
      iconColor: "text-rose-500"
    },
    warning: {
      bg: "bg-amber-50 dark:bg-amber-500/10 border-amber-100 dark:border-amber-500/20",
      text: "text-amber-800 dark:text-amber-400",
      icon: "warning",
      iconColor: "text-amber-500"
    },
    info: {
      bg: "bg-blue-50 dark:bg-blue-500/10 border-blue-100 dark:border-blue-500/20",
      text: "text-blue-800 dark:text-blue-400",
      icon: "info",
      iconColor: "text-blue-500"
    },
    promo: {
      bg: "bg-purple-50 dark:bg-purple-500/10 border-purple-100 dark:border-purple-500/20",
      text: "text-purple-800 dark:text-purple-400",
      icon: "local_offer",
      iconColor: "text-purple-500"
    },
    news: {
      bg: "bg-indigo-50 dark:bg-indigo-500/10 border-indigo-100 dark:border-indigo-500/20",
      text: "text-indigo-800 dark:text-indigo-400",
      icon: "newspaper",
      iconColor: "text-indigo-500"
    },
    order: {
      bg: "bg-teal-50 dark:bg-teal-500/10 border-teal-100 dark:border-teal-500/20",
      text: "text-teal-800 dark:text-teal-400",
      icon: "shopping_bag",
      iconColor: "text-teal-500"
    }
  };

  return styles[type] || styles.info;
};

const formatDate = (dateStr) => {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  return d.toLocaleString("uk-UA", {
    day: "numeric",
    month: "long",
    hour: "2-digit",
    minute: "2-digit"
  });
};

onMounted(fetchNotifications);
</script>

<template>
  <div class="space-y-6 animate-fade font-sans">
    <!-- Header Controls -->
    <div class="flex justify-between items-center">
      <h2 class="text-lg font-extrabold text-zinc-800 dark:text-zinc-200">
        Ваші сповіщення
      </h2>
      <button
        v-if="notifications.length > 0 && notifications.some(n => !n.read_at)"
        class="text-xs font-black text-[#00a046] hover:text-[#00b050] dark:text-[#00b050] dark:hover:text-[#00c060] transition-colors uppercase tracking-widest flex items-center gap-1.5"
        @click="markAllRead"
      >
        <span class="material-symbols-outlined text-[16px]">done_all</span>
        Читати все
      </button>
    </div>

    <!-- Loading State -->
    <div
      v-if="isLoading"
      class="space-y-4"
    >
      <div
        v-for="n in 3"
        :key="n"
        class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-850 rounded-2xl p-5 animate-pulse flex gap-4"
      >
        <div class="w-10 h-10 rounded-full bg-zinc-200 dark:bg-zinc-800 shrink-0" />
        <div class="flex-grow space-y-2">
          <div class="h-3 bg-zinc-200 dark:bg-zinc-800 rounded w-1/4" />
          <div class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded w-3/4" />
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 p-5 rounded-2xl text-center"
    >
      {{ error }}
    </div>

    <!-- Notifications List -->
    <div
      v-else-if="notifications.length > 0"
      class="space-y-4"
    >
      <div
        v-for="item in notifications"
        :key="item.id"
        class="bg-white dark:bg-zinc-900 border rounded-2xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-start justify-between gap-4"
        :class="[
          item.read_at ? 'border-zinc-100 dark:border-zinc-850/80 opacity-75' : 'border-[#00a046]/30 dark:border-[#00a046]/40 shadow-[#00a046]/5'
        ]"
      >
        <div class="flex gap-4 items-start">
          <!-- Icon Bubble -->
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 border"
            :class="getNotificationStyles(item.type).bg"
          >
            <span
              class="material-symbols-outlined text-[20px]"
              :class="getNotificationStyles(item.type).iconColor"
            >
              {{ getNotificationStyles(item.type).icon }}
            </span>
          </div>

          <!-- Content -->
          <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
              <h3
                class="text-sm md:text-base leading-snug"
                :class="item.read_at ? 'font-medium text-zinc-500 dark:text-zinc-400' : 'font-black text-zinc-900 dark:text-white'"
              >
                {{ item.title }}
              </h3>
              <span
                v-if="!item.read_at"
                class="bg-[#00a046] text-white text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-wider scale-90"
              >
                Нове
              </span>
            </div>
            <p class="text-xs md:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">
              {{ item.content }}
            </p>
            <div class="flex items-center gap-3 pt-1 text-[10px] font-extrabold text-zinc-400 uppercase tracking-wider">
              <span>{{ formatDate(item.created_at) }}</span>
              <span
                v-if="item.read_at"
                class="text-zinc-350 dark:text-zinc-650"
              >• Прочитано</span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 self-end md:self-center shrink-0">
          <a
            v-if="item.link"
            :href="item.link"
            class="bg-zinc-100 dark:bg-zinc-800 hover:bg-[#00a046]/10 hover:text-[#00a046] text-zinc-600 dark:text-zinc-300 px-4 py-2 rounded-xl font-extrabold text-xs transition-all uppercase tracking-wider flex items-center gap-1.5"
            @click="markAsRead(item)"
          >
            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
            Детальніше
          </a>
          <button
            v-if="!item.read_at"
            class="text-[#00a046] hover:bg-[#00a046]/10 p-2 rounded-full transition-colors flex items-center justify-center"
            title="Позначити як прочитане"
            @click="markAsRead(item)"
          >
            <span class="material-symbols-outlined text-[18px]">done</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-else
      class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-100 dark:border-zinc-800 p-12 text-center shadow-sm"
    >
      <div
        class="w-16 h-16 bg-[#00a046]/10 border border-[#00a046]/20 text-[#00a046] rounded-full flex items-center justify-center mx-auto mb-4"
      >
        <span class="material-symbols-outlined text-[32px]">notifications</span>
      </div>
      <h3 class="font-extrabold text-lg text-zinc-850 dark:text-zinc-150">
        У вас немає нових сповіщень
      </h3>
      <p
        class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 max-w-sm mx-auto mt-2"
      >
        Тут будуть з'являтися системні повідомлення, спеціальні пропозиції та інформація про ваші замовлення.
      </p>
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
