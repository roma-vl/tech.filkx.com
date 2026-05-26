<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const activeIndex = ref(0);
let intervalId = null;

const slides = [
  {
    badge: 'New Arrival',
    subtitle: 'Pre-order Exclusive',
    title: 'The Future of Sound: Ultra-HD Obsidian X',
    description: 'Experience 99.9% loss-less audio with the new Obsidian X Series. Pure acoustic precision meeting architectural design perfection.',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAeP34U9uxXzWX_WKcHhEi0vWNEKPS1GBvEAaaCn8H_vs759dx6l3vFG3nU5he_83uLeobjrt3wO0ZTEHNr25GfoTOXk5BgJw0aBq4DOBTZMDcXNWSXIr1Br7yzQkfStCKz3Oxa_9E9hwc-MI8TOzXJweyp4dCEYjzmPa4PcOZWK8cZ5xZfFuBzDK2HqrULJkf1Ml3VwbTL28VxUOr2bPKZymnCA8AKm6tLkWZ6qdevZbKINiYZfVXwJTG_-T6bU9QakJ1S-sv7f0Y',
    btnPrimary: 'Pre-order Now',
    btnSecondary: 'Discover Technology'
  },
  {
    badge: 'Special Edition',
    subtitle: 'OLED Revolution',
    title: 'Visual Perfection: Curved Ultra-Wide Gaming',
    description: 'Immerse yourself in infinite contrast and 144Hz fluidity. Elevate your desktop workspace and gaming experience with Obsidian Screen Tech.',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCVMdErktV3TxMtO3JGMvZ9zS0x0zYbKz1BOjfXYU1LCka8ZihAQRInqqCc_p8i-qxa_HPIqDZ5Kevwr5VKLqyqstWGjEH_WRir7OtrPCpV_H8AvfRt69AI8p1TEbtE5tbqG2zcqQqVYp5pEPBnpsxa17bgXzaPwXHLRwCkbNLOL2MDuK_HzBB7j5pEfGKiX20Mo3vcs919pbLNN6aCAU31C3gWvj4f1OiGSSrW_Xd-ECi9ml_qk2QQhzRko2TzwHZxUspi7SRTQJg',
    btnPrimary: 'Explore Monitors',
    btnSecondary: 'Watch Tech Specs'
  },
  {
    badge: 'Featured Release',
    subtitle: 'Smart Wearables',
    title: 'Obsidian Watch 5: Active Intelligence',
    description: 'Track your health metrics with aerospace-grade GPS and 7-day battery life. Designed for extreme performance and daily elegance.',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAG1j0OkwUBTuRJYlZq12iTWSfCx0hTSkVryqbF-FYIDY0p2IODoLkQCjdBzvMUE28jinrTZ-Yhx8ATbKeWyMq2bSKQehQZ5dUfTVBStqMLuWl_dnxdhw_-eZMoiP9_egaelvQQU7gzfXiv-g6KH0W1d7n6iYmoObJXDCEXbrnLagqWXZxOIyeHX_fQAyS84ZvkaGDv8Ld75VMMB-p3JQk_MupRVz9V0REcSykJQllrCavETBkPh8j054bmRUv5No-7faKEr_uRPp0',
    btnPrimary: 'Shop Smartwatches',
    btnSecondary: 'Explore Features'
  }
];

const nextSlide = () => {
  activeIndex.value = (activeIndex.value + 1) % slides.length;
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
  <!-- Hero Slider (Modernized) -->
  <section class="max-w-container-max mx-auto px-margin-desktop py-stack-md">
    <div class="relative rounded-xl overflow-hidden bg-inverse-surface min-h-[540px] flex items-center group shadow-xl border border-outline-variant/10">
      
      <!-- Slides -->
      <div 
        v-for="(slide, index) in slides" 
        :key="index"
        :class="[
          'absolute inset-0 w-full h-full transition-all duration-1000 ease-in-out',
          activeIndex === index ? 'opacity-100 scale-100 pointer-events-auto z-10' : 'opacity-0 scale-105 pointer-events-none z-0'
        ]"
      >
        <!-- Background Image -->
        <img 
          class="absolute inset-0 w-full h-full object-cover opacity-70" 
          :src="slide.image"
          alt="Hero Slide Image"
        />
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent"></div>
        
        <!-- Content -->
        <div class="relative z-10 px-20 max-w-3xl text-white h-full flex flex-col justify-center min-h-[540px]">
          <div class="flex items-center gap-3 mb-6">
            <span class="bg-primary text-white font-label-md uppercase tracking-[0.25em] px-4 py-1.5 rounded-full text-[10px] font-bold shadow-lg">
              {{ slide.badge }}
            </span>
            <span class="text-primary-fixed-dim font-bold text-xs uppercase tracking-widest">
              • {{ slide.subtitle }}
            </span>
          </div>
          <h1 class="font-display-lg text-display-lg mb-6 leading-[1.1] text-shadow-sm" v-html="slide.title"></h1>
          <p class="font-body-lg text-body-lg mb-10 text-surface-variant max-w-xl leading-relaxed">
            {{ slide.description }}
          </p>
          <div class="flex items-center gap-6">
            <button class="bg-primary text-white px-10 py-4 rounded-lg font-title-md hover:scale-105 active:scale-95 transition-all flex items-center gap-2 shadow-lg shadow-primary/20">
              {{ slide.btnPrimary }} <span class="material-symbols-outlined">shopping_bag</span>
            </button>
            <button class="bg-white/5 backdrop-blur-xl border border-white/20 text-white px-10 py-4 rounded-lg font-title-md hover:bg-white/20 transition-all">
              {{ slide.btnSecondary }}
            </button>
          </div>
        </div>
      </div>
      
      <!-- Slider Nav Indicators -->
      <div class="absolute bottom-10 left-20 flex gap-4 z-20">
        <button 
          v-for="(slide, index) in slides" 
          :key="index"
          @click="setSlide(index)"
          :class="[
            'h-1.5 rounded-full transition-all duration-500 shadow-sm',
            activeIndex === index ? 'w-16 bg-primary' : 'w-16 bg-white/20 hover:bg-white/40'
          ]"
        ></button>
      </div>
    </div>
  </section>
</template>
