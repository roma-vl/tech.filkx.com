<template>
  <div
    class="h-full bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm flex flex-col h-[850px] overflow-hidden"
  >
    <!-- User Profile Header -->
    <div
      class="p-6 pb-5 text-center border-b border-gray-100 dark:border-gray-700/50 bg-gradient-to-b from-gray-50/30 to-white dark:from-gray-900/20 dark:to-gray-800"
    >
      <div class="relative inline-block mb-3">
        <div
          class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 p-0.5 shadow-lg shadow-primary-500/20"
        >
          <div
            class="w-full h-full rounded-[0.9rem] bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden"
          >
            <img
              v-if="user?.avatar"
              :src="user.avatar"
              class="w-full h-full object-cover"
            >
            <span
              v-else
              class="text-2xl font-black text-primary-600 uppercase"
            >{{ user?.name?.charAt(0) || "U" }}</span>
          </div>
        </div>
        <div
          class="absolute -bottom-0.5 -right-0.5 w-5 h-5 rounded-lg bg-green-500 border-2 border-white dark:border-gray-800"
        />
      </div>

      <h3
        class="text-lg font-bold text-gray-900 dark:text-white mb-0.5 tracking-tight"
      >
        {{ user?.name || "User" }}
      </h3>
      <p
        class="text-[10px] font-bold text-gray-400 uppercase tracking-widest break-all px-2"
      >
        {{ user?.email }}
      </p>
    </div>

    <!-- User Details Body -->
    <div
      class="flex-1 p-6 space-y-6 overflow-y-auto custom-scrollbar bg-gray-50/20 dark:bg-gray-900/10"
    >
      <!-- Plan Info -->
      <section>
        <h4
          class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-2"
        >
          <StarIcon class="w-3 h-3 text-amber-500" />
          {{ t("admin.support.user.subscription") }}
        </h4>
        <div
          class="p-3.5 rounded-xl bg-primary-50 dark:bg-primary-900/10 border border-primary-100 dark:border-primary-900/20"
        >
          <div class="flex items-center justify-between">
            <span
              class="text-xs font-bold text-primary-700 dark:text-primary-400"
            >{{ user?.subscription?.plan_name || "Free Plan" }}</span>
            <span
              class="px-2 py-0.5 rounded-md bg-green-100 text-[#15803d] text-[8px] font-black uppercase"
            >{{ user?.subscription?.status || "Active" }}</span>
          </div>
        </div>
      </section>

      <!-- Account Info -->
      <section class="space-y-3">
        <h4
          class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2"
        >
          <InfoIcon class="w-3 h-3" />
          {{ t("admin.support.user.details") }}
        </h4>

        <div class="grid grid-cols-1 gap-1">
          <div
            class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/50"
          >
            <span
              class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter"
            >{{ t("admin.support.user.role") }}</span>
            <span
              class="text-[10px] font-black text-gray-700 dark:text-gray-300 uppercase"
            >{{ user?.role || "User" }}</span>
          </div>
          <div
            class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-700/50"
          >
            <span
              class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter"
            >{{ t("admin.support.user.joined") }}</span>
            <span
              class="text-[10px] font-black text-gray-700 dark:text-gray-300"
            >{{ formatDate(user?.createdAt) }}</span>
          </div>
        </div>
      </section>

      <!-- Ticket History -->
      <section class="space-y-4">
        <h4
          class="text-[10px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2"
        >
          <HistoryIcon class="w-3 h-3" />
          {{ t("admin.support.user.history") }}
        </h4>

        <div
          v-if="historyLoading"
          class="space-y-3"
        >
          <div
            v-for="i in 3"
            :key="i"
            class="h-12 bg-gray-50 dark:bg-gray-900/40 rounded-xl animate-pulse"
          />
        </div>
        <div
          v-else-if="userTickets.length > 0"
          class="space-y-2"
        >
          <AppButton
            v-for="hTicket in userTickets"
            :key="hTicket.id"
            variant="white"
            class="!w-full !p-3 !rounded-xl !border !transition-all !cursor-pointer group/item !block !text-left !shadow-none"
            :class="
              hTicket.id === ticket?.id
                ? '!bg-primary-50 dark:!bg-primary-900/10 !border-primary-200 dark:!border-primary-800'
                : '!bg-white dark:!bg-gray-800 !border-gray-100 dark:!border-gray-700 hover:!border-primary-300 dark:hover:!border-primary-700'
            "
            @click="$emit('select-ticket', hTicket.id)"
          >
            <div class="flex items-center justify-between mb-1">
              <span class="text-[9px] font-black text-gray-400">#{{ hTicket.id }}</span>
              <span
                :class="[
                  'text-[8px] font-black uppercase px-1.5 py-0.5 rounded-md',
                  hTicket.status === 'done'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-blue-100 text-blue-700',
                ]"
              >
                {{ hTicket.status }}
              </span>
            </div>
            <p
              class="text-[10px] font-bold text-gray-700 dark:text-gray-300 truncate group-hover/item:text-primary-600 transition-colors"
            >
              {{ hTicket.subject }}
            </p>
          </AppButton>
        </div>
        <div
          v-else
          class="text-center py-6"
        >
          <p
            class="text-[10px] font-black uppercase text-gray-300 tracking-widest"
          >
            {{ t("admin.support.user.no_history") }}
          </p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import axios from "@/services/api";
import {
  ClockIcon as HistoryIcon,
  InformationCircleIcon as InfoIcon,
  StarIcon,
} from "@heroicons/vue/24/outline";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  user: Object,
  ticket: Object,
});

const emit = defineEmits(["select-ticket"]);

const userTickets = ref([]);
const historyLoading = ref(false);

const fetchUserHistory = async () => {
  if (!props.user?.id) return;
  historyLoading.value = true;
  try {
    const response = await axios.get(
      `/admin/support/users/${props.user.id}/tickets`,
    );
    userTickets.value = response.data.data.data || response.data.data;
  } catch (error) {
    console.error("Failed to fetch user history", error);
  } finally {
    historyLoading.value = false;
  }
};

watch(() => props.user?.id, fetchUserHistory, { immediate: true });

const formatDate = (date) => {
  if (!date) return "Unknown";
  return new Date(date).toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
}
</style>
