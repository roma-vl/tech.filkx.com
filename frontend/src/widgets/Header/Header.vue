<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import { useRouter, RouterLink } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { useAuthStore } from "@/entities/user/model/authStore";
import { productApi } from "@/shared/services/api/productApi";
import { mapDbCategoriesToMenu } from "@/shared/utils/categoryMapper";

interface SearchProduct {
  id: string | number;
  slug: string;
  name: string;
  category: string;
  price: number;
  image: string;
}

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

const isDark = ref(false);
const currentLang = ref("UA");

const toggleDarkMode = () => {
  isDark.value = !isDark.value;
  if (isDark.value) {
    document.documentElement.classList.add("dark");
    localStorage.setItem("theme", "dark");
  } else {
    document.documentElement.classList.remove("dark");
    localStorage.setItem("theme", "light");
  }
};

const setLanguage = (lang: string) => {
  currentLang.value = lang;
  localStorage.setItem("lang", lang);
};

const searchInput = ref<HTMLInputElement | null>(null);
const searchQuery = ref("");
const showDropdown = ref(false);
const isMegaMenuOpen = ref(false);

const popularQueries = [
  "SSD",
  "Кава",
  "Свічки",
  "Чайник",
  "Кросівки",
  "Jack Daniel's",
  "Телефон",
  "Мишка",
  "Lego",
  "Сумка",
  "Крокси",
  "Наушники",
];

const searchResults = ref<SearchProduct[]>([]);
const isSearching = ref(false);

const mapApiProduct = (prod: any): SearchProduct => {
  const mainVariant = prod.variants?.[0] || null;
  const price = mainVariant ? parseFloat(mainVariant.price) : 0;

  let image = "";
  if (mainVariant && mainVariant.dimensions && mainVariant.dimensions.images) {
    const primary =
      mainVariant.dimensions.images.find((img: any) => img.isPrimary) ||
      mainVariant.dimensions.images[0];
    if (primary && primary.url) {
      image = primary.url;
    }
  }

  if (!image) {
    image =
      "https://lh3.googleusercontent.com/aida-public/AB6AXuC0pdjuB0YFLkInl4zdi5bxprMDGyN-cagKuDnRtaemxo2Cc7uHUFxB6DBm4KDzEA7-TWHm_tJ2X975lakn1VUXxj_Zii1600ZoHaFVsz42-JNUnzhMZS1yc7eB5PimODocEzaKmUou2cKXOmIO_iZOVYFvo3cykUosBr0wQGW7pts6rONrYQbozd8m96y1s0lscEtxiXD3coOXigoJlVixBgNJVGo917sZReo9Lr1nYzzcVx33iqM0_SAspKG6N-tlAqBX2Ta60sM";
  }

  const name =
    typeof prod.name === "object" ? prod.name.uk || prod.name.en : prod.name;

  const categoryObj = prod.categories?.[0] || null;
  const category = categoryObj
    ? typeof categoryObj.name === "object"
      ? categoryObj.name.uk || categoryObj.name.en
      : categoryObj.name
    : "Товари";

  return {
    id: prod.id,
    slug: prod.slug,
    name,
    category,
    price,
    image,
  };
};

let debounceTimeout: ReturnType<typeof setTimeout> | null = null;

watch(searchQuery, (newQuery) => {
  if (debounceTimeout) clearTimeout(debounceTimeout);

  const query = newQuery.trim();
  if (!query) {
    searchResults.value = [];
    return;
  }

  debounceTimeout = setTimeout(async () => {
    isSearching.value = true;
    try {
      const response = await productApi.catalogGetProducts({ search: query });
      const responseData = response.data;
      if (
        responseData &&
        (responseData.success || responseData.status === "success")
      ) {
        const productsList = responseData.data?.data || responseData.data || [];
        searchResults.value = productsList.map(mapApiProduct);
      }
    } catch (error) {
      console.error("Search query failed:", error);
    } finally {
      isSearching.value = false;
    }
  }, 300);
});

// Using dynamic database categories mapped to columns layout
const categories = ref<any[]>([]);
const activeCat = ref<any>(null);

const selectCategory = (cat: any) => {
  activeCat.value = cat;
};

