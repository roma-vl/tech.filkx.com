<template>
  <div class="space-y-4">
    <div
      v-for="campaign in campaigns"
      :key="campaign.id"
      class="bg-white dark:bg-gray-800 p-6 rounded-3xl border transition-all duration-300 cursor-pointer group"
      :class="[
        selectedId === campaign.id
          ? 'border-primary-500 ring-4 ring-primary-500/10 shadow-xl shadow-primary-500/5'
          : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-lg',
      ]"
      @click="$emit('select', campaign)"
    >
      <div class="flex items-start justify-between">
        <div class="flex items-center gap-5">
          <div
            class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600 dark:text-primary-400 group-hover:scale-110 transition-transform duration-300"
          >
            <EnvelopeIcon class="w-7 h-7" />
          </div>
          <div>
            <h3
              class="font-black text-xl text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors tracking-tight"
            >
              {{ campaign.name }}
            </h3>
            <p
              class="text-[13px] font-bold text-gray-400 dark:text-gray-500 mt-1 leading-relaxed"
            >
              {{ campaign.description }}
            </p>
          </div>
        </div>
        <div
          v-if="selectedId === campaign.id"
          class="text-primary-500"
        >
          <CheckCircleIcon class="w-8 h-8" />
        </div>
      </div>

      <div class="mt-6 flex items-center gap-3">
        <div
          class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700"
        >
          <span
            class="text-[10px] font-black uppercase tracking-widest text-gray-400"
          >
            {{ t("admin.emails.suggested_audience") }}
          </span>
          <span
            class="text-[10px] font-black uppercase tracking-widest text-primary-600 dark:text-primary-400"
          >
            {{ formatAudience(campaign.suggestedAudience) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { EnvelopeIcon, CheckCircleIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

defineProps({
  campaigns: {
    type: Array,
    required: true,
  },
  selectedId: {
    type: [Number, String],
    default: null,
  },
});

defineEmits(["select"]);

const formatAudience = (slug) => {
  const map = {
    all: t("admin.emails.audience.all"),
    trial_expired: t("admin.emails.audience.trial_expired"),
    new_users: t("admin.emails.audience.new_users"),
    active_subscribers: t("admin.emails.audience.active_subscribers"),
  };
  return map[slug] || slug;
};
</script>
