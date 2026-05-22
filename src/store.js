import { reactive } from 'vue';

export const store = reactive({
  cartCount: 3,
  wishlistCount: 0,
  compareCount: 0,
  
  addToCart(product) {
    this.cartCount++;
  },
  
  toggleWishlist(product) {
    this.wishlistCount++;
  },
  
  incrementCompare() {
    this.compareCount++;
  }
});
