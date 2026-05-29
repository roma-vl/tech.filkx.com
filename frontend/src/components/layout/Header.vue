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
const isMegaMenuOpen = ref(false);

const popularQueries = [
  'SSD',
  'Кава',
  'Свічки',
  'Чайник',
  'Кросівки',
  'Jack Daniel\'s',
  'Телефон',
  'Мишка',
  'Lego',
  'Сумка',
  'Крокси',
  'Наушники'
];

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

const categories = [
  {
    id: 'smartphones',
    label: 'Smartphones',
    icon: 'smartphone',
    sub: [
      { name: 'iPhone 15 Series', badge: 'New' },
      { name: 'Samsung Galaxy S24', badge: '' },
      { name: 'Google Pixel 8', badge: '' },
      { name: 'Folding Phones', badge: 'Trending' },
      { name: 'Budget Phones', badge: '' },
      { name: 'Accessories', badge: '' },
    ],
  },
  {
    id: 'laptops',
    label: 'Laptops',
    icon: 'laptop',
    sub: [
      { name: 'MacBook Pro & Air', badge: 'New' },
      { name: 'Gaming Laptops', badge: 'Hot' },
      { name: 'Ultrabooks', badge: '' },
      { name: 'Workstations', badge: '' },
      { name: 'Chromebooks', badge: '' },
      { name: 'Laptop Accessories', badge: '' },
    ],
  },
  {
    id: 'gaming',
    label: 'Gaming',
    icon: 'sports_esports',
    sub: [
      { name: 'PlayStation 5', badge: 'Hot' },
      { name: 'Xbox Series X', badge: '' },
      { name: 'Nintendo Switch', badge: '' },
      { name: 'Gaming PCs', badge: '' },
      { name: 'Controllers & Pads', badge: '' },
      { name: 'Gaming Chairs', badge: '' },
    ],
  },
  {
    id: 'audio',
    label: 'Audio',
    icon: 'headphones',
    sub: [
      { name: 'Noise Cancelling', badge: '' },
      { name: 'True Wireless Earbuds', badge: 'New' },
      { name: 'Home Theater', badge: '' },
      { name: 'Smart Speakers', badge: '' },
      { name: 'Studio Monitors', badge: '' },
      { name: 'Soundbars', badge: '' },
    ],
  },
  {
    id: 'wearables',
    label: 'Wearables',
    icon: 'watch',
    sub: [
      { name: 'Apple Watch', badge: 'New' },
      { name: 'Galaxy Watch', badge: '' },
      { name: 'Fitness Trackers', badge: '' },
      { name: 'Smart Rings', badge: 'Trending' },
      { name: 'AR & VR Headsets', badge: '' },
      { name: 'Smart Glasses', badge: '' },
    ],
  },
  {
    id: 'cameras',
    label: 'Cameras',
    icon: 'photo_camera',
    sub: [
      { name: 'DSLR Cameras', badge: '' },
      { name: 'Mirrorless', badge: 'Hot' },
      { name: 'Action Cameras', badge: '' },
      { name: 'Drones', badge: '' },
      { name: 'Lenses', badge: '' },
      { name: 'Camera Bags', badge: '' },
    ],
  },
  {
    id: 'tablets',
    label: 'Tablets',
    icon: 'tablet',
    sub: [
      { name: 'iPad Pro & Air', badge: 'New' },
      { name: 'Samsung Galaxy Tab', badge: '' },
      { name: 'Android Tablets', badge: '' },
      { name: 'E-Readers', badge: '' },
      { name: 'Tablet Cases', badge: '' },
      { name: 'Stylus & Pens', badge: '' },
    ],
  },
  {
    id: 'smart-home',
    label: 'Smart Home',
    icon: 'home',
    sub: [
      { name: 'Smart Displays', badge: '' },
      { name: 'Smart Bulbs', badge: '' },
      { name: 'Security Cameras', badge: '' },
      { name: 'Smart Locks', badge: '' },
      { name: 'Robot Vacuums', badge: 'Hot' },
      { name: 'Smart Thermostats', badge: '' },
    ],
  },
];

const activeCat = ref(categories[0]);

const selectCategory = (cat) => {
  activeCat.value = cat;
};

const clickCategorySub = (subName) => {
  isMegaMenuOpen.value = false;
  router.push({ name: 'catalog', query: { category: activeCat.value.id, sub: subName.toLowerCase() } });
};

const selectPopularQuery = (query) => {
  searchQuery.value = query;
  triggerSearch();
};

const triggerSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'catalog', query: { search: searchQuery.value.trim() } });
    showDropdown.value = false;
  }
};

const selectSearchResult = (product) => {
  store.addToCart(product);
  searchQuery.value = '';
  showDropdown.value = false;
};

