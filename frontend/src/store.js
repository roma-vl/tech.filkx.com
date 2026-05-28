import { reactive } from 'vue';

export const store = reactive({
  // Navigation state
  currentPage: 'home',
  selectedProduct: null,

  // Arrays for items
  cart: [],
  wishlist: [],
  compare: [],
  toasts: [],

  // Modals visibility state
  activeDrawer: null, // 'cart', 'wishlist', 'compare', 'account', or null

  // Toast notifications counter for unique IDs
  toastId: 0,

  // Getters (defined as JS getters on the reactive object)
  get cartCount() {
    return this.cart.reduce((acc, item) => acc + item.quantity, 0);
  },

  get cartTotal() {
    return this.cart.reduce((acc, item) => acc + (item.price * item.quantity), 0);
  },

  get wishlistCount() {
    return this.wishlist.length;
  },

  get compareCount() {
    return this.compare.length;
  },

  // Actions
  addToast(message, type = 'success') {
    const id = this.toastId++;
    this.toasts.push({ id, message, type });
    setTimeout(() => {
      this.removeToast(id);
    }, 3000);
  },

  removeToast(id) {
    const index = this.toasts.findIndex(t => t.id === id);
    if (index !== -1) {
      this.toasts.splice(index, 1);
    }
  },

  addToCart(product) {
    const existing = this.cart.find(item => item.id === product.id);
    if (existing) {
      existing.quantity++;
      this.addToast(`Updated quantity of ${product.name} to ${existing.quantity} in cart.`);
    } else {
      this.cart.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        category: product.category || 'Electronics',
        quantity: 1
      });
      this.addToast(`Added ${product.name} to cart.`);
    }
  },

  removeFromCart(productId) {
    const index = this.cart.findIndex(item => item.id === productId);
    if (index !== -1) {
      const name = this.cart[index].name;
      this.cart.splice(index, 1);
      this.addToast(`Removed ${name} from cart.`, 'info');
    }
  },

  updateCartQuantity(productId, quantity) {
    const item = this.cart.find(item => item.id === productId);
    if (item) {
      item.quantity = Math.max(1, quantity);
    }
  },

  toggleWishlist(product) {
    const index = this.wishlist.findIndex(item => item.id === product.id);
    if (index !== -1) {
      const name = this.wishlist[index].name;
      this.wishlist.splice(index, 1);
      this.addToast(`Removed ${name} from wishlist.`, 'info');
    } else {
      this.wishlist.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        category: product.category || 'Electronics'
      });
      this.addToast(`Added ${product.name} to wishlist.`);
    }
  },

  isInWishlist(productId) {
    return this.wishlist.some(item => item.id === productId);
  },

  toggleCompare(product) {
    const index = this.compare.findIndex(item => item.id === product.id);
    if (index !== -1) {
      const name = this.compare[index].name;
      this.compare.splice(index, 1);
      this.addToast(`Removed ${name} from compare.`, 'info');
    } else {
      if (this.compare.length >= 3) {
        this.addToast('You can compare a maximum of 3 items.', 'warning');
        return;
      }
      this.compare.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        category: product.category || 'Electronics',
        rating: product.rating || 4.5,
        reviews: product.reviews || 0,
        description: product.description || 'Premium build quality, ultra-durable, leading technology.'
      });
      this.addToast(`Added ${product.name} to compare.`);
    }
  },

  isInCompare(productId) {
    return this.compare.some(item => item.id === productId);
  },

  removeFromCompare(productId) {
    const index = this.compare.findIndex(item => item.id === productId);
    if (index !== -1) {
      const name = this.compare[index].name;
      this.compare.splice(index, 1);
      this.addToast(`Removed ${name} from compare.`, 'info');
    }
  },

  openDrawer(drawerName) {
    this.activeDrawer = drawerName;
  },

  closeDrawer() {
    this.activeDrawer = null;
  },

  async viewProduct(product = null) {
    this.selectedProduct = product;
    this.currentPage = 'product';
    if (product && typeof window !== 'undefined') {
      const { default: router } = await import('@/router');
      router.push({ name: 'product-detail', params: { id: product.slug } });
    }
  }
});
