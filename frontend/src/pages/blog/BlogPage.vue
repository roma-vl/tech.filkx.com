<template>
  <main class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-br from-emerald-950 via-gray-900 to-zinc-900 py-20 px-4">
      <div class="absolute inset-0 opacity-20">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-500 rounded-full blur-3xl" />
        <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-teal-500 rounded-full blur-3xl" />
      </div>
      <div class="relative max-w-5xl mx-auto text-center">
        <span class="inline-block px-4 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 text-sm font-semibold mb-5 border border-emerald-500/30">
          📰 Блог FilkxTech
        </span>
        <h1 class="text-4xl md:text-6xl font-black text-white mb-6 leading-tight">
          Новини, огляди<br><span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-400">та поради</span>
        </h1>
        <p class="text-lg text-gray-400 max-w-xl mx-auto">
          Актуальні статті про технології, гаджети та лайфхаки для вашого життя
        </p>
      </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 md:px-8 py-12">
      <div class="flex flex-col lg:flex-row gap-10">
        <!-- Main content -->
        <div class="flex-1 min-w-0">
          <!-- Filters row -->
          <div class="flex flex-col sm:flex-row gap-3 mb-8">
            <input
              v-model="search"
              type="text"
              placeholder="Пошук статей..."
              class="flex-1 px-4 py-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
            >
            <select
              v-model="activeCategory"
              class="px-4 py-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
              <option value="">
                Всі категорії
              </option>
              <option
                v-for="cat in categories"
                :key="cat.slug"
                :value="cat.slug"
              >
                {{ cat.name?.uk || cat.name?.en }}
              </option>
            </select>
          </div>

          <!-- Loading -->
          <div
            v-if="loading"
            class="grid grid-cols-1 md:grid-cols-2 gap-6"
          >
            <div
              v-for="i in 6"
              :key="i"
              class="animate-pulse rounded-3xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 h-80"
            />
          </div>

          <!-- Empty -->
          <div
            v-else-if="posts.length === 0"
            class="text-center py-24"
          >
            <div class="w-20 h-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4">
              <span class="text-3xl">📝</span>
            </div>
            <p class="text-gray-500 dark:text-gray-400">
              Статей не знайдено
            </p>
          </div>

          <!-- Posts grid -->
          <div
            v-else
            class="grid grid-cols-1 md:grid-cols-2 gap-6"
          >
            <RouterLink
              v-for="post in posts"
              :key="post.id"
              :to="{ name: 'blog-post', params: { slug: post.slug } }"
              class="group bg-white dark:bg-zinc-900 rounded-3xl overflow-hidden border border-zinc-100 dark:border-zinc-800 hover:shadow-xl hover:shadow-emerald-500/5 hover:-translate-y-1 transition-all duration-300 flex flex-col"
            >
              <!-- Cover image -->
              <div class="relative h-52 overflow-hidden bg-gradient-to-br from-emerald-900 to-zinc-800">
                <img
                  v-if="post.coverImage"
                  :src="post.coverImage"
                  :alt="post.title?.uk || post.title?.en"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                >
                <div
                  v-else
                  class="absolute inset-0 flex items-center justify-center"
                >
                  <span class="text-5xl">📰</span>
                </div>
                <!-- Category badge -->
                <div
                  v-if="post.category"
                  class="absolute top-4 left-4"
                >
                  <span class="px-3 py-1 rounded-full bg-emerald-500/90 backdrop-blur-sm text-white text-xs font-semibold">
                    {{ post.category.name?.uk || post.category.name?.en }}
                  </span>
                </div>
              </div>

              <!-- Content -->
              <div class="p-5 flex flex-col flex-1">
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-3">
                  <span>{{ formatDate(post.publishedAt) }}</span>
                  <span>·</span>
                  <span>{{ post.views }} переглядів</span>
                </div>

                <h2 class="font-bold text-gray-900 dark:text-white text-base leading-snug mb-2 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors line-clamp-2">
                  {{ post.title?.uk || post.title?.en }}
                </h2>

                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed flex-1 line-clamp-2 mb-4">
                  {{ post.excerpt?.uk || post.excerpt?.en }}
                </p>

                <div class="flex items-center justify-between">
                  <div class="flex flex-wrap gap-1.5">
                    <span
                      v-for="tag in post.tags.slice(0, 2)"
                      :key="tag.slug"
                      class="px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-gray-500 dark:text-gray-400 text-xs"
                    >
                      #{{ tag.name?.uk || tag.name?.en }}
                    </span>
                  </div>
                  <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 group-hover:underline">
                    Читати →
                  </span>
                </div>
              </div>
            </RouterLink>
          </div>

          <!-- Pagination -->
          <div
            v-if="meta.last_page > 1"
            class="flex justify-center gap-2 mt-10"
          >
            <button
              v-for="page in meta.last_page"
              :key="page"
              :class="[
                'w-10 h-10 rounded-xl text-sm font-semibold transition-all',
                meta.current_page === page
                  ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/25'
                  : 'bg-white dark:bg-zinc-900 text-gray-600 dark:text-gray-400 border border-zinc-200 dark:border-zinc-800 hover:border-emerald-400'
              ]"
              @click="fetchPosts(page)"
            >
              {{ page }}
            </button>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="w-full lg:w-72 shrink-0 space-y-6">
          <!-- Categories -->
          <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-100 dark:border-zinc-800 p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4 text-sm uppercase tracking-wider">
              Категорії
            </h3>
            <div class="space-y-1">
              <button
                :class="[
                  'w-full flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all',
                  !activeCategory
                    ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 font-semibold'
                    : 'text-gray-600 dark:text-gray-400 hover:bg-zinc-50 dark:hover:bg-zinc-800'
                ]"
                @click="activeCategory = ''; fetchPosts(1)"
              >
                <span>Всі статті</span>
                <span class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded-full">{{ meta.total }}</span>
              </button>
              <button
                v-for="cat in categories"
                :key="cat.slug"
                :class="[
                  'w-full flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all',
                  activeCategory === cat.slug
                    ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 font-semibold'
                    : 'text-gray-600 dark:text-gray-400 hover:bg-zinc-50 dark:hover:bg-zinc-800'
                ]"
                @click="activeCategory = cat.slug; fetchPosts(1)"
              >
                <span>{{ cat.name?.uk || cat.name?.en }}</span>
                <span class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 rounded-full">{{ cat.postsCount }}</span>
              </button>
            </div>
          </div>

          <!-- Popular tags -->
          <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-100 dark:border-zinc-800 p-5">
            <h3 class="font-bold text-gray-800 dark:text-white mb-4 text-sm uppercase tracking-wider">
              Теги
            </h3>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="tag in tags"
                :key="tag.slug"
                :class="[
                  'px-3 py-1.5 rounded-xl text-xs font-semibold transition-all',
                  activeTag === tag.slug
                    ? 'bg-emerald-600 text-white'
                    : 'bg-zinc-100 dark:bg-zinc-800 text-gray-600 dark:text-gray-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'
                ]"
                @click="activeTag = activeTag === tag.slug ? '' : tag.slug; fetchPosts(1)"
              >
                #{{ tag.name?.uk || tag.name?.en }}
              </button>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import api from '@/shared/services/api/apiClient';

const posts = ref([]);
const categories = ref([]);
const tags = ref([]);
const loading = ref(false);
const meta = ref({ current_page: 1, last_page: 1, total: 0 });

const search = ref('');
const activeCategory = ref('');
const activeTag = ref('');

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
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const fetchSidebar = async () => {
  const [catsRes, tagsRes] = await Promise.all([
    api.get('/v1/blog/categories'),
    api.get('/v1/blog/tags'),
  ]);
  categories.value = catsRes.data.data;
  tags.value = tagsRes.data.data;
};

const formatDate = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uk-UA', { day: '2-digit', month: 'long', year: 'numeric' });
};

let debounce;
watch(search, () => {
  clearTimeout(debounce);
  debounce = setTimeout(() => fetchPosts(1), 300);
});

onMounted(async () => {
  await Promise.all([fetchPosts(), fetchSidebar()]);
});
</script>
