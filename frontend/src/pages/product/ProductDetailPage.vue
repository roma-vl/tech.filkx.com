<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { store } from '@/store.js';
import api from '@/services/api.js';

const route = useRoute();
const router = useRouter();

const isLoading = ref(false);
const rawProduct = ref(null);
const activeVariantId = ref(null);

const activeVariant = computed(() => {
  if (!rawProduct.value || !rawProduct.value.variants || rawProduct.value.variants.length === 0) {
    return null;
  }
  if (activeVariantId.value) {
    const found = rawProduct.value.variants.find(v => v.id === activeVariantId.value);
    if (found) return found;
  }
  return rawProduct.value.variants[0];
});

const product = computed(() => {
  if (rawProduct.value) {
    const mainVariant = activeVariant.value;
    const price = mainVariant ? parseFloat(mainVariant.price) : 0;
    const oldPrice = mainVariant && mainVariant.oldPrice ? parseFloat(mainVariant.oldPrice) : null;
    const totalStock = mainVariant ? (mainVariant.stocks || []).reduce((acc, s) => acc + (parseInt(s.quantity) - parseInt(s.reserved)), 0) : 0;

    let image = '';
    if (mainVariant && mainVariant.dimensions && mainVariant.dimensions.images && mainVariant.dimensions.images.length > 0) {
      const primary = mainVariant.dimensions.images.find(img => img.isPrimary) || mainVariant.dimensions.images[0];
      if (primary && primary.url) {
        image = primary.url;
      }
    }
    
    if (!image) {
      image = 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZjrYzoYVLWW_oiXKtFfrvXfrqZhFl0aOo-qiqP-OxioJPU85soCgr1bPX8-8SrIpEgyr7zYqcamNRaM1BW5yOnQdyQkcNC89uNihkW1bThAYw05lRVqC36IMTBCvBLVH7opxwC_Q3tAwXBXFTV3E_7Pec49dMJ6oEmwa-i1h3rfPR3C3ZxfrlPDm4iN8h3YEy4Smhr2pI6IcA1YpRV8_hq162IYmxl8-kkt1WI_Z9ARaUKWft3ncDr_m6Dug4Fa0Nm0Rr2ngLp0Q';
    }

    const name = typeof rawProduct.value.name === 'object' ? (rawProduct.value.name.uk || rawProduct.value.name.en) : rawProduct.value.name;
    const description = typeof rawProduct.value.description === 'object' ? (rawProduct.value.description.uk || rawProduct.value.description.en) : rawProduct.value.description;

    const getAttrValue = (code) => {
      const list = [...(rawProduct.value.attributeValues || []), ...(mainVariant?.attributeValues || [])];
      const match = list.find(av => av.attribute?.code === code);
      if (!match) return null;
      return match.customValue || match.attributeValue?.value?.uk || match.attributeValue?.value?.en || match.attributeValue?.value;
    };

    const ramVal = getAttrValue('memory') || getAttrValue('ram');
    const storageVal = getAttrValue('storage');
    const subtitle = ramVal && storageVal ? `${ramVal} ОЗУ / ${storageVal}` : (rawProduct.value.brand?.name ? `${rawProduct.value.brand.name} Edition` : 'Premium Tech Edition');

    return {
      id: mainVariant ? mainVariant.id : rawProduct.value.id,
      productId: rawProduct.value.id,
      slug: rawProduct.value.slug,
      name: name,
      category: rawProduct.value.categories && rawProduct.value.categories[0] ? (rawProduct.value.categories[0].name.uk || rawProduct.value.categories[0].name.en) : 'Смартфони',
      subtitle: subtitle,
      price: price,
      oldPrice: oldPrice || price * 1.15,
      image: image,
      rating: rawProduct.value.rating || 4.8,
      reviews: rawProduct.value.reviews || 84,
      description: description
    };
  }
  return null;
});

