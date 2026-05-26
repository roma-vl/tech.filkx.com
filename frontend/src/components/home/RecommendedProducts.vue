<script setup>
import { ref } from 'vue';
import { store } from '@/store.js';

const carouselRef = ref(null);

const scrollCarousel = (direction) => {
  if (carouselRef.value) {
    const scrollAmount = direction === 'left' ? -320 : 320;
    carouselRef.value.scrollBy({
      left: scrollAmount,
      behavior: 'smooth'
    });
  }
};

const products = [
  {
    id: 10,
    name: 'Titan X Pro Max 512GB',
    category: 'Smartphones',
    price: 1299.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCD4mJ768diZ-3ohTcgiepF2WHyNgmK4O62kdfBqnXW26Z5JHFeznt335OWFBdTwki9yXaAiUyq9Q7u2iBU4LYrgn-E0C9aG3Fv8Bky0_kZLcZiRjMOe6cbDYUayleaLx4NVA4sjA2RgGRydL-H3j9etBD_FH7eauOIjS0Hfq8woxSFaXXSbRIouRCyGxlCXx7Iyghg58Y6rX3Gl55sPn4ut4Rv5zmj9lAJzc2iAOTLpTAU5EEd6r0y4V8J_KZ6vmI_Xjfo1wOWIYo',
    description: 'Next-generation flagship smartphone with a titanium chassis, 512GB ultra-fast storage, pro-level triple camera array, and 5G speeds.'
  },
  {
    id: 11,
    name: 'ProBook Air M3 Titanium',
    category: 'Laptops',
    price: 1599.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBJQNj0EC0emUkDyzp90oAj9LJPzpKPXT6LWZapnUxdjLR5TAEy9kuHaJr94uG2PzVeehWJTvry4Ee2usgri_YrTONN_qiozc1qDVA0IvjUJcjmfX4tlyXbG7cS55CYK0qdtU_5nNwNG1cvHRVWZ4RDHp1qQpcIlcPkq0kc54-dRRkNH3kiZOkglgTPglzSvbgSmBPllFt0kRkaUl6e5wMCrocXFoM-7JxcSFJb9mOT7tc5df8zDbGD5gV5FgaFM7ihaGDzCu-bE7Q',
    description: 'Sleek, fanless titanium chassis ultrabook featuring the state-of-the-art M3 chip, high-resolution Liquid Retina display, and all-day battery life.'
  },
  {
    id: 12,
    name: 'Aurora RGB Gaming Desktop',
    category: 'Gaming',
    price: 2199.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAE9_IW8qjD9Nc7mEzTBtpVASRLILY00pNvfzJBc0ScXAbqlVP5-MBnPCK1_FQAYoA00CnZnnOf3DXUx5-3bjC9TfwHbFKDwu7kn1eTD6_9iS1v7zsuQvBzZd7Pui64wUk0nmM8DVTBsm1JxygZ0gvXHmgO2SnukHBkKJO8m_0nmqJ_M0lTRaSc9yTEpmjg7GtNXu6z3I_GpVNqH8x5MAutaaD-vKU1feM5vrr1dtaIrvVf5pqHczZWNOnPC4QB6kuwjAOed-Fn338',
    description: 'High-performance prebuilt gaming rig featuring custom liquid cooling, liquid-smooth RGB lighting, NVIDIA RTX 4080 GPU, and AMD Ryzen 9 processor.'
  },
  {
    id: 13,
    name: 'Vintage Series Audiophile Headphones',
    category: 'Audio Tech',
    price: 450.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBVfw-53YUOGGnBoNP1DcRvTzsoRmMHBmiwJBD1k7x9SS1j74hj221NxSS2aY4rrQ2TGdYlU-TZEQTQrQL8VOYnj71VUijn7oUH0epVhUD9j-HYXKvcxT_WNN-QsdhR2CCjNflwttO8BRSJfO9nWXZqAwQGND-hcNG8NeHUOUtZqWnD0RJ65tirhUidkCux8eJ2F0J9AuoAZQzu9vG1rGiQD0_CM-36L3Z5vrvwifWeVwUUNFTLB0rYDFLbuosmr50rxsLVJl-ZuPA',
    description: 'Retro-inspired open-back dynamic headphones hand-crafted with premium solid walnut and genuine leather ear cushions.'
  },
  {
    id: 14,
    name: 'EchoSphere G2 Smart Speaker',
    category: 'Audio Tech',
    price: 299.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDPj2jX_NI6YnhpUgDqDHkjRyeW2EKyfTQoHQmuLzpIobASQ4gxKuixqe5u8J708jdxV2Zaw2z1-PM4SOnpiwkUKpWsEYu1Xii-NQJ1Jipsi2nl68gkV_FcIr8iE4C7FaoN5xPauziyMouuW158Nmv0Ps76AzgZ_V6rOgna1wINGvttbUBNXrNG25VrdbNfqVa8yASlBuYZHn5qBvutaAW_iE4HCbzyslvJrI4BzUQuNKGnSK3UTe12q37KdLS-Gb_o_h9l1gHkV44',
    description: 'Spherical premium smart speaker with voice assistant control, multi-room adaptive audio tuning, and high-excursion woofer.'
  }
];
</script>

