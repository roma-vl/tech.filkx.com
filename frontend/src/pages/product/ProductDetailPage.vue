<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { store } from '@/store.js';

const galleryImages = [
  {
    label: 'Main view',
    src: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZjrYzoYVLWW_oiXKtFfrvXfrqZhFl0aOo-qiqP-OxioJPU85soCgr1bPX8-8SrIpEgyr7zYqcamNRaM1BW5yOnQdyQkcNC89uNihkW1bThAYw05lRVqC36IMTBCvBLVH7opxwC_Q3tAwXBXFTV3E_7Pec49dMJ6oEmwa-i1h3rfPR3C3ZxfrlPDm4iN8h3YEy4Smhr2pI6IcA1YpRV8_hq162IYmxl8-kkt1WI_Z9ARaUKWft3ncDr_m6Dug4Fa0Nm0Rr2ngLp0Q'
  },
  {
    label: 'Titanium finish',
    src: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDPUZeieQE8ZsENKIoqpjuPaX0irbEaW_gK61xfA56woNa4tnOjtpzulpH-a6_zAWSs88rRSB6FnpI3j_u30Bw8tGGEdtsgGSNJxfTDGTUSCHas9tOQoqN8RRJtyMeYJfWy5_exfM0awIW3MSeS5sCivmC0w9EJVknnJLKr62TWpwZBUPU3w2Q5M_m7rfgSvXZBJSDRTLhgShNT1KprZQwDYONJrxl9KIAxyWmIFfneT-F2YRB3Or-aaO-ZSzqwqwkknEshloYdPow'
  },
  {
    label: 'Camera system',
    src: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCxWFW9FigqwmNnvKq-OOkKCFsBb2E3wa-YFkgvrL8IcmpcbOyWGf_AaQ8eAHGwZ0LMf-SlZ3g1l2DPUf-Ciehf_yHruUS10Gj7VkQIDKsLVCs1w3x-iZBCFx2RxbeJbPms06nian_js_qOEqNzC5vBZuHp1-RoIUVqjERoC-6oY6Z0R-4RSAnrLxyJvwRaaGWNaMCPie38VULThsSwORu5_IvMqJpVBKWcUpB3qlmVWdc_Acw4vesFJfxXuJP75CaQEKOoSl_rBq8'
  },
  {
    label: 'Side profile',
    src: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBSbuQDDpx9sKSULs9nQO8ermiDBg6-ZNkYpdjK35oYB5kqjJ6XsjIdWtpR2-ulutXJZQC76N-63kEdbRdsvIc9UayTUZUK6RdjujZoENvYgdt5sQCFA8x3V0WxvAwTiElA6nxDougr7C6cjr4EpJMMOoA-wxqAqHPh71fexkB4_0UlG-DIdjIdhl4dEGIe6Wi3PsMjk1yMqsKTIj5O-ycjd2Xk0KJXwrTH5RyPd8-60ofDNQOVU1bJGWM-LUKFvbtv9KqMhv0LDmg'
  }
];

const product = computed(() => ({
  id: store.selectedProduct?.id || 501,
  name: 'Lumix Pro 15 Ultra',
  category: 'Smartphones',
  subtitle: '1TB Storage / Emerald Edition',
  price: 1299,
  oldPrice: 1499,
  image: galleryImages[0].src,
  rating: 4.8,
  reviews: 482,
  description: 'A titanium flagship with a ProMotion XDR display, pro-grade optics, and all-day performance.'
}));

const selectedImageIndex = ref(0);
const selectedColor = ref('Titanium Emerald');
const selectedStorage = ref('1TB');
const activeTab = ref('experience');
const showStickyBar = ref(false);
const selectedBundleIds = ref(['pods', 'charger']);

const selectedImage = computed(() => galleryImages[selectedImageIndex.value].src);

const tabs = [
  { id: 'experience', label: 'The Experience' },
  { id: 'specs', label: 'Specifications' },
  { id: 'reviews', label: 'Reviews (482)' },
  { id: 'support', label: 'Support' }
];

