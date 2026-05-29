<template>
  <div
    class="relative min-w-[120px] min-h-[100px] p-2.5 sm:p-3 transition-all duration-300 rounded-3xl border border-white/40 dark:border-white/5"
    :class="{
      'opacity-50 grayscale-[50%]': !day.isCurrentMonth,
      'bg-primary-50/50 dark:bg-primary-900/20 shadow-inner border-primary-200/50 dark:border-primary-800/30': isToday,
      'bg-white/20 dark:bg-white/5 backdrop-blur-sm': isPast && !isToday,
      'cursor-pointer hover:bg-white/60 dark:hover:bg-gray-800/40 hover:shadow-md hover:border-white/80 dark:hover:border-white/20 bg-white/30 dark:bg-gray-800/20 backdrop-blur-md': !isPast && !isToday,
      'cursor-pointer hover:bg-primary-100/50 dark:hover:bg-primary-900/30 hover:shadow-md hover:border-primary-300/50 dark:hover:border-primary-700/50': isToday,
    }"
    @click="(!isPast || isToday) && $emit('click', day)"
  >
    <div class="flex justify-between items-start mb-2 overflow-visible">
      <div
        class="text-xs sm:text-sm font-black w-8 h-8 flex items-center justify-center rounded-2xl transition-all shadow-sm"
        :class="{
          'bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-primary-500/30 scale-110': isToday,
          'text-gray-400 dark:text-gray-500 bg-white/40 dark:bg-black/10 border border-white/50 dark:border-white/5': isPast && !isToday,
          'text-gray-700 dark:text-gray-200 bg-white/60 dark:bg-white/5 border border-white/60 dark:border-white/10': !isPast && !isToday,
        }"
      >
        {{ day.day }}
      </div>
    </div>

    <div class="space-y-1.5 custom-scrollbar overflow-y-auto max-h-[80px] sm:max-h-none pr-1">
      <div
        v-for="event in events"
        :key="event.id"
        class="text-[10px] sm:text-xs cursor-pointer truncate shadow-sm flex items-center gap-1.5 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm p-1.5 sm:p-2 rounded-xl transition-all duration-300 border border-white/50 dark:border-white/10 hover:-translate-y-0.5 hover:shadow-md group"
        :class="[
          {
            'border-l-4 border-l-red-500': event.platform === 'youtube',
            'border-l-4 border-l-[#9146FF]': event.platform === 'twitch',
            'opacity-90 border-dashed': event.status === 'scheduled',
          },
        ]"
        @click.stop="$emit('eventClick', event)"
      >
        <span
          v-if="event.status === 'live'"
          class="flex h-1.5 w-1.5 relative shrink-0"
        >
          <span
            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"
          />
          <span
            class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-500"
          />
        </span>
        <span
          v-else
          class="h-1.5 w-1.5 rounded-full bg-gray-400 shrink-0"
        />
        <span class="font-bold opacity-80 text-gray-500 dark:text-gray-400 tracking-tighter">{{ event.time }}</span>
        <span class="font-bold text-gray-700 dark:text-gray-200 truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ event.title }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  day: {
    type: Object,
    required: true,
  },
  events: {
    type: Array,
    required: true,
  },
});

defineEmits(["click", "eventClick"]);

const isToday = computed(() => {
  const now = new Date();
  const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}`;
  return props.day.date === today;
});

const isPast = computed(() => {
  const now = new Date();
  const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}`;
  return props.day.date < today;
});
</script>