const getLinkRoute = (link: any) => {
  return {
    name: "catalog",
    query: { category: link.slug }
  };
};

const getGroupRoute = (group: any) => {
  return {
    name: "catalog",
    query: { category: group.slug }
  };
};

const selectPopularQuery = (query: string) => {
  searchQuery.value = query;
  triggerSearch();
};

const triggerSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({
      name: "catalog",
      query: { search: searchQuery.value.trim() },
    });
    showDropdown.value = false;
  }
};

const selectSearchResult = (product: SearchProduct) => {
  searchQuery.value = "";
  showDropdown.value = false;
  router.push({ name: "product-detail", params: { id: product.slug } });
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};

const highlightMatch = (name: string, query: string) => {
  if (!query.trim()) return name;
  const regex = new RegExp(`(${query.trim()})`, "gi");
  return name.replace(
    regex,
    '<span class="font-extrabold text-[#00a046] dark:text-[#00b050]">$1</span>',
  );
};

const filteredProducts = computed(() => {
  return searchResults.value;
});

const handleKeydown = (e: KeyboardEvent) => {
  if ((e.metaKey || e.ctrlKey) && e.key === "k") {
    e.preventDefault();
    searchInput.value?.focus();
  }
  if (e.key === "Escape") {
    showDropdown.value = false;
    isMegaMenuOpen.value = false;
  }
};

const handleClickOutside = (e: MouseEvent) => {
  const container = searchInput.value?.closest(".search-container");
  if (container && !container.contains(e.target as Node)) {
    showDropdown.value = false;
  }
  const megaWrap = document.querySelector(".mega-menu-wrapper");
  if (
    megaWrap &&
    !megaWrap.contains(e.target as Node) &&
    !(e.target as HTMLElement).closest(".catalog-btn") &&
    !(e.target as HTMLElement).closest(".burger-btn")
  ) {
    isMegaMenuOpen.value = false;
  }
};

const toggleCatalog = () => {
  isMegaMenuOpen.value = !isMegaMenuOpen.value;
};

const triggerVoiceSearch = () => {
  cartStore.addToast("Voice search activated. Start speaking...", "info");
};

const unreadCount = computed(() => cartStore.unreadNotificationsCount);

const fetchUnreadCount = async () => {
  if (!authStore.isAuthenticated) {
    cartStore.unreadNotificationsCount = 0;
    return;
  }
  await cartStore.fetchUnreadNotificationsCount();
};

watch(
  () => authStore.isAuthenticated,
  (newVal) => {
    if (newVal) {
      fetchUnreadCount();
    } else {
      cartStore.unreadNotificationsCount = 0;
    }
  },
);

onMounted(async () => {
  window.addEventListener("keydown", handleKeydown);
  document.addEventListener("click", handleClickOutside);
  fetchUnreadCount();

  // Load saved theme
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark" || (!savedTheme && window.matchMedia("(prefers-color-scheme: dark)").matches)) {
    isDark.value = true;
    document.documentElement.classList.add("dark");
  } else {
    isDark.value = false;
    document.documentElement.classList.remove("dark");
  }

  // Load language
  currentLang.value = localStorage.getItem("lang") || "UA";

  try {
    const response = await productApi.catalogGetCategories();
    const responseData = response.data;
    if (
      responseData &&
      (responseData.success || responseData.status === "success")
    ) {
      const dbCats = responseData.data || [];
      categories.value = mapDbCategoriesToMenu(dbCats);
      if (categories.value.length > 0) {
        activeCat.value = categories.value[0];
      }
    }
  } catch (error) {
    console.error("Failed to load mega menu categories:", error);
  }
});

onUnmounted(() => {
  window.removeEventListener("keydown", handleKeydown);
  document.removeEventListener("click", handleClickOutside);
});
</script>

