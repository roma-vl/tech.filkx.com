<template>
  <main class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <!-- Loading state -->
    <div v-if="loading" class="max-w-4xl mx-auto px-4 py-20">
      <div class="animate-pulse space-y-6">
        <div class="h-72 bg-zinc-200 dark:bg-zinc-800 rounded-3xl" />
        <div class="h-10 bg-zinc-200 dark:bg-zinc-800 rounded-2xl w-3/4" />
        <div class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded w-1/2" />
        <div class="space-y-3">
          <div v-for="i in 6" :key="i" class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded" />
        </div>
      </div>
    </div>

    <!-- 404 -->
    <div v-else-if="!post" class="max-w-4xl mx-auto px-4 py-32 text-center">
      <span class="text-6xl mb-4 block">📄</span>
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Статтю не знайдено</h1>
      <RouterLink to="/blog" class="text-emerald-600 hover:underline">← Повернутися до блогу</RouterLink>
    </div>

    <!-- Post content -->
    <article v-else class="pb-16">
      <!-- Cover -->
      <div class="relative h-72 md:h-96 bg-gradient-to-br from-emerald-950 via-zinc-900 to-zinc-950 overflow-hidden">
        <img
          v-if="post.coverImage"
          :src="post.coverImage"
          :alt="post.title?.uk || post.title?.en"
          class="absolute inset-0 w-full h-full object-cover opacity-60"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-transparent" />
        <div class="relative max-w-4xl mx-auto px-4 h-full flex flex-col justify-end pb-8">
          <!-- Category -->
          <div v-if="post.category" class="mb-3">
            <RouterLink
              :to="{ name: 'blog', query: { category: post.category.slug } }"
              class="inline-block px-4 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 text-sm font-semibold border border-emerald-500/30 hover:bg-emerald-500/30 transition-colors"
            >
              {{ post.category.name?.uk || post.category.name?.en }}
            </RouterLink>
          </div>
          <h1 class="text-2xl md:text-4xl font-black text-white leading-tight">
            {{ post.title?.uk || post.title?.en }}
          </h1>
        </div>
      </div>

      <!-- Meta bar -->
      <div class="bg-white dark:bg-zinc-900 border-b border-zinc-100 dark:border-zinc-800">
        <div class="max-w-4xl mx-auto px-4 py-4 flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
          <div v-if="post.author" class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-emerald-600 flex items-center justify-center text-white text-xs font-bold">
              {{ post.author.name?.charAt(0) }}
            </div>
            <span>{{ post.author.name }}</span>
          </div>
          <span v-if="post.publishedAt">{{ formatDate(post.publishedAt) }}</span>
          <span class="flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            {{ post.views }} переглядів
          </span>
          <!-- Tags -->
          <div class="flex flex-wrap gap-1.5 ml-auto">
            <RouterLink
              v-for="tag in post.tags"
              :key="tag.slug"
              :to="{ name: 'blog', query: { tag: tag.slug } }"
              class="px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:text-emerald-600 dark:hover:text-emerald-400 text-xs transition-colors"
            >
              #{{ tag.name?.uk || tag.name?.en }}
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Breadcrumb -->
      <div class="max-w-4xl mx-auto px-4 py-4">
        <nav class="flex items-center gap-2 text-xs text-gray-400">
          <RouterLink to="/" class="hover:text-emerald-500 transition-colors">Головна</RouterLink>
          <span>/</span>
          <RouterLink to="/blog" class="hover:text-emerald-500 transition-colors">Блог</RouterLink>
          <span>/</span>
          <span class="text-gray-600 dark:text-gray-300 truncate max-w-xs">{{ post.title?.uk || post.title?.en }}</span>
        </nav>
      </div>

      <!-- Content -->
      <div class="max-w-4xl mx-auto px-4">
        <!-- Excerpt -->
        <p v-if="post.excerpt?.uk || post.excerpt?.en" class="text-lg text-gray-500 dark:text-gray-400 leading-relaxed mb-8 font-medium border-l-4 border-emerald-500 pl-4">
          {{ post.excerpt?.uk || post.excerpt?.en }}
        </p>

        <!-- Article body -->
        <div
          v-html="post.content?.uk || post.content?.en"
          class="blog-content prose prose-lg dark:prose-invert max-w-none text-gray-800 dark:text-gray-200"
        />

        <!-- Bottom navigation -->
        <div class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
          <RouterLink to="/blog" class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
            ← Всі статті
          </RouterLink>
        </div>
      </div>
    </article>
  </main>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import api from '@/services/api';

const route = useRoute();
const post = ref(null);
const loading = ref(true);

const fetchPost = async () => {
  loading.value = true;
  post.value = null;
  try {
    const { data } = await api.get(`/v1/blog/posts/${route.params.slug}`);
    post.value = data.data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const formatDate = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uk-UA', { day: '2-digit', month: 'long', year: 'numeric' });
};

watch(() => route.params.slug, fetchPost);
onMounted(fetchPost);
</script>

<style>
.blog-content h1 { @apply text-3xl font-black mt-8 mb-4; }
.blog-content h2 { @apply text-2xl font-bold mt-7 mb-3 pb-2 border-b border-zinc-200 dark:border-zinc-800; }
.blog-content h3 { @apply text-xl font-bold mt-6 mb-2; }
.blog-content p  { @apply mb-5 leading-relaxed; }
.blog-content ul { @apply list-disc pl-6 mb-5 space-y-2; }
.blog-content ol { @apply list-decimal pl-6 mb-5 space-y-2; }
.blog-content blockquote {
  @apply border-l-4 border-emerald-500 pl-5 py-1 italic text-gray-500 dark:text-gray-400 my-6 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-r-xl;
}
.blog-content pre {
  @apply bg-zinc-900 text-zinc-100 rounded-2xl p-5 font-mono text-sm mb-6 overflow-x-auto;
}
.blog-content code {
  @apply bg-zinc-100 dark:bg-zinc-800 text-rose-600 dark:text-rose-400 rounded px-1.5 py-0.5 text-sm font-mono;
}
.blog-content pre code { @apply bg-transparent text-inherit px-0 py-0; }
.blog-content hr { @apply border-zinc-200 dark:border-zinc-800 my-8; }
.blog-content img { @apply rounded-2xl max-w-full my-6 mx-auto shadow-lg; }
.blog-content a { @apply text-emerald-600 dark:text-emerald-400 underline hover:opacity-80; }
.blog-content table { @apply w-full border-collapse my-6; }
.blog-content th { @apply bg-zinc-100 dark:bg-zinc-800 px-4 py-2 text-left text-sm font-semibold; }
.blog-content td { @apply border border-zinc-200 dark:border-zinc-700 px-4 py-2 text-sm; }
mark { @apply bg-yellow-200 dark:bg-yellow-800/60 rounded px-0.5; }
</style>
