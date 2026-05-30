<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { store } from "@/store.js";
import CatalogFilters from "@/components/catalog/CatalogFilters.vue";
import ProductCard from "@/components/catalog/ProductCard.vue";
import QuickViewModal from "@/components/catalog/QuickViewModal.vue";
import api from "@/services/api.js";

const route = useRoute();
const router = useRouter();

const viewMode = ref("grid");
const sortBy = ref("popularity");
const initialPriceMin = ref(0);
const initialPriceMax = ref(200000);
const priceMin = ref(0);
const priceMax = ref(200000);
const selectedBrands = ref([]);
const selectedAttrs = ref({});
const selectedRating = ref("");
const onlyDiscounts = ref(false);
const onlyInStock = ref(false);

const isMobileFilterOpen = ref(false);
const selectedProductForQuickView = ref(null);
const isQuickViewOpen = ref(false);

const isLoading = ref(false);
const rawProducts = ref([]);
const categoriesList = ref([]);
const dbBrands = ref([]);
const dynamicAttributes = ref([]);
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
      count: b.products_count || 0,
    };
  });
});

function mapProduct(apiProduct) {
  if (!apiProduct) return null;
  const mainVariant =
    apiProduct.variants && apiProduct.variants[0]
      ? apiProduct.variants[0]
      : null;
  const price = mainVariant ? parseFloat(mainVariant.price) : 0;
  const oldPrice =
    mainVariant && mainVariant.old_price
      ? parseFloat(mainVariant.old_price)
      : null;
  const totalStock = mainVariant
    ? (mainVariant.stocks || []).reduce(
        (acc, s) => acc + (parseInt(s.quantity) - parseInt(s.reserved)),
        0,
      )
    : 0;

  // Try to get primary image from variant's dimensions.images
  let image = "";
  if (mainVariant && mainVariant.dimensions && mainVariant.dimensions.images) {
    const primary =
      mainVariant.dimensions.images.find((img) => img.isPrimary) ||
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

  const getAttrValue = (code) => {
    const checkList = [];
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
      return match.custom_value || "";
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
    rating: apiProduct.rating || 4.8,
    reviews: apiProduct.reviews || 84,
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

const formatPrice = (price) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};

const fetchCategories = async () => {
  try {
    const response = await api.get("/v1/catalog/categories");
    if (response.data && response.data.status === "success") {
      categoriesList.value = response.data.data || [];
    }
  } catch (error) {
    console.error("Failed to fetch categories:", error);
  }
};

const fetchBrands = async () => {
  try {
    const response = await api.get("/v1/catalog/brands");
    if (response.data && response.data.status === "success") {
      dbBrands.value = response.data.data || [];
    }
  } catch (error) {
    console.error("Failed to fetch brands:", error);
  }
};

const fetchFilterSchema = async () => {
  try {
    const response = await api.get("/v1/catalog/filters");
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
    const params = {
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

    // Add EAV attributes to query parameters
    Object.keys(selectedAttrs.value).forEach((code) => {
      const val = selectedAttrs.value[code];
      if (val) {
        params[`attrs[${code}]`] = val;
      }
    });

    const response = await api.get("/v1/catalog/products", { params });
    if (response.data && response.data.status === "success") {
      const apiData = response.data.data;
      rawProducts.value = (apiData.data || []).map(mapProduct);
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

const selectCategory = (categorySlug) => {
  router.push({
    name: "catalog",
    query: {
      ...route.query,
      category: categorySlug || undefined,
      page: 1,
    },
  });
};

const changePage = (page) => {
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
  const filters = [];

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
      const attrName = attr ? attr.name.uk || attr.name.en || attr.name : code;
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

const removeFilter = (filter) => {
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

const openQuickView = (product) => {
  selectedProductForQuickView.value = product;
  isQuickViewOpen.value = true;
};

const closeQuickView = () => {
  selectedProductForQuickView.value = null;
  isQuickViewOpen.value = false;
};

const currentCategoryName = computed(() => {
  if (!route.query.category) return "Всі товари";
  const cat = categoriesList.value.find((c) => c.slug === route.query.category);
  return cat ? cat.name.uk || cat.name.en || cat.name : "Каталог";
});

watch(
  () => [route.query.category, route.query.search, route.query.page],
  () => {
    pagination.value.page = parseInt(route.query.page) || 1;
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
  pagination.value.page = parseInt(route.query.page) || 1;
  fetchProducts();
});
</script>

<template>
  <!-- Main Catalog Area -->
  <main
    class="max-w-container-max mx-auto px-4 md:px-8 py-8 flex gap-8 font-sans text-zinc-800 dark:text-zinc-200"
  >
    <!-- Sidebar Filters (Desktop) -->
    <aside class="hidden lg:block w-72 flex-shrink-0">
      <div class="sticky top-24 space-y-6">
        <!-- Breadcrumbs -->
        <nav
          class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-550 mb-4 font-bold"
        >
          <a
            class="hover:text-[#00a046] transition-colors"
            href="#"
            @click.prevent="router.push('/')"
          >Головна</a>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <a
            class="hover:text-[#00a046] transition-colors"
            href="#"
            @click.prevent="selectCategory('')"
          >Каталог</a>
          <span
            v-if="route.query.category"
            class="material-symbols-outlined text-[12px]"
          >chevron_right</span>
          <span
            v-if="route.query.category"
            class="text-zinc-800 dark:text-zinc-100 font-extrabold"
          >{{ currentCategoryName }}</span>
        </nav>

        <!-- Catalog Filters Component -->
        <CatalogFilters
          v-model:price-min="priceMin"
          v-model:price-max="priceMax"
          v-model:selected-brands="selectedBrands"
          v-model:selected-attrs="selectedAttrs"
          v-model:selected-rating="selectedRating"
          v-model:only-discounts="onlyDiscounts"
          v-model:only-in-stock="onlyInStock"
          :products="rawProducts"
          :brands="brands"
          :dynamic-attributes="dynamicAttributes"
          :categories-list="categoriesList"
          :selected-category="route.query.category || ''"
          @select-category="selectCategory"
          @clear-filters="clearFilters"
        />
      </div>
    </aside>

    <!-- Products Workspace -->
    <section class="flex-1 min-w-0">
      <!-- Top Action bar -->
      <div
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 p-4 mb-6 shadow-sm"
      >
        <div
          class="flex flex-col md:flex-row md:items-center justify-between gap-4"
        >
          <div>
            <h1
              class="font-extrabold text-xl md:text-2xl text-zinc-900 dark:text-white tracking-tight"
            >
              {{ currentCategoryName }}
            </h1>
            <p class="text-xs text-zinc-450 dark:text-zinc-500 font-bold mt-1">
              Знайдено {{ pagination.total }} товарів
            </p>
          </div>
          <div class="flex items-center gap-3">
            <!-- View Mode toggle switcher -->
            <div
              class="flex items-center bg-zinc-55 dark:bg-zinc-850 rounded-lg p-0.5 border border-zinc-205 dark:border-zinc-800 mr-1.5"
            >
              <button
                :class="
                  viewMode === 'grid'
                    ? 'bg-white dark:bg-zinc-900 shadow-sm text-[#00a046]'
                    : 'text-zinc-450 dark:text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'
                "
                class="p-2 rounded-md transition-colors"
                title="Сітка"
                type="button"
                @click="viewMode = 'grid'"
              >
                <span class="material-symbols-outlined text-[18px]">grid_view</span>
              </button>
              <button
                :class="
                  viewMode === 'list'
                    ? 'bg-white dark:bg-zinc-900 shadow-sm text-[#00a046]'
                    : 'text-zinc-450 dark:text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'
                "
                class="p-2 rounded-md transition-colors"
                title="Список"
                type="button"
                @click="viewMode = 'list'"
              >
                <span class="material-symbols-outlined text-[18px]">view_list</span>
              </button>
            </div>

            <!-- Sorting Select Dropdown -->
            <div class="relative">
              <select
                v-model="sortBy"
                class="appearance-none bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg pl-3 pr-9 py-2.5 text-xs font-extrabold text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] w-48 cursor-pointer outline-none"
              >
                <option value="popularity">
                  За популярністю
                </option>
                <option value="newest">
                  Новинки
                </option>
                <option value="price-asc">
                  Ціна: від дешевих
                </option>
                <option value="price-desc">
                  Ціна: від дорогих
                </option>
              </select>
              <span
                class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-zinc-400 text-[18px]"
              >expand_more</span>
            </div>

            <!-- Mobile filter toggle button -->
            <button
              class="lg:hidden flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 p-2.5 rounded-lg border border-zinc-202 dark:border-zinc-700"
              @click="isMobileFilterOpen = true"
            >
              <span class="material-symbols-outlined text-[18px]">filter_alt</span>
            </button>
          </div>
        </div>

        <!-- Active Applied Filters badges -->
        <div
          class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap gap-2 items-center"
        >
          <span
            class="text-[9px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mr-1"
          >Застосовано:</span>
          <template v-if="activeFilters.length">
            <button
              v-for="filter in activeFilters"
              :key="`${filter.type}-${filter.label}`"
              class="flex items-center gap-1 bg-[#00a046]/10 text-[#00a046] px-2.5 py-1 rounded-full text-[10px] hover:bg-[#00a046]/20 transition-all font-black border border-[#00a046]/20"
              type="button"
              @click="removeFilter(filter)"
            >
              {{ filter.label }}
              <span class="material-symbols-outlined text-[12px]">close</span>
            </button>
            <button
              class="text-[#00a046] hover:text-[#00b050] font-black text-[10px] ml-auto hover:underline flex items-center gap-1 uppercase tracking-wider"
              type="button"
              @click="clearFilters"
            >
              <span class="material-symbols-outlined text-[14px]">filter_list_off</span>
              Скинути все
            </button>
          </template>
          <span
            v-else
            class="text-[10px] text-zinc-400 dark:text-zinc-500 font-extrabold italic"
          >Фільтри не вибрано</span>
        </div>
      </div>

      <!-- Grid / List Products Display -->
      <div
        v-if="filteredProducts.length"
        :class="
          viewMode === 'grid'
            ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6'
            : 'flex flex-col gap-4'
        "
      >
        <ProductCard
          v-for="product in filteredProducts"
          :key="product.id"
          :product="product"
          :view-mode="viewMode"
          @quick-view="openQuickView"
        />
      </div>

      <!-- No products found placeholder -->
      <div
        v-else
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800 p-12 text-center shadow-sm"
      >
        <div
          class="w-16 h-16 mx-auto mb-4 rounded-full bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center text-zinc-400"
        >
          <span class="material-symbols-outlined text-[36px]">search_off</span>
        </div>
        <h2 class="font-extrabold text-zinc-900 dark:text-white mb-2">
          Товари за вашим запитом не знайдені
        </h2>
        <p
          class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 mb-6 max-w-md mx-auto"
        >
          Спробуйте змінити значення цінового діапазону або приберіть зайві
          бренди чи параметри ОЗУ.
        </p>
        <button
          class="bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs py-2.5 px-6 rounded-lg transition-all"
          type="button"
          @click="clearFilters"
        >
          Скинути фільтри
        </button>
      </div>

      <!-- Pagination Block -->
      <nav
        v-if="pagination.lastPage > 1"
        class="mt-12 flex items-center justify-between border-t border-zinc-105 dark:border-zinc-800 pt-6"
      >
        <button
          :disabled="pagination.page === 1"
          :class="
            pagination.page === 1
              ? 'opacity-50 cursor-not-allowed'
              : 'hover:text-[#00a046] hover:bg-zinc-50 dark:hover:bg-zinc-800'
          "
          class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-extrabold text-zinc-550 rounded-lg transition-all"
          @click="changePage(pagination.page - 1)"
        >
          <span class="material-symbols-outlined text-[16px]">arrow_back</span>
          НАЗАД
        </button>
        <div class="flex items-center gap-1">
          <button
            v-for="p in pagination.lastPage"
            :key="p"
            :class="
              pagination.page === p
                ? 'bg-[#00a046] text-white shadow-sm'
                : 'text-zinc-550 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-zinc-800 dark:hover:text-zinc-200'
            "
            class="w-8 h-8 flex items-center justify-center rounded-lg transition-all font-extrabold text-xs"
            @click="changePage(p)"
          >
            {{ p }}
          </button>
        </div>
        <button
          :disabled="pagination.page === pagination.lastPage"
          :class="
            pagination.page === pagination.lastPage
              ? 'opacity-50 cursor-not-allowed'
              : 'hover:text-[#00a046] hover:bg-zinc-50 dark:hover:bg-zinc-800'
          "
          class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-extrabold text-zinc-550 rounded-lg transition-all"
          @click="changePage(pagination.page + 1)"
        >
          ВПЕРЕД
          <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
        </button>
      </nav>
    </section>
  </main>

  <!-- Mobile Filters Bottom Sheet / Side Drawer Overlay -->
  <div
    v-if="isMobileFilterOpen"
    class="fixed inset-0 z-50 flex lg:hidden bg-black/60 backdrop-blur-sm animate-fade"
  >
    <div
      class="bg-white dark:bg-zinc-900 w-80 max-w-[85vw] h-full shadow-2xl p-6 flex flex-col justify-between overflow-y-auto"
    >
      <div class="space-y-6">
        <div
          class="flex items-center justify-between border-b border-zinc-150 dark:border-zinc-800 pb-3"
        >
          <h2 class="font-extrabold text-base text-zinc-900 dark:text-white">
            Фільтри товарів
          </h2>
          <button
            class="text-zinc-450 hover:text-zinc-650"
            @click="isMobileFilterOpen = false"
          >
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>

        <CatalogFilters
          v-model:price-min="priceMin"
          v-model:price-max="priceMax"
          v-model:selected-brands="selectedBrands"
          v-model:selected-attrs="selectedAttrs"
          v-model:selected-rating="selectedRating"
          v-model:only-discounts="onlyDiscounts"
          v-model:only-in-stock="onlyInStock"
          :products="rawProducts"
          :brands="brands"
          :dynamic-attributes="dynamicAttributes"
          :categories-list="categoriesList"
          :selected-category="route.query.category || ''"
          @select-category="selectCategory"
          @clear-filters="clearFilters"
        />
      </div>
      <div
        class="border-t border-zinc-150 dark:border-zinc-800 pt-4 mt-6 flex gap-3"
      >
        <button
          class="flex-1 border border-zinc-250 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 font-extrabold py-2.5 rounded-lg text-xs"
          @click="clearFilters"
        >
          Скинути
        </button>
        <button
          class="flex-1 bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold py-2.5 rounded-lg text-xs"
          @click="isMobileFilterOpen = false"
        >
          Показати
        </button>
      </div>
    </div>
  </div>

  <!-- Quick View Modal (For detailed product stats overview) -->
  <QuickViewModal
    :is-open="isQuickViewOpen"
    :product="selectedProductForQuickView"
    @close="closeQuickView"
  />
</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.22s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(3px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
