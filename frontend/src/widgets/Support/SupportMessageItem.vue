<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import type { SupportMessage } from "@/entities/support/types";

const { t, locale } = useI18n();

const props = defineProps<{
  message: SupportMessage;
}>();

const formattedTime = computed(() => {
  if (!props.message.createdAt) return "";
  return new Date(props.message.createdAt).toLocaleTimeString(
    locale.value === "uk" ? "uk-UA" : "en-US",
    { hour: "2-digit", minute: "2-digit" },
  );
});
</script>

<template>
  <div
    :class="['flex w-full', message.isAdmin ? 'justify-start' : 'justify-end']"
  >
    <div class="flex flex-col max-w-[85%] gap-1">
      <div
        :class="[
          'rounded-2xl px-4 py-2.5 text-sm leading-relaxed whitespace-pre-wrap break-words',
          message.isAdmin
            ? 'bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 rounded-bl-sm'
            : 'bg-[#00a046] text-white rounded-br-sm',
        ]"
      >
        {{ message.message }}
      </div>
      <div
        :class="[
          'flex items-center gap-1.5 px-1 text-[10px] font-semibold uppercase tracking-wide text-zinc-400 dark:text-zinc-500',
          message.isAdmin ? 'justify-start' : 'justify-end',
        ]"
      >
        <span>{{
          message.isAdmin ? t("support.chat.support") : t("support.chat.you")
        }}</span>
        <span>&middot;</span>
        <span>{{ formattedTime }}</span>
      </div>
    </div>
  </div>
</template>