const galleryImages = computed(() => {
  if (!rawProduct.value) return [];
  const mainVariant = activeVariant.value;
  const images = [];
  
  if (mainVariant && mainVariant.dimensions && mainVariant.dimensions.images && mainVariant.dimensions.images.length > 0) {
    mainVariant.dimensions.images.forEach((img, idx) => {
      images.push({
        label: img.isPrimary ? 'Основний вигляд' : `Вигляд ${idx + 1}`,
        src: img.url
      });
    });
  }
  
  if (images.length === 0) {
    const mainImg = product.value?.image || 'https://lh3.googleusercontent.com/aida-public/AB6AXuBZjrYzoYVLWW_oiXKtFfrvXfrqZhFl0aOo-qiqP-OxioJPU85soCgr1bPX8-8SrIpEgyr7zYqcamNRaM1BW5yOnQdyQkcNC89uNihkW1bThAYw05lRVqC36IMTBCvBLVH7opxwC_Q3tAwXBXFTV3E_7Pec49dMJ6oEmwa-i1h3rfPR3C3ZxfrlPDm4iN8h3YEy4Smhr2pI6IcA1YpRV8_hq162IYmxl8-kkt1WI_Z9ARaUKWft3ncDr_m6Dug4Fa0Nm0Rr2ngLp0Q';
    images.push(
      { label: 'Основний вигляд', src: mainImg },
      { label: 'Вигляд збоку', src: mainImg },
      { label: 'Детальний макро-вигляд', src: mainImg }
    );
  }
  
  return images;
});

const availableColors = computed(() => {
  if (!rawProduct.value?.variants) return [];
  const colors = new Set();
  rawProduct.value.variants.forEach(v => {
    const colorAttr = v.attributeValues?.find(av => av.attribute?.code === 'color');
    if (colorAttr) {
      const val = colorAttr.customValue || colorAttr.attributeValue?.value?.uk || colorAttr.attributeValue?.value?.en;
      if (val) colors.add(val);
    }
  });
  return Array.from(colors);
});

const availableStorage = computed(() => {
  if (!rawProduct.value?.variants) return [];
  const storage = new Set();
  rawProduct.value.variants.forEach(v => {
    const storageAttr = v.attributeValues?.find(av => av.attribute?.code === 'storage' || av.attribute?.code === 'memory');
    if (storageAttr) {
      const val = storageAttr.customValue || storageAttr.attributeValue?.value?.uk || storageAttr.attributeValue?.value?.en;
      if (val) storage.add(val);
    }
  });
  return Array.from(storage);
});

const selectedImageIndex = ref(0);
const selectedColor = ref('');
const selectedStorage = ref('');

const selectVariantByAttributes = (attributeCode, value) => {
  if (!rawProduct.value?.variants) return;
  
  const matchedVariant = rawProduct.value.variants.find(v => {
    const attr = v.attributeValues?.find(av => {
      const code = av.attribute?.code;
      if (attributeCode === 'memory') {
        return code === 'memory' || code === 'storage' || code === 'ram';
      }
      return code === attributeCode;
    });
    if (!attr) return false;
    const val = attr.customValue || attr.attributeValue?.value?.uk || attr.attributeValue?.value?.en;
    return val === value;
  });
  
  if (matchedVariant) {
    activeVariantId.value = matchedVariant.id;
  }
};

