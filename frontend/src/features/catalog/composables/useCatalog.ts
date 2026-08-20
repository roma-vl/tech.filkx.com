import { ref, computed, watch, onMounted, onServerPrefetch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { productApi } from "@/shared/services/api/productApi";
import { mapCatalogProduct } from "@/entities/product/lib/mapCatalogProduct";

export function useCatalog() {
  const route = useRoute();
  const router = useRouter();

  const viewMode = ref("grid");
  // How many product cards per row from the lg breakpoint up - 4 is the default,
  // 5 is the denser option. Smaller breakpoints below lg still scale down to 1/2
  // columns responsively regardless of this setting.
  const gridDensity = ref<4 | 5>(4);
  const sortBy = ref("popularity");
  const initialPriceMin = ref(0);
  const initialPriceMax = ref(200000);
  const priceMin = ref(0);
  const priceMax = ref(200000);
  const selectedBrands = ref<string[]>(
    route.query.brand ? (route.query.brand as string).split(",") : [],
  );
  const selectedAttrs = ref<Record<string, string>>({});
  const selectedRating = ref("");
  const onlyDiscounts = ref(false);
  const onlyInStock = ref(false);

  const isMobileFilterOpen = ref(false);

  const isLoading = ref(false);
  const rawProducts = ref<any[]>([]);
  const categoriesList = ref<any[]>([]);
  const dbBrands = ref<any[]>([]);
  const dynamicAttributes = ref<any[]>([]);
  const pagination = ref({
    page: 1,
    lastPage: 1,
    total: 0,
  });

  // The category is addressed by its own route (`category/:slug`) rather than
  // a `?category=` query param on the flat `/catalog` route - that route now
  // exists only for free-text search results and a generic "all products"
  // browse view, never for category browsing.
  const categorySlug = computed(() =>
    route.name === "category" ? (route.params.slug as string) : "",
  );

  // Brand counts computed property dynamically mapping from DB
  const brands = computed(() => {
    return dbBrands.value.map((b) => {
      return {
        name: b.name,
        slug: b.slug,
        count: b.products_count || b.productsCount || 0,
      };
    });
  });

  const mapProduct = mapCatalogProduct;

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat("uk-UA", {
      style: "currency",
      currency: "UAH",
      maximumFractionDigits: 0,
    }).format(price);
  };

  const fetchCategories = async () => {
    try {
      const response = await productApi.catalogGetCategories();
      if (response.data && response.data.status === "success") {
        categoriesList.value = response.data.data || [];
      }
    } catch (error) {
      console.error("Failed to fetch categories:", error);
    }
  };

  const fetchBrands = async () => {
    try {
      const response = await productApi.catalogGetBrands();
      if (response.data && response.data.status === "success") {
        dbBrands.value = response.data.data || [];
      }
    } catch (error) {
      console.error("Failed to fetch brands:", error);
    }
  };

  const fetchFilterSchema = async () => {
    try {
      const response = await productApi.catalogGetFiltersSchema();
      if (response.data && response.data.status === "success") {
        const data = response.data.data;
        dynamicAttributes.value = data.attributes || [];
        if (priceMin.value === 0 && priceMax.value === 200000) {
          priceMin.value = data.price.min || 0;
          priceMax.value = data.price.max || 200000;
          initialPriceMin.value = data.price.min || 0;
          initialPriceMax.value = data.price.max || 200000;
        }
      }
    } catch (error) {
      console.error("Failed to fetch filter schema:", error);
    }
  };

  const fetchProducts = async () => {
    isLoading.value = true;
    try {
      const params: Record<string, any> = {
        page: pagination.value.page,
        sort_by: sortBy.value,
        price_from:
          priceMin.value > initialPriceMin.value ? priceMin.value : undefined,
        price_to:
          priceMax.value < initialPriceMax.value ? priceMax.value : undefined,
      };

      if (selectedBrands.value.length > 0) {
        params.brand = selectedBrands.value.join(",");
      }

      if (categorySlug.value) {
        params.category = categorySlug.value;
      }

      if (route.query.search) {
        params.search = route.query.search;
      }

      if (onlyDiscounts.value) {
        params.discounts = 1;
      }

      if (onlyInStock.value) {
        params.in_stock = 1;
      }

      // Add EAV attributes to query parameters in camelCase format
      Object.keys(selectedAttrs.value).forEach((code) => {
        const val = selectedAttrs.value[code];
        if (val) {
          params[`attrs[${code}]`] = val;
        }
      });

      const response = await productApi.catalogGetProducts(params);
      if (response.data && response.data.status === "success") {
        const apiData = response.data.data;
        rawProducts.value = (apiData.data || [])
          .map(mapProduct)
          .filter(Boolean);
        pagination.value = {
          page: apiData.currentPage || 1,
          lastPage: apiData.lastPage || 1,
          total: apiData.total || 0,
        };
      }
    } catch (error) {
      console.error("Failed to fetch products:", error);
    } finally {
      isLoading.value = false;
    }
  };

  const selectCategory = (slug: string) => {
    if (slug) {
      router.push({ name: "category", params: { slug } });
    } else {
      router.push({ name: "catalog" });
    }
  };

  const changePage = (page: number) => {
    if (page >= 1 && page <= pagination.value.lastPage) {
      pagination.value.page = page;
      router.push({
        name: route.name as string,
        params: route.params,
        query: {
          ...route.query,
          page: page,
        },
      });
    }
  };

  const filteredProducts = computed(() => {
    return rawProducts.value.filter((product) => {
      return (
        !selectedRating.value ||
        product.rating >= parseFloat(selectedRating.value)
      );
    });
  });

  const activeFilters = computed(() => {
    const filters: any[] = [];

    selectedBrands.value.forEach((brandSlug) => {
      const brandObj = dbBrands.value.find((b) => b.slug === brandSlug);
      filters.push({
        type: "brand",
        label: brandObj ? brandObj.name : brandSlug,
        value: brandSlug,
      });
    });

    // Dynamic attributes
    Object.keys(selectedAttrs.value).forEach((code) => {
      const val = selectedAttrs.value[code];
      if (val) {
        const attr = dynamicAttributes.value.find((a) => a.code === code);
        const attrName = attr
          ? attr.name.uk || attr.name.en || attr.name
          : code;
        filters.push({
          type: "attribute",
          code: code,
          label: `${attrName}: ${val}`,
          value: val,
        });
      }
    });

    if (
      priceMin.value > initialPriceMin.value ||
      priceMax.value < initialPriceMax.value
    ) {
      filters.push({
        type: "price",
        label: `${formatPrice(priceMin.value)} - ${formatPrice(priceMax.value)}`,
      });
    }

    if (selectedRating.value) {
      filters.push({
        type: "rating",
        label: `Рейтинг: ${selectedRating.value}+ ★`,
        value: selectedRating.value,
      });
    }

    if (onlyDiscounts.value) {
      filters.push({
        type: "discounts",
        label: "Тільки зі знижкою",
        value: true,
      });
    }

    if (onlyInStock.value) {
      filters.push({ type: "stock", label: "Тільки в наявності", value: true });
    }

    return filters;
  });

  const removeFilter = (filter: any) => {
    if (filter.type === "brand") {
      selectedBrands.value = selectedBrands.value.filter(
        (brand) => brand !== filter.value,
      );
    }
    if (filter.type === "attribute") {
      const current = { ...selectedAttrs.value };
      delete current[filter.code];
      selectedAttrs.value = current;
    }
    if (filter.type === "price") {
      priceMin.value = initialPriceMin.value;
      priceMax.value = initialPriceMax.value;
    }
    if (filter.type === "rating") {
      selectedRating.value = "";
    }
    if (filter.type === "discounts") {
      onlyDiscounts.value = false;
    }
    if (filter.type === "stock") {
      onlyInStock.value = false;
    }
  };

  const clearFilters = () => {
    selectedBrands.value = [];
    selectedAttrs.value = {};
    priceMin.value = initialPriceMin.value;
    priceMax.value = initialPriceMax.value;
    selectedRating.value = "";
    onlyDiscounts.value = false;
    onlyInStock.value = false;
  };

  const getCategoryPath = (
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

  const currentCategoryPath = computed(() => {
    if (!categorySlug.value) return [];
    return getCategoryPath(categoriesList.value, categorySlug.value) || [];
  });

  const currentCategoryName = computed(() => {
    if (!categorySlug.value) {
      return route.query.search ? `Пошук: ${route.query.search}` : "Всі товари";
    }
    const path = currentCategoryPath.value;
    if (path.length > 0) {
      const last = path[path.length - 1];
      return last.name?.uk || last.name?.en || last.name;
    }
    return "Каталог";
  });

  watch(
    () => [categorySlug.value, route.query.search, route.query.page],
    () => {
      pagination.value.page = parseInt(route.query.page as string) || 1;
      fetchProducts();
    },
  );

  watch(
    () => [
      sortBy.value,
      priceMin.value,
      priceMax.value,
      selectedBrands.value,
      selectedAttrs.value,
      onlyDiscounts.value,
      onlyInStock.value,
    ],
    () => {
      pagination.value.page = 1;
      fetchProducts();
    },
    { deep: true },
  );

  onMounted(() => {
    window.scrollTo(0, 0);
    fetchCategories();
    fetchBrands();
    fetchFilterSchema();
    pagination.value.page = parseInt(route.query.page as string) || 1;
    fetchProducts();
  });

  // Prerendering runs no onMounted hooks (there is no DOM) — mirror the same
  // fetches here so the static build captures real catalog content.
  //
  // fetchFilterSchema() is deliberately left out: it writes the fetched
  // price range into priceMin/priceMax, which the watch() above reacts to
  // by firing its own untracked fetchProducts() call — a race that could
  // flip isLoading back to true after this hook's own fetchProducts() has
  // already resolved and Vue has moved on to serializing the page. The
  // filter sidebar isn't SEO content, so it's fine for it to render with
  // its client-side defaults until onMounted runs in the browser.
  onServerPrefetch(async () => {
    pagination.value.page = parseInt(route.query.page as string) || 1;
    await Promise.all([fetchCategories(), fetchBrands(), fetchProducts()]);
  });

  return {
    route,
    router,
    viewMode,
    gridDensity,
    sortBy,
    priceMin,
    priceMax,
    selectedBrands,
    selectedAttrs,
    selectedRating,
    onlyDiscounts,
    onlyInStock,
    isMobileFilterOpen,
    isLoading,
    rawProducts,
    categorySlug,
    categoriesList,
    brands,
    dynamicAttributes,
    pagination,
    formatPrice,
    selectCategory,
    changePage,
    filteredProducts,
    activeFilters,
    removeFilter,
    clearFilters,
    currentCategoryName,
    currentCategoryPath,
  };
}
