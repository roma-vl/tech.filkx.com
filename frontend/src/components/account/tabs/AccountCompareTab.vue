<script setup>
import { ref, computed } from "vue";
import { store } from "@/store.js";

const selectedCategory = ref(null);

// Group compared items by category
const comparedByCategory = computed(() => {
  const groups = {};
  store.compare.forEach((item) => {
    const cat = item.category || "Різне";
    if (!groups[cat]) {
      groups[cat] = [];
    }
    groups[cat].push(item);
  });
  return groups;
});

// Compile dynamic specs for the selected category
const dynamicSpecLabels = computed(() => {
  if (!selectedCategory.value) return [];
  const items = comparedByCategory.value[selectedCategory.value] || [];
  const labels = new Set();
  items.forEach((item) => {
    if (item.specs && Array.isArray(item.specs)) {
      item.specs.forEach(([label]) => labels.add(label));
    }
  });
  return Array.from(labels);
});

const getSpecValue = (product, label) => {
  if (!product.specs || !Array.isArray(product.specs)) return "—";
  const found = product.specs.find(([l]) => l === label);
  return found ? found[1] : "—";
};

const removeCategoryComparison = (categoryName) => {
  const itemsToRemove = comparedByCategory.value[categoryName] || [];
  itemsToRemove.forEach((item) => {
    store.removeFromCompare(item.id);
  });
  if (selectedCategory.value === categoryName) {
    selectedCategory.value = null;
  }
};

const formatPrice = (price) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};
</script>

