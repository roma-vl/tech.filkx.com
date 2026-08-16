import { ref, onMounted } from "vue";
import { productApi } from "@/shared/services/api/productApi";
import { mapHomeProduct } from "@/entities/product/lib/mapHomeProduct";

export function useHome() {
  const banners = ref<any[]>([]);
  const categories = ref<any[]>([]);
  const popularCategories = ref<any[]>([]);
  const flashDeals = ref<any[]>([]);
  const recommended = ref<any[]>([]);
  const isRecommendedPersonalized = ref(false);
  const loading = ref(true);

  const loadHomeData = async () => {
    try {
      let wishlistIds = "";
      let viewedIds = "";
      if (typeof window !== "undefined") {
        const wishlist = JSON.parse(
          localStorage.getItem("electro_wishlist") || "[]",
        );
        wishlistIds = wishlist.map((p: any) => p.id).join(",");
        const viewed = JSON.parse(
          localStorage.getItem("electro_viewed") || "[]",
        );
        viewedIds = viewed.join(",");
      }

      isRecommendedPersonalized.value = Boolean(wishlistIds || viewedIds);

      const [homeRes, catsRes] = await Promise.all([
        productApi.catalogGetHome({
          wishlist_ids: wishlistIds,
          viewed_ids: viewedIds,
        }),
        productApi.catalogGetCategories()
      ]);

      const data = homeRes.data;
      if (data && (data.success || data.status === "success")) {
        banners.value = data.data.banners || [];
        flashDeals.value = (data.data.flashDeals || data.data.flash_deals || [])
          .map(mapHomeProduct)
          .filter(Boolean);
        recommended.value = (data.data.recommended || [])
          .map(mapHomeProduct)
          .filter(Boolean);
        popularCategories.value = data.data.categories || [];
      }

      const catsData = catsRes.data;
      if (catsData && (catsData.success || catsData.status === "success")) {
        categories.value = catsData.data || [];
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
    banners,
    categories,
    popularCategories,
    flashDeals,
    recommended,
    isRecommendedPersonalized,
    loading,
    loadHomeData,
  };
}
