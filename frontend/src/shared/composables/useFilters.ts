import { reactive, computed } from "vue";

export function useFilters<T extends Record<string, any>>(initialFilters: T) {
  const filters = reactive({ ...initialFilters }) as T;

  function updateFilter<K extends keyof T>(key: K, value: T[K]) {
    filters[key] = value;
  }

  function resetFilters() {
    Object.assign(filters, initialFilters);
  }

  const activeFiltersCount = computed(() => {
    return Object.keys(filters).reduce((count, key) => {
      const value = filters[key];
      const initialValue = initialFilters[key];
      if (JSON.stringify(value) !== JSON.stringify(initialValue)) {
        return count + 1;
      }
      return count;
    }, 0);
  });

  return {
    filters,
    updateFilter,
    resetFilters,
    activeFiltersCount,
  };
}
export default useFilters;
