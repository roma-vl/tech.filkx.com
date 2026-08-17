import { describe, it, expect, beforeEach, vi } from "vitest";
import { defineComponent } from "vue";
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import { createRouter, createMemoryHistory } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import type { CartItem } from "@/entities/order/types";

vi.mock("@/shared/services/api/productApi", () => ({
  productApi: {
    catalogGetRandomProducts: vi.fn().mockResolvedValue({ data: { status: "success", data: [] } }),
  },
}));

vi.mock("@/shared/services/api/orderApi", () => ({
  orderApi: {
    getOrderStatus: vi.fn(),
    validateCoupon: vi.fn(),
    placeOrder: vi.fn(),
    initiateLiqPayPayment: vi.fn(),
  },
}));

import { useShoppingCart } from "./useShoppingCart";

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

// Mounts the composable inside a real component so useRouter()/useRoute() resolve.
function withSetup<T>(composable: () => T) {
  let result!: T;
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [{ path: "/", name: "home", component: { template: "<div />" } }],
  });

  const TestComponent = defineComponent({
    setup() {
      result = composable();
      return () => null;
    },
  });

  const wrapper = mount(TestComponent, {
    global: { plugins: [router] },
  });

  return { result, wrapper, router };
}

describe("useShoppingCart shipping/tax/total math", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("charges the flat shipping fee below the free-shipping threshold", async () => {
    const { result, router } = withSetup(useShoppingCart);
    await router.isReady();
    result.cartStore.cart = [makeItem({ price: 1000, quantity: 1 })];

    expect(result.cartStore.cartTotal).toBe(1000);
    expect(result.shipping.value).toBe(250);
  });

  it("waives shipping once the cart total reaches the threshold", async () => {
    const { result, router } = withSetup(useShoppingCart);
    await router.isReady();
    result.cartStore.cart = [makeItem({ price: 5000, quantity: 1 })];

    expect(result.shipping.value).toBe(0);
  });

  it("charges no shipping for an empty cart", async () => {
    const { result, router } = withSetup(useShoppingCart);
    await router.isReady();
    result.cartStore.cart = [];

    expect(result.shipping.value).toBe(0);
  });

  it("computes tax as 7.5% of the total net of any applied discount", async () => {
    const { result, router } = withSetup(useShoppingCart);
    await router.isReady();
    result.cartStore.cart = [makeItem({ price: 1000, quantity: 1 })];
    result.promoDiscountAmount.value = 200;

    expect(result.tax.value).toBeCloseTo((1000 - 200) * 0.075);
  });

  it("never lets tax go negative when the discount exceeds the subtotal", async () => {
    const { result, router } = withSetup(useShoppingCart);
    await router.isReady();
    result.cartStore.cart = [makeItem({ price: 100, quantity: 1 })];
    result.promoDiscountAmount.value = 500;

    expect(result.tax.value).toBe(0);
  });

  it("total combines subtotal, discount, shipping, and tax", async () => {
    const { result, router } = withSetup(useShoppingCart);
    await router.isReady();
    result.cartStore.cart = [makeItem({ price: 1000, quantity: 1 })];
    result.promoDiscountAmount.value = 100;

    const expectedTax = (1000 - 100) * 0.075;
    const expectedShipping = 250; // below the 5000 threshold
    expect(result.total.value).toBeCloseTo(1000 - 100 + expectedShipping + expectedTax);
  });
});
