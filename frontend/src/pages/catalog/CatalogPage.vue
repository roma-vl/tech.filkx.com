<script setup>
import { computed, onMounted, ref } from 'vue';
import { store } from '@/store.js';

const viewMode = ref('grid');
const sortBy = ref('popularity');
const priceMin = ref(0);
const priceMax = ref(5000);
const selectedBrands = ref(['Apple']);
const selectedRam = ref('16GB');

const products = [
  {
    id: 101,
    name: 'MacBook Pro 14" - M3 Pro Chip, 18GB RAM, 512GB SSD Space Black',
    brand: 'Apple',
    ram: '16GB',
    category: 'Laptops',
    price: 1999,
    rating: 4.9,
    reviews: 128,
    badge: 'New',
    badgeClass: 'bg-primary',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM',
    description: 'Portable pro performance with a Liquid Retina XDR display, fast unified memory, and all-day battery life.'
  },
  {
    id: 102,
    name: 'Dell XPS 15 - Intel Core i9, 32GB RAM, 1TB SSD, RTX 4060 Platinum',
    brand: 'Dell',
    ram: '32GB',
    category: 'Laptops',
    price: 2299,
    oldPrice: 2499,
    rating: 4.8,
    reviews: 92,
    badge: 'Save $200',
    badgeClass: 'bg-sale-error',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDNXpOdOi1q9K16_agnjDdmva4mM8QDf9TI4MCTsRa0_OXpmRLAkd2BmZ0IpQebeCf9T-oqp5EXZIEqu5AgJgO3UAZfh8JwEUwazBkmMcqSqi5NOJjpKjWbdNN6PVkBt40FEXcJMc2b-kYP2x4afcnwiPcUckUaDsOZfW3QlxwFPMxfrXvfI7xR-8qcpi8AlkYYBVIucffemoFhQigVY-yrdYAUIMrcC6HgcPyO99EpuBM4WdjdU2LJpA6MY3BhgG7BudOrk4ZPlNw',
    description: 'A creator-ready laptop with discrete graphics, a premium display, and a precision-machined chassis.'
  },
  {
    id: 103,
    name: 'ROG Zephyrus G14 - Ryzen 9, 16GB RAM, RTX 4070, 120Hz Moonlight White',
    brand: 'ASUS',
    ram: '16GB',
    category: 'Laptops',
    price: 1599,
    rating: 5,
    reviews: 45,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDr331B7FabLZcRGhJ_DbZowzkaew5s_GJfms-DS1LXHrCr9JrEM_qiTSvHHdcRLOQU4NygZqdg2vzSEP8qolpkbrEuPi83FukM8x4ZzJpflfXCL5i6WZw99Ro2W_kJSyPwSKmBh7aTJ89xk_sSMwhQZu0di9CfY_tYG8xsS9crK6wdrdWzCio8Ct_P6vzzIdKMqZSvWk-cI5tR8P_uuTugKKtObu44X83uzkFVwQ768UhPlN4P_9soMg2YidbSr7gU_mGJdorHV3E',
    description: 'Compact gaming power with high-refresh visuals, serious GPU performance, and a travel-friendly footprint.'
  },
  {
    id: 104,
    name: 'ThinkPad X1 Carbon Gen 11 - Intel Core i7, 16GB RAM, 512GB Business',
    brand: 'Lenovo',
    ram: '16GB',
    category: 'Laptops',
    price: 1450,
    rating: 4.7,
    reviews: 210,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuB7ehlQmQnyIbqZ7UKgr8V0mCEj26xp7hzfddOM2Bmm2eSXyygw_1Pa5UtAUjF8FiEFuuTKU2nirKQ6xTW89jAyo3ptFohpbMoa763DzIGnU9WcYlFze2ITHt1uim5itGotu78u02aygZyLgplNTR_YD5AGF0YTqbbYnWcLi0svVRaGzDbKcLNNWAmXWoHRHJc36gfBS75lwJHiIYn9iv35SWv3yGi7oWiWjLQtJV3USiCRNLvPQYgGVjdtW7X71knwmeOoQXbY0cA',
    description: 'A durable business ultrabook with strong battery life, excellent keyboard feel, and enterprise-grade reliability.'
  },
  {
    id: 105,
    name: 'HP Spectre x360 Luxury 2-in-1 - Intel Evo i7, 16GB RAM Nightfall Blue',
    brand: 'HP',
    ram: '16GB',
    category: 'Laptops',
    price: 1399,
    rating: 4.6,
    reviews: 56,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBQiu2QhryOyOxSSyZfWD9MeA0GEjrvV--xa1qwaS8hrlevCY7YgST20v_LnbFwuZSSQdzVOsu2UIszOqE4us7tgsKjEgTwEdx_oCNLd3WkEtX6Xchu-OJXN0HFiNi5FMwtNcN3vOE4aPigIDgb0iQsyL0uyZTRUVKoRY8-w1mQid0uPH6ua6yBODBCxg2VRofafATzyTk29_fVakQo7pDLYkoZpnlabZ-a6KrP-8mkENT9CmR6ScK9g2GjyiSJHaJ20jDKnRfWURM',
    description: 'Premium convertible design with touch-first flexibility, sharp visuals, and polished everyday performance.'
  },
  {
    id: 106,
    name: 'Razer Blade 16 - Dual Mode Mini-LED, RTX 4090, 32GB Gaming Power',
    brand: 'Razer',
    ram: '32GB',
    category: 'Laptops',
    price: 4299.99,
    rating: 4.9,
    reviews: 18,
    badge: 'Low Stock',
    badgeClass: 'bg-secondary',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBkksX47BmkwjAiSzTUIEmWwzDqhcckudBCmKAGA-iUC_9VuRipCKBceGV1cZ2RBI2VLhB8yvdfmaC06PXzi0r2fcnLBPv3CgwfTMDLB_GIRPkwhljwi0940O06xAnYQKKdCkdVpI1_VIF_-YWnoFcn7XIGMoGRLNdjWbH7SAwNwayPVReoUGckbhit9qQh_0Ah89KFN093j6spzUDUYz1mmH84ykE43aqnzG6oM5CvxmMAqqmJXhlQ37JF5NcZfX-XPImae1pnjfw',
    description: 'Desktop-class gaming performance with a dual-mode display, flagship graphics, and CNC aluminum build quality.'
  }
];

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price);
};

