<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { store } from '@/store.js';
import { useAuthStore } from '@/stores/auth.js';
import Dropdown from '@/components/ui/Dropdown.vue';

const router = useRouter();
const authStore = useAuthStore();
const searchInput = ref(null);
const searchQuery = ref('');
const showDropdown = ref(false);

const allProducts = [
  {
    id: 1,
    name: 'Studio Pro Noise Cancelling Wireless Headphones',
    category: 'Audio Tech',
    price: 189.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBiUjsdg3UEZ3Invqr34vCkdhNreoHpan9Pb-BGKmaVy0RU0uSQyzTsKkbXAwFc883Y5jTxMbVRUKZoDSaTo3FbaT7XaZnss_re9YT00JhaTBlC6yvueGFeJfKhhh6JIjDtiTNIfRdJjC8ZyTTSHtYB81L85eJ1STBcLutY96W12sDqOctNxTwyq1m0MT7_6PTUKAE858poN7UqRe7nE46hjcjRrp_larxv7sHMDVCn7iT7817fw1OcxPdOG2sWfInGcMEAEIPakTE',
    description: 'Professional high-fidelity sound with advanced hybrid active noise cancellation, ambient pass-through mode, and up to 40 hours of battery life.'
  },
  {
    id: 2,
    name: '32" Ultra-Wide Curved Gaming Monitor 144Hz',
    category: 'Gaming',
    price: 499.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVMdErktV3TxMtO3JGMvZ9zS0x0zYbKz1BOjfXYU1LCka8ZihAQRInqqCc_p8i-qxa_HPIqDZ5Kevwr5VKLqyqstWGjEH_WRir7OtrPCpV_H8AvfRt69AI8p1TEbtE5tbqG2zcqQqVYp5pEPBnpsxa17bgXzaPwXHLRwCkbNLOL2MDuK_HzBB7j5pEfGKiX20Mo3vcs919pbLNN6aCAU31C3gWvj4f1OiGSSrW_Xd-ECi9ml_qk2QQhzRko2TzwHZxUspi7SRTQJg',
    description: 'Immersive curved screen with 144Hz refresh rate, 1ms response time, FreeSync Premium support, and breathtaking colors.'
  },
  {
    id: 3,
    name: 'FitTrack Pro 5 Health & GPS Smartwatch',
    category: 'Wearables',
    price: 129.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAG1j0OkwUBTuRJYlZq12iTWSfCx0hTSkVryqbF-FYIDY0p2IODoLkQCjdBzvMUE28jinrTZ-Yhx8ATbKeWyMq2bSKQehQZ5dUfTVBStqMLuWl_dnxdhw_-eZMoiP9_egaelvQQU7gzfXiv-g6KH0W1d7n6iYmoObJXDCEXbrnLagqWXZxOIyeHX_fQAyS84ZvkaGDv8Ld75VMMB-p3JQk_MupRVz9V0REcSykJQllrCavETBkPh8j054bmRUv5No-7faKEr_uRPp0',
    description: 'Track daily activity, heart rate, blood oxygen levels, workouts, sleep stages, and route navigation with precision built-in GPS.'
  },
  {
    id: 4,
    name: 'Lumix Alpha Prime v2 Mirrorless Camera',
    category: 'Cameras',
    price: 1150.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuATZZV5WGL8X21fMJ4zbzJqjOQrP5WtH59wdZ76aQq8CQz4N-_BW5kXynAsvxVY7V9KfXszWmJgUfQbJP92IJNVprYoqpbyPA1X8MLw3pVOwBJ8VSb4aH5zwNl18jlhXVFDAv8rEtCBSTQw23VkV72JflGsCm_YankTEulT2iRKpF041yLSapjVqyWOk9nP-vg3RI9GljpcKRP-ftNvSORJcNl0YjjQn6rD0krvStirsy_BBHTMZ8Dd3-OSCaaeIJZanzdpfzxUSuw',
    description: 'Capture world-class details with a high-resolution full-frame sensor, 4K/60p video, fast auto-focus tracking, and dual image stabilization.'
  },
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

const filteredProducts = computed(() => {
  if (!searchQuery.value.trim()) return [];
  const q = searchQuery.value.toLowerCase().trim();
  return allProducts.filter(p =>
    p.name.toLowerCase().includes(q) ||
    p.category.toLowerCase().includes(q)
  ).slice(0, 5);
});

const handleKeydown = (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault();
    searchInput.value?.focus();
  }
  if (e.key === 'Escape') {
    showDropdown.value = false;
  }
};

