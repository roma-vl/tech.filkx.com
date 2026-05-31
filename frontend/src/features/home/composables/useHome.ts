import { ref, onMounted } from "vue";
import { productApi } from "@/shared/services/api/productApi";

export function useHome() {
  const categories = ref<any[]>([]);
  const flashDeals = ref<any[]>([]);
  const recommended = ref<any[]>([]);
  const loading = ref(true);

  const mapProduct = (p: any) => {
    try {
      const name = p.name?.uk || p.name?.en || p.name || "";
      const category =
        p.categories?.[0]?.name?.uk ||
        p.categories?.[0]?.name?.en ||
        p.categories?.[0]?.name ||
        "Товар";

      const firstVariant = p.variants?.[0] || {};
      const price = parseFloat(firstVariant.price) || 0;
      const oldPriceVal = firstVariant.oldPrice || firstVariant.old_price;
      const oldPrice = oldPriceVal ? parseFloat(oldPriceVal) : null;

      let discount = "";
      if (oldPrice && oldPrice > price) {
        const pct = Math.round(((oldPrice - price) / oldPrice) * 100);
        discount = `-${pct}% OFF`;
      }

      let image =
        "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&fit=crop";
      const images = firstVariant.dimensions?.images || [];
      if (images.length > 0) {
        const primary =
          images.find((img: any) => img.isPrimary || img.is_primary) || images[0];
        if (primary && primary.url) {
          image = primary.url;
        }
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
          av.attribute?.name?.uk ||
          av.attribute?.name?.en ||
          av.attribute?.name ||
          "";
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
        brand: p.brand?.name || "Generic",
        warranty: "12 місяців",
        sku: firstVariant.sku || `FT-PROD-${p.id}`,
        availability: "В наявності",
        colors: colors.length > 0 ? colors : ["#09090b"],
      };

      const soldPercent = Math.min(95, Math.max(10, (p.id * 13) % 85));
      const leftCount = Math.max(2, (p.id * 3) % 15);

      return {
        id: p.id,
        slug: p.slug,
        name,
        category,
        price,
        oldPrice,
        discount,
        rating: 4.5,
        reviews: (p.id * 13) % 150,
        soldPercent,
        leftCount,
        image,
        description:
          p.description?.uk || p.description?.en || p.description || "",
        specs,
        features:
          features.length > 0
            ? features
            : [
                "Висока якість та надійність матеріалів",
                "Офіційна гарантія від виробника",
                "Екологічні та безпечні компоненти",
              ],
      };
    } catch (err) {
      console.error("Error mapping product:", p, err);
      return null;
    }
  };

  const loadHomeData = async () => {
    try {
      let wishlistIds = "";
      let viewedIds = "";
      if (typeof window !== "undefined") {
        const wishlist = JSON.parse(
          localStorage.getItem("electro_wishlist") || "[]"
        );
        wishlistIds = wishlist.map((p: any) => p.id).join(",");
        const viewed = JSON.parse(localStorage.getItem("electro_viewed") || "[]");
        viewedIds = viewed.join(",");
      }

      const response = await productApi.catalogGetHome({
        wishlist_ids: wishlistIds,
        viewed_ids: viewedIds,
      });

      const data = response.data;
      if (data && (data.success || data.status === "success")) {
        categories.value = data.data.categories || [];
        flashDeals.value = (data.data.flashDeals || data.data.flash_deals || [])
          .map(mapProduct)
          .filter(Boolean);
        recommended.value = (data.data.recommended || [])
          .map(mapProduct)
          .filter(Boolean);
      }
    } catch (error) {
      console.error("Failed to load homepage data:", error);
    } finally {
      loading.value = false;
    }
  };

  onMounted(() => {
    loadHomeData();
  });

  return {
    categories,
    flashDeals,
    recommended,
    loading,
    loadHomeData,
  };
}
