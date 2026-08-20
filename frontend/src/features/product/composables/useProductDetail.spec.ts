import { describe, it, expect, vi, beforeEach } from "vitest";
import { defineComponent } from "vue";
import { mount, flushPromises } from "@vue/test-utils";
import { createRouter, createMemoryHistory } from "vue-router";
import { createPinia, setActivePinia } from "pinia";
import productPlaceholder from "@/assets/images/product-placeholder.svg";

vi.mock("@/shared/services/api/productApi", () => ({
  productApi: {
    getProduct: vi.fn(),
    catalogGetRandomProducts: vi
      .fn()
      .mockResolvedValue({ data: { status: "success", data: [] } }),
    catalogGetRelatedProducts: vi
      .fn()
      .mockResolvedValue({ data: { status: "success", data: [] } }),
  },
}));

vi.mock("@/shared/services/api/orderApi", () => ({
  orderApi: {
    addToCart: vi
      .fn()
      .mockResolvedValue({ data: { status: "success", data: { items: [] } } }),
  },
}));

import { productApi } from "@/shared/services/api/productApi";
import { useProductDetail } from "./useProductDetail";

function apiVariant(overrides: Record<string, any> = {}) {
  return {
    id: 1,
    price: "1000",
    old_price: null,
    stocks: [{ quantity: "5", reserved: "0" }],
    dimensions: {},
    attribute_values: [],
    ...overrides,
  };
}

function apiProduct(overrides: Record<string, any> = {}) {
  return {
    id: 10,
    slug: "test-product",
    name: { uk: "Тестовий товар" },
    description: { uk: "Опис" },
    brand: null,
    categories: [],
    approvedReviewsAvgRating: null,
    approvedReviewsCount: null,
    variants: [apiVariant()],
    ...overrides,
  };
}

// Mounts the composable inside a real component so useRouter()/useRoute() resolve,
// and lets onMounted's fetchProductDetails()/fetchRandomProducts() settle.
async function withSetup<T>(composable: () => T) {
  let result!: T;
  const router = createRouter({
    history: createMemoryHistory(),
    routes: [
      {
        path: "/product/:id",
        name: "product-detail",
        component: { template: "<div />" },
      },
    ],
  });
  router.push("/product/test-product");
  await router.isReady();

  const TestComponent = defineComponent({
    setup() {
      result = composable();
      return () => null;
    },
  });

  const wrapper = mount(TestComponent, {
    global: { plugins: [router] },
  });

  await flushPromises();
  await flushPromises();

  return { result, wrapper, router };
}

describe("useProductDetail product mapping", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.mocked(productApi.catalogGetRandomProducts).mockResolvedValue({
      data: { status: "success", data: [] },
    } as any);
  });

  it("resolves the variant's primary image and carries its oldPrice", async () => {
    vi.mocked(productApi.getProduct).mockResolvedValue({
      data: {
        status: "success",
        data: apiProduct({
          variants: [
            apiVariant({
              old_price: "1500",
              dimensions: {
                images: [
                  { url: "https://cdn.example/a.webp", isPrimary: false },
                  { url: "https://cdn.example/b.webp", isPrimary: true },
                ],
              },
            }),
          ],
        }),
      },
    } as any);

    const { result } = await withSetup(() => useProductDetail());

    expect(result.product.value?.image).toBe("https://cdn.example/b.webp");
    expect(result.product.value?.oldPrice).toBe(1500);
    expect(result.product.value?.price).toBe(1000);
  });

  it("falls back to the placeholder image when no variant has photos", async () => {
    vi.mocked(productApi.getProduct).mockResolvedValue({
      data: { status: "success", data: apiProduct() },
    } as any);

    const { result } = await withSetup(() => useProductDetail());

    expect(result.product.value?.image).toBe(productPlaceholder);
  });

  it("builds galleryImages from every photo of the active variant", async () => {
    vi.mocked(productApi.getProduct).mockResolvedValue({
      data: {
        status: "success",
        data: apiProduct({
          variants: [
            apiVariant({
              dimensions: {
                images: [
                  { url: "https://cdn.example/a.webp", isPrimary: true },
                  { url: "https://cdn.example/b.webp", isPrimary: false },
                ],
              },
            }),
          ],
        }),
      },
    } as any);

    const { result } = await withSetup(() => useProductDetail());

    expect(result.galleryImages.value).toEqual([
      { label: "Основний вигляд", src: "https://cdn.example/a.webp" },
      { label: "Вигляд 2", src: "https://cdn.example/b.webp" },
    ]);
  });
});

