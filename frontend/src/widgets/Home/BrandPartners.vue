<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { productApi } from "@/shared/services/api/productApi";

const router = useRouter();
const brands = ref([]);

const goToBrand = (brand) => {
  router.push({ name: "catalog", query: { brand: brand.slug } });
};

const fetchBrands = async () => {
  try {
    const { data } = await productApi.catalogGetBrands();
    const list = data?.data || [];
    brands.value = list
      .filter((b) => (b.products_count ?? b.productsCount ?? 0) > 0)
      .sort((a, b) => (b.products_count ?? b.productsCount ?? 0) - (a.products_count ?? a.productsCount ?? 0));
  } catch (error) {
    console.error("Failed to load brands:", error);
  }
};

onMounted(fetchBrands);
</script>

<template>
  <section
    v-if="brands.length > 0"
    class="border-y border-zinc-100 dark:border-zinc-900 bg-white dark:bg-zinc-950 py-10 font-sans overflow-hidden"
  >
    <div class="max-w-container-max mx-auto px-4 md:px-8 mb-7">
      <h2
        class="text-center text-[11px] text-zinc-400 dark:text-zinc-500 font-extrabold uppercase tracking-[0.25em]"
      >
        Бренди в нашому каталозі
      </h2>
    </div>

    <!-- Marquee track -->
    <div class="relative flex overflow-hidden select-none [mask-image:linear-gradient(to_right,transparent,black_10%,black_90%,transparent)]">
      <div class="flex animate-marquee gap-12 md:gap-16 shrink-0 items-center">
        <button
          v-for="brand in brands"
          :key="brand.id"
          type="button"
          class="font-extrabold text-base md:text-lg tracking-widest text-zinc-400 dark:text-zinc-600 hover:text-[#00a046] dark:hover:text-[#00a046] transition-colors duration-300 whitespace-nowrap"
          @click="goToBrand(brand)"
        >
          {{ brand.name?.toUpperCase() }}
        </button>
      </div>
      <!-- Duplicate for seamless loop -->
      <div
        class="flex animate-marquee gap-12 md:gap-16 shrink-0 items-center"
        aria-hidden="true"
      >
        <button
          v-for="brand in brands"
          :key="`dup-${brand.id}`"
          type="button"
          tabindex="-1"
          class="font-extrabold text-base md:text-lg tracking-widest text-zinc-400 dark:text-zinc-600 hover:text-[#00a046] dark:hover:text-[#00a046] transition-colors duration-300 whitespace-nowrap"
          @click="goToBrand(brand)"
        >
          {{ brand.name?.toUpperCase() }}
        </button>
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes marquee {
  from { transform: translateX(0); }
  to { transform: translateX(-100%); }
}

.animate-marquee {
  animation: marquee 22s linear infinite;
}

.animate-marquee:hover {
  animation-play-state: paused;
}
</style>
