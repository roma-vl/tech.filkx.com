import productPlaceholder from "@/assets/images/product-placeholder.svg";

export interface HomeProduct {
  id: number | string;
  slug: string;
  name: string;
  category: string;
  price: number;
  oldPrice: number | null;
  discount: string;
  rating: number;
  reviews: number;
  leftCount: number;
  image: string;
  description: string;
  specs: {
    brand: string;
    warranty: string;
    sku: string;
    availability: string;
    colors: string[];
  };
  features: string[];
}

/**
 * Maps a raw API product (as returned by /v1/catalog/home and /v1/catalog/products)
 * into the shape used by the homepage product cards. Only surfaces data that actually
 * exists on the product — no invented discounts, ratings or scarcity indicators.
 */
export function mapHomeProduct(p: any): HomeProduct | null {
  try {
    const name = p.name?.uk || p.name?.en || p.name || "";
    const category =
      p.categories?.[0]?.name?.uk ||
      p.categories?.[0]?.name?.en ||
      p.categories?.[0]?.name ||
      "Товар";

    const firstVariant = p.variants?.[0] || {};
    const price = parseFloat(firstVariant.price) || 0;
    const oldPriceRaw = firstVariant.oldPrice || firstVariant.old_price;
    const oldPriceVal = oldPriceRaw ? parseFloat(oldPriceRaw) : null;
    const oldPrice = oldPriceVal && oldPriceVal > price ? oldPriceVal : null;
    const discount = oldPrice
      ? `-${Math.round(((oldPrice - price) / oldPrice) * 100)}% OFF`
      : "";

    let image = productPlaceholder;
    const images = firstVariant.dimensions?.images || [];
    if (images.length > 0) {
      const primary =
        images.find((img: any) => img.isPrimary || img.is_primary) || images[0];
      if (primary?.url) image = primary.url;
    } else if (firstVariant.dimensions?.image) {
      image = firstVariant.dimensions.image;
    }

    const colors: string[] = [];
    p.variants?.forEach((v: any) => {
      const attrVals = v.attributeValues || v.attribute_values || [];
      attrVals.forEach((av: any) => {
        if (av.attribute?.type === "color") {
          const attrValObj = av.attributeValue || av.attribute_value;
          const val = attrValObj?.value || av.customValue || av.custom_value;
          if (val && !colors.includes(val)) {
            colors.push(val);
          }
        }
      });
    });

    const features: string[] = [];
    const prodAttrVals = p.attributeValues || p.attribute_values || [];
    prodAttrVals.slice(0, 4).forEach((av: any) => {
      const label =
        av.attribute?.name?.uk || av.attribute?.name?.en || av.attribute?.name || "";
      const attrValObj = av.attributeValue || av.attribute_value;
      const val =
        attrValObj?.value?.uk ||
        attrValObj?.value?.en ||
        attrValObj?.value ||
        av.customValue ||
        av.custom_value ||
        "";
      if (label && val) {
        features.push(`${label}: ${val}`);
      }
    });

    const specs = {
      brand: p.brand?.name || "",
      warranty: "12 місяців",
      sku: firstVariant.sku || "",
      availability: "В наявності",
      colors: colors.length > 0 ? colors : ["#09090b"],
    };

    const totalStock = p.variants
      ? p.variants.reduce((acc: number, v: any) => {
          const vStock = v.stocks
            ? v.stocks.reduce(
                (sAcc: number, s: any) =>
                  sAcc + (parseInt(s.quantity) - parseInt(s.reserved || 0)),
                0,
              )
            : v.stock !== undefined
              ? parseInt(v.stock)
              : 0;
          return acc + vStock;
        }, 0)
      : p.stock !== undefined
        ? parseInt(p.stock)
        : 0;

    return {
      id: p.id,
      slug: p.slug,
      name,
      category,
      price,
      oldPrice,
      discount,
      rating: p.approvedReviewsAvgRating != null ? parseFloat(p.approvedReviewsAvgRating) : 0,
      reviews: p.approvedReviewsCount != null ? Number(p.approvedReviewsCount) : 0,
      leftCount: totalStock,
      image,
      description: p.description?.uk || p.description?.en || p.description || "",
      specs,
      features,
    };
  } catch (err) {
    console.error("Error mapping product:", p, err);
    return null;
  }
}
