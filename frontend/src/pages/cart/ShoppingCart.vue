<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { store } from '@/store.js';

const router = useRouter();
const promoCode = ref('');
const appliedPromo = ref('');

const shipping = computed(() => (store.cartTotal >= 500 || store.cart.length === 0 ? 0 : 25));
const discount = computed(() => (appliedPromo.value ? store.cartTotal * 0.08 : 0));
const tax = computed(() => Math.max(0, (store.cartTotal - discount.value) * 0.075));
const total = computed(() => store.cartTotal - discount.value + shipping.value + tax.value);

const recommended = [
    {
        id: 701,
        name: 'MultiPort Dock Pro',
        price: 89,
        rating: 4,
        reviews: 124,
        category: 'Accessories',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC_mIjGBAHFLCSnmpKkdkgluw78gP5fDfIt-rj8aDqau9wiUubv68sZfet_avneXb0BdlHgR8tzh3aBL5MKj83GuvSVij-YABBwJRePuzTKdMA2zqGbTvdzQGsHdD3NSTTjSbbPxVOKuECFtHzRaEhD9fBoyYGXR717CRPpYrJXHDa8z0XI66M4gP_g_9Hv82KaVGHSzypPCWknl6Ub4H9C30mao8EJzqQoOOGmGd51ETyjAo19kwe1UWnNWg4Och35CqAlSn6pqGQ'
    },
    {
        id: 702,
        name: 'NitroSpeed 2TB SSD',
        price: 179.99,
        rating: 5,
        reviews: 52,
        category: 'Storage',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDOzwSs-mEurkjnbxEorFqVI75POVdV9XlGLWzSzMbWRXxsy59HiItmmyAeg4BLoPf41gWBwaO7cGo-wpz3obaat4JK8vFzPZPhVCB6c1hEeinn3ky228a8QpSiKYty-HviuFXBQJ3MjUJ20Bj1_gieN_lhw3jws_vEvCogRvq8uGZrmKzsaeyHsJ2qJUCHjTvmsaFvAqKylsmuMcOq1_FxcL45gtBztQumOMczuls1pA5qK0RtuMAz51QPeJp8aKMl-s7m9o2Q6kw'
    },
    {
        id: 703,
        name: 'Apex Stand Walnut',
        price: 55,
        rating: 4,
        reviews: 89,
        category: 'Workspace',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCpUg5-c12LFVGvfSy_r-hIiDuew4xWOPJHE5r1BTJ6C0SZoI81TX4Rso9DsY1kRnI-dH4EFoBtyDeLNxxGygB1RRNSy9b0j8V56zv-H9qAPZ2x_vlxd74Tc0OThpmrAsLQzYdgSvQMIaNe0UOyn6T8rpdRKrfFIf4PTDzngldwuJzQznuYiB7OqKWxdkYKDPFxAQPQcY-KsiBUwnpQYYMJUcNKzoxl20RwAv9SQOcid5iX-yumFX-rfDMswz-Tif6n278ku7FlrLY'
    },
    {
        id: 704,
        name: 'VelvetSleeve 16"',
        price: 49.99,
        rating: 5,
        reviews: 210,
        category: 'Cases',
        image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCX0fRCtuolTkfK9Qa7tIVfBzYdLZrzoSEhGM_-ily0VsCSeEySc7yxUa42o7WB4OmDKMhFXwjw4aqNXCqvB5F_Oy0wSF8bvt7VsLpc27B96CzLYrxHt6nqgHQ7hKNHs222ilnNc1wvmr-quRJPU_o60YDoynDPHeDdqqJ3tquNiLYCRgDv6knsZqFN-eM-mXWw7kOxnQgXn0pJdd0pLwnNruSvEbkzpyxXMfW0X5nIoX3pokXrMUne4unP2CH0HOs-nZTlestvNj8'
    }
];

const savedItem = {
    name: 'PulseWatch Elite',
    price: 199,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBz2rEecMMKZDqSlTy2wn1wPo-W6lV9QKlZyMs-UQYlUhbtAafWpYU1bJvaAYXheNG0Kh1DFumEhrCByWghjO4L6jynlHaGoCG1wIpkWhuLd30oKLVVu6fOGzAiZqNCKah13ZPgq0lCOYJmrdDLIWiyq5MkHDzbP4kcxt8l0RyC2B83QZlw9AnwgA4cGsknwJsaRcH2tL4erIWhClRt0j3FxcBbAPR23b2ddRn9SXgTUiBD18p245Dr7tM40NDVEiD4kOgijyISvXc'
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
};