<template>
  <div class="space-y-6 animate-fade font-sans select-none">
    <!-- CATEGORY LIST VIEW (Default State) -->
    <div v-if="!selectedCategory">
      <div
        v-if="store.compare.length > 0"
        class="space-y-4"
      >
        <h2
          class="text-xl font-black text-zinc-950 dark:text-white tracking-tight mb-6"
        >
          Списки порівнянь
        </h2>

        <div class="grid grid-cols-1 gap-4">
          <div
            v-for="(products, catName) in comparedByCategory"
            :key="catName"
            class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-6 hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-6 cursor-pointer"
            @click="selectedCategory = catName"
          >
            <div class="space-y-4 flex-1">
              <div>
                <h3
                  class="font-extrabold text-base md:text-lg text-zinc-850 dark:text-zinc-150 group-hover:text-[#00a046] transition-colors"
                >
                  {{ catName }}
                </h3>
                <p
                  class="text-xs text-zinc-450 dark:text-zinc-500 font-bold mt-1"
                >
                  Кількість товарів: {{ products.length }}
                </p>
              </div>

              <!-- Thumbnails list -->
              <div class="flex flex-wrap gap-2.5">
                <div
                  v-for="prod in products"
                  :key="prod.id"
                  class="w-14 h-14 bg-white dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 rounded-lg p-1.5 flex items-center justify-center relative hover:scale-105 transition-transform"
                >
                  <img
                    :src="prod.image"
                    :alt="prod.name"
                    class="w-full h-full object-contain"
                  >
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div
              class="flex items-center gap-3 self-end md:self-center"
              @click.stop
            >
              <button
                class="p-2.5 rounded-lg border border-zinc-150 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-850 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-250 transition-colors"
                title="Поділитися списком"
                type="button"
                @click="
                  store.addToast(
                    'Посилання скопійовано в буфер обміну',
                    'success',
                  )
                "
              >
                <span class="material-symbols-outlined text-[20px]">share</span>
              </button>

              <button
                class="p-2.5 rounded-lg border border-zinc-150 dark:border-zinc-800 hover:bg-rose-50 dark:hover:bg-rose-950/20 text-zinc-450 hover:text-rose-550 transition-colors"
                title="Видалити весь список"
                type="button"
                @click="removeCategoryComparison(catName)"
              >
                <span class="material-symbols-outlined text-[20px]">delete</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div
        v-else
        class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-12 text-center shadow-sm"
      >
        <span
          class="material-symbols-outlined text-[48px] text-zinc-350 dark:text-zinc-600 mb-4"
        >compare_arrows</span>
        <h3 class="font-extrabold text-lg text-zinc-850 dark:text-zinc-150">
          Немає товарів для порівняння
        </h3>
        <p
          class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 max-w-sm mx-auto mt-2"
        >
          Додайте товари до порівняння, натиснувши кнопку порівняння на картках
          товарів.
        </p>
        <a
          href="/catalog"
          class="inline-block bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs md:text-sm py-3 px-6 rounded-lg transition-all mt-6 shadow-sm"
        >Перейти до каталогу</a>
      </div>
    </div>

    <!-- CATEGORY COMPARISON TABLE VIEW -->
    <div
      v-else
      class="space-y-6"
    >
      <div class="flex items-center justify-between gap-4">
        <button
          class="flex items-center gap-1.5 text-zinc-550 dark:text-zinc-400 hover:text-[#00a046] transition-colors font-extrabold text-xs md:text-sm"
          type="button"
          @click="selectedCategory = null"
        >
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          До списків порівнянь
        </button>

        <h2
          class="text-lg md:text-xl font-black text-zinc-950 dark:text-white tracking-tight"
        >
          Порівняння: {{ selectedCategory }}
        </h2>
      </div>

      <div
        class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm"
      >
        <table class="w-full min-w-[800px] border-collapse text-left text-sm">
          <thead>
            <tr
              class="bg-zinc-50 dark:bg-zinc-850/50 border-b border-zinc-200 dark:border-zinc-800 text-zinc-400 dark:text-zinc-500 font-extrabold uppercase text-[10px] tracking-wider"
            >
              <th class="p-5 w-1/4">
                Параметри
              </th>
              <th
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5 relative text-center"
              >
                <button
                  class="absolute top-3 right-3 text-zinc-400 hover:text-rose-500 transition-colors"
                  type="button"
                  @click="
                    store.removeFromCompare(product.id);
                    if (
                      (comparedByCategory[selectedCategory] || []).length <= 1
                    )
                      selectedCategory = null;
                  "
                >
                  <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
                <span class="inline-block py-1">Товар</span>
              </th>
            </tr>
          </thead>
          <tbody
            class="divide-y divide-zinc-250/60 dark:divide-zinc-800/60 text-zinc-700 dark:text-zinc-350"
          >
            <!-- Image Row -->
            <tr>
              <td
                class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/20 text-zinc-900 dark:text-white"
              >
                Зображення
              </td>
              <td
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5"
              >
                <div class="flex flex-col gap-3 items-center">
                  <img
                    :src="product.image"
                    :alt="product.name"
                    class="w-20 h-20 object-contain mx-auto bg-white rounded-lg border border-zinc-150 dark:border-zinc-850 p-2"
                  >
                  <h4
                    class="font-extrabold text-center text-xs md:text-sm line-clamp-2 text-zinc-800 dark:text-zinc-200 max-w-[180px]"
                  >
                    {{ product.name }}
                  </h4>
                </div>
              </td>
            </tr>
            <!-- Price Row -->
            <tr>
              <td
                class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/20 text-zinc-900 dark:text-white"
              >
                Ціна
              </td>
              <td
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5 font-black text-[#00a046] text-center text-sm md:text-base"
              >
                {{ formatPrice(product.price) }}
              </td>
            </tr>
            <!-- Rating Row -->
            <tr>
              <td
                class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/20 text-zinc-900 dark:text-white"
              >
                Рейтинг
              </td>
              <td
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5 text-center"
              >
                <div
                  class="flex items-center justify-center gap-1.5 text-amber-400"
                >
                  <span
                    class="material-symbols-outlined text-[16px]"
                    style="font-variation-settings: &quot;FILL&quot; 1"
                  >star</span>
                  <span
                    class="font-extrabold text-zinc-650 dark:text-zinc-350 text-xs md:text-sm"
                  >
                    {{ product.rating || "4.8" }} ({{ product.reviews || 0 }})
                  </span>
                </div>
              </td>
            </tr>
            <!-- Dynamic Specs Rows -->
            <tr
              v-for="label in dynamicSpecLabels"
              :key="label"
            >
              <td
                class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/20 text-zinc-900 dark:text-white"
              >
                {{ label }}
              </td>
              <td
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5 text-center text-xs md:text-sm font-semibold text-zinc-800 dark:text-zinc-200"
              >
                {{ getSpecValue(product, label) }}
              </td>
            </tr>
            <!-- Description Row -->
            <tr>
              <td
                class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/20 text-zinc-900 dark:text-white"
              >
                Опис
              </td>
              <td
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5 text-zinc-500 dark:text-zinc-400 text-xs md:text-sm leading-relaxed max-w-[250px] text-center"
              >
                {{ product.description }}
              </td>
            </tr>
            <!-- Actions Row -->
            <tr>
              <td class="p-5 bg-zinc-50/50 dark:bg-zinc-850/20" />
              <td
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5 text-center"
              >
                <button
                  class="bg-[#00a046] hover:bg-[#00b050] text-white px-4 py-2.5 rounded-lg font-extrabold text-xs transition-all uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm"
                  @click="store.addToCart(product)"
                >
                  <span
                    class="material-symbols-outlined text-[16px] md:text-[18px]"
                  >shopping_cart</span>
                  У кошик
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.25s ease-out forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
