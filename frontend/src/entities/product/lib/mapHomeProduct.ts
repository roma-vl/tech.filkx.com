import productPlaceholder from "@/assets/images/product-placeholder.svg";
import { i18n } from "@/i18n";

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

const pickLocalized = (value: any, locale: string): string => {
  if (value && typeof value === "object") {
    return value[locale] || value.uk || value.en || "";
  }
  return value || "";
};

/**
 * Maps a raw API product (as returned by /v1/catalog/home and /v1/catalog/products)
 * into the shape used by the homepage product cards. Only surfaces data that actually
 * exists on the product — no invented discounts, ratings or scarcity indicators.
 *
 * `locale` defaults to the app's current locale so callers that don't map on every
 * locale switch (e.g. data fetched once and cached) still show the right language
 * as of when they were mapped.
 */
export function mapHomeProduct(p: any, locale: string = i18n.global.locale.value): HomeProduct | null {
  try {
    const { t } = i18n.global;
    const name = pickLocalized(p.name, locale);
    const category =
      pickLocalized(p.categories?.[0]?.name, locale) || t("home.productDefaults.category");

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
      const label = pickLocalized(av.attribute?.name, locale);
      const attrValObj = av.attributeValue || av.attribute_value;
      const val =
        pickLocalized(attrValObj?.value, locale) || av.customValue || av.custom_value || "";
      if (label && val) {
        features.push(`${label}: ${val}`);
      }
    });

    const specs = {
      brand: p.brand?.name || "",
      warranty: t("home.productDefaults.warranty"),
      sku: firstVariant.sku || "",
      availability: t("home.productDefaults.availability"),
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
      description: pickLocalized(p.description, locale),
      specs,
      features,
    };
  } catch (err) {
    console.error("Error mapping product:", p, err);
    return null;
  }
}