watch(activeVariant, (newVariant) => {
  if (!newVariant) return;
  selectedImageIndex.value = 0;
  
  const getAttrValue = (code) => {
    const match = newVariant.attributeValues?.find(av => av.attribute?.code === code);
    if (!match) return null;
    return match.customValue || match.attributeValue?.value?.uk || match.attributeValue?.value?.en || match.attributeValue?.value;
  };
  
  const colorVal = getAttrValue('color');
  if (colorVal) selectedColor.value = colorVal;
  
  const storageVal = getAttrValue('storage') || getAttrValue('memory') || getAttrValue('ram');
  if (storageVal) selectedStorage.value = storageVal;
}, { immediate: true });

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
  if (!rawProduct.value) return [];
  
  const specs = [];
  const list = [];
  
  if (rawProduct.value.attributeValues) {
    list.push(...rawProduct.value.attributeValues);
  }
  
  const mainVariant = rawProduct.value.variants?.[0] || null;
  if (mainVariant && mainVariant.attributeValues) {
    list.push(...mainVariant.attributeValues);
  }
  
  const seenCodes = new Set();
  list.forEach(av => {
    if (!av.attribute) return;
    const code = av.attribute.code;
    if (seenCodes.has(code)) return;
    seenCodes.add(code);
    
    const label = av.attribute.name?.uk || av.attribute.name?.en || av.attribute.name || code;
    
    let val = '';
    if (av.customValue !== null && av.customValue !== undefined) {
      val = av.customValue;
    } else if (av.attributeValue) {
      val = av.attributeValue.value?.uk || av.attributeValue.value?.en || av.attributeValue.value || '';
    }
    
    if (label && val) {
      specs.push([label, val]);
    }
  });

  if (mainVariant && mainVariant.weight && !seenCodes.has('weight')) {
    specs.push(['Вага', `${mainVariant.weight} кг`]);
  }
  if (rawProduct.value.brand && !seenCodes.has('brand')) {
    specs.push(['Бренд', rawProduct.value.brand.name]);
  }
  if (mainVariant && mainVariant.sku && !seenCodes.has('sku')) {
    specs.push(['Артикул (SKU)', mainVariant.sku]);
  }
  
  return specs;
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
          <div v-if="availableColors.length > 0" class="space-y-3">
            <span class="font-black text-xs uppercase tracking-wider text-zinc-450 dark:text-zinc-500">Колір: {{ selectedColor }}</span>
            <div class="flex gap-3">
              <button 
                v-for="color in availableColors"
                :key="color"
                :title="color"
                class="w-8 h-8 rounded-full hover:scale-105 transition-transform p-0.5 border"
                :class="[
                  selectedColor === color ? 'ring-2 ring-[#00a046] ring-offset-2' : '',
                  color === 'Space Black' || color.toLowerCase().includes('black') || color.toLowerCase().includes('чорн') ? 'bg-[#1c1c1c] border-white' : 
                  color === 'Lunar Silver' || color.toLowerCase().includes('silver') || color.toLowerCase().includes('срібл') ? 'bg-[#d1d1d1] border-zinc-300' :
                  color === 'Deep Emerald' || color.toLowerCase().includes('emerald') || color.toLowerCase().includes('зелен') ? 'bg-[#004d40] border-white' :
                  'bg-zinc-400 border-zinc-300'
                ]" 
                type="button" 
                @click="selectVariantByAttributes('color', color)"
              ></button>
            </div>
          </div>

          <!-- Configuration selector -->
          <div v-if="availableStorage.length > 0" class="space-y-3">
            <span class="font-black text-xs uppercase tracking-wider text-zinc-450 dark:text-zinc-500">Конфігурація</span>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="storage in availableStorage"
                :key="storage"
                :class="selectedStorage === storage ? 'border-2 border-[#00a046] font-black text-[#00a046] bg-emerald-500/5 shadow-sm' : 'border border-zinc-250 dark:border-zinc-800 font-bold text-zinc-550 dark:text-zinc-400 hover:border-[#00a046] hover:text-[#00a046]'"
                class="py-2 px-3 rounded-lg transition-all text-xs min-w-[70px]"
                type="button"
                @click="selectVariantByAttributes('memory', storage)"
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
              <h3 class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight leading-snug">Опис товару</h3>
              <p class="text-sm text-zinc-550 dark:text-zinc-400 leading-relaxed whitespace-pre-line">
                {{ product.description }}
              </p>
            </div>
            <img v-if="galleryImages[1]" :alt="product.name" class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm object-contain max-h-[450px] bg-white p-4" :src="galleryImages[1].src" />
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
