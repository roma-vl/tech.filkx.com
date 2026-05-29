<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { store } from '@/store.js';
import api from '@/services/api.js';

const route = useRoute();
const router = useRouter();

const isLoading = ref(false);
const rawProduct = ref(null);

const product = computed(() => {
  if (rawProduct.value) {
    const mainVariant = rawProduct.value.variants && rawProduct.value.variants[0] ? rawProduct.value.variants[0] : null;
    const price = mainVariant ? parseFloat(mainVariant.price) : 0;
    const oldPrice = mainVariant && mainVariant.oldPrice ? parseFloat(mainVariant.oldPrice) : null;
    const totalStock = mainVariant ? (mainVariant.stocks || []).reduce((acc, s) => acc + (parseInt(s.quantity) - parseInt(s.reserved)), 0) : 0;

    let image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZjrYzoYVLWW_oiXKtFfrvXfrqZhFl0aOo-qiqP-OxioJPU85soCgr1bPX8-8SrIpEgyr7zYqcamNRaM1BW5yOnQdyQkcNC89uNihkW1bThAYw05lRVqC36IMTBCvBLVH7opxwC_Q3tAwXBXFTV3E_7Pec49dMJ6oEmwa-i1h3rfPR3C3ZxfrlPDm4iN8h3YEy4Smhr2pI6IcA1YpRV8_hq162IYmxl8-kkt1WI_Z9ARaUKWft3ncDr_m6Dug4Fa0Nm0Rr2ngLp0Q';
    if (rawProduct.value.slug === 'iphone-15-pro-max') {
      image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZjrYzoYVLWW_oiXKtFfrvXfrqZhFl0aOo-qiqP-OxioJPU85soCgr1bPX8-8SrIpEgyr7zYqcamNRaM1BW5yOnQdyQkcNC89uNihkW1bThAYw05lRVqC36IMTBCvBLVH7opxwC_Q3tAwXBXFTV3E_7Pec49dMJ6oEmwa-i1h3rfPR3C3ZxfrlPDm4iN8h3YEy4Smhr2pI6IcA1YpRV8_hq162IYmxl8-kkt1WI_Z9ARaUKWft3ncDr_m6Dug4Fa0Nm0Rr2ngLp0Q';
    } else if (rawProduct.value.slug === 'samsung-galaxy-s24-ultra') {
      image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuDNXpOdOi1q9K16_agnjDdmva4mM8QDf9TI4MCTsRa0_OXpmRLAkd2BmZ0IpQebeCf9T-oqp5EXZIEqu5AgJgO3UAZfh8JwEUwazBkmMcqSqi5NOJjpKjWbdNN6PVkBt40FEXcJMc2b-kYP2x4afcnwiPcUckUaDsOZfW3QlxwFPMxfrXvfI7xR-8qcpi8AlkYYBVIucffemoFhQigVY-yrdYAUIMrcC6HgcPyO99EpuBM4WdjdU2LJpA6MY3BhgG7BudOrk4ZPlNw';
    } else if (rawProduct.value.slug === 'lenovo-legion-5-pro') {
      image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuDr331B7FabLZcRGhJ_DbZowzkaew5s_GJfms-DS1LXHrCr9JrEM_qiTSvHHdcRLOQU4NygZqdg2vzSEP8qolpkbrEuPi83FukM8x4ZzJpflfXCL5i6WZw99Ro2W_kJSyPwSKmBh7aTJ89xk_sSMwhQZu0di9CfY_tYG8xsS9crK6wdrdWzCio8Ct_P6vzzIdKMqZSvWk-cI5tR8P_uuTugKKtObu44X83uzkFVwQ768UhPlN4P_9soMg2YidbSr7gU_mGJdorHV3E';
    } else if (rawProduct.value.slug === 'sony-wh-1000xm5-black') {
      image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuApPyQSbFm8gPmD-BUjU4KbU8lxRaJgxXIhErhaMatT2s9qIW-w_5-JYkv6KP4VCydvIJ7AILq7vAzgYxtBMWpH3kCLV-dTj-MLQXnn5QZ-wzUyExGQ4ctA0UF9iDDXWD5M5J4yjWdsZwVHkLS41IEyjl_3hgh0UOOKNAFACOcwflvlJmUTb4_shPWuLH9O39dD2jY3poIQW6bgNMNDkH27ULegCxzfRn5mcStW0AeWRcTRtB-FbFVceirC1rt5mfGkfUq5SmcUkmA';
    } else if (rawProduct.value.slug === 'apple-airpods-pro-2') {
      image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuA4CBkZB03qlIoMec3YDV24fO35X8SQ-nFR3-vSL9fHTRB_0yNWXQIPyPUR9XTJAgwPRqR9BMPLYRdA1wE5DJ45Whogygd0z1RLbsHf57iD3oNin76Iky7ChCqZYi5i_wfvTapwlF_E-PSDIHYoRQK6uRBPNNTQz4EHty0UuXvWXNNbKzjznstWRzJVKUzyYdU8ZPafSIhxOBNZZog6jxjU4a9KAaF5H8EcaCT0lQ1XAMin35srr2hS4Wizm7MABaeuhA9WVqY02lQ';
    }

    const name = typeof rawProduct.value.name === 'object' ? (rawProduct.value.name.uk || rawProduct.value.name.en) : rawProduct.value.name;
    const description = typeof rawProduct.value.description === 'object' ? (rawProduct.value.description.uk || rawProduct.value.description.en) : rawProduct.value.description;

    return {
      id: rawProduct.value.id,
      slug: rawProduct.value.slug,
      name: name,
      category: rawProduct.value.categories && rawProduct.value.categories[0] ? (rawProduct.value.categories[0].name.uk || rawProduct.value.categories[0].name.en) : 'Laptops',
      subtitle: mainVariant && mainVariant.dimensions && mainVariant.dimensions.ram ? `${mainVariant.dimensions.ram} ОЗУ / ${mainVariant.dimensions.storage}` : 'Premium Tech Edition',
      price: price,
      oldPrice: oldPrice || price * 1.15,
      image: image,
      rating: rawProduct.value.rating || 4.8,
      reviews: rawProduct.value.reviews || 84,
      description: description,
      specs: {
        processor: mainVariant && mainVariant.dimensions && mainVariant.dimensions.processor ? mainVariant.dimensions.processor : 'Apple Silicon / Intel Core',
        screen: mainVariant && mainVariant.dimensions && mainVariant.dimensions.screen ? mainVariant.dimensions.screen : '14" IPS',
        storage: mainVariant && mainVariant.dimensions && mainVariant.dimensions.storage ? mainVariant.dimensions.storage : '512GB SSD',
        os: mainVariant && mainVariant.dimensions && mainVariant.dimensions.os ? mainVariant.dimensions.os : 'Windows 11 / macOS',
        weight: mainVariant && mainVariant.weight ? `${mainVariant.weight} кг` : '1.5 кг'
      }
    };
  }
  return null;
});

