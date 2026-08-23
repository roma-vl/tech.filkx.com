import { describe, it, expect, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import { createRouter, createMemoryHistory } from "vue-router";
import { i18n } from "@/i18n";
import { useCartStore } from "@/entities/order/model/cartStore";
import type { CartItem } from "@/entities/order/types";
import CartDrawer from "./CartDrawer.vue";

// Same threshold/fee/rate the main checkout flow uses (useShoppingCart.ts) — this
// spec exists to catch the two computations drifting apart again, not to assert
// the constants are "correct" in isolation.
const SHIPPING_THRESHOLD = 5000;
const SHIPPING_FEE = 250;
const TAX_RATE = 0.075;

function formatPrice(price: number) {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
}

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

async function mountDrawer() {
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", name: "home", component: { template: "<div />" } },
      { path: "/cart", name: "cart", component: { template: "<div />" } },
    ],
  });
  await router.push("/");
  await router.isReady();

  return mount(CartDrawer, {
    global: { plugins: [router, i18n] },
  });
}

describe("CartDrawer totals stay in agreement with useShoppingCart", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("shows the flat shipping fee below the free-shipping threshold", async () => {
    const store = useCartStore();
    store.cart = [makeItem({ price: 1000, quantity: 1 })];
    store.activeDrawer = "cart";
    const wrapper = await mountDrawer();

    expect(wrapper.text()).toContain(formatPrice(SHIPPING_FEE));
  });

  it("shows free shipping once the threshold is reached", async () => {
    const store = useCartStore();
    store.cart = [makeItem({ price: SHIPPING_THRESHOLD, quantity: 1 })];
    store.activeDrawer = "cart";
    const wrapper = await mountDrawer();

    expect(wrapper.text()).toContain("FREE");
  });

  it("computes tax as cartTotal * taxRate with no discount applied", async () => {
    const store = useCartStore();
    store.cart = [makeItem({ price: 1000, quantity: 1 })];
    store.activeDrawer = "cart";
    const wrapper = await mountDrawer();

    // CartDrawer intentionally has no coupon/discount UI — its tax preview is
    // cartTotal * taxRate, matching useShoppingCart's formula when discount is 0.
    expect(wrapper.text()).toContain(formatPrice(1000 * TAX_RATE));
  });

  it("total equals subtotal + shipping + tax, matching useShoppingCart's formula", async () => {
    const store = useCartStore();
    store.cart = [makeItem({ price: 1000, quantity: 2 })]; // subtotal 2000, below threshold
    store.activeDrawer = "cart";
    const wrapper = await mountDrawer();

    const subtotal = 2000;
    const shipping = SHIPPING_FEE;
    const tax = subtotal * TAX_RATE;
    expect(wrapper.text()).toContain(formatPrice(subtotal + shipping + tax));
  });
});
