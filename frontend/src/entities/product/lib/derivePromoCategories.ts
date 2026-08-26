import { getCategoryPath } from "@/shared/utils/categoryMapper";

export interface PromoCategoryTreeNode {
  id: number | string;
  slug: string;
  name: any;
  count: number;
  children: PromoCategoryTreeNode[];
}

interface MutableNode {
  id: number | string;
  slug: string;
  name: any;
  count: number;
  childMap: Map<string, MutableNode>;
}

function toPlainNode(node: MutableNode): PromoCategoryTreeNode {
  return {
    id: node.id,
    slug: node.slug,
    name: node.name,
    count: node.count,
    children: [...node.childMap.values()].map(toPlainNode),
  };
}

/**
 * Builds the root-to-leaf category tree actually represented among a promo
 * page's products, with a count per node - like the main site's category
 * nav (icons on the top level, a plain nested list below), but pruned to
 * only the branches this promo's products fall under, and only counting
 * those products rather than the whole catalog.
 *
 * A promo page has no pagination and no per-category API of its own, so
 * this is computed client-side from data already in hand: each product's
 * own (leaf) categories, and the full category tree from
 * `/v1/catalog/categories` (the same one breadcrumbs/mega-menu already
 * fetch) to resolve each leaf's ancestors via `getCategoryPath`.
 */
export function buildPromoCategoryTree(
  products: any[],
  fullCategoryTree: any[],
): PromoCategoryTreeNode[] {
  const roots = new Map<string, MutableNode>();

  for (const product of products || []) {
    for (const category of product?.categories || []) {
      if (!category?.slug) continue;

      const path = getCategoryPath(fullCategoryTree || [], category.slug);
      if (!path || path.length === 0) continue;

      let level = roots;
      for (const cat of path) {
        let node = level.get(cat.slug);
        if (!node) {
          node = { id: cat.id, slug: cat.slug, name: cat.name, count: 0, childMap: new Map() };
          level.set(cat.slug, node);
        }
        node.count += 1;
        level = node.childMap;
      }
    }
  }

  return [...roots.values()].map(toPlainNode);
}

/** Every ancestor-and-self slug on the path to `categorySlug`, for auto-expanding a tree to reveal a selection. */
export function categoryPathSlugs(
  categorySlug: string,
  fullCategoryTree: any[],
): string[] {
  if (!categorySlug) return [];
  const path = getCategoryPath(fullCategoryTree || [], categorySlug);
  return path ? path.map((cat) => cat.slug) : [categorySlug];
}

/**
 * Narrows a promo page's product list to those tagged with the given
 * category slug *or any of its descendants* - selecting a parent node
 * (e.g. "Смартфони, ТВ і електроніка") should include everything filed
 * under it, not just products tagged with that exact slug.
 */
export function filterProductsByCategoryPath(
  products: any[],
  categorySlug: string,
  fullCategoryTree: any[],
): any[] {
  if (!categorySlug) return products || [];

  return (products || []).filter((product) =>
    (product?.categories || []).some((category: any) => {
      if (!category?.slug) return false;
      if (category.slug === categorySlug) return true;

      const path = getCategoryPath(fullCategoryTree || [], category.slug);
      return !!path?.some((cat) => cat.slug === categorySlug);
    }),
  );
}
