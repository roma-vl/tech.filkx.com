<script setup>
import { ref, onMounted, onServerPrefetch } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import api from "@/shared/services/api/apiClient";
import UiSectionLink from "@/shared/ui/UiSectionLink.vue";

const router = useRouter();
const { t, locale } = useI18n();
const posts = ref([]);
const loading = ref(true);

const loadFailed = ref(false);

const fetchLatestPosts = async () => {
  try {
    const { data } = await api.get("/v1/blog/posts", {
      params: { per_page: 3, sort: "latest" },
    });
    posts.value = data.data.data || [];
  } catch {
    loadFailed.value = true;
  } finally {
    loading.value = false;
  }
};

const formatDate = (d) => {
  if (!d) return "";
  return new Date(d).toLocaleDateString(
    locale.value === "uk" ? "uk-UA" : "en-US",
    {
      day: "2-digit",
      month: "long",
      year: "numeric",
    },
  );
};

const getTitle = (post) =>
  post.title?.[locale.value] ||
  post.title?.uk ||
  post.title?.en ||
  post.title ||
  "";

const getExcerpt = (post) =>
  post.excerpt?.[locale.value] ||
  post.excerpt?.uk ||
  post.excerpt?.en ||
  post.excerpt ||
  "";

onMounted(fetchLatestPosts);
// Prerendering has no DOM, so onMounted never runs — fetch here so the
// static build captures real teaser content.
onServerPrefetch(fetchLatestPosts);
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-14 font-sans">
    <!-- Header -->
    <div
      class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10"
    >
      <div class="space-y-2">
        <span
          class="text-[#00a046] font-extrabold text-xs uppercase tracking-widest"
          >{{ t("home.blog.label") }}</span
        >
        <h2
          class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight leading-tight"
        >
          {{ t("home.blog.title") }}
        </h2>
        <p
          class="text-sm md:text-[15px] text-zinc-500 dark:text-zinc-400 max-w-md"
        >
          {{ t("home.blog.description") }}
        </p>
      </div>
      <UiSectionLink :to="{ name: 'blog' }">
        {{ t("home.blog.readAll") }}
      </UiSectionLink>
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

    <!-- Empty / failed state -->
    <div
      v-else-if="posts.length === 0"
      class="flex flex-col items-center justify-center py-16 text-zinc-500 bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800"
    >
      <span
        class="material-symbols-outlined text-5xl mb-3 text-zinc-400 dark:text-zinc-650"
        >article</span
      >
      <p class="text-sm font-bold">
        {{ loadFailed ? t("home.blog.loadFailed") : t("home.blog.empty") }}
      </p>
    </div>

    <!-- Posts grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <router-link
        v-for="post in posts"
        :key="post.id"
        :to="
          post.slug
            ? { name: 'blog-post', params: { slug: post.slug } }
            : { name: 'blog' }
        "
        class="group bg-white dark:bg-zinc-900 rounded-xl overflow-hidden border border-zinc-100 dark:border-zinc-800 hover:shadow-lg hover:border-zinc-200 dark:hover:border-zinc-700 transition-all duration-300 flex flex-col"
      >
        <!-- Image -->
        <div
          class="aspect-[16/10] w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800 relative"
        >
          <img
            v-if="post.coverImage"
            :src="post.coverImage"
            :alt="getTitle(post)"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          />
          <div
            v-else
            class="absolute inset-0 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800"
          >
            <span
              class="material-symbols-outlined text-5xl text-zinc-300 dark:text-zinc-600"
              >article</span
            >
          </div>

          <!-- Category badge -->
          <div v-if="post.category" class="absolute top-3 left-3">
            <span
              class="px-2.5 py-1 rounded bg-[#00a046]/90 backdrop-blur-sm text-white text-[11px] font-bold uppercase tracking-wide"
            >
              {{
                post.category.name?.[locale] ||
                post.category.name?.uk ||
                post.category.name?.en
              }}
            </span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-5 flex-grow flex flex-col gap-2.5">
          <!-- Meta -->
          <div
            class="flex items-center gap-2 text-xs text-zinc-400 dark:text-zinc-500 font-semibold"
          >
            <span>{{ formatDate(post.publishedAt) }}</span>
            <span>·</span>
            <span>{{
              post.readTime ||
              (post.views
                ? t("home.blog.viewsCount", { count: post.views })
                : t("home.blog.defaultReadTime"))
            }}</span>
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
          <div
            class="pt-3 border-t border-zinc-100 dark:border-zinc-800 mt-auto"
          >
            <span
              class="text-sm font-extrabold text-zinc-700 dark:text-zinc-300 group-hover:text-[#00a046] dark:group-hover:text-[#00b050] transition-colors flex items-center gap-1.5"
            >
              {{ t("home.blog.readArticle") }}
              <span
                class="material-symbols-outlined text-[15px] group-hover:translate-x-1 transition-transform"
                >arrow_forward</span
              >
            </span>
          </div>
        </div>
      </router-link>
    </div>
  </section>
</template>

<style scoped></style>