const specs = [
  ['Processor', 'ElectroLux X2 Octa-Core (3.4GHz) with Neural Engine'],
  ['Memory (RAM)', '16GB LPDDR5X Hyper-Speed Unified Memory'],
  ['Display Panel', '6.9" ProMotion XDR OLED, 1-120Hz dynamic refresh'],
  ['Optics System', 'Triple 108MP Main + 48MP Ultra-Wide + 48MP Periscope Zoom'],
  ['Battery & Power', '5500mAh Lithium-Titanate with 100W HyperCharge'],
  ['Connectivity', '5G Ultra-Wideband, Wi-Fi 7 Ready, Satellite SOS']
];

const bundleItems = [
  {
    id: 'phone',
    name: 'Pro 15 Ultra',
    category: 'Main device',
    price: 1299,
    locked: true,
    image: galleryImages[0].src
  },
  {
    id: 'pods',
    name: 'Elite Sound Pods',
    category: 'Spatial audio',
    price: 179,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuA4CBkZB03qlIoMec3YDV24fO35X8SQ-nFR3-vSL9fHTRB_0yNWXQIPyPUR9XTJAgwPRqR9BMPLYRdA1wE5DJ45Whogygd0z1RLbsHf57iD3oNin76Iky7ChCqZYi5i_wfvTapwlF_E-PSDIHYoRQK6uRBPNNTQz4EHty0UuXvWXNNbKzjznstWRzJVKUzyYdU8ZPafSIhxOBNZZog6jxjU4a9KAaF5H8EcaCT0lQ1XAMin35srr2hS4Wizm7MABaeuhA9WVqY02lQ'
  },
  {
    id: 'charger',
    name: 'Pro Charge 100W',
    category: 'GaN fast charger',
    price: 89,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDHpw39gwGprYkW4qIbZzy2rLTbcLvDxWukT6TsUoCUIIA_MP68QWCuopoQuE9aG5HGs8xzGWJvtts8ShB73VX0DQ3EYiBd5Muljqm64CLPv2t0Sih0HEiaGcZ9xQ2yqdEPy-u7wBaEYQHeCCQqfWYxSM_XjbxKllnWehPZ9qyUDYglqNb73NhCqinyVtGSEOFch5lKqYYp6TABRJkKScR9scZP6lbXxLVOft7ZvFplc69p0s6hEhsBh7bPRgQPf3E-KLZlLIvte_g'
  }
];

const qualityGuarantees = [
  { icon: 'award_star', title: '2-Year Platinum Warranty', text: 'Full coverage for parts and labor.' },
  { icon: 'published_with_changes', title: '30-Day Elite Returns', text: 'Risk-free trial period for all orders.' },
  { icon: 'security', title: 'Certified Secure Platform', text: 'Military-grade encryption for all payments.' }
];

const reviews = [
  {
    name: 'Marta V.',
    title: 'Camera system is the reason to buy it',
    text: 'Low-light photos are clean, the display is bright outdoors, and battery life easily lasts through travel days.'
  },
  {
    name: 'Daniel K.',
    title: 'Feels like a professional device',
    text: 'The titanium finish is excellent and the 1TB model has enough room for video work without constant cleanup.'
  }
];

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price);
};

const bundleSubtotal = computed(() => {
  return bundleItems
    .filter((item) => item.locked || selectedBundleIds.value.includes(item.id))
    .reduce((sum, item) => sum + item.price, 0);
});

const bundleSavings = computed(() => selectedBundleIds.value.length ? 120 : 0);
const bundleTotal = computed(() => bundleSubtotal.value - bundleSavings.value);

const setSelectedImage = (index) => {
  selectedImageIndex.value = index;
};

const selectNextImage = () => {
  selectedImageIndex.value = (selectedImageIndex.value + 1) % galleryImages.length;
};

const selectPreviousImage = () => {
  selectedImageIndex.value = (selectedImageIndex.value - 1 + galleryImages.length) % galleryImages.length;
};

