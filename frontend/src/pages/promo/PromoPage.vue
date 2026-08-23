<template>
  <main class="min-h-screen bg-zinc-50 dark:bg-zinc-950">
    <!-- Loading state -->
    <div v-if="loading" class="max-w-container-max mx-auto px-4 md:px-8 py-10">
      <div class="animate-pulse space-y-8">
        <div class="h-64 md:h-80 bg-zinc-200 dark:bg-zinc-800 rounded-2xl" />
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div
            v-for="i in 8"
            :key="i"
            class="h-72 bg-zinc-200 dark:bg-zinc-800 rounded-xl"
          />
        </div>
      </div>
    </div>

    <!-- Not found -->
    <div
      v-else-if="!promoPage"
      class="max-w-4xl mx-auto px-4 py-32 text-center"
    >
      <span class="text-6xl mb-4 block">🏷️</span>
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
        {{ t("promoPage.notFound.title") }}
      </h1>
      <RouterLink
        :to="{ name: 'catalog' }"
        class="text-emerald-600 hover:underline"
      >
        {{ t("promoPage.notFound.backToCatalog") }}
      </RouterLink>
    </div>

    <!-- Content -->
    <template v-else>
      <!-- Hero header -->
      <section class="relative h-64 md:h-80 overflow-hidden">
        <img
          class="absolute inset-0 w-full h-full object-cover opacity-60"
          :src="promoPage.imageUrl"
          :alt="promoPage.title"
        />
        <div
          class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/40 to-transparent"
        />
        <div
          class="relative z-10 max-w-container-max mx-auto px-4 md:px-8 h-full flex flex-col justify-center text-white"
        >
          <div
            v-if="promoPage.badge"
            class="mb-3 inline-flex w-fit items-center bg-[#00a046] text-white font-bold uppercase tracking-wider px-3 py-1 rounded-none text-[10px]"
          >
            {{ promoPage.badge }}
          </div>
          <h1 class="font-extrabold text-3xl md:text-5xl mb-3 leading-tight">
            {{ promoPage.title }}
          </h1>
          <p
            v-if="promoPage.subtitle"
            class="text-zinc-200 font-bold text-sm uppercase tracking-widest mb-2"
          >
            {{ promoPage.subtitle }}
          </p>
          <p
            v-if="promoPage.description"
            class="text-sm md:text-[15px] text-zinc-300 max-w-2xl leading-relaxed"
          >
            {{ promoPage.description }}
          </p>
        </div>
      </section>

      <div class="max-w-container-max mx-auto px-4 md:px-8 py-10">
        <div
          v-if="products.length"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 border-t border-l border-zinc-200 dark:border-zinc-800"
        >
          <ProductCard
            v-for="product in products"
            :key="product.id"
            :product="product"
          />
        </div>
        <div
          v-else
          class="bg-white dark:bg-zinc-900 rounded-md border border-zinc-100 dark:border-zinc-800 p-16 text-center text-zinc-500 dark:text-zinc-400"
        >
          {{ t("promoPage.empty") }}
        </div>
      </div>
    </template>
  </main>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { useHead } from "@vueuse/head";
import { productApi } from "@/shared/services/api/productApi";
import { mapCatalogProduct } from "@/entities/product/lib/mapCatalogProduct";
import ProductCard from "@/widgets/Catalog/ProductCard.vue";

const route = useRoute();
const { t } = useI18n();

const loading = ref(true);
const promoPage = ref(null);

const products = computed(() =>
  (promoPage.value?.products || []).map(mapCatalogProduct).filter(Boolean),
);

useHead({
  title: computed(
    () => promoPage.value?.title || t("promoPage.notFound.title"),
  ),
});

const fetchPromoPage = async () => {
  loading.value = true;
  promoPage.value = null;
  try {
    const { data } = await productApi.getPromoPage(route.params.slug);
    promoPage.value = data.data;
  } catch (e) {
    console.error("Failed to load promo page:", e);
  } finally {
    loading.value = false;
  }
};

watch(() => route.params.slug, fetchPromoPage, { immediate: true });
</script>
