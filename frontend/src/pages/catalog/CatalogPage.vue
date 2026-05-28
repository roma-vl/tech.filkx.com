<script setup>
import { computed, onMounted, ref } from 'vue';
import { store } from '@/store.js';

const viewMode = ref('grid');
const sortBy = ref('popularity');
const priceMin = ref(0);
const priceMax = ref(200000);
const selectedBrands = ref([]);
const selectedRam = ref('');
const selectedRating = ref('');
const onlyDiscounts = ref(false);
const onlyInStock = ref(false);

const isMobileFilterOpen = ref(false);
const selectedProductForQuickView = ref(null);
const isQuickViewOpen = ref(false);

const products = [
  {
    id: 101,
    name: 'Apple MacBook Pro 14" M3 Pro Space Black',
    brand: 'Apple',
    ram: '18GB',
    category: 'Laptops',
    price: 84990,
    rating: 4.9,
    reviews: 128,
    badge: 'Новинка',
    badgeClass: 'bg-[#00a046]',
    inStock: true,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM',
    description: 'Портативна професійна продуктивність з дисплеєм Liquid Retina XDR, надшвидкою об’єднаною пам’яттю та роботою від батареї протягом усього дня.',
    specs: {
      processor: 'Apple M3 Pro (11-core CPU, 14-core GPU)',
      screen: '14.2" Liquid Retina XDR (3024x1964) 120Hz',
      storage: '512GB SSD Superfast',
      os: 'macOS Sonoma',
      weight: '1.61 кг'
    }
  },
  {
    id: 102,
    name: 'Dell XPS 15 9530 Platinum Silver',
    brand: 'Dell',
    ram: '32GB',
    category: 'Laptops',
    price: 97990,
    oldPrice: 105990,
    rating: 4.8,
    reviews: 92,
    badge: 'Акція -8000 ₴',
    badgeClass: 'bg-rose-600',
    inStock: true,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDNXpOdOi1q9K16_agnjDdmva4mM8QDf9TI4MCTsRa0_OXpmRLAkd2BmZ0IpQebeCf9T-oqp5EXZIEqu5AgJgO3UAZfh8JwEUwazBkmMcqSqi5NOJjpKjWbdNN6PVkBt40FEXcJMc2b-kYP2x4afcnwiPcUckUaDsOZfW3QlxwFPMxfrXvfI7xR-8qcpi8AlkYYBVIucffemoFhQigVY-yrdYAUIMrcC6HgcPyO99EpuBM4WdjdU2LJpA6MY3BhgG7BudOrk4ZPlNw',
    description: 'Ноутбук преміум-класу для творців контенту з потужною дискретною графікою NVIDIA RTX, яскравим безрамковим дисплеєм InfinityEdge та алюмінієвим корпусом.',
    specs: {
      processor: 'Intel Core i9-13900H (14 ядер, до 5.4 ГГц)',
      screen: '15.6" OLED 3.5K (3456x2160) Сенсорний',
      storage: '1TB SSD NVMe PCIe',
      os: 'Windows 11 Pro',
      weight: '1.92 кг'
    }
  },
  {
    id: 103,
    name: 'ASUS ROG Zephyrus G14 Moonlight White',
    brand: 'ASUS',
    ram: '16GB',
    category: 'Laptops',
    price: 67990,
    rating: 5.0,
    reviews: 45,
    badge: 'Топ продажів',
    badgeClass: 'bg-amber-500',
    inStock: true,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDr331B7FabLZcRGhJ_DbZowzkaew5s_GJfms-DS1LXHrCr9JrEM_qiTSvHHdcRLOQU4NygZqdg2vzSEP8qolpkbrEuPi83FukM8x4ZzJpflfXCL5i6WZw99Ro2W_kJSyPwSKmBh7aTJ89xk_sSMwhQZu0di9CfY_tYG8xsS9crK6wdrdWzCio8Ct_P6vzzIdKMqZSvWk-cI5tR8P_uuTugKKtObu44X83uzkFVwQ768UhPlN4P_9soMg2YidbSr7gU_mGJdorHV3E',
    description: 'Компактна геймерська станція з матрицею ROG Nebula, потужною відеокартою GeForce RTX 4070 та чудовою автономністю для подорожей.',
    specs: {
      processor: 'AMD Ryzen 9 7940HS (8 ядер, до 5.2 ГГц)',
      screen: '14" IPS QHD+ (2560x1600) 165Hz G-Sync',
      storage: '1TB SSD PCIe 4.0',
      os: 'Без ОС',
      weight: '1.65 кг'
    }
  },
  {
    id: 104,
    name: 'Lenovo ThinkPad X1 Carbon Gen 11 Deep Black',
    brand: 'Lenovo',
    ram: '16GB',
    category: 'Laptops',
    price: 61990,
    rating: 4.7,
    reviews: 210,
    inStock: true,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuB7ehlQmQnyIbqZ7UKgr8V0mCEj26xp7hzfddOM2Bmm2eSXyygw_1Pa5UtAUjF8FiEFuuTKU2nirKQ6xTW89jAyo3ptFohpbMoa763DzIGnU9WcYlFze2ITHt1uim5itGotu78u02aygZyLgplNTR_YD5AGF0YTqbbYnWcLi0svVRaGzDbKcLNNWAmXWoHRHJc36gfBS75lwJHiIYn9iv35SWv3yGi7oWiWjLQtJV3USiCRNLvPQYgGVjdtW7X71knwmeOoQXbY0cA',
    description: 'Легендарний корпоративний ультрабук з надлегким корпусом із вуглецевого волокна, найкращою у своєму класі клавіатурою та надійним захистом даних.',
    specs: {
      processor: 'Intel Core i7-1365U vPro (10 ядер, до 5.2 ГГц)',
      screen: '14" IPS WUXGA (1920x1200) Антивідблисковий',
      storage: '512GB SSD NVMe Opal2',
      os: 'Windows 11 Pro',
      weight: '1.12 кг'
    }
  },
  {
    id: 105,
    name: 'HP Spectre x360 Luxury 2-in-1 Nightfall Blue',
    brand: 'HP',
    ram: '16GB',
    category: 'Laptops',
    price: 59990,
    rating: 4.6,
    reviews: 56,
    inStock: false,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBQiu2QhryOyOxSSyZfWD9MeA0GEjrvV--xa1qwaS8hrlevCY7YgST20v_LnbFwuZSSQdzVOsu2UIszOqE4us7tgsKjEgTwEdx_oCNLd3WkEtX6Xchu-OJXN0HFiNi5FMwtNcN3vOE4aPigIDgb0iQsyL0uyZTRUVKoRY8-w1mQid0uPH6ua6yBODBCxg2VRofafATzyTk29_fVakQo7pDLYkoZpnlabZ-a6KrP-8mkENT9CmR6ScK9g2GjyiSJHaJ20jDKnRfWURM',
    description: 'Преміальний трансформер, який легко перетворюється з ноутбука на планшет. OLED-екран високої точності з підтримкою фірмового стилуса.',
    specs: {
      processor: 'Intel Core i7-13700H (14 ядер, до 5.0 ГГц)',
      screen: '16" OLED 3K+ (3072x1920) 120Hz Сенсорний',
      storage: '512GB SSD NVMe PCIe',
      os: 'Windows 11 Home',
      weight: '2.15 кг'
    }
  },
  {
    id: 106,
    name: 'Razer Blade 16 Dual Mode Mini-LED Black',
    brand: 'Razer',
    ram: '32GB',
    category: 'Laptops',
    price: 182990,
    rating: 4.9,
    reviews: 18,
    badge: 'Обмежена кількість',
    badgeClass: 'bg-amber-600',
    inStock: true,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBkksX47BmkwjAiSzTUIEmWwzDqhcckudBCmKAGA-iUC_9VuRipCKBceGV1cZ2RBI2VLhB8yvdfmaC06PXzi0r2fcnLBPv3CgwfTMDLB_GIRPkwhljwi0940O06xAnYQKKdCkdVpI1_VIF_-YWnoFcn7XIGMoGRLNdjWbH7SAwNwayPVReoUGckbhit9qQh_0Ah89KFN093j6spzUDUYz1mmH84ykE43aqnzG6oM5CvxmMAqqmJXhlQ37JF5NcZfX-XPImae1pnjfw',
    description: 'Флагманська ігрова система в тонкому суцільнометалевому корпусі. Унікальний дисплей з можливістю апаратного перемикання роздільної здатності та частоти.',
    specs: {
      processor: 'Intel Core i9-13950HX (24 ядра, до 5.5 ГГц)',
      screen: '16" Mini-LED QHD+ 240Hz / UHD+ 120Hz',
      storage: '1TB SSD NVMe PCIe 4.0',
      os: 'Windows 11 Home',
      weight: '2.45 кг'
    }
  },
  {
    id: 107,
    name: 'ASUS Zenbook 14 OLED Ponder Blue',
    brand: 'ASUS',
    ram: '16GB',
    category: 'Laptops',
    price: 39990,
    rating: 4.5,
    reviews: 31,
    inStock: true,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA',
    description: 'Стильний, легкий та екологічний ноутбук з чудовим OLED-екраном 90 Гц, тривалим терміном служби акумулятора та продуктивним процесором Ryzen.',
    specs: {
      processor: 'AMD Ryzen 7 7730U (8 ядер, до 4.5 ГГц)',
      screen: '14" OLED 2.8K (2880x1800) 90Hz',
      storage: '512GB SSD NVMe',
      os: 'Без ОС',
      weight: '1.39 кг'
    }
  },
  {
    id: 108,
    name: 'Apple MacBook Air 13" M3 Space Gray',
    brand: 'Apple',
    ram: '8GB',
    category: 'Laptops',
    price: 54990,
    rating: 4.8,
    reviews: 73,
    badge: 'Суперціна',
    badgeClass: 'bg-emerald-600',
    inStock: true,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM',
    description: 'Надзвичайно тонкий і швидкий ноутбук на базі революційного чипа Apple M3. Безшумна робота без вентиляторів та до 18 годин автономності.',
    specs: {
      processor: 'Apple M3 (8-core CPU, 8-core GPU)',
      screen: '13.6" Liquid Retina (2560x1664) True Tone',
      storage: '256GB SSD',
      os: 'macOS Sonoma',
      weight: '1.24 кг'
    }
  }
];

