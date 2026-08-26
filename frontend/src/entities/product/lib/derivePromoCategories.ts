export interface PromoCategoryFacet {
  id: number | string;
  slug: string;
  name: any;
  count: number;
}

/**
 * Builds the set of categories actually present among a promo page's product
 * list, with how many of those products carry each one - mirrors the main
 * catalog's brand/attribute facet counts, but computed client-side since a
 * promo page has no pagination and no per-category API of its own; every
 * product (with its `categories`) is already in the one response.
 */
export function derivePromoCategories(products: any[]): PromoCategoryFacet[] {
  const bySlug = new Map<string, PromoCategoryFacet>();

  for (const product of products || []) {
    for (const category of product?.categories || []) {
      if (!category?.slug) continue;
      const existing = bySlug.get(category.slug);
      if (existing) {
        existing.count += 1;
      } else {
        bySlug.set(category.slug, {
          id: category.id,
          slug: category.slug,
          name: category.name,
          count: 1,
        });
      }
    }
  }

  return [...bySlug.values()];
}

/** Narrows a promo page's product list to those tagged with the given category slug. */
export function filterProductsByCategory(
  products: any[],
  categorySlug: string,
): any[] {
  if (!categorySlug) return products || [];
  return (products || []).filter((product) =>
    (product?.categories || []).some(
      (category: any) => category?.slug === categorySlug,
    ),
  );
}
