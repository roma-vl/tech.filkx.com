<template>
  <section class="mt-16">
    <div
      class="flex border-b border-zinc-200 dark:border-zinc-800 mb-12 overflow-x-auto hide-scrollbar scroll-smooth"
    >
      <button
        v-for="tab in tabs"
        :key="tab.id"
        :class="
          activeTab === tab.id
            ? 'text-[#00a046] font-black border-b-2 border-[#00a046]'
            : 'text-zinc-450 hover:text-[#00a046] font-bold'
        "
        class="px-8 py-5 text-sm whitespace-nowrap transition-all uppercase tracking-wider font-bold"
        type="button"
        @click="$emit('change-tab', tab.id)"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <!-- Tab Content -->
      <div class="lg:col-span-8">
        <!-- Experience Tab -->
        <section
          v-if="activeTab === 'experience'"
          class="space-y-6 text-left"
        >
          <div class="max-w-2xl space-y-4">
            <h3
              class="text-xl md:text-2xl font-black text-zinc-900 dark:text-white tracking-tight leading-snug font-bold"
            >
              Опис товару
            </h3>
            <p
              class="text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed whitespace-pre-line"
            >
              {{ product.description }}
            </p>
          </div>
          <img
            v-if="galleryImages[1]"
            :alt="product.name"
            class="w-full rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm object-contain max-h-[450px] bg-white p-4"
            :src="galleryImages[1].src"
          >
        </section>

        <!-- Specifications Tab -->
        <section
          v-else-if="activeTab === 'specs'"
          class="space-y-6 text-left"
        >
          <h3
            class="font-extrabold text-lg text-zinc-900 dark:text-white flex items-center gap-2 font-bold"
          >
            <span class="material-symbols-outlined text-[#00a046]">terminal</span>
            Технічні характеристики
          </h3>
          <div
            class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-sm"
          >
            <table
              class="w-full text-left border-collapse text-xs md:text-sm"
            >
              <thead>
                <tr
                  class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-200 dark:border-zinc-800"
                >
                  <th
                    class="p-4 font-black uppercase tracking-wider text-[10px] text-zinc-450 dark:text-zinc-500 w-1/3"
                  >
                    Характеристика
                  </th>
                  <th
                    class="p-4 font-black uppercase tracking-wider text-[10px] text-zinc-450 dark:text-zinc-500"
                  >
                    Значення
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                <tr
                  v-for="(spec, index) in product.specs"
                  :key="spec[0]"
                  :class="
                    Number(index) % 2 ? 'bg-zinc-50/30 dark:bg-zinc-850/10' : ''
                  "
                  class="hover:bg-emerald-500/5 transition-colors"
                >
                  <td
                    class="p-4 font-bold text-zinc-800 dark:text-zinc-200"
                  >
                    {{ spec[0] }}
                  </td>
                  <td
                    class="p-4 text-zinc-650 dark:text-zinc-400 font-extrabold"
                  >
                    {{ spec[1] }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- Reviews Tab -->
        <section
          v-else-if="activeTab === 'reviews'"
          class="space-y-6 text-left"
        >
          <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
            <div
              class="md:col-span-4 p-6 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 text-center space-y-2"
            >
              <p
                class="text-5xl font-black text-zinc-900 dark:text-white tracking-tighter font-bold"
              >
                {{ product.rating }}
              </p>
              <div class="flex justify-center text-amber-400 mb-1">
                <span
                  v-for="star in 5"
                  :key="star"
                  class="material-symbols-outlined text-[20px]"
                  style="font-variation-settings: &quot;FILL&quot; 1"
                >star</span>
              </div>
              <p
                class="text-[10px] font-black uppercase tracking-wider text-zinc-450 dark:text-zinc-500 font-bold"
              >
                Середня оцінка
              </p>
            </div>
            <div
              class="md:col-span-8 p-6 border border-zinc-200 dark:border-zinc-800 rounded-xl flex flex-col justify-center space-y-3"
            >
              <div
                v-for="rating in [
                  { label: '5 зірок', value: '85%' },
                  { label: '4 зірки', value: '10%' },
                  { label: '3 зірки', value: '5%' },
                ]"
                :key="rating.label"
                class="flex items-center gap-4 text-xs"
              >
                <span
                  class="font-extrabold w-12 text-right text-zinc-600 dark:text-zinc-400 font-semibold"
                >{{ rating.label }}</span>
                <div
                  class="flex-1 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden"
                >
                  <div
                    class="h-full bg-[#00a046]"
                    :style="{ width: rating.value }"
                  />
                </div>
                <span class="font-extrabold w-10 text-zinc-400 font-semibold">{{
                  rating.value
                }}</span>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <article
              v-for="review in reviews"
              :key="review.name"
              class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm space-y-3"
            >
              <div class="flex items-center justify-between gap-4">
                <div>
                  <h4
                    class="font-black text-sm text-zinc-900 dark:text-white leading-snug font-bold"
                  >
                    {{ review.title }}
                  </h4>
                  <p
                    class="text-[10px] font-bold uppercase tracking-wider text-zinc-450 dark:text-zinc-500 mt-0.5"
                  >
                    {{ review.name }}
                  </p>
                </div>
                <div class="flex text-amber-400">
                  <span
                    v-for="star in 5"
                    :key="star"
                    class="material-symbols-outlined text-[16px]"
                    style="font-variation-settings: &quot;FILL&quot; 1"
                  >star</span>
                </div>
              </div>
              <p
                class="text-xs md:text-sm text-zinc-650 dark:text-zinc-400 leading-relaxed"
              >
                {{ review.text }}
              </p>
            </article>
          </div>
        </section>

        <!-- Support Tab -->
        <section
          v-else
          class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left"
        >
          <div
            class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm space-y-3 text-center md:text-left"
          >
            <span
              class="material-symbols-outlined text-[#00a046] text-[28px]"
            >support_agent</span>
            <h3 class="font-black text-sm text-zinc-900 dark:text-white font-bold">
              Консультація експерта
            </h3>
            <p
              class="text-xs text-zinc-550 dark:text-zinc-400 leading-relaxed"
            >
              Наші спеціалісти готові допомогти з налаштуванням та
              перенесенням даних.
            </p>
          </div>
          <div
            class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm space-y-3 text-center md:text-left"
          >
            <span
              class="material-symbols-outlined text-[#00a046] text-[28px]"
            >local_shipping</span>
            <h3 class="font-black text-sm text-zinc-900 dark:text-white font-bold">
              Доставка і заміна
            </h3>
            <p
              class="text-xs text-zinc-550 dark:text-zinc-400 leading-relaxed"
            >
              Безкоштовна доставка, можливість примірки та швидкий обмін
              товару.
            </p>
          </div>
          <div
            class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm space-y-3 text-center md:text-left"
          >
            <span
              class="material-symbols-outlined text-[#00a046] text-[28px]"
            >workspace_premium</span>
            <h3 class="font-black text-sm text-zinc-900 dark:text-white font-bold">
              Програма захисту
            </h3>
            <p
              class="text-xs text-zinc-550 dark:text-zinc-400 leading-relaxed"
            >
              Можливість продовження гарантії та страхування від випадкових
              пошкоджень.
            </p>
          </div>
        </section>
      </div>

      <!-- Guarantees Sidebar -->
      <aside class="lg:col-span-4 space-y-6 text-left">
        <div
          class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm space-y-5"
        >
          <h4
            class="font-black text-[10px] uppercase tracking-wider text-zinc-450 dark:text-zinc-500 flex items-center gap-2 font-bold"
          >
            <span
              class="material-symbols-outlined text-[#00a046] text-[18px]"
            >verified</span>
            Гарантії якості
          </h4>
          <ul class="space-y-4 text-xs">
            <li
              v-for="item in qualityGuarantees"
              :key="item.title"
              class="flex gap-4 items-start"
            >
              <span
                class="material-symbols-outlined text-[#00a046] mt-0.5"
              >{{ item.icon }}</span>
              <div class="space-y-0.5">
                <p class="font-black text-zinc-850 dark:text-zinc-100 font-bold">
                  {{ item.title }}
                </p>
                <p class="text-zinc-500 dark:text-zinc-400">
                  {{ item.text }}
                </p>
              </div>
            </li>
          </ul>
        </div>

        <div
          class="p-6 bg-emerald-500/10 dark:bg-emerald-950/20 text-[#00a046] rounded-xl border border-emerald-500/20 shadow-sm space-y-4 relative overflow-hidden"
        >
          <div class="relative z-10 space-y-3">
            <h4
              class="font-black text-sm uppercase tracking-wider text-[#00a046] font-bold"
            >
              Технічний радник
            </h4>
            <p
              class="text-xs text-zinc-650 dark:text-zinc-400 leading-relaxed"
            >
              Наші інженери допоможуть обрати ідеальну конфігурацію під ваші
              потреби в режимі реального часу.
            </p>
            <button
              class="w-full bg-[#00a046] hover:bg-[#00b050] text-white py-2.5 rounded-lg font-extrabold text-xs flex items-center justify-center gap-1.5 transition-all shadow-sm font-bold"
              type="button"
            >
              <span class="material-symbols-outlined text-[16px]">chat_bubble</span>
              ПОЧАТИ ЧАТ
            </button>
          </div>
        </div>
      </aside>
    </div>
  </section>
</template>

<script setup lang="ts">
interface TabItem {
  id: string;
  label: string;
}

interface QualityGuarantee {
  icon: string;
  title: string;
  text: string;
}

interface Review {
  name: string;
  title: string;
  text: string;
}

defineProps<{
  activeTab: string;
  tabs: TabItem[];
  product: any;
  galleryImages: Array<{ label: string; src: string }>;
  qualityGuarantees: QualityGuarantee[];
  reviews: Review[];
}>();

defineEmits<{
  (e: "change-tab", tabId: string): void;
}>();
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