const applyPromo = () => {
    if (!promoCode.value.trim()) return;
    appliedPromo.value = promoCode.value.trim().toUpperCase();
    store.addToast(`Promo code ${appliedPromo.value} applied.`, 'success');
};

const addRecommended = (product) => {
    store.addToCart(product);
};
</script>

<template>
    <main class="max-w-container-max mx-auto px-margin-desktop py-stack-lg min-h-screen">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-stack-lg">
            <div>
                <h1 class="font-headline-lg text-headline-lg">Your Shopping Cart</h1>
                <p class="text-on-surface-variant mt-2">{{ store.cartCount }} items ready for secure checkout.</p>
            </div>
            <button class="text-primary font-bold text-sm hover:underline flex items-center gap-1" type="button" @click="router.push({ name: 'catalog' })">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Continue Shopping
            </button>
        </div>

        <div v-if="store.cart.length === 0" class="bg-surface-container-lowest rounded-xl border border-outline-variant p-12 text-center">
            <div class="w-20 h-20 mx-auto rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant mb-5">
                <span class="material-symbols-outlined text-[44px]">remove_shopping_cart</span>
            </div>
            <h2 class="font-headline-md text-headline-md mb-2">Your cart is empty</h2>
            <p class="text-on-surface-variant mb-6">Add premium electronics to start checkout.</p>
            <button class="bg-primary text-on-primary px-8 py-3 rounded-lg font-bold hover:bg-primary-container transition-all" type="button" @click="router.push({ name: 'catalog' })">
                Explore Catalog
            </button>
        </div>

        <div v-else class="cart-layout">
            <section class="space-y-stack-md">
                <article
                    v-for="item in store.cart"
                    :key="item.id"
                    class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-outline-variant flex flex-col md:flex-row gap-6"
                >
                    <div class="w-full md:w-32 h-32 bg-surface-container rounded-lg overflow-hidden flex-shrink-0 p-3">
                        <img class="w-full h-full object-contain" :src="item.image" :alt="item.name" />
                    </div>
                    <div class="flex-grow flex flex-col justify-between gap-5">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                            <div>
                                <h3 class="font-title-lg text-title-lg">{{ item.name }}</h3>
                                <p class="font-body-md text-on-surface-variant mt-1">{{ item.category }} | Premium configuration</p>
                            </div>
                            <span class="font-price-lg text-price-lg text-primary">{{ formatPrice(item.price * item.quantity) }}</span>
                        </div>
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-4">
                                <div class="flex items-center border border-outline-variant rounded-lg overflow-hidden">
                                    <button class="px-3 py-1 hover:bg-surface-variant transition-colors" type="button" @click="store.updateCartQuantity(item.id, item.quantity - 1)">
                                        <span class="material-symbols-outlined text-body-md">remove</span>
                                    </button>
                                    <span class="px-4 font-title-md">{{ item.quantity }}</span>
                                    <button class="px-3 py-1 hover:bg-surface-variant transition-colors" type="button" @click="store.updateCartQuantity(item.id, item.quantity + 1)">
                                        <span class="material-symbols-outlined text-body-md">add</span>
                                    </button>
                                </div>
                                <button class="text-error font-label-md flex items-center gap-1 hover:underline" type="button" @click="store.removeFromCart(item.id)">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                    Remove
                                </button>
                                <button class="text-on-surface-variant font-label-md flex items-center gap-1 hover:underline" type="button">
                                    <span class="material-symbols-outlined text-[18px]">bookmark</span>
                                    Save for later
                                </button>
                            </div>
                            <div class="text-on-surface-variant font-label-md flex items-center gap-1">
                                <span class="material-symbols-outlined text-primary text-[18px]">check_circle</span>
                                In Stock
                            </div>
                        </div>
                    </div>
                </article>

                <div class="mt-stack-lg pt-stack-lg border-t border-outline-variant">
                    <h2 class="font-headline-md text-headline-md mb-6">Saved for later (1)</h2>
                    <div class="bg-surface-container-low p-4 rounded-xl border border-dashed border-outline-variant flex items-center gap-4 opacity-80">
                        <div class="w-16 h-16 bg-surface-variant rounded-lg flex-shrink-0 overflow-hidden">
                            <img class="w-full h-full object-cover" :src="savedItem.image" :alt="savedItem.name" />
                        </div>
                        <div class="flex-grow">
                            <h4 class="font-title-md text-title-md">{{ savedItem.name }}</h4>
                            <p class="font-label-md text-on-surface-variant">{{ formatPrice(savedItem.price) }}</p>
                        </div>
                        <button class="px-4 py-2 bg-secondary-container text-on-secondary-container rounded-full font-label-md hover:opacity-90 transition-opacity" type="button">
                            Move to Cart
                        </button>
                    </div>
                </div>
            </section>

            <aside>
                <div class="bg-surface-container-low rounded-xl p-6 border border-outline-variant sticky top-24">
                    <h2 class="font-headline-md text-headline-md mb-6">Order Summary</h2>
                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between font-body-lg text-body-lg">
                            <span class="text-on-surface-variant">Subtotal</span>
                            <span class="font-semibold">{{ formatPrice(store.cartTotal) }}</span>
                        </div>
                        <div v-if="discount > 0" class="flex justify-between font-body-lg text-body-lg text-primary">
                            <span>Promo Discount</span>
                            <span class="font-semibold">-{{ formatPrice(discount) }}</span>
                        </div>
                        <div class="flex justify-between font-body-lg text-body-lg">
                            <span class="text-on-surface-variant">Shipping Estimate</span>
                            <span class="font-semibold">{{ shipping === 0 ? 'FREE' : formatPrice(shipping) }}</span>
                        </div>
                        <div class="flex justify-between font-body-lg text-body-lg">
                            <span class="text-on-surface-variant">Tax Estimate</span>
                            <span class="font-semibold">{{ formatPrice(tax) }}</span>
                        </div>
                        <div class="pt-4 border-t border-outline-variant flex justify-between font-headline-md text-headline-md text-primary">
                            <span>Total</span>
                            <span>{{ formatPrice(total) }}</span>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block font-label-md text-on-surface-variant mb-2" for="promo">Promo Code</label>
                        <div class="flex gap-2">
                            <input v-model="promoCode" class="flex-grow bg-surface-container-lowest border border-outline-variant rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary" id="promo" placeholder="Enter code" type="text" />
                            <button class="px-4 py-2 border border-on-surface text-on-surface rounded-lg font-label-md hover:bg-surface-variant transition-colors" type="button" @click="applyPromo">Apply</button>
                        </div>
                    </div>
                    <button class="w-full py-4 bg-primary-container text-on-primary-container rounded-xl font-headline-md text-headline-md shadow-md hover:opacity-90 active:scale-95 transition-all mb-4" type="button" @click="router.push({ name: 'cart' })">
                        Proceed to Checkout
                    </button>
                    <div class="flex items-center justify-center gap-2 text-on-surface-variant font-label-md">
                        <span class="material-symbols-outlined text-[18px]">lock</span>
                        Secure Checkout with SSL Encryption
                    </div>
                </div>
            </aside>
        </div>

        <section class="mt-20">
            <h2 class="font-headline-md text-headline-md mb-8">Recommended for you</h2>
            <div class="recommended-grid">
                <article v-for="product in recommended" :key="product.id" class="group bg-surface-container-lowest rounded-xl border border-outline-variant p-4 hover:shadow-lg transition-all">
                    <div class="aspect-square bg-surface-container rounded-lg mb-4 overflow-hidden">
                        <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" :src="product.image" :alt="product.name" />
                    </div>
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-title-md text-title-md line-clamp-1">{{ product.name }}</h4>
                        <button class="text-on-surface-variant hover:text-primary" type="button" @click="store.toggleWishlist(product)">
                            <span class="material-symbols-outlined">favorite</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-1 mb-2">
                        <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[16px]" :class="star <= product.rating ? 'text-star-rating filled-symbol' : 'text-outline-variant'">star</span>
                        <span class="text-label-md text-on-surface-variant ml-1">({{ product.reviews }})</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-title-lg text-title-lg text-primary">{{ formatPrice(product.price) }}</span>
                        <button class="bg-secondary-container text-on-secondary-container p-2 rounded-full hover:bg-primary hover:text-on-primary transition-colors" type="button" @click="addRecommended(product)">
                            <span class="material-symbols-outlined">add_shopping_cart</span>
                        </button>
                    </div>
                </article>
            </div>
        </section>
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
    font-variation-settings: 'FILL' 1;
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
