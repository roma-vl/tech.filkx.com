import { ref } from "vue";

export function useSorting(
  initialSortBy = "id",
  initialSortOrder: "asc" | "desc" = "asc",
) {
  const sortBy = ref(initialSortBy);
  const sortOrder = ref<"asc" | "desc">(initialSortOrder);

  function toggleSort(field: string) {
    if (sortBy.value === field) {
      sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
    } else {
      sortBy.value = field;
      sortOrder.value = "asc";
    }
  }

  function setSort(field: string, order: "asc" | "desc") {
    sortBy.value = field;
    sortOrder.value = order;
  }

  function reset() {
    sortBy.value = initialSortBy;
    sortOrder.value = initialSortOrder;
  }

  return {
    sortBy,
    sortOrder,
    toggleSort,
    setSort,
    reset,
  };
}
