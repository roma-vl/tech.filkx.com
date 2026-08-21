import { describe, it, expect, vi } from "vitest";
import { defineComponent } from "vue";
import { mount } from "@vue/test-utils";
import { createRouter, createMemoryHistory } from "vue-router";
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
function withSetup<T>(composable: () => T) {
  let result!: T;
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", name: "catalog", component: { template: "<div />" } },
    ],
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

    const { result, router } = withSetup(useCatalog);
    await router.isReady();
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

    const { result, router } = withSetup(useCatalog);
    await router.isReady();
    await flushPromises();

    const product = result.rawProducts.value[0];
    expect(product.brand).toBe("Apple");
    expect(product.ram).toBe("8GB");
    expect(product.specs.weight).toBe("0.05 кг");
  });
});