const galleryImages = computed(() => {
  const mainImg = product.value?.image || 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZjrYzoYVLWW_oiXKtFfrvXfrqZhFl0aOo-qiqP-OxioJPU85soCgr1bPX8-8SrIpEgyr7zYqcamNRaM1BW5yOnQdyQkcNC89uNihkW1bThAYw05lRVqC36IMTBCvBLVH7opxwC_Q3tAwXBXFTV3E_7Pec49dMJ6oEmwa-i1h3rfPR3C3ZxfrlPDm4iN8h3YEy4Smhr2pI6IcA1YpRV8_hq162IYmxl8-kkt1WI_Z9ARaUKWft3ncDr_m6Dug4Fa0Nm0Rr2ngLp0Q';
  return [
    { label: 'Основний вигляд', src: mainImg },
    { label: 'Вигляд збоку', src: src => mainImg },
    { label: 'Детальний макро-вигляд', src: mainImg }
  ];
});

const selectedImageIndex = ref(0);
const selectedColor = ref('Space Black');
const selectedStorage = ref('512GB');
const activeTab = ref('experience');
const showStickyBar = ref(false);
const selectedBundleIds = ref(['pods', 'charger']);

// Magnifying hover zoom state variables
const isZoomed = ref(false);
const zoomStyle = ref({
  transform: 'scale(1)',
  transformOrigin: 'center center'
});

const handleMouseMove = (e) => {
  const container = e.currentTarget;
  const rect = container.getBoundingClientRect();
  const x = ((e.clientX - rect.left) / rect.width) * 100;
  const y = ((e.clientY - rect.top) / rect.height) * 100;
  isZoomed.value = true;
  zoomStyle.value = {
    transform: 'scale(1.8)',
    transformOrigin: `${x}% ${y}%`
  };
};

const handleMouseLeave = () => {
  isZoomed.value = false;
  zoomStyle.value = {
    transform: 'scale(1)',
    transformOrigin: 'center center'
  };
};

const selectedImage = computed(() => galleryImages.value[selectedImageIndex.value]?.src || '');

const tabs = [
  { id: 'experience', label: 'Огляд продукту' },
  { id: 'specs', label: 'Характеристики' },
  { id: 'reviews', label: 'Відгуки' },
  { id: 'support', label: 'Підтримка' }
];

const productSpecs = computed(() => {
  if (!product.value || !product.value.specs) return [];
  const entries = Object.entries(product.value.specs);
  const keyTranslation = {
    processor: 'Процесор',
    screen: 'Екран',
    storage: 'Накопичувач',
    os: 'Операційна система',
    weight: 'Вага'
  };
  return entries.map(([key, val]) => [keyTranslation[key] || key, val]);
});

