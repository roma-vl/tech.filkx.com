import { describe, it, expect } from "vitest";
import { mapCatalogProduct } from "./mapCatalogProduct";

function apiProduct(overrides: Record<string, any> = {}) {
  return {
    id: 1,
    slug: "test-product",
    name: { uk: "Тестовий товар" },
    description: { uk: "Опис" },
    brand: null,
    categories: [],
    approvedReviewsAvgRating: null,
    approvedReviewsCount: null,
    variants: [
      {
        price: "1000",
        old_price: null,
        weight: null,
        stocks: [{ quantity: "5", reserved: "0" }],
        attribute_values: [],
      },
    ],
    ...overrides,
  };
}

describe("mapCatalogProduct", () => {
  it("returns null for a falsy input", () => {
    expect(mapCatalogProduct(null)).toBeNull();
    expect(mapCatalogProduct(undefined)).toBeNull();
  });

  it("maps price, rating and reviews from the raw API fields", () => {
    const mapped = mapCatalogProduct(
      apiProduct({
        approvedReviewsAvgRating: "4.5",
        approvedReviewsCount: "12",
      }),
    );

    expect(mapped?.price).toBe(1000);
    expect(mapped?.rating).toBe(4.5);
    expect(mapped?.reviews).toBe(12);
  });

  it("computes a discount badge from oldPrice, and omits it when there is no discount", () => {
    const discounted = mapCatalogProduct(
      apiProduct({
        variants: [{ price: "800", old_price: "1000", stocks: [] }],
      }),
    );
    expect(discounted?.badge).toBe("-20%");
    expect(discounted?.badgeClass).toBe("bg-rose-600");

    const regular = mapCatalogProduct(apiProduct());
    expect(regular?.badge).toBeNull();
    expect(regular?.badgeClass).toBe("");
  });

  it("derives inStock from quantity minus reserved across stocks", () => {
    const inStock = mapCatalogProduct(
      apiProduct({
        variants: [
          { price: "100", stocks: [{ quantity: "5", reserved: "3" }] },
        ],
      }),
    );
    expect(inStock?.inStock).toBe(true);

    const outOfStock = mapCatalogProduct(
      apiProduct({
        variants: [
          { price: "100", stocks: [{ quantity: "5", reserved: "5" }] },
        ],
      }),
    );
    expect(outOfStock?.inStock).toBe(false);
  });

  it("falls back to the product's own attribute_values when the variant has none", () => {
    const mapped = mapCatalogProduct(
      apiProduct({
        attribute_values: [
          { attribute: { code: "ram" }, attribute_value: { value: "16GB" } },
        ],
      }),
    );

    expect(mapped?.ram).toBe("16GB");
  });

  it("falls back to custom_value only when there is no catalog attribute_value", () => {
    const mapped = mapCatalogProduct(
      apiProduct({
        variants: [
          {
            price: "100",
            stocks: [],
            attribute_values: [
              {
                attribute: { code: "processor" },
                attribute_value: null,
                custom_value: "Custom Chip X",
              },
            ],
          },
        ],
      }),
    );

    expect(mapped?.specs.processor).toBe("Custom Chip X");
  });

  it("takes the category name from the first category, in Ukrainian when present", () => {
    const mapped = mapCatalogProduct(
      apiProduct({
        categories: [{ name: { uk: "Смартфони", en: "Smartphones" } }],
      }),
    );

    expect(mapped?.category).toBe("Смартфони");
  });

  it("returns null category when the product has none", () => {
    const mapped = mapCatalogProduct(apiProduct({ categories: [] }));
    expect(mapped?.category).toBeNull();
  });
});
