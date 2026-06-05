<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";
import { productApi } from "@/shared/services/api/productApi";

interface ProductItem {
  id: string | number;
  slug?: string;
  name: string;
  brand: string;
  image: string;
  rating: number;
  reviews: number;
  price: number;
  oldPrice?: number;
  inStock: boolean;
  description: string;
  category?: string;
  specs?: Array<[string, string]>;
}

const route = useRoute();
const cartStore = useCartStore();

const selectedCategory = ref<string | null>(null);
const sharedStates = ref<Record<string, boolean>>({});

// Group compared items by category
const comparedByCategory = computed(() => {
  const groups: Record<string, ProductItem[]> = {};
  (cartStore.compare as any[]).forEach((item) => {
    const cat = item.category || "Різне";
    if (!groups[cat]) {
      groups[cat] = [];
    }
    groups[cat].push(item as ProductItem);
  });
  return groups;
});

// Compile dynamic specs for the selected category
const dynamicSpecLabels = computed(() => {
  if (!selectedCategory.value) return [];
  const items = comparedByCategory.value[selectedCategory.value] || [];
  const labels = new Set<string>();
  items.forEach((item) => {
    if (item.specs && Array.isArray(item.specs)) {
      item.specs.forEach(([label]) => labels.add(label));
    }
  });
  return Array.from(labels);
});

const getSpecValue = (product: ProductItem, label: string) => {
  if (!product.specs || !Array.isArray(product.specs)) return "—";
  const found = product.specs.find(([l]) => l === label);
  return found ? found[1] : "—";
};

const removeCategoryComparison = (categoryName: string) => {
  const itemsToRemove = comparedByCategory.value[categoryName] || [];
  itemsToRemove.forEach((item) => {
    cartStore.removeFromCompare(item.id as any);
  });
  if (selectedCategory.value === categoryName) {
    selectedCategory.value = null;
  }
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat("uk-UA", {
    style: "currency",
    currency: "UAH",
    maximumFractionDigits: 0,
  }).format(price);
};

const shareCompareList = (categoryName: string) => {
  const products = comparedByCategory.value[categoryName] || [];
  const slugs = products
    .map((p) => p.slug || p.id)
    .filter(Boolean)
    .join(",");
  if (!slugs) {
    cartStore.addToast("Немає товарів для обміну", "error");
    return;
  }

  const url = `${window.location.origin}${window.location.pathname}?tab=compare&items=${slugs}`;

  navigator.clipboard
    .writeText(url)
    .then(() => {
      cartStore.addToast("Посилання на порівняння скопійовано!", "success");
      sharedStates.value[categoryName] = true;
      setTimeout(() => {
        sharedStates.value[categoryName] = false;
      }, 2000);
    })
    .catch((err) => {
      console.error("Failed to copy URL:", err);
      cartStore.addToast("Не вдалося скопіювати посилання", "error");
    });
};

const loadSharedItems = async () => {
  const itemsQuery = route.query.items as string;
  if (!itemsQuery) return;

  const slugs = itemsQuery
    .split(",")
    .map((s) => s.trim())
    .filter(Boolean);
  let loadedCategory: string | null = null;
  let newAdded = false;

  for (const slug of slugs) {
    // Check if already in comparison list
    const exists = cartStore.compare.some(
      (p: any) => p.slug === slug || String(p.id) === slug,
    );
    if (!exists) {
      try {
        const response = await productApi.getProduct(slug);
        if (response.data && response.data.status === "success") {
          const apiProduct = response.data.data;
          if (apiProduct) {
            const mainVariant = apiProduct.variants?.[0];
            const price = mainVariant ? parseFloat(mainVariant.price) : 0;
            let image = "";
            if (
              mainVariant &&
              mainVariant.dimensions &&
              mainVariant.dimensions.images
            ) {
              const primary =
                mainVariant.dimensions.images.find(
                  (img: any) => img.isPrimary,
                ) || mainVariant.dimensions.images[0];
              if (primary && primary.url) {
                image = primary.url;
              }
            }
            if (!image) {
              image =
                "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop";
            }

            const productCategory =
              apiProduct.categories?.[0]?.name?.uk ||
              apiProduct.categories?.[0]?.name?.en ||
              "Різне";

            const mappedProduct = {
              id: apiProduct.id,
              slug: apiProduct.slug,
              name:
                apiProduct.name?.uk || apiProduct.name?.en || apiProduct.name,
              brand: apiProduct.brand?.name || "Unknown",
              image: image,
              rating: apiProduct.rating || 4.8,
              reviews: apiProduct.reviews || 0,
              price: price,
              inStock: true,
              description:
                apiProduct.description?.uk ||
                apiProduct.description?.en ||
                apiProduct.description ||
                "",
              category: productCategory,
              specs:
                apiProduct.attributeValues?.map((av: any) => {
                  const label =
                    av.attribute?.name?.uk ||
                    av.attribute?.name?.en ||
                    av.attribute?.code ||
                    "";
                  const val =
                    av.customValue ||
                    av.attributeValue?.value?.uk ||
                    av.attributeValue?.value ||
                    "";
                  return [label, val];
                }) || [],
            };

            cartStore.compare.push(mappedProduct);
            loadedCategory = productCategory;
            newAdded = true;
          }
        }
      } catch (e) {
        console.error(
          `Failed to load compared product with slug/id: ${slug}`,
          e,
        );
      }
    } else {
      const found = cartStore.compare.find(
        (p: any) => p.slug === slug || String(p.id) === slug,
      );
      if (found) {
        loadedCategory = found.category || "Різне";
      }
    }
  }

  if (loadedCategory) {
    selectedCategory.value = loadedCategory;
    if (newAdded || typeof window !== "undefined") {
      localStorage.setItem(
        "electro_compare",
        JSON.stringify(cartStore.compare),
      );
    }
  }
};

