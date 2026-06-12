import { ref, computed, watch, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { productApi } from "@/shared/services/api/productApi";

export function useCatalog() {
  const route = useRoute();
  const router = useRouter();

  const viewMode = ref("grid");
  const sortBy = ref("popularity");
  const initialPriceMin = ref(0);
  const initialPriceMax = ref(200000);
  const priceMin = ref(0);
  const priceMax = ref(200000);
  const selectedBrands = ref<string[]>([]);
  const selectedAttrs = ref<Record<string, string>>({});
  const selectedRating = ref("");
  const onlyDiscounts = ref(false);
  const onlyInStock = ref(false);

  const isMobileFilterOpen = ref(false);
  const selectedProductForQuickView = ref<any>(null);
  const isQuickViewOpen = ref(false);

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

  function mapProduct(apiProduct: any) {
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

    // Try to get primary image from variant's dimensions.images
    let image = "";
    if (
      mainVariant &&
      mainVariant.dimensions &&
      mainVariant.dimensions.images
    ) {
      const primary =
        mainVariant.dimensions.images.find((img: any) => img.isPrimary) ||
        mainVariant.dimensions.images[0];
      if (primary && primary.url) {
        image = primary.url;
      }
    }

    // Fallback to legacy static images if no dynamic images exist
    if (!image) {
      image =
        "https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM";
      if (apiProduct.slug === "iphone-15-pro-max") {
        image =
          "https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM";
      } else if (apiProduct.slug === "samsung-galaxy-s24-ultra") {
        image =
          "https://lh3.googleusercontent.com/aida-public/AB6AXuDNXpOdOi1q9K16_agnjDdmva4mM8QDf9TI4MCTsRa0_OXpmRLAkd2BmZ0IpQebeCf9T-oqp5EXZIEqu5AgJgO3UAZfh8JwEUwazBkmMcqSqi5NOJjpKjWbdNN6PVkBt40FEXcJMc2b-kYP2x4afcnwiPcUckUaDsOZfW3QlxwFPMxfrXvfI7xR-8qcpi8AlkYYBVIucffemoFhQigVY-yrdYAUIMrcC6HgcPyO99EpuBM4WdjdU2LJpA6MY3BhgG7BudOrk4ZPlNw";
      } else if (apiProduct.slug === "lenovo-legion-5-pro") {
        image =
          "https://lh3.googleusercontent.com/aida-public/AB6AXuDr331B7FabLZcRGhJ_DbZowzkaew5s_GJfms-DS1LXHrCr9JrEM_qiTSvHHdcRLOQU4NygZqdg2vzSEP8qolpkbrEuPi83FukM8x4ZzJpflfXCL5i6WZw99Ro2W_kJSyPwSKmBh7aTJ89xk_sSMwhQZu0di9CfY_tYG8xsS9crK6wdrdWzCio8Ct_P6vzzIdKMqZSvWk-cI5tR8P_uuTugKKtObu44X83uzkFVwQ768UhPlN4P_9soMg2YidbSr7gU_mGJdorHV3E";
      } else if (apiProduct.slug === "sony-wh-1000xm5-black") {
        image =
          "https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA";
      } else if (apiProduct.slug === "apple-airpods-pro-2") {
        image =
          "https://lh3.googleusercontent.com/aida-public/AB6AXuA4CBkZB03qlIoMec3YDV24fO35X8SQ-nFR3-vSL9fHTRB_0yNWXQIPyPUR9XTJAgwPRqR9BMPLYRdA1wE5DJ45Whogygd0z1RLbsHf57iD3oNin76Iky7ChCqZYi5i_wfvTapwlF_E-PSDIHYoRQK6uRBPNNTQz4EHty0UuXvWXNNbKzjznstWRzJVKUzyYdU8ZPafSIhxOBNZZog6jxjU4a9KAaF5H8EcaCT0lQ1XAMin35srr2hS4Wizm7MABaeuhA9WVqY02lQ";
      }
    }

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
      brand: apiProduct.brand ? apiProduct.brand.name : "Unknown",
      ram: getAttrValue("ram") || "16GB",
      category:
        apiProduct.categories && apiProduct.categories[0]
          ? apiProduct.categories[0].name.uk || apiProduct.categories[0].name.en
          : "Laptops",
      price: price,
      oldPrice: oldPrice,
      rating: apiProduct.approvedReviewsAvgRating != null ? parseFloat(apiProduct.approvedReviewsAvgRating) : 0,
      reviews: apiProduct.approvedReviewsCount != null ? Number(apiProduct.approvedReviewsCount) : 0,
      badge: oldPrice ? "Акція" : null,
      badgeClass: oldPrice ? "bg-rose-600" : "",
      inStock: totalStock > 0,
      image: image,
      description: description,
      specs: {
        processor: getAttrValue("processor") || "Apple Silicon / Intel Core",
        screen: getAttrValue("screen_size") || '14" IPS',
        storage: getAttrValue("storage") || "512GB SSD",
        os: getAttrValue("os") || "Windows 11 / macOS",
        weight:
          mainVariant && mainVariant.weight
            ? `${mainVariant.weight} кг`
            : "1.5 кг",
      },
    };
  }

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

      if (route.query.category) {
        params.category = route.query.category;
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

  const selectCategory = (categorySlug: string) => {
    router.push({
      name: "catalog",
      query: {
        ...route.query,
        category: categorySlug || undefined,
        page: 1,
      },
    });
  };

  const changePage = (page: number) => {
    if (page >= 1 && page <= pagination.value.lastPage) {
      pagination.value.page = page;
      router.push({
        name: "catalog",
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

  const openQuickView = (product: any) => {
    selectedProductForQuickView.value = product;
    isQuickViewOpen.value = true;
  };

  const closeQuickView = () => {
    selectedProductForQuickView.value = null;
    isQuickViewOpen.value = false;
  };

  const getCategoryPath = (categories: any[], slug: string, path: any[] = []): any[] | null => {
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
    if (!route.query.category) return [];
    return getCategoryPath(categoriesList.value, route.query.category as string) || [];
  });

  const currentCategoryName = computed(() => {
    if (!route.query.category) return "Всі товари";
    const path = currentCategoryPath.value;
    if (path.length > 0) {
      const last = path[path.length - 1];
      return last.name?.uk || last.name?.en || last.name;
    }
    return "Каталог";
  });

  watch(
    () => [route.query.category, route.query.search, route.query.page],
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

  return {
    route,
    router,
    viewMode,
    sortBy,
    priceMin,
    priceMax,
    selectedBrands,
    selectedAttrs,
    selectedRating,
    onlyDiscounts,
    onlyInStock,
    isMobileFilterOpen,
    selectedProductForQuickView,
    isQuickViewOpen,
    isLoading,
    rawProducts,
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
    openQuickView,
    closeQuickView,
    currentCategoryName,
    currentCategoryPath,
  };
}
