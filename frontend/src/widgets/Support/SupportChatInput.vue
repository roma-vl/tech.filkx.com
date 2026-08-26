<script setup lang="ts">
import { ref } from "vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

defineProps<{
  sending: boolean;
}>();

const emit = defineEmits<{
  (e: "send", message: string): void;
}>();

const draft = ref("");

const handleSend = () => {
  if (!draft.value.trim()) return;
  emit("send", draft.value);
  draft.value = "";
};
</script>

<template>
  <div
    class="flex items-end gap-2 p-3 border-t border-zinc-100 dark:border-zinc-800 bg-white dark:bg-zinc-900"
  >
    <textarea
      v-model="draft"
      rows="1"
      :placeholder="t('support.chat.inputPlaceholder')"
      class="flex-1 resize-none max-h-24 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-3.5 py-2.5 text-sm text-zinc-800 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046]"
      @keydown.enter.exact.prevent="handleSend"
    />
    <button
      type="button"
      :disabled="sending || !draft.trim()"
      class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-white bg-[#00a046] hover:bg-[#00b050] transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
      :aria-label="t('support.chat.send')"
      @click="handleSend"
    >
      <span
        class="material-symbols-outlined text-[19px]"
        :class="{ 'animate-spin': sending }"
        >{{ sending ? "progress_activity" : "send" }}</span
      >
    </button>
  </div>
</template>
