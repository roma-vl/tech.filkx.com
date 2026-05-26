<script setup>
import { computed, defineProps, defineEmits } from "vue";
import ArrowLeftIcon from "@/components/Icon/ArrowLeftIcon.vue";
import ArrowRightIcon from "@/components/Icon/ArrowRightIcon.vue";

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

  return props.pagination.meta ?? props.pagination;
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

<template>
  <div
    v-if="meta.total > meta.per_page && pages.length"
    class="flex flex-col md:flex-row items-center justify-between gap-2 md:gap-4"
  >
    <span class="text-sm text-gray-500 dark:text-gray-300">
      Всього записів: {{ meta.total }}
    </span>

    <div class="flex gap-1">
      <button
        class="pagination-btn px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex justify-center items-center"
        :disabled="meta.current_page === 1"
        @click="go(meta.current_page - 1)"
      >
        <ArrowLeftIcon class="w-4 h-4 mr-1" /> <span> Попередня </span>
      </button>

      <button
        v-for="page in pages"
        :key="page"
        class="pagination-page px-3 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
        :class="{
          'bg-primary-500 text-white dark:bg-primary-400':
            page === meta.current_page,
        }"
        @click="go(page)"
      >
        {{ page }}
      </button>

      <button
        class="pagination-btn px-2 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 flex justify-center items-center"
        :disabled="meta.current_page === meta.last_page"
        @click="go(meta.current_page + 1)"
      >
        <span> Наступна </span> <ArrowRightIcon class="inline w-4 h-4 ml-1" />
      </button>
    </div>
  </div>
</template>

<style scoped>
.pagination-page {
  min-width: 2rem;
  text-align: center;
  font-weight: 500;
}
</style>