onMounted(loadSharedItems);
watch(() => route.query.items, loadSharedItems);
</script>

<template>
  <div class="space-y-6 animate-fade font-sans select-none">
    <!-- CATEGORY LIST VIEW (Default State) -->
    <div v-if="!selectedCategory">
      <div v-if="cartStore.compare.length > 0" class="space-y-4">
        <h2
          class="text-xl font-black text-zinc-955 dark:text-white tracking-tight mb-6"
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
                  class="font-extrabold text-base md:text-lg text-zinc-855 dark:text-zinc-150 group-hover:text-[#00a046] transition-colors"
                >
                  {{ catName }}
                </h3>
                <p
                  class="text-xs text-zinc-455 dark:text-zinc-500 font-bold mt-1"
                >
                  Кількість товарів: {{ products.length }}
                </p>
              </div>

              <!-- Thumbnails list -->
              <div class="flex flex-wrap gap-2.5">
                <div
                  v-for="prod in products"
                  :key="prod.id"
                  class="w-14 h-14 bg-white dark:bg-zinc-800 border border-zinc-150 dark:border-zinc-700 rounded-lg p-1.5 flex items-center justify-center relative hover:scale-105 transition-transform cursor-pointer"
                  :title="prod.name"
                  @click.stop="cartStore.viewProduct(prod as any)"
                >
                  <img
                    :src="prod.image"
                    :alt="prod.name"
                    class="w-full h-full object-contain"
                  />
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div
              class="flex items-center gap-3 self-end md:self-center"
              @click.stop
            >
              <button
                class="p-2.5 rounded-lg border transition-all flex items-center justify-center"
                :class="
                  sharedStates[catName as string]
                    ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-600 dark:text-emerald-400'
                    : 'border-zinc-150 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-850 text-zinc-555 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-250'
                "
                title="Поділитися списком"
                type="button"
                @click="shareCompareList(catName as string)"
              >
                <span
                  class="material-symbols-outlined text-[20px] transition-transform"
                  :class="sharedStates[catName as string] ? 'scale-110' : ''"
                >
                  {{ sharedStates[catName as string] ? "check" : "share" }}
                </span>
              </button>

              <button
                class="p-2.5 rounded-lg border border-zinc-150 dark:border-zinc-800 hover:bg-rose-50 dark:hover:bg-rose-955/20 text-zinc-455 hover:text-rose-550 transition-colors flex items-center justify-center"
                title="Видалити весь список"
                type="button"
                @click="removeCategoryComparison(catName as string)"
              >
                <span class="material-symbols-outlined text-[20px]"
                  >delete</span
                >
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
          >compare_arrows</span
        >
        <h3 class="font-extrabold text-lg text-zinc-855 dark:text-zinc-150">
          Немає товарів для порівняння
        </h3>
        <p
          class="text-xs md:text-sm text-zinc-455 dark:text-zinc-500 max-w-sm mx-auto mt-2"
        >
          Додайте товари до порівняння, натиснувши кнопку порівняння на картках
          товарів.
        </p>
        <a
          href="/catalog"
          class="inline-block bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs md:text-sm py-3 px-6 rounded-lg transition-all mt-6 shadow-sm"
          >Перейти до каталогу</a
        >
      </div>
    </div>

    <!-- CATEGORY COMPARISON TABLE VIEW -->
    <div v-else class="space-y-6">
      <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-200 dark:border-zinc-800"
      >
        <button
          class="flex items-center gap-1.5 text-zinc-555 dark:text-zinc-400 hover:text-[#00a046] transition-colors font-extrabold text-xs md:text-sm"
          type="button"
          @click="selectedCategory = null"
        >
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          До списків порівнянь
        </button>

        <div class="flex items-center gap-3">
          <h2
            class="text-lg md:text-xl font-black text-[#00a046] dark:text-[#00a046] tracking-tight"
          >
            Порівняння: {{ selectedCategory }}
          </h2>
          <button
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border transition-all shadow-sm text-xs font-bold"
            :class="
              sharedStates[selectedCategory]
                ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-600 dark:text-emerald-400'
                : 'border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-850 text-zinc-650 hover:text-[#00a046] dark:text-zinc-355 dark:hover:text-white'
            "
            title="Поділитися цим порівнянням"
            type="button"
            @click="shareCompareList(selectedCategory as string)"
          >
            <span
              class="material-symbols-outlined text-[16px] transition-transform"
              :class="sharedStates[selectedCategory] ? 'scale-110' : ''"
            >
              {{ sharedStates[selectedCategory] ? "check" : "share" }}
            </span>
            <span>{{
              sharedStates[selectedCategory] ? "Скопійовано!" : "Поділитися"
            }}</span>
          </button>
        </div>
      </div>

      <div
        class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm"
      >
        <table class="w-full min-w-[800px] border-collapse text-left text-sm">
          <thead>
            <tr
              class="bg-zinc-50 dark:bg-zinc-850/50 border-b border-zinc-200 dark:border-zinc-800 text-zinc-400 dark:text-zinc-500 font-extrabold uppercase text-[10px] tracking-wider"
            >
              <th
                class="p-5 w-1/4 sticky left-0 bg-zinc-50 dark:bg-zinc-850 z-20 border-r border-zinc-200 dark:border-zinc-800"
              >
                Параметри
              </th>
              <th
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5 relative text-center min-w-[220px]"
              >
                <button
                  class="absolute top-3 right-3 w-7 h-7 flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 hover:bg-rose-500/10 hover:text-rose-500 text-zinc-455 dark:text-zinc-500 rounded-full transition-all border border-zinc-200/40 dark:border-zinc-800/40"
                  type="button"
                  @click="
                    cartStore.removeFromCompare(product.id as any);
                    if (
                      (comparedByCategory[selectedCategory as string] || [])
                        .length <= 1
                    )
                      selectedCategory = null;
                  "
                >
                  <span class="material-symbols-outlined text-[15px]"
                    >close</span
                  >
                </button>
                <span
                  class="inline-block py-1 font-bold text-zinc-555 dark:text-zinc-400"
                  >Товар</span
                >
              </th>
            </tr>
          </thead>
          <tbody
            class="divide-y divide-zinc-250/60 dark:divide-zinc-800/60 text-zinc-700 dark:text-zinc-355"
          >
            <!-- Image Row -->
            <tr
              class="hover:bg-zinc-50/50 dark:hover:bg-zinc-850/20 transition-colors"
            >
              <td
                class="p-5 font-extrabold bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white sticky left-0 z-10 border-r border-zinc-200 dark:border-zinc-800"
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
                    class="w-20 h-20 object-contain mx-auto bg-white rounded-lg border border-zinc-150 dark:border-zinc-850 p-2 cursor-pointer hover:border-[#00a046]/40 transition-all hover:scale-105 duration-300"
                    @click="cartStore.viewProduct(product as any)"
                  />
                  <h4
                    class="font-extrabold text-center text-xs md:text-sm line-clamp-2 text-zinc-855 dark:text-zinc-200 max-w-[180px] cursor-pointer hover:text-[#00a046] transition-colors"
                    @click="cartStore.viewProduct(product as any)"
                  >
                    {{ product.name }}
                  </h4>
                </div>
              </td>
            </tr>
            <!-- Price Row -->
            <tr
              class="hover:bg-zinc-50/50 dark:hover:bg-zinc-850/20 transition-colors"
            >
              <td
                class="p-5 font-extrabold bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white sticky left-0 z-10 border-r border-zinc-200 dark:border-zinc-800"
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
            <tr
              class="hover:bg-zinc-50/50 dark:hover:bg-zinc-850/20 transition-colors"
            >
              <td
                class="p-5 font-extrabold bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white sticky left-0 z-10 border-r border-zinc-200 dark:border-zinc-800"
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
                    >star</span
                  >
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
              class="hover:bg-zinc-50/50 dark:hover:bg-zinc-850/20 transition-colors"
            >
              <td
                class="p-5 font-extrabold bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white sticky left-0 z-10 border-r border-zinc-200 dark:border-zinc-800"
              >
                {{ label }}
              </td>
              <td
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5 text-center text-xs md:text-sm font-semibold text-zinc-850 dark:text-zinc-200"
              >
                {{ getSpecValue(product, label) }}
              </td>
            </tr>
            <!-- Description Row -->
            <tr
              class="hover:bg-zinc-50/50 dark:hover:bg-zinc-850/20 transition-colors"
            >
              <td
                class="p-5 font-extrabold bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white sticky left-0 z-10 border-r border-zinc-200 dark:border-zinc-800"
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
            <tr
              class="hover:bg-zinc-50/50 dark:hover:bg-zinc-850/20 transition-colors"
            >
              <td
                class="p-5 bg-zinc-50 dark:bg-zinc-900 sticky left-0 z-10 border-r border-zinc-200 dark:border-zinc-800"
              />
              <td
                v-for="product in comparedByCategory[selectedCategory]"
                :key="product.id"
                class="p-5 text-center"
              >
                <button
                  class="bg-[#00a046] hover:bg-[#00b050] text-white px-4 py-2.5 rounded-lg font-extrabold text-xs transition-all uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm active:scale-95"
                  @click="cartStore.addToCart(product as any)"
                >
                  <span
                    class="material-symbols-outlined text-[16px] md:text-[18px]"
                    >shopping_cart</span
                  >
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
