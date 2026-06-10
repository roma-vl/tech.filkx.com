<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm relative z-20 mb-6"
  >
    <div
      class="p-4 flex flex-col md:flex-row items-center justify-between gap-4"
    >
      <div class="flex-1 w-full">
        <AppInput
          :model-value="searchQuery"
          :placeholder="t('admin.roles.search_placeholder')"
          @update:model-value="$emit('update:searchQuery', $event)"
        >
          <template #prepend>
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </template>
        </AppInput>
      </div>

      <div class="flex items-center gap-4 self-end md:self-auto">
        <!-- View Mode Toggles -->
        <div
          class="flex p-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm"
        >
          <button
            class="p-2 rounded-lg transition-all"
            :class="
              viewMode === 'grid'
                ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30'
                : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
            "
            @click="$emit('update:viewMode', 'grid')"
          >
            <Squares2X2Icon class="w-5 h-5" />
          </button>
          <button
            class="p-2 rounded-lg transition-all"
            :class="
              viewMode === 'list'
                ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30'
                : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700'
            "
            @click="$emit('update:viewMode', 'list')"
          >
            <ListBulletIcon class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  ListBulletIcon,
  MagnifyingGlassIcon,
  Squares2X2Icon,
} from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

const { t } = useI18n();

defineProps({
  searchQuery: {
    type: String,
    default: "",
  },
  viewMode: {
    type: String,
    required: true,
  },
});

defineEmits(["update:searchQuery", "update:viewMode"]);
</script>
