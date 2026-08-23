<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import { RouterLink } from "vue-router";
import { useI18n } from "vue-i18n";
import UiButton from "@/shared/ui/UiButton.vue";
import { mapDbCategoriesToMenu } from "@/shared/utils/categoryMapper";

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
  banners: {
    type: Array,
    default: () => [],
  },
});

const { locale, t } = useI18n();

const activeIndex = ref(0);
const hoveredCat = ref(null);
let intervalId = null;

const mappedCategories = computed(() =>
  mapDbCategoriesToMenu(props.categories, locale.value),
);

const getBannerLink = (banner) => {
  switch (banner.linkType) {
    case "category":
      return banner.linkValue
        ? { name: "category", params: { slug: banner.linkValue } }
        : { name: "catalog" };
    case "product":
      return banner.linkValue
        ? { name: "product-detail", params: { id: banner.linkValue } }
        : { name: "catalog" };
    case "url":
      return banner.linkValue || { name: "catalog" };
    case "catalog":
    default:
      return { name: "catalog" };
  }
};

const slides = computed(() => {
  if (props.banners.length > 0) {
    return props.banners.map((banner) => ({
      badge: banner.badge,
      subtitle: banner.subtitle,
      title: banner.title,
      description: banner.description,
      image: banner.imageUrl,
      buttonLabel: banner.buttonLabel || t("home.hero.viewButton"),
      link: getBannerLink(banner),
    }));
  }

  // Safe fallback when no admin-managed banners are configured yet:
  // no external images, no invented promo content.
  return [
    {
      badge: "",
      subtitle: "",
      title: t("home.hero.welcomeTitle"),
      description: t("home.hero.welcomeDescription"),
      image: null,
      buttonLabel: t("home.hero.goToCatalog"),
      link: { name: "catalog" },
    },
  ];
});

