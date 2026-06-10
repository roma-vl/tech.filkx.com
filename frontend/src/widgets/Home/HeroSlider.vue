<script setup>
import { ref, onMounted, onUnmounted } from "vue";

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

onMounted(() => {
  startTimer();
});

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-6 font-sans">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left side: Main Slider (takes 2 columns) -->
      <div
        class="lg:col-span-2 relative rounded-xl overflow-hidden bg-zinc-950 h-[380px] md:h-[480px] flex items-center group shadow-md border border-zinc-800"
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
              <a
                :href="slide.link"
                class="bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-2.5 rounded-lg text-sm font-bold transition-all shadow-md flex items-center gap-1.5"
              >
                {{ slide.btnPrimary }}
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
              </a>
              <a
                :href="slide.link"
                class="bg-white/10 hover:bg-white/20 text-white border border-white/10 px-6 py-2.5 rounded-lg text-sm font-bold transition-colors"
              >
                {{ slide.btnSecondary }}
              </a>
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

      <!-- Right side: Mini Promo Cards (takes 1 column) -->
      <div class="flex flex-col gap-5 h-[380px] md:h-[480px] justify-between">
        <!-- Promo 1: Smart Home Setup -->
        <div
          class="relative flex-1 rounded-xl overflow-hidden bg-gradient-to-br from-indigo-950 to-purple-900 border border-purple-500/20 shadow-md p-6 flex flex-col justify-between group hover:border-purple-500/40 transition-colors"
        >
          <div
            class="absolute right-0 bottom-0 w-1/2 h-full opacity-30 group-hover:scale-110 transition-transform duration-700 pointer-events-none"
          >
            <svg
              class="w-full h-full text-purple-400"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M2.25 21h19.5M3 10.5h.008v.008H3V10.5zm3 0h.008v.008H6V10.5zm3 0h.008v.008H9V10.5zm0-3h.008v.008H9V7.5zm3 3h.008v.008h-.008V10.5zm3 0h.008v.008H15V10.5zm3 0h.008v.008H18V10.5zm-3-3h.008v.008H15V7.5zm-6 6h.008v.008H9v-.008zm3 0h.008v.008h-.008v-.008zm3 0h.008v.008H15v-.008z"
              />
            </svg>
          </div>
          <div class="relative z-10">
            <span
              class="text-[10px] bg-purple-500/20 border border-purple-400/30 text-purple-300 px-2 py-0.5 rounded font-black uppercase tracking-wider"
            >Розумний Дім</span>
            <h3
              class="font-extrabold text-lg md:text-xl text-white mt-3 leading-snug"
            >
              Технології майбутнього вже сьогодні
            </h3>
            <p
              class="text-xs md:text-sm text-zinc-400 mt-1.5 leading-relaxed max-w-[220px]"
            >
              Керуйте освітленням, безпекою та кліматом за допомогою смартфона.
            </p>
          </div>
          <a
            href="/catalog"
            class="relative z-10 text-xs md:text-sm font-bold text-white hover:text-purple-300 transition-colors flex items-center gap-1.5 w-fit mt-4"
          >
            Перейти в каталог
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </a>
        </div>

        <!-- Promo 2: Active Lifestyle Accessories -->
        <div
          class="relative flex-1 rounded-xl overflow-hidden bg-gradient-to-br from-emerald-950 to-teal-900 border border-emerald-500/20 shadow-md p-6 flex flex-col justify-between group hover:border-emerald-500/40 transition-colors"
        >
          <div
            class="absolute right-0 bottom-0 w-1/2 h-full opacity-35 group-hover:scale-110 transition-transform duration-700 pointer-events-none"
          >
            <svg
              class="w-full h-full text-emerald-400"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.5"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 18a3.75 3.75 0 0 0 .495-7.467 5.99 5.99 0 0 0-1.925 3.546 5.974 5.974 0 0 1-2.133-1A3.75 3.75 0 0 0 12 18Z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 2.25V4.5m0 15v2.25m-9.75-9.75h2.25m15 0h2.25M6.22 6.22l1.59 1.59m10.38 10.38l1.59 1.59M6.22 17.78l1.59-1.59m10.38-10.38l1.59-1.59"
              />
            </svg>
          </div>
          <div class="relative z-10">
            <span
              class="text-[10px] bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 px-2 py-0.5 rounded font-black uppercase tracking-wider"
            >Суперсила</span>
            <h3
              class="font-extrabold text-lg md:text-xl text-white mt-3 leading-snug"
            >
              Спорт та Здоров'я на максимумі
            </h3>
            <p
              class="text-xs md:text-sm text-zinc-400 mt-1.5 leading-relaxed max-w-[220px]"
            >
              Нові моделі фітнес-браслетів та смарт-годинників з GPS.
            </p>
          </div>
          <a
            href="/catalog"
            class="relative z-10 text-xs md:text-sm font-bold text-white hover:text-emerald-300 transition-colors flex items-center gap-1.5 w-fit mt-4"
          >
            Переглянути акції
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped></style>
