import { ref, computed, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { useAuthStore } from "@/entities/user/model/authStore";
import { orderApi } from "@/shared/services/api/orderApi";
import { productApi } from "@/shared/services/api/productApi";

export function useShoppingCart() {
  const router = useRouter();
  const cartStore = useCartStore();
  const authStore = useAuthStore();

  const promoCode = ref("");
  const appliedPromo = ref("");
  const promoDiscountAmount = ref(0);
  const isCheckoutMode = ref(false);
  const isSuccessMode = ref(false);
  const isSubmitting = ref(false);
  const orderSuccessData = ref<any>(null);

  // Checkout form state
  const checkoutForm = ref({
    customerName: "",
    customerEmail: "",
    customerPhone: "",
    shippingCountry: "Ukraine",
    shippingCity: "",
    shippingAddress: "",
    deliveryMethod: "nova_poshta",
    paymentMethod: "cod",
  });

  // Pre-fill user data if logged in
  if (authStore.user) {
    checkoutForm.value.customerName = authStore.user.name || "";
    checkoutForm.value.customerEmail = authStore.user.email || "";
    checkoutForm.value.customerPhone = authStore.user.phone || "";
  }

  const shipping = computed(() =>
    cartStore.cartTotal >= 5000 || cartStore.cart.length === 0 ? 0 : 250,
  );
  const discount = computed(() => promoDiscountAmount.value);
  const tax = computed(() =>
    Math.max(0, (cartStore.cartTotal - discount.value) * 0.075),
  );
  const total = computed(
    () => cartStore.cartTotal - discount.value + shipping.value + tax.value,
  );

  const recommended = ref<any[]>([]);

  const mapProduct = (p: any) => {
    const mainVariant = p.variants?.[0];
    const price = mainVariant ? parseFloat(mainVariant.price) : (p.price || 0);
    const oldPrice = mainVariant && mainVariant.old_price ? parseFloat(mainVariant.old_price) : (p.oldPrice || p.old_price || null);
    
    let image = p.image || "";
    if (!image && mainVariant && mainVariant.dimensions && mainVariant.dimensions.images) {
      const primary = mainVariant.dimensions.images.find((img: any) => img.isPrimary) || mainVariant.dimensions.images[0];
      if (primary && primary.url) {
        image = primary.url;
      }
    }
    if (!image) {
      image = "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop";
    }

    const category = p.categories?.[0]
      ? (p.categories[0].name?.uk || p.categories[0].name?.en || p.categories[0].name)
      : (p.category || "Electronics");

    const brandName = p.brand?.name || p.brand || "Brand";

    // Extract specs
    const ram = p.attributeValues?.find((av: any) => av.attribute?.code === "ram")?.customValue || "8GB";
    const screen = p.attributeValues?.find((av: any) => av.attribute?.code === "screen")?.customValue || "6.1\"";
    const storage = p.attributeValues?.find((av: any) => av.attribute?.code === "storage")?.customValue || "128GB";

    const totalStock = mainVariant
      ? (mainVariant.stocks
          ? mainVariant.stocks.reduce((acc: number, s: any) => acc + (parseInt(s.quantity) - parseInt(s.reserved || 0)), 0)
          : (mainVariant.stock !== undefined ? mainVariant.stock : 0))
      : (p.stock !== undefined ? p.stock : 10);

    return {
      id: p.id,
      name: p.name?.uk || p.name?.en || p.name,
      slug: p.slug,
      price: price,
      oldPrice: oldPrice,
      image: image,
      category: category,
      brand: brandName,
      rating: p.rating || 4.5,
      reviews: p.reviews || Math.floor(Math.random() * 80) + 12,
      inStock: totalStock > 0,
      ram: ram,
      specs: {
        screen: screen,
        storage: storage
      }
    };
  };

  const fetchRecommended = async () => {
    try {
      const response = await productApi.catalogGetRandomProducts();
      if (response.data && response.data.status === "success") {
        const rawProducts = response.data.data || [];
        recommended.value = rawProducts.slice(0, 4).map(mapProduct);
      }
    } catch (e) {
      console.error("Failed to fetch recommended products", e);
    }
  };

  onMounted(() => {
    fetchRecommended();
  });

  const moveToCart = async (product: any) => {
    await cartStore.addToCart(product);
    if (cartStore.isInWishlist(product.id)) {
      await cartStore.toggleWishlist(product);
    }
  };

  const removePromo = () => {
    appliedPromo.value = "";
    promoDiscountAmount.value = 0;
    promoCode.value = "";
    cartStore.addToast("Промокод видалено.", "info");
  };

  const hasOutOfStockItems = computed(() => {
    return cartStore.cart.some(item => item.stock !== undefined && item.stock <= 0);
  });

  const isQuickViewOpen = ref(false);
  const quickViewProduct = ref<any>(null);

  const openQuickView = (product: any) => {
    quickViewProduct.value = product;
    isQuickViewOpen.value = true;
  };

  const closeQuickView = () => {
    isQuickViewOpen.value = false;
    quickViewProduct.value = null;
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat("uk-UA", {
      style: "currency",
      currency: "UAH",
      maximumFractionDigits: 0,
    }).format(price);
  };

  const applyPromo = async () => {
    if (!promoCode.value.trim()) return;
    const code = promoCode.value.trim().toUpperCase();
    try {
      const response = await orderApi.validateCoupon(code, cartStore.cartTotal);
      if (response.data && response.data.status === "success") {
        appliedPromo.value = code;
        promoDiscountAmount.value = response.data.data.discount;
        cartStore.addToast(
          `Promo code ${code} applied successfully!`,
          "success",
        );
      }
    } catch (error: any) {
      const errMsg = error.response?.data?.message || "Invalid coupon code";
      cartStore.addToast(errMsg, "error");
      appliedPromo.value = "";
      promoDiscountAmount.value = 0;
    }
  };

  watch(
    () => cartStore.cart,
    () => {
      if (appliedPromo.value) {
        applyPromo();
      }
    },
    { deep: true },
  );

  const addRecommended = (product: any) => {
    cartStore.addToCart(product);
  };

  const handlePlaceOrder = async () => {
    if (
      !checkoutForm.value.customerName ||
      !checkoutForm.value.customerEmail ||
      !checkoutForm.value.customerPhone ||
      !checkoutForm.value.shippingAddress
    ) {
      cartStore.addToast("Please fill in all required fields.", "error");
      return;
    }

    isSubmitting.value = true;
    try {
      // Convert state properties to backend snake_case if they aren't parsed by middleware,
      // but ConvertCamelCaseToSnakeCase middleware does it for request body, so camelCase is perfect!
      const response = await orderApi.placeOrder({
        customerName: checkoutForm.value.customerName,
        customerEmail: checkoutForm.value.customerEmail,
        customerPhone: checkoutForm.value.customerPhone,
        shippingCountry: checkoutForm.value.shippingCountry,
        shippingCity: checkoutForm.value.shippingCity,
        shippingAddress: checkoutForm.value.shippingAddress,
        deliveryMethod: checkoutForm.value.deliveryMethod,
        paymentMethod: checkoutForm.value.paymentMethod,
        sessionId: localStorage.getItem("cart_session_id") || "",
        couponCode: appliedPromo.value,
      });

      if (response.data && response.data.status === "success") {
        cartStore.addToast("Order successfully placed!", "success");
        orderSuccessData.value = response.data.data;
        cartStore.cart = [];
        isSuccessMode.value = true;
      }
    } catch (error: any) {
      console.error("Checkout failed:", error);
      const errMsg =
        error.response?.data?.message || "Error occurred during checkout";
      cartStore.addToast(errMsg, "error");
    } finally {
      isSubmitting.value = false;
    }
  };

  return {
    router,
    cartStore,
    promoCode,
    appliedPromo,
    promoDiscountAmount,
    isCheckoutMode,
    isSuccessMode,
    isSubmitting,
    orderSuccessData,
    checkoutForm,
    shipping,
    discount,
    tax,
    total,
    recommended,
    moveToCart,
    removePromo,
    hasOutOfStockItems,
    isQuickViewOpen,
    quickViewProduct,
    openQuickView,
    closeQuickView,
    formatPrice,
    applyPromo,
    addRecommended,
    handlePlaceOrder,
  };
}
