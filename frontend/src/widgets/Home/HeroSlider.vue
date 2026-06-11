<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { useRouter } from "vue-router";
import UiButton from "@/shared/ui/UiButton.vue";
import { mapDbCategoriesToMenu } from "@/shared/utils/categoryMapper";

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});

const router = useRouter();
const activeIndex = ref(0);
const hoveredCat = ref(null);
let intervalId = null;

const mappedCategories = computed(() => mapDbCategoriesToMenu(props.categories));

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
  if (intervalId) { clearInterval(intervalId); startTimer(); }
};

const handleLinkClick = (link) => {
  hoveredCat.value = null;
  if (link.slug) {
    router.push({
      name: "catalog",
      query: { category: link.slug, q: link.name }
    });
  } else {
    router.push({
      name: "catalog",
      query: { q: link.name }
    });
  }
};

const handleGroupTitleClick = (group) => {
  hoveredCat.value = null;
  router.push({
    name: "catalog",
    query: { category: group.showMoreSlug || "phones", q: group.title }
  });
};

onMounted(() => { startTimer(); });
onUnmounted(() => { if (intervalId) clearInterval(intervalId); });
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-5 font-sans">
    <div
      class="flex items-stretch overflow-hidden shadow-xl rounded-none relative"
      @mouseleave="hoveredCat = null"
    >

      <!-- ── Left: dark category sidebar (desktop only) ── -->
      <div class="hidden lg:flex flex-col w-[230px] xl:w-[250px] shrink-0 bg-[#1c2229] relative z-20 border-r border-zinc-800">
        <!-- Category links -->
        <nav class="flex-1 overflow-y-auto cat-scroll bg-[#1c2229]">
          <template v-if="mappedCategories.length > 0">
            <button
              v-for="cat in mappedCategories"
              :key="cat.id"
              class="w-full flex items-center justify-between px-4 py-2.5 text-left transition-all duration-150 group/cl rounded-none"
              :class="
                hoveredCat && hoveredCat.id === cat.id
                  ? 'bg-[#252e37] text-white font-bold'
                  : 'text-zinc-300 hover:text-white hover:bg-[#252e37]/75'
              "
              @mouseenter="hoveredCat = cat"
              @click="router.push({ name: 'catalog', query: { category: cat.slug } })"
            >
              <div class="flex items-center gap-3">
                <span
                  class="material-symbols-outlined text-[19px] shrink-0 transition-colors"
                  :class="hoveredCat && hoveredCat.id === cat.id ? 'text-white' : 'text-zinc-400 group-hover/cl:text-white'"
                >
                  {{ cat.icon }}
                </span>
                <span
                  class="text-xs font-semibold flex-1 line-clamp-1 transition-colors"
                >
                  {{ cat.label }}
                </span>
              </div>
              <span
                class="material-symbols-outlined text-[14px] shrink-0 text-zinc-650 group-hover/cl:text-zinc-400 group-hover/cl:translate-x-0.5 transition-all"
              >
                chevron_right
              </span>
            </button>
          </template>
          <template v-else>
            <div v-for="i in 11" :key="i" class="mx-4 my-2.5 h-6 bg-white/10 rounded-none animate-pulse" />
          </template>
        </nav>
      </div>

      <!-- ── Right: hero slider ── -->
      <div
        class="flex-1 min-w-0 relative bg-zinc-950 h-[380px] md:h-[480px] flex items-center group z-10"
        @mouseenter="hoveredCat = null"
      >

        <!-- Slides -->
        <div
          v-for="(slide, index) in slides"
          :key="index"
          :class="[
            'absolute inset-0 transition-all duration-1000 ease-in-out',
            activeIndex === index
              ? 'opacity-100 scale-100 pointer-events-auto z-10'
              : 'opacity-0 scale-105 pointer-events-none z-0',
          ]"
        >
          <img class="absolute inset-0 w-full h-full object-cover opacity-60" :src="slide.image" alt="" />
          <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/40 to-transparent" />

          <div class="relative z-10 px-8 md:px-14 max-w-2xl text-white h-full flex flex-col justify-center">
            <div class="flex items-center gap-2 mb-3">
              <span class="bg-[#00a046] text-white font-bold uppercase tracking-wider px-3 py-1 rounded-none text-[10px]">
                {{ slide.badge }}
              </span>
              <span class="text-zinc-300 font-bold text-xs uppercase tracking-widest">• {{ slide.subtitle }}</span>
            </div>
            <h1 class="font-extrabold text-3xl md:text-5xl mb-4 leading-tight text-white" v-html="slide.title" />
            <p class="text-sm md:text-[15px] mb-6 text-zinc-300 max-w-lg leading-relaxed">{{ slide.description }}</p>
            <div class="flex items-center gap-3">
              <UiButton :to="slide.link" size="md">
                {{ slide.btnPrimary }}
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
              </UiButton>
              <router-link :to="slide.link" class="bg-white/10 hover:bg-white/20 text-white border border-white/10 px-4 py-2.5 rounded-none text-sm font-bold transition-colors">
                {{ slide.btnSecondary }}
              </router-link>
            </div>
          </div>
        </div>

        <!-- Arrows -->
        <button
          class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-sm border border-white/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110"
          @click="prevSlide"
        >
          <span class="material-symbols-outlined text-white text-[20px]">chevron_left</span>
        </button>
        <button
          class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-sm border border-white/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110"
          @click="() => { nextSlide(); resetTimer(); }"
        >
          <span class="material-symbols-outlined text-white text-[20px]">chevron_right</span>
        </button>

        <!-- Counter -->
        <div class="absolute bottom-6 right-8 z-20 text-white/40 text-xs font-bold tabular-nums">
          {{ activeIndex + 1 }}&nbsp;/&nbsp;{{ slides.length }}
        </div>

        <!-- Dots -->
        <div class="absolute bottom-6 left-8 md:left-12 flex gap-2.5 z-20">
          <button
            v-for="(_, index) in slides"
            :key="index"
            :class="['h-1.5 rounded-full transition-all duration-500', activeIndex === index ? 'w-12 bg-[#00a046]' : 'w-6 bg-white/30 hover:bg-white/50']"
            :aria-label="`Слайд ${index + 1}`"
            @click="setSlide(index)"
          />
        </div>
      </div>

      <!-- ── Subcategories Mega Menu overlay (appears on hover) ── -->
      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-x-2"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-2"
      >
        <div
          v-if="hoveredCat"
          class="absolute left-[230px] xl:left-[250px] top-0 bottom-0 right-0 z-30 bg-[#1c2229] border-l border-zinc-800 shadow-2xl flex flex-col p-6 overflow-y-auto animate-in fade-in duration-200"
        >
          <!-- Subcategories Columns (Identical to screenshot) -->
          <div
            v-if="hoveredCat.columns && hoveredCat.columns.length > 0"
            class="grid grid-cols-4 gap-6"
          >
            <div
              v-for="(col, colIdx) in hoveredCat.columns"
              :key="colIdx"
              class="space-y-6 text-white"
            >
              <div
                v-for="(group, gIdx) in col"
                :key="gIdx"
                class="space-y-2"
              >
                <h4
                  class="text-[#3898ec] hover:underline font-extrabold text-[11.5px] uppercase tracking-wider cursor-pointer"
                  @click="handleGroupTitleClick(group)"
                >
                  {{ group.title }}
                </h4>
                <ul class="space-y-1.5">
                  <li
                    v-for="(link, lIdx) in group.links"
                    :key="lIdx"
                    class="flex items-center"
                  >
                    <span
                      class="text-zinc-300 hover:text-[#3898ec] text-xs cursor-pointer transition-colors leading-relaxed"
                      @click="handleLinkClick(link)"
                    >
                      {{ link.name }}
                    </span>
                    <span
                      v-if="link.badge"
                      class="text-[#ff4b5f] text-[9px] font-black uppercase tracking-wider ml-1"
                    >
                      {{ link.badge }}
                    </span>
                  </li>
                </ul>
                <div
                  v-if="group.showMoreSlug"
                  class="text-zinc-500 hover:text-zinc-300 text-[11px] font-semibold cursor-pointer underline decoration-dashed decoration-zinc-600 underline-offset-2 mt-1 inline-block"
                  @click="handleGroupTitleClick(group)"
                >
                  Дивитися далі →
                </div>
              </div>
            </div>
          </div>
          <div
            v-else
            class="flex-grow flex flex-col items-center justify-center text-zinc-500 py-12"
          >
            <span class="material-symbols-outlined text-4xl mb-2">category</span>
            <p class="text-xs font-bold">Немає дочірніх розділів.</p>
          </div>
        </div>
      </Transition>

    </div>
  </section>
</template>

<style scoped>
.cat-scroll::-webkit-scrollbar { width: 3px; }
.cat-scroll::-webkit-scrollbar-track { background: transparent; }
.cat-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
</style>
