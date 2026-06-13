<template>
  <main class="min-h-screen bg-zinc-50 dark:bg-zinc-950 font-sans pb-16">
    <!-- Breadcrumbs -->
    <nav class="max-w-container-max mx-auto px-4 md:px-8 pt-6 flex items-center flex-wrap gap-1.5 text-xs text-zinc-450 dark:text-zinc-500">
      <router-link
        :to="{ name: 'home' }"
        class="hover:text-[#00a046] transition-colors flex items-center gap-1 font-semibold"
      >
        <span class="material-symbols-outlined text-[15px]">home</span>
        Головна
      </router-link>
      <span class="material-symbols-outlined text-[13px] text-zinc-300 dark:text-zinc-700">chevron_right</span>
      <span class="text-zinc-800 dark:text-zinc-200 font-bold">Блог</span>
    </nav>

    <!-- Page Header & Search -->
    <header class="max-w-container-max mx-auto px-4 md:px-8 pt-6 pb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-zinc-200 dark:border-zinc-800">
        <div class="space-y-2">
          <span class="text-[#00a046] font-extrabold text-xs uppercase tracking-widest">Журнал</span>
          <h1 class="font-extrabold text-3xl md:text-4xl text-zinc-900 dark:text-white tracking-tight leading-tight">
            {{ activeTitle }}
          </h1>
          <p class="text-sm md:text-[15px] text-zinc-500 dark:text-zinc-400 max-w-md">
            Корисні статті, огляди новинок та поради від експертів FilkxTech
          </p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch gap-3 w-full md:w-auto md:min-w-[400px]">
          <UiInput
            v-model="search"
            placeholder="Пошук статей..."
            class="flex-1"
            @update:model-value="onSearchInput"
          >
            <template #prepend>
              <span class="material-symbols-outlined text-[20px] text-zinc-400">search</span>
            </template>
          </UiInput>
          <UiSelect
            v-model="activeCategory"
            :options="categoryOptions"
            class="w-full sm:w-56 shrink-0"
            @update:model-value="updateRouteQuery"
          />
        </div>
      </div>
    </header>

    <div class="max-w-container-max mx-auto px-4 md:px-8">
      <!-- Active filters description / clear all -->
      <div
        v-if="activeCategory || activeTag || search"
        class="flex flex-wrap items-center gap-2 mb-6"
      >
        <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-555 uppercase">Активні фільтри:</span>
        <span
          v-if="activeCategoryName"
          class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-[#00a046] text-xs font-medium border border-[#00a046]/20"
        >
          Категорія: {{ activeCategoryName }}
          <button
            class="hover:text-red-500 focus:outline-none flex items-center"
            @click="selectCategory('')"
          >
            <span class="material-symbols-outlined text-[14px]">close</span>
          </button>
        </span>
        <span
          v-if="activeTagName"
          class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-[#00a046] text-xs font-medium border border-[#00a046]/20"
        >
          Тег: #{{ activeTagName }}
          <button
            class="hover:text-red-500 focus:outline-none flex items-center"
            @click="selectTag(activeTag)"
          >
            <span class="material-symbols-outlined text-[14px]">close</span>
          </button>
        </span>
        <span
          v-if="search"
          class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-[#00a046] text-xs font-medium border border-[#00a046]/20"
        >
          Пошук: "{{ search }}"
          <button
            class="hover:text-red-500 focus:outline-none flex items-center"
            @click="search = ''; updateRouteQuery()"
          >
            <span class="material-symbols-outlined text-[14px]">close</span>
          </button>
        </span>
        <button
          class="text-xs text-red-500 hover:text-red-600 dark:hover:text-red-400 font-bold hover:underline transition-all"
          @click="clearAllFilters"
        >
          Скинути все
        </button>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Main Content -->
        <div class="flex-1 min-w-0">
          <!-- Loading skeleton grid -->
          <div
            v-if="loading"
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
          >
            <div
              v-for="i in 6"
              :key="i"
              class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden animate-pulse"
            >
              <div class="aspect-[16/10] w-full bg-zinc-200 dark:bg-zinc-800" />
              <div class="p-5 space-y-3">
                <div class="h-3 w-1/3 bg-zinc-200 dark:bg-zinc-800 rounded" />
                <div class="h-5 w-full bg-zinc-200 dark:bg-zinc-800 rounded" />
                <div class="h-4 w-5/6 bg-zinc-200 dark:bg-zinc-800 rounded" />
              </div>
            </div>
          </div>

          <!-- Empty state -->
          <div
            v-else-if="posts.length === 0"
            class="text-center py-20 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-8"
          >
            <span class="material-symbols-outlined text-5xl text-zinc-350 dark:text-zinc-650 mb-3">edit_note</span>
            <p class="text-zinc-555 dark:text-zinc-400 font-semibold mb-1">
              Статей не знайдено
            </p>
            <p class="text-xs text-zinc-450 dark:text-zinc-500">
              Спробуйте змінити фільтри або пошуковий запит
            </p>
          </div>

          <!-- Posts grid -->
          <div
            v-else
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 animate-in fade-in duration-300"
          >
            <router-link
              v-for="post in posts"
              :key="post.id"
              :to="{ name: 'blog-post', params: { slug: post.slug } }"
              class="group bg-white dark:bg-zinc-900 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-800 hover:shadow-lg hover:border-zinc-300 dark:hover:border-zinc-700 transition-all duration-300 flex flex-col"
            >
              <!-- Cover image -->
              <div class="aspect-[16/10] w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative">
                <img
                  v-if="post.coverImage"
                  :src="post.coverImage"
                  :alt="post.title?.uk || post.title?.en"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                  loading="lazy"
                >
                <div
                  v-else
                  class="absolute inset-0 flex items-center justify-center bg-zinc-100 dark:bg-zinc-850"
                >
                  <span class="material-symbols-outlined text-5xl text-zinc-300 dark:text-zinc-700">article</span>
                </div>
                <!-- Category badge -->
                <div
                  v-if="post.category"
                  class="absolute top-3 left-3"
                >
                  <span class="px-2.5 py-1 rounded bg-[#00a046]/90 backdrop-blur-sm text-white text-[11px] font-bold uppercase tracking-wide">
                    {{ post.category.name?.uk || post.category.name?.en }}
                  </span>
                </div>
              </div>

              <!-- Content -->
              <div class="p-5 flex-grow flex flex-col gap-2.5">
                <div class="flex items-center gap-2 text-xs text-zinc-400 dark:text-zinc-500 font-semibold">
                  <span>{{ formatDate(post.publishedAt) }}</span>
                  <span>·</span>
                  <span>{{ post.views }} переглядів</span>
                </div>

                <h3 class="font-extrabold text-base text-zinc-900 dark:text-white leading-snug line-clamp-2 group-hover:text-[#00a046] dark:group-hover:text-[#00b050] transition-colors">
                  {{ post.title?.uk || post.title?.en }}
                </h3>

                <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed line-clamp-2 flex-grow">
                  {{ post.excerpt?.uk || post.excerpt?.en }}
                </p>

                <!-- Tags at the bottom of card -->
                <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between mt-auto">
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="tag in post.tags.slice(0, 2)"
                      :key="tag.slug"
                      class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-450 text-[10px] font-bold"
                    >
                      #{{ tag.name?.uk || tag.name?.en }}
                    </span>
                  </div>
                  <span class="text-sm font-extrabold text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] dark:group-hover:text-[#00b050] transition-colors flex items-center gap-1">
                    Читати
                    <span class="material-symbols-outlined text-[15px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                  </span>
                </div>
              </div>
            </router-link>
          </div>

          <!-- Pagination -->
          <div
            v-if="meta.lastPage > 1"
            class="flex justify-center items-center gap-2 mt-10"
          >
            <!-- Prev Page -->
            <button
              :disabled="meta.currentPage === 1"
              class="w-10 h-10 rounded-xl flex items-center justify-center border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-650 dark:text-zinc-400 hover:border-[#00a046] disabled:opacity-50 disabled:pointer-events-none transition-all cursor-pointer"
              @click="fetchPosts(meta.currentPage - 1)"
            >
              <span class="material-symbols-outlined text-[18px]">chevron_left</span>
            </button>

            <!-- Page numbers -->
            <button
              v-for="page in meta.lastPage"
              :key="page"
              :class="[
                'w-10 h-10 rounded-xl text-sm font-bold transition-all border cursor-pointer',
                meta.currentPage === page
                  ? 'bg-[#00a046] text-white border-transparent shadow-lg shadow-[#00a046]/20'
                  : 'bg-white dark:bg-zinc-900 text-zinc-650 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800 hover:border-[#00a046]'
              ]"
              @click="fetchPosts(page)"
            >
              {{ page }}
            </button>

            <!-- Next Page -->
            <button
              :disabled="meta.currentPage === meta.lastPage"
              class="w-10 h-10 rounded-xl flex items-center justify-center border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-650 dark:text-zinc-400 hover:border-[#00a046] disabled:opacity-50 disabled:pointer-events-none transition-all cursor-pointer"
              @click="fetchPosts(meta.currentPage + 1)"
            >
              <span class="material-symbols-outlined text-[18px]">chevron_right</span>
            </button>
          </div>
        </div>

        <!-- Sidebar (Desktop) -->
        <aside class="w-full lg:w-72 shrink-0 space-y-6">
          <!-- Categories block -->
          <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 shadow-sm">
            <h3 class="font-extrabold text-zinc-900 dark:text-white mb-4 text-xs uppercase tracking-wider">
              Категорії
            </h3>
            <div class="space-y-1">
              <button
                :class="[
                  'w-full flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition-all',
                  !activeCategory
                    ? 'bg-emerald-50 dark:bg-emerald-950/20 text-[#00a046] font-extrabold'
                    : 'text-zinc-650 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800'
                ]"
                @click="selectCategory('')"
              >
                <span>Всі статті</span>
                <span class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 rounded-full font-bold">{{ meta.total }}</span>
              </button>
              <button
                v-for="cat in categories"
                :key="cat.slug"
                :class="[
                  'w-full flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition-all',
                  activeCategory === cat.slug
                    ? 'bg-emerald-50 dark:bg-emerald-950/20 text-[#00a046] font-extrabold'
                    : 'text-zinc-650 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800'
                ]"
                @click="selectCategory(cat.slug)"
              >
                <span class="truncate pr-2">{{ cat.name?.uk || cat.name?.en }}</span>
                <span class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2.5 py-0.5 rounded-full font-bold">{{ cat.postsCount }}</span>
              </button>
            </div>
          </div>

          <!-- Tags block -->
          <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 shadow-sm">
            <h3 class="font-extrabold text-zinc-900 dark:text-white mb-4 text-xs uppercase tracking-wider">
              Теги
            </h3>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="tag in tags"
                :key="tag.slug"
                :class="[
                  'px-3 py-1.5 rounded-xl text-xs font-bold transition-all border',
                  activeTag === tag.slug
                    ? 'bg-[#00a046] text-white border-transparent shadow-sm'
                    : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700/50'
                ]"
                @click="selectTag(tag.slug)"
              >
                #{{ tag.name?.uk || tag.name?.en }}
                <span class="text-[10px] opacity-70 ml-0.5">({{ tag.postsCount }})</span>
              </button>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/shared/services/api/apiClient';
