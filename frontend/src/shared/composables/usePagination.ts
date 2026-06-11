import { ref, computed } from "vue";

export function usePagination(initialPerPage = 10) {
  const page = ref(1);
  const perPage = ref(initialPerPage);
  const total = ref(0);

  const totalPages = computed(
    () => Math.ceil(total.value / perPage.value) || 1,
  );

  function next() {
    if (page.value < totalPages.value) {
      page.value++;
    }
  }

  function prev() {
    if (page.value > 1) {
      page.value--;
    }
  }

  function setPage(p: number) {
    if (p >= 1 && p <= totalPages.value) {
      page.value = p;
    }
  }

  function reset() {
    page.value = 1;
  }

  return {
    page,
    perPage,
    total,
    totalPages,
    next,
    prev,
    setPage,
    reset,
  };
}