const brands = computed(() => {
  return [...new Set(products.map((product) => product.brand))].map((brand) => ({
    name: brand,
    count: products.filter((product) => product.brand === brand).length
  }));
});

const ramOptions = computed(() => {
  return [...new Set(products.map((product) => product.ram))].sort((a, b) => parseInt(a) - parseInt(b));
});

const normalizedPriceRange = computed(() => {
  const min = Math.min(Number(priceMin.value) || 0, Number(priceMax.value) || 0);
  const max = Math.max(Number(priceMin.value) || 0, Number(priceMax.value) || 0);

  return { min, max };
});

const filteredProducts = computed(() => {
  const { min, max } = normalizedPriceRange.value;

  const filtered = products.filter((product) => {
    const matchesPrice = product.price >= min && product.price <= max;
    const matchesBrand = selectedBrands.value.length === 0 || selectedBrands.value.includes(product.brand);
    const matchesRam = !selectedRam.value || product.ram === selectedRam.value;

    return matchesPrice && matchesBrand && matchesRam;
  });

  return [...filtered].sort((a, b) => {
    if (sortBy.value === 'newest') return b.id - a.id;
    if (sortBy.value === 'price-asc') return a.price - b.price;
    if (sortBy.value === 'price-desc') return b.price - a.price;

    return (b.rating * b.reviews) - (a.rating * a.reviews);
  });
});

const activeFilters = computed(() => {
  const filters = [];

  selectedBrands.value.forEach((brand) => {
    filters.push({ type: 'brand', label: brand, value: brand });
  });

  if (selectedRam.value) {
    filters.push({ type: 'ram', label: `${selectedRam.value} RAM`, value: selectedRam.value });
  }

  if (priceMin.value > 0 || priceMax.value < 5000) {
    filters.push({
      type: 'price',
      label: `${formatPrice(normalizedPriceRange.value.min)} - ${formatPrice(normalizedPriceRange.value.max)}`
    });
  }

  return filters;
});

