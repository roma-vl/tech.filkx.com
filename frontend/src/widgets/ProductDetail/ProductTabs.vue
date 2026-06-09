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
          <!-- Summary -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
            <div class="md:col-span-4 p-6 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 text-center space-y-2">
              <p class="text-5xl font-extrabold text-zinc-900 dark:text-white tracking-tighter">{{ product.rating }}</p>
              <div class="flex justify-center text-amber-400">
                <span
                  v-for="star in 5"
                  :key="star"
                  class="material-symbols-outlined text-[20px]"
                  style="font-variation-settings: &quot;FILL&quot; 1"
                >star</span>
              </div>
              <p class="text-xs text-zinc-400 dark:text-zinc-500">Середня оцінка ({{ product.reviews }} відгуків)</p>
            </div>
            <div class="md:col-span-8 p-6 border border-zinc-200 dark:border-zinc-800 rounded-xl flex flex-col justify-center gap-3">
              <div
                v-for="r in [{ label: '5 зірок', value: '85%' }, { label: '4 зірки', value: '10%' }, { label: '3 зірки', value: '5%' }]"
                :key="r.label"
                class="flex items-center gap-3 text-sm"
              >
                <span class="font-semibold w-14 text-right text-zinc-500 dark:text-zinc-400 shrink-0">{{ r.label }}</span>
                <div class="flex-1 h-2 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                  <div class="h-full bg-[#00a046] rounded-full" :style="{ width: r.value }" />
                </div>
                <span class="font-semibold w-9 text-zinc-400 dark:text-zinc-500 shrink-0">{{ r.value }}</span>
              </div>
            </div>
          </div>

          <!-- Review cards -->
          <div class="space-y-4">
            <article
              v-for="review in reviews"
              :key="review.name"
              class="p-5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl space-y-3"
            >
              <div class="flex items-start justify-between gap-4">
                <div class="space-y-0.5">
                  <h4 class="font-bold text-sm text-zinc-900 dark:text-white">{{ review.title }}</h4>
                  <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ review.name }}</p>
                </div>
                <div class="flex text-amber-400 shrink-0">
                  <span
                    v-for="star in 5"
                    :key="star"
                    class="material-symbols-outlined text-[15px]"
                    style="font-variation-settings: &quot;FILL&quot; 1"
                  >star</span>
                </div>
              </div>
              <p class="text-[15px] text-zinc-600 dark:text-zinc-400 leading-relaxed">{{ review.text }}</p>
            </article>
          </div>
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
</template>

<script setup lang="ts">
interface TabItem { id: string; label: string }
interface QualityGuarantee { icon: string; title: string; text: string }
interface Review { name: string; title: string; text: string }

defineProps<{
  activeTab: string;
  tabs: TabItem[];
  product: any;
  galleryImages: Array<{ label: string; src: string }>;
  qualityGuarantees: QualityGuarantee[];
  reviews: Review[];
}>();

defineEmits<{ (e: "change-tab", tabId: string): void }>();

const supportCards = [
  { icon: "support_agent", title: "Консультація експерта", text: "Наші спеціалісти допоможуть з налаштуванням та перенесенням даних." },
  { icon: "local_shipping", title: "Доставка і заміна", text: "Безкоштовна доставка та швидкий обмін товару за вашим запитом." },
  { icon: "workspace_premium", title: "Програма захисту", text: "Продовження гарантії та страхування від випадкових пошкоджень." },
];
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
