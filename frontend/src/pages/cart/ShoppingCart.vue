<script setup>
import { computed, ref, watch } from "vue";
import { useRouter } from "vue-router";
import { store } from "@/store.js";
import api from "@/services/api.js";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const promoCode = ref("");
const appliedPromo = ref("");
const promoDiscountAmount = ref(0);
const isCheckoutMode = ref(false);
const isSuccessMode = ref(false);
const isSubmitting = ref(false);
const orderSuccessData = ref(null);

// Checkout form state
const checkoutForm = ref({
  customer_name: "",
  customer_email: "",
  customer_phone: "",
  shipping_country: "Ukraine",
  shipping_city: "",
  shipping_address: "",
  delivery_method: "nova_poshta",
  payment_method: "cod",
});

// Pre-fill user data if logged in
if (authStore.user) {
  checkoutForm.value.customer_name = authStore.user.name || "";
  checkoutForm.value.customer_email = authStore.user.email || "";
  checkoutForm.value.customer_phone = authStore.user.phone || "";
}

const shipping = computed(() =>
  store.cartTotal >= 5000 || store.cart.length === 0 ? 0 : 250,
);
const discount = computed(() => promoDiscountAmount.value);
const tax = computed(() =>
  Math.max(0, (store.cartTotal - discount.value) * 0.075),
);
const total = computed(
  () => store.cartTotal - discount.value + shipping.value + tax.value,
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

const formatPrice = (price) => {
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
    const response = await api.post("/v1/coupons/validate", {
      code: code,
      cart_total: store.cartTotal,
    });
    if (response.data && response.data.status === "success") {
      appliedPromo.value = code;
      promoDiscountAmount.value = response.data.data.discount;
      store.addToast(`Promo code ${code} applied successfully!`, "success");
    }
  } catch (error) {
    const errMsg = error.response?.data?.message || "Invalid coupon code";
    store.addToast(errMsg, "error");
    appliedPromo.value = "";
    promoDiscountAmount.value = 0;
  }
};

watch(() => store.cart, () => {
  if (appliedPromo.value) {
    applyPromo();
  }
}, { deep: true });

const addRecommended = (product) => {
  store.addToCart(product);
};