const formatPrice = (price) => {
  return new Intl.NumberFormat('uk-UA', {
    style: 'currency',
    currency: 'UAH',
    maximumFractionDigits: 0
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
    const matchesDiscount = !onlyDiscounts.value || product.oldPrice !== undefined;
    const matchesStock = !onlyInStock.value || product.inStock;
    const matchesRating = !selectedRating.value || product.rating >= parseFloat(selectedRating.value);

    return matchesPrice && matchesBrand && matchesRam && matchesDiscount && matchesStock && matchesRating;
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
    filters.push({ type: 'ram', label: `ОЗУ: ${selectedRam.value}`, value: selectedRam.value });
  }

  if (priceMin.value > 0 || priceMax.value < 200000) {
    filters.push({
      type: 'price',
      label: `${formatPrice(normalizedPriceRange.value.min)} - ${formatPrice(normalizedPriceRange.value.max)}`
    });
  }

  if (selectedRating.value) {
    filters.push({ type: 'rating', label: `Рейтинг: ${selectedRating.value}+ ★`, value: selectedRating.value });
  }

  if (onlyDiscounts.value) {
    filters.push({ type: 'discounts', label: 'Тільки зі знижкою', value: true });
  }

  if (onlyInStock.value) {
    filters.push({ type: 'stock', label: 'Тільки в наявності', value: true });
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
    priceMax.value = 200000;
  }
  if (filter.type === 'rating') {
    selectedRating.value = '';
  }
  if (filter.type === 'discounts') {
    onlyDiscounts.value = false;
  }
  if (filter.type === 'stock') {
    onlyInStock.value = false;
  }
};

const clearFilters = () => {
  selectedBrands.value = [];
  selectedRam.value = '';
  priceMin.value = 0;
  priceMax.value = 200000;
  selectedRating.value = '';
  onlyDiscounts.value = false;
  onlyInStock.value = false;
};

const openQuickView = (product) => {
  selectedProductForQuickView.value = product;
  isQuickViewOpen.value = true;
};

const closeQuickView = () => {
  selectedProductForQuickView.value = null;
  isQuickViewOpen.value = false;
};

onMounted(() => {
  window.scrollTo(0, 0);
});
</script>

<template>
  <!-- Main Catalog Area -->
  <main class="max-w-container-max mx-auto px-4 md:px-8 py-8 flex gap-8 font-sans select-none text-zinc-800 dark:text-zinc-200">
    
    <!-- Sidebar Filters (Desktop) -->
    <aside class="hidden lg:block w-72 flex-shrink-0">
      <div class="sticky top-24 space-y-6">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500 mb-4 font-bold">
          <a class="hover:text-[#00a046] transition-colors" href="#" @click.prevent="store.currentPage = 'home'">Головна</a>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <a class="hover:text-[#00a046] transition-colors" href="#">Комп'ютери</a>
          <span class="material-symbols-outlined text-[12px]">chevron_right</span>
          <span class="text-zinc-800 dark:text-zinc-100 font-extrabold">Ноутбуки</span>
        </nav>

        <!-- Filters Container -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 shadow-sm divide-y divide-zinc-100 dark:divide-zinc-800">
          
          <!-- Categories Filter Header/Item -->
          <div class="p-5">
            <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-3.5">Категорія</h3>
            <div class="space-y-1">
              <a class="flex items-center justify-between bg-emerald-500/10 text-[#00a046] rounded-lg px-3 py-2 transition-all font-extrabold text-xs" href="#">
                <span class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-[18px]">laptop_mac</span>
                  <span>Ноутбуки</span>
                </span>
                <span class="text-[10px] bg-[#00a046]/10 px-2 py-0.5 rounded-full">{{ products.length }}</span>
              </a>
              <a class="flex items-center justify-between text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-lg px-3 py-2 transition-all text-xs font-semibold" href="#">
                <span class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-[18px]">smartphone</span>
                  <span>Смартфони</span>
                </span>
                <span class="text-[10px]">182</span>
              </a>
              <a class="flex items-center justify-between text-zinc-500 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-lg px-3 py-2 transition-all text-xs font-semibold" href="#">
                <span class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-[18px]">headphones</span>
                  <span>Аудіо & Навушники</span>
                </span>
                <span class="text-[10px]">96</span>
              </a>
            </div>
          </div>

          <!-- Quick Switches (Stock & Promo) -->
          <div class="p-5 space-y-3">
            <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-1">Фільтри швидкого вибору</h3>
            <label class="flex items-center justify-between group cursor-pointer py-0.5">
              <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] transition-colors">Тільки в наявності</span>
              <input v-model="onlyInStock" type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 focus:ring-offset-0 cursor-pointer" />
            </label>
            <label class="flex items-center justify-between group cursor-pointer py-0.5">
              <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] transition-colors">Акційні пропозиції</span>
              <input v-model="onlyDiscounts" type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 focus:ring-offset-0 cursor-pointer" />
            </label>
          </div>

          <!-- Price Range Slider -->
          <div class="p-5">
            <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-4">Максимальна ціна</h3>
            <div class="space-y-4">
              <div class="space-y-1.5 px-1">
                <input v-model.number="priceMax" class="w-full accent-[#00a046]" max="200000" min="0" step="5000" type="range" />
                <div class="flex justify-between text-[10px] text-zinc-400 font-bold uppercase">
                  <span>0 ₴</span>
                  <span>200 000 ₴</span>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <div class="flex-1 relative">
                  <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px] text-zinc-400 font-extrabold">₴</span>
                  <input v-model.number="priceMin" class="w-full h-9 pl-6 pr-2 border border-zinc-200 dark:border-zinc-700 rounded bg-zinc-50 dark:bg-zinc-800 text-xs font-extrabold focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none" min="0" type="number" placeholder="Від" />
                </div>
                <span class="text-zinc-400 text-[10px] font-black">—</span>
                <div class="flex-1 relative">
                  <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-[10px] text-zinc-400 font-extrabold">₴</span>
                  <input v-model.number="priceMax" class="w-full h-9 pl-6 pr-2 border border-zinc-200 dark:border-zinc-700 rounded bg-zinc-50 dark:bg-zinc-800 text-xs font-extrabold focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none" min="0" type="number" placeholder="До" />
                </div>
              </div>
            </div>
          </div>

          <!-- Brand Filter -->
          <div class="p-5">
            <div class="flex items-center justify-between mb-3.5">
              <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Бренд</h3>
              <button class="text-[9px] text-[#00a046] font-black hover:underline uppercase tracking-wider" type="button" @click="selectedBrands = []">Очистити</button>
            </div>
            <div class="space-y-1.5 max-h-48 overflow-y-auto custom-scrollbar pr-1">
              <label v-for="brand in brands" :key="brand.name" class="flex items-center justify-between group cursor-pointer p-1 rounded hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                <div class="flex items-center gap-2.5">
                  <input v-model="selectedBrands" :value="brand.name" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 focus:ring-offset-0 cursor-pointer" type="checkbox" />
                  <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] transition-colors">{{ brand.name }}</span>
                </div>
                <span class="text-[10px] font-bold text-zinc-400">{{ brand.count }}</span>
              </label>
            </div>
          </div>

          <!-- RAM Options -->
          <div class="p-5">
            <div class="flex items-center justify-between mb-3.5">
              <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Об'єм ОЗУ</h3>
              <button class="text-[9px] text-[#00a046] font-black hover:underline uppercase tracking-wider" type="button" @click="selectedRam = ''">Очистити</button>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="ram in ramOptions"
                :key="ram"
                :class="selectedRam === ram ? 'bg-[#00a046] text-white shadow-sm' : 'border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-[#00a046] hover:text-[#00a046] bg-zinc-50/50 dark:bg-zinc-900'"
                class="py-2 rounded-lg text-xs font-extrabold transition-all"
                type="button"
                @click="selectedRam = selectedRam === ram ? '' : ram"
              >
                {{ ram }}
              </button>
            </div>
          </div>

          <!-- Rating filter -->
          <div class="p-5">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500">Оцінка покупців</h3>
              <button class="text-[9px] text-[#00a046] font-black hover:underline uppercase tracking-wider" type="button" @click="selectedRating = ''">Очистити</button>
            </div>
            <div class="space-y-1">
              <label v-for="rate in ['4.8', '4.5', '4.0']" :key="rate" class="flex items-center gap-2 cursor-pointer p-1 rounded hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                <input type="radio" v-model="selectedRating" :value="rate" class="w-3.5 h-3.5 border-zinc-300 text-[#00a046] focus:ring-0 cursor-pointer" />
                <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300 flex items-center gap-1">
                  {{ rate }}+ <span class="material-symbols-outlined text-[14px] text-amber-400" style="font-variation-settings: 'FILL' 1;">star</span>
                </span>
              </label>
            </div>
          </div>

        </div>
      </div>
    </aside>

    <!-- Products Workspace -->
    <section class="flex-1 min-w-0">
      
      <!-- Top Action bar -->
      <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 p-4 mb-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="font-extrabold text-xl md:text-2xl text-zinc-900 dark:text-white tracking-tight">Каталог техніки</h1>
            <p class="text-xs text-zinc-450 dark:text-zinc-500 font-bold mt-1">Знайдено {{ filteredProducts.length }} товарів у категорії "Ноутбуки"</p>
          </div>
          <div class="flex items-center gap-3">
            
            <!-- View Mode toggle switcher -->
            <div class="flex items-center bg-zinc-55 dark:bg-zinc-850 rounded-lg p-0.5 border border-zinc-200 dark:border-zinc-800 mr-1.5">
              <button
                :class="viewMode === 'grid' ? 'bg-white dark:bg-zinc-900 shadow-sm text-[#00a046]' : 'text-zinc-400 dark:text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'"
                class="p-2 rounded-md transition-colors"
                title="Сітка"
                type="button"
                @click="viewMode = 'grid'"
              >
                <span class="material-symbols-outlined text-[18px]">grid_view</span>
              </button>
              <button
                :class="viewMode === 'list' ? 'bg-white dark:bg-zinc-900 shadow-sm text-[#00a046]' : 'text-zinc-400 dark:text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200'"
                class="p-2 rounded-md transition-colors"
                title="Список"
                type="button"
                @click="viewMode = 'list'"
              >
                <span class="material-symbols-outlined text-[18px]">view_list</span>
              </button>
            </div>

            <!-- Sorting Select Dropdown -->
            <div class="relative">
              <select v-model="sortBy" class="appearance-none bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg pl-3 pr-9 py-2.5 text-xs font-extrabold text-zinc-800 dark:text-zinc-200 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] w-48 cursor-pointer outline-none">
                <option value="popularity">За популярністю</option>
                <option value="newest">Новинки</option>
                <option value="price-asc">Ціна: від дешевих</option>
                <option value="price-desc">Ціна: від дорогих</option>
              </select>
              <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-zinc-400 text-[18px]">expand_more</span>
            </div>

            <!-- Mobile filter toggle button -->
            <button @click="isMobileFilterOpen = true" class="lg:hidden flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700">
              <span class="material-symbols-outlined text-[18px]">filter_alt</span>
            </button>

          </div>
        </div>

        <!-- Active Applied Filters badges -->
        <div class="mt-4 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex flex-wrap gap-2 items-center">
          <span class="text-[9px] font-black text-zinc-400 dark:text-zinc-500 uppercase tracking-widest mr-1">Застосовано:</span>
          <template v-if="activeFilters.length">
            <button
              v-for="filter in activeFilters"
              :key="`${filter.type}-${filter.label}`"
              class="flex items-center gap-1 bg-[#00a046]/10 text-[#00a046] px-2.5 py-1 rounded-full text-[10px] hover:bg-[#00a046]/20 transition-all font-black border border-[#00a046]/20"
              type="button"
              @click="removeFilter(filter)"
            >
              {{ filter.label }} <span class="material-symbols-outlined text-[12px]">close</span>
            </button>
            <button class="text-[#00a046] hover:text-[#00b050] font-black text-[10px] ml-auto hover:underline flex items-center gap-1 uppercase tracking-wider" type="button" @click="clearFilters">
              <span class="material-symbols-outlined text-[14px]">filter_list_off</span>
              Скинути все
            </button>
          </template>
          <span v-else class="text-[10px] text-zinc-400 dark:text-zinc-500 font-extrabold italic">Фільтри не вибрано</span>
        </div>
      </div>

      <!-- Grid / List Products Display -->
      <div
        v-if="filteredProducts.length"
        :class="viewMode === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6' : 'flex flex-col gap-4'"
      >
        <article
          v-for="product in filteredProducts"
          :key="product.id"
          :class="viewMode === 'grid'
            ? 'flex-col rounded-xl hover:shadow-xl hover:border-emerald-500/30'
            : 'flex-col sm:flex-row rounded-xl hover:shadow-xl hover:border-emerald-500/30'"
          class="group flex bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 transition-all duration-300 overflow-hidden relative"
        >
          
          <!-- Image Section -->
          <div :class="viewMode === 'grid' ? 'w-full p-4 border-b border-zinc-100 dark:border-zinc-800' : 'w-full sm:w-60 p-4 border-r border-zinc-100 dark:border-zinc-800'" class="relative bg-white dark:bg-white/95 flex justify-center items-center aspect-[1.15/1]">
            <a class="w-full h-full overflow-hidden relative cursor-pointer block" @click.prevent="store.viewProduct(product)">
              <img :alt="product.name" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500 p-2" :src="product.image" />
              <span v-if="product.badge" :class="product.badgeClass" class="absolute top-2 left-2 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest shadow-sm">{{ product.badge }}</span>
            </a>
            
            <!-- Quick View Floating Button on Hover (Grid) -->
            <button
              @click="openQuickView(product)"
              class="absolute bottom-4 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all bg-zinc-950/80 hover:bg-zinc-950 text-white font-extrabold text-[10px] px-3.5 py-2 rounded-lg flex items-center gap-1.5 shadow-md backdrop-blur-sm"
            >
              <span class="material-symbols-outlined text-[14px]">visibility</span> Швидкий перегляд
            </button>

            <!-- Wishlist button (oblong pill) -->
            <button
              class="absolute top-4 right-4 px-2.5 py-1.5 bg-white/90 dark:bg-zinc-800/90 hover:bg-rose-500/10 hover:text-rose-550 text-zinc-550 dark:text-zinc-300 rounded-lg transition-all shadow-sm flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider backdrop-blur-sm"
              type="button"
              @click="store.toggleWishlist(product)"
            >
              <span
                :class="{ 'text-rose-550': store.isInWishlist(product.id) }"
                :style="store.isInWishlist(product.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
                class="material-symbols-outlined text-[14px]"
              >
                favorite
              </span>
              <span>{{ store.isInWishlist(product.id) ? 'Обрано' : 'В обране' }}</span>
            </button>
          </div>

          <!-- Product Details Body -->
          <div :class="viewMode === 'grid' ? 'p-5 flex-col flex-1' : 'p-6 flex-1 flex flex-col justify-between'" class="flex flex-col justify-between">
            <div class="space-y-3">
              
              <!-- Stars & brand -->
              <div class="flex items-center justify-between gap-2">
                <span class="text-[9px] font-black text-[#00a046] uppercase bg-emerald-500/10 px-2 py-0.5 rounded">{{ product.brand }}</span>
                <div class="flex items-center gap-1">
                  <div class="flex text-amber-400">
                    <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[14px]" :style="star <= Math.floor(product.rating) ? 'font-variation-settings: \'FILL\' 1;' : ''">
                      {{ star <= Math.floor(product.rating) ? 'star' : 'star_half' }}
                    </span>
                  </div>
                  <span class="text-[10px] font-black text-zinc-450 dark:text-zinc-500">({{ product.reviews }})</span>
                </div>
              </div>

              <!-- Product Name -->
              <a class="block text-left cursor-pointer" @click.prevent="store.viewProduct(product)">
                <h2 :class="viewMode === 'grid' ? 'text-sm md:text-base line-clamp-2 min-h-[44px]' : 'text-base md:text-lg'" class="font-extrabold text-zinc-900 dark:text-white group-hover:text-[#00a046] transition-colors leading-snug">{{ product.name }}</h2>
              </a>

              <!-- Specs Pills (Grid / List spec display) -->
              <div class="flex flex-wrap gap-1.5 mt-2">
                <span class="inline-flex items-center gap-1 rounded bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 text-[9px] font-extrabold text-zinc-550 dark:text-zinc-400">
                  <span class="material-symbols-outlined text-[12px]">memory</span>
                  {{ product.ram }} RAM
                </span>
                <span class="inline-flex items-center gap-1 rounded bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 text-[9px] font-extrabold text-zinc-550 dark:text-zinc-400">
                  <span class="material-symbols-outlined text-[12px]">laptop</span>
                  {{ product.specs.screen.split(' ')[0] }}
                </span>
                <span class="inline-flex items-center gap-1 rounded bg-zinc-50 dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 px-2 py-0.5 text-[9px] font-extrabold text-zinc-550 dark:text-zinc-400">
                  <span class="material-symbols-outlined text-[12px]">storage</span>
                  {{ product.specs.storage.split(' ')[0] }}
                </span>
                <span v-if="product.inStock" class="inline-flex items-center gap-1 rounded bg-emerald-500/5 border border-emerald-500/10 px-2 py-0.5 text-[9px] font-extrabold text-[#00a046]">
                  В наявності
                </span>
                <span v-else class="inline-flex items-center gap-1 rounded bg-zinc-500/5 border border-zinc-500/10 px-2 py-0.5 text-[9px] font-extrabold text-zinc-450 dark:text-zinc-500">
                  Немає в наявності
                </span>
              </div>

              <!-- List short description -->
              <p v-if="viewMode === 'list'" class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed mt-2.5">{{ product.description }}</p>

            </div>

            <!-- Price and Cart Buttons -->
            <div :class="viewMode === 'grid' ? 'pt-4 mt-4 border-t border-zinc-100 dark:border-zinc-800' : 'mt-5 pt-4 border-t border-zinc-100 dark:border-zinc-800 flex sm:flex-row flex-col justify-between items-stretch sm:items-center gap-4'" class="space-y-3">
              <div class="flex items-center justify-between gap-4">
                <div class="flex flex-col">
                  <span v-if="product.oldPrice" class="text-[10px] text-zinc-400 line-through font-extrabold">{{ formatPrice(product.oldPrice) }}</span>
                  <span class="text-lg md:text-xl font-black text-[#00a046] tracking-tight">{{ formatPrice(product.price) }}</span>
                </div>
                
                <!-- Compare Button -->
                <button
                  type="button"
                  @click="store.toggleCompare(product)"
                  :class="store.isInCompare(product.id)
                    ? 'bg-emerald-500/10 text-[#00a046] border-[#00a046]/30'
                    : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-550 dark:text-zinc-400 border-zinc-250 dark:border-zinc-700 hover:text-[#00a046] hover:border-[#00a046]/40'"
                  class="flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-wider transition-all"
                >
                  <span class="material-symbols-outlined text-[14px]">compare_arrows</span>
                  <span>{{ store.isInCompare(product.id) ? 'У порівнянні' : 'Порівняти' }}</span>
                </button>
              </div>

              <div :class="viewMode === 'grid' ? 'w-full' : 'w-full sm:w-auto'">
                <button
                  :disabled="!product.inStock"
                  @click="store.addToCart(product)"
                  :class="product.inStock 
                    ? 'bg-[#00a046] hover:bg-[#00b050] text-white active:scale-[0.98]' 
                    : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed'"
                  class="w-full bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs py-2.5 px-6 rounded-lg transition-all flex items-center justify-center gap-2 shadow-sm uppercase tracking-wider"
                  type="button"
                >
                  <span class="material-symbols-outlined text-[16px] md:text-[18px]">shopping_cart</span>
                  {{ product.inStock ? 'Купити' : 'Немає в наявності' }}
                </button>
              </div>
            </div>

          </div>
        </article>
      </div>

      <!-- No products found placeholder -->
      <div v-else class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800 p-12 text-center shadow-sm">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center text-zinc-400">
          <span class="material-symbols-outlined text-[36px]">search_off</span>
        </div>
        <h2 class="font-extrabold text-zinc-900 dark:text-white mb-2">Товари за вашим запитом не знайдені</h2>
        <p class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 mb-6 max-w-md mx-auto">Спробуйте змінити значення цінового діапазону або приберіть зайві бренди чи параметри ОЗУ.</p>
        <button class="bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs py-2.5 px-6 rounded-lg transition-all" type="button" @click="clearFilters">
          Скинути фільтри
        </button>
      </div>

      <!-- Pagination Block -->
      <nav class="mt-12 flex items-center justify-between border-t border-zinc-100 dark:border-zinc-800 pt-6">
        <button class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-extrabold text-zinc-500 hover:text-[#00a046] hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-lg transition-all">
          <span class="material-symbols-outlined text-[16px]">arrow_back</span>
          НАЗАД
        </button>
        <div class="flex items-center gap-1">
          <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-[#00a046] text-white font-extrabold text-xs shadow-sm">1</button>
          <button class="w-8 h-8 flex items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-zinc-800 dark:hover:text-zinc-200 transition-all font-extrabold text-xs">2</button>
          <button class="w-8 h-8 flex items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-zinc-800 dark:hover:text-zinc-200 transition-all font-extrabold text-xs">3</button>
          <span class="px-2 text-zinc-400 font-extrabold text-xs select-none">...</span>
          <button class="w-8 h-8 flex items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-zinc-800 dark:hover:text-zinc-200 transition-all font-extrabold text-xs">12</button>
        </div>
        <button class="flex items-center gap-1.5 px-3.5 py-2 text-xs font-extrabold text-zinc-500 hover:text-[#00a046] hover:bg-zinc-50 dark:hover:bg-zinc-800 rounded-lg transition-all">
          ВПЕРЕД
          <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
        </button>
      </nav>
    </section>
  </main>

  <!-- Mobile Filters Bottom Sheet / Side Drawer Overlay -->
  <div v-if="isMobileFilterOpen" class="fixed inset-0 z-50 flex lg:hidden bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-white dark:bg-zinc-900 w-80 max-w-[85vw] h-full shadow-2xl p-6 flex flex-col justify-between overflow-y-auto">
      <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-zinc-150 dark:border-zinc-800 pb-3">
          <h2 class="font-extrabold text-base text-zinc-900 dark:text-white">Фільтри товарів</h2>
          <button @click="isMobileFilterOpen = false" class="text-zinc-400 hover:text-zinc-650"><span class="material-symbols-outlined">close</span></button>
        </div>
        
        <!-- Category (Mobile) -->
        <div>
          <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-2">Категорія</h3>
          <select class="w-full bg-zinc-50 dark:bg-zinc-800 border border-zinc-250 dark:border-zinc-700 rounded-lg px-3 py-2 text-xs font-extrabold text-zinc-800 dark:text-zinc-200 outline-none">
            <option>Ноутбуки ({{ products.length }})</option>
            <option>Смартфони (182)</option>
            <option>Аудіо & Навушники (96)</option>
          </select>
        </div>

        <!-- Switches (Mobile) -->
        <div class="space-y-3">
          <label class="flex items-center justify-between cursor-pointer">
            <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300">Тільки в наявності</span>
            <input v-model="onlyInStock" type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 cursor-pointer" />
          </label>
          <label class="flex items-center justify-between cursor-pointer">
            <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300">Акційні пропозиції</span>
            <input v-model="onlyDiscounts" type="checkbox" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 cursor-pointer" />
          </label>
        </div>

        <!-- Price Range (Mobile) -->
        <div>
          <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-3">Діапазон цін</h3>
          <div class="flex items-center gap-2">
            <input v-model.number="priceMin" type="number" class="w-full h-9 px-3 border border-zinc-200 dark:border-zinc-700 rounded bg-zinc-50 dark:bg-zinc-800 text-xs font-extrabold text-zinc-800 dark:text-zinc-200 outline-none" placeholder="Від" />
            <span class="text-zinc-400 font-extrabold">-</span>
            <input v-model.number="priceMax" type="number" class="w-full h-9 px-3 border border-zinc-200 dark:border-zinc-700 rounded bg-zinc-50 dark:bg-zinc-800 text-xs font-extrabold text-zinc-800 dark:text-zinc-200 outline-none" placeholder="До" />
          </div>
        </div>

        <!-- Brand (Mobile) -->
        <div>
          <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-2">Бренди</h3>
          <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1">
            <label v-for="brand in brands" :key="brand.name" class="flex items-center justify-between cursor-pointer py-1">
              <div class="flex items-center gap-2">
                <input v-model="selectedBrands" :value="brand.name" class="w-4 h-4 rounded border-zinc-300 text-[#00a046] focus:ring-0 cursor-pointer" type="checkbox" />
                <span class="text-xs font-extrabold text-zinc-700 dark:text-zinc-300">{{ brand.name }}</span>
              </div>
              <span class="text-[10px] font-bold text-zinc-400">{{ brand.count }}</span>
            </label>
          </div>
        </div>

        <!-- RAM (Mobile) -->
        <div>
          <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-2">Об'єм ОЗУ</h3>
          <div class="grid grid-cols-2 gap-1.5">
            <button
              v-for="ram in ramOptions"
              :key="ram"
              :class="selectedRam === ram ? 'bg-[#00a046] text-white' : 'border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 bg-zinc-50/50 dark:bg-zinc-800'"
              class="py-1.5 rounded-lg text-xs font-extrabold"
              type="button"
              @click="selectedRam = selectedRam === ram ? '' : ram"
            >
              {{ ram }}
            </button>
          </div>
        </div>

        <!-- Rating (Mobile) -->
        <div>
          <h3 class="font-extrabold text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500 mb-2">Рейтинг покупців</h3>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="rate in ['4.8', '4.5', '4.0']"
              :key="rate"
              @click="selectedRating = selectedRating === rate ? '' : rate"
              :class="selectedRating === rate ? 'bg-[#00a046] text-white' : 'border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 bg-zinc-55 dark:bg-zinc-800'"
              class="px-3 py-1.5 rounded-lg text-xs font-extrabold flex items-center gap-1"
            >
              {{ rate }}+ <span class="material-symbols-outlined text-[12px] text-amber-400" style="font-variation-settings: 'FILL' 1;">star</span>
            </button>
          </div>
        </div>

      </div>
      <div class="border-t border-zinc-150 dark:border-zinc-800 pt-4 mt-6 flex gap-3">
        <button @click="clearFilters" class="flex-1 border border-zinc-250 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 font-extrabold py-2.5 rounded-lg text-xs">Скинути</button>
        <button @click="isMobileFilterOpen = false" class="flex-1 bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold py-2.5 rounded-lg text-xs">Показати</button>
      </div>
    </div>
  </div>

  <!-- Quick View Modal (For detailed product stats overview) -->
  <div v-if="isQuickViewOpen && selectedProductForQuickView" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl max-w-3xl w-full shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
      <div class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 px-6 py-4.5 flex justify-between items-center">
        <div>
          <span class="text-[9px] font-black text-[#00a046] uppercase bg-emerald-500/10 px-2.5 py-0.5 rounded tracking-wider">Швидкий перегляд</span>
          <h3 class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white mt-1">Детальні характеристики</h3>
        </div>
        <button @click="closeQuickView" class="text-zinc-400 hover:text-zinc-650 dark:hover:text-zinc-250"><span class="material-symbols-outlined">close</span></button>
      </div>
      
      <div class="p-6 overflow-y-auto space-y-6 text-xs md:text-sm grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
        
        <!-- Left: Product Image block -->
        <div class="flex flex-col gap-4 items-center">
          <div class="bg-white rounded-lg border border-zinc-100 dark:border-zinc-800 p-6 flex items-center justify-center w-full aspect-square relative select-none">
            <img :src="selectedProductForQuickView.image" :alt="selectedProductForQuickView.name" class="max-h-[220px] object-contain" />
            <span v-if="selectedProductForQuickView.badge" :class="selectedProductForQuickView.badgeClass" class="absolute top-3 left-3 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-widest shadow-sm">{{ selectedProductForQuickView.badge }}</span>
          </div>
          <div class="flex items-center gap-1.5 text-zinc-400">
            <span class="material-symbols-outlined text-[16px]">info</span>
            <span class="text-[10px] font-extrabold uppercase tracking-wide">Зображення слугує для демонстрації</span>
          </div>
        </div>

        <!-- Right: Specs list -->
        <div class="space-y-5">
          <div>
            <h4 class="font-black text-zinc-900 dark:text-white text-base leading-snug">{{ selectedProductForQuickView.name }}</h4>
            <div class="flex items-center gap-3 mt-2.5">
              <span class="text-xs font-black text-[#00a046] bg-emerald-500/5 border border-emerald-500/10 px-2 py-0.5 rounded">{{ selectedProductForQuickView.brand }}</span>
              <div class="flex items-center gap-1 text-amber-400">
                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="font-extrabold text-zinc-800 dark:text-zinc-200 text-xs mt-0.5">{{ selectedProductForQuickView.rating }} ({{ selectedProductForQuickView.reviews }} відгуків)</span>
              </div>
            </div>
          </div>

          <!-- Price Block -->
          <div class="bg-zinc-50 dark:bg-zinc-850 p-4.5 rounded-lg border border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
            <div class="flex flex-col">
              <span v-if="selectedProductForQuickView.oldPrice" class="text-xs text-zinc-400 line-through font-extrabold">{{ formatPrice(selectedProductForQuickView.oldPrice) }}</span>
              <span class="text-xl font-black text-[#00a046]">{{ formatPrice(selectedProductForQuickView.price) }}</span>
            </div>
            <span v-if="selectedProductForQuickView.inStock" class="text-xs text-[#00a046] font-extrabold bg-[#00a046]/10 border border-[#00a046]/20 px-3 py-1 rounded-lg">В наявності</span>
            <span v-else class="text-xs text-zinc-500 font-extrabold bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 px-3 py-1 rounded-lg">Немає в наявності</span>
          </div>

          <!-- Description -->
          <div class="space-y-1.5">
            <span class="text-[10px] font-extrabold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Короткий опис</span>
            <p class="text-zinc-650 dark:text-zinc-350 text-xs leading-relaxed">{{ selectedProductForQuickView.description }}</p>
          </div>

          <!-- Tech Specs Table -->
          <div class="space-y-2">
            <span class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">Основні параметри</span>
            <div class="border border-zinc-100 dark:border-zinc-800 rounded-lg overflow-hidden divide-y divide-zinc-100 dark:divide-zinc-800 text-xs">
              <div class="flex p-2.5 bg-zinc-50/50 dark:bg-zinc-850/50"><span class="w-1/3 text-zinc-400 font-bold">Процесор</span><span class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold">{{ selectedProductForQuickView.specs.processor }}</span></div>
              <div class="flex p-2.5"><span class="w-1/3 text-zinc-400 font-bold">Екран</span><span class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold">{{ selectedProductForQuickView.specs.screen }}</span></div>
              <div class="flex p-2.5 bg-zinc-50/50 dark:bg-zinc-850/50"><span class="w-1/3 text-zinc-400 font-bold">Пам'ять</span><span class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold">{{ selectedProductForQuickView.ram }} RAM / {{ selectedProductForQuickView.specs.storage }}</span></div>
              <div class="flex p-2.5"><span class="w-1/3 text-zinc-400 font-bold">Операційна система</span><span class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold">{{ selectedProductForQuickView.specs.os }}</span></div>
              <div class="flex p-2.5 bg-zinc-50/50 dark:bg-zinc-850/50"><span class="w-1/3 text-zinc-400 font-bold">Вага</span><span class="w-2/3 text-zinc-800 dark:text-zinc-200 font-extrabold">{{ selectedProductForQuickView.specs.weight }}</span></div>
            </div>
          </div>

        </div>

      </div>

      <!-- Footer Buttons -->
      <div class="bg-zinc-50 dark:bg-zinc-850 border-t border-zinc-100 dark:border-zinc-800 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3">
        <button @click="store.toggleCompare(selectedProductForQuickView)" class="border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 px-5 py-2.5 rounded-lg font-extrabold text-xs md:text-sm transition-colors flex items-center justify-center gap-1.5">
          <span class="material-symbols-outlined text-[16px] md:text-[18px]">compare_arrows</span> {{ store.isInCompare(selectedProductForQuickView.id) ? 'У порівнянні' : 'Порівняти' }}
        </button>
        <button
          @click="store.addToCart(selectedProductForQuickView); closeQuickView();"
          :disabled="!selectedProductForQuickView.inStock"
          :class="selectedProductForQuickView.inStock ? 'bg-[#00a046] hover:bg-[#00b050] text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 cursor-not-allowed'"
          class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-lg font-extrabold text-xs md:text-sm transition-all flex items-center justify-center gap-2 shadow-sm uppercase tracking-wider"
        >
          <span class="material-symbols-outlined text-[16px] md:text-[18px]">shopping_cart</span> Додати в кошик
        </button>
      </div>
    </div>
  </div>

</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.22s ease-out forwards;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(3px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #d4d4d8;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #a1a1aa;
}

input[type='range'] {
  -webkit-appearance: none;
  width: 100%;
  background: transparent;
}
input[type='range']::-webkit-slider-runnable-track {
  width: 100%;
  height: 2.5px;
  cursor: pointer;
  background: #e4e4e7;
  border-radius: 1px;
}
.dark input[type='range']::-webkit-slider-runnable-track {
  background: #27272a;
}
input[type='range']::-webkit-slider-thumb {
  height: 14px;
  width: 14px;
  border-radius: 50%;
  background: #00a046;
  cursor: pointer;
  -webkit-appearance: none;
  margin-top: -6px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  border: 1.5px solid #ffffff;
  transition: transform 0.1s ease;
}
input[type='range']::-webkit-slider-thumb:hover {
  transform: scale(1.15);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
