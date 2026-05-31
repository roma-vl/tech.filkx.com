import { defineStore } from "pinia";
import { orderApi } from "@/shared/services/api/orderApi";
import { authApi } from "@/shared/services/api/authApi";
import { CartItem } from "../types";
import { Product } from "@/entities/product/types";

export interface ToastMessage {
  id: number;
  message: string;
  type: "success" | "info" | "warning" | "error";
}

export interface CartState {
  currentPage: string;
  selectedProduct: Product | null;
  cart: CartItem[];
  wishlist: any[];
  compare: any[];
  toasts: ToastMessage[];
  unreadNotificationsCount: number;
  activeDrawer: "cart" | "wishlist" | "compare" | "account" | null;
  toastId: number;
}

export const useCartStore = defineStore("cart", {
  state: (): CartState => ({
    currentPage: "home",
    selectedProduct: null,
    cart: [],
    wishlist:
      typeof window !== "undefined" && localStorage.getItem("electro_wishlist")
        ? JSON.parse(localStorage.getItem("electro_wishlist")!)
        : [],
    compare:
      typeof window !== "undefined" && localStorage.getItem("electro_compare")
        ? JSON.parse(localStorage.getItem("electro_compare")!)
        : [],
    toasts: [],
    unreadNotificationsCount: 0,
    activeDrawer: null,
    toastId: 0,
  }),

  getters: {
    cartCount: (state): number => {
      return state.cart.reduce((acc, item) => acc + item.quantity, 0);
    },
    cartTotal: (state): number => {
      return state.cart.reduce((acc, item) => acc + item.price * item.quantity, 0);
    },
    wishlistCount: (state): number => {
      return state.wishlist.length;
    },
    compareCount: (state): number => {
      return state.compare.length;
    },
  },

  actions: {
    addToast(message: string, type: "success" | "info" | "warning" | "error" = "success") {
      const id = this.toastId++;
      this.toasts.push({ id, message, type });
      setTimeout(() => {
        this.removeToast(id);
      }, 3000);
    },

    removeToast(id: number) {
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
            const mergeResponse = await orderApi.mergeCart(sessionId);
            if (mergeResponse.data && mergeResponse.data.status === "success") {
              localStorage.setItem("cart_merged", "1");
              this.cart = mergeResponse.data.data.items || [];
              return;
            }
          } catch (mergeError) {
            console.warn("Failed to merge cart on backend", mergeError);
          }
        }

        const response = await orderApi.getCart();
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

    async addToCart(product: Product | { id: number; name: string }) {
      try {
        const response = await orderApi.addToCart(product.id, 1);
        if (response.data && response.data.status === "success") {
          this.cart = response.data.data.items || [];
          this.addToast(`Додано ${product.name} до кошика.`);
        }
      } catch (e) {
        console.error("Failed to add to cart", e);
        this.addToast("Помилка при додаванні товару до кошика", "error");
      }
    },

    async removeFromCart(productId: number | string) {
      try {
        const response = await orderApi.removeFromCart(productId);
        if (response.data && response.data.status === "success") {
          this.cart = response.data.data.items || [];
          this.addToast("Товар видалено з кошика.", "info");
        }
      } catch (e) {
        console.error("Failed to remove from cart", e);
        this.addToast("Помилка при видаленні товару з кошика", "error");
      }
    },

    async updateCartQuantity(productId: number | string, quantity: number) {
      try {
        if (quantity < 1) return;
        const response = await orderApi.updateCartItem(productId, quantity);
        if (response.data && response.data.status === "success") {
          this.cart = response.data.data.items || [];
        }
      } catch (e) {
        console.error("Failed to update cart quantity", e);
      }
    },

    toggleWishlist(product: Product | any) {
      const index = this.wishlist.findIndex((item) => item.id === product.id);
      if (index !== -1) {
        const name = this.wishlist[index].name;
        this.wishlist.splice(index, 1);
        this.addToast(`Removed ${name} from wishlist.`, "info");
      } else {
        this.wishlist.push({
          id: product.id,
          name: product.name,
          slug: product.slug,
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

    isInWishlist(productId: number): boolean {
      return this.wishlist.some((item) => item.id === productId);
    },

    toggleCompare(product: Product | any) {
      const index = this.compare.findIndex((item) => item.id === product.id);
      if (index !== -1) {
        const name = this.compare[index].name;
        this.compare.splice(index, 1);
        this.addToast(`Removed ${name} from compare.`, "info");
      } else {
        if (this.compare.length >= 12) {
          this.addToast("Ви можете порівнювати не більше 12 товарів одночасно.", "warning");
          return;
        }
        this.compare.push({
          id: product.id,
          name: product.name,
          slug: product.slug,
          price: product.price,
          image: product.image,
          category: product.category || "Electronics",
          rating: product.rating || 4.5,
          reviews: product.reviews || 0,
          description: product.description || "Premium build quality, ultra-durable, leading technology.",
          specs: product.specs || [],
        });
        this.addToast(`Added ${product.name} to compare.`);
      }
      if (typeof window !== "undefined") {
        localStorage.setItem("electro_compare", JSON.stringify(this.compare));
      }
    },

    isInCompare(productId: number): boolean {
      return this.compare.some((item) => item.id === productId);
    },

    removeFromCompare(productId: number) {
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

    openDrawer(drawerName: "cart" | "wishlist" | "compare" | "account") {
      this.activeDrawer = drawerName;
    },

    closeDrawer() {
      this.activeDrawer = null;
    },

    trackProductView(productId: number) {
      if (typeof window !== "undefined" && productId) {
        let viewed = JSON.parse(localStorage.getItem("electro_viewed") || "[]");
        viewed = viewed.filter((id: number) => id !== productId);
        viewed.unshift(productId);
        viewed = viewed.slice(0, 10);
        localStorage.setItem("electro_viewed", JSON.stringify(viewed));
      }
    },

    async viewProduct(product: Product | null = null) {
      this.selectedProduct = product;
      this.currentPage = "product";
      if (product && typeof window !== "undefined") {
        this.trackProductView(product.id);
        const { default: router } = await import("@/router");
        router.push({ name: "product-detail", params: { id: product.slug || product.id } });
      }
    },

    async fetchUnreadNotificationsCount() {
      try {
        const response = await authApi.getNotifications();
        const list = response.data?.data?.data || response.data?.data || [];
        this.unreadNotificationsCount = list.filter((n: any) => !n.readAt && !n.read_at).length;
      } catch (e) {
        console.warn("Failed to fetch unread notifications count", e);
      }
    },
  },
});
export default useCartStore;
