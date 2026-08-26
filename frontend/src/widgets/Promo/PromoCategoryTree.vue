<script setup lang="ts">
// Recursive by filename - Vue 3 <script setup> SFCs can reference themselves
// by their own inferred component name, so this renders itself for `children`
// without needing to register/import anything extra.
import { pickLocalized, getCategoryIcon } from "@/shared/utils/categoryMapper";
import type { PromoCategoryTreeNode } from "@/entities/product/lib/derivePromoCategories";

const props = withDefaults(
  defineProps<{
    nodes: PromoCategoryTreeNode[];
    depth?: number;
    selectedCategory: string;
    expandedSlugs: Set<string>;
    locale: string;
  }>(),
  { depth: 0 },
);

const emit = defineEmits<{
  (e: "select", slug: string): void;
  (e: "toggle", slug: string): void;
}>();

const isExpanded = (slug: string) => props.expandedSlugs.has(slug);
</script>

<template>
  <div>
    <div v-for="node in nodes" :key="node.slug">
      <button
        type="button"
        class="w-full flex items-center gap-2 py-2 pr-3 text-sm font-medium transition-all"
        :class="
          selectedCategory === node.slug
            ? 'bg-emerald-50 dark:bg-emerald-950/20 text-[#00a046] font-extrabold'
            : 'text-zinc-650 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800'
        "
        :style="{ paddingLeft: `${12 + depth * 16}px` }"
        @click="emit('select', node.slug)"
      >
        <span
          v-if="node.children.length"
          class="material-symbols-outlined text-[16px] shrink-0 transition-transform"
          :class="{ '-rotate-90': !isExpanded(node.slug) }"
          @click.stop="emit('toggle', node.slug)"
          >expand_more</span
        >
        <span v-else class="w-4 shrink-0" />

        <span
          v-if="depth === 0"
          class="material-symbols-outlined text-[17px] shrink-0"
          >{{ getCategoryIcon(node.slug) }}</span
        >

        <span class="truncate flex-1 text-left">{{
          pickLocalized(node.name, locale)
        }}</span>
        <span
          class="text-xs bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 font-bold shrink-0"
          >{{ node.count }}</span
        >
      </button>

      <PromoCategoryTree
        v-if="node.children.length && isExpanded(node.slug)"
        :nodes="node.children"
        :depth="depth + 1"
        :selected-category="selectedCategory"
        :expanded-slugs="expandedSlugs"
        :locale="locale"
        @select="emit('select', $event)"
        @toggle="emit('toggle', $event)"
      />
    </div>
  </div>
</template>
