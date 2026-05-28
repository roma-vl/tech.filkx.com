<template>
  <div v-if="subscriptionStore.isActive" class="mb-8">
    <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">
      {{ t('dashboard.usage.title', 'Account Usage') }}
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- Storage Card -->
      <div class="relative overflow-hidden bg-white/40 dark:bg-gray-800/20 backdrop-blur-xl rounded-[2rem] border border-white/60 dark:border-white/10 p-6 sm:p-8 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center border shadow-sm bg-blue-50 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-500/30">
              <CircleStackIcon class="w-6 h-6" />
            </div>
            <h4 class="font-bold text-lg text-gray-900 dark:text-white">
              {{ t('dashboard.usage.storage', 'Storage') }}
            </h4>
          </div>
          <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">
            {{ subscriptionStore.storageUsedGB }} GB / {{ subscriptionStore.storageLimitGB }} GB
          </p>
        </div>
        
        <div class="w-full bg-gray-200 dark:bg-gray-700/50 rounded-full h-3 mb-2 overflow-hidden shadow-inner">
          <div
            class="h-3 rounded-full transition-all duration-500 shadow-sm"
            :class="[
              subscriptionStore.storageUsagePercent > 90 ? 'bg-red-500' : 'bg-blue-500 bg-gradient-to-r from-blue-500 to-indigo-500'
            ]"
            :style="{ width: `${Math.min(subscriptionStore.storageUsagePercent, 100)}%` }"
          />
        </div>
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-2 text-right">
          {{ subscriptionStore.storageUsagePercent }}% {{ t('dashboard.usage.used', 'used') }}
        </p>
      </div>

      <!-- Videos Card -->
      <div v-if="subscriptionStore.subscription?.features?.maxVideos" class="relative overflow-hidden bg-white/40 dark:bg-gray-800/20 backdrop-blur-xl rounded-[2rem] border border-white/60 dark:border-white/10 p-6 sm:p-8 hover:shadow-lg transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center border shadow-sm bg-purple-50 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-500/30">
              <VideoCameraIcon class="w-6 h-6" />
            </div>
            <h4 class="font-bold text-lg text-gray-900 dark:text-white">
              {{ t('dashboard.usage.videos', 'Videos Quota') }}
            </h4>
          </div>
          <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">
            {{ subscriptionStore.subscription.usage?.videosUploaded || 0 }} / {{ subscriptionStore.subscription.features.maxVideos }}
          </p>
        </div>
        
        <div class="w-full bg-gray-200 dark:bg-gray-700/50 rounded-full h-3 mb-2 overflow-hidden shadow-inner">
          <div
            class="h-3 rounded-full transition-all duration-500 shadow-sm bg-purple-500 bg-gradient-to-r from-purple-500 to-pink-500"
            :style="{ width: `${Math.min(((subscriptionStore.subscription.usage?.videosUploaded || 0) / subscriptionStore.subscription.features.maxVideos) * 100, 100)}%` }"
          />
        </div>
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mt-2 text-right">
          {{ subscriptionStore.videosLeft }} {{ t('dashboard.usage.remaining', 'remaining') }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';
import { useSubscriptionStore } from '@/stores/subscription';
import { CircleStackIcon, VideoCameraIcon } from '@heroicons/vue/24/outline';

const { t } = useI18n();
const subscriptionStore = useSubscriptionStore();
</script>
