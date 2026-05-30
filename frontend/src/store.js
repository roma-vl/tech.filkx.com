import { reactive } from "vue";
import api from "@/services/api.js";

export const store = reactive({
  // Navigation state
  currentPage: "home",
  selectedProduct: null,

  // Arrays for items
  cart: [],
  wishlist:
    typeof window !== "undefined" && localStorage.getItem("electro_wishlist")
      ? JSON.parse(localStorage.getItem("electro_wishlist"))
      : [],
  compare:
    typeof window !== "undefined" && localStorage.getItem("electro_compare")
      ? JSON.parse(localStorage.getItem("electro_compare"))
      : [],
  toasts: [],
  unreadNotificationsCount: 0,

  // Modals visibility state
  activeDrawer: null, // 'cart', 'wishlist', 'compare', 'account', or null

  // Toast notifications counter for unique IDs
  toastId: 0,

  // Getters (defined as JS getters on the reactive object)
  get cartCount() {
    return this.cart.reduce((acc, item) => acc + item.quantity, 0);
  },

  get cartTotal() {
    return this.cart.reduce((acc, item) => acc + item.price * item.quantity, 0);
  },

  get wishlistCount() {
    return this.wishlist.length;
  },

  get compareCount() {
    return this.compare.length;
  },

  // Actions
  addToast(message, type = "success") {
    const id = this.toastId++;
    this.toasts.push({ id, message, type });
    setTimeout(() => {
      this.removeToast(id);
    }, 3000);
  },

  removeToast(id) {
    const index = this.toasts.findIndex((t) => t.id === id);
    if (index !== -1) {
      this.toasts.splice(index, 1);
    }
  },

  async fetchCart() {
    try {
      if (typeof window === "undefined") return;
      const token = localStorage.getItem("filkx_auth");
      const sessionId = localStorage.getItem("cart_session_id");

      if (token && sessionId && !localStorage.getItem("cart_merged")) {
        try {
          const mergeResponse = await api.post("/v1/cart/merge", {
            session_id: sessionId,
          });
          if (mergeResponse.data && mergeResponse.data.status === "success") {
            localStorage.setItem("cart_merged", "1");
            this.cart = mergeResponse.data.data.items || [];
            return;
          }
        } catch (mergeError) {
          console.warn("Failed to merge cart on backend", mergeError);
        }
      }

      const response = await api.get("/v1/cart");
      if (response.data && response.data.status === "success") {
        this.cart = response.data.data.items || [];
      }
    } catch (e) {
      console.error("Failed to fetch cart", e);
    }
  },

  clearCart() {
    this.cart = [];
    if (typeof window !== "undefined") {
      localStorage.removeItem("cart_merged");
      localStorage.removeItem("cart_session_id");
    }
  },

  async addToCart(product) {
    try {
      const response = await api.post("/v1/cart", {
        variant_id: product.id,
        quantity: 1,
      });
      if (response.data && response.data.status === "success") {
        this.cart = response.data.data.items || [];
        this.addToast(`Додано ${product.name} до кошика.`);
      }
    } catch (e) {
      console.error("Failed to add to cart", e);
      this.addToast("Помилка при додаванні товару до кошика", "error");
    }
  },

  async removeFromCart(productId) {
    try {
      const response = await api.delete(`/v1/cart/items/${productId}`);
      if (response.data && response.data.status === "success") {
        this.cart = response.data.data.items || [];
        this.addToast("Товар видалено з кошика.", "info");
      }
    } catch (e) {
      console.error("Failed to remove from cart", e);
      this.addToast("Помилка при видаленні товару з кошика", "error");
    }
  },

  async updateCartQuantity(productId, quantity) {
    try {
      if (quantity < 1) return;
      const response = await api.put(`/v1/cart/items/${productId}`, {
        quantity,
      });
      if (response.data && response.data.status === "success") {
        this.cart = response.data.data.items || [];
      }
    } catch (e) {
      console.error("Failed to update cart quantity", e);
    }
  },

  toggleWishlist(product) {
    const index = this.wishlist.findIndex((item) => item.id === product.id);
    if (index !== -1) {
      const name = this.wishlist[index].name;
      this.wishlist.splice(index, 1);
      this.addToast(`Removed ${name} from wishlist.`, "info");
    } else {
      this.wishlist.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        category: product.category || "Electronics",
      });
      this.addToast(`Added ${product.name} to wishlist.`);
    }
    if (typeof window !== "undefined") {
      localStorage.setItem("electro_wishlist", JSON.stringify(this.wishlist));
    }
  },

  isInWishlist(productId) {
    return this.wishlist.some((item) => item.id === productId);
  },

  toggleCompare(product) {
    const index = this.compare.findIndex((item) => item.id === product.id);
    if (index !== -1) {
      const name = this.compare[index].name;
      this.compare.splice(index, 1);
      this.addToast(`Removed ${name} from compare.`, "info");
    } else {
      if (this.compare.length >= 12) {
        this.addToast(
          "Ви можете порівнювати не більше 12 товарів одночасно.",
          "warning",
        );
        return;
      }
      this.compare.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        category: product.category || "Electronics",
        rating: product.rating || 4.5,
        reviews: product.reviews || 0,
        description:
          product.description ||
          "Premium build quality, ultra-durable, leading technology.",
        specs: product.specs || [],
      });
      this.addToast(`Added ${product.name} to compare.`);
    }
    if (typeof window !== "undefined") {
      localStorage.setItem("electro_compare", JSON.stringify(this.compare));
    }
  },

  isInCompare(productId) {
    return this.compare.some((item) => item.id === productId);
  },

  removeFromCompare(productId) {
    const index = this.compare.findIndex((item) => item.id === productId);
    if (index !== -1) {
      const name = this.compare[index].name;
      this.compare.splice(index, 1);
      this.addToast(`Removed ${name} from compare.`, "info");
      if (typeof window !== "undefined") {
        localStorage.setItem("electro_compare", JSON.stringify(this.compare));
      }
    }
  },

  openDrawer(drawerName) {
    this.activeDrawer = drawerName;
  },

  closeDrawer() {
    this.activeDrawer = null;
  },

  trackProductView(productId) {
    if (typeof window !== "undefined" && productId) {
      let viewed = JSON.parse(localStorage.getItem("electro_viewed") || "[]");
      viewed = viewed.filter((id) => id !== productId);
      viewed.unshift(productId);
      viewed = viewed.slice(0, 10);
      localStorage.setItem("electro_viewed", JSON.stringify(viewed));
    }
  },

  async viewProduct(product = null) {
    this.selectedProduct = product;
    this.currentPage = "product";
    if (product && typeof window !== "undefined") {
      this.trackProductView(product.id);
      const { default: router } = await import("@/router");
      router.push({ name: "product-detail", params: { id: product.slug } });
    }
  },

  async fetchUnreadNotificationsCount() {
    try {
      const response = await api.get("/notifications");
      const list = response.data?.data?.data || response.data?.data || [];
      this.unreadNotificationsCount = list.filter(n => !n.read_at).length;
    } catch (e) {
      console.warn("Failed to fetch unread notifications count", e);
    }
  },
});

if (typeof window !== "undefined") {
  store.fetchCart();
}
