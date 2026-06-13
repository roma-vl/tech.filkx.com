<template>
  <div
    v-if="meta.total > meta.per_page && pages.length"
    class="flex flex-col md:flex-row items-center justify-between gap-4 py-3"
  >
    <span class="text-sm text-gray-500 dark:text-gray-400">
      {{ totalLabel }}
    </span>

    <div class="flex gap-1">
      <AppButton
        variant="secondary"
        size="sm"
        :disabled="meta.current_page === 1 || disabled"
        @click="go(meta.current_page - 1)"
      >
        <ArrowLeftIcon class="w-4 h-4 mr-1" />
        <span class="hidden sm:inline">{{ prevLabel }}</span>
      </AppButton>
      <template
        v-for="page in pages"
        :key="page"
      >
        <span
          v-if="page === '...'"
          class="px-2 py-1 text-sm font-medium text-gray-400 dark:text-gray-500 self-center select-none"
        >
          ...
        </span>
        <AppButton
          v-else
          :variant="page === meta.current_page ? 'primary' : 'text'"
          size="sm"
          class="min-w-[32px]"
          :disabled="disabled"
          @click="go(page)"
        >
          {{ page }}
        </AppButton>
      </template>
      <AppButton
        variant="secondary"
        size="sm"
        :disabled="meta.current_page === meta.last_page || disabled"
        @click="go(meta.current_page + 1)"
      >
        <span class="hidden sm:inline">{{ nextLabel }}</span>
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

const { t, te } = useI18n();

const prevLabel = computed(() => te("admin.pagination.prev") ? t("admin.pagination.prev") : (te("pagination.prev") ? t("pagination.prev") : "Попередня"));
const nextLabel = computed(() => te("admin.pagination.next") ? t("admin.pagination.next") : (te("pagination.next") ? t("pagination.next") : "Наступна"));
const totalLabel = computed(() => {
  const total = meta.value.total;
  if (te("admin.pagination.total")) {
    return t("admin.pagination.total", { total });
  }
  if (te("pagination.total")) {
    return t("pagination.total", { total });
  }
  return `Всього записів: ${total}`;
});

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
  const current = meta.value.current_page;
  const last = meta.value.last_page;

  if (!last || last <= 1) return [];
  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1);
  }

  const result = [];
  
  if (current <= 4) {
    for (let i = 1; i <= 5; i++) {
      result.push(i);
    }
    result.push("...");
    result.push(last);
  } else if (current >= last - 3) {
    result.push(1);
    result.push("...");
    for (let i = last - 4; i <= last; i++) {
      result.push(i);
    }
  } else {
    result.push(1);
    result.push("...");
    result.push(current - 1);
    result.push(current);
    result.push(current + 1);
    result.push("...");
    result.push(last);
  }

  return result;
});

const go = (page) => {
  if (props.disabled) return;
  if (page === "...") return;
  if (page === meta.value.current_page) return;
  emit("page-change", page);
};
</script>