const formatPrice = (price) => {
  return new Intl.NumberFormat('uk-UA', {
    style: 'currency',
    currency: 'UAH',
    maximumFractionDigits: 0
  }).format(price);
};

const highlightMatch = (name, query) => {
  if (!query.trim()) return name;
  const regex = new RegExp(`(${query.trim()})`, 'gi');
  return name.replace(regex, '<span class="font-extrabold text-[#09090b]">$1</span>');
};

const filteredProducts = computed(() => {
  if (!searchQuery.value.trim()) return [];
  const q = searchQuery.value.toLowerCase().trim();
  return allProducts.filter(p =>
    p.name.toLowerCase().includes(q) ||
    p.category.toLowerCase().includes(q)
  ).slice(0, 8);
});

const handleKeydown = (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault();
    searchInput.value?.focus();
  }
  if (e.key === 'Escape') {
    showDropdown.value = false;
    isMegaMenuOpen.value = false;
  }
};

const handleClickOutside = (e) => {
  const container = searchInput.value?.closest('.search-container');
  if (container && !container.contains(e.target)) {
    showDropdown.value = false;
  }
  const megaWrap = document.querySelector('.mega-menu-wrapper');
  if (megaWrap && !megaWrap.contains(e.target) && !e.target.closest('.catalog-btn') && !e.target.closest('.burger-btn')) {
    isMegaMenuOpen.value = false;
  }
};

const toggleCatalog = () => {
  isMegaMenuOpen.value = !isMegaMenuOpen.value;
};