const removeFilter = (filter) => {
  if (filter.type === 'brand') {
    selectedBrands.value = selectedBrands.value.filter((brand) => brand !== filter.value);
  }

  if (filter.type === 'ram') {
    selectedRam.value = '';
  }

  if (filter.type === 'price') {
    priceMin.value = 0;
    priceMax.value = 5000;
  }
};

const clearFilters = () => {
  selectedBrands.value = [];
  selectedRam.value = '';
  priceMin.value = 0;
  priceMax.value = 5000;
};

onMounted(() => {
  window.scrollTo(0, 0);
});
</script>

<template>
  <main class="max-w-container-max mx-auto px-margin-desktop py-8 flex gap-8">
    <aside class="hidden lg:block w-64 flex-shrink-0">
      <div class="sticky top-24 space-y-6">
        <nav class="flex items-center gap-2 text-label-md text-on-surface-variant mb-4">
          <a class="hover:text-primary transition-colors" href="#" @click.prevent="store.currentPage = 'home'">Home</a>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <a class="hover:text-primary transition-colors" href="#">Computers</a>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <span class="text-on-surface font-semibold">Laptops</span>
        </nav>

        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant overflow-hidden shadow-sm">
          <div class="p-4 border-b border-outline-variant">
            <h3 class="font-bold text-xs uppercase tracking-widest text-on-surface-variant mb-3">Department</h3>
            <div class="space-y-0.5">
              <a class="flex items-center justify-between bg-primary/10 text-primary rounded-lg px-2.5 py-1.5 transition-all" href="#">
                <span class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-[18px]">laptop_mac</span>
                  <span class="font-semibold text-sm">Laptops</span>
                </span>
                <span class="text-[11px] font-bold">{{ products.length }}</span>
              </a>
              <a class="flex items-center justify-between text-on-surface-variant hover:bg-surface-variant/30 rounded-lg px-2.5 py-1.5 transition-all" href="#">
                <span class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-[18px]">smartphone</span>
                  <span class="text-sm">Smartphones</span>
                </span>
                <span class="text-[11px]">182</span>
              </a>
              <a class="flex items-center justify-between text-on-surface-variant hover:bg-surface-variant/30 rounded-lg px-2.5 py-1.5 transition-all" href="#">
                <span class="flex items-center gap-2.5">
                  <span class="material-symbols-outlined text-[18px]">headphones</span>
                  <span class="text-sm">Audio</span>
                </span>
                <span class="text-[11px]">96</span>
              </a>
            </div>
          </div>

          <div class="p-4 border-b border-outline-variant">
            <h3 class="font-bold text-xs uppercase tracking-widest text-on-surface-variant mb-4">Price Range</h3>
            <div class="space-y-4">
              <div class="space-y-3">
                <input v-model.number="priceMin" class="w-full accent-primary" max="5000" min="0" step="50" type="range" />
                <input v-model.number="priceMax" class="w-full accent-primary" max="5000" min="0" step="50" type="range" />
              </div>
              <div class="flex items-center gap-2">
                <div class="flex-1 relative">
                  <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px] text-on-surface-variant font-bold">$</span>
                  <input v-model.number="priceMin" class="w-full h-8 pl-5 pr-2 border border-outline-variant rounded bg-surface-container-low text-xs font-medium focus:ring-1 focus:ring-primary focus:border-primary" min="0" type="number" />
                </div>
                <span class="text-on-surface-variant text-[10px]">TO</span>
                <div class="flex-1 relative">
                  <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px] text-on-surface-variant font-bold">$</span>
                  <input v-model.number="priceMax" class="w-full h-8 pl-5 pr-2 border border-outline-variant rounded bg-surface-container-low text-xs font-medium focus:ring-1 focus:ring-primary focus:border-primary" min="0" type="number" />
                </div>
              </div>
            </div>
          </div>

          <div class="p-4 border-b border-outline-variant">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-bold text-xs uppercase tracking-widest text-on-surface-variant">Brand</h3>
              <button class="text-[10px] text-primary font-bold hover:underline" type="button" @click="selectedBrands = []">CLEAR</button>
            </div>
            <div class="space-y-1.5">
              <label v-for="brand in brands" :key="brand.name" class="flex items-center justify-between group cursor-pointer p-1 rounded hover:bg-surface-variant/20 transition-colors">
                <div class="flex items-center gap-2.5">
                  <input v-model="selectedBrands" :value="brand.name" class="w-3.5 h-3.5 rounded border-outline-variant text-primary focus:ring-offset-0 focus:ring-0" type="checkbox" />
                  <span class="text-sm text-on-surface group-hover:text-primary transition-colors">{{ brand.name }}</span>
                </div>
                <span class="text-[10px] font-medium text-on-surface-variant">{{ brand.count }}</span>
              </label>
            </div>
          </div>

          <div class="p-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-bold text-xs uppercase tracking-widest text-on-surface-variant">RAM Capacity</h3>
              <button class="text-[10px] text-primary font-bold hover:underline" type="button" @click="selectedRam = ''">CLEAR</button>
            </div>
            <div class="grid grid-cols-2 gap-1.5">
              <button
                v-for="ram in ramOptions"
                :key="ram"
                :class="selectedRam === ram ? 'bg-primary text-on-primary ring-2 ring-primary ring-offset-1' : 'border border-outline-variant hover:border-primary hover:text-primary'"
                class="py-1.5 rounded-lg text-xs font-semibold transition-all"
                type="button"
                @click="selectedRam = selectedRam === ram ? '' : ram"
              >
                {{ ram }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </aside>

    <section class="flex-1">
      <div class="bg-surface-container-lowest rounded-xl border border-outline-variant p-4 mb-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="font-bold text-xl text-on-surface mb-0.5 tracking-tight">Premium Laptops</h1>
            <p class="text-xs text-on-surface-variant font-medium">{{ filteredProducts.length }} of {{ products.length }} products found in this category</p>
          </div>
          <div class="flex items-center gap-2">
            <div class="flex items-center bg-surface-container rounded-lg p-0.5 border border-outline-variant mr-2">
              <button
                :class="viewMode === 'grid' ? 'bg-white shadow-sm text-primary' : 'text-on-surface-variant hover:text-on-surface'"
                class="p-2 rounded-md transition-colors"
                title="Grid View"
                type="button"
                @click="viewMode = 'grid'"
              >
                <span class="material-symbols-outlined text-[18px]">grid_view</span>
              </button>
              <button
                :class="viewMode === 'list' ? 'bg-white shadow-sm text-primary' : 'text-on-surface-variant hover:text-on-surface'"
                class="p-2 rounded-md transition-colors"
                title="List View"
                type="button"
                @click="viewMode = 'list'"
              >
                <span class="material-symbols-outlined text-[18px]">view_list</span>
              </button>
            </div>
            <div class="relative">
              <select v-model="sortBy" class="appearance-none bg-surface-container-low border border-outline-variant rounded-lg pl-3 pr-9 py-2 text-xs font-bold text-on-surface focus:ring-1 focus:ring-primary focus:border-primary w-40 cursor-pointer">
                <option value="popularity">Popularity</option>
                <option value="newest">Newest</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
              </select>
              <span class="material-symbols-outlined absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-[16px]">expand_more</span>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-4 border-t border-outline-variant flex flex-wrap gap-2 items-center">
          <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest mr-2">Applied:</span>
          <template v-if="activeFilters.length">
            <button
              v-for="filter in activeFilters"
              :key="`${filter.type}-${filter.label}`"
              class="flex items-center gap-1.5 bg-primary/10 text-primary px-2.5 py-1 rounded-full text-[11px] hover:bg-primary/20 transition-all font-bold border border-primary/20"
              type="button"
              @click="removeFilter(filter)"
            >
              {{ filter.label }} <span class="material-symbols-outlined text-[12px]">close</span>
            </button>
            <button class="text-primary font-bold text-[11px] ml-auto hover:underline flex items-center gap-1" type="button" @click="clearFilters">
              <span class="material-symbols-outlined text-[14px]">filter_list_off</span>
              Clear all filters
            </button>
          </template>
          <span v-else class="text-[11px] text-on-surface-variant font-medium">No filters applied</span>
        </div>
      </div>

      <div
        v-if="filteredProducts.length"
        :class="viewMode === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6' : 'flex flex-col gap-3'"
      >
        <article
          v-for="product in filteredProducts"
          :key="product.id"
          :class="viewMode === 'grid' ? 'catalog-card--grid flex-col rounded-xl hover:shadow-lg' : 'catalog-card--list flex-col rounded-lg hover:border-primary/30 hover:shadow-md'"
          class="catalog-card group flex bg-surface-container-lowest border border-outline-variant transition-all duration-300 overflow-hidden relative"
        >
          <div :class="viewMode === 'grid' ? 'p-3' : 'p-3'" class="catalog-card__media relative bg-white">
            <router-link :to="{ name: 'product-detail', params: { id: product.id } }" :class="viewMode === 'grid' ? 'aspect-square' : 'aspect-[16/10] sm:aspect-square'" class="w-full overflow-hidden rounded-lg bg-surface-container-low relative cursor-pointer block" @click="store.viewProduct(product)">
              <img :alt="product.name" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500 p-4" :src="product.image" />
              <span v-if="product.badge" :class="product.badgeClass" class="absolute top-2 left-2 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest shadow-sm">{{ product.badge }}</span>
            </router-link>
            <button
              :class="viewMode === 'grid' ? 'opacity-0 group-hover:opacity-100' : 'opacity-100'"
              class="absolute top-5 right-5 p-1.5 bg-white/90 backdrop-blur shadow-sm rounded-full text-on-surface-variant hover:text-error hover:scale-110 transition-all"
              type="button"
              @click="store.toggleWishlist(product)"
            >
              <span
                :class="{ 'text-error': store.isInWishlist(product.id) }"
                :style="store.isInWishlist(product.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
                class="material-symbols-outlined text-[18px]"
              >
                favorite
              </span>
            </button>
          </div>
          <div :class="viewMode === 'grid' ? 'px-4 pb-4 flex-col' : 'p-4 md:p-5'" class="catalog-card__body flex flex-1">
            <div class="min-w-0">
              <div class="flex items-center gap-1 mb-1.5">
                <div class="flex text-star-rating">
                  <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[14px]" :style="star <= Math.floor(product.rating) ? 'font-variation-settings: \'FILL\' 1;' : ''">
                    {{ star <= Math.floor(product.rating) ? 'star' : 'star_half' }}
                  </span>
                </div>
                <span class="text-[11px] font-bold text-on-surface-variant ml-1">{{ product.rating.toFixed(1) }} ({{ product.reviews }})</span>
              </div>
              <router-link :to="{ name: 'product-detail', params: { id: product.id } }" class="block text-left" @click="store.viewProduct(product)">
                <h2 :class="viewMode === 'grid' ? 'text-sm line-clamp-2' : 'text-base line-clamp-1'" class="font-semibold text-on-surface group-hover:text-primary transition-colors leading-snug">{{ product.name }}</h2>
              </router-link>
              <p v-if="viewMode === 'list'" class="mt-2 text-xs text-on-surface-variant leading-relaxed max-w-2xl">{{ product.description }}</p>
              <div v-if="viewMode === 'list'" class="mt-3 flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-1 rounded border border-outline-variant bg-surface-container-low px-2 py-1 text-[11px] font-bold text-on-surface-variant">
                  <span class="material-symbols-outlined text-[14px]">business</span>
                  {{ product.brand }}
                </span>
                <span class="inline-flex items-center gap-1 rounded border border-outline-variant bg-surface-container-low px-2 py-1 text-[11px] font-bold text-on-surface-variant">
                  <span class="material-symbols-outlined text-[14px]">memory</span>
                  {{ product.ram }} RAM
                </span>
                <span class="inline-flex items-center gap-1 rounded border border-outline-variant bg-surface-container-low px-2 py-1 text-[11px] font-bold text-on-surface-variant">
                  <span class="material-symbols-outlined text-[14px]">inventory_2</span>
                  In stock
                </span>
              </div>
            </div>
            <div :class="viewMode === 'grid' ? 'pt-4' : 'mt-4'" class="catalog-card__actions flex flex-col gap-3">
              <div :class="viewMode === 'grid' ? 'items-end justify-between' : 'items-start md:items-end justify-between md:justify-start'" class="flex gap-4">
                <div class="flex flex-col">
                  <span v-if="product.oldPrice" class="text-[10px] text-on-surface-variant line-through font-bold">{{ formatPrice(product.oldPrice) }}</span>
                  <span :class="viewMode === 'grid' ? 'text-lg' : 'text-xl'" class="font-bold text-primary tracking-tight">{{ formatPrice(product.price) }}</span>
                </div>
                <label :class="viewMode === 'grid' ? '' : 'md:mt-2'" class="flex items-center gap-1.5 cursor-pointer text-[10px] text-on-surface-variant hover:text-on-surface font-semibold">
                  <input :checked="store.isInCompare(product.id)" class="w-3 h-3 rounded border-outline-variant text-primary focus:ring-0" type="checkbox" @change="store.toggleCompare(product)" />
                  <span>Compare</span>
                </label>
              </div>
              <button :class="viewMode === 'grid' ? 'w-full' : 'w-full sm:w-48 md:w-full'" class="bg-primary text-on-primary font-bold text-xs py-2.5 rounded-lg hover:bg-primary-container active:scale-[0.97] transition-all flex items-center justify-center gap-2 shadow-sm uppercase tracking-wider" type="button" @click="store.addToCart(product)">
                <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
                Add to Cart
              </button>
            </div>
          </div>
        </article>
      </div>

      <div v-else class="bg-surface-container-lowest rounded-xl border border-outline-variant p-10 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant">
          <span class="material-symbols-outlined">search_off</span>
        </div>
        <h2 class="font-bold text-on-surface mb-2">No products match these filters</h2>
        <p class="text-sm text-on-surface-variant mb-5">Try widening the price range or clearing selected brands and RAM.</p>
        <button class="bg-primary text-on-primary font-bold text-xs py-2.5 px-5 rounded-lg hover:bg-primary-container transition-all" type="button" @click="clearFilters">
          Clear filters
        </button>
      </div>

      <nav class="mt-12 flex items-center justify-between border-t border-outline-variant pt-6">
        <button class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-on-surface-variant hover:text-primary hover:bg-surface-variant/20 rounded-lg transition-all">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          PREVIOUS
        </button>
        <div class="flex items-center gap-1">
          <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-primary text-on-primary font-bold text-xs shadow-sm">1</button>
          <button class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-variant/30 hover:text-on-surface transition-all font-bold text-xs">2</button>
          <button class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-variant/30 hover:text-on-surface transition-all font-bold text-xs">3</button>
          <span class="px-2 text-on-surface-variant font-bold text-xs">...</span>
          <button class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-variant/30 hover:text-on-surface transition-all font-bold text-xs">12</button>
        </div>
        <button class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-on-surface-variant hover:text-primary hover:bg-surface-variant/20 rounded-lg transition-all">
          NEXT
          <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
        </button>
      </nav>
    </section>
  </main>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
input[type='range'] {
  -webkit-appearance: none;
  width: 100%;
  background: transparent;
}
input[type='range']::-webkit-slider-runnable-track {
  width: 100%;
  height: 2px;
  cursor: pointer;
  background: #e4e4e7;
  border-radius: 1px;
}
input[type='range']::-webkit-slider-thumb {
  height: 14px;
  width: 14px;
  border-radius: 50%;
  background: #09090b;
  cursor: pointer;
  -webkit-appearance: none;
  margin-top: -6px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  border: 1.5px solid #ffffff;
  transition: transform 0.1s ease;
}
input[type='range']::-webkit-slider-thumb:hover {
  transform: scale(1.1);
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 2.5rem;
}
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
@media (min-width: 640px) {
  .catalog-card--list {
    flex-direction: row;
    align-items: stretch;
  }
  .catalog-card--list .catalog-card__media {
    width: 10rem;
    flex-shrink: 0;
    border-right: 1px solid #e4e4e7;
  }
  .catalog-card--list .catalog-card__media > div {
    aspect-ratio: 1 / 1;
  }
}
@media (min-width: 768px) {
  .catalog-card--list .catalog-card__body {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 180px;
    gap: 1.5rem;
  }
  .catalog-card--list .catalog-card__actions {
    margin-top: 0;
    justify-content: center;
    border-left: 1px solid #e4e4e7;
    padding-left: 1.25rem;
  }
  .catalog-card--list .catalog-card__actions button {
    width: 100%;
  }
}
@media (min-width: 1280px) {
  .catalog-card--list .catalog-card__media {
    width: 12rem;
  }
  .catalog-card--list .catalog-card__body {
    grid-template-columns: minmax(0, 1fr) 220px;
  }
}
</style>

