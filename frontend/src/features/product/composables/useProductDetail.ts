import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { productApi } from "@/shared/services/api/productApi";

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
        (v: any) => v.id === activeVariantId.value
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
            (acc: number, s: any) => acc + (parseInt(s.quantity) - parseInt(s.reserved)),
            0
          )
        : 0;

      let image = "";
      if (
        mainVariant &&
        mainVariant.dimensions &&
        mainVariant.dimensions.images &&
        mainVariant.dimensions.images.length > 0
      ) {
        const primary =
          mainVariant.dimensions.images.find((img: any) => img.isPrimary) ||
          mainVariant.dimensions.images[0];
        if (primary && primary.url) {
          image = primary.url;
        }
      }

      if (!image) {
        image =
          "https://lh3.googleusercontent.com/aida-public/AB6AXuBZjrYzoYVLWW_oiXKtFfrvXfrqZhFl0aOo-qiqP-OxioJPU85soCgr1bPX8-8SrIpEgyr7zYqcamNRaM1BW5yOnQdyQkcNC89uNihkW1bThAYw05lRVqC36IMTBCvBLVH7opxwC_Q3tAwXBXFTV3E_7Pec49dMJ6oEmwa-i1h3rfPR3C3ZxfrlPDm4iN8h3YEy4Smhr2pI6IcA1YpRV8_hq162IYmxl8-kkt1WI_Z9ARaUKWft3ncDr_m6Dug4Fa0Nm0Rr2ngLp0Q";
      }

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
        ramVal && storageVal
          ? `${ramVal} ОЗУ / ${storageVal}`
          : rawProduct.value.brand?.name
          ? `${rawProduct.value.brand.name} Edition`
          : "Premium Tech Edition";

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
            : "Смартфони",
        subtitle: subtitle,
        price: price,
        oldPrice: oldPrice || price * 1.15,
        image: image,
        rating: rawProduct.value.rating || 4.8,
        reviews: rawProduct.value.reviews || 84,
        description: description,
        specs: specsList,
        inStock: totalStock > 0,
      };
    }
    return null;
  });

  const galleryImages = computed(() => {
    if (!rawProduct.value) return [];
    const mainVariant = activeVariant.value;
    const images: any[] = [];

    if (
      mainVariant &&
      mainVariant.dimensions &&
      mainVariant.dimensions.images &&
      mainVariant.dimensions.images.length > 0
    ) {
      mainVariant.dimensions.images.forEach((img: any, idx: number) => {
        images.push({
          label: img.isPrimary ? "Основний вигляд" : `Вигляд ${idx + 1}`,
          src: img.url,
        });
      });
    }

    if (images.length === 0) {
      const mainImg =
        product.value?.image ||
        "https://lh3.googleusercontent.com/aida-public/AB6AXuBZjrYzoYVLWW_oiXKtFfrvXfrqZhFl0aOo-qiqP-OxioJPU85soCgr1bPX8-8SrIpEgyr7zYqcamNRaM1BW5yOnQdyQkcNC89uNihkW1bThAYw05lRVqC36IMTBCvBLVH7opxwC_Q3tAwXBXFTV3E_7Pec49dMJ6oEmwa-i1h3rfPR3C3ZxfrlPDm4iN8h3YEy4Smhr2pI6IcA1YpRV8_hq162IYmxl8-kkt1WI_Z9ARaUKWft3ncDr_m6Dug4Fa0Nm0Rr2ngLp0Q";
      images.push(
        { label: "Основний вигляд", src: mainImg },
        { label: "Вигляд збоку", src: mainImg },
        { label: "Детальний макро-вигляд", src: mainImg }
      );
    }

    return images;
  });

  const availableColors = computed(() => {
    if (!rawProduct.value?.variants) return [];
    const colors = new Set<string>();
    rawProduct.value.variants.forEach((v: any) => {
      const list = v.attributeValues || v.attribute_values || [];
      const colorAttr = list.find(
        (av: any) => av.attribute?.code === "color"
      );
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
          av.attribute?.code === "ram"
      );
      if (storageAttr) {
        const valObj = storageAttr.attributeValue || storageAttr.attribute_value;
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
        const list = newVariant.attributeValues || newVariant.attribute_values || [];
        const match = list.find(
          (av: any) => av.attribute?.code === code
        );
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
        getAttrValue("storage") || getAttrValue("memory") || getAttrValue("ram");
      if (storageVal) selectedStorage.value = storageVal;
    },
    { immediate: true }
  );

  const activeTab = ref("experience");
  const showStickyBar = ref(false);
  const selectedBundleIds = ref<string[]>(["pods", "charger"]);

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
    () => galleryImages.value[selectedImageIndex.value]?.src || ""
  );

  const tabs = [
    { id: "experience", label: "Огляд продукту" },
    { id: "specs", label: "Характеристики" },
    { id: "reviews", label: "Відгуки" },
    { id: "support", label: "Підтримка" },
  ];

  const bundleItems = computed(() => {
    if (!product.value) return [];
    return [
      {
        id: "device",
        name: product.value.name,
        category: "Основний пристрій",
        price: product.value.price,
        locked: true,
        image: galleryImages.value[0]?.src || "",
      },
      {
        id: "pods",
        name: "Elite Sound Pods",
        category: "Бездротові навушники",
        price: 6990,
        image:
          "https://lh3.googleusercontent.com/aida-public/AB6AXuA4CBkZB03qlIoMec3YDV24fO35X8SQ-nFR3-vSL9fHTRB_0yNWXQIPyPUR9XTJAgwPRqR9BMPLYRdA1wE5DJ45Whogygd0z1RLbsHf57iD3oNin76Iky7ChCqZYi5i_wfvTapwlF_E-PSDIHYoRQK6uRBPNNTQz4EHty0UuXvWXNNbKzjznstWRzJVKUzyYdU8ZPafSIhxOBNZZog6jxjU4a9KAaF5H8EcaCT0lQ1XAMin35srr2hS4Wizm7MABaeuhA9WVqY02lQ",
      },
      {
        id: "charger",
        name: "Pro Charge 100W GaN",
        category: "Швидкий зарядний пристрій",
        price: 2990,
        image:
          "https://lh3.googleusercontent.com/aida-public/AB6AXuDHpw39gwGprYkW4qIbZzy2rLTbcLvDxWukT6TsUoCUIIA_MP68QWCuopoQuE9aG5HGs8xzGWJvtts8ShB73VX0DQ3EYiBd5Muljqm64CLPv2t0Sih0HEiaGcZ9xQ2yqdEPy-u7wBaEYQHeCCQqfWYxSM_XjbxKllnWehPZ9qyUDYglqNb73NhCqinyVtGSEOFch5lKqYYp6TABRJkKScR9scZP6lbXxLVOft7ZvFplc69p0s6hEhsBh7bPRgQPf3E-KLZlLIvte_g",
      },
    ];
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

  const reviews = [
    {
      name: "Марта В.",
      title: "Чудовий вибір для роботи та графіки",
      text: "Неймовірний екран, кольори соковиті, очі не втомлюються. Продуктивності з запасом, а матеріали корпусу відчуваються дорого.",
    },
    {
      name: "Данило К.",
      title: "Преміальний рівень пристрою",
      text: "Швидкість роботи SSD вражає. Дуже задоволений часом автономної роботи — витримує цілий день інтенсивного програмування.",
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
      .filter((item) => item.locked || selectedBundleIds.value.includes(item.id))
      .reduce((sum, item) => sum + item.price, 0);
  });

  const bundleSavings = computed(() =>
    selectedBundleIds.value.length ? 3000 : 0
  );
  const bundleTotal = computed(() =>
    Math.max(0, bundleSubtotal.value - bundleSavings.value)
  );

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
        (id) => id !== item.id
      );
    } else {
      selectedBundleIds.value = [...selectedBundleIds.value, item.id];
    }
  };

  const addBundleToCart = () => {
    if (!product.value) return;
    cartStore.addToCart(product.value);
    selectedBundleIds.value.forEach((id) => {
      const item = bundleItems.value.find((bundleItem) => bundleItem.id === id);
      if (item && !item.locked) {
        cartStore.addToCart({
          id: item.id as any,
          name: item.name,
          price: item.price,
          image: item.image,
          category: "Accessories",
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
          cartStore.trackProductView(rawProduct.value.id);
        }
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

  // Re-fetch when navigating between products (Vue Router reuses the component instance)
  watch(
    () => route.params.id,
    (newId, oldId) => {
      if (newId && newId !== oldId) {
        window.scrollTo(0, 0);
        rawProduct.value = null;
        fetchProductDetails(newId as string);
      }
    }
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
    reviews,
    formatPrice,
    bundleSubtotal,
    bundleSavings,
    bundleTotal,
    setSelectedImage,
    selectNextImage,
    selectPreviousImage,
    toggleBundleItem,
    addBundleToCart,
    fetchProductDetails,
  };
}
