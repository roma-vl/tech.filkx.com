import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { i18n } from "@/i18n";
import CheckoutForm from "./CheckoutForm.vue";

const { getAvailability, searchCities, searchWarehouses } = vi.hoisted(() => ({
  getAvailability: vi.fn(),
  searchCities: vi.fn(),
  searchWarehouses: vi.fn(),
}));

vi.mock("@/shared/services/api/deliveryApi", () => ({
  deliveryApi: { getAvailability, searchCities, searchWarehouses },
}));

function baseModelValue(overrides: Partial<Record<string, string>> = {}) {
  return {
    customerName: "",
    customerPhone: "",
    customerEmail: "",
    shippingCountry: "Ukraine",
    shippingCity: "",
    shippingAddress: "",
    deliveryMethod: "nova_poshta",
    paymentMethod: "cod",
    ...overrides,
  };
}

function mountForm(modelValue = baseModelValue()) {
  return mount(CheckoutForm, {
    props: { modelValue, "onUpdate:modelValue": () => {} },
    global: { plugins: [i18n] },
  });
}

describe("CheckoutForm Nova Poshta delivery fields", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    getAvailability.mockReset();
    searchCities.mockReset();
    searchWarehouses.mockReset();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it("falls back to plain city/address inputs when the capability check reports unavailable", async () => {
    getAvailability.mockResolvedValue({ data: { data: { available: false } } });

    const wrapper = mountForm();
    await flushPromises();

    expect(wrapper.find("#shipping_city").exists()).toBe(true);
    expect(wrapper.find("#shipping_address").exists()).toBe(true);
    expect(wrapper.find("#shipping_city_search").exists()).toBe(false);
  });

  it("falls back to plain inputs when the capability check errors out", async () => {
    getAvailability.mockRejectedValue(new Error("network error"));

    const wrapper = mountForm();
    await flushPromises();

    expect(wrapper.find("#shipping_city").exists()).toBe(true);
    expect(wrapper.find("#shipping_city_search").exists()).toBe(false);
  });

  it("renders city autocomplete once availability is confirmed, and warehouse search after a city is picked", async () => {
    getAvailability.mockResolvedValue({ data: { data: { available: true } } });
    searchCities.mockResolvedValue({
      data: {
        data: [
          {
            ref: "city-ref-1",
            name: "м. Київ, Київська обл.",
            area: "Київська",
          },
        ],
      },
    });
    searchWarehouses.mockResolvedValue({
      data: {
        data: [
          {
            ref: "wh-ref-1",
            number: "14",
            description: "Відділення №14: вул. Хрещатик, 1",
          },
        ],
      },
    });

    const wrapper = mountForm();
    await flushPromises();

    expect(wrapper.find("#shipping_city").exists()).toBe(false);
    expect(wrapper.find("#shipping_city_search").exists()).toBe(true);

    const cityInput = wrapper.find("#shipping_city_search");
    await cityInput.setValue("Ки");
    await vi.advanceTimersByTimeAsync(350);
    await flushPromises();

    expect(searchCities).toHaveBeenCalledWith("Ки");
    expect(wrapper.text()).toContain("м. Київ, Київська обл.");

    const cityOption = wrapper
      .findAll("li")
      .find((li) => li.text().includes("Київ"));
    await cityOption!.trigger("mousedown");
    await flushPromises();

    expect(
      (wrapper.emitted("update:modelValue")!.at(-1)![0] as any).shippingCity,
    ).toBe("м. Київ, Київська обл.");
    expect(searchWarehouses).toHaveBeenCalledWith("city-ref-1", undefined);

    const warehouseInput = wrapper.find("#shipping_warehouse_search");
    expect(warehouseInput.attributes("disabled")).toBeUndefined();

    await warehouseInput.trigger("focus");
    await flushPromises();
    expect(wrapper.text()).toContain("Відділення №14: вул. Хрещатик, 1");

    const warehouseOption = wrapper
      .findAll("li")
      .find((li) => li.text().includes("Відділення №14"));
    await warehouseOption!.trigger("mousedown");

    expect(
      (wrapper.emitted("update:modelValue")!.at(-1)![0] as any).shippingAddress,
    ).toBe("Відділення №14: вул. Хрещатик, 1");
  });
});
