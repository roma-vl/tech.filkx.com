<template>
  <section v-if="bundleItems.length > 1" class="mt-14">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-3">
      <div>
        <span class="text-xs font-extrabold text-[#00a046] uppercase tracking-widest">Вигідний набір</span>
        <h2 class="font-extrabold text-xl md:text-2xl text-zinc-900 dark:text-white tracking-tight mt-1">
          Краща ціна разом
        </h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
          Ми підібрали сумісні аксесуари для максимальної вигоди.
        </p>
      </div>
    </div>

    <div class="bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 p-5 md:p-6 flex flex-col lg:flex-row lg:items-center gap-8">
      <!-- Items list -->
      <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 flex-1">
        <template v-for="(item, index) in bundleItems" :key="item.id">
          <div
            class="relative w-36 text-center transition-all bg-white dark:bg-zinc-800 p-4 rounded-xl border"
            :class="{
              'border-[#00a046]/30 shadow-sm ring-1 ring-[#00a046]/10': item.locked || selectedBundleIds.includes(item.id),
              'border-zinc-100 dark:border-zinc-700 opacity-55': !item.locked && !selectedBundleIds.includes(item.id),
              'cursor-pointer hover:border-[#00a046]/30 hover:opacity-100 select-none': !item.locked,
            }"
            @click="!item.locked && $emit('toggle-item', item)"
          >
            <!-- Toggle button -->
            <button
              v-if="!item.locked"
              class="absolute -right-2 -top-2 z-10 w-6 h-6 flex items-center justify-center rounded-full text-white shadow-md transition-all"
              :class="selectedBundleIds.includes(item.id) ? 'bg-[#00a046]' : 'bg-zinc-700 dark:bg-zinc-600'"
              @click.stop="$emit('toggle-item', item)"
            >
              <span class="material-symbols-outlined text-[13px]">{{ selectedBundleIds.includes(item.id) ? 'check' : 'add' }}</span>
            </button>

            <div class="aspect-square bg-zinc-50 dark:bg-zinc-700 rounded-lg p-2 flex items-center justify-center mb-3">
              <img :alt="item.name" class="max-h-[72px] object-contain" :src="item.image" />
            </div>
            <p class="text-[11px] font-bold text-zinc-800 dark:text-zinc-200 truncate">{{ item.name }}</p>
            <p class="text-[10px] text-zinc-400 mt-0.5">{{ item.category }}</p>
            <p class="text-sm font-extrabold text-[#00a046] mt-1.5">{{ formatPrice(item.price) }}</p>
          </div>

          <span
            v-if="index < bundleItems.length - 1"
            class="material-symbols-outlined text-zinc-300 dark:text-zinc-600 text-2xl"
          >add</span>
        </template>
      </div>

      <!-- Summary -->
      <div class="lg:w-64 border-t lg:border-t-0 lg:border-l border-zinc-200 dark:border-zinc-700 pt-5 lg:pt-0 lg:pl-6 space-y-4">
        <div class="space-y-1.5">
          <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Загальна вартість</p>
          <p class="text-3xl font-extrabold text-[#00a046] tracking-tight">{{ formatPrice(bundleTotal) }}</p>
          <p class="text-xs text-zinc-400">Окремо: {{ formatPrice(bundleSubtotal) }}</p>
          <span class="inline-flex items-center gap-1 bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 font-bold text-xs px-2.5 py-1 rounded-full">
            <span class="material-symbols-outlined text-[12px]">savings</span>
            Економія {{ formatPrice(bundleSavings) }}
          </span>
        </div>
        <UiButton class="w-full" @click="$emit('add-bundle')">
          <template #prefix><span class="material-symbols-outlined text-[17px]">shopping_bag</span></template>
          Додати комплект
        </UiButton>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { UiButton } from "@/shared/ui";

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