const bundleItems = computed(() => {
  if (!product.value) return [];
  return [
    {
      id: 'device',
      name: product.value.name,
      category: 'Основний пристрій',
      price: product.value.price,
      locked: true,
      image: galleryImages.value[0]?.src || ''
    },
    {
      id: 'pods',
      name: 'Elite Sound Pods',
      category: 'Бездротові навушники',
      price: 6990,
      image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuA4CBkZB03qlIoMec3YDV24fO35X8SQ-nFR3-vSL9fHTRB_0yNWXQIPyPUR9XTJAgwPRqR9BMPLYRdA1wE5DJ45Whogygd0z1RLbsHf57iD3oNin76Iky7ChCqZYi5i_wfvTapwlF_E-PSDIHYoRQK6uRBPNNTQz4EHty0UuXvWXNNbKzjznstWRzJVKUzyYdU8ZPafSIhxOBNZZog6jxjU4a9KAaF5H8EcaCT0lQ1XAMin35srr2hS4Wizm7MABaeuhA9WVqY02lQ'
    },
    {
      id: 'charger',
      name: 'Pro Charge 100W GaN',
      category: 'Швидкий зарядний пристрій',
      price: 2990,
      image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDHpw39gwGprYkW4qIbZzy2rLTbcLvDxWukT6TsUoCUIIA_MP68QWCuopoQuE9aG5HGs8xzGWJvtts8ShB73VX0DQ3EYiBd5Muljqm64CLPv2t0Sih0HEiaGcZ9xQ2yqdEPy-u7wBaEYQHeCCQqfWYxSM_XjbxKllnWehPZ9qyUDYglqNb73NhCqinyVtGSEOFch5lKqYYp6TABRJkKScR9scZP6lbXxLVOft7ZvFplc69p0s6hEhsBh7bPRgQPf3E-KLZlLIvte_g'
    }
  ];
});

const qualityGuarantees = [
  { icon: 'award_star', title: '2 роки офіційної гарантії', text: 'Повне сервісне обслуговування.' },
  { icon: 'published_with_changes', title: '30 днів для обміну/повернення', text: 'Легкий обмін без зайвих запитань.' },
  { icon: 'security', title: 'Захищені платежі SSL', text: 'Сертифікована безпека всіх транзакцій.' }
];

const reviews = [
  {
    name: 'Марта В.',
    title: 'Чудовий вибір для роботи та графіки',
    text: 'Неймовірний екран, кольори соковиті, очі не втомлюються. Продуктивності з запасом, а матеріали корпусу відчуваються дорого.'
  },
  {
    name: 'Данило К.',
    title: 'Преміальний рівень пристрою',
    text: 'Швидкість роботи SSD вражає. Дуже задоволений часом автономної роботи — витримує цілий день інтенсивного програмування.'
  }
];

const formatPrice = (price) => {
  return new Intl.NumberFormat('uk-UA', {
    style: 'currency',
    currency: 'UAH',
    maximumFractionDigits: 0
  }).format(price);
};

const bundleSubtotal = computed(() => {
  return bundleItems.value
    .filter((item) => item.locked || selectedBundleIds.value.includes(item.id))
    .reduce((sum, item) => sum + item.price, 0);
});

const bundleSavings = computed(() => selectedBundleIds.value.length ? 3000 : 0);
const bundleTotal = computed(() => Math.max(0, bundleSubtotal.value - bundleSavings.value));

const setSelectedImage = (index) => {
  selectedImageIndex.value = index;
};

const selectNextImage = () => {
  selectedImageIndex.value = (selectedImageIndex.value + 1) % galleryImages.value.length;
};

const selectPreviousImage = () => {
  selectedImageIndex.value = (selectedImageIndex.value - 1 + galleryImages.value.length) % galleryImages.value.length;
};

const toggleBundleItem = (item) => {
  if (item.locked) return;

  if (selectedBundleIds.value.includes(item.id)) {
    selectedBundleIds.value = selectedBundleIds.value.filter((id) => id !== item.id);
  } else {
    selectedBundleIds.value = [...selectedBundleIds.value, item.id];
  }
};

const addBundleToCart = () => {
  if (!product.value) return;
  store.addToCart(product.value);
  selectedBundleIds.value.forEach((id) => {
    const item = bundleItems.value.find((bundleItem) => bundleItem.id === id);
    if (item && !item.locked) {
      store.addToCart({
        id: item.id,
        name: item.name,
        price: item.price,
        image: item.image,
        category: 'Accessories'
      });
    }
  });
};

const fetchProductDetails = async () => {
  isLoading.value = true;
  try {
    const slug = route.params.id;
    const response = await api.get(`/v1/catalog/products/${slug}`);
    if (response.data && response.data.status === 'success') {
      rawProduct.value = response.data.data;
      if (rawProduct.value && rawProduct.value.id) {
        store.trackProductView(rawProduct.value.id);
      }
    }
  } catch (error) {
    console.error('Failed to fetch product details:', error);
  } finally {
    isLoading.value = false;
  }
};

