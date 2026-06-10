<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import UiButton from "@/shared/ui/UiButton.vue";

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});

const router = useRouter();

const activeIndex = ref(0);
let intervalId = null;

const slides = [
  {
    badge: "Новинка",
    subtitle: "Ексклюзивне передзамовлення",
    title: "Майбутнє звуку:<br/>Ultra-HD Obsidian X",
    description:
      "Відчуйте 99.9% чистоти звуку з новими бездротовими навушниками Obsidian X. Професійне гібридне шумозаглушення ANC.",
    image:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAeP34U9uxXzWX_WKcHhEi0vWNEKPS1GBvEAaaCn8H_vs759dx6l3vFG3nU5he_83uLeobjrt3wO0ZTEHNr25GfoTOXk5BgJw0aBq4DOBTZMDcXNWSXIr1Br7yzQkfStCKz3Oxa_9E9hwc-MI8TOzXJweyp4dCEYjzmPa4PcOZWK8cZ5xZfFuBzDK2HqrULJkf1Ml3VwbTL28VxUOr2bPKZymnCA8AKm6tLkWZ6qdevZbKINiYZfVXwJTG_-T6bU9QakJ1S-sv7f0Y",
    btnPrimary: "Передзамовити",
    btnSecondary: "Детальніше",
    link: "/product/1",
  },
  {
    badge: "Спеціальна пропозиція",
    subtitle: "Еволюція OLED екранів",
    title: "Ігровий монітор:<br/>Curved Ultra-Wide 144Hz",
    description:
      "Повністю пориньте в ігровий процес завдяки вигнутому екрану з радіусом кривизни 1500R та частотою оновлення 144 Гц.",
    image:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuCVMdErktV3TxMtO3JGMvZ9zS0x0zYbKz1BOjfXYU1LCka8ZihAQRInqqCc_p8i-qxa_HPIqDZ5Kevwr5VKLqyqstWGjEH_WRir7OtrPCpV_H8AvfRt69AI8p1TEbtE5tbqG2zcqQqVYp5pEPBnpsxa17bgXzaPwXHLRwCkbNLOL2MDuK_HzBB7j5pEfGKiX20Mo3vcs919pbLNN6aCAU31C3gWvj4f1OiGSSrW_Xd-ECi9ml_qk2QQhzRko2TzwHZxUspi7SRTQJg",
    btnPrimary: "Переглянути",
    btnSecondary: "Характеристики",
    link: "/product/2",
  },
  {
    badge: "Рекомендовано",
    subtitle: "Розумні гаджети",
    title: "FilkxTech Watch 5:<br/>Активний інтелект",
    description:
      "Відстежуйте показники здоров'я та тренування за допомогою точного GPS та до 7 днів роботи на одному заряді.",
    image:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAG1j0OkwUBTuRJYlZq12iTWSfCx0hTSkVryqbF-FYIDY0p2IODoLkQCjdBzvMUE28jinrTZ-Yhx8ATbKeWyMq2bSKQehQZ5dUfTVBStqMLuWl_dnxdhw_-eZMoiP9_egaelvQQU7gzfXiv-g6KH0W1d7n6iYmoObJXDCEXbrnLagqWXZxOIyeHX_fQAyS84ZvkaGDv8Ld75VMMB-p3JQk_MupRVz9V0REcSykJQllrCavETBkPh8j054bmRUv5No-7faKEr_uRPp0",
    btnPrimary: "Придбати",
    btnSecondary: "Функції",
    link: "/product/3",
  },
];

const nextSlide = () => {
  activeIndex.value = (activeIndex.value + 1) % slides.length;
};

const prevSlide = () => {
  activeIndex.value = (activeIndex.value - 1 + slides.length) % slides.length;
  resetTimer();
};

const setSlide = (index) => {
  activeIndex.value = index;
  resetTimer();
};

const startTimer = () => {
  intervalId = setInterval(nextSlide, 7000);
};

const resetTimer = () => {
  if (intervalId) {
    clearInterval(intervalId);
    startTimer();
  }
};

const getCategoryIcon = (slug) => {
  const s = slug ? slug.toLowerCase() : "";
  if (s.includes("phone") || s.includes("smartfon") || s.includes("telefon")) return "smartphone";
  if (s.includes("laptop") || s.includes("noutbuk") || s.includes("comp") || s.includes("komp")) return "laptop_mac";
  if (s.includes("audio") || s.includes("sound") || s.includes("navushn") || s.includes("headphone")) return "headphones";
  if (s.includes("watch") || s.includes("hodynn") || s.includes("braslet")) return "watch";
  if (s.includes("game") || s.includes("igr") || s.includes("heym") || s.includes("play")) return "sports_esports";
  if (s.includes("camera") || s.includes("foto") || s.includes("kamer")) return "photo_camera";
  if (s.includes("tab") || s.includes("plansh")) return "tablet_mac";
  if (s.includes("tv") || s.includes("telev")) return "tv";
  if (s.includes("home") || s.includes("dim") || s.includes("pobu")) return "home";
  return "grid_view";
};

const getCatName = (cat) => {
  if (!cat) return "";
  if (typeof cat.name === "object") return cat.name?.uk || cat.name?.en || "";
  return cat.name || cat.label || "";
};

const goCat = (slug) => {
  if (slug) router.push({ name: "catalog", query: { category: slug } });
};

