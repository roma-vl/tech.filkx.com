import { ref, computed, watch, onMounted, onServerPrefetch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { productApi } from "@/shared/services/api/productApi";
import { mapCatalogProduct } from "@/entities/product/lib/mapCatalogProduct";
import { getCategoryPath, pickLocalized } from "@/shared/utils/categoryMapper";

export function useCatalog() {
  const route = useRoute();
  const router = useRouter();
  const { locale } = useI18n();

  // A snapshot of the URL filters/were navigated in with, taken once before
  // anything below starts writing to route.query - restores a shared/refreshed
  // link's filters (including price, which needs the category's fetched bounds
  // first - see the fetchFilterSchema().then() in onMounted) without fighting
  // this composable's own later query updates.
  const initialQuery = { ...route.query };
  const initialPriceFrom = initialQuery.price_from
    ? Number(initialQuery.price_from)
    : null;
  const initialPriceTo = initialQuery.price_to
    ? Number(initialQuery.price_to)
    : null;

  const viewMode = ref("grid");
  // How many product cards per row from the lg breakpoint up - 4 is the default,
  // 5 is the denser option. Smaller breakpoints below lg still scale down to 1/2
  // columns responsively regardless of this setting.
  const gridDensity = ref<4 | 5>(4);
  const sortBy = ref((initialQuery.sort_by as string) || "popularity");
  const initialPriceMin = ref(0);
  const initialPriceMax = ref(200000);
  const priceMin = ref(0);
  const priceMax = ref(200000);
  const selectedBrands = ref<string[]>(
    initialQuery.brand ? (initialQuery.brand as string).split(",") : [],
  );
  const selectedAttrs = ref<Record<string, string[]>>(
    Object.fromEntries(
      Object.entries(initialQuery)
        .filter(([key]) => /^attrs\[.+\]$/.test(key))
        .map(([key, value]) => [
          key.slice(6, -1),
          (value as string).split(","),
        ]),
    ),
  );
  const selectedRating = ref((initialQuery.rating as string) || "");
  const onlyDiscounts = ref(
    initialQuery.discounts === "1" || initialQuery.discounts === "true",
  );
  const onlyInStock = ref(
    initialQuery.in_stock === "1" || initialQuery.in_stock === "true",
  );

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
      const params = categorySlug.value
        ? { category: categorySlug.value }
        : undefined;
      const response = await productApi.catalogGetBrands(params);
      if (response.data && response.data.status === "success") {
        dbBrands.value = response.data.data || [];
      }
    } catch (error) {
      console.error("Failed to fetch brands:", error);
    }
  };

  const fetchFilterSchema = async () => {
    try {
      const params = categorySlug.value
        ? { category: categorySlug.value }
        : undefined;
      const response = await productApi.catalogGetFiltersSchema(params);
      if (response.data && response.data.status === "success") {
        const data = response.data.data;
        dynamicAttributes.value = data.attributes || [];
        priceMin.value = data.price.min || 0;
        priceMax.value = data.price.max || 200000;
        initialPriceMin.value = data.price.min || 0;
        initialPriceMax.value = data.price.max || 200000;
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

      // Add EAV attributes to query parameters, comma-joining multi-selected values -
      // matches ListProductsAction's `explode(',', ...)` parsing on the backend.
      Object.keys(selectedAttrs.value).forEach((code) => {
        const values = selectedAttrs.value[code];
        if (values && values.length > 0) {
          params[`attrs[${code}]`] = values.join(",");
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

  // Mirrors the current filter selection into the URL query so a reload,
  // browser back/forward, or a shared link all restore it - previously these
  // refs were the only place the selection lived, so refreshing the page lost
  // every filter but the page number (changePage() already wrote that one).
  const syncFiltersToUrl = () => {
    const query: Record<string, any> = { ...route.query };

    delete query.brand;
    if (selectedBrands.value.length > 0) {
      query.brand = selectedBrands.value.join(",");
    }

    delete query.price_from;
    if (priceMin.value > initialPriceMin.value) {
      query.price_from = priceMin.value;
    }
    delete query.price_to;
    if (priceMax.value < initialPriceMax.value) {
      query.price_to = priceMax.value;
    }

    delete query.rating;
    if (selectedRating.value) {
      query.rating = selectedRating.value;
    }

    delete query.discounts;
    if (onlyDiscounts.value) {
      query.discounts = "1";
    }

    delete query.in_stock;
    if (onlyInStock.value) {
      query.in_stock = "1";
    }

    delete query.sort_by;
    if (sortBy.value && sortBy.value !== "popularity") {
      query.sort_by = sortBy.value;
    }

    Object.keys(query).forEach((key) => {
      if (/^attrs\[.+\]$/.test(key)) delete query[key];
    });
    Object.entries(selectedAttrs.value).forEach(([code, values]) => {
      if (values && values.length > 0) {
        query[`attrs[${code}]`] = values.join(",");
      }
    });

    delete query.page;
    if (pagination.value.page > 1) {
      query.page = pagination.value.page;
    }

    router.replace({ name: route.name as string, params: route.params, query });
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

    // Dynamic attributes - one chip per selected value so each can be removed independently
    Object.keys(selectedAttrs.value).forEach((code) => {
      const values = selectedAttrs.value[code] || [];
      const attr = dynamicAttributes.value.find((a) => a.code === code);
      const attrName = attr ? pickLocalized(attr.name, locale.value) : code;
      values.forEach((val) => {
        filters.push({
          type: "attribute",
          code: code,
          label: `${attrName}: ${val}`,
          value: val,
        });
      });
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
      const remaining = (current[filter.code] || []).filter(
        (v) => v !== filter.value,
      );
      if (remaining.length > 0) {
        current[filter.code] = remaining;
      } else {
        delete current[filter.code];
      }
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
    async ([newCategory], [oldCategory]) => {
      pagination.value.page = parseInt(route.query.page as string) || 1;
      if (newCategory !== oldCategory) {
        // A category switch invalidates filters chosen for the previous category's
        // facets (e.g. a phone attribute carrying over into laptops) - clear them and
        // refetch the category-scoped price bounds/attributes/brand counts before
        // reloading products against the new category. Resetting selectedAttrs/
        // selectedBrands (and fetchFilterSchema() updating priceMin/priceMax) already
        // triggers the watch() below, which calls fetchProducts() itself - an explicit
        // call here would just be a redundant duplicate request.
        selectedAttrs.value = {};
        selectedBrands.value = [];
        selectedRating.value = "";
        onlyDiscounts.value = false;
        onlyInStock.value = false;
        await Promise.all([fetchFilterSchema(), fetchBrands()]);
        return;
      }
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
      selectedRating.value,
      onlyDiscounts.value,
      onlyInStock.value,
    ],
    () => {
      pagination.value.page = 1;
      fetchProducts();
      syncFiltersToUrl();
    },
    { deep: true },
  );

  onMounted(() => {
    window.scrollTo(0, 0);
    fetchCategories();
    fetchBrands();
    // Bounds aren't known until this resolves, so a shared/refreshed link's
    // price_from/price_to (captured in initialPriceFrom/To before anything
    // else in this composable could touch priceMin/priceMax) is only applied
    // once they arrive - applying it against the 0-200000 placeholder above
    // would just get overwritten by fetchFilterSchema's own assignment.
    fetchFilterSchema().then(() => {
      if (initialPriceFrom !== null) {
        priceMin.value = Math.max(initialPriceFrom, initialPriceMin.value);
      }
      if (initialPriceTo !== null) {
        priceMax.value = Math.min(initialPriceTo, initialPriceMax.value);
      }
    });
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
    initialPriceMin,
    initialPriceMax,
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
