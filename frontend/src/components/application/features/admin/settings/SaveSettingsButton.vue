<template>
  <div class="fixed bottom-8 right-8 z-30">
    <button
      :disabled="saving"
      class="flex items-center gap-3 px-8 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition-all shadow-2xl shadow-indigo-500/40 active:scale-95 disabled:opacity-50"
      @click="$emit('save')"
    >
      <ArrowPathIcon
        v-if="saving"
        class="w-5 h-5 animate-spin"
      />
      <CloudArrowUpIcon
        v-else
        class="w-5 h-5"
      />
      {{ saving ? $t("admin.settings.saving") : $t("admin.settings.save_all") }}
    </button>
  </div>
</template>

<script setup>
import { ArrowPathIcon, CloudArrowUpIcon } from "@heroicons/vue/24/outline";

defineProps({
  saving: Boolean,
});

defineEmits(["save"]);
</script>
