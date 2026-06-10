<template>
  <div
    v-if="meta.total > meta.per_page && pages.length"
    class="flex flex-col md:flex-row items-center justify-between gap-4 py-3"
  >
    <span class="text-sm text-gray-500 dark:text-gray-400">
      {{
        t("pagination.total", { total: meta.total }) ||
          `Всього записів: ${meta.total}`
      }}
    </span>

    <div class="flex gap-1">
      <AppButton
        variant="secondary"
        size="sm"
        :disabled="meta.current_page === 1 || disabled"
        @click="go(meta.current_page - 1)"
      >
        <ArrowLeftIcon class="w-4 h-4 mr-1" />
        <span class="hidden sm:inline">{{
          t("pagination.prev") || "Попередня"
        }}</span>
      </AppButton>
      <AppButton
        v-for="page in pages"
        :key="page"
        :variant="page === meta.current_page ? 'primary' : 'text'"
        size="sm"
        class="min-w-[32px]"
        :disabled="disabled"
        @click="go(page)"
      >
        {{ page }}
      </AppButton>
      <AppButton
        variant="secondary"
        size="sm"
        :disabled="meta.current_page === meta.last_page || disabled"
        @click="go(meta.current_page + 1)"
      >
        <span class="hidden sm:inline">{{
          t("pagination.next") || "Наступна"
        }}</span>
        <ArrowRightIcon class="w-4 h-4 ml-1" />
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppButton from "./AppButton.vue";
import ArrowLeftIcon from "@/components/Icon/ArrowLeftIcon.vue";
import ArrowRightIcon from "@/components/Icon/ArrowRightIcon.vue";

const { t } = useI18n();

const props = defineProps({
  pagination: {
    type: Object,
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["page-change"]);

const meta = computed(() => {
  if (!props.pagination) {
    return {
      current_page: 1,
      last_page: 1,
      per_page: 0,
      total: 0,
    };
  }

  const p = props.pagination.meta ?? props.pagination;

  return {
    current_page: p.current_page ?? p.currentPage ?? p.current ?? 1,
    last_page: p.last_page ?? p.lastPage ?? 1,
    per_page: p.per_page ?? p.perPage ?? 10,
    total: p.total ?? 0,
  };
});

const pages = computed(() => {
  if (!meta.value.last_page || meta.value.last_page <= 1) {
    return [];
  }
  return Array.from({ length: meta.value.last_page }, (_, i) => i + 1);
});

const go = (page) => {
  if (props.disabled) return;
  if (page === meta.value.current_page) return;
  emit("page-change", page);
};
</script>
