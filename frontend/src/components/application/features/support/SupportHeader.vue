<template>
  <div
    class="relative overflow-hidden shrink-0 border-b border-white/10 dark:border-white/5 bg-gradient-to-br from-primary-500 to-primary-700 dark:from-primary-900/80 dark:to-primary-800/80"
  >
    <div
      class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-white/10 blur-3xl pointer-events-none"
    />
    <div
      class="absolute -bottom-24 -left-24 h-48 w-48 rounded-full bg-black/10 blur-3xl pointer-events-none"
    />

    <div class="relative p-6 sm:p-8 text-white z-10">
      <div class="flex items-center justify-between mb-6">
        <div
          v-if="currentView !== 'home'"
          class="p-2 -ml-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/10 shadow-sm cursor-pointer transition-all hover:-translate-x-0.5"
          @click="$emit('goHome')"
        >
          <ArrowLeftIcon class="w-5 h-5" />
        </div>
        <div
          v-else
          class="w-10 h-10 rounded-xl bg-white/20 border border-white/20 shadow-inner flex items-center justify-center overflow-hidden"
        >
          <img
            v-if="user?.avatar_url"
            :src="user.avatar_url"
            class="w-full h-full object-cover"
          >
          <UserIcon
            v-else
            class="w-5 h-5 opacity-80"
          />
        </div>
        <button
          class="p-2 -mr-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/10 shadow-sm cursor-pointer transition-all hover:scale-105 active:scale-95"
          @click="$emit('close')"
        >
          <CrossIcon class="w-5 h-5" />
        </button>
      </div>

      <div v-if="currentView === 'home'">
        <h3 class="text-2xl font-black tracking-tight leading-tight mb-1">
          {{ t("support.hi") }}
        </h3>
        <p class="text-sm font-medium opacity-80 leading-relaxed max-w-[90%]">
          {{ t("support.description") }}
        </p>
      </div>
      <div
        v-else-if="currentView === 'chat'"
        class="flex items-center gap-4"
      >
        <div
          class="w-12 h-12 rounded-full overflow-hidden border-2 border-white/20 bg-white/10 shadow-inner flex items-center justify-center shrink-0"
        >
          <SparklesIcon
            v-if="!activeTicket || activeTicket.handled_by === 'ai'"
            class="w-6 h-6 text-white animate-pulse"
          />
          <img
            v-else
            src="https://ui-avatars.com/api/?name=Support&background=000&color=fff"
            class="w-full h-full object-cover"
          >
        </div>
        <div>
          <h3 class="font-bold text-lg leading-tight dropshadow-sm">
            {{
              !activeTicket || activeTicket.handled_by === "ai"
                ? t("support.ai_assistant")
                : t("support.human_support")
            }}
          </h3>
          <div
            class="flex items-center gap-1.5 opacity-90 text-[10px] uppercase font-black tracking-widest mt-0.5"
          >
            <span
              class="w-1.5 h-1.5 rounded-full bg-green-400 shadow-[0_0_8px_rgba(74,222,128,0.8)] animate-pulse"
            />
            {{
              !activeTicket || activeTicket.handled_by === "ai"
                ? t("support.bot_online")
                : t("support.operator_online")
            }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import {
  ArrowLeftIcon,
  SparklesIcon,
  UserIcon,
  XMarkIcon as CrossIcon,
} from "@heroicons/vue/24/outline";

const { t } = useI18n();

defineProps({
  currentView: String,
  activeTicket: Object,
  user: Object,
});

defineEmits(["goHome", "close"]);
</script>

<style scoped>
.grad-bg {
  background-size: 200% 200%;
  animation: grad-animation 8s ease infinite;
}

@keyframes grad-animation {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
</style>