<template>
  <!-- Recommended for You (Refined Spacing & Alignment) -->
  <section class="max-w-container-max mx-auto px-margin-desktop py-stack-xl">
    <div class="flex items-center justify-between mb-12">
      <div class="flex flex-col gap-2">
        <h2 class="font-headline-lg text-headline-lg text-on-background">Recommended For You</h2>
        <p class="text-on-surface-variant text-sm">Based on your browsing history and interests.</p>
      </div>
      <div class="flex gap-4">
        <button
          @click="scrollCarousel('left')"
          class="w-12 h-12 rounded-full border border-outline-variant flex items-center justify-center hover:bg-surface-container-high hover:border-primary transition-all shadow-sm"
        >
          <span class="material-symbols-outlined">chevron_left</span>
        </button>
        <button
          @click="scrollCarousel('right')"
          class="w-12 h-12 rounded-full border border-outline-variant flex items-center justify-center hover:bg-surface-container-high hover:border-primary transition-all shadow-sm"
        >
          <span class="material-symbols-outlined">chevron_right</span>
        </button>
      </div>
    </div>

    <!-- Carousel Row -->
    <div
      ref="carouselRef"
      class="flex overflow-x-auto gap-8 hide-scrollbar scroll-smooth snap-x snap-mandatory px-1 py-4"
    >
      <!-- Recommendation Item -->
      <router-link
        v-for="prod in products"
        :key="prod.id"
        :to="{ name: 'product-detail', params: { id: prod.id } }"
        class="group cursor-pointer flex-shrink-0 w-[calc(50%-16px)] lg:w-[calc(20%-26px)] min-w-[220px] snap-start block"
        @click="store.viewProduct(prod)"
      >
        <div class="aspect-square bg-surface-container-low rounded-xl mb-5 overflow-hidden relative border border-outline-variant/10 shadow-sm transition-all duration-500 group-hover:shadow-lg group-hover:border-primary/20">
          <img
            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
            :src="prod.image"
            :alt="prod.name"
          />
          <button
            @click.stop="store.toggleWishlist(prod)"
            class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/90 backdrop-blur-md shadow-lg hover:bg-white transition-all flex items-center justify-center text-on-surface-variant hover:text-error opacity-0 group-hover:opacity-100 scale-90 group-hover:scale-100 z-10"
          >
            <span
              class="material-symbols-outlined text-[20px]"
              :class="{ 'fill text-error': store.isInWishlist(prod.id) }"
              :style="store.isInWishlist(prod.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
            >
              favorite
            </span>
          </button>
          <div class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors"></div>
        </div>
        <div class="flex flex-col gap-2">
          <h3 class="font-title-md text-on-surface group-hover:text-primary transition-colors line-clamp-1">
            {{ prod.name }}
          </h3>
          <div class="flex items-center justify-between">
            <span class="font-bold text-xl text-on-surface">${{ prod.price.toFixed(2) }}</span>
            <div class="flex gap-2">
              <button
                @click.stop="store.toggleCompare(prod)"
                class="w-10 h-10 rounded-full bg-surface-container-high text-on-surface flex items-center justify-center hover:scale-110 active:scale-95 transition-all shadow-md"
                :class="{ 'bg-primary-container/20 text-primary': store.isInCompare(prod.id) }"
                title="Compare"
              >
                <span class="material-symbols-outlined text-[20px]" :class="{ 'fill': store.isInCompare(prod.id) }">compare_arrows</span>
              </button>
              <button
                @click.stop="store.addToCart(prod)"
                class="w-10 h-10 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center hover:scale-110 active:scale-95 transition-all shadow-md"
                title="Add to Cart"
              >
                <span class="material-symbols-outlined text-[20px]">add_shopping_cart</span>
              </button>
            </div>
          </div>
        </div>
      </router-link>
    </div>
  </section>
</template>
