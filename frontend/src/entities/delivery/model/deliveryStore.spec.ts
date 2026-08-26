import { describe, it, expect, beforeEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useDeliveryStore } from "./deliveryStore";

const CITY_STORAGE_KEY = "filkx_delivery_city";

describe("deliveryStore", () => {
  beforeEach(() => {
    localStorage.clear();
    setActivePinia(createPinia());
  });

  it("has no city selected by default", () => {
    const store = useDeliveryStore();
    expect(store.city).toBeNull();
  });

  it("setCity updates state and persists to localStorage", () => {
    const store = useDeliveryStore();

    store.setCity({ ref: "city-ref-1", name: "м. Київ, Київська обл." });

    expect(store.city).toEqual({
      ref: "city-ref-1",
      name: "м. Київ, Київська обл.",
    });
    expect(JSON.parse(localStorage.getItem(CITY_STORAGE_KEY)!)).toEqual({
      ref: "city-ref-1",
      name: "м. Київ, Київська обл.",
    });
  });

  it("restores the previously selected city from localStorage on init", () => {
    localStorage.setItem(
      CITY_STORAGE_KEY,
      JSON.stringify({ ref: "city-ref-2", name: "Львів" }),
    );

    const store = useDeliveryStore();

    expect(store.city).toEqual({ ref: "city-ref-2", name: "Львів" });
  });

  it("ignores a malformed stored value", () => {
    localStorage.setItem(CITY_STORAGE_KEY, "not-json");

    const store = useDeliveryStore();

    expect(store.city).toBeNull();
  });

  it("clearCity resets state and removes the stored value", () => {
    const store = useDeliveryStore();
    store.setCity({ ref: "city-ref-1", name: "Київ" });

    store.clearCity();

    expect(store.city).toBeNull();
    expect(localStorage.getItem(CITY_STORAGE_KEY)).toBeNull();
  });
});
