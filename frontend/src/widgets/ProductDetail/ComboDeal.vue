<template>
  <section class="mt-16">
    <div
      class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4"
    >
      <div class="text-left">
        <h2
          class="font-extrabold text-xl md:text-2xl text-zinc-900 dark:text-white tracking-tight font-bold"
        >
          Краща ціна разом
        </h2>
        <p class="text-xs text-zinc-500 mt-2">
          Ми підібрали сумісні аксесуари для максимальної вигоди.
        </p>
      </div>
    </div>

    <div
      class="bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-800 p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-8"
    >
      <!-- Bundle items list -->
      <div
        class="flex flex-wrap items-center justify-center lg:justify-start gap-6 flex-1"
      >
        <template
          v-for="(item, index) in bundleItems"
          :key="item.id"
        >
          <div
            class="relative w-36 text-center transition-all bg-white dark:bg-zinc-850 p-4 rounded-lg border border-zinc-100 dark:border-zinc-800"
            :class="{
              'opacity-100 ring-1 ring-emerald-500/20':
                item.locked || selectedBundleIds.includes(item.id),
              'opacity-60':
                !item.locked && !selectedBundleIds.includes(item.id),
            }"
          >
            <button
              v-if="!item.locked"
              class="absolute -right-2 -top-2 z-10 flex w-7 h-7 items-center justify-center rounded-full bg-zinc-900 text-white shadow-sm"
              type="button"
              @click="$emit('toggle-item', item)"
            >
              <span class="material-symbols-outlined text-[16px]">{{
                selectedBundleIds.includes(item.id) ? "check" : "add"
              }}</span>
            </button>
            <div
              class="aspect-square bg-white rounded-md p-2 flex items-center justify-center mb-3"
            >
              <img
                :alt="item.name"
                class="max-h-[80px] object-contain"
                :src="item.image"
              >
            </div>
            <div class="space-y-1">
              <p
                class="text-[10px] font-black uppercase truncate text-zinc-800 dark:text-zinc-200 font-bold"
              >
                {{ item.name }}
              </p>
              <p
                class="text-[9px] text-zinc-450 dark:text-zinc-500 font-bold"
              >
                {{ item.category }}
              </p>
              <p class="text-xs font-black text-[#00a046] font-bold">
                {{ formatPrice(item.price) }}
              </p>
            </div>
          </div>

          <span
            v-if="index < bundleItems.length - 1"
            class="material-symbols-outlined text-zinc-300 dark:text-zinc-700 text-2xl font-light"
          >add</span>
        </template>
      </div>

      <!-- Bundle summary calculator -->
      <div
        class="lg:w-72 border-t lg:border-t-0 lg:border-l border-zinc-200 dark:border-zinc-800 pt-6 lg:pt-0 lg:pl-8 space-y-4 text-left"
      >
        <div class="space-y-1">
          <p
            class="text-[10px] font-black uppercase tracking-wider text-zinc-450 dark:text-zinc-500 font-bold"
          >
            Загальна вартість:
          </p>
          <p class="text-2xl font-black text-[#00a046] tracking-tight font-bold">
            {{ formatPrice(bundleTotal) }}
          </p>
          <p class="text-[10px] text-zinc-400 dark:text-zinc-550 font-bold">
            Окремо: {{ formatPrice(bundleSubtotal) }}
          </p>
          <p
            class="bg-rose-100 dark:bg-rose-955/30 text-rose-600 dark:text-rose-400 font-black text-[9px] px-2 py-0.5 rounded inline-block tracking-wider uppercase font-bold"
          >
            Економія: {{ formatPrice(bundleSavings) }}
          </p>
        </div>
        <button
          class="w-full bg-[#00a046] hover:bg-[#00b050] text-white px-6 py-3 rounded-md font-extrabold text-xs transition-all uppercase tracking-wider shadow-sm font-bold"
          type="button"
          @click="$emit('add-bundle')"
        >
          Додати комплект до кошика
        </button>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
interface BundleItem {
  id: string;
  name: string;
  category: string;
  price: number;
  locked?: boolean;
  image: string;
}

defineProps<{
  bundleItems: BundleItem[];
  selectedBundleIds: string[];
  bundleTotal: number;
  bundleSubtotal: number;
  bundleSavings: number;
  formatPrice: (p: number) => string;
}>();

defineEmits<{
  (e: "toggle-item", item: BundleItem): void;
  (e: "add-bundle"): void;
}>();
</script>
