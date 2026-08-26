import { describe, it, expect, beforeEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useUiStore } from "./uiStore";

describe("uiStore stickyBottomBarHeight", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("defaults to 0", () => {
    const store = useUiStore();
    expect(store.stickyBottomBarHeight).toBe(0);
  });

  it("setStickyBottomBarHeight updates the value", () => {
    const store = useUiStore();
    store.setStickyBottomBarHeight(68);
    expect(store.stickyBottomBarHeight).toBe(68);

    store.setStickyBottomBarHeight(0);
    expect(store.stickyBottomBarHeight).toBe(0);
  });
});
