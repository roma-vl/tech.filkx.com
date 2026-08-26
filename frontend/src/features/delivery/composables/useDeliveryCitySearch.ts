import { ref, watch, type Ref } from "vue";
import { useDebounce } from "@/shared/composables/useDebounce";
import { deliveryApi } from "@/shared/services/api/deliveryApi";

export interface DeliveryCityOption {
  ref: string;
  name: string;
  area: string;
}

const MIN_QUERY_LENGTH = 2;
const DEBOUNCE_MS = 350;

/**
 * Debounced Nova Poshta city search - the same lookup checkout's autocomplete uses,
 * shared here so the product page's delivery-city picker doesn't fork it.
 *
 * @param enabled Set to false to skip searching (e.g. while a city is already selected).
 */
export function useDeliveryCitySearch(enabled: Ref<boolean>) {
  const query = ref("");
  const debouncedQuery = useDebounce(query, DEBOUNCE_MS);
  const results = ref<DeliveryCityOption[]>([]);
  const isSearching = ref(false);

  watch(debouncedQuery, async (value) => {
    if (!enabled.value) {
      return;
    }

    const trimmed = value.trim();
    if (trimmed.length < MIN_QUERY_LENGTH) {
      results.value = [];
      return;
    }

    isSearching.value = true;
    try {
      const response = await deliveryApi.searchCities(trimmed);
      results.value = response.data?.data || [];
    } catch {
      results.value = [];
    } finally {
      isSearching.value = false;
    }
  });

  function reset() {
    query.value = "";
    results.value = [];
  }

  return { query, results, isSearching, reset };
}

export default useDeliveryCitySearch;
