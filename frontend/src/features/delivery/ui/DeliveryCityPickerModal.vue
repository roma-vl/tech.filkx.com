<template>
  <div
    v-if="props.isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade"
    @click.self="closeModal"
  >
    <div
      class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg max-w-sm w-full shadow-2xl overflow-hidden flex flex-col relative"
    >
      <div
        class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 px-5 py-3.5 flex justify-between items-center"
      >
        <h3 class="font-extrabold text-sm text-zinc-900 dark:text-white">
          {{ t("product.purchase.delivery.cityPickerTitle") }}
        </h3>
        <button
          class="text-zinc-400 hover:text-zinc-650 dark:hover:text-zinc-250 flex items-center justify-center"
          @click="closeModal"
        >
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>

      <div class="p-5">
        <div class="relative">
          <input
            v-model="query"
            type="text"
            autocomplete="off"
            :placeholder="t('product.purchase.delivery.cityPickerPlaceholder')"
            class="w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary text-zinc-900 dark:text-white"
            @input="isDropdownOpen = true"
            @focus="isDropdownOpen = true"
          />
          <ul
            v-if="
              isDropdownOpen &&
              (isSearching || results.length || query.trim().length >= 2)
            "
            class="mt-1 bg-white dark:bg-zinc-900 rounded-lg shadow-xl border border-zinc-200 dark:border-zinc-800 overflow-hidden max-h-56 overflow-y-auto"
          >
            <li
              v-if="isSearching"
              class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400"
            >
              {{ t("product.purchase.delivery.citySearching") }}
            </li>
            <template v-else-if="results.length">
              <li
                v-for="city in results"
                :key="city.ref"
                class="px-4 py-2 text-sm text-zinc-800 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-800/60 cursor-pointer transition-colors"
                @mousedown.prevent="selectCity(city)"
              >
                {{ city.name }}
              </li>
            </template>
            <li
              v-else
              class="px-4 py-2 text-sm text-zinc-500 dark:text-zinc-400"
            >
              {{ t("product.purchase.delivery.cityNoResults") }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import {
  useDeliveryCitySearch,
  type DeliveryCityOption,
} from "../composables/useDeliveryCitySearch";

const props = defineProps<{
  isOpen: boolean;
}>();

const emit = defineEmits<{
  (e: "close"): void;
  (e: "select", city: DeliveryCityOption): void;
}>();

const { t } = useI18n();

const isDropdownOpen = ref(false);
// Only search while the modal itself is open.
const isModalOpen = computed(() => props.isOpen);

const { query, results, isSearching, reset } =
  useDeliveryCitySearch(isModalOpen);

watch(
  () => props.isOpen,
  (open) => {
    if (open) {
      reset();
      isDropdownOpen.value = false;
    }
  },
);

function closeModal() {
  emit("close");
}

function selectCity(city: DeliveryCityOption) {
  emit("select", city);
  isDropdownOpen.value = false;
  emit("close");
}
</script>
