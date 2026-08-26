import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { i18n } from "@/i18n";
import DeliveryCityPickerModal from "./DeliveryCityPickerModal.vue";

const { searchCities } = vi.hoisted(() => ({
  searchCities: vi.fn(),
}));

vi.mock("@/shared/services/api/deliveryApi", () => ({
  deliveryApi: { searchCities },
}));

describe("DeliveryCityPickerModal", () => {
  beforeEach(() => {
    vi.useFakeTimers();
    searchCities.mockReset();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it("renders nothing while closed", () => {
    const wrapper = mount(DeliveryCityPickerModal, {
      props: { isOpen: false },
      global: { plugins: [i18n] },
    });

    expect(wrapper.find('input[type="text"]').exists()).toBe(false);
  });

  it("searches once open, and emits select + close when a city is picked", async () => {
    searchCities.mockResolvedValue({
      data: { data: [{ ref: "city-ref-1", name: "Київ", area: "Київська" }] },
    });

    const wrapper = mount(DeliveryCityPickerModal, {
      props: { isOpen: true },
      global: { plugins: [i18n] },
    });

    await wrapper.find("input").setValue("Ки");
    await vi.advanceTimersByTimeAsync(350);
    await flushPromises();

    expect(searchCities).toHaveBeenCalledWith("Ки");
    expect(wrapper.text()).toContain("Київ");

    await wrapper.find("li").trigger("mousedown");

    expect(wrapper.emitted("select")![0][0]).toEqual({
      ref: "city-ref-1",
      name: "Київ",
      area: "Київська",
    });
    expect(wrapper.emitted("close")).toBeTruthy();
  });

  it("emits close when the backdrop or close button is clicked", async () => {
    const wrapper = mount(DeliveryCityPickerModal, {
      props: { isOpen: true },
      global: { plugins: [i18n] },
    });

    await wrapper.find("button").trigger("click");

    expect(wrapper.emitted("close")).toBeTruthy();
  });
});
