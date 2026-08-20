import { resolveProductImage } from "@/entities/product/lib/resolveProductImage";

/**
 * Maps a raw API product into the shape ProductCard.vue expects. Shared by
 * the catalog grid and any other place that renders products through
 * ProductCard (e.g. the product detail page's related-products section) so
 * the mapping - and its no-fake-data rules - has one authoritative place.
 */
export function mapCatalogProduct(apiProduct: any) {
  if (!apiProduct) return null;
  const mainVariant =
    apiProduct.variants && apiProduct.variants[0]
      ? apiProduct.variants[0]
      : null;
  const price = mainVariant ? parseFloat(mainVariant.price) : 0;
  const oldPrice =
    mainVariant && mainVariant.old_price
      ? parseFloat(mainVariant.old_price)
      : mainVariant && mainVariant.oldPrice
        ? parseFloat(mainVariant.oldPrice)
        : null;
  const totalStock = mainVariant
    ? (mainVariant.stocks || []).reduce(
        (acc: number, s: any) =>
          acc + (parseInt(s.quantity) - parseInt(s.reserved)),
        0,
      )
    : 0;

  const image = resolveProductImage(mainVariant, apiProduct.variants);

  const name =
    typeof apiProduct.name === "object"
      ? apiProduct.name.uk || apiProduct.name.en
      : apiProduct.name;
  const description =
    typeof apiProduct.description === "object"
      ? apiProduct.description.uk || apiProduct.description.en
      : apiProduct.description;

  const getAttrValue = (code: string) => {
    const checkList: any[] = [];
    if (mainVariant) {
      if (mainVariant.attribute_values)
        checkList.push(...mainVariant.attribute_values);
      if (mainVariant.attributeValues)
        checkList.push(...mainVariant.attributeValues);
    }
    if (apiProduct) {
      if (apiProduct.attribute_values)
        checkList.push(...apiProduct.attribute_values);
      if (apiProduct.attributeValues)
        checkList.push(...apiProduct.attributeValues);
    }

    const match = checkList.find(
      (av) => av.attribute && av.attribute.code === code,
    );
    if (match) {
      const valObj = match.attribute_value || match.attributeValue;
      if (valObj && valObj.value) {
        if (typeof valObj.value === "object") {
          return valObj.value.uk || valObj.value.en || "";
        }
        return valObj.value;
      }
      return match.custom_value || match.customValue || "";
    }
    return "";
  };

  return {
    id: apiProduct.id,
    slug: apiProduct.slug,
    name: name,
    brand: apiProduct.brand ? apiProduct.brand.name : null,
    ram: getAttrValue("ram"),
    category:
      apiProduct.categories && apiProduct.categories[0]
        ? apiProduct.categories[0].name.uk || apiProduct.categories[0].name.en
        : null,
    price: price,
    oldPrice: oldPrice,
    rating: apiProduct.approvedReviewsAvgRating != null ? parseFloat(apiProduct.approvedReviewsAvgRating) : 0,
    reviews: apiProduct.approvedReviewsCount != null ? Number(apiProduct.approvedReviewsCount) : 0,
    badge: oldPrice ? `-${Math.round((1 - price / oldPrice) * 100)}%` : null,
    badgeClass: oldPrice ? "bg-rose-600" : "",
    inStock: totalStock > 0,
    image: image,
    description: description,
    specs: {
      processor: getAttrValue("processor"),
      screen: getAttrValue("screen_size"),
      storage: getAttrValue("storage"),
      os: getAttrValue("os"),
      weight: mainVariant && mainVariant.weight ? `${mainVariant.weight} кг` : "",
    },
  };
}
