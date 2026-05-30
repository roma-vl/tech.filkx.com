<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import { useRouter } from "vue-router";
import { store } from "@/store.js";
import { useAuthStore } from "@/stores/auth.js";
import Dropdown from "@/components/ui/Dropdown.vue";
import axios from "@/services/api";

const router = useRouter();
const authStore = useAuthStore();
const searchInput = ref(null);
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

const searchResults = ref([]);
const isSearching = ref(false);

const mapApiProduct = (prod) => {
  const mainVariant = prod.variants?.[0] || null;
  const price = mainVariant ? parseFloat(mainVariant.price) : 0;

  let image = "";
  if (mainVariant && mainVariant.dimensions && mainVariant.dimensions.images) {
    const primary =
      mainVariant.dimensions.images.find((img) => img.isPrimary) ||
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

let debounceTimeout = null;

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
      const { data } = await axios.get("/v1/catalog/products", {
        params: { search: query },
      });
      if (data && (data.success || data.status === "success")) {
        const productsList = data.data?.data || data.data || [];
        searchResults.value = productsList.map(mapApiProduct);
      }
    } catch (error) {
      console.error("Search query failed:", error);
    } finally {
      isSearching.value = false;
    }
  }, 300);
});
const getCategoryIcon = (slug) => {
  const mapping = {
    phones: "smartphone",
    smartphones: "smartphone",
    laptops: "laptop",
    notebooks: "laptop",
    gaming: "sports_esports",
    audio: "headphones",
    "audio-headphones": "headphones",
    wearables: "watch",
    watches: "watch",
    cameras: "photo_camera",
    tablets: "tablet",
    "smart-home": "home",
    "home-appliances": "home",
  };
  return mapping[slug] || "grid_view";
};

const categories = ref([]);
const activeCat = ref(null);

const selectCategory = (cat) => {
  activeCat.value = cat;
};

const clickCategorySub = (sub) => {
  isMegaMenuOpen.value = false;
  router.push({ name: "catalog", query: { category: sub.slug } });
};

const selectPopularQuery = (query) => {
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

const selectSearchResult = (product) => {
  searchQuery.value = "";
  showDropdown.value = false;
  router.push({ name: "product-detail", params: { id: product.slug } });
};

const formatPrice = (price) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};

const highlightMatch = (name, query) => {
  if (!query.trim()) return name;
  const regex = new RegExp(`(${query.trim()})`, "gi");
  return name.replace(
    regex,
    '<span class="font-extrabold text-[#09090b]">$1</span>',
  );
};

const filteredProducts = computed(() => {
  return searchResults.value;
});

const handleKeydown = (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key === "k") {
    e.preventDefault();
    searchInput.value?.focus();
  }
  if (e.key === "Escape") {
    showDropdown.value = false;
    isMegaMenuOpen.value = false;
  }
};

const handleClickOutside = (e) => {
  const container = searchInput.value?.closest(".search-container");
  if (container && !container.contains(e.target)) {
    showDropdown.value = false;
  }
  const megaWrap = document.querySelector(".mega-menu-wrapper");
  if (
    megaWrap &&
    !megaWrap.contains(e.target) &&
    !e.target.closest(".catalog-btn") &&
    !e.target.closest(".burger-btn")
  ) {
    isMegaMenuOpen.value = false;
  }
};

const toggleCatalog = () => {
  isMegaMenuOpen.value = !isMegaMenuOpen.value;
};

const triggerVoiceSearch = () => {
  store.addToast("Voice search activated. Start speaking...", "info");
};

const unreadCount = ref(0);

const fetchUnreadCount = async () => {
  if (!authStore.isAuthenticated) {
    unreadCount.value = 0;
    return;
  }
  try {
    const { data } = await axios.get("/notifications");
    const notificationsList = data.data?.data || data.data || [];
    unreadCount.value = notificationsList.filter(n => !n.read_at).length;
  } catch (error) {
    console.error("Failed to load notifications for header:", error);
  }
};

watch(() => authStore.isAuthenticated, (newVal) => {
  if (newVal) {
    fetchUnreadCount();
  } else {
    unreadCount.value = 0;
  }
});

