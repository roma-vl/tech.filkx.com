<template>
  <nav
    v-if="navItems.length > 1"
    class="max-w-container-max mx-auto px-4 md:px-8 pb-4 font-sans "
  >
    <div
      class="flex items-stretch gap-4 md:gap-6 overflow-x-auto no-scrollbar pb-1 -mx-1 px-1"
    >
      <button
        v-for="item in navItems"
        :key="item.slug || 'all'"
        class="flex flex-col items-center gap-2 flex-shrink-0 w-[72px] group"
        @click="emit('select-category', item.slug)"
      >
        <span
          :class="
            isActive(item)
              ? 'bg-emerald-50 dark:bg-emerald-900/20 ring-2 ring-[#00a046] text-[#00a046]'
              : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 group-hover:bg-zinc-200 dark:group-hover:bg-zinc-700 group-hover:text-zinc-600 dark:group-hover:text-zinc-300'
          "
          class="w-14 h-14 rounded-full flex items-center justify-center transition-all mt-4"
        >
          <span class="material-symbols-outlined text-[24px]">{{
            item.icon
          }}</span>
        </span>
        <span
          :class="
            isActive(item)
              ? 'text-[#00a046]'
              : 'text-zinc-500 dark:text-zinc-400 group-hover:text-zinc-700 dark:group-hover:text-zinc-200'
          "
          class="text-[11px] font-semibold text-center leading-tight line-clamp-2"
        >
          {{ item.name }}
        </span>
      </button>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { getCategoryIcon } from "@/shared/utils/categoryMapper";

const props = defineProps<{
  selectedCategory: string;
  currentCategoryPath: any[];
}>();

const emit = defineEmits<{
  "select-category": [slug: string];
}>();

const { t } = useI18n();

const localizedName = (cat: any): string =>
  cat.name?.uk || cat.name?.en || cat.name || "";

interface NavItem {
  slug: string;
  name: string;
  icon: string;
}

const toNavItem = (cat: any): NavItem => ({
  slug: cat.slug,
  name: localizedName(cat),
  icon: getCategoryIcon(cat.slug),
});

// Top-level (depth 1) categories are already browsable via the header mega
// menu, so this nav only ever surfaces depth 2+ - bare /catalog (no category
// selected) and a depth-1 leaf category (no children, so no parent to fall
// back to) both render nothing here rather than listing depth-1 categories.
//
// Below a category with children of its own, show those children. Below a
// leaf, the useful next step is jumping sideways to a sibling, not down - so
// the nav falls back to the parent's children instead of showing nothing,
// as long as that parent is itself depth 2+.
const navItems = computed<NavItem[]>(() => {
  const path = props.currentCategoryPath;
  const current = path[path.length - 1];
  if (!current) return [];

  if (current.children && current.children.length > 0) {
    return [
      {
        slug: current.slug,
        name: t("catalog.categoryNav.allIn", { name: localizedName(current) }),
        icon: getCategoryIcon(current.slug),
      },
      ...current.children.map(toNavItem),
    ];
  }

  const parent = path[path.length - 2];
  if (parent && parent.children && parent.children.length > 0) {
    return [
      {
        slug: parent.slug,
        name: t("catalog.categoryNav.allIn", { name: localizedName(parent) }),
        icon: getCategoryIcon(parent.slug),
      },
      ...parent.children.map(toNavItem),
    ];
  }

  return [];
});

const isActive = (item: NavItem): boolean =>
  item.slug === props.selectedCategory;
</script>

<style scoped>
.no-scrollbar {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
