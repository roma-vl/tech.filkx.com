<script setup>
const articles = [
  {
    title: "Як вибрати ідеальний ноутбук для розробника в 2026 році",
    excerpt:
      "Аналіз актуальних процесорів, обсягу оперативної пам'яті та дисплеїв для комфортного кодингу.",
    date: "28 Травня, 2026",
    readTime: "5 хв читання",
    image:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBJQNj0EC0emUkDyzp90oAj9LJPzpKPXT6LWZapnUxdjLR5TAEy9kuHaJr94uG2PzVeehWJTvry4Ee2usgri_YrTONN_qiozc1qDVA0IvjUJcjmfX4tlyXbG7cS55CYK0qdtU_5nNwNG1cvHRVWZ4RDHp1qQpcIlcPkq0kc54-dRRkNH3kiZOkglgTPglzSvbgSmBPllFt0kRkaUl6e5wMCrocXFoM-7JxcSFJb9mOT7tc5df8zDbGD5gV5FgaFM7ihaGDzCu-bE7Q",
  },
  {
    title: "Порівняння ANC навушників: Obsidian X проти конкурентів",
    excerpt:
      "Детальний звуковий тест та вимірювання ефективності активного шумозаглушення в міському середовищі.",
    date: "24 Травня, 2026",
    readTime: "7 хв читання",
    image:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBiUjsdg3UEZ3Invqr34vCkdhNreoHpan9Pb-BGKmaVy0RU0uSQyzTsKkbXAwFc883Y5jTxMbVRUKZoDSaTo3FbaT7XaZnss_re9YT00JhaTBlC6yvueGFeJfKhhh6JIjDtiTNIfRdJjC8ZyTTSHtYB81L85eJ1STBcLutY96W12sDqOctNxTwyq1m0MT7_6PTUKAE858poN7UqRe7nE46hjcjRrp_larxv7sHMDVCn7iT7817fw1OcxPdOG2sWfInGcMEAEIPakTE",
  },
  {
    title: "Екосистема розумного дому: з чого розпочати побудову",
    excerpt:
      "Покрокове керівництво з підбору датчиків, розумних колонок та хабів автоматизації для квартири.",
    date: "18 Травня, 2026",
    readTime: "6 хв читання",
    image:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAG1j0OkwUBTuRJYlZq12iTWSfCx0hTSkVryqbF-FYIDY0p2IODoLkQCjdBzvMUE28jinrTZ-Yhx8ATbKeWyMq2bSKQehQZ5dUfTVBStqMLuWl_dnxdhw_-eZMoiP9_egaelvQQU7gzfXiv-g6KH0W1d7n6iYmoObJXDCEXbrnLagqWXZxOIyeHX_fQAyS84ZvkaGDv8Ld75VMMB-p3JQk_MupRVz9V0REcSykJQllrCavETBkPh8j054bmRUv5No-7faKEr_uRPp0",
  },
];
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-12 font-sans">
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10"
    >
      <div class="space-y-1">
        <h2
          class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight"
        >
          Блог та огляди техніки
        </h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
          Корисні статті, огляди новинок та поради від експертів FilkxTech
        </p>
      </div>
      <a
        class="text-[#00a046] hover:text-[#00b050] font-bold text-sm hover:underline flex items-center gap-1.5 transition-colors"
        href="/blog"
      >
        Читати всі статті
        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
      </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div
        v-for="article in articles"
        :key="article.title"
        class="bg-white dark:bg-zinc-900 rounded-3xl overflow-hidden border border-zinc-100 dark:border-zinc-800 hover:shadow-lg transition-all duration-300 group flex flex-col"
      >
        <!-- Article Image -->
        <div
          class="aspect-[16/10] w-full overflow-hidden bg-zinc-50 dark:bg-zinc-850 relative"
        >
          <img
            :src="article.image"
            :alt="article.title"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          >
          <div
            class="absolute inset-0 bg-black/0 group-hover:bg-black/5 transition-colors"
          />
        </div>

        <!-- Content -->
        <div class="p-6 flex-grow flex flex-col justify-between">
          <div class="space-y-3">
            <!-- Meta details -->
            <div
              class="flex items-center gap-2 text-xs text-zinc-400 font-bold"
            >
              <span>{{ article.date }}</span>
              <span>•</span>
              <span>{{ article.readTime }}</span>
            </div>

            <h3
              class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white group-hover:text-[#00a046] transition-colors leading-snug line-clamp-2"
            >
              {{ article.title }}
            </h3>

            <p
              class="text-zinc-555 dark:text-zinc-400 text-xs md:text-sm leading-relaxed line-clamp-2"
            >
              {{ article.excerpt }}
            </p>
          </div>

          <div class="mt-6 pt-4 border-t border-zinc-100 dark:border-zinc-800">
            <a
              href="/blog"
              class="text-xs md:text-sm font-extrabold text-zinc-800 dark:text-zinc-200 hover:text-[#00a046] dark:hover:text-[#00b050] transition-colors flex items-center gap-1.5"
            >
              Читати статтю
              <span
                class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform"
              >arrow_forward</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped></style>
