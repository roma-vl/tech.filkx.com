<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-in-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in-out duration-300"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/50" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <div
            class="pointer-events-none fixed inset-y-0 left-0 max-w-full flex"
          >
            <TransitionChild
              as="template"
              enter="transform transition ease-in-out duration-300"
              enter-from="-translate-x-full"
              enter-to="translate-x-0"
              leave="transform transition ease-in-out duration-300"
              leave-from="translate-x-0"
              leave-to="-translate-x-full"
            >
              <DialogPanel class="pointer-events-auto w-screen max-w-[300px]">
                <div
                  class="flex h-full flex-col bg-white/90 dark:bg-gray-900/90 backdrop-blur-2xl shadow-2xl border-r border-white/20 dark:border-white/5"
                >
                  <div class="px-6 py-8 flex items-center justify-between">
                    <router-link
                      to="/"
                      class="flex items-center"
                      @click="$emit('close')"
                    >
                      <img :src="Logo" alt="Filkx" class="w-28" />
                    </router-link>
                    <button
                      class="p-2.5 rounded-xl bg-white/50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 border border-white/60 dark:border-white/10 shadow-sm transition-all"
                      @click="$emit('close')"
                    >
                      <XMarkIcon
                        class="h-6 w-6 text-gray-700 dark:text-gray-200"
                      />
                    </button>
                  </div>

                  <nav
                    class="flex-1 overflow-y-auto px-4 py-4 custom-scrollbar"
                  >
                    <div class="mb-8">
                      <UsageSummary vertical />
                    </div>

                    <div
                      v-for="group in navigationGroups"
                      :key="group.id"
                      class="space-y-1"
                    >
                      <div v-for="item in group.items" :key="item.id">
                        <AppNavDropdown
                          v-if="item.items"
                          :item="item"
                          :collapsed="false"
                          @click="$emit('close')"
                        />
                        <AppNavItem
                          v-else
                          :item="item"
                          :collapsed="false"
                          @click="$emit('close')"
                        />
                      </div>

                      <div
                        v-if="group.id !== 'account'"
                        class="my-4 border-t border-white/10"
                      />
                    </div>
                  </nav>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { XMarkIcon } from "@heroicons/vue/24/outline";
import AppNavItem from "./AppNavItem.vue";
import AppNavDropdown from "./AppNavDropdown.vue";
import UsageSummary from "./UsageSummary.vue";
import Logo from "@/assets/images/logo/logo.png";
import { useNavigation } from "@/layouts/main/useNavigation.js";

defineEmits<{ (e: "close"): void }>();
defineProps<{ open?: boolean }>();

const { navigationGroups } = useNavigation();
</script>
