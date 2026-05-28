<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden"
  >
    <div
      class="px-6 py-5 border-b border-gray-50 dark:border-gray-700/50 flex items-center justify-between"
    >
      <h2
        class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-tight"
      >
        {{ t("affiliate.dashboard.yourLinks") }}
      </h2>
      <AppButton
        variant="primary"
        size="sm"
        class="!py-2"
        @click="$emit('create')"
      >
        {{ t("affiliate.dashboard.createLink") }}
      </AppButton>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr
            class="bg-gray-50/50 dark:bg-gray-900/50 text-gray-400 text-xs uppercase font-bold tracking-widest"
          >
            <th class="px-6 py-4 font-bold">
              {{ t("affiliate.dashboard.linkName") }}
            </th>
            <th class="px-6 py-4 font-bold text-center">
              {{ t("affiliate.dashboard.clicks") }}
            </th>
            <th class="px-6 py-4 font-bold text-center">
              {{ t("affiliate.dashboard.registrations") }}
            </th>
            <th class="px-6 py-4 font-bold text-center">
              {{ t("affiliate.dashboard.sales") }}
            </th>
            <th class="px-6 py-4 font-bold text-right">
              {{ t("affiliate.dashboard.income") }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
          <tr
            v-for="link in links"
            :key="link.id"
            class="group hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors"
          >
            <td class="px-6 py-5">
              <div class="flex items-start gap-4">
                <div class="relative group/copy">
                  <button
                    class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 text-gray-400 hover:text-primary hover:border-primary/30 hover:bg-white dark:hover:bg-gray-800 transition-all shadow-sm hover:shadow-md"
                    @click="copyLink(link.url, link.id)"
                  >
                    <CopyIcon
                      v-if="copiedId !== link.id"
                      class="w-4 h-4"
                    />
                    <CheckIcon
                      v-else
                      class="w-4 h-4 text-green-500"
                    />
                  </button>
                </div>
                <div class="min-w-0 flex-1">
                  <p
                    class="font-bold text-gray-900 dark:text-white truncate text-base mb-1"
                  >
                    {{ link.name || link.slug }}
                  </p>
                  <div class="flex items-center">
                    <div
                      class="px-3 py-1 rounded-lg bg-primary-50/50 dark:bg-primary-900/10 border border-primary-100/50 dark:border-primary-900/20"
                    >
                      <span
                        class="text-xs font-mono font-bold text-primary-600 dark:text-primary-400 tracking-tight"
                      >
                        {{ link.url }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </td>
            <td
              class="px-6 py-5 text-center font-semibold text-gray-600 dark:text-gray-400"
            >
              {{ link.clicksCount }}
            </td>
            <td
              class="px-6 py-5 text-center font-semibold text-gray-600 dark:text-gray-400"
            >
              {{ link.referralsCount }}
            </td>
            <td
              class="px-6 py-5 text-center font-semibold text-gray-600 dark:text-gray-400"
            >
              {{ link.salesCount }}
            </td>
            <td class="px-6 py-5 text-right">
              <span class="font-bold text-green-600 dark:text-green-400">
                ${{ (link.income || 0).toFixed(2) }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Cards -->
    <div class="md:hidden space-y-4 p-4">
      <div
        v-for="link in links"
        :key="link.id"
        class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 border border-gray-100 dark:border-gray-700"
      >
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-bold text-gray-900 dark:text-white">
              {{ link.name || link.slug }}
            </h3>
            <p
              class="text-xs text-primary-600 dark:text-primary-400 mt-1 font-mono break-all"
            >
              {{ link.url }}
            </p>
          </div>
          <button
            class="p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 text-gray-400"
            @click="copyLink(link.url, link.id)"
          >
            <CopyIcon
              v-if="copiedId !== link.id"
              class="w-4 h-4"
            />
            <CheckIcon
              v-else
              class="w-4 h-4 text-green-500"
            />
          </button>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-500 dark:text-gray-400 text-xs uppercase">
              {{ t("affiliate.dashboard.clicks") }}
            </p>
            <p class="font-bold text-gray-900 dark:text-white">
              {{ link.clicksCount }}
            </p>
          </div>
          <div>
            <p class="text-gray-500 dark:text-gray-400 text-xs uppercase">
              {{ t("affiliate.dashboard.income") }}
            </p>
            <p class="font-bold text-green-600 dark:text-green-400">
              ${{ (link.income || 0).toFixed(2) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="!links?.length"
      class="py-12 flex flex-col items-center justify-center text-center opacity-40"
    >
      <p class="text-sm font-bold uppercase tracking-widest text-gray-400">
        {{ t("ui.no_data") }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { CopyIcon, CheckIcon } from "lucide-vue-next";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const props = defineProps({
  links: {
    type: Array,
    default: () => [],
  },
});

defineEmits(["create"]);

const { t } = useI18n();
const copiedId = ref(null);

const copyLink = async (url, id = null) => {
  try {
    await navigator.clipboard.writeText(url);
    if (id) {
      copiedId.value = id;
      setTimeout(() => {
        copiedId.value = null;
      }, 2000);
    }
  } catch (err) {
    console.error("Failed to copy:", err);
  }
};
</script>
