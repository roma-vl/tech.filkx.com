<template>
  <aside
    :class="[
      'bg-white/40 dark:bg-gray-800/20 backdrop-blur-xl border-r border-white/60 dark:border-white/10 flex flex-col transition-all duration-300 shadow-xl relative z-40',
      collapsed ? 'w-20' : 'w-72',
    ]"
  >
    <div
      :class="[
        'flex items-center justify-between transition-all duration-300',
        collapsed ? 'flex-col gap-4 px-2 py-6' : 'px-6 py-8'
      ]"
    >
      <router-link
        v-if="!collapsed"
        to="/"
        class="flex items-center space-x-2"
      >
        <img
          :src="Logo"
          alt="Filkx"
          class="w-28"
        >
      </router-link>
      <button
        class="p-2.5 rounded-xl bg-white/50 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 border border-white/60 dark:border-white/10 shadow-sm transition-all duration-300 flex items-center justify-center flex-shrink-0"
        aria-label="Toggle sidebar"
        @click="$emit('toggle')"
      >
        <ChevronDoubleRightIcon
          v-if="collapsed"
          class="h-5 w-5 text-gray-700 dark:text-gray-300"
        />
        <ChevronDoubleLeftIcon
          v-else
          class="h-5 w-5 text-gray-700 dark:text-gray-300"
        />
      </button>
    </div>

    <nav class="flex-1 py-4 space-y-1 overflow-y-auto custom-scrollbar">
      <div
        v-for="group in navigationGroups"
        :key="group.id"
        class="px-4"
      >
        <div
          v-for="item in group.items"
          :key="item.id"
          class="mb-1"
        >
          <AppNavDropdown
            v-if="item.items"
            :item="item"
            :collapsed="collapsed"
          />
          <AppNavItem
            v-else
            :item="item"
            :collapsed="collapsed"
            @click="collapsed && $emit('toggle')"
          />
        </div>
        <div
          v-if="group.id !== 'account' || group.id !== 'admin'"
          class="my-4 border-t border-white/10"
        />
      </div>
    </nav>
    <Version />
  </aside>
</template>

<script setup>
import { useI18n } from "vue-i18n";
import {
  ChevronDoubleLeftIcon,
  ChevronDoubleRightIcon,
} from "@heroicons/vue/24/outline";
import AppNavItem from "./AppNavItem.vue";
import AppNavDropdown from "./AppNavDropdown.vue";
import { useNavigation } from "@/layouts/appllication/useNavigation.js";
import Logo from "@/assets/images/logo/logo.png";
// import TourReminderBanner from "@/components/application/features/onboarding/TourReminderBanner.vue";
// import TrialActivationBanner from "@/components/application/ui/Banners/TrialActivationBanner.vue";
import Version from "@/layouts/appllication/components/Version.vue";

const { t } = useI18n();

defineProps({
  collapsed: {
    type: Boolean,
    required: true,
  },
});

defineEmits(["toggle", "start-tour"]);

const { navigationGroups } = useNavigation();
</script>