describe("useProductDetail bundle pricing", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("bundleSavings sums each included item's own oldPrice-price markdown", async () => {
    vi.mocked(productApi.getProduct).mockResolvedValue({
      data: {
        status: "success",
        data: apiProduct({
          variants: [apiVariant({ price: "1000", old_price: "1200" })],
        }),
      },
    } as any);
    vi.mocked(productApi.catalogGetRandomProducts).mockResolvedValue({
      data: {
        status: "success",
        data: [
          apiProduct({
            id: 20,
            slug: "accessory-1",
            variants: [apiVariant({ id: 2, price: "300", old_price: "500" })],
          }),
        ],
      },
    } as any);

    const { result } = await withSetup(() => useProductDetail());

    // Device (locked, always included) + the one auto-selected accessory:
    // (1200-1000) + (500-300) = 400
    expect(result.bundleSavings.value).toBe(400);
    expect(result.bundleSubtotal.value).toBe(1300);
    expect(result.bundleTotal.value).toBe(result.bundleSubtotal.value);
  });

  it("bundleSavings is 0 when nothing in the bundle has an oldPrice", async () => {
    vi.mocked(productApi.getProduct).mockResolvedValue({
      data: {
        status: "success",
        data: apiProduct({ variants: [apiVariant({ price: "1000" })] }),
      },
    } as any);
    vi.mocked(productApi.catalogGetRandomProducts).mockResolvedValue({
      data: { status: "success", data: [] },
    } as any);

    const { result } = await withSetup(() => useProductDetail());

    expect(result.bundleSavings.value).toBe(0);
    expect(result.bundleTotal.value).toBe(result.bundleSubtotal.value);
  });

  it("excludes a deselected accessory from bundleSubtotal, bundleSavings and bundleTotal", async () => {
    vi.mocked(productApi.getProduct).mockResolvedValue({
      data: {
        status: "success",
        data: apiProduct({
          variants: [apiVariant({ price: "1000", old_price: "1200" })],
        }),
      },
    } as any);
    vi.mocked(productApi.catalogGetRandomProducts).mockResolvedValue({
      data: {
        status: "success",
        data: [
          apiProduct({
            id: 20,
            slug: "accessory-1",
            variants: [apiVariant({ id: 2, price: "300", old_price: "500" })],
          }),
        ],
      },
    } as any);

    const { result } = await withSetup(() => useProductDetail());
    const accessory = result.bundleItems.value.find(
      (item: any) => item.id === 20,
    );
    result.toggleBundleItem(accessory);

    expect(result.bundleSubtotal.value).toBe(1000);
    expect(result.bundleSavings.value).toBe(200);
    expect(result.bundleTotal.value).toBe(1000);
  });

  it("the locked device item cannot be toggled out of the bundle", async () => {
    vi.mocked(productApi.getProduct).mockResolvedValue({
      data: {
        status: "success",
        data: apiProduct({ variants: [apiVariant({ price: "1000" })] }),
      },
    } as any);
    vi.mocked(productApi.catalogGetRandomProducts).mockResolvedValue({
      data: { status: "success", data: [] },
    } as any);

    const { result } = await withSetup(() => useProductDetail());
    const device = result.bundleItems.value.find(
      (item: any) => item.id === "device",
    );
    result.toggleBundleItem(device);

    expect(result.bundleSubtotal.value).toBe(1000);
  });
});

describe("useProductDetail related products", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.mocked(productApi.catalogGetRandomProducts).mockResolvedValue({
      data: { status: "success", data: [] },
    } as any);
  });

  it("fetches related products for the loaded slug and maps them through mapCatalogProduct", async () => {
    vi.mocked(productApi.getProduct).mockResolvedValue({
      data: { status: "success", data: apiProduct() },
    } as any);
    vi.mocked(productApi.catalogGetRelatedProducts).mockResolvedValue({
      data: {
        status: "success",
        data: [
          apiProduct({
            id: 30,
            slug: "related-1",
            name: { uk: "Схожий товар" },
          }),
        ],
      },
    } as any);

    const { result } = await withSetup(() => useProductDetail());

    expect(productApi.catalogGetRelatedProducts).toHaveBeenCalledWith(
      "test-product",
    );
    expect(result.relatedProducts.value).toHaveLength(1);
    expect(result.relatedProducts.value[0]).toMatchObject({
      id: 30,
      slug: "related-1",
      name: "Схожий товар",
    });
  });

  it("leaves relatedProducts empty when the API call fails", async () => {
    vi.mocked(productApi.getProduct).mockResolvedValue({
      data: { status: "success", data: apiProduct() },
    } as any);
    vi.mocked(productApi.catalogGetRelatedProducts).mockRejectedValue(
      new Error("network error"),
    );

    const { result } = await withSetup(() => useProductDetail());

    expect(result.relatedProducts.value).toEqual([]);
  });
});
