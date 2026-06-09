<script setup>
const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});

const getCategoryStyle = (slug) => {
  const s = slug ? slug.toLowerCase() : "";
  if (s.includes("phone") || s.includes("smartfon") || s.includes("telefon")) {
    return { icon: "smartphone", color: "from-blue-500/10 to-indigo-500/5", iconColor: "text-blue-500" };
  }
  if (s.includes("laptop") || s.includes("noutbuk") || s.includes("comp") || s.includes("komp")) {
    return { icon: "laptop_mac", color: "from-amber-500/10 to-orange-500/5", iconColor: "text-amber-500" };
  }
  if (s.includes("audio") || s.includes("sound") || s.includes("navushn") || s.includes("headphone")) {
    return { icon: "headphones", color: "from-purple-500/10 to-pink-500/5", iconColor: "text-purple-500" };
  }
  if (s.includes("watch") || s.includes("hodynn") || s.includes("braslet")) {
    return { icon: "watch", color: "from-emerald-500/10 to-teal-500/5", iconColor: "text-emerald-500" };
  }
  if (s.includes("game") || s.includes("igr") || s.includes("heym") || s.includes("play")) {
    return { icon: "sports_esports", color: "from-red-500/10 to-rose-500/5", iconColor: "text-red-500" };
  }
  if (s.includes("camera") || s.includes("foto") || s.includes("kamery")) {
    return { icon: "photo_camera", color: "from-cyan-500/10 to-blue-500/5", iconColor: "text-cyan-500" };
  }
  if (s.includes("tab") || s.includes("plansh")) {
    return { icon: "tablet_mac", color: "from-violet-500/10 to-purple-500/5", iconColor: "text-violet-500" };
  }
  if (s.includes("mem") || s.includes("proc") || s.includes("komplekt") || s.includes("part") || s.includes("detal")) {
    return { icon: "memory", color: "from-teal-500/10 to-green-500/5", iconColor: "text-teal-500" };
  }
  return { icon: "grid_view", color: "from-zinc-500/10 to-neutral-500/5", iconColor: "text-zinc-500" };
};
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-12 font-sans">
    <div class="flex items-center justify-between mb-8">
      <div class="space-y-1.5">
        <span class="text-[#00a046] font-extrabold text-xs uppercase tracking-widest">Каталог</span>
        <h2
          class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight"
        >
          Популярні категорії
        </h2>
        <p class="text-sm md:text-[15px] text-zinc-500 dark:text-zinc-400">
          Швидкий перехід до потрібних розділів нашого каталогу
        </p>
      </div>
      <a
        class="text-[#00a046] hover:text-[#00b050] font-bold text-sm hover:underline flex items-center gap-1.5 transition-colors shrink-0"
        href="/catalog"
      >
        Усі категорії
        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
      </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
      <a
        v-for="cat in categories"
        :key="cat.id"
        :href="`/catalog?category=${cat.slug}`"
        class="group flex flex-col items-center p-4 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl hover:shadow-md hover:border-zinc-200 dark:hover:border-zinc-700 transition-all duration-300 hover:-translate-y-0.5 relative overflow-hidden"
      >
        <!-- Background Gradient on Hover -->
        <div
          class="absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-100 transition-opacity duration-300"
          :class="getCategoryStyle(cat.slug).color"
        />

        <!-- Icon -->
        <div
          class="relative z-10 w-16 h-16 rounded-lg bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center mb-2.5 group-hover:scale-110 transition-transform duration-300"
        >
          <span
            class="material-symbols-outlined text-3xl transition-colors duration-300"
            :class="getCategoryStyle(cat.slug).iconColor"
          >
            {{ getCategoryStyle(cat.slug).icon }}
          </span>
        </div>

        <!-- Name -->
        <span
          class="relative z-10 font-bold text-xs md:text-sm text-zinc-700 dark:text-zinc-200 group-hover:text-zinc-900 dark:group-hover:text-white transition-colors text-center line-clamp-2 leading-snug"
        >
          {{ cat.name?.uk || cat.name?.en || cat.name }}
        </span>
      </a>
    </div>
  </section>
</template>

<style scoped></style>
