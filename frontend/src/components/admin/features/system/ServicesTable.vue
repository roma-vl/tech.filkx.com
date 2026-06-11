<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700/50 shadow-sm overflow-hidden"
  >
    <div
      class="p-8 border-b border-gray-50 dark:border-gray-700/50 flex items-center justify-between bg-gray-50/30 dark:bg-gray-900/10"
    >
      <div class="flex items-center gap-4">
        <div
          class="w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 flex items-center justify-center text-primary-500 shadow-sm"
        >
          <CommandLineIcon class="w-6 h-6" />
        </div>
        <div>
          <h3
            class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]"
          >
            {{ t("admin.system.services.title") }}
          </h3>
          <p
            class="text-lg font-black text-gray-900 dark:text-white tracking-tight uppercase"
          >
            {{ t("admin.system.services.modules") }}
          </p>
        </div>
      </div>
      <button
        class="flex items-center gap-3 px-6 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-primary-500 text-gray-500 hover:text-primary-600 font-bold uppercase tracking-widest text-[10px] transition-all active:scale-95 shadow-sm"
        @click="$emit('refresh')"
      >
        <ArrowPathIcon
          class="w-4 h-4"
          :class="{ 'animate-spin': loading }"
        />
        {{ t("admin.system.refresh") }}
      </button>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50/50 dark:bg-gray-900/30">
          <tr>
            <th
              class="py-5 px-8 font-black uppercase tracking-[0.2em] text-[10px] text-gray-400"
            >
              {{ t("admin.system.services.table.service") }}
            </th>
            <th
              class="py-5 px-8 font-black uppercase tracking-[0.2em] text-[10px] text-gray-400"
            >
              {{ t("admin.system.services.table.endpoint") }}
            </th>
            <th
              class="py-5 px-8 font-black uppercase tracking-[0.2em] text-[10px] text-gray-400 text-center"
            >
              {{ t("admin.system.services.table.status") }}
            </th>
            <th
              class="py-5 px-8 font-black uppercase tracking-[0.2em] text-[10px] text-gray-400 text-right"
            >
              {{ t("admin.system.services.table.load") }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
          <tr
            v-for="svc in services"
            :key="svc.name"
            class="group hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-all duration-300"
          >
            <td class="py-6 px-8">
              <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-green-500" />
                <span
                  class="font-black text-sm uppercase tracking-tight text-gray-900 dark:text-white group-hover:text-primary-500 transition-colors"
                >
                  {{ svc.name }}
                </span>
              </div>
            </td>
            <td class="py-6 px-8">
              <span
                class="text-xs font-mono font-bold text-gray-400 bg-gray-100 dark:bg-gray-900 px-3 py-1 rounded-lg border border-gray-50 dark:border-gray-800"
              >
                {{ svc.endpoint }}
              </span>
            </td>
            <td class="py-6 px-8 text-center">
              <span
                class="px-3 py-1 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 text-xs font-black uppercase tracking-widest border border-green-100 dark:border-green-800/50"
              >
                {{ svc.status }}
              </span>
            </td>
            <td class="py-6 px-8 text-right">
              <div class="flex items-center justify-end gap-2">
                <span
                  class="font-black text-sm text-gray-900 dark:text-white font-mono"
                >
                  {{ svc.latency }}
                </span>
                <span
                  class="text-[10px] font-black text-gray-400 uppercase tracking-widest"
                >ms</span>
                <div
                  class="w-24 h-1.5 bg-gray-100 dark:bg-gray-900 rounded-full overflow-hidden ml-4"
                >
                  <div
                    class="h-full bg-primary-500/50 opacity-0 group-hover:opacity-100 transition-all duration-500"
                    :style="{ width: Math.min(svc.latency / 2, 100) + '%' }"
                  />
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ArrowPathIcon, CommandLineIcon } from "@heroicons/vue/24/outline";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

defineProps({
  services: {
    type: Array,
    required: true,
  },
  loading: Boolean,
});

defineEmits(["refresh"]);
</script>
