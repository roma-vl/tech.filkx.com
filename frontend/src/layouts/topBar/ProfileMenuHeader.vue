<script setup>
import { computed } from "vue";
import {
  Menu,
  MenuButton,
  MenuItems,
  MenuItem,
  TransitionRoot,
} from "@headlessui/vue";
import VerifyIcon from "@/components/Icon/VerifyIcon.vue";
import SettingsIcon from "@/components/Icon/SettingsIcon.vue";
import LogoutIcon from "@/components/Icon/LogoutIcon.vue";
import { useAuthStore } from "@/stores/auth.js";
import { useRouter } from "vue-router";
import avatarPlaceholder from "@/assets/images/avatars/info/empty.png";
import ArrowSmallDownIcon from "@/components/Icon/ArrowSmallDownIcon.vue";
import DarkModeToggle from "@/layouts/topBar/DarkModeToggle.vue";
import Locale from "@/layouts/topBar/Locale.vue";
import { UserIcon, HomeIcon } from "@heroicons/vue/24/outline";

const store = useAuthStore();
const router = useRouter();
const user = computed(() => store.user);
const avatar = computed(() => user.value?.avatarUrl || avatarPlaceholder);

const cabinetLabel = computed(() => store.user?.locale === "en" ? "Personal Cabinet" : "Особистий кабінет");
const settingsLabel = computed(() => store.user?.locale === "en" ? "Cabinet Settings" : "Налаштування кабінету");
const shopLabel = computed(() => store.user?.locale === "en" ? "Back to Shop" : "Повернутися в магазин");

async function logout() {
  await store.logout();
  await router.push("/login");
}
</script>

<template>
  <Menu
    as="div"
    class="relative inline-block text-left"
  >
    <MenuButton
      class="profile-menu-button inline-flex items-center space-x-2 px-3 py-1.5 rounded-md text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors"
    >
      <img
        :src="avatar"
        :alt="user?.name"
        class="h-8 w-8 md:h-9 md:w-9 rounded-full object-cover ring-2 ring-gray-100 dark:ring-gray-700"
      >
      <span class="hidden md:inline truncate max-w-[120px]">
        {{ user?.name || $t("userMenu.profile.fallback") }}
      </span>
      <ArrowSmallDownIcon class="h-4 w-4 opacity-50" />
    </MenuButton>

    <TransitionRoot
      enter="transition ease-out duration-200"
      enter-from="opacity-0 translate-y-1 scale-95"
      enter-to="opacity-100 translate-y-0 scale-100"
      leave="transition ease-in duration-150"
      leave-from="opacity-100 translate-y-0 scale-100"
      leave-to="opacity-0 translate-y-1 scale-95"
    >
      <MenuItems
        class="absolute right-0 mt-2 w-72 origin-top-right divide-y divide-gray-100 dark:divide-gray-700 rounded-xl bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none z-[20]"
      >
        <div class="px-4 py-4">
          <div class="flex items-center space-x-3">
            <img
              :src="avatar"
              :alt="user?.name"
              class="h-10 w-10 rounded-full object-cover"
            >
            <div class="flex-1 min-w-0">
              <div class="flex items-center">
                <p
                  class="text-sm font-semibold text-gray-900 dark:text-white truncate"
                >
                  {{ user?.name }}
                </p>
                <VerifyIcon
                  v-if="user?.is_verified"
                  class="ml-1.5 h-4 w-4 text-blue-500"
                />
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ user?.email }}
              </p>
            </div>
          </div>
        </div>

        <div class="p-1.5 space-y-0.5">
          <MenuItem v-slot="{ active }">
            <router-link
              to="/account"
              :class="[
                active
                  ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                  : 'text-gray-700 dark:text-gray-300',
                'group flex items-center px-3 py-2 text-sm rounded-lg transition-colors',
              ]"
            >
              <UserIcon
                class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500"
              />
              {{ cabinetLabel }}
            </router-link>
          </MenuItem>

          <MenuItem v-slot="{ active }">
            <router-link
              to="/account?tab=settings"
              :class="[
                active
                  ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                  : 'text-gray-700 dark:text-gray-300',
                'group flex items-center px-3 py-2 text-sm rounded-lg transition-colors',
              ]"
            >
              <SettingsIcon
                class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500"
              />
              {{ settingsLabel }}
            </router-link>
          </MenuItem>

          <MenuItem v-slot="{ active }">
            <router-link
              to="/"
              :class="[
                active
                  ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white'
                  : 'text-gray-700 dark:text-gray-300',
                'group flex items-center px-3 py-2 text-sm rounded-lg transition-colors',
              ]"
            >
              <HomeIcon
                class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500"
              />
              {{ shopLabel }}
            </router-link>
          </MenuItem>
        </div>

        <div class="px-4 py-3 space-y-3">
          <div class="flex items-center justify-between">
            <span
              class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              {{ $t("account.darkMode") }}
            </span>
            <DarkModeToggle />
          </div>

          <div class="flex items-center justify-between">
            <span
              class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
            >
              {{ $t("account.localeMode") }}
            </span>
            <Locale />
          </div>
        </div>

        <div class="p-1.5">
          <MenuItem v-slot="{ active }">
            <button
              :class="[
                active
                  ? 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400'
                  : 'text-gray-700 dark:text-gray-300',
                'group flex w-full items-center px-3 py-2 text-sm rounded-lg transition-colors',
              ]"
              @click="logout"
            >
              <LogoutIcon
                class="mr-3 h-5 w-5 text-gray-400 group-hover:text-red-500"
              />
              {{ $t("auth.logout") }}
            </button>
          </MenuItem>
        </div>
      </MenuItems>
    </TransitionRoot>
  </Menu>
</template>
