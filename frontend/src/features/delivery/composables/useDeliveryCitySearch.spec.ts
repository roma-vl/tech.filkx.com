import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { ref, nextTick } from "vue";
import { useDeliveryCitySearch } from "./useDeliveryCitySearch";

const { searchCities } = vi.hoisted(() => ({
  searchCities: vi.fn(),
}));

vi.mock("@/shared/services/api/deliveryApi", () => ({
  deliveryApi: { searchCities },
}));

describe("useDeliveryCitySearch", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    searchCities.mockReset();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it("does not search below the minimum query length", async () => {
    const enabled = ref(true);
    const { query, results } = useDeliveryCitySearch(enabled);

    query.value = "К";
    await vi.advanceTimersByTimeAsync(350);
    await nextTick();

    expect(searchCities).not.toHaveBeenCalled();
    expect(results.value).toEqual([]);
  });

  it("searches after the debounce once the query is long enough", async () => {
    searchCities.mockResolvedValue({
      data: { data: [{ ref: "city-ref-1", name: "Київ", area: "Київська" }] },
    });
    const enabled = ref(true);
    const { query, results, isSearching } = useDeliveryCitySearch(enabled);

    query.value = "Ки";
    await vi.advanceTimersByTimeAsync(350);
    await nextTick();
    await nextTick();

    expect(searchCities).toHaveBeenCalledWith("Ки");
    expect(isSearching.value).toBe(false);
    expect(results.value).toEqual([
      { ref: "city-ref-1", name: "Київ", area: "Київська" },
    ]);
  });

  it("does not search while disabled", async () => {
    const enabled = ref(false);
    const { query, results } = useDeliveryCitySearch(enabled);

    query.value = "Ки";
    await vi.advanceTimersByTimeAsync(350);
    await nextTick();

    expect(searchCities).not.toHaveBeenCalled();
    expect(results.value).toEqual([]);
  });

  it("clears results silently when the request fails", async () => {
    searchCities.mockRejectedValue(new Error("network error"));
    const enabled = ref(true);
    const { query, results } = useDeliveryCitySearch(enabled);

    query.value = "Ки";
    await vi.advanceTimersByTimeAsync(350);
    await nextTick();
    await nextTick();

    expect(results.value).toEqual([]);
  });

  it("reset clears the query and results", async () => {
    searchCities.mockResolvedValue({
      data: { data: [{ ref: "city-ref-1", name: "Київ", area: "Київська" }] },
    });
    const enabled = ref(true);
    const { query, results, reset } = useDeliveryCitySearch(enabled);

    query.value = "Ки";
    await vi.advanceTimersByTimeAsync(350);
    await nextTick();
    await nextTick();

    reset();

    expect(query.value).toBe("");
    expect(results.value).toEqual([]);
  });
});