onMounted(async () => {
  window.addEventListener("keydown", handleKeydown);
  document.addEventListener("click", handleClickOutside);
  fetchUnreadCount();

  try {
    const { data } = await axios.get("/v1/catalog/categories");
    if (data && (data.success || data.status === "success")) {
      categories.value = data.data.map((cat) => ({
        id: cat.slug,
        label: cat.name?.uk || cat.name?.en || cat.name || "",
        icon: getCategoryIcon(cat.slug),
        sub: (cat.children || []).map((child) => ({
          name: child.name?.uk || child.name?.en || child.name || "",
          slug: child.slug,
          badge: "",
        })),
      }));

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
    <div
      class="max-w-container-max mx-auto h-16 px-4 md:px-8 flex items-center justify-between gap-4"
    >
      <!-- Left: Burger & Logo -->
      <div class="flex items-center gap-3">
        <!-- Burger Button -->
        <button
          class="burger-btn p-1.5 hover:bg-white/10 rounded-lg transition-colors flex items-center justify-center"
          title="Toggle Categories"
          @click="toggleCatalog"
        >
          <span class="material-symbols-outlined text-2xl text-white">menu</span>
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
            FilkxTech
          </span>
        </a>
      </div>

      <!-- Catalog Toggle Button -->
      <button
        :class="
          isMegaMenuOpen
            ? 'bg-white/20 border-white/40'
            : 'border-white/20 hover:bg-white/10 hover:border-white/40'
        "
        class="catalog-btn hidden md:flex items-center gap-2 border px-4 py-2 rounded-lg font-bold text-sm tracking-wide text-white transition-all"
        @click="toggleCatalog"
      >
        <span class="material-symbols-outlined text-[18px]">grid_view</span>
        <span>Каталог</span>
      </button>

      <!-- Center: Rozetka Styled Search Input -->
      <div class="flex-grow max-w-3xl search-container relative z-40">
        <form
          class="flex items-center bg-white rounded-lg overflow-hidden shadow-sm h-10 border border-transparent focus-within:border-zinc-500"
          @submit.prevent="triggerSearch"
        >
          <div class="relative flex-grow flex items-center h-full px-3">
            <span
              class="material-symbols-outlined text-zinc-400 mr-2 text-[20px]"
            >search</span>
            <input
              ref="searchInput"
              v-model="searchQuery"
              class="w-full h-full text-zinc-800 text-sm focus:outline-none placeholder-zinc-400 bg-transparent"
              placeholder="Я шукаю..."
              type="text"
              @focus="showDropdown = true"
            >
            <!-- Clear Search Button -->
            <button
              v-if="searchQuery"
              type="button"
              class="p-1 text-zinc-400 hover:text-zinc-600 mr-1 flex items-center justify-center"
              @click="searchQuery = ''"
            >
              <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
            <!-- Microphone Voice Search Button -->
            <button
              type="button"
              class="p-1 text-zinc-400 hover:text-zinc-600 flex items-center justify-center"
              @click="triggerVoiceSearch"
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
          <div
            v-if="isSearching"
            class="space-y-2 py-2"
          >
            <div
              class="text-[10px] font-black uppercase text-zinc-400 tracking-wider mb-2"
            >
              Шукаємо товари...
            </div>
            <div
              v-for="n in 3"
              :key="n"
              class="flex items-center gap-3 p-2 animate-pulse"
            >
              <div class="w-10 h-10 bg-zinc-150 rounded shrink-0" />
              <div class="flex-grow space-y-1.5">
                <div class="h-2 bg-zinc-200 rounded w-1/4" />
                <div class="h-3 bg-zinc-200 rounded w-3/4" />
              </div>
              <div class="w-12 h-3 bg-zinc-200 rounded shrink-0" />
            </div>
          </div>
          <div
            v-else-if="filteredProducts.length > 0"
            class="space-y-1"
          >
            <div
              class="text-[10px] font-black uppercase text-zinc-400 tracking-wider mb-2"
            >
              Результати пошуку
            </div>
            <div
              v-for="prod in filteredProducts"
              :key="prod.id"
              class="flex items-center gap-3 p-2 hover:bg-zinc-50 rounded-lg cursor-pointer transition-colors group/item"
              @click="selectSearchResult(prod)"
            >
              <div
                class="w-10 h-10 bg-zinc-100 rounded p-1 shrink-0 flex items-center justify-center"
              >
                <img
                  class="max-h-full object-contain"
                  :src="prod.image"
                  :alt="prod.name"
                >
              </div>
              <div class="flex-grow">
                <p
                  class="text-[9px] font-bold text-zinc-400 uppercase tracking-wide leading-none mb-0.5"
                >
                  {{ prod.category }}
                </p>
                <p
                  class="text-xs text-zinc-700 line-clamp-1 group-hover/item:text-primary transition-colors"
                  v-html="highlightMatch(prod.name, searchQuery)"
                />
              </div>
              <span class="text-xs font-bold text-zinc-900 shrink-0">{{
                formatPrice(prod.price)
              }}</span>
            </div>
          </div>

          <div
            v-else-if="searchQuery.trim()"
            class="text-center text-xs text-zinc-400 py-2"
          >
            Нічого не знайдено для "{{ searchQuery }}".
          </div>

          <!-- Popular Searches (Rozetka Tags Style) -->
          <div class="space-y-2.5">
            <div
              class="text-[10px] font-black uppercase text-zinc-400 tracking-wider"
            >
              Популярні запити
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="tag in popularQueries"
                :key="tag"
                type="button"
                class="px-3 py-1.5 border border-zinc-200 rounded-lg text-xs font-medium text-zinc-600 hover:bg-zinc-50 hover:border-zinc-300 transition-colors"
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
        <!-- Account -->
        <Dropdown
          align="right"
          width="60"
        >
          <template #trigger>
            <button
              class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
              title="Account"
            >
              <span class="material-symbols-outlined text-[24px]">person</span>
            </button>
          </template>

          <template #content>
            <template v-if="authStore.isAuthenticated">
              <div
                class="p-4 bg-zinc-50 border-b border-zinc-200 flex items-center gap-3 text-zinc-800"
              >
                <div
                  class="w-10 h-10 rounded-full bg-[#00a046] flex items-center justify-center text-white font-bold"
                >
                  {{
                    authStore.user?.name
                      ? authStore.user.name
                        .split(" ")
                        .map((n) => n[0])
                        .join("")
                        .substring(0, 2)
                        .toUpperCase()
                      : "U"
                  }}
                </div>
                <div>
                  <p class="text-sm font-bold leading-none">
                    {{ authStore.user?.name }}
                  </p>
                  <p class="text-[10px] text-zinc-500 mt-1">
                    {{ authStore.user?.email }}
                  </p>
                </div>
              </div>
              <div class="p-2 flex flex-col gap-1">
                <button
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-zinc-800"
                  @click="
                    router.push({
                      name: 'account',
                      query: { tab: 'dashboard' },
                    })
                  "
                >
                  <span class="material-symbols-outlined text-lg">person</span>
                  <span class="text-xs font-semibold">Кабінет</span>
                </button>
                <button
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-zinc-800"
                  @click="
                    router.push({ name: 'account', query: { tab: 'orders' } })
                  "
                >
                  <span class="material-symbols-outlined text-lg">shopping_bag</span>
                  <span class="text-xs font-semibold">Мої замовлення</span>
                </button>
                <button
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-error"
                  @click="authStore.logout().then(() => router.push('/login'))"
                >
                  <span class="material-symbols-outlined text-lg text-red-500">logout</span>
                  <span class="text-xs font-semibold text-red-600">Вихід</span>
                </button>
              </div>
            </template>
            <template v-else>
              <div class="p-2 flex flex-col gap-1">
                <button
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-zinc-800"
                  @click="router.push('/login')"
                >
                  <span class="material-symbols-outlined text-lg">login</span>
                  <span class="text-xs font-semibold">Увійти</span>
                </button>
                <button
                  class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-zinc-100 transition-colors text-left w-full text-zinc-800"
                  @click="router.push('/register')"
                >
                  <span class="material-symbols-outlined text-lg">person_add</span>
                  <span class="text-xs font-semibold">Реєстрація</span>
                </button>
              </div>
            </template>
          </template>
        </Dropdown>

        <!-- Notifications -->
        <button
          class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
          title="Notifications"
          @click="router.push({ name: 'account', query: { tab: 'notifications' } })"
        >
          <span class="material-symbols-outlined text-[24px]">notifications</span>
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
          <span class="material-symbols-outlined text-[24px]">compare_arrows</span>
          <span
            v-if="store.compareCount > 0"
            class="absolute -top-1.5 -right-2 bg-[#00a046] text-white text-[11px] w-5 h-5 rounded-full flex items-center justify-center font-black leading-none animate-scale-in"
          >
            {{ store.compareCount }}
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
            v-if="store.wishlistCount > 0"
            class="absolute -top-1.5 -right-2 bg-[#00a046] text-white text-[11px] w-5 h-5 rounded-full flex items-center justify-center font-black leading-none animate-scale-in"
          >
            {{ store.wishlistCount }}
          </span>
        </button>

        <!-- Cart -->
        <button
          class="p-1 hover:text-[#00a046] transition-colors relative flex items-center justify-center"
          title="Cart"
          @click="store.openDrawer('cart')"
        >
          <span class="material-symbols-outlined text-[24px]">shopping_cart</span>
          <span
            v-if="store.cartCount > 0"
            class="absolute -top-1.5 -right-2 bg-[#00a046] text-white text-[11px] w-5 h-5 rounded-full flex items-center justify-center font-black leading-none animate-scale-in"
          >
            {{ store.cartCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Dropdown Floating Mega Menu Overlay -->
    <div
      v-if="isMegaMenuOpen && categories.length > 0 && activeCat"
      class="mega-menu-wrapper absolute left-0 right-0 top-full bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl text-zinc-900 dark:text-zinc-100 border-t border-zinc-200 dark:border-zinc-800 shadow-2xl z-[120] duration-350 flex animate-in fade-in slide-in-from-top-4"
    >
      <div class="max-w-container-max mx-auto w-full flex min-h-[480px]">
        <!-- Left Sidebar: Category list -->
        <div
          class="w-1/4 border-r border-zinc-250/60 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-950/20 p-5"
        >
          <ul class="space-y-1.5">
            <li
              v-for="cat in categories"
              :key="cat.id"
              :class="
                activeCat && activeCat.id === cat.id
                  ? 'bg-gradient-to-r from-[#00a046] to-[#00b050] text-white shadow-md shadow-emerald-500/10 font-bold'
                  : 'hover:bg-zinc-100/80 dark:hover:bg-zinc-800/80 text-zinc-700 dark:text-zinc-300'
              "
              class="flex items-center justify-between p-3 rounded-xl cursor-pointer transition-all duration-200 group/item"
              @mouseenter="selectCategory(cat)"
            >
              <div class="flex items-center gap-3">
                <span
                  class="material-symbols-outlined text-[21px] transition-transform duration-300"
                  :class="
                    activeCat && activeCat.id === cat.id
                      ? 'scale-110'
                      : 'group-hover/item:scale-110 text-zinc-400 dark:text-zinc-500'
                  "
                >
                  {{ cat.icon }}
                </span>
                <span class="text-xs uppercase tracking-wider font-extrabold">{{
                  cat.label
                }}</span>
              </div>
              <span
                class="material-symbols-outlined text-[16px] transition-transform duration-300"
                :class="
                  activeCat && activeCat.id === cat.id
                    ? 'translate-x-0.5 opacity-100'
                    : 'opacity-0 group-hover/item:opacity-75 group-hover/item:translate-x-0.5'
                "
              >
                chevron_right
              </span>
            </li>
          </ul>
        </div>

        <!-- Center/Right: Sub-categories Grid & Promo Widget -->
        <div
          class="flex-grow p-8 bg-white dark:bg-zinc-900 flex gap-8 justify-between"
        >
          <!-- Left: Sub-categories grid -->
          <div class="flex-grow flex flex-col justify-between max-w-4xl">
            <div>
              <div
                class="flex items-center gap-3 mb-6 text-[#00a046] dark:text-[#00b050] border-b border-zinc-150 dark:border-zinc-800 pb-3.5"
              >
                <span class="material-symbols-outlined text-[26px] font-bold">{{
                  activeCat.icon
                }}</span>
                <h3 class="text-lg font-black uppercase tracking-wider">
                  {{ activeCat.label }}
                </h3>
              </div>

              <div
                v-if="activeCat.sub && activeCat.sub.length > 0"
                class="grid grid-cols-2 lg:grid-cols-3 gap-5"
              >
                <div
                  v-for="sub in activeCat.sub"
                  :key="sub.slug"
                  class="p-4 border border-zinc-100 dark:border-zinc-800/80 bg-zinc-50/30 dark:bg-zinc-950/20 hover:border-zinc-200 dark:hover:border-zinc-700 hover:bg-white dark:hover:bg-zinc-850 rounded-2xl cursor-pointer shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between h-24 group/sub"
                  @click="clickCategorySub(sub)"
                >
                  <div class="flex justify-between items-start gap-2">
                    <span
                      class="text-xs font-black text-zinc-800 dark:text-zinc-200 group-hover/sub:text-[#00a046] dark:group-hover/sub:text-[#00b050] transition-colors leading-snug"
                    >{{ sub.name }}</span>
                    <span
                      v-if="sub.badge"
                      class="bg-red-500 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-wider shrink-0 animate-pulse"
                    >{{ sub.badge }}</span>
                  </div>
                  <span
                    class="text-[10px] text-zinc-400 dark:text-zinc-500 group-hover/sub:text-[#00a046] dark:group-hover/sub:text-[#00b050] transition-colors flex items-center gap-1 font-bold uppercase tracking-wider"
                  >
                    Переглянути товари
                    <span
                      class="material-symbols-outlined text-[13px] group-hover/sub:translate-x-1 transition-transform"
                    >arrow_forward</span>
                  </span>
                </div>
              </div>
              <div
                v-else
                class="text-center py-12 text-zinc-400 dark:text-zinc-500"
              >
                <span class="material-symbols-outlined text-4xl mb-2">category</span>
                <p class="text-xs font-bold">
                  У цій категорії поки що немає підкатегорій.
                </p>
              </div>
            </div>

            <div
              class="mt-8 pt-4 border-t border-zinc-150 dark:border-zinc-800 flex justify-between items-center"
            >
              <p
                class="text-[10px] text-zinc-400 dark:text-zinc-550 font-bold uppercase tracking-wider"
              >
                Швидка доставка та офіційна гарантія на всі товари
              </p>
              <button
                class="inline-flex items-center gap-2 text-xs font-black text-[#00a046] hover:text-[#00b050] dark:text-[#00b050] dark:hover:text-[#00c060] transition-colors uppercase tracking-widest"
                @click="
                  isMegaMenuOpen = false;
                  router.push({
                    name: 'catalog',
                    query: { category: activeCat.id },
                  });
                "
              >
                Всі товари {{ activeCat.label }}
                <span class="material-symbols-outlined text-sm font-bold">arrow_forward</span>
              </button>
            </div>
          </div>

          <!-- Right Promo Widget (Featured Banner) -->
          <div
            class="w-80 shrink-0 hidden xl:flex flex-col justify-between relative rounded-3xl overflow-hidden bg-gradient-to-br from-zinc-900 to-zinc-950 border border-zinc-800 shadow-lg p-6 text-white group/promo"
          >
            <!-- Glow Effect -->
            <div
              class="absolute -right-16 -top-16 w-36 h-36 rounded-full bg-emerald-500/10 blur-2xl group-hover/promo:bg-emerald-500/20 transition-all duration-500"
            />

            <div class="relative z-10 space-y-4">
              <span
                class="inline-block text-[9px] bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 px-2.5 py-0.5 rounded-full font-black uppercase tracking-widest"
              >
                Пропозиція тижня
              </span>
              <h4
                class="font-extrabold text-lg leading-snug tracking-tight text-white group-hover/promo:text-[#00b050] transition-colors"
              >
                Оновлюйте гаджети на весну зі знижкою до 40%
              </h4>
              <p class="text-[11px] text-zinc-450 leading-relaxed">
                Спеціальні ціни на преміальні смартфони, навушники та ноутбуки.
              </p>
            </div>

            <div class="relative z-10 mt-6 space-y-3">
              <div
                class="flex items-center gap-2 text-xs font-bold text-zinc-300"
              >
                <span class="material-symbols-outlined text-emerald-400 text-sm">check_circle</span>
                <span>Офіційна гарантія</span>
              </div>
              <div
                class="flex items-center gap-2 text-xs font-bold text-zinc-300"
              >
                <span class="material-symbols-outlined text-emerald-400 text-sm">local_shipping</span>
                <span>Безкоштовна доставка</span>
              </div>

              <button
                class="w-full mt-4 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs uppercase tracking-wider py-3 rounded-xl transition-all shadow-md hover:shadow-emerald-500/10 flex items-center justify-center gap-1.5"
                @click="
                  isMegaMenuOpen = false;
                  router.push({ name: 'catalog', query: { is_hot: 'true' } });
                "
              >
                <span>Перейти до акцій</span>
                <span
                  class="material-symbols-outlined text-sm font-bold group-hover/promo:translate-x-0.5 transition-transform"
                >arrow_forward</span>
              </button>
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