const toggleBundleItem = (item) => {
  if (item.locked) return;

  if (selectedBundleIds.value.includes(item.id)) {
    selectedBundleIds.value = selectedBundleIds.value.filter((id) => id !== item.id);
    return;
  }

  selectedBundleIds.value = [...selectedBundleIds.value, item.id];
};

const addBundleToCart = () => {
  store.addToCart(product.value);
  selectedBundleIds.value.forEach((id) => {
    const item = bundleItems.find((bundleItem) => bundleItem.id === id);
    if (item) store.addToCart({ ...item, category: 'Accessories' });
  });
};

const handleScroll = () => {
  showStickyBar.value = window.scrollY > 420;
};

onMounted(() => {
  window.scrollTo(0, 0);
  handleScroll();
  window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <main class="product-detail max-w-container-max mx-auto px-margin-desktop py-stack-md">
    <nav class="flex gap-2 text-label-md font-label-md text-on-surface-variant mb-stack-lg">
      <a class="hover:text-primary transition-colors" href="#" @click.prevent="store.currentPage = 'home'">Home</a>
      <span>/</span>
      <a class="hover:text-primary transition-colors" href="#" @click.prevent="store.currentPage = 'catalog'">Smartphones</a>
      <span>/</span>
      <span class="text-on-surface font-semibold">{{ product.name }}</span>
    </nav>

    <div class="product-hero">
      <div class="product-gallery">
        <div class="product-thumbs hide-scrollbar">
          <button
            v-for="(image, index) in galleryImages"
            :key="image.label"
            :class="selectedImageIndex === index ? 'border-primary ring-2 ring-primary ring-offset-2' : 'border-outline-variant hover:border-primary'"
            class="w-20 h-20 flex-shrink-0 border rounded-xl overflow-hidden cursor-pointer shadow-sm transition-all group"
            type="button"
            @click="setSelectedImage(index)"
          >
            <img :alt="image.label" class="w-full h-full object-cover group-hover:scale-110 transition-transform" :src="image.src" />
          </button>
        </div>

        <div class="product-main-image bg-surface-container-lowest rounded-2xl border border-outline-variant shadow-sm relative group overflow-hidden">
          <img :key="selectedImage" alt="Main product view" class="w-full max-h-[640px] object-contain transition-transform duration-500 group-hover:scale-105" :src="selectedImage" />
          <div class="absolute inset-x-4 top-1/2 -translate-y-1/2 flex justify-between opacity-0 group-hover:opacity-100 transition-opacity">
            <button class="bg-white/90 backdrop-blur-sm p-3 rounded-full shadow-lg hover:bg-white transition-colors" type="button" @click="selectPreviousImage">
              <span class="material-symbols-outlined text-primary">chevron_left</span>
            </button>
            <button class="bg-white/90 backdrop-blur-sm p-3 rounded-full shadow-lg hover:bg-white transition-colors" type="button" @click="selectNextImage">
              <span class="material-symbols-outlined text-primary">chevron_right</span>
            </button>
          </div>
          <span class="absolute bottom-6 left-6 bg-white/90 backdrop-blur-sm border border-outline-variant rounded-full px-3 py-1 text-[11px] font-black uppercase tracking-widest text-on-surface-variant">
            {{ galleryImages[selectedImageIndex].label }}
          </span>
        </div>
      </div>

      <div class="product-purchase flex flex-col gap-6">
        <div class="flex flex-col gap-3">
          <div class="flex justify-between items-center">
            <span class="bg-primary/10 text-primary font-bold text-[10px] px-3 py-1 rounded-full uppercase tracking-widest border border-primary/20">Titanium Series 2024</span>
            <div class="flex gap-2">
              <button class="p-2 border border-outline-variant rounded-full hover:bg-surface-variant hover:border-primary transition-all" type="button" @click="store.toggleWishlist(product)">
                <span class="material-symbols-outlined text-[20px]" :style="store.isInWishlist(product.id) ? 'font-variation-settings: \'FILL\' 1;' : ''">favorite</span>
              </button>
              <button class="p-2 border border-outline-variant rounded-full hover:bg-surface-variant hover:border-primary transition-all" type="button">
                <span class="material-symbols-outlined text-[20px]">share</span>
              </button>
            </div>
          </div>
          <h1 class="text-[40px] leading-[48px] font-black text-on-surface tracking-tight">{{ product.name }}</h1>
          <p class="text-title-lg text-on-surface-variant font-medium">{{ product.subtitle }}</p>
          <div class="flex flex-wrap items-center gap-4 py-1">
            <div class="flex items-center gap-1">
              <div class="flex text-star-rating">
                <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
              </div>
              <span class="text-label-md font-bold text-on-surface underline decoration-primary/30 cursor-pointer">{{ product.reviews }} reviews</span>
            </div>
            <div class="h-4 w-px bg-outline-variant"></div>
            <span class="text-label-md font-bold text-on-surface-variant tracking-wider uppercase">SKU: ELX-2024-LP15U</span>
          </div>
        </div>

        <div class="p-8 bg-surface-container-low rounded-[2rem] border border-outline-variant shadow-sm space-y-8">
          <div class="space-y-2">
            <div class="flex flex-wrap items-baseline gap-3">
              <span class="text-[48px] font-black tracking-tighter text-on-surface">{{ formatPrice(product.price) }}</span>
              <span class="text-title-lg text-on-surface-variant line-through font-medium">{{ formatPrice(product.oldPrice) }}</span>
              <span class="bg-error text-on-error text-[10px] font-black px-2 py-1 rounded uppercase ml-auto">Save $200</span>
            </div>
            <p class="text-body-md text-on-surface-variant font-medium italic">Available for $108.25/mo with 0% APR</p>
          </div>

          <div class="space-y-6">
            <div class="space-y-3">
              <span class="font-bold text-label-md uppercase tracking-widest text-on-surface-variant">Color: {{ selectedColor }}</span>
              <div class="flex gap-4">
                <button class="w-10 h-10 rounded-full bg-[#004d40] ring-offset-4 p-0.5 border border-white shadow-md" :class="selectedColor === 'Titanium Emerald' ? 'ring-2 ring-primary' : ''" type="button" @click="selectedColor = 'Titanium Emerald'"></button>
                <button class="w-10 h-10 rounded-full bg-[#1c1c1c] hover:scale-110 transition-transform p-0.5 border border-outline-variant" :class="selectedColor === 'Graphite Black' ? 'ring-2 ring-primary ring-offset-4' : ''" type="button" @click="selectedColor = 'Graphite Black'"></button>
                <button class="w-10 h-10 rounded-full bg-[#d1d1d1] hover:scale-110 transition-transform p-0.5 border border-outline-variant" :class="selectedColor === 'Lunar Silver' ? 'ring-2 ring-primary ring-offset-4' : ''" type="button" @click="selectedColor = 'Lunar Silver'"></button>
              </div>
            </div>

            <div class="space-y-3">
              <span class="font-bold text-label-md uppercase tracking-widest text-on-surface-variant">Configuration</span>
              <div class="product-option-grid">
                <button
                  v-for="storage in ['256GB', '512GB', '1TB']"
                  :key="storage"
                  :class="selectedStorage === storage ? 'border-2 border-primary font-black text-primary bg-primary/5 shadow-sm' : 'border border-outline-variant font-bold text-on-surface-variant hover:border-primary hover:bg-white'"
                  class="py-3 px-4 rounded-xl transition-all text-body-md"
                  type="button"
                  @click="selectedStorage = storage"
                >
                  {{ storage }}
                </button>
              </div>
            </div>
          </div>

          <div class="pt-4 space-y-4">
            <button class="w-full bg-primary hover:bg-deep-emerald text-on-primary py-5 rounded-2xl font-black text-title-lg shadow-xl shadow-primary/20 hover:shadow-primary/30 active:scale-[0.98] transition-all flex items-center justify-center gap-3" type="button" @click="store.addToCart(product)">
              <span class="material-symbols-outlined">shopping_cart</span>
              ADD TO CART
            </button>
            <button class="w-full border-2 border-on-surface text-on-surface py-5 rounded-2xl font-black text-title-lg hover:bg-on-surface hover:text-white transition-all active:scale-[0.98]" type="button">
              EXPRESS CHECKOUT
            </button>
          </div>

          <div class="stock-grid bg-outline-variant/30 rounded-xl overflow-hidden">
            <div class="bg-white p-4 flex flex-col gap-1 items-center text-center">
              <span class="material-symbols-outlined text-primary text-[24px]">inventory_2</span>
              <span class="text-[11px] font-black text-primary tracking-widest uppercase mt-1">In Stock</span>
              <span class="text-[10px] text-on-surface-variant">Ready to ship today</span>
            </div>
            <div class="bg-white p-4 flex flex-col gap-1 items-center text-center">
              <span class="material-symbols-outlined text-primary text-[24px]">local_shipping</span>
              <span class="text-[11px] font-black text-on-surface tracking-widest uppercase mt-1">Express</span>
              <span class="text-[10px] text-on-surface-variant">Free delivery by Oct 24</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="mt-stack-xl">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
        <div>
          <h2 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">Complete Your Setup</h2>
          <p class="text-sm text-on-surface-variant mt-2">Curated accessories that match this configuration.</p>
        </div>
        <p class="text-primary font-bold text-body-md cursor-pointer hover:underline">See all compatible accessories</p>
      </div>
      <div class="bundle-panel bg-surface-container-low rounded-[2rem] border border-outline-variant">
        <div class="bundle-items">
          <template v-for="(item, index) in bundleItems" :key="item.id">
            <div class="bundle-item" :class="{ 'bundle-item--selected': item.locked || selectedBundleIds.includes(item.id) }">
              <button class="bundle-check" type="button" @click="toggleBundleItem(item)">
                <span class="material-symbols-outlined text-[18px]">{{ item.locked || selectedBundleIds.includes(item.id) ? 'check' : 'add' }}</span>
              </button>
              <div class="aspect-square bg-white rounded-2xl p-4 border border-outline-variant shadow-sm">
                <img :alt="item.name" class="w-full h-full object-contain" :src="item.image" />
              </div>
              <div class="text-center space-y-1">
                <p class="text-[11px] font-black uppercase tracking-tighter truncate">{{ item.name }}</p>
                <p class="text-[11px] text-on-surface-variant font-semibold">{{ item.category }}</p>
                <p class="text-sm font-black text-primary">{{ formatPrice(item.price) }}</p>
              </div>
            </div>
            <span v-if="index < bundleItems.length - 1" class="material-symbols-outlined text-outline text-3xl font-light">add</span>
          </template>
        </div>
        <div class="bundle-summary">
          <div class="text-center lg:text-right space-y-2">
            <p class="text-label-md font-black uppercase tracking-widest text-on-surface-variant">Bundle Price</p>
            <p class="text-[32px] font-black text-primary tracking-tighter">{{ formatPrice(bundleTotal) }}</p>
            <p class="text-xs text-on-surface-variant font-bold">Subtotal {{ formatPrice(bundleSubtotal) }}</p>
            <p class="bg-error/10 text-error font-black text-[10px] px-2 py-1 rounded inline-block">INSTANT SAVINGS: {{ formatPrice(bundleSavings) }}</p>
          </div>
          <button class="w-full bg-on-surface text-white px-8 py-4 rounded-xl font-black text-title-md hover:shadow-xl transition-all active:scale-95" type="button" @click="addBundleToCart">
            ADD SELECTED TO CART
          </button>
        </div>
      </div>
    </section>

    <section class="mt-stack-xl">
      <div class="flex border-b border-outline-variant mb-12 overflow-x-auto hide-scrollbar scroll-smooth">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="activeTab === tab.id ? 'text-primary font-black border-b-4 border-primary' : 'text-on-surface-variant font-bold hover:text-primary'"
          class="px-10 py-6 text-title-md whitespace-nowrap transition-all uppercase tracking-widest"
          type="button"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="detail-info-grid">
        <div class="detail-info-main">
          <section v-if="activeTab === 'experience'" class="space-y-8">
            <div class="max-w-3xl">
              <h3 class="text-[36px] font-black text-on-surface tracking-tight leading-tight mb-6">Revolutionary Performance. Mastered in Titanium.</h3>
              <p class="text-body-lg text-on-surface-variant leading-relaxed">
                The Lumix Pro 15 Ultra is engineered with a revolutionary X2 Biometric Chip and a stunning 6.9-inch ProMotion XDR display for peak performance, visual brilliance, and pro-grade capture.
              </p>
            </div>
            <img alt="Internal tech" class="w-full rounded-[2rem] border border-outline-variant shadow-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAlLPi4qPWW-pjg1qJFIjxIZKyFFhepLHkWr7dmmTfsPA-M94Tz3oVozEJ4-mN77JEATZieBYs6_9MPJKNZBIpA-0eFrFzVkd7s_nfCbkw9dFyH0F0Cwsnx3aE7dAmdQBmrm6qHr0oWhsyKzfNcimCHqfoChvRJQiDRpFFDdtfltbepaRt4_GntXGDbnBMOqWB9y_kw0QYy-g32zYaD67xLw0y8N2gvfzLQO_5tVsFm9zDvWtCXD-gOLSTpEDIra9-vc9wMXlwpT20" />
          </section>

          <section v-else-if="activeTab === 'specs'" class="space-y-8">
            <h3 class="text-headline-md font-black tracking-tight flex items-center gap-3">
              <span class="material-symbols-outlined text-primary text-3xl">terminal</span>
              Technical Specifications
            </h3>
            <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant overflow-hidden shadow-sm">
              <table class="w-full text-left border-collapse text-body-md">
                <thead>
                  <tr class="bg-surface-container-low border-b border-outline-variant">
                    <th class="p-6 font-black uppercase tracking-widest text-[11px] text-on-surface-variant w-1/3">Hardware Component</th>
                    <th class="p-6 font-black uppercase tracking-widest text-[11px] text-on-surface-variant">Performance Metric</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/30">
                  <tr v-for="(spec, index) in specs" :key="spec[0]" :class="index % 2 ? 'bg-surface-container-low' : ''" class="hover:bg-primary/5 transition-colors">
                    <td class="p-6 font-bold text-on-surface">{{ spec[0] }}</td>
                    <td class="p-6 text-on-surface-variant">{{ spec[1] }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section v-else-if="activeTab === 'reviews'" class="space-y-8">
            <div class="reviews-grid">
              <div class="p-8 bg-surface-container rounded-2xl text-center space-y-2">
                <p class="text-[56px] font-black text-on-surface tracking-tighter">4.8</p>
                <div class="flex justify-center text-star-rating mb-1">
                  <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[24px]" style="font-variation-settings: 'FILL' 1;">star</span>
                </div>
                <p class="text-label-md font-bold uppercase tracking-widest text-on-surface-variant">Customer Rating</p>
              </div>
              <div class="p-8 border border-outline-variant rounded-2xl flex flex-col justify-center space-y-4">
                <div v-for="rating in [{ label: '5 Star', value: '85%' }, { label: '4 Star', value: '10%' }, { label: '3 Star', value: '3%' }]" :key="rating.label" class="flex items-center gap-4">
                  <span class="text-label-md font-bold w-12 text-right">{{ rating.label }}</span>
                  <div class="flex-1 h-3 bg-surface-container rounded-full overflow-hidden">
                    <div class="h-full bg-primary" :style="{ width: rating.value }"></div>
                  </div>
                  <span class="text-label-md font-bold w-10 text-on-surface-variant">{{ rating.value }}</span>
                </div>
              </div>
            </div>
            <article v-for="review in reviews" :key="review.name" class="p-6 bg-white border border-outline-variant rounded-2xl shadow-sm">
              <div class="flex items-center justify-between gap-4 mb-3">
                <div>
                  <h4 class="font-black text-on-surface">{{ review.title }}</h4>
                  <p class="text-xs font-bold uppercase tracking-widest text-on-surface-variant">{{ review.name }}</p>
                </div>
                <div class="flex text-star-rating">
                  <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                </div>
              </div>
              <p class="text-sm text-on-surface-variant leading-relaxed">{{ review.text }}</p>
            </article>
          </section>

          <section v-else class="support-grid">
            <div class="support-card">
              <span class="material-symbols-outlined text-primary text-[32px]">support_agent</span>
              <h3 class="font-black text-title-lg text-on-surface">Priority Expert Support</h3>
              <p class="text-sm text-on-surface-variant">Chat with product engineers for setup, data transfer, and configuration guidance.</p>
            </div>
            <div class="support-card">
              <span class="material-symbols-outlined text-primary text-[32px]">local_shipping</span>
              <h3 class="font-black text-title-lg text-on-surface">Delivery & Setup</h3>
              <p class="text-sm text-on-surface-variant">Free express delivery, appointment setup, and tracked replacement options.</p>
            </div>
            <div class="support-card">
              <span class="material-symbols-outlined text-primary text-[32px]">workspace_premium</span>
              <h3 class="font-black text-title-lg text-on-surface">Care Plan</h3>
              <p class="text-sm text-on-surface-variant">Extend protection with accidental damage coverage and same-day swap support.</p>
            </div>
          </section>
        </div>

        <aside class="detail-info-aside">
          <div class="p-8 bg-white border border-outline-variant rounded-2xl shadow-sm space-y-6">
            <h4 class="font-black text-[11px] uppercase tracking-widest text-on-surface-variant flex items-center gap-2">
              <span class="material-symbols-outlined text-primary text-[18px]">verified</span>
              Quality Guarantees
            </h4>
            <ul class="space-y-4">
              <li v-for="item in qualityGuarantees" :key="item.title" class="flex gap-4">
                <span class="material-symbols-outlined text-primary">{{ item.icon }}</span>
                <div class="space-y-0.5">
                  <p class="text-body-md font-black">{{ item.title }}</p>
                  <p class="text-label-md text-on-surface-variant">{{ item.text }}</p>
                </div>
              </li>
            </ul>
          </div>
          <div class="p-8 bg-primary-container text-on-primary-container rounded-2xl shadow-md space-y-4 relative overflow-hidden">
            <div class="relative z-10">
              <h4 class="font-black text-title-lg mb-2">Technical Advisor</h4>
              <p class="text-body-md opacity-90 mb-6">Our product engineers are available for a 1-on-1 consultation to find your perfect configuration.</p>
              <button class="w-full bg-white text-primary py-4 rounded-xl font-black flex items-center justify-center gap-2 hover:bg-primary-fixed transition-all group" type="button">
                <span class="material-symbols-outlined transition-transform group-hover:scale-125">chat_bubble</span>
                START LIVE CONSULTATION
              </button>
            </div>
            <div class="absolute -right-8 -bottom-8 opacity-10">
              <span class="material-symbols-outlined text-[120px]">support_agent</span>
            </div>
          </div>
        </aside>
      </div>
    </section>
  </main>

  <div class="product-sticky-bar" :class="{ 'product-sticky-bar--visible': showStickyBar }">
    <div class="max-w-container-max mx-auto px-margin-desktop h-24 flex items-center justify-between gap-6">
      <div class="flex items-center gap-6 overflow-hidden min-w-0">
        <img alt="Small product thumbnail" class="w-14 h-14 object-cover rounded-xl border border-outline-variant hidden sm:block shadow-sm" :src="selectedImage" />
        <div class="truncate">
          <p class="font-black text-on-surface text-title-md truncate tracking-tight">{{ product.name }}</p>
          <div class="flex items-center gap-3">
            <span class="text-primary font-black text-title-lg">{{ formatPrice(product.price) }}</span>
            <span class="text-label-md font-black text-primary uppercase bg-primary/10 px-2 py-0.5 rounded">In Stock</span>
          </div>
        </div>
      </div>
      <button class="bg-primary hover:bg-deep-emerald text-on-primary px-8 py-4 rounded-xl font-black text-title-md shadow-lg shadow-primary/20 transition-all hover:-translate-y-0.5 active:translate-y-0 shrink-0" type="button" @click="store.addToCart(product)">
        ADD TO CART
      </button>
    </div>
  </div>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.product-hero,
.detail-info-grid,
.reviews-grid,
.support-grid {
  display: grid;
  gap: 24px;
  grid-template-columns: 1fr;
}
.product-gallery {
  display: flex;
  flex-direction: column;
  gap: 24px;
}
.product-thumbs {
  order: 2;
  display: flex;
  gap: 12px;
  overflow-x: auto;
}
.product-main-image {
  order: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  min-height: 420px;
}
.product-option-grid,
.stock-grid {
  display: grid;
  gap: 12px;
  grid-template-columns: repeat(3, minmax(0, 1fr));
}
.stock-grid {
  gap: 1px;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}
.bundle-panel {
  display: flex;
  flex-direction: column;
  gap: 32px;
  padding: 2rem;
}
.bundle-items {
  display: flex;
  flex: 1;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 24px;
}
.bundle-item {
  position: relative;
  width: 144px;
  text-align: center;
  opacity: 0.55;
  transition: opacity 0.2s, transform 0.2s;
}
.bundle-item--selected {
  opacity: 1;
}
.bundle-item:hover {
  transform: translateY(-2px);
}
.bundle-check {
  position: absolute;
  right: -6px;
  top: -6px;
  z-index: 2;
  display: flex;
  width: 28px;
  height: 28px;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  background: #005228;
  color: #fff;
  box-shadow: 0 8px 18px rgba(0, 82, 40, 0.22);
}
.bundle-summary {
  display: flex;
  width: 100%;
  flex-direction: column;
  align-items: center;
  gap: 24px;
  border-top: 1px solid #becabd;
  padding-top: 2rem;
}
.detail-info-main {
  min-width: 0;
}
.detail-info-aside {
  display: flex;
  flex-direction: column;
  gap: 24px;
  height: fit-content;
}
.support-card {
  border: 1px solid #becabd;
  border-radius: 16px;
  background: #fff;
  padding: 2rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.product-sticky-bar {
  position: fixed;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 120;
  transform: translateY(100%);
  border-top: 1px solid #becabd;
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.08);
  backdrop-filter: blur(12px);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.product-sticky-bar--visible {
  transform: translateY(0);
}
@media (min-width: 768px) {
  .product-gallery {
    flex-direction: row;
  }
  .product-thumbs {
    order: 1;
    flex-direction: column;
    overflow-x: visible;
  }
  .product-main-image {
    order: 2;
    padding: 3rem;
  }
  .reviews-grid {
    grid-template-columns: minmax(0, 1fr) minmax(0, 2fr);
  }
  .support-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
@media (min-width: 1024px) {
  .product-hero {
    grid-template-columns: minmax(0, 7fr) minmax(360px, 5fr);
    align-items: start;
  }
  .bundle-panel {
    flex-direction: row;
    align-items: center;
    padding: 3rem;
  }
  .bundle-summary {
    width: 18rem;
    align-items: flex-end;
    border-top-width: 0;
    border-left: 1px solid #becabd;
    padding-top: 0;
    padding-left: 3rem;
  }
  .detail-info-grid {
    grid-template-columns: minmax(0, 8fr) minmax(320px, 4fr);
  }
  .detail-info-aside {
    position: sticky;
    top: 7rem;
  }
}
</style>
