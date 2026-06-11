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
    
    if (slug === "smartphones" || slug === "phones") {
      const appleCat = children.find((c: any) => c.slug.includes("apple"));
      const samsungCat = children.find((c: any) => c.slug.includes("samsung"));
      const xiaomiCat = children.find((c: any) => c.slug.includes("xiaomi"));
      const motorolaCat = children.find((c: any) => c.slug.includes("motorola"));
      const refurbishedCat = children.find((c: any) => c.slug.includes("refurbished"));
      
      columns[0].push({
        title: "Останні новинки",
        links: [
          { name: "iPhone 15 Pro Max", slug: appleCat?.slug || "smartphones", badge: "new" },
          { name: "Galaxy S24 Ultra", slug: samsungCat?.slug || "smartphones", badge: "new" },
          { name: "Redmi Note 13 Pro", slug: xiaomiCat?.slug || "smartphones", badge: "new" },
          { name: "Motorola Edge 50", slug: motorolaCat?.slug || "smartphones", badge: "new" }
        ]
      });
      columns[0].push({
        title: "Хіти",
        links: [
          { name: "iPhone 15", slug: appleCat?.slug || "smartphones", badge: "hit" },
          { name: "Galaxy A55", slug: samsungCat?.slug || "smartphones", badge: "hit" },
          { name: "Redmi 13C", slug: xiaomiCat?.slug || "smartphones", badge: "hit" }
        ]
      });
      if (refurbishedCat) {
        columns[0].push({
          title: "Б/В та уцінка",
          links: [
            { name: "Уцінені смартфони", slug: refurbishedCat.slug }
          ]
        });
      }
      
      if (appleCat) {
        columns[1].push({
          title: appleCat.name?.uk || appleCat.name || "Apple iPhone",
          showMoreSlug: appleCat.slug,
          links: [
            { name: "iPhone 15 Pro Max", slug: appleCat.slug },
            { name: "iPhone 15 Pro", slug: appleCat.slug },
            { name: "iPhone 15 Plus", slug: appleCat.slug },
            { name: "iPhone 15", slug: appleCat.slug },
            { name: "iPhone 14 Pro", slug: appleCat.slug },
            { name: "iPhone 13", slug: appleCat.slug }
          ]
        });
      }
      if (samsungCat) {
        columns[1].push({
          title: samsungCat.name?.uk || samsungCat.name || "Samsung Galaxy",
          showMoreSlug: samsungCat.slug,
          links: [
            { name: "Galaxy S24 Ultra", slug: samsungCat.slug },
            { name: "Galaxy S24+", slug: samsungCat.slug },
            { name: "Galaxy S24", slug: samsungCat.slug },
            { name: "Galaxy S23 Ultra", slug: samsungCat.slug },
            { name: "Galaxy A55", slug: samsungCat.slug },
            { name: "Galaxy A35", slug: samsungCat.slug }
          ]
        });
      }
      
      if (xiaomiCat) {
        columns[2].push({
          title: xiaomiCat.name?.uk || xiaomiCat.name || "Xiaomi / Redmi",
          showMoreSlug: xiaomiCat.slug,
          links: [
            { name: "Xiaomi 14 Ultra", slug: xiaomiCat.slug },
            { name: "Xiaomi 14", slug: xiaomiCat.slug },
            { name: "Redmi Note 13 Pro+", slug: xiaomiCat.slug },
            { name: "Redmi Note 13", slug: xiaomiCat.slug },
            { name: "Redmi 13C", slug: xiaomiCat.slug }
          ]
        });
      }
      if (motorolaCat) {
        columns[2].push({
          title: motorolaCat.name?.uk || motorolaCat.name || "Motorola",
          showMoreSlug: motorolaCat.slug,
          links: [
            { name: "Edge 50 Ultra", slug: motorolaCat.slug },
            { name: "Edge 50 Pro", slug: motorolaCat.slug },
            { name: "Edge 40 Neo", slug: motorolaCat.slug },
            { name: "Moto G84", slug: motorolaCat.slug },
            { name: "Moto G54", slug: motorolaCat.slug }
          ]
        });
      }
      
      const otherBrands = children.filter((c: any) => 
        !["apple", "samsung", "xiaomi", "motorola", "refurbished"].some(term => c.slug.includes(term))
      );
      if (otherBrands.length > 0) {
        columns[3].push({
          title: "Інші бренди та моделі",
          links: otherBrands.map((b: any) => ({
            name: b.name?.uk || b.name || "",
            slug: b.slug
          }))
        });
      }
    } else if (slug === "tablets-laptops-pc" || slug === "laptops") {
      const laptopCat = children.find((c: any) => c.slug.includes("laptop"));
      const tabletCat = children.find((c: any) => c.slug.includes("tablet"));
      const monitorCat = children.find((c: any) => c.slug.includes("monitor"));
      const desktopCat = children.find((c: any) => c.slug.includes("desktop") || c.slug.includes("pc"));
      
      if (laptopCat) {
        columns[0].push({
          title: laptopCat.name?.uk || laptopCat.name || "Ноутбуки",
          showMoreSlug: laptopCat.slug,
          links: [
            { name: "MacBook Air", slug: laptopCat.slug, badge: "hit" },
            { name: "MacBook Pro", slug: laptopCat.slug, badge: "new" },
            { name: "Ігрові ноутбуки", slug: laptopCat.slug }
          ]
        });
      }
      if (tabletCat) {
        columns[1].push({
          title: tabletCat.name?.uk || tabletCat.name || "Планшети",
          showMoreSlug: tabletCat.slug,
          links: [
            { name: "iPad Pro", slug: tabletCat.slug, badge: "new" },
            { name: "iPad Air", slug: tabletCat.slug },
            { name: "Galaxy Tab", slug: tabletCat.slug }
          ]
        });
      }
      if (monitorCat) {
        columns[2].push({
          title: monitorCat.name?.uk || monitorCat.name || "Монітори",
          showMoreSlug: monitorCat.slug,
          links: [
            { name: "Ігрові монітори", slug: monitorCat.slug },
            { name: "Монітори 4K", slug: monitorCat.slug }
          ]
        });
      }
      if (desktopCat) {
        columns[2].push({
          title: desktopCat.name?.uk || desktopCat.name || "Комп'ютери",
          showMoreSlug: desktopCat.slug,
          links: [
            { name: "Ігрові ПК", slug: desktopCat.slug },
            { name: "Офісні ПК", slug: desktopCat.slug }
          ]
        });
      }
      const remaining = children.filter((c: any) =>
        ![laptopCat, tabletCat, monitorCat, desktopCat].some(x => x && x.slug === c.slug)
      );
      if (remaining.length > 0) {
        columns[3].push({
          title: "Комплектуючі та аксесуари",
          links: remaining.slice(0, 10).map((c: any) => ({
            name: c.name?.uk || c.name || "",
            slug: c.slug
          }))
        });
      }
    } else {
      const itemsPerCol = Math.ceil(children.length / 4) || 1;
      children.forEach((child: any, idx: number) => {
        const colIdx = Math.floor(idx / itemsPerCol);
        if (colIdx < 4) {
          columns[colIdx].push({
            title: child.name?.uk || child.name || "",
            showMoreSlug: child.slug,
            links: [
              { name: `Всі товари ${child.name?.uk || child.name || ""}`, slug: child.slug }
            ]
          });
        }
      });
    }
    
    return {
      id: cat.slug || cat.id,
      slug: cat.slug,
      label,
      icon,
      columns: columns.filter((col: any[]) => col.length > 0)
    };
  });
};
