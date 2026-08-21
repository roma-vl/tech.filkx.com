import {
  ref,
  computed,
  watch,
  onMounted,
  onUnmounted,
  onServerPrefetch,
} from "vue";
import { useRoute, useRouter } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { productApi } from "@/shared/services/api/productApi";
import {
  resolveProductImage,
  resolveGalleryImages,
} from "@/entities/product/lib/resolveProductImage";
import { mapCatalogProduct } from "@/entities/product/lib/mapCatalogProduct";

export function useProductDetail() {
  const route = useRoute();
  const router = useRouter();
  const cartStore = useCartStore();

  const isLoading = ref(false);
  const rawProduct = ref<any>(null);
  const activeVariantId = ref<number | null>(null);

  const activeVariant = computed(() => {
    if (
      !rawProduct.value ||
      !rawProduct.value.variants ||
      rawProduct.value.variants.length === 0
    ) {
      return null;
    }
    if (activeVariantId.value) {
      const found = rawProduct.value.variants.find(
        (v: any) => v.id === activeVariantId.value,
      );
      if (found) return found;
    }
    return rawProduct.value.variants[0];
  });

  const product = computed(() => {
    if (rawProduct.value) {
      const mainVariant = activeVariant.value;
      const price = mainVariant ? parseFloat(mainVariant.price) : 0;
      const oldPrice =
        mainVariant && mainVariant.oldPrice
          ? parseFloat(mainVariant.oldPrice)
          : mainVariant && mainVariant.old_price
            ? parseFloat(mainVariant.old_price)
            : null;
      const totalStock = mainVariant
        ? (mainVariant.stocks || []).reduce(
            (acc: number, s: any) =>
              acc + (parseInt(s.quantity) - parseInt(s.reserved)),
            0,
          )
        : 0;

      const image = resolveProductImage(mainVariant, rawProduct.value.variants);

      const name =
        typeof rawProduct.value.name === "object"
          ? rawProduct.value.name.uk || rawProduct.value.name.en
          : rawProduct.value.name;
      const description =
        typeof rawProduct.value.description === "object"
          ? rawProduct.value.description.uk || rawProduct.value.description.en
          : rawProduct.value.description;

      const getAttrValue = (code: string) => {
        const list = [
          ...(rawProduct.value.attributeValues || []),
          ...(rawProduct.value.attribute_values || []),
          ...(mainVariant?.attributeValues || []),
          ...(mainVariant?.attribute_values || []),
        ];
        const match = list.find((av) => av.attribute?.code === code);
        if (!match) return null;
        const valObj = match.attributeValue || match.attribute_value;
        return (
          match.customValue ||
          match.custom_value ||
          valObj?.value?.uk ||
          valObj?.value?.en ||
          valObj?.value
        );
      };

      const ramVal = getAttrValue("memory") || getAttrValue("ram");
      const storageVal = getAttrValue("storage");
      const subtitle =
        ramVal && storageVal ? `${ramVal} ОЗУ / ${storageVal}` : null;

      const specsList: any[] = [];
      const list: any[] = [];
      if (rawProduct.value.attributeValues) {
        list.push(...rawProduct.value.attributeValues);
      }
      if (rawProduct.value.attribute_values) {
        list.push(...rawProduct.value.attribute_values);
      }
      if (mainVariant && mainVariant.attributeValues) {
        list.push(...mainVariant.attributeValues);
      }
      if (mainVariant && mainVariant.attribute_values) {
        list.push(...mainVariant.attribute_values);
      }
      const seenCodes = new Set();
      list.forEach((av) => {
        if (!av.attribute) return;
        const code = av.attribute.code;
        if (seenCodes.has(code)) return;
        seenCodes.add(code);
        const label =
          av.attribute.name?.uk ||
          av.attribute.name?.en ||
          av.attribute.name ||
          code;
        let val = "";
        if (av.customValue !== null && av.customValue !== undefined) {
          val = av.customValue;
        } else if (av.custom_value !== null && av.custom_value !== undefined) {
          val = av.custom_value;
        } else {
          const valObj = av.attributeValue || av.attribute_value;
          if (valObj) {
            val = valObj.value?.uk || valObj.value?.en || valObj.value || "";
          }
        }
        if (label && val) {
          specsList.push([label, val]);
        }
      });
      if (mainVariant && mainVariant.weight && !seenCodes.has("weight")) {
        specsList.push(["Вага", `${mainVariant.weight} кг`]);
      }
      if (rawProduct.value.brand && !seenCodes.has("brand")) {
        specsList.push(["Бренд", rawProduct.value.brand.name]);
      }
      if (mainVariant && mainVariant.sku && !seenCodes.has("sku")) {
        specsList.push(["Артикул (SKU)", mainVariant.sku]);
      }

      return {
        id: mainVariant ? mainVariant.id : rawProduct.value.id,
        productId: rawProduct.value.id,
        slug: rawProduct.value.slug,
        name: name,
        category:
          rawProduct.value.categories && rawProduct.value.categories[0]
            ? rawProduct.value.categories[0].name.uk ||
              rawProduct.value.categories[0].name.en
            : null,
        subtitle: subtitle,
        price: price,
        oldPrice: oldPrice,
        image: image,
        rating:
          rawProduct.value.approvedReviewsAvgRating != null
            ? parseFloat(rawProduct.value.approvedReviewsAvgRating)
            : 0,
        reviews:
          rawProduct.value.approvedReviewsCount != null
            ? Number(rawProduct.value.approvedReviewsCount)
            : 0,
        description: description,
        specs: specsList,
        inStock: totalStock > 0,
      };
    }
    return null;
  });

  const galleryImages = computed(() => {
    if (!rawProduct.value) return [];
    const images = resolveGalleryImages(
      activeVariant.value,
      rawProduct.value.variants,
    );
    return images.map((img, idx) => ({
      label: img.isPrimary ? "Основний вигляд" : `Вигляд ${idx + 1}`,
      src: img.url,
    }));
  });

  const availableColors = computed(() => {
    if (!rawProduct.value?.variants) return [];
    const colors = new Set<string>();
    rawProduct.value.variants.forEach((v: any) => {
      const list = v.attributeValues || v.attribute_values || [];
      const colorAttr = list.find((av: any) => av.attribute?.code === "color");
      if (colorAttr) {
        const valObj = colorAttr.attributeValue || colorAttr.attribute_value;
        const val =
          colorAttr.customValue ||
          colorAttr.custom_value ||
          valObj?.value?.uk ||
          valObj?.value?.en;
        if (val) colors.add(val);
      }
    });
    return Array.from(colors);
  });

  const availableStorage = computed(() => {
    if (!rawProduct.value?.variants) return [];
    const storage = new Set<string>();
    rawProduct.value.variants.forEach((v: any) => {
      const list = v.attributeValues || v.attribute_values || [];
      const storageAttr = list.find(
        (av: any) =>
          av.attribute?.code === "storage" ||
          av.attribute?.code === "memory" ||
          av.attribute?.code === "ram",
      );
      if (storageAttr) {
        const valObj =
          storageAttr.attributeValue || storageAttr.attribute_value;
        const val =
          storageAttr.customValue ||
          storageAttr.custom_value ||
          valObj?.value?.uk ||
          valObj?.value?.en;
        if (val) storage.add(val);
      }
    });
    return Array.from(storage);
  });

  const selectedImageIndex = ref(0);
  const selectedColor = ref("");
  const selectedStorage = ref("");

  const selectVariantByAttributes = (attributeCode: string, value: string) => {
    if (!rawProduct.value?.variants) return;

    const matchedVariant = rawProduct.value.variants.find((v: any) => {
      const list = v.attributeValues || v.attribute_values || [];
      const attr = list.find((av: any) => {
        const code = av.attribute?.code;
        if (attributeCode === "memory") {
          return code === "memory" || code === "storage" || code === "ram";
        }
        return code === attributeCode;
      });
      if (!attr) return false;
      const valObj = attr.attributeValue || attr.attribute_value;
      const val =
        attr.customValue ||
        attr.custom_value ||
        valObj?.value?.uk ||
        valObj?.value?.en;
      return val === value;
    });

    if (matchedVariant) {
      activeVariantId.value = matchedVariant.id;
    }
  };

  watch(
    activeVariant,
    (newVariant) => {
      if (!newVariant) return;
      selectedImageIndex.value = 0;

      const getAttrValue = (code: string) => {
        const list =
          newVariant.attributeValues || newVariant.attribute_values || [];
        const match = list.find((av: any) => av.attribute?.code === code);
        if (!match) return null;
        const valObj = match.attributeValue || match.attribute_value;
        return (
          match.customValue ||
          match.custom_value ||
          valObj?.value?.uk ||
          valObj?.value?.en ||
          valObj?.value
        );
      };

      const colorVal = getAttrValue("color");
      if (colorVal) selectedColor.value = colorVal;

      const storageVal =
        getAttrValue("storage") ||
        getAttrValue("memory") ||
        getAttrValue("ram");
      if (storageVal) selectedStorage.value = storageVal;
    },
    { immediate: true },
  );

  const activeTab = ref("experience");
  const showStickyBar = ref(false);
  const selectedBundleIds = ref<any[]>([]);
  const randomProducts = ref<any[]>([]);
  const isQuickOrderOpen = ref(false);

  const openQuickOrder = () => {
    isQuickOrderOpen.value = true;
  };

  const closeQuickOrder = () => {
    isQuickOrderOpen.value = false;
  };

  const fetchRandomProducts = async () => {
    try {
      const response = await productApi.catalogGetRandomProducts();
      if (response.data && response.data.status === "success") {
        const currentProductId = rawProduct.value?.id;
        const items = (response.data.data || []).filter(
          (p: any) => p.id !== currentProductId,
        );
        randomProducts.value = items.slice(0, 2);
        selectedBundleIds.value = randomProducts.value.map((p) => p.id);
      }
    } catch (e) {
      console.error("Failed to fetch random products:", e);
    }
  };

  const relatedProducts = ref<any[]>([]);

  const fetchRelatedProducts = async (slug: string) => {
    try {
      const response = await productApi.catalogGetRelatedProducts(slug);
      if (response.data && response.data.status === "success") {
        relatedProducts.value = (response.data.data || [])
          .map(mapCatalogProduct)
          .filter(Boolean);
      }
    } catch (e) {
      console.error("Failed to fetch related products:", e);
    }
  };

  // Sourced from cartStore.viewedDetailed (guest localStorage, synced to the
  // backend for logged-in users) rather than a dedicated fetch — the store
  // already keeps this list current, so it's just excluding the product being
  // viewed right now and shaping it for ProductCard.
  const recentlyViewed = computed(() => {
    const currentProductId = rawProduct.value?.id;
    return (cartStore.viewedDetailed || [])
      .filter((item: any) => String(item.id) !== String(currentProductId))
      .slice(0, 10)
      .map((item: any) => ({
        id: item.id,
        slug: item.slug,
        name: item.name,
        brand: item.brand,
        image: item.image,
        price: item.price,
        oldPrice: null,
        rating: 0,
        reviews: 0,
        inStock: item.inStock !== false,
      }));
  });

  // Magnifying hover zoom state variables
  const isZoomed = ref(false);
  const zoomStyle = ref({
    transform: "scale(1)",
    transformOrigin: "center center",
  });

  const handleMouseMove = (e: MouseEvent) => {
    const container = e.currentTarget as HTMLElement;
    const rect = container.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    isZoomed.value = true;
    zoomStyle.value = {
      transform: "scale(1.8)",
      transformOrigin: `${x}% ${y}%`,
    };
  };

  const handleMouseLeave = () => {
    isZoomed.value = false;
    zoomStyle.value = {
      transform: "scale(1)",
      transformOrigin: "center center",
    };
  };

  const selectedImage = computed(
    () => galleryImages.value[selectedImageIndex.value]?.src || "",
  );

  const tabs = [
    { id: "experience", label: "Огляд продукту" },
    { id: "specs", label: "Характеристики" },
    { id: "reviews", label: "Відгуки" },
    { id: "support", label: "Підтримка" },
  ];

  const bundleItems = computed(() => {
    if (!product.value) return [];
    const list = [
      {
        id: "device",
        name: product.value.name,
        category: "Основний пристрій",
        price: product.value.price,
        oldPrice: product.value.oldPrice,
        locked: true,
        image: galleryImages.value[0]?.src || "",
      },
    ];

    randomProducts.value.forEach((rp: any) => {
      const name =
        typeof rp.name === "object" ? rp.name.uk || rp.name.en : rp.name;
      const category =
        rp.categories && rp.categories[0]
          ? rp.categories[0].name.uk || rp.categories[0].name.en
          : "Аксесуар";

      const mainVariant = rp.variants?.[0] || null;
      const image = resolveProductImage(mainVariant, rp.variants);
      const price = mainVariant ? parseFloat(mainVariant.price) : 0;
      const oldPriceRaw = mainVariant?.oldPrice || mainVariant?.old_price;
      const oldPrice = oldPriceRaw ? parseFloat(oldPriceRaw) : null;

      list.push({
        id: rp.id,
        name: name,
        category: category,
        price: price,
        oldPrice: oldPrice,
        locked: false,
        image: image,
      });
    });

    return list;
  });

  const qualityGuarantees = [
    {
      icon: "award_star",
      title: "2 роки офіційної гарантії",
      text: "Повне сервісне обслуговування.",
    },
    {
      icon: "published_with_changes",
      title: "30 днів для обміну/повернення",
      text: "Легкий обмін без зайвих запитань.",
    },
    {
      icon: "security",
      title: "Захищені платежі SSL",
      text: "Сертифікована безпека всіх транзакцій.",
    },
  ];

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat("uk-UA", {
      style: "currency",
      currency: "UAH",
      maximumFractionDigits: 0,
    }).format(price);
  };

  const bundleSubtotal = computed(() => {
    return bundleItems.value
      .filter(
        (item) => item.locked || selectedBundleIds.value.includes(item.id),
      )
      .reduce((sum, item) => sum + item.price, 0);
  });

  // Real savings only - the sum of each included item's own oldPrice→price
  // markdown. There is no separate bundle-only discount, so the total is
  // simply the subtotal; this figure is purely informational.
  const bundleSavings = computed(() =>
    bundleItems.value
      .filter(
        (item) => item.locked || selectedBundleIds.value.includes(item.id),
      )
      .reduce(
        (sum, item) =>
          sum + Math.max(0, (item.oldPrice ?? item.price) - item.price),
        0,
      ),
  );
  const bundleTotal = computed(() => bundleSubtotal.value);

  const setSelectedImage = (index: number) => {
    selectedImageIndex.value = index;
  };

  const selectNextImage = () => {
    selectedImageIndex.value =
      (selectedImageIndex.value + 1) % galleryImages.value.length;
  };

  const selectPreviousImage = () => {
    selectedImageIndex.value =
      (selectedImageIndex.value - 1 + galleryImages.value.length) %
      galleryImages.value.length;
  };

  const toggleBundleItem = (item: any) => {
    if (item.locked) return;

    if (selectedBundleIds.value.includes(item.id)) {
      selectedBundleIds.value = selectedBundleIds.value.filter(
        (id) => id !== item.id,
      );
    } else {
      selectedBundleIds.value = [...selectedBundleIds.value, item.id];
    }
  };

  const addBundleToCart = () => {
    if (!product.value) return;
    cartStore.addToCart(product.value);
    selectedBundleIds.value.forEach((id) => {
      const rp = randomProducts.value.find(
        (randomP) => randomP.id === id || String(randomP.id) === String(id),
      );
      if (rp) {
        const mainVariant =
          rp.variants && rp.variants[0] ? rp.variants[0] : null;
        const price = mainVariant ? parseFloat(mainVariant.price) : 0;
        const image = resolveProductImage(mainVariant, rp.variants);
        const name =
          typeof rp.name === "object" ? rp.name.uk || rp.name.en : rp.name;

        cartStore.addToCart({
          id: mainVariant ? mainVariant.id : rp.id,
          productId: rp.id,
          name: name,
          price: price,
          image: image,
          category: "Accessories",
          inStock: true,
        } as any);
      }
    });
  };

  const fetchProductDetails = async (slugOverride?: string) => {
    isLoading.value = true;
    try {
      const slug = slugOverride ?? route.params.id;
      const response = await productApi.getProduct(slug as string);
      if (response.data && response.data.status === "success") {
        rawProduct.value = response.data.data;
        if (rawProduct.value && rawProduct.value.id) {
          cartStore.trackProductView(rawProduct.value);
        }
        fetchRandomProducts();
        fetchRelatedProducts(rawProduct.value.slug);
      }
    } catch (error) {
      console.error("Failed to fetch product details:", error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleScroll = () => {
    showStickyBar.value = window.scrollY > 420;
  };

  onMounted(() => {
    window.scrollTo(0, 0);
    fetchProductDetails();
    handleScroll();
    window.addEventListener("scroll", handleScroll, { passive: true });
  });

  // No DOM/onMounted during prerendering — fetch the same data here so the
  // static build captures the real product.
  onServerPrefetch(() => fetchProductDetails());

  // Re-fetch when navigating between products (Vue Router reuses the component instance)
  watch(
    () => route.params.id,
    (newId, oldId) => {
      if (newId && newId !== oldId) {
        window.scrollTo(0, 0);
        rawProduct.value = null;
        fetchProductDetails(newId as string);
      }
    },
  );

  onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
  });

  return {
    route,
    router,
    cartStore,
    isLoading,
    rawProduct,
    product,
    galleryImages,
    availableColors,
    availableStorage,
    selectedImageIndex,
    selectedColor,
    selectedStorage,
    selectVariantByAttributes,
    activeTab,
    showStickyBar,
    selectedBundleIds,
    isZoomed,
    zoomStyle,
    handleMouseMove,
    handleMouseLeave,
    selectedImage,
    tabs,
    bundleItems,
    qualityGuarantees,
    relatedProducts,
    formatPrice,
    bundleSubtotal,
    bundleSavings,
    bundleTotal,
    recentlyViewed,
    setSelectedImage,
    selectNextImage,
    selectPreviousImage,
    toggleBundleItem,
    addBundleToCart,
    fetchProductDetails,
    isQuickOrderOpen,
    openQuickOrder,
    closeQuickOrder,
  };
}
