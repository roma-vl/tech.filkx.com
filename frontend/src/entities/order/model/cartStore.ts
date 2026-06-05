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
  viewedDetailed: any[];
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
    viewedDetailed:
      typeof window !== "undefined" &&
      localStorage.getItem("electro_viewed_detailed")
        ? JSON.parse(localStorage.getItem("electro_viewed_detailed")!)
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
      return state.cart.reduce(
        (acc, item) => acc + item.price * item.quantity,
        0,
      );
    },
    wishlistCount: (state): number => {
      return state.wishlist.length;
    },
    compareCount: (state): number => {
      return state.compare.length;
    },
  },

  actions: {
    addToast(
      message: string,
      type: "success" | "info" | "warning" | "error" = "success",
    ) {
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

    async toggleWishlist(product: Product | any) {
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

      // Sync to database if logged in
      if (typeof window !== "undefined" && localStorage.getItem("filkx_auth")) {
        try {
          const response = await authApi.toggleFavorite(product.id);
          if (response.data && response.data.status === "success") {
            this.wishlist = response.data.data.map((p: any) => {
              const mainVariant = p.variants?.[0];
              const price = mainVariant
                ? parseFloat(mainVariant.price)
                : p.price || 0;
              let image = p.image || "";
              if (
                !image &&
                mainVariant &&
                mainVariant.dimensions &&
                mainVariant.dimensions.images
              ) {
                const primary =
                  mainVariant.dimensions.images.find(
                    (img: any) => img.isPrimary,
                  ) || mainVariant.dimensions.images[0];
                if (primary && primary.url) image = primary.url;
              }
              return {
                id: p.id,
                name: p.name?.uk || p.name?.en || p.name,
                slug: p.slug,
                price: price,
                image:
                  image ||
                  "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop",
                category:
                  p.categories?.[0]?.name?.uk ||
                  p.categories?.[0]?.name?.en ||
                  "Electronics",
              };
            });
            localStorage.setItem(
              "electro_wishlist",
              JSON.stringify(this.wishlist),
            );
          }
        } catch (e) {
          console.error("Failed to toggle favorite in database", e);
        }
      }
    },

    isInWishlist(productId: number): boolean {
      return this.wishlist.some((item) => item.id === productId);
    },

    async toggleCompare(product: Product | any) {
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
          slug: product.slug,
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

      // Sync to database if logged in
      if (typeof window !== "undefined" && localStorage.getItem("filkx_auth")) {
        try {
          const response = await authApi.toggleCompare(product.id);
          if (response.data && response.data.status === "success") {
            this.compare = response.data.data.map((p: any) => {
              const mainVariant = p.variants?.[0];
              const price = mainVariant
                ? parseFloat(mainVariant.price)
                : p.price || 0;
              let image = p.image || "";
              if (
                !image &&
                mainVariant &&
                mainVariant.dimensions &&
                mainVariant.dimensions.images
              ) {
                const primary =
                  mainVariant.dimensions.images.find(
                    (img: any) => img.isPrimary,
                  ) || mainVariant.dimensions.images[0];
                if (primary && primary.url) image = primary.url;
              }
              return {
                id: p.id,
                name: p.name?.uk || p.name?.en || p.name,
                slug: p.slug,
                price: price,
                image:
                  image ||
                  "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop",
                category:
                  p.categories?.[0]?.name?.uk ||
                  p.categories?.[0]?.name?.en ||
                  "Electronics",
                rating: p.rating || 4.5,
                reviews: p.reviews || 0,
                description:
                  p.description?.uk || p.description?.en || p.description || "",
                specs:
                  p.attributeValues?.map((av: any) => [
                    av.attribute?.name?.uk || av.attribute?.name?.en || "",
                    av.customValue ||
                      av.attributeValue?.value?.uk ||
                      av.attributeValue?.value ||
                      "",
                  ]) || [],
              };
            });
            localStorage.setItem(
              "electro_compare",
              JSON.stringify(this.compare),
            );
          }
        } catch (e) {
          console.error("Failed to toggle compare in database", e);
        }
      }
    },

    isInCompare(productId: number): boolean {
      return this.compare.some((item) => item.id === productId);
    },

    async removeFromCompare(productId: number) {
      const index = this.compare.findIndex((item) => item.id === productId);
      if (index !== -1) {
        const name = this.compare[index].name;
        this.compare.splice(index, 1);
        this.addToast(`Removed ${name} from compare.`, "info");
        if (typeof window !== "undefined") {
          localStorage.setItem("electro_compare", JSON.stringify(this.compare));
        }

        // Sync to database if logged in
        if (
          typeof window !== "undefined" &&
          localStorage.getItem("filkx_auth")
        ) {
          try {
            await authApi.toggleCompare(productId);
          } catch (e) {
            console.error("Failed to remove compare in database", e);
          }
        }
      }
    },

    openDrawer(drawerName: "cart" | "wishlist" | "compare" | "account") {
      this.activeDrawer = drawerName;
    },

    closeDrawer() {
      this.activeDrawer = null;
    },

    async trackProductView(productOrId: number | any) {
      if (typeof window === "undefined" || !productOrId) return;

      const productId =
        typeof productOrId === "object" ? productOrId.id : productOrId;

      // 1. Detailed tracking (new feature)
      let viewed: any[] = [];
      try {
        viewed = JSON.parse(
          localStorage.getItem("electro_viewed_detailed") || "[]",
        );
      } catch (e) {
        viewed = [];
      }
      if (!Array.isArray(viewed)) viewed = [];

      const existingIndex = viewed.findIndex((item) => item.id === productId);
      if (existingIndex !== -1) {
        const existing = viewed[existingIndex];
        existing.viewCount = (existing.viewCount || 0) + 1;
        existing.lastViewedAt = new Date().toISOString();

        // Move to the beginning of the list
        viewed.splice(existingIndex, 1);
        viewed.unshift(existing);
      } else {
        let productDetails: any = {};
        if (typeof productOrId === "object") {
          const mainVariant = productOrId.variants?.[0];
          const price = mainVariant
            ? parseFloat(mainVariant.price)
            : productOrId.price || 0;
          let image = productOrId.image || "";
          if (
            !image &&
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
          if (!image) {
            image =
              "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop";
          }
          const productCategory =
            productOrId.categories?.[0]?.name?.uk ||
            productOrId.categories?.[0]?.name?.en ||
            productOrId.category ||
            "Різне";

          productDetails = {
            id: productOrId.id,
            slug: productOrId.slug,
            name:
              productOrId.name?.uk || productOrId.name?.en || productOrId.name,
            brand: productOrId.brand?.name || productOrId.brand || "Unknown",
            image: image,
            price: price,
            category: productCategory,
            inStock: productOrId.stock > 0 || productOrId.inStock || true,
          };
        } else {
          // If only ID is supplied, try to find product in other store collections
          const foundInStore =
            this.compare.find((p) => p.id === productId) ||
            this.wishlist.find((p) => p.id === productId) ||
            this.cart.find((p) => p.productId === productId);
          if (foundInStore) {
            productDetails = {
              id: foundInStore.id,
              slug: foundInStore.slug,
              name: foundInStore.name,
              brand: foundInStore.brand || "Unknown",
              image: foundInStore.image,
              price: foundInStore.price,
              category: foundInStore.category || "Різне",
              inStock: foundInStore.inStock ?? true,
            };
          } else {
            productDetails = {
              id: productId,
              name: "Товар #" + productId,
              price: 0,
              image: "",
              category: "Різне",
              inStock: true,
            };
          }
        }

        viewed.unshift({
          ...productDetails,
          viewCount: 1,
          lastViewedAt: new Date().toISOString(),
        });
      }

      // Limit to 50 items
      viewed = viewed.slice(0, 50);
      this.viewedDetailed = viewed;
      localStorage.setItem("electro_viewed_detailed", JSON.stringify(viewed));

      // 2. Legacy tracking (for backwards compatibility)
      try {
        let viewedLegacy = JSON.parse(
          localStorage.getItem("electro_viewed") || "[]",
        );
        if (Array.isArray(viewedLegacy)) {
          viewedLegacy = viewedLegacy.filter((id: number) => id !== productId);
          viewedLegacy.unshift(productId);
          viewedLegacy = viewedLegacy.slice(0, 10);
          localStorage.setItem("electro_viewed", JSON.stringify(viewedLegacy));
        }
      } catch (e) {
        console.warn("Failed to update legacy views storage", e);
      }

      // 3. Database tracking if logged in
      if (typeof window !== "undefined" && localStorage.getItem("filkx_auth")) {
        try {
          await authApi.trackViewedProduct(productId);
        } catch (e) {
          console.error("Failed to track view in database", e);
        }
      }
    },

    async removeViewedItem(productId: number | string) {
      this.viewedDetailed = this.viewedDetailed.filter(
        (p) => p.id !== productId,
      );
      if (typeof window !== "undefined") {
        localStorage.setItem(
          "electro_viewed_detailed",
          JSON.stringify(this.viewedDetailed),
        );
        const legacyIds = this.viewedDetailed.slice(0, 10).map((p) => p.id);
        localStorage.setItem("electro_viewed", JSON.stringify(legacyIds));
      }

      if (typeof window !== "undefined" && localStorage.getItem("filkx_auth")) {
        try {
          await authApi.clearViewedProducts();
          if (this.viewedDetailed.length > 0) {
            await authApi.syncViewedProducts(this.viewedDetailed);
          }
        } catch (e) {
          console.error("Failed to remove viewed item from database", e);
        }
      }
    },

    async clearViewedHistory() {
      this.viewedDetailed = [];
      if (typeof window !== "undefined") {
        localStorage.removeItem("electro_viewed_detailed");
        localStorage.removeItem("electro_viewed");
      }

      if (typeof window !== "undefined" && localStorage.getItem("filkx_auth")) {
        try {
          await authApi.clearViewedProducts();
        } catch (e) {
          console.error("Failed to clear viewed history in database", e);
        }
      }
    },

    async syncUserLists() {
      if (typeof window === "undefined" || !localStorage.getItem("filkx_auth"))
        return;

      try {
        const localWishlistIds = this.wishlist.map((p) => p.id);
        const favResponse = await authApi.syncFavorites(localWishlistIds);
        if (favResponse.data && favResponse.data.status === "success") {
          this.wishlist = favResponse.data.data.map((p: any) => {
            const mainVariant = p.variants?.[0];
            const price = mainVariant
              ? parseFloat(mainVariant.price)
              : p.price || 0;
            let image = p.image || "";
            if (
              !image &&
              mainVariant &&
              mainVariant.dimensions &&
              mainVariant.dimensions.images
            ) {
              const primary =
                mainVariant.dimensions.images.find(
                  (img: any) => img.isPrimary,
                ) || mainVariant.dimensions.images[0];
              if (primary && primary.url) image = primary.url;
            }
            return {
              id: p.id,
              name: p.name?.uk || p.name?.en || p.name,
              slug: p.slug,
              price: price,
              image:
                image ||
                "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop",
              category:
                p.categories?.[0]?.name?.uk ||
                p.categories?.[0]?.name?.en ||
                "Electronics",
            };
          });
          localStorage.setItem(
            "electro_wishlist",
            JSON.stringify(this.wishlist),
          );
        }
      } catch (e) {
        console.error("Failed to sync favorites with database", e);
      }

      try {
        const localCompareIds = this.compare.map((p) => p.id);
        const compResponse = await authApi.syncCompares(localCompareIds);
        if (compResponse.data && compResponse.data.status === "success") {
          this.compare = compResponse.data.data.map((p: any) => {
            const mainVariant = p.variants?.[0];
            const price = mainVariant
              ? parseFloat(mainVariant.price)
              : p.price || 0;
            let image = p.image || "";
            if (
              !image &&
              mainVariant &&
              mainVariant.dimensions &&
              mainVariant.dimensions.images
            ) {
              const primary =
                mainVariant.dimensions.images.find(
                  (img: any) => img.isPrimary,
                ) || mainVariant.dimensions.images[0];
              if (primary && primary.url) image = primary.url;
            }
            return {
              id: p.id,
              name: p.name?.uk || p.name?.en || p.name,
              slug: p.slug,
              price: price,
              image:
                image ||
                "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop",
              category:
                p.categories?.[0]?.name?.uk ||
                p.categories?.[0]?.name?.en ||
                "Electronics",
              rating: p.rating || 4.5,
              reviews: p.reviews || 0,
              description:
                p.description?.uk || p.description?.en || p.description || "",
              specs:
                p.attributeValues?.map((av: any) => [
                  av.attribute?.name?.uk || av.attribute?.name?.en || "",
                  av.customValue ||
                    av.attributeValue?.value?.uk ||
                    av.attributeValue?.value ||
                    "",
                ]) || [],
            };
          });
          localStorage.setItem("electro_compare", JSON.stringify(this.compare));
        }
      } catch (e) {
        console.error("Failed to sync compares with database", e);
      }

      try {
        let localViewedDetailed: any[] = [];
        try {
          localViewedDetailed = JSON.parse(
            localStorage.getItem("electro_viewed_detailed") || "[]",
          );
        } catch (e) {
          localViewedDetailed = [];
        }
        if (!Array.isArray(localViewedDetailed)) localViewedDetailed = [];

        const viewedResponse =
          await authApi.syncViewedProducts(localViewedDetailed);
        if (viewedResponse.data && viewedResponse.data.status === "success") {
          const mappedViewed = viewedResponse.data.data.map((p: any) => {
            const mainVariant = p.variants?.[0];
            const price = mainVariant
              ? parseFloat(mainVariant.price)
              : p.price || 0;
            let image = p.image || "";
            if (
              !image &&
              mainVariant &&
              mainVariant.dimensions &&
              mainVariant.dimensions.images
            ) {
              const primary =
                mainVariant.dimensions.images.find(
                  (img: any) => img.isPrimary,
                ) || mainVariant.dimensions.images[0];
              if (primary && primary.url) image = primary.url;
            }
            return {
              id: p.id,
              name: p.name?.uk || p.name?.en || p.name,
              brand: p.brand?.name || "Unknown",
              slug: p.slug,
              price: price,
              image:
                image ||
                "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop",
              category:
                p.categories?.[0]?.name?.uk ||
                p.categories?.[0]?.name?.en ||
                "Різне",
              inStock: p.stock > 0 || true,
              viewCount: p.view_count || 1,
              lastViewedAt: p.last_viewed_at || new Date().toISOString(),
            };
          });
          this.viewedDetailed = mappedViewed;
          localStorage.setItem(
            "electro_viewed_detailed",
            JSON.stringify(mappedViewed),
          );

          const legacyIds = mappedViewed.slice(0, 10).map((p: any) => p.id);
          localStorage.setItem("electro_viewed", JSON.stringify(legacyIds));
        }
      } catch (e) {
        console.error("Failed to sync viewed products with database", e);
      }
    },

    async viewProduct(product: Product | null = null) {
      this.selectedProduct = product;
      this.currentPage = "product";
      if (product && typeof window !== "undefined") {
        this.trackProductView(product);
        const { default: router } = await import("@/router");
        router.push({
          name: "product-detail",
          params: { id: product.slug || product.id },
        });
      }
    },

    async fetchUnreadNotificationsCount() {
      try {
        const response = await authApi.getNotifications();
        const list = response.data?.data?.data || response.data?.data || [];
        this.unreadNotificationsCount = list.filter(
          (n: any) => !n.readAt && !n.read_at,
        ).length;
      } catch (e) {
        console.warn("Failed to fetch unread notifications count", e);
      }
    },
  },
});
export default useCartStore;