const nextSlide = () => {
  activeIndex.value = (activeIndex.value + 1) % slides.value.length;
};
const prevSlide = () => {
  activeIndex.value =
    (activeIndex.value - 1 + slides.value.length) % slides.value.length;
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

const getGroupRoute = (group) => {
  return {
    name: "category",
    params: { slug: group.slug },
  };
};

const getLinkRoute = (link) => {
  return {
    name: "category",
    params: { slug: link.slug },
  };
};

onMounted(() => {
  startTimer();
});
onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-5 font-sans">
    <div
      class="flex items-stretch overflow-hidden shadow-xl rounded-none relative"
      @mouseleave="hoveredCat = null"
    >
      <!-- ── Left: dark category sidebar (desktop only) ── -->
      <div
        class="hidden lg:flex flex-col w-[230px] xl:w-[250px] shrink-0 bg-[#1c2229] relative z-20 border-r border-zinc-800"
      >
        <!-- Category links -->
        <nav class="flex-1 overflow-y-auto cat-scroll bg-[#1c2229]">
          <template v-if="mappedCategories.length > 0">
            <RouterLink
              v-for="cat in mappedCategories"
              :key="cat.id"
              :to="{ name: 'category', params: { slug: cat.slug } }"
              class="w-full flex items-center justify-between px-4 py-2.5 text-left transition-all duration-150 group/cl rounded-none"
              :class="
                hoveredCat && hoveredCat.id === cat.id
                  ? 'bg-[#252e37] text-white font-bold'
                  : 'text-zinc-300 hover:text-white hover:bg-[#252e37]/75'
              "
              @mouseenter="hoveredCat = cat"
              @click="hoveredCat = null"
            >
              <div class="flex items-center gap-3">
                <span
                  class="material-symbols-outlined text-[19px] shrink-0 transition-colors"
                  :class="
                    hoveredCat && hoveredCat.id === cat.id
                      ? 'text-white'
                      : 'text-zinc-400 group-hover/cl:text-white'
                  "
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
            </RouterLink>
          </template>
          <template v-else>
            <div
              v-for="i in 11"
              :key="i"
              class="mx-4 my-2.5 h-6 bg-white/10 rounded-none animate-pulse"
            />
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
          <img
            v-if="slide.image"
            class="absolute inset-0 w-full h-full object-cover opacity-60"
            :src="slide.image"
            alt=""
          />
          <div
            v-else
            class="absolute inset-0 bg-gradient-to-br from-[#1c2229] via-zinc-900 to-[#00a046]/20"
          />
          <div
            class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/40 to-transparent"
          />

          <div
            class="relative z-10 px-8 md:px-14 max-w-2xl text-white h-full flex flex-col justify-center"
          >
            <div
              v-if="slide.badge || slide.subtitle"
              class="flex items-center gap-2 mb-3"
            >
              <span
                v-if="slide.badge"
                class="bg-[#00a046] text-white font-bold uppercase tracking-wider px-3 py-1 rounded-none text-[10px]"
              >
                {{ slide.badge }}
              </span>
              <span
                v-if="slide.subtitle"
                class="text-zinc-300 font-bold text-xs uppercase tracking-widest"
                >• {{ slide.subtitle }}</span
              >
            </div>
            <h1
              class="font-extrabold text-3xl md:text-5xl mb-4 leading-tight text-white"
            >
              {{ slide.title }}
            </h1>
            <p
              v-if="slide.description"
              class="text-sm md:text-[15px] mb-6 text-zinc-300 max-w-lg leading-relaxed"
            >
              {{ slide.description }}
            </p>
            <div class="flex items-center gap-3">
              <UiButton :to="slide.link" size="md">
                {{ slide.buttonLabel }}
                <span class="material-symbols-outlined text-[18px]"
                  >arrow_forward</span
                >
              </UiButton>
            </div>
          </div>
        </div>

        <!-- Arrows -->
        <template v-if="slides.length > 1">
          <button
            class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-sm border border-white/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110"
            @click="prevSlide"
          >
            <span class="material-symbols-outlined text-white text-[20px]"
              >chevron_left</span
            >
          </button>
          <button
            class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/10 hover:bg-white/25 backdrop-blur-sm border border-white/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110"
            @click="
              () => {
                nextSlide();
                resetTimer();
              }
            "
          >
            <span class="material-symbols-outlined text-white text-[20px]"
              >chevron_right</span
            >
          </button>

          <!-- Counter -->
          <div
            class="absolute bottom-6 right-8 z-20 text-white/40 text-xs font-bold tabular-nums"
          >
            {{ activeIndex + 1 }}&nbsp;/&nbsp;{{ slides.length }}
          </div>

          <!-- Dots -->
          <div class="absolute bottom-6 left-8 md:left-12 flex gap-2.5 z-20">
            <button
              v-for="(_, index) in slides"
              :key="index"
              :class="[
                'h-1.5 rounded-full transition-all duration-500',
                activeIndex === index
                  ? 'w-12 bg-[#00a046]'
                  : 'w-6 bg-white/30 hover:bg-white/50',
              ]"
              :aria-label="t('home.hero.slideAriaLabel', { n: index + 1 })"
              @click="setSlide(index)"
            />
          </div>
        </template>
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
              <div v-for="(group, gIdx) in col" :key="gIdx" class="space-y-2">
                <h4
                  class="font-extrabold text-[11.5px] uppercase tracking-wider"
                >
                  <RouterLink
                    :to="getGroupRoute(group)"
                    class="text-[#3898ec] hover:underline cursor-pointer"
                    @click="hoveredCat = null"
                  >
                    {{ group.title }}
                  </RouterLink>
                </h4>
                <ul class="space-y-1.5">
                  <li
                    v-for="(link, lIdx) in group.links"
                    :key="lIdx"
                    class="flex items-center"
                  >
                    <RouterLink
                      :to="getLinkRoute(link)"
                      class="text-zinc-300 hover:text-[#3898ec] text-xs cursor-pointer transition-colors leading-relaxed"
                      @click="hoveredCat = null"
                    >
                      {{ link.name }}
                    </RouterLink>
                    <span
                      v-if="link.badge"
                      class="text-[#ff4b5f] text-[9px] font-black uppercase tracking-wider ml-1"
                    >
                      {{ link.badge }}
                    </span>
                  </li>
                </ul>
                <RouterLink
                  v-if="group.showMoreSlug"
                  :to="getGroupRoute(group)"
                  class="text-zinc-500 hover:text-zinc-300 text-[11px] font-semibold cursor-pointer underline decoration-dashed decoration-zinc-600 underline-offset-2 mt-1 inline-block"
                  @click="hoveredCat = null"
                >
                  {{ t("header.search.viewMore") }}
                </RouterLink>
              </div>
            </div>
          </div>
          <div
            v-else
            class="flex-grow flex flex-col items-center justify-center text-zinc-500 py-12"
          >
            <span class="material-symbols-outlined text-4xl mb-2"
              >category</span
            >
            <p class="text-xs font-bold">
              {{ t("header.megaMenu.noSubcategories") }}
            </p>
          </div>
        </div>
      </Transition>
    </div>
  </section>
</template>

<style scoped>
.cat-scroll::-webkit-scrollbar {
  width: 3px;
}
.cat-scroll::-webkit-scrollbar-track {
  background: transparent;
}
.cat-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
}
</style>
