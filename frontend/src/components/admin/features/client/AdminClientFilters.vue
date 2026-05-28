<template>
  <div class="space-y-4">
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4"
    >
      <div class="flex flex-1 items-center gap-3">
        <div class="flex-1 max-w-md">
          <AppInput
            :model-value="searchQuery"
            :placeholder="$t('admin.users.search')"
            :icon="MagnifyingGlassIcon"
            @update:model-value="$emit('update:searchQuery', $event)"
          />
        </div>
        <AppButton
          variant="secondary"
          class="!p-2.5"
          :class="{
            'ring-2 ring-primary-500 !bg-primary-50 dark:!bg-primary-900/20 !border-primary-200 dark:!border-primary-800':
              showFilters,
          }"
          :title="$t('admin.users.filters.title')"
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

      <div class="flex items-center gap-3">
        <AppButton
          variant="secondary"
          @click="$emit('export')"
        >
          <template #prefix>
            <ArrowDownTrayIcon class="w-4 h-4" />
          </template>
          {{ $t("admin.users.exportCsv") }}
        </AppButton>
        <AppButton
          variant="primary"
          @click="$emit('add')"
        >
          <template #prefix>
            <PlusIcon class="w-4 h-4 stroke-[3px]" />
          </template>
          {{ $t("admin.users.addNew") }}
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
            :model-value="filters.plan"
            :label="$t('admin.users.filters.planType')"
            :options="planOptions"
            @update:model-value="updateFilter('plan', $event)"
          />

          <AppSelect
            :model-value="filters.status"
            :label="$t('admin.users.filters.status')"
            :options="statusOptions"
            @update:model-value="updateFilter('status', $event)"
          />

          <AppDatePicker
            :model-value="filters.dateFrom"
            :label="$t('admin.users.filters.fromDate')"
            @update:model-value="updateFilter('dateFrom', $event)"
          />

          <AppDatePicker
            :model-value="filters.dateTo"
            :label="$t('admin.users.filters.toDate')"
            @update:model-value="updateFilter('dateTo', $event)"
          />
        </div>

        <div
          class="flex items-center justify-between pt-6 border-t border-gray-100 dark:border-gray-700/50"
        >
          <AppToggle
            :model-value="filters.withDeleted"
            active-color="primary"
            @update:model-value="updateFilter('withDeleted', $event)"
          >
            <span
              class="text-xs font-black text-gray-500 uppercase tracking-widest leading-none"
            >
              {{ $t("admin.users.filters.includeDeleted") }}
            </span>
          </AppToggle>

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
import {computed, onMounted, ref} from "vue";
import {ArrowDownTrayIcon, FunnelIcon, MagnifyingGlassIcon, PlusIcon,} from "@heroicons/vue/24/outline";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppToggle from "@/components/admin/ui/Form/AppToggle.vue";
import AppDatePicker from "@/components/admin/ui/Form/AppDatePicker.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import {useI18n} from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  searchQuery: {
    type: String,
    required: true,
  },
  filters: {
    type: Object,
    required: true,
  },
  availablePlans: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits([
  "update:searchQuery",
  "update:filters",
  "export",
  "add",
  "reset",
]);

const showFilters = ref(false);

const planOptions = computed(() => [
  { id: "", name: t("admin.users.filters.allPlans") },
  ...props.availablePlans.map((plan) => ({id: plan, name: plan})),
]);

const statusOptions = computed(() => [
  { id: "", name: t("admin.users.filters.allStatuses") },
  { id: "active", name: t("admin.users.status.active") },
  { id: "trial", name: t("admin.users.status.trial") },
  { id: "suspended", name: t("admin.users.status.suspended") },
  { id: "expired", name: t("admin.users.status.expired") },
  { id: "deleted", name: t("admin.users.status.deleted") },
]);

const activeFiltersCount = computed(() => {
  let count = 0;
  if (props.filters.plan) count++;
  if (props.filters.status) count++;
  if (props.filters.dateFrom) count++;
  if (props.filters.dateTo) count++;
  if (props.filters.withDeleted) count++;
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
