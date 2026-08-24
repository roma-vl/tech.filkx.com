export const getCategoryIcon = (slug: string): string => {
  const mapping: Record<string, string> = {
    smartphones: "smartphone",
    phones: "smartphone",
    "smart-gadgets": "watch",
    "tablets-laptops-pc": "laptop_mac",
    laptops: "laptop_mac",
    "tv-audio-photo": "tv",
    audio: "tv",
    "home-garden": "home",
    kitchen: "soup_kitchen",
    "beauty-health": "face",
    kids: "child_care",
    "sports-tourism": "sports_soccer",
    "electrotransport-auto": "electric_scooter",
    "used-tech": "recycling",
  };
  const s = slug ? slug.toLowerCase() : "";
  return mapping[s] || "grid_view";
};

/**
 * Multi-language DB fields are stored as `{ uk: "...", en: "..." }`. Prefers the
 * active locale, falling back to the other language rather than always defaulting
 * to Ukrainian regardless of what the visitor has selected.
 */
export const pickLocalized = (value: any, locale: string): string => {
  if (value && typeof value === "object") {
    return value[locale] || value.uk || value.en || "";
  }
  return value || "";
};

/**
 * Finds the root-to-leaf chain of categories for a given slug within a nested
 * category tree (as returned by /v1/catalog/categories), e.g. for breadcrumbs -
 * shared between the catalog page and the product detail page so both build the
 * same chain the same way.
 */
export const getCategoryPath = (
  categories: any[],
  slug: string,
  path: any[] = [],
): any[] | null => {
  for (const cat of categories) {
    const currentPath = [...path, cat];
    if (cat.slug === slug) {
      return currentPath;
    }
    if (cat.children && cat.children.length > 0) {
      const result = getCategoryPath(cat.children, slug, currentPath);
      if (result) return result;
    }
  }
  return null;
};

export const mapDbCategoriesToMenu = (
  dbCats: any[],
  locale: string = "uk",
): any[] => {
  if (!dbCats || dbCats.length === 0) return [];
  return dbCats.map((cat: any) => {
    const label = pickLocalized(cat.name, locale);
    const icon = getCategoryIcon(cat.slug);
    const slug = cat.slug;
    const children = cat.children || [];
    const columns: any[][] = [[], [], [], []];

    const itemsPerCol = Math.ceil(children.length / 4) || 1;
    children.forEach((child: any, idx: number) => {
      const colIdx = Math.floor(idx / itemsPerCol);
      if (colIdx < 4) {
        const subchildren = child.children || [];
        const hasSubchildren = subchildren.length > 0;

        let links: any[] = [];
        let showMoreSlug: string | undefined = undefined;

        if (hasSubchildren) {
          links = subchildren.slice(0, 3).map((sub: any) => ({
            name: pickLocalized(sub.name, locale),
            slug: sub.slug,
          }));
        }

        columns[colIdx].push({
          title: pickLocalized(child.name, locale),
          slug: child.slug,
          showMoreSlug: subchildren.length > 3 ? child.slug : undefined,
          links,
        });
      }
    });

    return {
      id: cat.slug || cat.id,
      slug: cat.slug,
      label,
      icon,
      columns: columns.filter((col: any[]) => col.length > 0),
    };
  });
};
