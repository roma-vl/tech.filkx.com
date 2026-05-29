import { reactive } from 'vue';

export const store = reactive({
  // Navigation state
  currentPage: 'home',
  selectedProduct: null,

  // Arrays for items
  cart: typeof window !== 'undefined' && localStorage.getItem('electro_cart') 
    ? JSON.parse(localStorage.getItem('electro_cart')) 
    : [],
  wishlist: typeof window !== 'undefined' && localStorage.getItem('electro_wishlist') 
    ? JSON.parse(localStorage.getItem('electro_wishlist')) 
    : [],
  compare: typeof window !== 'undefined' && localStorage.getItem('electro_compare') 
    ? JSON.parse(localStorage.getItem('electro_compare')) 
    : [],
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
    if (typeof window !== 'undefined') {
      localStorage.setItem('electro_cart', JSON.stringify(this.cart));
    }
  },

  removeFromCart(productId) {
    const index = this.cart.findIndex(item => item.id === productId);
    if (index !== -1) {
      const name = this.cart[index].name;
      this.cart.splice(index, 1);
      this.addToast(`Removed ${name} from cart.`, 'info');
      if (typeof window !== 'undefined') {
        localStorage.setItem('electro_cart', JSON.stringify(this.cart));
      }
    }
  },

  updateCartQuantity(productId, quantity) {
    const item = this.cart.find(item => item.id === productId);
    if (item) {
      item.quantity = Math.max(1, quantity);
      if (typeof window !== 'undefined') {
        localStorage.setItem('electro_cart', JSON.stringify(this.cart));
      }
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
    if (typeof window !== 'undefined') {
      localStorage.setItem('electro_wishlist', JSON.stringify(this.wishlist));
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
      if (this.compare.length >= 12) {
        this.addToast('Ви можете порівнювати не більше 12 товарів одночасно.', 'warning');
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
        description: product.description || 'Premium build quality, ultra-durable, leading technology.',
        specs: product.specs || []
      });
      this.addToast(`Added ${product.name} to compare.`);
    }
    if (typeof window !== 'undefined') {
      localStorage.setItem('electro_compare', JSON.stringify(this.compare));
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
      if (typeof window !== 'undefined') {
        localStorage.setItem('electro_compare', JSON.stringify(this.compare));
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
    if (typeof window !== 'undefined' && productId) {
      let viewed = JSON.parse(localStorage.getItem('electro_viewed') || '[]');
      viewed = viewed.filter(id => id !== productId);
      viewed.unshift(productId);
      viewed = viewed.slice(0, 10);
      localStorage.setItem('electro_viewed', JSON.stringify(viewed));
    }
  },

  async viewProduct(product = null) {
    this.selectedProduct = product;
    this.currentPage = 'product';
    if (product && typeof window !== 'undefined') {
      this.trackProductView(product.id);
      const { default: router } = await import('@/router');
      router.push({ name: 'product-detail', params: { id: product.slug } });
    }
  }
});
