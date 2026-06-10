<script setup>
import { useAuthStore } from "@/stores/auth";
import Dropdown from "@/shared/ui/Dropdown.vue";
import ArrowSmallDownIcon from "@/components/Icon/ArrowSmallDownIcon.vue";

const store = useAuthStore();

const changeLocale = async (locale) => {
  if (store.user?.locale === locale) return;
  await store.updateLocale(locale);
};
</script>

<template>
  <Dropdown
    align="right"
    width="48"
    :dropdown-classes="[
      'bg-gray-50 dark:bg-gray-700',
      'border border-gray-200 dark:border-gray-700',
      'shadow-xl rounded-lg',
    ]"
  >
    <template #trigger>
      <button
        type="button"
        class="inline-flex items-center gap-2 py-1 px-2 text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:text-gray-900 hover:bg-gray-100 dark:hover:text-white dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500/60 transition-all"
        aria-label="Change language"
      >
        <span class="text-sm">
          {{ $t("account.locale") }}
        </span>

        <ArrowSmallDownIcon class="w-4 h-4" />
      </button>
    </template>

    <template #content>
      <div class="py-1 z-[40]">
        <button
          class="flex items-center w-full gap-3 px-4 py-2 text-sm text-left transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
          :class="
            store.user?.locale === 'uk'
              ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
              : 'text-gray-700 dark:text-gray-300'
          "
          @click="changeLocale('uk')"
        >
          Українська
        </button>

        <button
          class="flex items-center w-full gap-3 px-4 py-2 text-sm text-left transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
          :class="
            store.user?.locale === 'en'
              ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
              : 'text-gray-700 dark:text-gray-300'
          "
          @click="changeLocale('en')"
        >
          English
        </button>
      </div>
    </template>
  </Dropdown>
</template>
