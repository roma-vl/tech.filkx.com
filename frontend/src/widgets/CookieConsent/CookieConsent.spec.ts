import { describe, it, expect, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import { createRouter, createMemoryHistory } from "vue-router";
import { i18n } from "@/i18n";
import CookieConsent from "./CookieConsent.vue";

async function mountBanner() {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", name: "home", component: { template: "<div />" } },
      { path: "/cookies", name: "cookies", component: { template: "<div />" } },
    ],
  });
  await router.push("/");
  await router.isReady();

  return mount(CookieConsent, { global: { plugins: [router, i18n] } });
}

describe("CookieConsent", () => {
  beforeEach(() => {
    localStorage.clear();
  });

  it("shows the banner on first visit", async () => {
    const wrapper = await mountBanner();
    expect(wrapper.find('[role="dialog"]').exists()).toBe(true);
  });

  it("stays hidden on repeat visits once a choice was persisted", async () => {
    localStorage.setItem("cookie_consent", "accepted");
    const wrapper = await mountBanner();
    expect(wrapper.find('[role="dialog"]').exists()).toBe(false);
  });

  it("accepting all persists the choice and hides the banner", async () => {
    const wrapper = await mountBanner();
    await wrapper.find("button.bg-\\[\\#00a046\\]").trigger("click");

    expect(localStorage.getItem("cookie_consent")).toBe("accepted");
    expect(wrapper.find('[role="dialog"]').exists()).toBe(false);
  });

  it("rejecting non-essential cookies persists a distinct choice and hides the banner", async () => {
    const wrapper = await mountBanner();
    const buttons = wrapper.findAll("button");
    await buttons[0].trigger("click"); // "essential only" is the first button

    expect(localStorage.getItem("cookie_consent")).toBe("essential");
    expect(wrapper.find('[role="dialog"]').exists()).toBe(false);
  });

  it("links to the real cookie policy page instead of duplicating the text", async () => {
    const wrapper = await mountBanner();
    const link = wrapper.find("a");
    expect(link.attributes("href")).toBe("/cookies");
  });
});