const selectSearchResult = (product) => {
  store.addToCart(product);
  searchQuery.value = '';
  showDropdown.value = false;
};

// Click outside handling for search dropdown
const handleClickOutside = (e) => {
  const container = searchInput.value?.closest('.group');
  if (container && !container.contains(e.target)) {
    showDropdown.value = false;
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <!-- Main Sticky Header -->
  <header class="sticky top-0 z-50 w-full bg-surface-container-lowest/95 backdrop-blur-md shadow-sm border-b border-surface-variant">
    <div class="max-w-container-max mx-auto h-20 px-margin-desktop flex items-center justify-between gap-gutter">
      <!-- Brand -->
      <a class="font-display-lg text-display-lg font-bold text-primary flex-shrink-0 tracking-tight" href="/">
        ElectroLux
      </a>

      <!-- Search Bar (Refined & Large) -->
      <div class="flex-grow max-w-2xl relative z-40">
        <div class="relative group">
          <input
            ref="searchInput"
            v-model="searchQuery"
            @focus="showDropdown = true"
            class="w-full h-12 pl-12 pr-4 bg-surface-container-low border-none ring-1 ring-outline-variant rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:bg-white transition-all font-body-md shadow-inner"
            placeholder="Search premium electronics..."
            type="text"
          />
          <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant group-focus-within:text-primary transition-colors">search</span>
          <div class="absolute right-3 top-1/2 -translate-y-1/2 hidden md:flex items-center gap-1.5 px-2 py-1 bg-surface-container-highest rounded text-[10px] font-bold text-on-surface-variant border border-outline-variant/30">
            <span>⌘</span><span>K</span>
          </div>
        </div>

        <!-- Live Search Autocomplete Dropdown -->
        <div
          v-if="showDropdown && filteredProducts.length > 0"
          class="absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-surface-variant z-[110] overflow-hidden p-2 flex flex-col gap-1 max-h-[360px] overflow-y-auto animate-in fade-in slide-in-from-top-2 duration-200"
        >
          <div class="px-3 py-1.5 text-[10px] font-black uppercase text-on-surface-variant tracking-wider border-b border-surface-variant/35 mb-1">
            Search Suggestions
          </div>
          <div
            v-for="prod in filteredProducts"
            :key="prod.id"
            @click="selectSearchResult(prod)"
            class="flex items-center gap-3 p-2 hover:bg-surface-container rounded-lg cursor-pointer transition-colors group/item"
          >
            <div class="w-10 h-10 bg-surface-container-low rounded p-1 shrink-0 flex items-center justify-center">
              <img class="max-h-full object-contain" :src="prod.image" :alt="prod.name" />
            </div>
            <div class="flex-grow">
              <p class="text-[9px] font-bold text-primary uppercase tracking-wide leading-none mb-0.5">{{ prod.category }}</p>
              <p class="text-xs font-semibold text-on-surface line-clamp-1 group-hover/item:text-primary transition-colors">{{ prod.name }}</p>
            </div>
            <div class="shrink-0 flex items-center gap-3">
              <span class="text-xs font-bold text-on-surface">${{ prod.price.toFixed(2) }}</span>
              <button
                class="w-7 h-7 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center hover:scale-105 active:scale-95 transition-transform"
                title="Add to Cart"
              >
                <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Empty Results Alert -->
        <div
          v-if="showDropdown && searchQuery.trim() && filteredProducts.length === 0"
          class="absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-surface-variant z-[110] p-4 text-center text-xs text-on-surface-variant"
        >
          No premium products found for "{{ searchQuery }}".
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-8 text-on-surface">
        <!-- Account -->
        <Dropdown align="right" width="60">
          <template #trigger>
            <button
              class="flex flex-col items-center gap-1 hover:text-primary transition-colors relative"
            >
              <span class="material-symbols-outlined">person</span>
              <span class="text-[10px] font-bold uppercase tracking-wider">Account</span>
            </button>
          </template>

          <template #content>
            <template v-if="authStore.isAuthenticated">
              <div class="p-4 bg-surface-container-low border-b border-surface-variant flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-bold">
                  {{ authStore.user?.name ? authStore.user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'U' }}
                </div>
                <div>
                  <p class="text-sm font-bold text-on-surface leading-none">{{ authStore.user?.name }}</p>
                  <p class="text-[10px] text-on-surface-variant mt-1">{{ authStore.user?.email }}</p>
                </div>
              </div>
              <div class="p-2 flex flex-col gap-1">
                <button
                  @click="router.push({ name: 'account', query: { tab: 'dashboard' } })"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface-variant transition-colors text-left w-full"
                >
                  <span class="material-symbols-outlined text-lg">person</span>
                  <span class="text-xs font-semibold text-on-surface">My Profile</span>
                </button>
                <button
                  @click="router.push({ name: 'account', query: { tab: 'orders' } })"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface-variant transition-colors text-left w-full"
                >
                  <span class="material-symbols-outlined text-lg">shopping_bag</span>
                  <span class="text-xs font-semibold text-on-surface">Order History</span>
                </button>
                <button
                  @click="router.push({ name: 'account', query: { tab: 'settings' } })"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface-variant transition-colors text-left w-full"
                >
                  <span class="material-symbols-outlined text-lg">settings</span>
                  <span class="text-xs font-semibold text-on-surface">Settings</span>
                </button>
                <div class="h-px bg-surface-variant my-1"></div>
                <button
                  @click="authStore.logout().then(() => router.push('/login'))"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface-variant transition-colors text-left w-full text-error"
                >
                  <span class="material-symbols-outlined text-lg">logout</span>
                  <span class="text-xs font-semibold">Sign Out</span>
                </button>
              </div>
            </template>
            <template v-else>
              <div class="p-2 flex flex-col gap-1">
                <button
                  @click="router.push('/login')"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface-variant transition-colors text-left w-full"
                >
                  <span class="material-symbols-outlined text-lg">login</span>
                  <span class="text-xs font-semibold text-on-surface">Sign In</span>
                </button>
                <button
                  @click="router.push('/register')"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface-variant transition-colors text-left w-full"
                >
                  <span class="material-symbols-outlined text-lg">person_add</span>
                  <span class="text-xs font-semibold text-on-surface">Register</span>
                </button>
              </div>
            </template>
          </template>
        </Dropdown>

        <!-- Compare -->
        <button
          @click="store.openDrawer('compare')"
          class="flex flex-col items-center gap-1 hover:text-primary transition-colors hidden xl:flex relative"
        >
          <span class="material-symbols-outlined">compare_arrows</span>
          <span class="text-[10px] font-bold uppercase tracking-wider">Compare</span>
          <span
            v-if="store.compareCount > 0"
            class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold animate-pulse"
          >
            {{ store.compareCount }}
          </span>
        </button>

        <!-- Wishlist -->
        <button
          @click="store.openDrawer('wishlist')"
          class="flex flex-col items-center gap-1 hover:text-primary transition-colors relative"
        >
          <span class="material-symbols-outlined">favorite</span>
          <span class="text-[10px] font-bold uppercase tracking-wider">Wishlist</span>
          <span
            v-if="store.wishlistCount > 0"
            class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold"
          >
            {{ store.wishlistCount }}
          </span>
        </button>

        <!-- Cart -->
        <button
          @click="store.openDrawer('cart')"
          class="flex flex-col items-center gap-1 hover:text-primary transition-colors relative"
        >
          <span class="material-symbols-outlined">shopping_cart</span>
          <span class="text-[10px] font-bold uppercase tracking-wider">Cart</span>
          <span
            v-if="store.cartCount > 0"
            class="absolute -top-1 -right-1 bg-primary text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center font-bold"
          >
            {{ store.cartCount }}
          </span>
        </button>
      </div>
    </div>
  </header>
</template>
