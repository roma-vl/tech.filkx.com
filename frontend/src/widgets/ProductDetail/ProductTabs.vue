<template>
  <section class="mt-14">
    <!-- Tab bar -->
    <div class="flex border-b border-zinc-200 dark:border-zinc-800 mb-10 overflow-x-auto hide-scrollbar">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        :class="activeTab === tab.id
          ? 'text-zinc-900 dark:text-white font-bold border-b-2 border-[#00a046]'
          : 'text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 font-medium'"
        class="px-6 py-4 text-sm whitespace-nowrap transition-all -mb-px"
        @click="$emit('change-tab', tab.id)"
      >
        {{ tab.label }}
        <span
          v-if="tab.id === 'reviews' && reviewTabCount > 0"
          class="ml-1.5 text-[10px] font-black bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 px-1.5 py-0.5 rounded-full"
        >{{ reviewTabCount }}</span>
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
      <!-- Tab content -->
      <div class="lg:col-span-8">

        <!-- Overview tab -->
        <section v-if="activeTab === 'experience'" class="space-y-8 text-left">
          <div class="max-w-2xl space-y-3">
            <h3 class="text-xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Опис товару</h3>
            <p class="text-[15px] text-zinc-600 dark:text-zinc-400 leading-relaxed whitespace-pre-line">
              {{ product.description }}
            </p>
          </div>
          <img
            v-if="galleryImages[1]"
            :alt="product.name"
            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 object-contain max-h-[480px] bg-white p-6"
            :src="galleryImages[1].src"
          />
        </section>

        <!-- Specs tab -->
        <section v-else-if="activeTab === 'specs'" class="space-y-5 text-left">
          <h3 class="text-lg font-extrabold text-zinc-900 dark:text-white flex items-center gap-2">
            <span class="material-symbols-outlined text-[#00a046] text-[22px]">terminal</span>
            Технічні характеристики
          </h3>
          <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden">
            <table class="w-full text-left border-collapse">
              <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <tr
                  v-for="(spec, index) in product.specs"
                  :key="spec[0]"
                  :class="Number(index) % 2 ? 'bg-zinc-50/50 dark:bg-zinc-800/20' : ''"
                  class="hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-colors"
                >
                  <td class="px-5 py-3.5 text-sm font-semibold text-zinc-500 dark:text-zinc-400 w-2/5">
                    {{ spec[0] }}
                  </td>
                  <td class="px-5 py-3.5 text-sm font-bold text-zinc-800 dark:text-zinc-200">
                    {{ spec[1] }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Reviews tab -->
        <section v-else-if="activeTab === 'reviews'" class="space-y-8 text-left">
          <!-- Loading skeleton -->
          <div v-if="reviewsLoading" class="space-y-4">
            <div v-for="i in 2" :key="i" class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl animate-pulse">
              <div class="flex gap-3 mb-3">
                <div class="w-8 h-8 bg-zinc-200 dark:bg-zinc-800 rounded-full" />
                <div class="flex-1 space-y-2">
                  <div class="h-3 bg-zinc-200 dark:bg-zinc-800 rounded w-1/4" />
                  <div class="h-3 bg-zinc-200 dark:bg-zinc-800 rounded w-1/3" />
                </div>
              </div>
              <div class="space-y-2">
                <div class="h-3 bg-zinc-200 dark:bg-zinc-800 rounded" />
                <div class="h-3 bg-zinc-200 dark:bg-zinc-800 rounded w-3/4" />
              </div>
            </div>
          </div>

          <template v-else>
            <!-- Summary (only when reviews exist) -->
            <div v-if="liveReviews.length > 0" class="grid grid-cols-1 md:grid-cols-12 gap-5">
              <div class="md:col-span-4 p-6 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 text-center space-y-2">
                <p class="text-5xl font-extrabold text-zinc-900 dark:text-white tracking-tighter">{{ reviewStats.avg }}</p>
                <div class="flex justify-center text-amber-400">
                  <span
                    v-for="star in 5"
                    :key="star"
                    class="material-symbols-outlined text-[20px]"
                    :style="star <= Math.round(reviewStats.avg) ? 'font-variation-settings: &quot;FILL&quot; 1' : ''"
                  >star</span>
                </div>
                <p class="text-xs text-zinc-400 dark:text-zinc-500">Середня оцінка ({{ reviewStats.count }} відгуків)</p>
              </div>
              <div class="md:col-span-8 p-6 border border-zinc-200 dark:border-zinc-800 rounded-xl flex flex-col justify-center gap-3">
                <div
                  v-for="(count, idx) in reviewStats.distribution"
                  :key="idx"
                  class="flex items-center gap-3 text-sm"
                >
                  <span class="font-semibold w-14 text-right text-zinc-500 dark:text-zinc-400 shrink-0">{{ 5 - idx }} зірок</span>
                  <div class="flex-1 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-[#00a046] rounded-full transition-all duration-500"
                      :style="{ width: reviewStats.count ? `${Math.round(count / reviewStats.count * 100)}%` : '0%' }"
                    />
                  </div>
                  <span class="font-semibold w-7 text-zinc-400 dark:text-zinc-500 shrink-0 text-right">{{ count }}</span>
                </div>
              </div>
            </div>

            <!-- Reviews list -->
            <div v-if="liveReviews.length > 0" class="space-y-4">
              <article
                v-for="review in liveReviews"
                :key="review.id"
                class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-3"
              >
                <div class="flex items-start justify-between gap-4">
                  <div class="space-y-0.5">
                    <h4 class="font-bold text-sm text-zinc-900 dark:text-white">{{ review.title || review.author }}</h4>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ review.title ? review.author : '' }} · {{ formatReviewDate(review.created_at) }}</p>
                  </div>
                  <div class="flex text-amber-400 shrink-0">
                    <span
                      v-for="star in 5"
                      :key="star"
                      class="material-symbols-outlined text-[15px]"
                      :style="star <= review.rating ? 'font-variation-settings: &quot;FILL&quot; 1' : ''"
                    >star</span>
                  </div>
                </div>
                <p class="text-[15px] text-zinc-600 dark:text-zinc-400 leading-relaxed">{{ review.body }}</p>

                <!-- Photo grid -->
                <div v-if="review.photos?.length" class="flex flex-wrap gap-2 pt-1">
                  <button
                    v-for="(photo, pi) in review.photos"
                    :key="pi"
                    class="w-20 h-20 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 hover:border-[#00a046]/40 transition-colors"
                    @click="openLightbox(review.photos!, pi)"
                  >
                    <img :src="photo" class="w-full h-full object-cover" />
                  </button>
                </div>
              </article>
            </div>

            <!-- Empty state -->
            <div v-else class="py-12 text-center">
              <div class="w-16 h-16 bg-zinc-100 dark:bg-zinc-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-[28px] text-zinc-400">rate_review</span>
              </div>
              <h3 class="font-bold text-sm text-zinc-700 dark:text-zinc-300">Ще немає відгуків</h3>
              <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1 max-w-xs mx-auto">
                Будьте першим, хто залишить відгук про цей товар після покупки.
              </p>
            </div>
          </template>
        </section>

        <!-- Support tab -->
        <section v-else class="grid grid-cols-1 md:grid-cols-3 gap-5 text-left">
          <div
            v-for="item in supportCards"
            :key="item.title"
            class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-2.5"
          >
            <span class="material-symbols-outlined text-[#00a046] text-[26px]">{{ item.icon }}</span>
            <h3 class="font-bold text-sm text-zinc-900 dark:text-white">{{ item.title }}</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed">{{ item.text }}</p>
          </div>
        </section>
      </div>

      <!-- Guarantees sidebar -->
      <aside class="lg:col-span-4 space-y-5 text-left">
        <div class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-5">
          <h4 class="text-xs font-extrabold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 flex items-center gap-2">
            <span class="material-symbols-outlined text-[#00a046] text-[17px]">verified</span>
            Гарантії якості
          </h4>
          <ul class="space-y-4">
            <li
              v-for="item in qualityGuarantees"
              :key="item.title"
              class="flex gap-3.5 items-start"
            >
              <span class="material-symbols-outlined text-[#00a046] text-[20px] mt-0.5 shrink-0">{{ item.icon }}</span>
              <div>
                <p class="font-bold text-sm text-zinc-800 dark:text-zinc-100">{{ item.title }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 leading-relaxed">{{ item.text }}</p>
              </div>
            </li>
          </ul>
        </div>

        <div class="p-5 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-700/20 rounded-xl space-y-3">
          <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[#00a046] text-[20px]">chat_bubble</span>
            <h4 class="font-bold text-sm text-zinc-800 dark:text-zinc-200">Технічний радник</h4>
          </div>
          <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
            Наші інженери допоможуть обрати ідеальну конфігурацію під ваші потреби.
          </p>
          <button
            class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-2.5 rounded-lg font-bold text-sm flex items-center justify-center gap-1.5 transition-all shadow-sm"
          >
            <span class="material-symbols-outlined text-[16px]">chat_bubble</span>
            Почати чат
          </button>
        </div>
      </aside>
    </div>
  </section>

  <!-- Photo Lightbox -->
  <Teleport to="body">
    <div
      v-if="lightbox.open"
      class="fixed inset-0 z-[60] bg-black/90 backdrop-blur-sm flex items-center justify-center p-4"
      @click.self="lightbox.open = false"
    >
      <button
        class="absolute top-4 right-4 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-all"
        @click="lightbox.open = false"
      >
        <span class="material-symbols-outlined text-[22px]">close</span>
      </button>

      <!-- Prev -->
      <button
        v-if="lightbox.photos.length > 1"
        class="absolute left-4 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-all"
        @click="lightbox.index = (lightbox.index - 1 + lightbox.photos.length) % lightbox.photos.length"
      >
        <span class="material-symbols-outlined text-[22px]">chevron_left</span>
      </button>

      <img
        :src="lightbox.photos[lightbox.index]"
        class="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl select-none"
      />

      <!-- Next -->
      <button
        v-if="lightbox.photos.length > 1"
        class="absolute right-4 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white transition-all"
        @click="lightbox.index = (lightbox.index + 1) % lightbox.photos.length"
      >
        <span class="material-symbols-outlined text-[22px]">chevron_right</span>
      </button>

      <!-- Counter -->
      <div v-if="lightbox.photos.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/70 text-xs font-semibold">
        {{ lightbox.index + 1 }} / {{ lightbox.photos.length }}
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted } from "vue";
import api from "@/services/api";

interface TabItem { id: string; label: string }
interface QualityGuarantee { icon: string; title: string; text: string }
interface LiveReview { id: number; rating: number; title?: string; body: string; author: string; photos?: string[]; created_at: string }

const props = defineProps<{
  activeTab: string;
  tabs: TabItem[];
  product: any;
  galleryImages: Array<{ label: string; src: string }>;
  qualityGuarantees: QualityGuarantee[];
  reviews: any[]; // legacy prop kept for compatibility
}>();

defineEmits<{ (e: "change-tab", tabId: string): void }>();

const supportCards = [
  { icon: "support_agent", title: "Консультація експерта", text: "Наші спеціалісти допоможуть з налаштуванням та перенесенням даних." },
  { icon: "local_shipping", title: "Доставка і заміна", text: "Безкоштовна доставка та швидкий обмін товару за вашим запитом." },
  { icon: "workspace_premium", title: "Програма захисту", text: "Продовження гарантії та страхування від випадкових пошкоджень." },
];

const liveReviews = ref<LiveReview[]>([]);
const reviewsLoading = ref(false);
const reviewStats = reactive({ count: 0, avg: 0, distribution: [0, 0, 0, 0, 0] });
const reviewsFetched = ref(false);

// Show product.reviews from API immediately; update to real count after tab is loaded
const reviewTabCount = computed(() =>
  reviewsFetched.value ? reviewStats.count : (props.product?.reviews ?? 0)
);

const fetchReviews = async () => {
  if (!props.product?.slug) return;
  reviewsLoading.value = true;
  try {
    const { data } = await api.get(`/v1/catalog/products/${props.product.slug}/reviews`);
    liveReviews.value = data.data?.reviews || [];
    const s = data.data?.stats;
    if (s) {
      reviewStats.count = s.count;
      reviewStats.avg = s.avg;
      reviewStats.distribution = s.distribution;
    }
    reviewsFetched.value = true;
  } catch {
    liveReviews.value = [];
  } finally {
    reviewsLoading.value = false;
  }
};

watch(() => props.activeTab, (tab) => {
  if (tab === 'reviews' && !reviewsFetched.value && !reviewsLoading.value) {
    fetchReviews();
  }
});

onMounted(() => {
  // Always fetch to get the accurate count for the tab badge
  fetchReviews();
});

const formatReviewDate = (iso: string) => {
  return new Date(iso).toLocaleDateString('uk-UA', { day: 'numeric', month: 'long', year: 'numeric' });
};

// Lightbox
const lightbox = reactive({ open: false, photos: [] as string[], index: 0 });
const openLightbox = (photos: string[], index: number) => {
  lightbox.photos = photos;
  lightbox.index = index;
  lightbox.open = true;
};
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
