<template>
  <main class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-10">
    <!-- Breadcrumb & Container -->
    <div class="max-w-4xl mx-auto px-4">
      <!-- Loading state -->
      <div
        v-if="loading"
        class="space-y-6"
      >
        <nav class="flex items-center gap-2 text-xs text-gray-400">
          <div class="h-4 w-12 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse" />
          <span>/</span>
          <div class="h-4 w-16 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse" />
        </nav>
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 md:p-10 space-y-6">
          <div class="h-10 bg-zinc-200 dark:bg-zinc-800 rounded-2xl w-2/3 animate-pulse" />
          <div class="h-px bg-zinc-100 dark:bg-zinc-800" />
          <div class="space-y-4 pt-4">
            <div
              v-for="i in 8"
              :key="i"
              class="h-4 bg-zinc-200 dark:bg-zinc-800 rounded animate-pulse"
              :style="{ width: `${100 - (i % 3) * 10}%` }"
            />
          </div>
        </div>
      </div>

      <!-- 404 Page not found -->
      <div
        v-else-if="!page"
        class="py-24 text-center"
      >
        <span class="text-6xl mb-6 block">📄</span>
        <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white mb-3">
          {{ locale === 'uk' ? 'Сторінку не знайдено' : 'Page Not Found' }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-sm mx-auto">
          {{ locale === 'uk' ? 'Можливо, вона була видалена або адреса вказана неправильно.' : 'The page you are looking for might have been removed or the address is incorrect.' }}
        </p>
        <RouterLink
          to="/"
          class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all shadow-md shadow-emerald-500/10"
        >
          {{ locale === 'uk' ? 'На головну' : 'Go to Homepage' }}
        </RouterLink>
      </div>

      <!-- Page content -->
      <div
        v-else
        class="space-y-4"
      >
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-gray-450 dark:text-gray-400 px-1">
          <RouterLink
            to="/"
            class="hover:text-emerald-600 dark:hover:text-emerald-450 transition-colors"
          >
            {{ locale === 'uk' ? 'Головна' : 'Home' }}
          </RouterLink>
          <span>/</span>
          <span class="text-gray-700 dark:text-gray-200 font-semibold truncate max-w-xs">
            {{ page.title?.[locale] || page.title?.uk || page.title?.en }}
          </span>
        </nav>

        <!-- Main Card Container -->
        <article class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-6 md:p-10 shadow-sm relative overflow-hidden">
          <!-- Top emerald accent line -->
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500" />
          
          <header class="mb-6">
            <h1 class="text-2xl md:text-3.5xl font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
              {{ page.title?.[locale] || page.title?.uk || page.title?.en }}
            </h1>
            <div class="flex items-center gap-2 mt-3 text-xs text-gray-400">
              <span>{{ locale === 'uk' ? 'Оновлено' : 'Updated' }}:</span>
              <span>{{ formatDate(page.updatedAt) }}</span>
            </div>
          </header>

          <hr class="border-zinc-100 dark:border-zinc-800 my-6">

          <!-- Page body -->
          <div
            class="page-content prose prose-zinc dark:prose-invert max-w-none text-zinc-800 dark:text-zinc-200"
            v-html="page.content?.[locale] || page.content?.uk || page.content?.en"
          />
        </article>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import { useI18n } from 'vue-i18n';
import api from '@/shared/services/api/apiClient';

const props = defineProps({
  slug: {
    type: String,
    default: '',
  },
});

const route = useRoute();
const { locale } = useI18n();

const page = ref(null);
const loading = ref(true);

const activeSlug = computed(() => props.slug || route.params.slug);

const fetchPage = async () => {
  if (!activeSlug.value) return;
  loading.value = true;
  page.value = null;
  try {
    const { data } = await api.get(`/v1/pages/${activeSlug.value}`);
    page.value = data.data;
  } catch (e) {
    console.error('Failed to fetch static page:', e);
  } finally {
    loading.value = false;
  }
};

const formatDate = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString(locale.value === 'uk' ? 'uk-UA' : 'en-US', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  });
};

watch(activeSlug, fetchPage);
onMounted(fetchPage);
</script>

<style>
/* Prose stylings inside the dynamic content container to match the site aesthetics */
.page-content h1 { @apply text-2xl md:text-3xl font-black mt-8 mb-4 text-zinc-900 dark:text-white; }
.page-content h2 { @apply text-xl md:text-2xl font-bold mt-7 mb-3 pb-2 border-b border-zinc-100 dark:border-zinc-800 text-zinc-900 dark:text-white; }
.page-content h3 { @apply text-lg md:text-xl font-bold mt-6 mb-2 text-zinc-900 dark:text-white; }
.page-content h4 { @apply text-base font-bold mt-4 mb-2 text-zinc-900 dark:text-white; }
.page-content p  { @apply mb-5 leading-relaxed text-sm md:text-base; }
.page-content ul { @apply list-disc pl-6 mb-5 space-y-2 text-sm md:text-base; }
.page-content ol { @apply list-decimal pl-6 mb-5 space-y-2 text-sm md:text-base; }
.page-content blockquote {
  @apply border-l-4 border-emerald-500 pl-5 py-1 italic text-zinc-500 dark:text-zinc-400 my-6 bg-emerald-50/30 dark:bg-emerald-950/10 rounded-r-xl;
}
.page-content pre {
  @apply bg-zinc-900 text-zinc-100 rounded-2xl p-5 font-mono text-sm mb-6 overflow-x-auto;
}
.page-content code {
  @apply bg-zinc-100 dark:bg-zinc-800 text-rose-600 dark:text-rose-400 rounded px-1.5 py-0.5 text-xs font-mono;
}
.page-content pre code { @apply bg-transparent text-inherit px-0 py-0; }
.page-content hr { @apply border-zinc-200 dark:border-zinc-800 my-8; }
.page-content img { @apply rounded-2xl max-w-full my-6 mx-auto shadow-sm; }
.page-content a { @apply text-emerald-600 dark:text-emerald-400 underline hover:opacity-80; }
.page-content table { @apply w-full border-collapse my-6 text-sm; }
.page-content th { @apply bg-zinc-50 dark:bg-zinc-800/50 px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400; }
.page-content td { @apply border border-zinc-100 dark:border-zinc-800 px-4 py-2.5; }
mark { @apply bg-yellow-100 dark:bg-yellow-900/40 rounded px-0.5; }
</style>
