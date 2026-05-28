<template>
  <div class="space-y-4">
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4"
    >
      <div class="flex flex-1 items-center gap-3">
        <div class="flex-1 max-w-md">
          <AppInput
            :model-value="filters.search"
            :placeholder="$t('admin.accounting.filters.search')"
            :icon="MagnifyingGlassIcon"
            @update:model-value="updateFilter('search', $event)"
          />
        </div>
        <AppButton
          variant="secondary"
          class="!p-2.5"
          :class="{
            'ring-2 ring-primary-500 !bg-primary-50 dark:!bg-primary-900/20 !border-primary-200 dark:!border-primary-800':
              showFilters,
          }"
          :title="$t('common.filter')"
          @click="showFilters = !showFilters"
        >
          <FunnelIcon
            class="w-5 h-5 transition-colors"
            :class="
              showFilters
                ? 'text-primary-600'
                : 'text-gray-500 group-hover:text-primary-600'
            "
          />
          <span
            v-if="activeFiltersCount > 0"
            class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-[10px] flex items-center justify-center rounded-full font-black shadow-lg shadow-primary-500/30 ring-2 ring-white dark:ring-gray-800"
          >
            {{ activeFiltersCount }}
          </span>
        </AppButton>
      </div>
    </div>

    <transition name="expand">
      <div
        v-if="showFilters"
        class="p-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl shadow-gray-200/50 dark:shadow-none space-y-6 animate-in slide-in-from-top-2 duration-300"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <AppSelect
            :model-value="filters.status"
            :label="$t('admin.accounting.filters.status')"
            :options="statusOptions"
            optionValue="value"
            optionLabel="label"
            @update:model-value="updateFilter('status', $event)"
          />
        </div>

        <div
          class="flex items-center justify-end pt-6 border-t border-gray-100 dark:border-gray-700/50"
        >
            <AppButton
            variant="text"
            class="!text-red-500 hover:!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20 !px-4 !py-2 !rounded-xl"
            @click="$emit('reset')"
          >
            {{ $t("admin.users.filters.reset") }}
          </AppButton>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from "vue";
import {
  FunnelIcon,
  MagnifyingGlassIcon,
} from "@heroicons/vue/24/outline";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  filters: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits([
  "update:filters",
  "reset",
]);

const showFilters = ref(false);

const statusOptions = computed(() => [
  { label: t('admin.accounting.status.all'), value: '' },
  { label: t('admin.accounting.status.draft'), value: 'draft' },
  { label: t('admin.accounting.status.issued'), value: 'issued' },
  { label: t('admin.accounting.status.paid'), value: 'paid' },
  { label: t('admin.accounting.status.cancelled'), value: 'cancelled' },
]);

const activeFiltersCount = computed(() => {
  let count = 0;
  if (props.filters.status) count++;
  return count;
});

const updateFilter = (key, value) => {
  emit("update:filters", {
    ...props.filters,
    [key]: value,
  });
};

onMounted(() => {
  if (activeFiltersCount.value > 0) {
    showFilters.value = true;
  }
});
</script>

<style scoped>
.expand-enter-active,
.expand-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  max-height: 400px;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-10px);
}
</style>
