import { describe, it, expect } from "vitest";
import {
  buildPromoCategoryTree,
  categoryPathSlugs,
  filterProductsByCategoryPath,
} from "./derivePromoCategories";

function product(categories: any[] = []) {
  return { id: Math.random(), categories };
}

// A small 3-level tree mirroring the real catalog's shape: top -> mid -> leaf.
const fullTree = [
  {
    id: 1,
    slug: "tv-audio-photo",
    name: { uk: "Смартфони, ТВ і електроніка", en: "Phones, TVs & electronics" },
    children: [
      {
        id: 2,
        slug: "tvs",
        name: { uk: "Телевізори", en: "TVs" },
        children: [
          { id: 3, slug: "tvs-compact", name: { uk: "Компактні", en: "Compact" }, children: [] },
        ],
      },
    ],
  },
  {
    id: 4,
    slug: "tablets-laptops-pc",
    name: { uk: "Ноутбуки, планшети", en: "Laptops, tablets" },
    children: [
      {
        id: 5,
        slug: "tablets",
        name: { uk: "Планшети", en: "Tablets" },
        children: [
          { id: 6, slug: "tablets-cellular", name: { uk: "З SIM", en: "Cellular" }, children: [] },
        ],
      },
    ],
  },
];

const tvsCompact = { id: 3, slug: "tvs-compact", name: { uk: "Компактні", en: "Compact" } };
const tabletsCellular = { id: 6, slug: "tablets-cellular", name: { uk: "З SIM", en: "Cellular" } };

describe("buildPromoCategoryTree", () => {
  it("returns an empty list for no products", () => {
    expect(buildPromoCategoryTree([], fullTree)).toEqual([]);
    expect(buildPromoCategoryTree(undefined as any, fullTree)).toEqual([]);
  });

  it("builds the full ancestor chain for a single product, counting every level", () => {
    const tree = buildPromoCategoryTree([product([tvsCompact])], fullTree);

    expect(tree).toEqual([
      {
        id: 1,
        slug: "tv-audio-photo",
        name: fullTree[0].name,
        count: 1,
        children: [
          {
            id: 2,
            slug: "tvs",
            name: fullTree[0].children[0].name,
            count: 1,
            children: [
              { id: 3, slug: "tvs-compact", name: tvsCompact.name, count: 1, children: [] },
            ],
          },
        ],
      },
    ]);
  });

  it("merges products under different top-level branches into separate roots", () => {
    const tree = buildPromoCategoryTree(
      [product([tvsCompact]), product([tabletsCellular])],
      fullTree,
    );

    expect(tree.map((node) => node.slug)).toEqual(["tv-audio-photo", "tablets-laptops-pc"]);
    expect(tree[0].count).toBe(1);
    expect(tree[1].count).toBe(1);
  });

  it("bubbles counts up through every ancestor when multiple products share a branch", () => {
    const tree = buildPromoCategoryTree(
      [product([tvsCompact]), product([tvsCompact])],
      fullTree,
    );

    expect(tree[0].count).toBe(2);
    expect(tree[0].children[0].count).toBe(2);
    expect(tree[0].children[0].children[0].count).toBe(2);
  });

  it("ignores categories that aren't found in the full tree", () => {
    expect(
      buildPromoCategoryTree([product([{ slug: "not-a-real-category" }])], fullTree),
    ).toEqual([]);
  });

  it("ignores products with no categories or malformed entries", () => {
    expect(
      buildPromoCategoryTree([product([]), product(undefined as any), {}], fullTree),
    ).toEqual([]);
    expect(
      buildPromoCategoryTree([product([{ slug: "" }, null])], fullTree),
    ).toEqual([]);
  });
});

describe("categoryPathSlugs", () => {
  it("returns an empty list for no category", () => {
    expect(categoryPathSlugs("", fullTree)).toEqual([]);
  });

  it("returns every ancestor slug plus the category itself", () => {
    expect(categoryPathSlugs("tvs-compact", fullTree)).toEqual([
      "tv-audio-photo",
      "tvs",
      "tvs-compact",
    ]);
  });
});

describe("filterProductsByCategoryPath", () => {
  const products = [product([tvsCompact]), product([tabletsCellular])];

  it("returns every product when no category is selected", () => {
    expect(filterProductsByCategoryPath(products, "", fullTree)).toEqual(products);
  });

  it("keeps products tagged with the exact leaf category", () => {
    expect(filterProductsByCategoryPath(products, "tvs-compact", fullTree)).toEqual([
      products[0],
    ]);
  });

  it("keeps products under a selected ancestor (mid-level) category", () => {
    expect(filterProductsByCategoryPath(products, "tvs", fullTree)).toEqual([products[0]]);
  });

  it("keeps products under a selected top-level category", () => {
    expect(filterProductsByCategoryPath(products, "tv-audio-photo", fullTree)).toEqual([
      products[0],
    ]);
  });

  it("returns an empty list when nothing matches", () => {
    expect(filterProductsByCategoryPath(products, "audio", fullTree)).toEqual([]);
  });
});