onMounted(() => {
  startTimer();
});

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-6 font-sans">
    <div class="flex gap-4 items-stretch">

      <!-- Left: Category Sidebar (hidden on mobile) -->
      <div class="hidden lg:flex flex-col w-[220px] xl:w-[240px] shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden">
        <!-- Green header -->
        <div class="bg-[#00a046] px-4 py-2.5 flex items-center gap-2 shrink-0">
          <span class="material-symbols-outlined text-white text-[17px]">grid_view</span>
          <span class="text-white font-extrabold text-[11px] uppercase tracking-widest">Каталог товарів</span>
        </div>
        <!-- Category list -->
        <nav class="flex-1 overflow-y-auto py-1 custom-scrollbar">
          <template v-if="categories.length > 0">
            <button
              v-for="cat in categories"
              :key="cat.slug || cat.id"
              class="w-full flex items-center gap-2.5 px-3 py-2 text-left hover:bg-[#f0faf6] dark:hover:bg-zinc-800 hover:text-[#00a046] text-zinc-700 dark:text-zinc-300 transition-colors group/catlink"
              @click="goCat(cat.slug)"
            >
              <span class="material-symbols-outlined text-[15px] shrink-0 text-zinc-400 group-hover/catlink:text-[#00a046] transition-colors">
                {{ getCategoryIcon(cat.slug) }}
              </span>
              <span class="text-xs font-semibold line-clamp-1 flex-1">{{ getCatName(cat) }}</span>
              <span class="material-symbols-outlined text-[12px] shrink-0 opacity-0 group-hover/catlink:opacity-100 text-[#00a046] transition-opacity">
                chevron_right
              </span>
            </button>
          </template>
          <template v-else>
            <div v-for="i in 10" :key="i" class="mx-3 my-1 h-7 bg-zinc-100 dark:bg-zinc-800 rounded animate-pulse" />
          </template>
        </nav>
      </div>

      <!-- Right: Main Slider -->
      <div
        class="flex-1 min-w-0 relative rounded-xl overflow-hidden bg-zinc-950 h-[380px] md:h-[480px] flex items-center group shadow-md border border-zinc-800"
      >
        <!-- Slides -->
        <div
          v-for="(slide, index) in slides"
          :key="index"
          :class="[
            'absolute inset-0 w-full h-full transition-all duration-1000 ease-in-out',
            activeIndex === index
              ? 'opacity-100 scale-100 pointer-events-auto z-10'
              : 'opacity-0 scale-105 pointer-events-none z-0',
          ]"
        >
          <!-- Background Image -->
          <img
            class="absolute inset-0 w-full h-full object-cover opacity-60"
            :src="slide.image"
            alt="Hero Slide Image"
          >
          <!-- Gradient Overlay -->
          <div
            class="absolute inset-0 bg-gradient-to-r from-black/90 via-black/45 to-transparent"
          />

          <!-- Content -->
          <div
            class="relative z-10 px-8 md:px-16 max-w-2xl text-white h-full flex flex-col justify-center"
          >
            <div class="flex items-center gap-2 mb-4">
              <span
                class="bg-[#00a046] text-white font-bold uppercase tracking-wider px-3 py-1 rounded text-[10px] shadow-md"
              >
                {{ slide.badge }}
              </span>
              <span
                class="text-zinc-300 font-bold text-xs md:text-sm uppercase tracking-widest"
              >
                • {{ slide.subtitle }}
              </span>
            </div>

            <h1
              class="font-extrabold text-3xl md:text-5xl mb-4 leading-tight text-white"
              v-html="slide.title"
            />
            <p
              class="text-sm md:text-[16px] mb-6 text-zinc-300 max-w-lg leading-relaxed"
            >
              {{ slide.description }}
            </p>

            <div class="flex items-center gap-4">
              <UiButton :to="slide.link" size="md">
                {{ slide.btnPrimary }}
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
              </UiButton>
              <router-link
                :to="slide.link"
                class="bg-white/10 hover:bg-white/20 text-white border border-white/10 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors"
              >
                {{ slide.btnSecondary }}
              </router-link>
            </div>
          </div>
        </div>

        <!-- Prev/Next Navigation Arrows -->
        <button
          class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-sm border border-white/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 active:scale-95"
          aria-label="Попередній слайд"
          @click="prevSlide"
        >
          <span class="material-symbols-outlined text-white text-[20px]">chevron_left</span>
        </button>
        <button
          class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-sm border border-white/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 active:scale-95"
          aria-label="Наступний слайд"
          @click="() => { nextSlide(); resetTimer(); }"
        >
          <span class="material-symbols-outlined text-white text-[20px]">chevron_right</span>
        </button>

        <!-- Slide counter -->
        <div class="absolute bottom-6 right-8 z-20 text-white/40 text-xs font-bold tabular-nums">
          {{ activeIndex + 1 }}&nbsp;/&nbsp;{{ slides.length }}
        </div>

        <!-- Slider Navigation Indicators -->
        <div class="absolute bottom-6 left-8 md:left-16 flex gap-2.5 z-20">
          <button
            v-for="(slide, index) in slides"
            :key="index"
            :class="[
              'h-1.5 rounded-full transition-all duration-500',
              activeIndex === index
                ? 'w-12 bg-[#00a046]'
                : 'w-6 bg-white/30 hover:bg-white/50',
            ]"
            :aria-label="`Слайд ${index + 1}`"
            @click="setSlide(index)"
          />
        </div>
      </div>

    </div>
  </section>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 3px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e4e4e7;
  border-radius: 2px;
}
.dark .custom-scrollbar::-webkit-scrollbar-thumb {
  background: #3f3f46;
}
</style>
