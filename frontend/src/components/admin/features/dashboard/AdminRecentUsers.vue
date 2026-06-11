<template>
  <AppCard
    :title="$t('admin.dashboard.recentUsers.title')"
    class="lg:col-span-2"
    body-class="!p-0"
  >
    <template #header>
      <div class="flex items-center justify-between w-full">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
          {{ $t("admin.dashboard.recentUsers.title") }}
        </h3>
        <router-link
          to="/admin/users"
          class="text-sm font-semibold text-primary-600 hover:text-primary-700 transition-colors"
        >
          {{ $t("admin.dashboard.recentUsers.viewAll") }}
        </router-link>
      </div>
    </template>

    <div class="divide-y divide-gray-100 dark:divide-gray-700">
      <div
        v-for="user in users"
        :key="user.email"
        class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
      >
        <div class="flex items-center gap-3">
          <div
            class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 dark:from-primary-900/40 dark:to-primary-800/40 flex items-center justify-center font-bold text-primary-600 dark:text-primary-400 uppercase shadow-sm"
          >
            {{ user.name.charAt(0) }}
          </div>
          <div>
            <p class="font-semibold text-sm text-gray-900 dark:text-gray-100">
              {{ user.name }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ user.email }}
            </p>
          </div>
        </div>
        <div class="text-right">
          <span
            class="text-xs font-medium px-2 py-1 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400 border border-blue-100 dark:border-blue-800/30"
          >
            {{ user.plan }}
          </span>
          <p
            class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider"
          >
            {{ $t("admin.dashboard.recentUsers.joined") }} {{ user.joined }}
          </p>
        </div>
      </div>

      <div
        v-if="users.length === 0"
        class="p-12 text-center"
      >
        <div
          class="bg-gray-50 dark:bg-gray-900/50 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
        >
          <UsersIcon class="w-8 h-8 text-gray-300 dark:text-gray-600" />
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
          {{ $t("admin.dashboard.recentUsers.noUsers") }}
        </p>
      </div>
    </div>
  </AppCard>
</template>

<script setup>
import AppCard from "@/components/admin/ui/AppCard.vue";
import { UsersIcon } from "@heroicons/vue/24/outline";

defineProps({
  users: {
    type: Array,
    required: true,
  },
});
</script>
