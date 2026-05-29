<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm relative z-20 mb-6"
  >
    <div class="p-4 flex flex-col lg:flex-row items-center justify-between gap-4">
      <div class="flex-1 w-full flex flex-col sm:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1">
          <AppInput
            :model-value="search"
            :placeholder="t('admin.logs.search_placeholder')"
            class="w-full"
            @update:model-value="$emit('update:search', $event)"
          >
            <template #prepend>
              <MagnifyingGlassIcon class="h-5 w-5 text-gray-400"/>
            </template>
          </AppInput>
        </div>

        <!-- Domain Filter -->
        <div class="w-full sm:w-48">
          <AppSelect
            :model-value="domain"
            :options="domainOptions"
            class="w-full"
            @update:model-value="$emit('update:domain', $event)"
          />
        </div>

        <!-- Per Page Filter -->
        <div class="w-full sm:w-40">
          <AppSelect
            :model-value="perPage"
            :options="perPageOptions"
            class="w-full"
            @update:model-value="$emit('update:perPage', $event)"
          />
        </div>
      </div>

      <div class="flex items-center gap-3 self-end lg:self-auto">
        <AppButton
          @click="$emit('refresh')"
        >
          <template #prefix>
            <ArrowPathIcon class="w-4 h-4 mr-2"/>
          </template>
          {{ t("admin.logs.refresh") }}
        </AppButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import {computed} from "vue";
import {useI18n} from "vue-i18n";
import {ArrowPathIcon, MagnifyingGlassIcon,} from "@heroicons/vue/24/outline";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const {t} = useI18n();

defineProps({
  search: String,
  domain: String,
  perPage: [Number, String],
});

defineEmits(["update:search", "update:domain", "update:perPage", "refresh"]);

const domains = ["security", "billing", "content", "system", "team"];

const domainOptions = computed(() => [
  {id: "", name: t("admin.logs.filter_all_domains")},
  ...domains.map(d => ({
    id: d,
    name: t(`admin.logs.domains.${d}`)
  }))
]);

const perPageOptions = computed(() => [
  {id: 20, name: t("admin.logs.filters.per_page_options.20")},
  {id: 50, name: t("admin.logs.filters.per_page_options.50")},
  {id: 100, name: t("admin.logs.filters.per_page_options.100")}
]);
</script>
