<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "@/shared/services/api/apiClient";

const router = useRouter();
const posts = ref([]);
const loading = ref(true);

const fetchLatestPosts = async () => {
  try {
    const { data } = await api.get("/v1/blog/posts", {
      params: { per_page: 3, sort: "latest" },
    });
    posts.value = data.data.data || [];
  } catch {
    // fallback to static articles if API is unavailable
    posts.value = staticArticles;
  } finally {
    loading.value = false;
  }
};

const formatDate = (d) => {
  if (!d) return "";
  return new Date(d).toLocaleDateString("uk-UA", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
};

const getTitle = (post) =>
  post.title?.uk || post.title?.en || post.title || "";

const getExcerpt = (post) =>
  post.excerpt?.uk || post.excerpt?.en || post.excerpt || "";

const staticArticles = [
  {
    id: 1,
    slug: "",
    coverImage:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBJQNj0EC0emUkDyzp90oAj9LJPzpKPXT6LWZapnUxdjLR5TAEy9kuHaJr94uG2PzVeehWJTvry4Ee2usgri_YrTONN_qiozc1qDVA0IvjUJcjmfX4tlyXbG7cS55CYK0qdtU_5nNwNG1cvHRVWZ4RDHp1qQpcIlcPkq0kc54-dRRkNH3kiZOkglgTPglzSvbgSmBPllFt0kRkaUl6e5wMCrocXFoM-7JxcSFJb9mOT7tc5df8zDbGD5gV5FgaFM7ihaGDzCu-bE7Q",
    title: { uk: "Як вибрати ідеальний ноутбук для розробника в 2026 році" },
    excerpt: {
      uk: "Аналіз актуальних процесорів, обсягу оперативної пам'яті та дисплеїв для комфортного кодингу.",
    },
    publishedAt: "2026-05-28",
    readTime: "5 хв",
    category: { name: { uk: "Ноутбуки" } },
  },
  {
    id: 2,
    slug: "",
    coverImage:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBiUjsdg3UEZ3Invqr34vCkdhNreoHpan9Pb-BGKmaVy0RU0uSQyzTsKkbXAwFc883Y5jTxMbVRUKZoDSaTo3FbaT7XaZnss_re9YT00JhaTBlC6yvueGFeJfKhhh6JIjDtiTNIfRdJjC8ZyTTSHtYB81L85eJ1STBcLutY96W12sDqOctNxTwyq1m0MT7_6PTUKAE858poN7UqRe7nE46hjcjRrp_larxv7sHMDVCn7iT7817fw1OcxPdOG2sWfInGcMEAEIPakTE",
    title: { uk: "Порівняння ANC навушників: Obsidian X проти конкурентів" },
    excerpt: {
      uk: "Детальний звуковий тест та вимірювання ефективності активного шумозаглушення в міському середовищі.",
    },
    publishedAt: "2026-05-24",
    readTime: "7 хв",
    category: { name: { uk: "Аудіо" } },
  },
  {
    id: 3,
    slug: "",
    coverImage:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAG1j0OkwUBTuRJYlZq12iTWSfCx0hTSkVryqbF-FYIDY0p2IODoLkQCjdBzvMUE28jinrTZ-Yhx8ATbKeWyMq2bSKQehQZ5dUfTVBStqMLuWl_dnxdhw_-eZMoiP9_egaelvQQU7gzfXiv-g6KH0W1d7n6iYmoObJXDCEXbrnLagqWXZxOIyeHX_fQAyS84ZvkaGDv8Ld75VMMB-p3JQk_MupRVz9V0REcSykJQllrCavETBkPh8j054bmRUv5No-7faKEr_uRPp0",
    title: { uk: "Екосистема розумного дому: з чого розпочати побудову" },
    excerpt: {
      uk: "Покрокове керівництво з підбору датчиків, розумних колонок та хабів автоматизації для квартири.",
    },
    publishedAt: "2026-05-18",
    readTime: "6 хв",
    category: { name: { uk: "Смарт Дім" } },
  },
];

onMounted(fetchLatestPosts);
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-14 font-sans">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
      <div class="space-y-2">
        <span class="text-[#00a046] font-extrabold text-xs uppercase tracking-widest">Журнал</span>
        <h2
          class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight leading-tight"
        >
          Блог та огляди техніки
        </h2>
        <p class="text-sm md:text-[15px] text-zinc-500 dark:text-zinc-400 max-w-md">
          Корисні статті, огляди новинок та поради від експертів FilkxTech
        </p>
      </div>
      <a
        class="inline-flex items-center gap-1.5 text-sm font-bold text-[#00a046] hover:text-[#00b050] transition-colors shrink-0"
        href="/blog"
      >
        Читати всі статті
        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
      </a>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="i in 3"
        :key="i"
        class="bg-zinc-100 dark:bg-zinc-800 rounded-xl overflow-hidden animate-pulse"
      >
        <div class="aspect-[16/10] w-full bg-zinc-200 dark:bg-zinc-700" />
        <div class="p-5 space-y-3">
          <div class="h-3 w-1/3 bg-zinc-200 dark:bg-zinc-700 rounded" />
          <div class="h-5 w-full bg-zinc-200 dark:bg-zinc-700 rounded" />
          <div class="h-4 w-5/6 bg-zinc-200 dark:bg-zinc-700 rounded" />
        </div>
      </div>
    </div>

    <!-- Posts grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <component
        :is="post.slug ? 'RouterLink' : 'a'"
        v-for="post in posts"
        :key="post.id"
        v-bind="post.slug ? { to: { name: 'blog-post', params: { slug: post.slug } } } : { href: '/blog' }"
        class="group bg-white dark:bg-zinc-900 rounded-xl overflow-hidden border border-zinc-100 dark:border-zinc-800 hover:shadow-lg hover:border-zinc-200 dark:hover:border-zinc-700 transition-all duration-300 flex flex-col"
      >
        <!-- Image -->
        <div class="aspect-[16/10] w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative">
          <img
            v-if="post.coverImage"
            :src="post.coverImage"
            :alt="getTitle(post)"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />
          <div v-else class="absolute inset-0 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800">
            <span class="material-symbols-outlined text-5xl text-zinc-300 dark:text-zinc-600">article</span>
          </div>

          <!-- Category badge -->
          <div v-if="post.category" class="absolute top-3 left-3">
            <span
              class="px-2.5 py-1 rounded bg-[#00a046]/90 backdrop-blur-sm text-white text-[11px] font-bold uppercase tracking-wide"
            >
              {{ post.category.name?.uk || post.category.name?.en }}
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-5 flex-grow flex flex-col gap-2.5">
          <!-- Meta -->
          <div class="flex items-center gap-2 text-xs text-zinc-400 dark:text-zinc-500 font-semibold">
            <span>{{ formatDate(post.publishedAt) }}</span>
            <span>·</span>
            <span>{{ post.readTime || (post.views ? post.views + " переглядів" : "5 хв") }}</span>
          </div>

          <!-- Title -->
          <h3
            class="font-extrabold text-[15px] md:text-base text-zinc-900 dark:text-white leading-snug line-clamp-2 group-hover:text-[#00a046] dark:group-hover:text-[#00b050] transition-colors"
          >
            {{ getTitle(post) }}
          </h3>

          <!-- Excerpt -->
          <p
            class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed line-clamp-2 flex-grow"
          >
            {{ getExcerpt(post) }}
          </p>

          <!-- Footer -->
          <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 mt-auto">
            <span
              class="text-sm font-extrabold text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] dark:group-hover:text-[#00b050] transition-colors flex items-center gap-1.5"
            >
              Читати статтю
              <span
                class="material-symbols-outlined text-[15px] group-hover:translate-x-1 transition-transform"
              >arrow_forward</span>
            </span>
          </div>
        </div>
      </component>
    </div>
  </section>
</template>

<style scoped></style>
