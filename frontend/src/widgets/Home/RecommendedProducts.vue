<script setup lang="ts">
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";
import type { HomeProduct } from "@/entities/product/lib/mapHomeProduct";

const props = defineProps<{
  products: HomeProduct[];
  personalized?: boolean;
}>();

const { t } = useI18n();
const carouselRef = ref<HTMLElement | null>(null);

const scrollCarousel = (direction: "left" | "right") => {
  if (carouselRef.value) {
    const scrollAmount = direction === "left" ? -320 : 320;
    carouselRef.value.scrollBy({ left: scrollAmount, behavior: "smooth" });
  }
};
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-14 font-sans">
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-8">
      <div class="space-y-1.5">
        <span
          class="text-[#00a046] font-extrabold text-xs uppercase tracking-widest"
        >
          {{
            personalized
              ? t("home.recommended.personalizedBadge")
              : t("home.recommended.genericBadge")
          }}
        </span>
        <h2
          class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight"
        >
          {{
            personalized
              ? t("home.recommended.personalizedTitle")
              : t("home.recommended.genericTitle")
          }}
        </h2>
        <p class="text-sm md:text-[15px] text-zinc-500 dark:text-zinc-400">
          {{
            personalized
              ? t("home.recommended.personalizedSubtitle")
              : t("home.recommended.genericSubtitle")
          }}
        </p>
      </div>

      <!-- Arrow Controls -->
      <div class="flex gap-2 shrink-0">
        <button
          class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
          :aria-label="t('home.recommended.scrollLeft')"
          @click="scrollCarousel('left')"
        >
          <span
            class="material-symbols-outlined text-[20px] text-zinc-600 dark:text-zinc-400"
            >chevron_left</span
          >
        </button>
        <button
          class="w-9 h-9 rounded-lg border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
          :aria-label="t('home.recommended.scrollRight')"
          @click="scrollCarousel('right')"
        >
          <span
            class="material-symbols-outlined text-[20px] text-zinc-600 dark:text-zinc-400"
            >chevron_right</span
          >
        </button>
      </div>
    </div>

    <!-- Carousel Row -->
    <div
      ref="carouselRef"
      class="flex overflow-x-auto hide-scrollbar scroll-smooth snap-x snap-mandatory px-0.5 py-8 -my-6"
    >
      <ProductCard
        v-for="prod in products"
        :key="prod.id"
        :product="prod"
        view-mode="grid"
        :scale-on-hover="false"
        class="w-1/2 md:w-1/3 lg:w-1/5 min-w-[220px] snap-start shrink-0"
      />
    </div>
  </section>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
