import { describe, it, expect, vi, beforeEach } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import { createRouter, createMemoryHistory } from "vue-router";
import { i18n } from "@/i18n";
import { useDeliveryStore } from "@/entities/delivery/model/deliveryStore";
import ProductPurchase from "./ProductPurchase.vue";

const { getEstimate } = vi.hoisted(() => ({
  getEstimate: vi.fn(),
}));

vi.mock("@/shared/services/api/deliveryApi", () => ({
  deliveryApi: { getEstimate },
}));

function baseProduct() {
  return {
    id: 1,
    productId: "SKU-1",
    name: "Test phone",
    category: "Phones",
    rating: 4.5,
    reviews: 12,
    price: 20000,
    oldPrice: null,
    inStock: true,
  };
}

async function mountPurchase() {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", name: "home", component: { template: "<div />" } },
      { path: "/login", name: "login", component: { template: "<div />" } },
    ],
  });
  await router.push("/");
  await router.isReady();

  return mount(ProductPurchase, {
    props: {
      product: baseProduct(),
      availableColors: [],
      selectedColor: "",
      availableStorage: [],
      selectedStorage: "",
      formatPrice: (p: number) => `${p} ₴`,
    },
    global: { plugins: [router, i18n] },
  });
}

describe("ProductPurchase delivery estimate", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    getEstimate.mockReset();
    localStorage.clear();
  });

  it("shows the static delivery text when no city is set", async () => {
    const wrapper = await mountPurchase();
    await flushPromises();

    expect(getEstimate).not.toHaveBeenCalled();
    expect(wrapper.text()).toContain("Delivery");
    expect(wrapper.text()).toContain("Free from");
    expect(wrapper.text()).toContain("Set delivery city");
  });

  it("shows the city estimate once a city is set and the backend confirms one", async () => {
    getEstimate.mockResolvedValue({
      data: { data: { available: true, date: "2026-08-28" } },
    });

    const wrapper = await mountPurchase();
    const deliveryStore = useDeliveryStore();
    deliveryStore.setCity({ ref: "city-ref-1", name: "Kyiv" });
    await flushPromises();

    expect(getEstimate).toHaveBeenCalledWith("city-ref-1");
    expect(wrapper.text()).toContain("Delivery to Kyiv");
    expect(wrapper.text()).toContain("approx.");
    expect(wrapper.text()).toContain("Change city");
  });

  it("falls back to the static text when the estimate is unavailable", async () => {
    getEstimate.mockResolvedValue({
      data: { data: { available: false, date: null } },
    });

    const wrapper = await mountPurchase();
    const deliveryStore = useDeliveryStore();
    deliveryStore.setCity({ ref: "city-ref-1", name: "Kyiv" });
    await flushPromises();

    expect(wrapper.text()).toContain("Delivery");
    expect(wrapper.text()).toContain("Free from");
    expect(wrapper.text()).not.toContain("Delivery to Kyiv");
  });

  it("falls back to the static text silently when the estimate request fails", async () => {
    getEstimate.mockRejectedValue(new Error("network error"));

    const wrapper = await mountPurchase();
    const deliveryStore = useDeliveryStore();
    deliveryStore.setCity({ ref: "city-ref-1", name: "Kyiv" });
    await flushPromises();

    expect(wrapper.text()).toContain("Delivery");
    expect(wrapper.text()).toContain("Free from");
  });
});
