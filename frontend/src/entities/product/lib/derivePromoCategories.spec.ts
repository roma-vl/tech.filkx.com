import { describe, it, expect } from "vitest";
import {
  derivePromoCategories,
  filterProductsByCategory,
} from "./derivePromoCategories";

function product(categories: any[] = []) {
  return { id: Math.random(), categories };
}

const laptops = { id: 1, slug: "laptops", name: { uk: "Ноутбуки", en: "Laptops" } };
const phones = { id: 2, slug: "phones", name: { uk: "Телефони", en: "Phones" } };

describe("derivePromoCategories", () => {
  it("returns an empty list for no products", () => {
    expect(derivePromoCategories([])).toEqual([]);
    expect(derivePromoCategories(undefined as any)).toEqual([]);
  });

  it("counts one product's categories once per category", () => {
    const facets = derivePromoCategories([product([laptops])]);
    expect(facets).toEqual([{ id: 1, slug: "laptops", name: laptops.name, count: 1 }]);
  });

  it("accumulates counts across multiple products", () => {
    const facets = derivePromoCategories([
      product([laptops]),
      product([laptops, phones]),
      product([phones]),
    ]);
    expect(facets).toEqual([
      { id: 1, slug: "laptops", name: laptops.name, count: 2 },
      { id: 2, slug: "phones", name: phones.name, count: 2 },
    ]);
  });

  it("ignores products with no categories or malformed entries", () => {
    expect(derivePromoCategories([product([]), product(undefined as any), {}])).toEqual(
      [],
    );
    expect(derivePromoCategories([product([{ slug: "" }, null])])).toEqual([]);
  });
});

describe("filterProductsByCategory", () => {
  const products = [product([laptops]), product([phones]), product([laptops, phones])];

  it("returns every product when no category is selected", () => {
    expect(filterProductsByCategory(products, "")).toEqual(products);
  });

  it("keeps only products tagged with the selected category", () => {
    expect(filterProductsByCategory(products, "phones")).toEqual([
      products[1],
      products[2],
    ]);
  });

  it("returns an empty list when nothing matches", () => {
    expect(filterProductsByCategory(products, "audio")).toEqual([]);
  });
});