import { UiInput, UiSelect } from '@/shared/ui';

const route = useRoute();
const router = useRouter();

const posts = ref([]);
const categories = ref([]);
const tags = ref([]);
const loading = ref(false);
const meta = ref({ currentPage: 1, lastPage: 1, total: 0 });

// Initialize filters from route queries
const search = ref(route.query.search || '');
const activeCategory = ref(route.query.category || '');
const activeTag = ref(route.query.tag || '');

const categoryOptions = computed(() => {
  const list = [{ value: '', label: 'Всі категорії' }];
  categories.value.forEach((cat) => {
    list.push({
      value: cat.slug,
      label: `${cat.name?.uk || cat.name?.en} (${cat.postsCount})`,
    });
  });
  return list;
});

const activeTitle = computed(() => {
  if (activeCategory.value) {
    const cat = categories.value.find((c) => c.slug === activeCategory.value);
    if (cat) return `Блог: ${cat.name?.uk || cat.name?.en}`;
  }
  if (activeTag.value) {
    const t = tags.value.find((tg) => tg.slug === activeTag.value);
    if (t) return `Блог: #${t.name?.uk || t.name?.en}`;
  }
  return 'Блог та огляди техніки';
});

const activeCategoryName = computed(() => {
  const cat = categories.value.find((c) => c.slug === activeCategory.value);
  return cat ? (cat.name?.uk || cat.name?.en) : '';
});

