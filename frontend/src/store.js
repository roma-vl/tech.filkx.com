import { reactive } from "vue";
import { useCartStore } from "@/entities/order/model/cartStore";

export const store = reactive({
  get currentPage() {
    return useCartStore().currentPage;
  },
  set currentPage(v) {
    useCartStore().currentPage = v;
  },

  get selectedProduct() {
    return useCartStore().selectedProduct;
  },
  set selectedProduct(v) {
    useCartStore().selectedProduct = v;
  },

  get cart() {
    return useCartStore().cart;
  },
  set cart(v) {
    useCartStore().cart = v;
  },

  get wishlist() {
    return useCartStore().wishlist;
  },
  set wishlist(v) {
    useCartStore().wishlist = v;
  },

  get compare() {
    return useCartStore().compare;
  },
  set compare(v) {
    useCartStore().compare = v;
  },

  get toasts() {
    return useCartStore().toasts;
  },

  get unreadNotificationsCount() {
    return useCartStore().unreadNotificationsCount;
  },
  set unreadNotificationsCount(v) {
    useCartStore().unreadNotificationsCount = v;
  },

  get activeDrawer() {
    return useCartStore().activeDrawer;
  },
  set activeDrawer(v) {
    useCartStore().activeDrawer = v;
  },

  // Getters
  get cartCount() {
    return useCartStore().cartCount;
  },

  get cartTotal() {
    return useCartStore().cartTotal;
  },

  get wishlistCount() {
    return useCartStore().wishlistCount;
  },

  get compareCount() {
    return useCartStore().compareCount;
  },

  // Actions
  addToast(message, type = "success") {
    useCartStore().addToast(message, type);
  },

  removeToast(id) {
    useCartStore().removeToast(id);
  },

  fetchCart() {
    return useCartStore().fetchCart();
  },

  clearCart() {
    useCartStore().clearCart();
  },

  addToCart(product) {
    return useCartStore().addToCart(product);
  },

  removeFromCart(productId) {
    return useCartStore().removeFromCart(productId);
  },

  updateCartQuantity(productId, quantity) {
    return useCartStore().updateCartQuantity(productId, quantity);
  },

  toggleWishlist(product) {
    useCartStore().toggleWishlist(product);
  },

  isInWishlist(productId) {
    return useCartStore().isInWishlist(productId);
  },

  toggleCompare(product) {
    useCartStore().toggleCompare(product);
  },

  isInCompare(productId) {
    return useCartStore().isInCompare(productId);
  },

  removeFromCompare(productId) {
    useCartStore().removeFromCompare(productId);
  },

  openDrawer(drawerName) {
    useCartStore().openDrawer(drawerName);
  },

  closeDrawer() {
    useCartStore().closeDrawer();
  },

  trackProductView(productId) {
    useCartStore().trackProductView(productId);
  },

  viewProduct(product = null) {
    return useCartStore().viewProduct(product);
  },

  fetchUnreadNotificationsCount() {
    return useCartStore().fetchUnreadNotificationsCount();
  },
});

if (typeof window !== "undefined") {
  // Wait for Pinia/app to mount, then fetch
  setTimeout(() => {
    try {
      useCartStore().fetchCart();
    } catch (e) {
      // Ignored if Pinia not ready
    }
  }, 100);
}
