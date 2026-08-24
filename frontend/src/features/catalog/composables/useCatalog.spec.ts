import { describe, it, expect, vi, beforeEach } from "vitest";
import { defineComponent } from "vue";
import { mount } from "@vue/test-utils";
import { createRouter, createMemoryHistory } from "vue-router";
import { createI18n } from "vue-i18n";
import { flushPromises } from "@vue/test-utils";

vi.mock("@/shared/services/api/productApi", () => ({
  productApi: {
    catalogGetProducts: vi.fn(),
    catalogGetCategories: vi
      .fn()
      .mockResolvedValue({ data: { status: "success", data: [] } }),
    catalogGetBrands: vi
      .fn()
      .mockResolvedValue({ data: { status: "success", data: [] } }),
    catalogGetFiltersSchema: vi.fn().mockResolvedValue({
      data: {
        status: "success",
        data: { attributes: [], price: { min: 0, max: 200000 } },
      },
    }),
  },
}));

import { productApi } from "@/shared/services/api/productApi";
import { useCatalog } from "./useCatalog";

// Mounts the composable inside a real component so useRouter()/useRoute() resolve.
// initialPath lets a test start the router on a URL with query params already
// present, e.g. to exercise restoring a filter selection from a shared/refreshed link.
async function withSetup<T>(composable: () => T, initialPath = "/") {
  let result!: T;
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", name: "catalog", component: { template: "<div />" } },
    ],
  });

  await router.push(initialPath);
  await router.isReady();

  // useCatalog() reads the active locale (for the label/name translations
  // it picks between) via useI18n(), which throws unless the plugin is
  // installed - no real messages needed since this composable never calls t().
  const i18n = createI18n({ legacy: false, locale: "uk", messages: {} });

  const TestComponent = defineComponent({
    setup() {
      result = composable();
      return () => null;
    },
  });

  const wrapper = mount(TestComponent, {
    global: { plugins: [router, i18n] },
  });

  return { result, wrapper, router };
}

function apiProductWithNoRealAttributes() {
  return {
    id: 1,
    slug: "apple-airpods-pro-2",
    name: { uk: "AirPods Pro 2" },
    description: { uk: "Навушники" },
    brand: null,
    categories: [],
    approvedReviewsAvgRating: null,
    approvedReviewsCount: null,
    variants: [
      {
        price: "5999",
        weight: null,
        stocks: [{ quantity: "5", reserved: "0" }],
        attribute_values: [],
      },
    ],
  };
}

describe("useCatalog product mapping", () => {
  it("does not fabricate brand/ram/category/specs when a product has no matching attributes", async () => {
    (productApi.catalogGetProducts as any).mockResolvedValue({
      data: {
        status: "success",
        data: {
          data: [apiProductWithNoRealAttributes()],
          currentPage: 1,
          lastPage: 1,
          total: 1,
        },
      },
    });

    const { result } = await withSetup(useCatalog);
    await flushPromises();

    expect(result.rawProducts.value).toHaveLength(1);
    const product = result.rawProducts.value[0];

    expect(product.brand).toBeFalsy();
    expect(product.ram).toBeFalsy();
    expect(product.category).toBeFalsy();
    expect(product.specs.processor).toBeFalsy();
    expect(product.specs.screen).toBeFalsy();
    expect(product.specs.storage).toBeFalsy();
    expect(product.specs.os).toBeFalsy();
    expect(product.specs.weight).toBeFalsy();
  });

  it("keeps real attribute values when the API returns them", async () => {
    const apiProduct = apiProductWithNoRealAttributes();
    apiProduct.brand = { name: "Apple" } as any;
    apiProduct.variants[0].weight = "0.05" as any;
    apiProduct.variants[0].attribute_values = [
      {
        attribute: { code: "ram" },
        attribute_value: { value: "8GB" },
      },
    ] as any;

    (productApi.catalogGetProducts as any).mockResolvedValue({
      data: {
        status: "success",
        data: { data: [apiProduct], currentPage: 1, lastPage: 1, total: 1 },
      },
    });

    const { result } = await withSetup(useCatalog);
    await flushPromises();

    const product = result.rawProducts.value[0];
    expect(product.brand).toBe("Apple");
    expect(product.ram).toBe("8GB");
    expect(product.specs.weight).toBe("0.05 кг");
  });
});

describe("useCatalog filter URL persistence", () => {
  beforeEach(() => {
    (productApi.catalogGetProducts as any).mockResolvedValue({
      data: {
        status: "success",
        data: { data: [], currentPage: 1, lastPage: 1, total: 0 },
      },
    });
  });

  it("writes a selected brand into the URL query", async () => {
    const { result, router } = await withSetup(useCatalog);
    await flushPromises();

    result.selectedBrands.value = ["samsung"];
    await flushPromises();

    expect(router.currentRoute.value.query.brand).toBe("samsung");
  });

  // Otherwise a reload/shared link/browser back loses the selection entirely,
  // since these refs were previously the only place it lived.
  it("restores brand/discount/rating filters from the initial URL", async () => {
    const { result } = await withSetup(
      useCatalog,
      "/?brand=samsung,xiaomi&discounts=1&rating=4",
    );
    await flushPromises();

    expect(result.selectedBrands.value).toEqual(["samsung", "xiaomi"]);
    expect(result.onlyDiscounts.value).toBe(true);
    expect(result.selectedRating.value).toBe("4");
  });

  it("clears the URL query when a filter is unset", async () => {
    const { result, router } = await withSetup(useCatalog, "/?brand=samsung");
    await flushPromises();

    result.selectedBrands.value = [];
    await flushPromises();

    expect(router.currentRoute.value.query.brand).toBeUndefined();
  });
});
