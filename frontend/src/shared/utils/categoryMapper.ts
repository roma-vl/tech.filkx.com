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
    "used-tech": "recycling"
  };
  const s = slug ? slug.toLowerCase() : "";
  return mapping[s] || "grid_view";
};

export const mapDbCategoriesToMenu = (dbCats: any[]): any[] => {
  if (!dbCats || dbCats.length === 0) return [];
  return dbCats.map((cat: any) => {
    const label = cat.name?.uk || cat.name?.en || cat.name || "";
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
            name: sub.name?.uk || sub.name?.en || sub.name || "",
            slug: sub.slug
          }));
        }
        
        columns[colIdx].push({
          title: child.name?.uk || child.name?.en || child.name || "",
          slug: child.slug,
          showMoreSlug: subchildren.length > 3 ? child.slug : undefined,
          links
        });
      }
    });
    
    return {
      id: cat.slug || cat.id,
      slug: cat.slug,
      label,
      icon,
      columns: columns.filter((col: any[]) => col.length > 0)
    };
  });
};