const activeTagName = computed(() => {
  const t = tags.value.find((tg) => tg.slug === activeTag.value);
  return t ? (t.name?.uk || t.name?.en) : '';
});

const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await api.get('/v1/blog/posts', {
      params: {
        page,
        per_page: 9,
        search: search.value || undefined,
        category: activeCategory.value || undefined,
        tag: activeTag.value || undefined,
      },
    });
    posts.value = data.data.data;
    meta.value = data.data.meta;
  } catch (e) {
    console.error('Failed to fetch posts:', e);
  } finally {
    loading.value = false;
  }
};

const fetchSidebar = async () => {
  try {
    const [catsRes, tagsRes] = await Promise.all([
      api.get('/v1/blog/categories'),
      api.get('/v1/blog/tags'),
    ]);
    categories.value = catsRes.data.data;
    tags.value = tagsRes.data.data;
  } catch (e) {
    console.error('Failed to fetch blog sidebar:', e);
  }
};

const updateRouteQuery = () => {
  router.push({
    name: 'blog',
    query: {
      search: search.value || undefined,
      category: activeCategory.value || undefined,
      tag: activeTag.value || undefined,
    },
  });
};

const selectCategory = (slug) => {
  activeCategory.value = slug;
  updateRouteQuery();
};

const selectTag = (slug) => {
  activeTag.value = activeTag.value === slug ? '' : slug;
  updateRouteQuery();
};

const clearAllFilters = () => {
  search.value = '';
  activeCategory.value = '';
  activeTag.value = '';
  updateRouteQuery();
};

const formatDate = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uk-UA', { day: '2-digit', month: 'long', year: 'numeric' });
};

// Sync search input with router queries (debounced)
let debounceTimeout;
const onSearchInput = () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    updateRouteQuery();
  }, 400);
};

// Watch query params and reload posts when URL changes
watch(
  () => route.query,
  (newQuery) => {
    search.value = newQuery.search || '';
    activeCategory.value = newQuery.category || '';
    activeTag.value = newQuery.tag || '';
    fetchPosts(1);
  },
  { deep: true }
);

onMounted(async () => {
  await Promise.all([fetchPosts(Number(route.query.page) || 1), fetchSidebar()]);
});
</script>