const handlePlaceOrder = async () => {
  if (
    !checkoutForm.value.customer_name ||
    !checkoutForm.value.customer_email ||
    !checkoutForm.value.customer_phone ||
    !checkoutForm.value.shipping_address
  ) {
    store.addToast("Please fill in all required fields.", "error");
    return;
  }

  isSubmitting.value = true;
  try {
    const response = await api.post("/v1/checkout", {
      ...checkoutForm.value,
      session_id: localStorage.getItem("cart_session_id"),
      coupon_code: appliedPromo.value,
    });

    if (response.data && response.data.status === "success") {
      store.addToast("Order successfully placed!", "success");
      orderSuccessData.value = response.data.data;
      store.cart = [];
      isSuccessMode.value = true;
    }
  } catch (error) {
    console.error("Checkout failed:", error);
    const errMsg =
      error.response?.data?.message || "Error occurred during checkout";
    store.addToast(errMsg, "error");
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<template>
  <main
    class="max-w-container-max mx-auto px-margin-desktop py-stack-lg min-h-screen"
  >
    <!-- Success State -->
    <div
      v-if="isSuccessMode"
      class="max-w-2xl mx-auto bg-surface-container-lowest rounded-2xl border border-outline-variant p-8 md:p-12 text-center shadow-lg my-12"
    >
      <div
        class="w-20 h-20 mx-auto rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-600 mb-6"
      >
        <span class="material-symbols-outlined text-[44px]">check_circle</span>
      </div>
      <h2
        class="font-headline-md text-headline-md mb-2 text-zinc-900 dark:text-white"
      >
        Thank you for your order!
      </h2>
      <p class="text-on-surface-variant mb-6">
        Your order
        <strong class="text-[#00a046] font-bold">{{
          orderSuccessData?.order_number
        }}</strong>
        has been successfully placed.
      </p>
      <div
        class="bg-surface-container-low rounded-xl p-6 mb-8 text-left space-y-3 border border-outline-variant"
      >
        <div class="flex justify-between text-sm">
          <span class="text-on-surface-variant">Amount to Pay:</span>
          <span class="font-black text-[#00a046]">{{
            formatPrice(orderSuccessData?.total_price)
          }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-on-surface-variant">Customer Name:</span>
          <span class="font-semibold text-zinc-900 dark:text-white">{{
            orderSuccessData?.customer_name
          }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-on-surface-variant">Payment Method:</span>
          <span
            class="font-semibold uppercase text-zinc-700 dark:text-zinc-300"
          >
            {{
              orderSuccessData?.payment_method === "cod"
                ? "Cash on Delivery"
                : orderSuccessData?.payment_method === "card"
                  ? "Online Card"
                  : "Bank Transfer"
            }}
          </span>
        </div>
      </div>
      <button
        class="bg-[#00a046] hover:bg-[#00b050] text-white px-8 py-3 rounded-lg font-bold transition-all shadow-sm"
        type="button"
        @click="router.push('/catalog')"
      >
        Continue Shopping
      </button>
    </div>

    <!-- Cart / Checkout State -->
    <div v-else>
      <div
        class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-stack-lg"
      >
        <div>
          <h1
            class="font-headline-lg text-headline-lg text-zinc-900 dark:text-white"
          >
            {{ isCheckoutMode ? "Secure Checkout" : "Your Shopping Cart" }}
          </h1>
          <p class="text-on-surface-variant mt-2">
            {{
              isCheckoutMode
                ? "Please fill in your delivery details to finalize the order."
                : `${store.cartCount} items ready for secure checkout.`
            }}
          </p>
        </div>
        <button
          class="text-primary font-bold text-sm hover:underline flex items-center gap-1"
          type="button"
          @click="
            isCheckoutMode
              ? (isCheckoutMode = false)
              : router.push({ name: 'catalog' })
          "
        >
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          {{ isCheckoutMode ? "Back to Cart" : "Continue Shopping" }}
        </button>
      </div>

      <div
        v-if="store.cart.length === 0"
        class="bg-surface-container-lowest rounded-xl border border-outline-variant p-12 text-center"
      >
        <div
          class="w-20 h-20 mx-auto rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant mb-5"
        >
          <span class="material-symbols-outlined text-[44px]">remove_shopping_cart</span>
        </div>
        <h2 class="font-headline-md text-headline-md mb-2">
          Your cart is empty
        </h2>
        <p class="text-on-surface-variant mb-6">
          Add premium electronics to start checkout.
        </p>
        <button
          class="bg-primary text-on-primary px-8 py-3 rounded-lg font-bold hover:bg-primary-container transition-all"
          type="button"
          @click="router.push({ name: 'catalog' })"
        >
          Explore Catalog
        </button>
      </div>

      <div
        v-else
        class="cart-layout"
      >
        <!-- Left Column -->
        <section class="space-y-stack-md">
          <!-- Checkout Details Form -->
          <div
            v-if="isCheckoutMode"
            class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant space-y-6"
          >
            <h2
              class="font-title-lg text-title-lg text-zinc-900 dark:text-white border-b border-outline-variant pb-3 flex items-center gap-2"
            >
              <span class="material-symbols-outlined text-primary">contact_mail</span>
              Shipping and Contact Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col gap-1.5">
                <label
                  class="font-label-md text-on-surface-variant"
                  for="customer_name"
                >Full Name *</label>
                <input
                  id="customer_name"
                  v-model="checkoutForm.customer_name"
                  type="text"
                  placeholder="John Doe"
                  class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
                  required
                >
              </div>
              <div class="flex flex-col gap-1.5">
                <label
                  class="font-label-md text-on-surface-variant"
                  for="customer_phone"
                >Phone *</label>
                <input
                  id="customer_phone"
                  v-model="checkoutForm.customer_phone"
                  type="tel"
                  placeholder="+380991234567"
                  class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
                  required
                >
              </div>
            </div>

            <div class="flex flex-col gap-1.5">
              <label
                class="font-label-md text-on-surface-variant"
                for="customer_email"
              >Email Address *</label>
              <input
                id="customer_email"
                v-model="checkoutForm.customer_email"
                type="email"
                placeholder="john@example.com"
                class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
                required
              >
            </div>

            <h2
              class="font-title-lg text-title-lg text-zinc-900 dark:text-white border-b border-outline-variant pt-4 pb-3 flex items-center gap-2"
            >
              <span class="material-symbols-outlined text-primary">local_shipping</span>
              Delivery Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col gap-1.5">
                <label
                  class="font-label-md text-on-surface-variant"
                  for="delivery_method"
                >Delivery Method *</label>
                <select
                  id="delivery_method"
                  v-model="checkoutForm.delivery_method"
                  class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary dark:text-white"
                >
                  <option value="nova_poshta">
                    Nova Poshta Branch
                  </option>
                  <option value="ukr_poshta">
                    Ukrposhta
                  </option>
                  <option value="courier">
                    Address Courier
                  </option>
                  <option value="pickup">
                    Self-pickup from Shop
                  </option>
                </select>
              </div>
              <div class="flex flex-col gap-1.5">
                <label
                  class="font-label-md text-on-surface-variant"
                  for="payment_method"
                >Payment Method *</label>
                <select
                  id="payment_method"
                  v-model="checkoutForm.payment_method"
                  class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary dark:text-white"
                >
                  <option value="cod">
                    Cash on Delivery
                  </option>
                  <option value="card">
                    Online Payment by Card
                  </option>
                  <option value="bank">
                    Bank Wire Transfer
                  </option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex flex-col gap-1.5">
                <label
                  class="font-label-md text-on-surface-variant"
                  for="shipping_city"
                >City *</label>
                <input
                  id="shipping_city"
                  v-model="checkoutForm.shipping_city"
                  type="text"
                  placeholder="Kyiv"
                  class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
                  required
                >
              </div>
              <div class="flex flex-col gap-1.5">
                <label
                  class="font-label-md text-on-surface-variant"
                  for="shipping_address"
                >Address / Branch Number *</label>
                <input
                  id="shipping_address"
                  v-model="checkoutForm.shipping_address"
                  type="text"
                  placeholder="Nova Poshta Branch №14"
                  class="bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
                  required
                >
              </div>
            </div>
          </div>

          <!-- Cart Item list -->
          <div
            v-else
            class="space-y-stack-md"
          >
            <article
              v-for="item in store.cart"
              :key="item.id"
              class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant flex flex-col md:flex-row gap-6"
            >
              <div
                class="w-full md:w-32 h-32 bg-surface-container rounded-lg overflow-hidden flex-shrink-0 p-3"
              >
                <img
                  class="w-full h-full object-contain"
                  :src="item.image"
                  :alt="item.name"
                >
              </div>
              <div class="flex-grow flex flex-col justify-between gap-5">
                <div
                  class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3"
                >
                  <div>
                    <h3
                      class="font-title-lg text-title-lg text-zinc-900 dark:text-white"
                    >
                      {{ item.name }}
                    </h3>
                    <p class="font-body-md text-on-surface-variant mt-1">
                      {{ item.sku }} | Premium Product
                    </p>
                  </div>
                  <span class="font-price-lg text-price-lg text-[#00a046]">{{
                    formatPrice(item.price * item.quantity)
                  }}</span>
                </div>
                <div
                  class="flex flex-col lg:flex-row lg:items-center justify-between gap-4"
                >
                  <div class="flex flex-wrap items-center gap-4">
                    <div
                      class="flex items-center border border-outline-variant rounded-lg overflow-hidden"
                    >
                      <button
                        class="px-3 py-1 hover:bg-surface-variant transition-colors text-zinc-700 dark:text-zinc-300"
                        type="button"
                        @click="
                          store.updateCartQuantity(item.id, item.quantity - 1)
                        "
                      >
                        <span class="material-symbols-outlined text-body-md">remove</span>
                      </button>
                      <span
                        class="px-4 font-title-md text-zinc-900 dark:text-white"
                      >{{ item.quantity }}</span>
                      <button
                        class="px-3 py-1 hover:bg-surface-variant transition-colors text-zinc-700 dark:text-zinc-300"
                        type="button"
                        @click="
                          store.updateCartQuantity(item.id, item.quantity + 1)
                        "
                      >
                        <span class="material-symbols-outlined text-body-md">add</span>
                      </button>
                    </div>
                    <button
                      class="text-error font-label-md flex items-center gap-1 hover:underline"
                      type="button"
                      @click="store.removeFromCart(item.id)"
                    >
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                      Remove
                    </button>
                  </div>
                  <div
                    class="text-on-surface-variant font-label-md flex items-center gap-1"
                  >
                    <span
                      class="material-symbols-outlined text-[#00a046] text-[18px]"
                    >check_circle</span>
                    Available
                  </div>
                </div>
              </div>
            </article>
          </div>

          <div
            v-if="!isCheckoutMode"
            class="mt-stack-lg pt-stack-lg border-t border-outline-variant"
          >
            <h2
              class="font-headline-md text-headline-md mb-6 text-zinc-900 dark:text-white"
            >
              Saved for later (1)
            </h2>
            <div
              class="bg-surface-container-low p-4 rounded-xl border border-dashed border-outline-variant flex items-center gap-4 opacity-80"
            >
              <div
                class="w-16 h-16 bg-surface-variant rounded-lg flex-shrink-0 overflow-hidden"
              >
                <img
                  class="w-full h-full object-cover"
                  :src="savedItem.image"
                  :alt="savedItem.name"
                >
              </div>
              <div class="flex-grow">
                <h4
                  class="font-title-md text-title-md text-zinc-800 dark:text-zinc-200"
                >
                  {{ savedItem.name }}
                </h4>
                <p class="font-label-md text-on-surface-variant">
                  {{ formatPrice(savedItem.price) }}
                </p>
              </div>
              <button
                class="px-4 py-2 bg-secondary-container text-on-secondary-container rounded-full font-label-md hover:opacity-90 transition-opacity"
                type="button"
              >
                Move to Cart
              </button>
            </div>
          </div>
        </section>

        <!-- Right Column (Sticky Summary) -->
        <aside>
          <div
            class="bg-surface-container-low rounded-xl p-6 border border-outline-variant sticky top-24"
          >
            <h2
              class="font-headline-md text-headline-md mb-6 text-zinc-900 dark:text-white"
            >
              Order Summary
            </h2>
            <div class="space-y-4 mb-6">
              <div class="flex justify-between font-body-lg text-body-lg">
                <span class="text-on-surface-variant">Subtotal</span>
                <span class="font-semibold text-zinc-900 dark:text-white">{{
                  formatPrice(store.cartTotal)
                }}</span>
              </div>
              <div
                v-if="discount > 0"
                class="flex justify-between font-body-lg text-body-lg text-primary"
              >
                <span>Promo Discount</span>
                <span class="font-semibold">-{{ formatPrice(discount) }}</span>
              </div>
              <div class="flex justify-between font-body-lg text-body-lg">
                <span class="text-on-surface-variant">Shipping Estimate</span>
                <span class="font-semibold text-zinc-900 dark:text-white">{{
                  shipping === 0 ? "FREE" : formatPrice(shipping)
                }}</span>
              </div>
              <div class="flex justify-between font-body-lg text-body-lg">
                <span class="text-on-surface-variant">Tax Estimate</span>
                <span class="font-semibold text-zinc-900 dark:text-white">{{
                  formatPrice(tax)
                }}</span>
              </div>
              <div
                class="pt-4 border-t border-outline-variant flex justify-between font-headline-md text-headline-md text-[#00a046]"
              >
                <span>Total</span>
                <span>{{ formatPrice(total) }}</span>
              </div>
            </div>

            <div
              v-if="!isCheckoutMode"
              class="mb-6"
            >
              <label
                class="block font-label-md text-on-surface-variant mb-2"
                for="promo"
              >Promo Code</label>
              <div class="flex gap-2">
                <input
                  id="promo"
                  v-model="promoCode"
                  class="flex-grow bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary dark:text-white"
                  placeholder="Enter code"
                  type="text"
                >
                <button
                  class="px-4 py-2 border border-on-surface text-on-surface rounded-lg font-label-md hover:bg-surface-variant transition-colors dark:text-white"
                  type="button"
                  @click="applyPromo"
                >
                  Apply
                </button>
              </div>
            </div>

            <!-- Primary Checkout Button -->
            <button
              class="w-full py-4 bg-[#00a046] hover:bg-[#00b050] text-white rounded-xl font-headline-md text-headline-md shadow-md active:scale-95 transition-all mb-4 uppercase tracking-wider flex items-center justify-center gap-2"
              type="button"
              :disabled="isSubmitting"
              @click="
                isCheckoutMode ? handlePlaceOrder() : (isCheckoutMode = true)
              "
            >
              <span
                v-if="isSubmitting"
                class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"
              />
              <span
                v-else
                class="material-symbols-outlined text-[20px]"
              >shopping_cart_checkout</span>
              {{
                isSubmitting
                  ? "Processing..."
                  : isCheckoutMode
                    ? "Place Order"
                    : "Proceed to Checkout"
              }}
            </button>

            <div
              class="flex items-center justify-center gap-2 text-on-surface-variant font-label-md"
            >
              <span class="material-symbols-outlined text-[18px]">lock</span>
              Secure Checkout with SSL Encryption
            </div>
          </div>
        </aside>
      </div>

      <!-- Recommended section -->
      <section class="mt-20">
        <h2
          class="font-headline-md text-headline-md mb-8 text-zinc-900 dark:text-white"
        >
          Recommended for you
        </h2>
        <div class="recommended-grid">
          <article
            v-for="product in recommended"
            :key="product.id"
            class="group bg-surface-container-lowest rounded-xl border border-outline-variant p-4 hover:shadow-lg transition-all"
          >
            <div
              class="aspect-square bg-surface-container rounded-lg mb-4 overflow-hidden"
            >
              <img
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                :src="product.image"
                :alt="product.name"
              >
            </div>
            <div class="flex justify-between items-start mb-2">
              <h4
                class="font-title-md text-title-md line-clamp-1 text-zinc-800 dark:text-zinc-200"
              >
                {{ product.name }}
              </h4>
              <button
                class="text-on-surface-variant hover:text-primary"
                type="button"
                @click="store.toggleWishlist(product)"
              >
                <span class="material-symbols-outlined">favorite</span>
              </button>
            </div>
            <div class="flex items-center gap-1 mb-2">
              <span
                v-for="star in 5"
                :key="star"
                class="material-symbols-outlined text-[16px]"
                :class="
                  star <= product.rating
                    ? 'text-star-rating filled-symbol'
                    : 'text-outline-variant'
                "
              >star</span>
              <span class="text-label-md text-on-surface-variant ml-1">({{ product.reviews }})</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="font-title-lg text-title-lg text-[#00a046]">{{
                formatPrice(product.price)
              }}</span>
              <button
                class="bg-secondary-container text-on-secondary-container p-2 rounded-full hover:bg-primary hover:text-on-primary transition-colors"
                type="button"
                @click="addRecommended(product)"
              >
                <span class="material-symbols-outlined">add_shopping_cart</span>
              </button>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>

<style scoped>
.cart-layout {
  display: grid;
  gap: 24px;
  grid-template-columns: 1fr;
}
.recommended-grid {
  display: grid;
  gap: 24px;
  grid-template-columns: 1fr;
}
.filled-symbol {
  font-variation-settings: "FILL" 1;
}
@media (min-width: 768px) {
  .recommended-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (min-width: 1024px) {
  .cart-layout {
    grid-template-columns: minmax(0, 8fr) minmax(320px, 4fr);
  }
  .recommended-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}
</style>
