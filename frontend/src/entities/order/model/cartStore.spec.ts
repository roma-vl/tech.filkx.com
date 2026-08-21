import { describe, it, expect, beforeEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useCartStore } from "./cartStore";
import type { CartItem } from "../types";

function makeItem(overrides: Partial<CartItem> = {}): CartItem {
  return {
    id: 1,
    productId: 1,
    name: "Test product",
    sku: "SKU-1",
    price: 100,
    quantity: 1,
    image: "",
    ...overrides,
  };
}

describe("cartStore getters", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("cartCount is 0 for an empty cart", () => {
    const store = useCartStore();
    expect(store.cartCount).toBe(0);
  });

  it("cartCount sums item quantities, not line count", () => {
    const store = useCartStore();
    store.cart = [
      makeItem({ id: 1, quantity: 2 }),
      makeItem({ id: 2, quantity: 3 }),
    ];
    expect(store.cartCount).toBe(5);
  });

  it("cartTotal is 0 for an empty cart", () => {
    const store = useCartStore();
    expect(store.cartTotal).toBe(0);
  });

  it("cartTotal sums price * quantity across items", () => {
    const store = useCartStore();
    store.cart = [
      makeItem({ id: 1, price: 100, quantity: 2 }),
      makeItem({ id: 2, price: 49.99, quantity: 1 }),
    ];
    expect(store.cartTotal).toBeCloseTo(249.99);
  });

  it("wishlistCount and compareCount reflect list lengths", () => {
    const store = useCartStore();
    store.wishlist = [{ id: 1 }, { id: 2 }];
    store.compare = [{ id: 3 }];
    expect(store.wishlistCount).toBe(2);
    expect(store.compareCount).toBe(1);
  });
});

describe("cartStore toast queue", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("addToast appends a toast with an incrementing id", () => {
    const store = useCartStore();
    store.addToast("First", "success");
    store.addToast("Second", "error");
    expect(store.toasts).toHaveLength(2);
    expect(store.toasts[0]).toMatchObject({
      message: "First",
      type: "success",
    });
    expect(store.toasts[1]).toMatchObject({ message: "Second", type: "error" });
    expect(store.toasts[0].id).not.toBe(store.toasts[1].id);
  });

  it("removeToast removes only the matching toast", () => {
    const store = useCartStore();
    store.addToast("Keep me");
    store.addToast("Remove me");
    const idToRemove = store.toasts[1].id;
    store.removeToast(idToRemove);
    expect(store.toasts).toHaveLength(1);
    expect(store.toasts[0].message).toBe("Keep me");
  });
});

describe("cartStore drawer state", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("openDrawer/closeDrawer toggle activeDrawer", () => {
    const store = useCartStore();
    expect(store.activeDrawer).toBeNull();
    store.openDrawer("cart");
    expect(store.activeDrawer).toBe("cart");
    store.closeDrawer();
    expect(store.activeDrawer).toBeNull();
  });
});