const triggerVoiceSearch = () => {
  store.addToast("Voice search activated. Start speaking...", "info");
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
  <!-- Main Header Shell -->
  <header class="sticky top-0 z-50 w-full bg-[#211f1f] text-white shadow-md">
    <div class="max-w-container-max mx-auto h-16 px-4 md:px-8 flex items-center justify-between gap-4">
      
      <!-- Left: Burger & Logo -->
      <div class="flex items-center gap-3">
        <!-- Burger Button -->
        <button 
          @click="toggleCatalog"
          class="burger-btn p-1.5 hover:bg-white/10 rounded-lg transition-colors flex items-center justify-center"
          title="Toggle Categories"
        >
          <span class="material-symbols-outlined text-2xl text-white">menu</span>
        </button>

        <!-- Brand/Logo (Rozetka Green Smiley Style) -->
        <a class="flex items-center gap-2 hover:opacity-90 transition-opacity" href="/" @click.prevent="router.push('/')">
          <div class="w-8 h-8 rounded-full bg-[#00a046] flex items-center justify-center shadow-md">
            <span class="material-symbols-outlined text-white text-[20px] font-bold">sentiment_very_satisfied</span>
          </div>
          <span class="font-extrabold text-lg tracking-tight text-white hidden sm:inline-block">
            FilkxTech
          </span>
        </a>
      </div>

      <!-- Catalog Toggle Button -->
      <button 
        @click="toggleCatalog"
        :class="isMegaMenuOpen ? 'bg-white/20 border-white/40' : 'border-white/20 hover:bg-white/10 hover:border-white/40'"
        class="catalog-btn hidden md:flex items-center gap-2 border px-4 py-2 rounded-lg font-bold text-sm tracking-wide text-white transition-all"
      >
        <span class="material-symbols-outlined text-[18px]">grid_view</span>
        <span>Каталог</span>
      </button>

      <!-- Center: Rozetka Styled Search Input -->
      <div class="flex-grow max-w-3xl search-container relative z-40">
        <form @submit.prevent="triggerSearch" class="flex items-center bg-white rounded-lg overflow-hidden shadow-sm h-10 border border-transparent focus-within:border-zinc-500">
          <div class="relative flex-grow flex items-center h-full px-3">
            <span class="material-symbols-outlined text-zinc-400 mr-2 text-[20px]">search</span>
            <input
              ref="searchInput"
              v-model="searchQuery"
              @focus="showDropdown = true"
              class="w-full h-full text-zinc-800 text-sm focus:outline-none placeholder-zinc-400 bg-transparent"
              placeholder="Я шукаю..."
              type="text"
            />
            <!-- Clear Search Button -->
            <button 
              v-if="searchQuery" 
              type="button" 
              @click="searchQuery = ''" 
              class="p-1 text-zinc-400 hover:text-zinc-600 mr-1 flex items-center justify-center"
            >
              <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
            <!-- Microphone Voice Search Button -->
            <button 
              type="button" 
              @click="triggerVoiceSearch" 
              class="p-1 text-zinc-400 hover:text-zinc-600 flex items-center justify-center"
            >
              <span class="material-symbols-outlined text-[20px]">mic</span>
            </button>
          </div>
          <!-- Rozetka Green Find Button -->
          <button 
            type="submit" 
            class="bg-[#00a046] hover:bg-[#00b050] text-white font-bold px-6 h-full text-sm transition-colors border-l border-zinc-200 shrink-0"
          >
            Знайти
          </button>
        </form>

        <!-- Dropdown Panel (Rozetka matching auto-completion & popular queries) -->
        <div
          v-if="showDropdown"
          class="absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-2xl border border-zinc-200 z-[110] overflow-hidden p-4 flex flex-col gap-4 animate-in fade-in slide-in-from-top-2 duration-200"
        >
          <!-- Suggestions results -->
          <div v-if="filteredProducts.length > 0" class="space-y-1">
            <div class="text-[10px] font-black uppercase text-zinc-400 tracking-wider mb-2">
              Результати пошуку
            </div>
            <div
              v-for="prod in filteredProducts"
              :key="prod.id"
              @click="selectSearchResult(prod)"
              class="flex items-center gap-3 p-2 hover:bg-zinc-50 rounded-lg cursor-pointer transition-colors group/item"
            >
              <div class="w-10 h-10 bg-zinc-100 rounded p-1 shrink-0 flex items-center justify-center">
                <img class="max-h-full object-contain" :src="prod.image" :alt="prod.name" />
              </div>
              <div class="flex-grow">
                <p class="text-[9px] font-bold text-zinc-400 uppercase tracking-wide leading-none mb-0.5">{{ prod.category }}</p>
                <p class="text-xs text-zinc-700 line-clamp-1 group-hover/item:text-primary transition-colors" v-html="highlightMatch(prod.name, searchQuery)"></p>
              </div>
              <span class="text-xs font-bold text-zinc-900 shrink-0">{{ formatPrice(prod.price) }}</span>
            </div>
          </div>

          <div v-else-if="searchQuery.trim()" class="text-center text-xs text-zinc-400 py-2">
            Нічого не знайдено для "{{ searchQuery }}".
          </div>

          <!-- Popular Searches (Rozetka Tags Style) -->
          <div class="space-y-2.5">
            <div class="text-[10px] font-black uppercase text-zinc-400 tracking-wider">
              Популярні запити
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="tag in popularQueries"
                :key="tag"
                type="button"
                @click="selectPopularQuery(tag)"
                class="px-3 py-1.5 border border-zinc-200 rounded-lg text-xs font-medium text-zinc-600 hover:bg-zinc-50 hover:border-zinc-300 transition-colors"
              >
                {{ tag }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Compact Icon Actions -->
      <div class="flex items-center gap-4 md:gap-6 text-white">
        
        <!-- Account -->
        <Dropdown align="right" width="60">
          <template #trigger>
            <button class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center" title="Account">
              <span class="material-symbols-outlined text-[24px]">person</span>
            </button>
          </template>

          <template #content>
            <template v-if="authStore.isAuthenticated">
              <div class="p-4 bg-zinc-50 border-b border-zinc-200 flex items-center gap-3 text-zinc-800">
                <div class="w-10 h-10 rounded-full bg-[#00a046] flex items-center justify-center text-white font-bold">
                  {{ authStore.user?.name ? authStore.user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase() : 'U' }}
                </div>
                <div>
                  <p class="text-sm font-bold leading-none">{{ authStore.user?.name }}</p>
                  <p class="text-[10px] text-zinc-500 mt-1">{{ authStore.user?.email }}</p>
                </div>
              </div>
              <div class="p-2 flex flex-col gap-1">
                <button
                  @click="router.push({ name: 'account', query: { tab: 'dashboard' } })"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-zinc-800"
                >
                  <span class="material-symbols-outlined text-lg">person</span>
                  <span class="text-xs font-semibold">Кабінет</span>
                </button>
                <button
                  @click="router.push({ name: 'account', query: { tab: 'orders' } })"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-zinc-800"
                >
                  <span class="material-symbols-outlined text-lg">shopping_bag</span>
                  <span class="text-xs font-semibold">Мої замовлення</span>
                </button>
                <button
                  @click="authStore.logout().then(() => router.push('/login'))"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-error"
                >
                  <span class="material-symbols-outlined text-lg text-red-500">logout</span>
                  <span class="text-xs font-semibold text-red-600">Вихід</span>
                </button>
              </div>
            </template>
            <template v-else>
              <div class="p-2 flex flex-col gap-1">
                <button
                  @click="router.push('/login')"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-zinc-800"
                >
                  <span class="material-symbols-outlined text-lg">login</span>
                  <span class="text-xs font-semibold">Увійти</span>
                </button>
                <button
                  @click="router.push('/register')"
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-zinc-800"
                >
                  <span class="material-symbols-outlined text-lg">person_add</span>
                  <span class="text-xs font-semibold">Реєстрація</span>
                </button>
              </div>
            </template>
          </template>
        </Dropdown>

        <!-- Compare -->
        <button
          @click="store.openDrawer('compare')"
          class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
          title="Compare"
        >
          <span class="material-symbols-outlined text-[24px]">compare_arrows</span>
          <span
            v-if="store.compareCount > 0"
            class="absolute -top-1 -right-1.5 bg-[#00a046] text-white text-[9px] w-4.5 h-4.5 rounded-full flex items-center justify-center font-bold"
          >
            {{ store.compareCount }}
          </span>
        </button>

        <!-- Wishlist -->
        <button
          @click="store.openDrawer('wishlist')"
          class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
          title="Wishlist"
        >
          <span class="material-symbols-outlined text-[24px]">favorite</span>
          <span
            v-if="store.wishlistCount > 0"
            class="absolute -top-1 -right-1.5 bg-[#00a046] text-white text-[9px] w-4.5 h-4.5 rounded-full flex items-center justify-center font-bold"
          >
            {{ store.wishlistCount }}
          </span>
        </button>

        <!-- Cart -->
        <button
          @click="store.openDrawer('cart')"
          class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
          title="Cart"
        >
          <span class="material-symbols-outlined text-[24px]">shopping_cart</span>
          <span
            v-if="store.cartCount > 0"
            class="absolute -top-1 -right-1.5 bg-[#00a046] text-white text-[9px] w-4.5 h-4.5 rounded-full flex items-center justify-center font-bold"
          >
            {{ store.cartCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Dropdown Floating Mega Menu Overlay -->
    <div 
      v-if="isMegaMenuOpen" 
      class="mega-menu-wrapper absolute left-0 right-0 top-full bg-white text-zinc-900 border-t border-zinc-200 shadow-2xl z-[120] duration-200 flex"
    >
      <div class="max-w-container-max mx-auto w-full flex min-h-[480px]">
        <!-- Left Sidebar: Category list -->
        <div class="w-1/4 border-r border-zinc-200 bg-zinc-50 p-4">
          <ul class="space-y-1">
            <li
              v-for="cat in categories"
              :key="cat.id"
              @mouseenter="selectCategory(cat)"
              :class="activeCat.id === cat.id ? 'bg-[#00a046] text-white font-bold' : 'hover:bg-zinc-150 text-zinc-700'"
              class="flex items-center justify-between p-2.5 rounded-lg cursor-pointer transition-colors"
            >
              <div class="flex items-center gap-2.5">
                <span class="material-symbols-outlined text-[20px]">{{ cat.icon }}</span>
                <span class="text-xs uppercase tracking-wider font-semibold">{{ cat.label }}</span>
              </div>
              <span class="material-symbols-outlined text-[16px] opacity-75">chevron_right</span>
            </li>
          </ul>
        </div>

        <!-- Center/Right: Sub-categories Grid -->
        <div class="flex-grow p-8 bg-white flex flex-col justify-between">
          <div>
            <div class="flex items-center gap-2 mb-6 text-[#00a046] border-b border-zinc-100 pb-3">
              <span class="material-symbols-outlined text-[24px]">{{ activeCat.icon }}</span>
              <h3 class="text-base font-black uppercase tracking-wider">{{ activeCat.label }}</h3>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="sub in activeCat.sub"
                :key="sub.name"
                @click="clickCategorySub(sub.name)"
                class="p-4 border border-zinc-100 hover:border-zinc-200 hover:bg-zinc-50 rounded-xl cursor-pointer transition-all flex flex-col justify-between h-20"
              >
                <div class="flex justify-between items-start gap-2">
                  <span class="text-xs font-bold text-zinc-800 leading-snug">{{ sub.name }}</span>
                  <span v-if="sub.badge" class="bg-red-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-wider shrink-0">{{ sub.badge }}</span>
                </div>
                <span class="text-[10px] text-zinc-400 group-hover:text-primary transition-colors flex items-center gap-1 font-semibold uppercase">
                  Переглянути товари <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                </span>
              </div>
            </div>
          </div>

          <div class="mt-8 pt-4 border-t border-zinc-100 text-right">
            <button 
              @click="isMegaMenuOpen = false; router.push({ name: 'catalog', query: { category: activeCat.id } })"
              class="inline-flex items-center gap-1.5 text-xs font-black text-[#00a046] hover:underline uppercase tracking-widest"
            >
              Всі товари {{ activeCat.label }} <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
