import productPlaceholder from "@/assets/images/product-placeholder.svg";

interface VariantImage {
  url: string;
  isPrimary?: boolean;
  is_primary?: boolean;
}

interface VariantLike {
  dimensions?: {
    images?: VariantImage[];
  };
}

const imagesOf = (variant: VariantLike | null | undefined): VariantImage[] | null => {
  const images = variant?.dimensions?.images;
  return images && images.length > 0 ? images : null;
};

/**
 * Resolves a single display image for a product variant, preferring the
 * variant's own primary photo but falling back to a sibling variant's photo
 * before the placeholder - a product's photoshoot is often only attached to
 * one variant (see DownloadProductImages.php), and different configs of the
 * same physical device share the same photos anyway.
 */
export function resolveProductImage(
  variant: VariantLike | null | undefined,
  siblingVariants: VariantLike[] = [],
): string {
  const own = imagesOf(variant);
  const images = own || siblingVariants.map(imagesOf).find((v) => v) || null;
  if (!images) return productPlaceholder;
  const primary = images.find((img) => img.isPrimary || img.is_primary) || images[0];
  return primary.url || productPlaceholder;
}

/**
 * Resolves the full gallery for a variant, with the same sibling-variant
 * fallback as resolveProductImage. Always returns at least one entry.
 */
export function resolveGalleryImages(
  variant: VariantLike | null | undefined,
  siblingVariants: VariantLike[] = [],
): Array<{ url: string; isPrimary: boolean }> {
  const own = imagesOf(variant);
  const images = own || siblingVariants.map(imagesOf).find((v) => v) || null;
  if (!images) return [{ url: productPlaceholder, isPrimary: true }];
  return images
    .filter((img) => img.url)
    .map((img) => ({ url: img.url, isPrimary: Boolean(img.isPrimary || img.is_primary) }));
}
