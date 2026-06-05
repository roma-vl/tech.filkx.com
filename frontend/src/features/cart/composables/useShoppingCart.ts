import { ref, computed, watch } from "vue";
import { useRouter } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { useAuthStore } from "@/entities/user/model/authStore";
import { orderApi } from "@/shared/services/api/orderApi";

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

  const recommended = [
    {
      id: 701,
      name: "MultiPort Dock Pro",
      price: 3200,
      rating: 4,
      reviews: 124,
      category: "Accessories",
      image:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuC_mIjGBAHFLCSnmpKkdkgluw78gP5fDfIt-rj8aDqau9wiUubv68sZfet_avneXb0BdlHgR8tzh3aBL5MKj83GuvSVij-YABBwJRePuzTKdMA2zqGbTvdzQGsHdD3NSTTjSbbPxVOKuECFtHzRaEhD9fBoyYGXR717CRPpYrJXHDa8z0XI66M4gP_g_9Hv82KaVGHSzypPCWknl6Ub4H9C30mao8EJzqQoOOGmGd51ETyjAo19kwe1UWnNWg4Och35CqAlSn6pqGQ",
    },
    {
      id: 702,
      name: "NitroSpeed 2TB SSD",
      price: 6800,
      rating: 5,
      reviews: 52,
      category: "Storage",
      image:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuDOzwSs-mEurkjnbxEorFqVI75POVdV9XlGLWzSzMbWRXxsy59HiItmmyAeg4BLoPf41gWBwaO7cGo-wpz3obaat4JK8vFzPZPhVCB6c1hEeinn3ky228a8QpSiKYty-HviuFXBQJ3MjUJ20Bj1_gieN_lhw3jws_vEvCogRvq8uGZrmKzsaeyHsJ2qJUCHjTvmsaFvAqKylsmuMcOq1_FxcL45gtBztQumOMczuls1pA5qK0RtuMAz51QPeJp8aKMl-s7m9o2Q6kw",
    },
    {
      id: 703,
      name: "Apex Stand Walnut",
      price: 2100,
      rating: 4,
      reviews: 89,
      category: "Workspace",
      image:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuCpUg5-c12LFVGvfSy_r-hIiDuew4xWOPJHE5r1BTJ6C0SZoI81TX4Rso9DsY1kRnI-dH4EFoBtyDeLNxxGygB1RRNSy9b0j8V56zv-H9qAPZ2x_vlxd74Tc0OThpmrAsLQzYdgSvQMIaNe0UOyn6T8rpdRKrfFIf4PTDzngldwuJzQznuYiB7OqKWxdkYKDPFxAQPQcY-KsiBUwnpQYYMJUcNKzoxl20RwAv9SQOcid5iX-yumFX-rfDMswz-Tif6n278ku7FlrLY",
    },
    {
      id: 704,
      name: 'VelvetSleeve 16"',
      price: 1800,
      rating: 5,
      reviews: 210,
      category: "Cases",
      image:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuCX0fRCtuolTkfK9Qa7tIVfBzYdLZrzoSEhGM_-ily0VsCSeEySc7yxUa42o7WB4OmDKMhFXwjw4aqNXCqvB5F_Oy0wSF8bvt7VsLpc27B96CzLYrxHt6nqgHQ7hKNHs222ilnNc1wvmr-quRJPU_o60YDoynDPHeDdqqJ3tquNiLYCRgDv6knsZqFN-eM-mXWw7kOxnQgXn0pJdd0pLwnNruSvEbkzpyxXMfW0X5nIoX3pokXrMUne4unP2CH0HOs-nZTlestvNj8",
    },
  ];

  const savedItem = {
    name: "PulseWatch Elite",
    price: 7600,
    image:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBz2rEecMMKZDqSlTy2wn1wPo-W6lV9QKlZyMs-UQYlUhbtAafWpYU1bJvaAYXheNG0Kh1DFumEhrCByWghjO4L6jynlHaGoCG1wIpkWhuLd30oKLVVu6fOGzAiZqNCKah13ZPgq0lCOYJmrdDLIWiyq5MkHDzbP4kcxt8l0RyC2B83QZlw9AnwgA4cGsknwJsaRcH2tL4erIWhClRt0j3FxcBbAPR23b2ddRn9SXgTUiBD18p245Dr7tM40NDVEiD4kOgijyISvXc",
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
    savedItem,
    formatPrice,
    applyPromo,
    addRecommended,
    handlePlaceOrder,
  };
}