const handleScroll = () => {
  showStickyBar.value = window.scrollY > 420;
};

onMounted(() => {
  window.scrollTo(0, 0);
  fetchProductDetails();
  handleScroll();
  window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <div v-if="isLoading" class="min-h-screen flex items-center justify-center bg-white dark:bg-zinc-950">
    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-emerald-500"></div>
  </div>
  <div v-else-if="!product" class="min-h-screen flex flex-col items-center justify-center text-center bg-white dark:bg-zinc-950 px-4">
    <span class="material-symbols-outlined text-[64px] text-zinc-400 mb-4">search_off</span>
    <h1 class="text-xl font-extrabold mb-2 text-zinc-800 dark:text-zinc-200">Товар не знайдено</h1>
    <p class="text-zinc-500 mb-6">Будь ласка, перевірте правильність посилання або скористайтесь каталогом.</p>
    <button @click="router.push('/catalog')" class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-lg text-sm font-extrabold transition-all">
      Перейти до каталогу
    </button>
  </div>
  <div v-else>
    <main class="max-w-container-max mx-auto px-4 md:px-8 py-8 text-zinc-800 dark:text-zinc-200 font-sans">
      
      <!-- Breadcrumbs -->
      <nav class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-550 mb-8 font-bold">
        <a class="hover:text-[#00a046] transition-colors" href="#" @click.prevent="router.push('/')">Головна</a>
        <span class="material-symbols-outlined text-[12px]">chevron_right</span>
        <a class="hover:text-[#00a046] transition-colors" href="#" @click.prevent="router.push('/catalog')">{{ product.category }}</a>
        <span class="material-symbols-outlined text-[12px]">chevron_right</span>
        <span class="text-zinc-800 dark:text-zinc-100 font-extrabold">{{ product.name }}</span>
      </nav>

    <!-- Hero block -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      
      <!-- Gallery Column -->
      <div class="lg:col-span-7 flex flex-col md:flex-row gap-4">
        
        <!-- Thumbnails list -->
        <div class="flex md:flex-col gap-3 overflow-x-auto md:overflow-x-visible shrink-0 order-2 md:order-1">
          <button
            v-for="(image, index) in galleryImages"
            :key="image.label"
            :class="selectedImageIndex === index ? 'border-[#00a046] ring-2 ring-[#00a046]/20' : 'border-zinc-200 dark:border-zinc-800 hover:border-[#00a046]'"
            class="w-16 h-16 md:w-20 md:h-20 flex-shrink-0 border rounded-xl overflow-hidden cursor-pointer shadow-sm bg-white p-1 transition-all"
            type="button"
            @click="setSelectedImage(index)"
          >
            <img :alt="image.label" class="w-full h-full object-contain hover:scale-105 transition-transform" :src="image.src" />
          </button>
        </div>

        <!-- Main Photo Box with hover Zoom Magnifier -->
        <div
          class="flex-1 bg-white dark:bg-white/95 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm relative group overflow-hidden flex justify-center items-center aspect-[1.12/1] cursor-zoom-in order-1 md:order-2 select-none"
          @mousemove="handleMouseMove"
          @mouseleave="handleMouseLeave"
        >
          <img
            :alt="product.name"
            class="max-h-[380px] md:max-h-[460px] object-contain transition-transform duration-100 p-6 pointer-events-none"
            :style="zoomStyle"
            :src="selectedImage"
          />
          
          <!-- Zoom details guide text -->
          <div class="absolute bottom-4 left-4 bg-zinc-950/80 backdrop-blur-sm border border-white/10 rounded-lg px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-white">
            {{ isZoomed ? 'Збільшено' : 'Наведіть для наближення' }}
          </div>

          <div class="absolute inset-x-4 top-1/2 -translate-y-1/2 flex justify-between opacity-0 group-hover:opacity-100 transition-opacity">
            <button class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow hover:bg-white transition-colors" type="button" @click.stop="selectPreviousImage">
              <span class="material-symbols-outlined text-[#00a046]">chevron_left</span>
            </button>
            <button class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow hover:bg-white transition-colors" type="button" @click.stop="selectNextImage">
              <span class="material-symbols-outlined text-[#00a046]">chevron_right</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Purchase Column -->
      <div class="lg:col-span-5 flex flex-col gap-6">
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="bg-emerald-500/10 text-[#00a046] font-extrabold text-[10px] px-3 py-1 rounded-full uppercase tracking-widest border border-emerald-500/20">Преміум якість FilkxTech</span>
            <div class="flex gap-2">
              
              <!-- Wishlist action -->
              <button
                class="p-2.5 border border-zinc-200 dark:border-zinc-800 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-rose-500 transition-all flex items-center justify-center text-zinc-400 dark:text-zinc-500"
                type="button"
                @click="store.toggleWishlist(product)"
              >
                <span
                  :class="{ 'text-rose-550': store.isInWishlist(product.id) }"
                  :style="store.isInWishlist(product.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
                  class="material-symbols-outlined text-[20px]"
                >
                  favorite
                </span>
              </button>

              <!-- Compare Action -->
              <button
                class="p-2.5 border border-zinc-200 dark:border-zinc-800 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-[#00a046] transition-all flex items-center justify-center text-zinc-400 dark:text-zinc-500"
                type="button"
                @click="store.toggleCompare(product)"
                :class="{ 'text-[#00a046] border-[#00a046]/40': store.isInCompare(product.id) }"
              >
                <span class="material-symbols-outlined text-[20px]">compare_arrows</span>
              </button>
            </div>
          </div>

          <h1 class="text-2xl md:text-3xl font-black text-zinc-900 dark:text-white tracking-tight leading-tight">{{ product.name }}</h1>
          <p class="text-sm text-zinc-450 dark:text-zinc-500 font-bold">{{ product.subtitle }}</p>

          <div class="flex flex-wrap items-center gap-4 py-1 text-xs">
            <div class="flex items-center gap-1.5">
              <div class="flex text-amber-400">
                <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
              </div>
              <span class="font-extrabold text-zinc-800 dark:text-zinc-200 underline hover:text-[#00a046] transition-colors cursor-pointer">{{ product.reviews }} відгуків</span>
            </div>
            <div class="h-4 w-px bg-zinc-200 dark:bg-zinc-800"></div>
            <span class="font-bold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider">КОД: FLX-{{ product.id }}</span>
          </div>
        </div>

        <!-- Price box -->
        <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl space-y-6">
          <div class="space-y-2">
            <div class="flex flex-wrap items-baseline gap-3">
              <span class="text-3xl font-black tracking-tight text-[#00a046]">{{ formatPrice(product.price) }}</span>
              <span v-if="product.oldPrice" class="text-base text-zinc-400 line-through font-extrabold">{{ formatPrice(product.oldPrice) }}</span>
              <span v-if="product.oldPrice" class="bg-rose-600 text-white text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider ml-auto">Акція</span>
            </div>
            <p class="text-[11px] text-zinc-450 dark:text-zinc-500 font-bold">Можлива безвідсоткова оплата частинами від банків-партнерів</p>
          </div>

          <!-- Color options selector -->
          <div class="space-y-3">
            <span class="font-black text-xs uppercase tracking-wider text-zinc-450 dark:text-zinc-500">Колір: {{ selectedColor }}</span>
            <div class="flex gap-3">
              <button class="w-8 h-8 rounded-full bg-[#1c1c1c] hover:scale-105 transition-transform p-0.5 border border-white" :class="selectedColor === 'Space Black' ? 'ring-2 ring-[#00a046] ring-offset-2' : ''" type="button" @click="selectedColor = 'Space Black'"></button>
              <button class="w-8 h-8 rounded-full bg-[#d1d1d1] hover:scale-105 transition-transform p-0.5 border border-zinc-300" :class="selectedColor === 'Lunar Silver' ? 'ring-2 ring-[#00a046] ring-offset-2' : ''" type="button" @click="selectedColor = 'Lunar Silver'"></button>
              <button class="w-8 h-8 rounded-full bg-[#004d40] hover:scale-105 transition-transform p-0.5 border border-white" :class="selectedColor === 'Deep Emerald' ? 'ring-2 ring-[#00a046] ring-offset-2' : ''" type="button" @click="selectedColor = 'Deep Emerald'"></button>
            </div>
          </div>

          <!-- Configuration selector -->
          <div class="space-y-3">
            <span class="font-black text-xs uppercase tracking-wider text-zinc-450 dark:text-zinc-500">Конфігурація пам'яті</span>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="storage in ['256GB', '512GB', '1TB']"
                :key="storage"
                :class="selectedStorage === storage ? 'border-2 border-[#00a046] font-black text-[#00a046] bg-emerald-500/5 shadow-sm' : 'border border-zinc-250 dark:border-zinc-800 font-bold text-zinc-550 dark:text-zinc-400 hover:border-[#00a046] hover:text-[#00a046]'"
                class="py-2 px-3 rounded-lg transition-all text-xs"
                type="button"
                @click="selectedStorage = storage"
              >
                {{ storage }}
              </button>
            </div>
          </div>

          <!-- Add to cart -->
          <div class="pt-2 space-y-3">
            <button class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-3.5 rounded-lg font-extrabold text-sm shadow-md hover:shadow-lg hover:shadow-emerald-500/10 active:scale-[0.98] transition-all flex items-center justify-center gap-2 uppercase tracking-wider" type="button" @click="store.addToCart(product)">
              <span class="material-symbols-outlined text-[20px]">shopping_cart</span>
              Додати в кошик
            </button>
            <button class="w-full border border-zinc-300 dark:border-zinc-700 hover:bg-zinc-55 dark:hover:bg-zinc-800 text-zinc-800 dark:text-zinc-200 py-3.5 rounded-lg font-extrabold text-sm active:scale-[0.98] transition-all uppercase tracking-wider" type="button" @click="store.addToCart(product)">
              Швидке замовлення
            </button>
          </div>

          <!-- Delivery info -->
          <div class="grid grid-cols-2 gap-px bg-zinc-200/50 dark:bg-zinc-800 rounded-lg overflow-hidden border border-zinc-200/50 dark:border-zinc-800">
            <div class="bg-white dark:bg-zinc-900 p-4.5 flex flex-col gap-1 items-center text-center">
              <span class="material-symbols-outlined text-[#00a046] text-[22px]">inventory_2</span>
              <span class="text-[10px] font-black text-[#00a046] tracking-wider uppercase mt-1">В наявності</span>
              <span class="text-[10px] text-zinc-450 dark:text-zinc-500">Відправка сьогодні</span>
            </div>
            <div class="bg-white dark:bg-zinc-900 p-4.5 flex flex-col gap-1 items-center text-center">
              <span class="material-symbols-outlined text-[#00a046] text-[22px]">local_shipping</span>
              <span class="text-[10px] font-black text-zinc-700 dark:text-zinc-300 tracking-wider uppercase mt-1">Доставка</span>
              <span class="text-[10px] text-zinc-450 dark:text-zinc-500">Безкоштовно від 2000 ₴</span>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Bundle section -->
    <section class="mt-16">
      <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
        <div>
          <h2 class="font-extrabold text-xl md:text-2xl text-zinc-900 dark:text-white tracking-tight">Краща ціна разом</h2>
          <p class="text-xs text-zinc-450 dark:text-zinc-500 mt-2">Ми підібрали сумісні аксесуари для максимальної вигоди.</p>
        </div>
      </div>
      
      <div class="bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800 p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
        
        <!-- Bundle items list -->
        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-6 flex-1">
          <template v-for="(item, index) in bundleItems" :key="item.id">
            
            <div class="relative w-36 text-center transition-all bg-white dark:bg-zinc-850 p-4 rounded-xl border border-zinc-100 dark:border-zinc-800" :class="{ 'opacity-100 ring-1 ring-emerald-500/20': item.locked || selectedBundleIds.includes(item.id), 'opacity-60': !item.locked && !selectedBundleIds.includes(item.id) }">
              <button v-if="!item.locked" class="absolute -right-2 -top-2 z-10 flex w-7 h-7 items-center justify-center rounded-full bg-zinc-900 text-white shadow-sm" type="button" @click="toggleBundleItem(item)">
                <span class="material-symbols-outlined text-[16px]">{{ selectedBundleIds.includes(item.id) ? 'check' : 'add' }}</span>
              </button>
              <div class="aspect-square bg-white rounded-lg p-2 flex items-center justify-center mb-3">
                <img :alt="item.name" class="max-h-[80px] object-contain" :src="item.image" />
              </div>
              <div class="space-y-1">
                <p class="text-[10px] font-black uppercase truncate text-zinc-800 dark:text-zinc-200">{{ item.name }}</p>
                <p class="text-[9px] text-zinc-450 dark:text-zinc-500 font-bold">{{ item.category }}</p>
                <p class="text-xs font-black text-[#00a046]">{{ formatPrice(item.price) }}</p>
              </div>
            </div>

            <span v-if="index < bundleItems.length - 1" class="material-symbols-outlined text-zinc-300 dark:text-zinc-700 text-2xl font-light">add</span>
          </template>
        </div>

        <!-- Bundle summary calculator -->
        <div class="lg:w-72 border-t lg:border-t-0 lg:border-l border-zinc-200 dark:border-zinc-800 pt-6 lg:pt-0 lg:pl-8 space-y-4">
          <div class="space-y-1">
            <p class="text-[10px] font-black uppercase tracking-wider text-zinc-450 dark:text-zinc-500">Загальна вартість:</p>
            <p class="text-2xl font-black text-[#00a046] tracking-tight">{{ formatPrice(bundleTotal) }}</p>
            <p class="text-[10px] text-zinc-400 dark:text-zinc-550 font-bold">Окремо: {{ formatPrice(bundleSubtotal) }}</p>
            <p class="bg-rose-100 dark:bg-rose-950/30 text-rose-600 dark:text-rose-450 font-black text-[9px] px-2 py-0.5 rounded inline-block tracking-wider uppercase">Економія: {{ formatPrice(bundleSavings) }}</p>
          </div>
          <button class="w-full bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-3 rounded-lg font-extrabold text-xs transition-all uppercase tracking-wider shadow-sm" type="button" @click="addBundleToCart">
            Додати комплект до кошика
          </button>
        </div>
      </div>
    </section>

    <!-- Details tabs panel -->
    <section class="mt-16">
      <div class="flex border-b border-zinc-200 dark:border-zinc-800 mb-12 overflow-x-auto hide-scrollbar scroll-smooth">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          :class="activeTab === tab.id ? 'text-[#00a046] font-black border-b-2 border-[#00a046]' : 'text-zinc-450 hover:text-[#00a046] font-bold'"
          class="px-8 py-5 text-sm whitespace-nowrap transition-all uppercase tracking-wider"
          type="button"
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Tab Content -->
        <div class="lg:col-span-8">
          
          <!-- Experience Tab -->
          <section v-if="activeTab === 'experience'" class="space-y-6">
            <div class="max-w-2xl space-y-4">
              <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight leading-snug">Інновації та ергономіка у кожній деталі пристрою</h3>
              <p class="text-sm text-zinc-550 dark:text-zinc-400 leading-relaxed">
                Кожен компонент FilkxTech розроблений для максимальної продуктивності та стабільності. Ми використовуємо тільки найкращі матеріали та компоненти від лідерів ринку, щоб ви отримували задоволення від щоденної роботи.
              </p>
            </div>
            <img alt="Детальний огляд плати" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAlLPi4qPWW-pjg1qJFIjxIZKyFFhepLHkWr7dmmTfsPA-M94Tz3oVozEJ4-mN77JEATZieBYs6_9MPJKNZBIpA-0eFrFzVkd7s_nfCbkw9dFyH0F0Cwsnx3aE7dAmdQBmrm6qHr0oWhsyKzfNcimCHqfoChvRJQiDRpFFDdtfltbepaRt4_GntXGDbnBMOqWB9y_kw0QYy-g32zYaD67xLw0y8N2gvfzLQO_5tVsFm9zDvWtCXD-gOLSTpEDIra9-vc9wMXlwpT20" />
          </section>

          <!-- Specifications Tab -->
          <section v-else-if="activeTab === 'specs'" class="space-y-6">
            <h3 class="font-extrabold text-lg text-zinc-900 dark:text-white flex items-center gap-2">
              <span class="material-symbols-outlined text-[#00a046]">terminal</span>
              Технічні характеристики
            </h3>
            <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800 overflow-hidden shadow-sm">
              <table class="w-full text-left border-collapse text-xs md:text-sm">
                <thead>
                  <tr class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-150 dark:border-zinc-800">
                    <th class="p-4 font-black uppercase tracking-wider text-[10px] text-zinc-450 dark:text-zinc-500 w-1/3">Характеристика</th>
                    <th class="p-4 font-black uppercase tracking-wider text-[10px] text-zinc-450 dark:text-zinc-500">Значення</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                  <tr v-for="(spec, index) in productSpecs" :key="spec[0]" :class="index % 2 ? 'bg-zinc-50/30 dark:bg-zinc-850/10' : ''" class="hover:bg-emerald-500/5 transition-colors">
                    <td class="p-4 font-bold text-zinc-800 dark:text-zinc-200">{{ spec[0] }}</td>
                    <td class="p-4 text-zinc-600 dark:text-zinc-400 font-extrabold">{{ spec[1] }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- Reviews Tab -->
          <section v-else-if="activeTab === 'reviews'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
              <div class="md:col-span-4 p-6 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800 text-center space-y-2">
                <p class="text-5xl font-black text-zinc-900 dark:text-white tracking-tighter">{{ product.rating }}</p>
                <div class="flex justify-center text-amber-400 mb-1">
                  <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">star</span>
                </div>
                <p class="text-[10px] font-black uppercase tracking-wider text-zinc-450 dark:text-zinc-500">Середня оцінка</p>
              </div>
              <div class="md:col-span-8 p-6 border border-zinc-150 dark:border-zinc-800 rounded-xl flex flex-col justify-center space-y-3">
                <div v-for="rating in [{ label: '5 зірок', value: '85%' }, { label: '4 зірки', value: '10%' }, { label: '3 зірки', value: '5%' }]" :key="rating.label" class="flex items-center gap-4 text-xs">
                  <span class="font-extrabold w-12 text-right text-zinc-600 dark:text-zinc-400">{{ rating.label }}</span>
                  <div class="flex-1 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <div class="h-full bg-[#00a046]" :style="{ width: rating.value }"></div>
                  </div>
                  <span class="font-extrabold w-10 text-zinc-400">{{ rating.value }}</span>
                </div>
              </div>
            </div>
            
            <div class="space-y-4">
              <article v-for="review in reviews" :key="review.name" class="p-5 bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl shadow-sm space-y-3">
                <div class="flex items-center justify-between gap-4">
                  <div>
                    <h4 class="font-black text-sm text-zinc-900 dark:text-white leading-snug">{{ review.title }}</h4>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-zinc-450 dark:text-zinc-500 mt-0.5">{{ review.name }}</p>
                  </div>
                  <div class="flex text-amber-400">
                    <span v-for="star in 5" :key="star" class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
                  </div>
                </div>
                <p class="text-xs md:text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">{{ review.text }}</p>
              </article>
            </div>
          </section>

          <!-- Support Tab -->
          <section v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl shadow-sm space-y-3 text-center md:text-left">
              <span class="material-symbols-outlined text-[#00a046] text-[28px]">support_agent</span>
              <h3 class="font-black text-sm text-zinc-900 dark:text-white">Консультація експерта</h3>
              <p class="text-xs text-zinc-550 dark:text-zinc-400 leading-relaxed">Наші спеціалісти готові допомогти з налаштуванням та перенесенням даних.</p>
            </div>
            <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl shadow-sm space-y-3 text-center md:text-left">
              <span class="material-symbols-outlined text-[#00a046] text-[28px]">local_shipping</span>
              <h3 class="font-black text-sm text-zinc-900 dark:text-white">Доставка і заміна</h3>
              <p class="text-xs text-zinc-550 dark:text-zinc-400 leading-relaxed">Безкоштовна доставка, можливість примірки та швидкий обмін товару.</p>
            </div>
            <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl shadow-sm space-y-3 text-center md:text-left">
              <span class="material-symbols-outlined text-[#00a046] text-[28px]">workspace_premium</span>
              <h3 class="font-black text-sm text-zinc-900 dark:text-white">Програма захисту</h3>
              <p class="text-xs text-zinc-550 dark:text-zinc-400 leading-relaxed">Можливість продовження гарантії та страхування від випадкових пошкоджень.</p>
            </div>
          </section>

        </div>

        <!-- Guarantees Sidebar -->
        <aside class="lg:col-span-4 space-y-6">
          
          <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl shadow-sm space-y-5">
            <h4 class="font-black text-[10px] uppercase tracking-wider text-zinc-450 dark:text-zinc-500 flex items-center gap-2">
              <span class="material-symbols-outlined text-[#00a046] text-[18px]">verified</span>
              Гарантії якості
            </h4>
            <ul class="space-y-4 text-xs">
              <li v-for="item in qualityGuarantees" :key="item.title" class="flex gap-4 items-start">
                <span class="material-symbols-outlined text-[#00a046] mt-0.5">{{ item.icon }}</span>
                <div class="space-y-0.5">
                  <p class="font-black text-zinc-850 dark:text-zinc-100">{{ item.title }}</p>
                  <p class="text-zinc-500 dark:text-zinc-450">{{ item.text }}</p>
                </div>
              </li>
            </ul>
          </div>

          <div class="p-6 bg-emerald-500/10 dark:bg-emerald-950/20 text-[#00a046] rounded-xl border border-emerald-500/20 shadow-sm space-y-4 relative overflow-hidden">
            <div class="relative z-10 space-y-3">
              <h4 class="font-black text-sm uppercase tracking-wider text-[#00a046]">Технічний радник</h4>
              <p class="text-xs text-zinc-650 dark:text-zinc-400 leading-relaxed">Наші інженери допоможуть обрати ідеальну конфігурацію під ваші потреби в режимі реального часу.</p>
              <button class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-2.5 rounded-lg font-extrabold text-xs flex items-center justify-center gap-1.5 transition-all shadow-sm" type="button">
                <span class="material-symbols-outlined text-[16px]">chat_bubble</span>
                ПОЧАТИ ЧАТ
              </button>
            </div>
          </div>

        </aside>

      </div>
    </section>

  </main>

  <!-- Sticky Bottom Buy Bar -->
  <div class="fixed right-0 bottom-0 left-0 z-40 border-t border-zinc-200 dark:border-zinc-800 bg-white/95 dark:bg-zinc-900/95 shadow-lg backdrop-blur-md transition-transform duration-300 ease-in-out translate-y-full" :class="{ 'translate-y-0': showStickyBar }">
    <div class="max-w-container-max mx-auto px-4 md:px-8 h-20 flex items-center justify-between gap-6">
      <div class="flex items-center gap-4 overflow-hidden min-w-0">
        <img alt="Компактне прев'ю товару" class="w-12 h-12 object-contain rounded-lg border border-zinc-200 dark:border-zinc-800 hidden sm:block p-1 bg-white" :src="selectedImage" />
        <div class="truncate">
          <p class="font-black text-zinc-900 dark:text-white text-sm truncate tracking-tight">{{ product.name }}</p>
          <div class="flex items-center gap-3 text-xs mt-0.5">
            <span class="text-[#00a046] font-black">{{ formatPrice(product.price) }}</span>
            <span class="text-[9px] font-black text-white bg-[#00a046] px-1.5 py-0.5 rounded uppercase tracking-wider">В наявності</span>
          </div>
        </div>
      </div>
      <button class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-3 rounded-lg font-extrabold text-xs shadow-md transition-all active:scale-[0.98] shrink-0 uppercase tracking-wider" type="button" @click="store.addToCart(product)">
        Додати в кошик
      </button>
    </div>
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

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