<template>
  <!-- Main Header Shell -->
  <header class="sticky top-0 z-50 w-full bg-[#211f1f] text-white shadow-md">
    <!-- Announcement bar -->
    <div class="hidden md:block bg-[#13191f] border-b border-white/[0.06] text-zinc-400">
      <div class="max-w-container-max mx-auto px-4 md:px-8 py-2 flex items-center justify-between text-[11.5px] gap-4">
        <!-- Left: sota.store style top menu links -->
        <div class="flex items-center gap-5">
          <router-link to="/shipping-payment" class="hover:text-white transition-colors">Оплата та доставка</router-link>
          <router-link to="/warranty-returns" class="hover:text-white transition-colors">Гарантія та обмін</router-link>
          <router-link to="/service" class="hover:text-white transition-colors">Сервіс</router-link>
          <router-link to="/services" class="hover:text-white transition-colors">Послуги</router-link>
          <router-link to="/installments" class="hover:text-white transition-colors">Розстрочка</router-link>
          <router-link to="/filkx-exchange" class="hover:text-white font-extrabold text-[#00a046] hover:text-[#00b050] transition-colors">Filkx Обмін</router-link>
          <router-link to="/contacts" class="hover:text-white transition-colors">Контакти</router-link>
        </div>

        <!-- Right: phone, theme toggle, and language switcher -->
        <div class="flex items-center gap-5">
          <!-- Phone link -->
          <a href="tel:0800330131" class="flex items-center gap-1 hover:text-white transition-colors whitespace-nowrap font-bold text-zinc-300">
            0 800 33-01-31
            <span class="material-symbols-outlined text-[13px] select-none">keyboard_arrow_down</span>
          </a>

          <!-- Theme Toggle Button -->
          <button
            type="button"
            class="flex items-center justify-center p-1 rounded hover:bg-white/10 text-zinc-400 hover:text-white transition-colors"
            @click="toggleDarkMode"
            :title="isDark ? 'Увімкнути світлу тему' : 'Увімкнути темну тему'"
          >
            <span class="material-symbols-outlined text-[16px]">
              {{ isDark ? 'light_mode' : 'dark_mode' }}
            </span>
          </button>

          <!-- Language Selector -->
          <div class="flex items-center bg-[#252a32] p-0.5 rounded border border-white/[0.05]">
            <button
              type="button"
              class="px-2 py-0.5 rounded text-[10px] font-black tracking-wider transition-all"
              :class="
                currentLang === 'UA'
                  ? 'bg-[#00a046] text-white shadow-sm'
                  : 'text-zinc-400 hover:text-zinc-200'
              "
              @click="setLanguage('UA')"
            >
              UA
            </button>
            <button
              type="button"
              class="px-2 py-0.5 rounded text-[10px] font-black tracking-wider transition-all"
              :class="
                currentLang === 'EN'
                  ? 'bg-[#00a046] text-white shadow-sm'
                  : 'text-zinc-400 hover:text-zinc-200'
              "
              @click="setLanguage('EN')"
            >
                EN
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="max-w-container-max mx-auto h-16 px-4 md:px-8 flex items-center justify-between gap-4"
    >
      <!-- Left: Burger & Logo -->
      <div class="flex items-center gap-3">
        <button
          class="burger-btn p-1.5 hover:bg-white/10 rounded-lg transition-colors flex items-center justify-center shrink-0"
          title="Menu"
          @click="cartStore.openDrawer('account')"
        >
          <img
            v-if="authStore.user?.avatarUrl"
            :src="authStore.user.avatarUrl"
            class="w-7 h-7 object-cover rounded-full border border-white/20 select-none"
          />
          <span v-else class="material-symbols-outlined text-2xl text-white"
            >menu</span
          >
        </button>

        <!-- Brand/Logo (Rozetka Green Smiley Style) -->
        <a
          class="flex items-center gap-2 hover:opacity-90 transition-opacity"
          href="/"
          @click.prevent="router.push('/')"
        >
          <span
            class="font-extrabold text-lg tracking-tight text-white hidden sm:inline-block"
          >
            TechNova
          </span>
        </a>
      </div>

      <!-- Catalog Toggle Button -->
      <button
        :class="isMegaMenuOpen ? 'bg-[#009040]' : 'bg-[#00a046] hover:bg-[#00b050]'"
        class="catalog-btn hidden md:flex items-center gap-2 px-4 py-2 rounded-lg font-bold text-sm text-white transition-all shrink-0 shadow-sm"
        @click="toggleCatalog"
      >
        <span v-if="!isMegaMenuOpen" class="flex flex-col gap-[4px] w-[15px] shrink-0">
          <span class="block h-[2px] bg-white rounded-full w-full" />
          <span class="block h-[2px] bg-white rounded-full w-[10px]" />
          <span class="block h-[2px] bg-white rounded-full w-full" />
        </span>
        <span v-else class="material-symbols-outlined text-[18px]">close</span>
        <span>Каталог</span>
      </button>

      <!-- Center: Rozetka Styled Search Input -->
      <div class="flex-grow max-w-3xl search-container relative z-40">
        <form
          class="flex items-center bg-white dark:bg-zinc-800 rounded-lg overflow-hidden shadow-sm h-10 border border-transparent dark:border-zinc-700/80 focus-within:border-zinc-500 dark:focus-within:border-zinc-400"
          @submit.prevent="triggerSearch"
        >
          <div class="relative flex-grow flex items-center h-full px-3">
            <span
              class="material-symbols-outlined text-zinc-400 dark:text-zinc-500 mr-2 text-[20px]"
              >search</span
            >
            <input
              ref="searchInput"
              v-model="searchQuery"
              class="w-full h-full text-zinc-800 dark:text-zinc-100 text-sm focus:outline-none placeholder-zinc-400 dark:placeholder-zinc-500 bg-transparent"
              placeholder="Я шукаю..."
              type="text"
              @focus="showDropdown = true"
            />
            <!-- Clear Search Button -->
            <button
              v-if="searchQuery"
              type="button"
              class="p-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 mr-1 flex items-center justify-center"
              @click="searchQuery = ''"
            >
              <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
            <!-- Microphone Voice Search Button -->
            <button
              type="button"
              class="p-1 text-zinc-400 hover:text-zinc-600 dark:text-zinc-500 dark:hover:text-zinc-300 flex items-center justify-center"
              @click="triggerVoiceSearch"
            >
              <span class="material-symbols-outlined text-[20px]">mic</span>
            </button>
          </div>
          <!-- Rozetka Green Find Button -->
          <button
            type="submit"
            class="bg-[#00a046] hover:bg-[#00b050] text-white font-bold px-6 h-full text-sm transition-colors border-l border-zinc-200 dark:border-zinc-700 shrink-0"
          >
            Знайти
          </button>
        </form>

        <!-- Dropdown Panel (Rozetka matching auto-completion & popular queries) -->
        <div
          v-if="showDropdown"
          class="absolute left-0 right-0 top-full mt-2 bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-zinc-200 dark:border-zinc-800 z-[110] overflow-hidden p-4 flex flex-col gap-4 animate-in fade-in slide-in-from-top-2 duration-200"
        >
          <!-- Suggestions results -->
          <div v-if="isSearching" class="space-y-2 py-2">
            <div
              class="text-[10px] font-black uppercase text-zinc-400 dark:text-zinc-500 tracking-wider mb-2"
            >
              Шукаємо товари...
            </div>
            <div
              v-for="n in 3"
              :key="n"
              class="flex items-center gap-3 p-2 animate-pulse"
            >
              <div class="w-10 h-10 bg-zinc-150 dark:bg-zinc-800 rounded shrink-0" />
              <div class="flex-grow space-y-1.5">
                <div class="h-2 bg-zinc-200 dark:bg-zinc-700 rounded w-1/4" />
                <div class="h-3 bg-zinc-200 dark:bg-zinc-700 rounded w-3/4" />
              </div>
              <div class="w-12 h-3 bg-zinc-200 dark:bg-zinc-700 rounded shrink-0" />
            </div>
          </div>
          <div v-else-if="filteredProducts.length > 0" class="space-y-1">
            <div
              class="text-[10px] font-black uppercase text-zinc-400 dark:text-zinc-500 tracking-wider mb-2"
            >
              Результати пошуку
            </div>
            <div
              v-for="prod in filteredProducts"
              :key="prod.id"
              class="flex items-center gap-3 p-2 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 rounded-lg cursor-pointer transition-colors group/item"
              @click="selectSearchResult(prod)"
            >
              <div
                class="w-10 h-10 bg-zinc-100 dark:bg-zinc-800 rounded p-1 shrink-0 flex items-center justify-center"
              >
                <img
                  class="max-h-full object-contain"
                  :src="prod.image"
                  :alt="prod.name"
                />
              </div>
              <div class="flex-grow">
                <p
                  class="text-[9px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wide leading-none mb-0.5"
                >
                  {{ prod.category }}
                </p>
                <p
                  class="text-xs text-zinc-700 dark:text-zinc-300 line-clamp-1 group-hover/item:text-primary transition-colors"
                  v-html="highlightMatch(prod.name, searchQuery)"
                />
              </div>
              <span class="text-xs font-bold text-zinc-900 dark:text-zinc-100 shrink-0">{{
                formatPrice(prod.price)
              }}</span>
            </div>
          </div>

          <div
            v-else-if="searchQuery.trim()"
            class="text-center text-xs text-zinc-400 dark:text-zinc-500 py-2"
          >
            Нічого не знайдено для "{{ searchQuery }}".
          </div>

          <!-- Popular Searches (Rozetka Tags Style) -->
          <div class="space-y-2.5">
            <div
              class="text-[10px] font-black uppercase text-zinc-400 dark:text-zinc-500 tracking-wider"
            >
              Популярні запити
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="tag in popularQueries"
                :key="tag"
                type="button"
                class="px-3 py-1.5 border border-zinc-200 dark:border-zinc-800 rounded-lg text-xs font-medium text-zinc-600 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800/80 hover:border-zinc-300 dark:hover:border-zinc-700 transition-colors"
                @click="selectPopularQuery(tag)"
              >
                {{ tag }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Compact Icon Actions -->
      <div class="flex items-center gap-4 md:gap-6 text-white">
        <!-- Notifications -->
        <button
          class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
          title="Notifications"
          @click="
            router.push({ name: 'account', query: { tab: 'notifications' } })
          "
        >
          <span class="material-symbols-outlined text-[24px]"
            >notifications</span
          >
          <span
            v-if="unreadCount > 0"
            class="absolute -top-1.5 -right-2 bg-[#00a046] text-white text-[11px] w-5 h-5 rounded-full flex items-center justify-center font-black leading-none animate-scale-in"
          >
            {{ unreadCount }}
          </span>
        </button>

        <!-- Compare -->
        <button
          class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
          title="Compare"
          @click="router.push({ name: 'account', query: { tab: 'compare' } })"
        >
          <span class="material-symbols-outlined text-[24px]"
            >compare_arrows</span
          >
          <span
            v-if="cartStore.compareCount > 0"
            class="absolute -top-1.5 -right-2 bg-[#00a046] text-white text-[11px] w-5 h-5 rounded-full flex items-center justify-center font-black leading-none animate-scale-in"
          >
            {{ cartStore.compareCount }}
          </span>
        </button>

        <!-- Wishlist -->
        <button
          class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
          title="Wishlist"
          @click="router.push({ name: 'account', query: { tab: 'favorites' } })"
        >
          <span class="material-symbols-outlined text-[24px]">favorite</span>
          <span
            v-if="cartStore.wishlistCount > 0"
            class="absolute -top-1.5 -right-2 bg-[#00a046] text-white text-[11px] w-5 h-5 rounded-full flex items-center justify-center font-black leading-none animate-scale-in"
          >
            {{ cartStore.wishlistCount }}
          </span>
        </button>

        <!-- Cart -->
        <button
          class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
          title="Cart"
          @click="cartStore.openDrawer('cart')"
        >
          <span class="material-symbols-outlined text-[24px]"
            >shopping_cart</span
          >
          <span
            v-if="cartStore.cartCount > 0"
            class="absolute -top-1.5 -right-2 bg-[#00a046] text-white text-[11px] w-5 h-5 rounded-full flex items-center justify-center font-black leading-none animate-scale-in"
          >
            {{ cartStore.cartCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Dropdown Floating Mega Menu Overlay -->
    <div
      v-if="isMegaMenuOpen && categories.length > 0 && activeCat"
      class="mega-menu-wrapper absolute left-0 right-0 top-full bg-[#1c2229] text-zinc-150 border-t border-zinc-800 shadow-2xl z-[120] duration-350 flex animate-in fade-in slide-in-from-top-4 rounded-none"
    >
      <div class="max-w-container-max mx-auto w-full flex min-h-[480px]">
        <!-- Left Sidebar: Category list -->
        <div
          class="w-[230px] xl:w-[250px] border-r border-zinc-800 bg-[#1c2229] p-4 shrink-0"
        >
          <ul class="space-y-1">
            <li
              v-for="cat in categories"
              :key="cat.id"
            >
              <RouterLink
                :to="{ name: 'catalog', query: { category: cat.slug } }"
                :class="
                  activeCat && activeCat.id === cat.id
                    ? 'bg-[#252e37] text-white font-bold'
                    : 'hover:bg-[#252e37]/75 text-zinc-300 hover:text-white'
                "
                class="flex items-center justify-between p-2.5 rounded-none cursor-pointer transition-all duration-150 group/item w-full"
                @mouseenter="selectCategory(cat)"
                @click="isMegaMenuOpen = false"
              >
                <div class="flex items-center gap-3">
                  <span
                    class="material-symbols-outlined text-[19px] transition-transform duration-300"
                    :class="
                      activeCat && activeCat.id === cat.id
                        ? 'scale-110 text-white'
                        : 'group-hover/item:scale-110 text-zinc-400'
                    "
                  >
                    {{ cat.icon }}
                  </span>
                  <span class="text-xs font-semibold">{{ cat.label }}</span>
                </div>
                <span
                  class="material-symbols-outlined text-[14px] text-zinc-650 transition-transform duration-300 group-hover/item:text-zinc-400 group-hover/item:translate-x-0.5"
                >
                  chevron_right
                </span>
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- Center/Right: Sub-categories Columns (Identical to screenshot) -->
        <div
          class="flex-grow p-6 pl-8 bg-[#1c2229] text-white"
        >
          <div
            v-if="activeCat.columns && activeCat.columns.length > 0"
            class="grid grid-cols-4 gap-6"
          >
            <div
              v-for="(col, colIdx) in activeCat.columns"
              :key="colIdx"
              class="space-y-6"
            >
              <div
                v-for="(group, gIdx) in col"
                :key="gIdx"
                class="space-y-2"
              >
                <h4 class="font-extrabold text-[11.5px] uppercase tracking-wider">
                  <RouterLink
                    :to="getGroupRoute(group)"
                    class="text-[#3898ec] hover:underline cursor-pointer"
                    @click="isMegaMenuOpen = false"
                  >
                    {{ group.title }}
                  </RouterLink>
                </h4>
                <ul class="space-y-1.5">
                  <li
                    v-for="(link, lIdx) in group.links"
                    :key="lIdx"
                    class="flex items-center"
                  >
                    <RouterLink
                      :to="getLinkRoute(link)"
                      class="text-zinc-300 hover:text-[#3898ec] text-xs cursor-pointer transition-colors leading-relaxed"
                      @click="isMegaMenuOpen = false"
                    >
                      {{ link.name }}
                    </RouterLink>
                    <span
                      v-if="link.badge"
                      class="text-[#ff4b5f] text-[9px] font-black uppercase tracking-wider ml-1"
                    >
                      {{ link.badge }}
                    </span>
                  </li>
                </ul>
                <RouterLink
                  v-if="group.showMoreSlug"
                  :to="getGroupRoute(group)"
                  class="text-zinc-500 hover:text-zinc-300 text-[11px] font-semibold cursor-pointer underline decoration-dashed decoration-zinc-600 underline-offset-2 mt-1 inline-block"
                  @click="isMegaMenuOpen = false"
                >
                  Дивитися далі →
                </RouterLink>
              </div>
            </div>
          </div>
          <div
            v-else
            class="flex items-center justify-center h-full text-zinc-500"
          >
            <div class="text-center">
              <span class="material-symbols-outlined text-4xl mb-2">category</span>
              <p class="text-xs font-bold">Немає дочірніх розділів.</p>
            </div>
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
