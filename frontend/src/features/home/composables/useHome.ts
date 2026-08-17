import { ref, computed, onMounted, onServerPrefetch } from "vue";
import { useI18n } from "vue-i18n";
import { productApi } from "@/shared/services/api/productApi";
import { mapHomeProduct } from "@/entities/product/lib/mapHomeProduct";

export function useHome() {
  const { locale } = useI18n();

  const banners = ref<any[]>([]);
  const categories = ref<any[]>([]);
  const popularCategories = ref<any[]>([]);
  const isRecommendedPersonalized = ref(false);
  const loading = ref(true);

  // Kept as raw API data so flashDeals/recommended can re-map into the active
  // locale immediately on language switch, without a refetch.
  const rawFlashDeals = ref<any[]>([]);
  const rawRecommended = ref<any[]>([]);

  const flashDeals = computed(() =>
    rawFlashDeals.value.map((p) => mapHomeProduct(p, locale.value)).filter(Boolean),
  );
  const recommended = computed(() =>
    rawRecommended.value.map((p) => mapHomeProduct(p, locale.value)).filter(Boolean),
  );

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
        rawFlashDeals.value = data.data.flashDeals || data.data.flash_deals || [];
        rawRecommended.value = data.data.recommended || [];
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

  // Prerendering has no DOM, so onMounted never runs — fetch here so the
  // static build captures real homepage content.
  onServerPrefetch(loadHomeData);

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
