<template>
  <div
    class="min-h-screen bg-gradient-to-br from-gray-50 via-gray-100 to-gray-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 text-gray-900 dark:text-gray-100"
  >
    <aside
      :class="[
        'fixed top-0 left-0 z-40 h-screen transition-all duration-300 ease-in-out',
        sidebarCollapsed ? 'w-20' : 'w-72',
      ]"
    >
      <div
        class="h-full flex flex-col bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-xl"
      >
        <div
          :class="[
            'h-16 flex items-center border-b border-gray-200 dark:border-gray-700 transition-all duration-300',
            sidebarCollapsed ? 'justify-center px-2' : 'justify-between px-4',
          ]"
        >
          <div
            class="flex items-center gap-3 transition-all duration-300"
            :class="{ 'cursor-pointer hover:opacity-80': sidebarCollapsed }"
            @click="sidebarCollapsed && toggleSidebar()"
          >
            <div
              class="w-10 h-10 rounded-lg bg-gradient-to-br from-primary-500 to-purple-600 flex items-center justify-center shadow-lg shrink-0"
            >
              <svg
                class="w-6 h-6 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 10V3L4 14h7v7l9-11h-7z"
                />
              </svg>
            </div>
            <transition name="fade">
              <img
                v-if="!sidebarCollapsed"
                :src="AdminLogo"
                alt="Logo"
                class="w-36"
              >
            </transition>
          </div>

          <button
            v-if="!sidebarCollapsed"
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            @click="toggleSidebar"
          >
            <svg
              class="w-5 h-5 text-gray-700 dark:text-gray-300"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
              />
            </svg>
          </button>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-2">
          <div
            v-for="item in navItems"
            :key="item.name"
          >
            <router-link
              v-if="!item.children"
              :to="item.path"
              class="group flex items-center transition-all duration-200 rounded-lg"
              :class="[
                isActive(item.path)
                  ? 'bg-gradient-to-r from-primary-500 to-purple-600 text-gray-100 shadow-lg'
                  : 'hover:bg-gray-100 dark:hover:bg-gray-700',
                sidebarCollapsed ? 'justify-center p-2.5' : 'gap-3 px-3 py-2.5',
              ]"
            >
              <component
                :is="item.icon"
                class="w-5 h-5 shrink-0"
              />
              <transition name="fade">
                <span
                  v-if="!sidebarCollapsed"
                  class="font-medium whitespace-nowrap text-gray-900 dark:text-gray-100"
                  :class="[isActive(item.path) ? ' !text-gray-100' : '']"
                >
                  {{ item.name }}
                </span>
              </transition>
              <span
                v-if="item.badge && !sidebarCollapsed"
                class="ml-auto px-2 py-0.5 text-xs font-semibold rounded-full bg-red-500 text-white"
              >
                {{ item.badge }}
              </span>
            </router-link>

            <div v-else>
              <button
                class="w-full group flex items-center transition-all duration-200 rounded-lg"
                :class="[
                  sidebarCollapsed
                    ? 'justify-center p-2.5'
                    : 'gap-3 px-3 py-2.5',
                  'hover:bg-gray-100 dark:hover:bg-gray-700',
                ]"
                @click="toggleSubmenu(item.name)"
              >
                <component
                  :is="item.icon"
                  class="w-5 h-5 shrink-0"
                />
                <transition name="fade">
                  <span
                    v-if="!sidebarCollapsed"
                    class="font-medium flex-1 text-left whitespace-nowrap text-sm text-gray-900 dark:text-gray-100"
                  >
                    {{ item.name }}
                  </span>
                </transition>
                <svg
                  v-if="!sidebarCollapsed"
                  class="w-4 h-4 transition-transform text-gray-400 group-hover:text-gray-700 dark:group-hover:text-white"
                  :class="{ 'rotate-180': openSubmenus.includes(item.name) }"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </button>

              <transition name="slide">
                <div
                  v-if="openSubmenus.includes(item.name) && !sidebarCollapsed"
                  class="overflow-hidden"
                >
                  <div class="ml-8 mt-1 space-y-1 pb-1">
                    <router-link
                      v-for="child in item.children"
                      :key="child.name"
                      :to="child.path"
                      class="block px-3 py-2 rounded-lg text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                      :class="[
                        isActive(child.path)
                          ? 'text-primary-600 dark:text-primary-400 font-medium'
                          : 'text-gray-700 dark:text-gray-300',
                      ]"
                    >
                      {{ child.name }}
                    </router-link>
                  </div>
                </div>
              </transition>
            </div>
          </div>
        </nav>
      </div>
    </aside>

    <div
      :class="[
        'transition-all duration-300',
        sidebarCollapsed ? 'ml-20' : 'ml-72',
      ]"
    >
      <header
        class="sticky top-0 z-30 bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg border-b border-gray-200 dark:border-gray-700"
      >
        <div class="h-16 px-6 flex items-center justify-between">
          <nav class="flex items-center gap-2 text-sm">
            <router-link
              to="/admin"
              class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
            >
              Home
            </router-link>
            <svg
              class="w-4 h-4 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5l7 7-7 7"
              />
            </svg>
            <span class="font-medium text-gray-900 dark:text-gray-100">
              {{ currentPageTitle }}
            </span>
          </nav>

          <div class="flex items-center gap-3">
            <Notifications />
            <ProfileMenuHeader />
          </div>
        </div>
      </header>

      <main class="p-6">
        <div class="max-w-[1600px] mx-auto">
          <div class="mb-8 flex items-center justify-between">
            <div class="relative">
              <div class="flex items-center gap-3">
                <div
                  class="h-8 w-1.5 rounded-full bg-gradient-to-b from-primary-500 to-purple-600 shadow-sm"
                />
                <div class="flex items-center gap-4">
                  <h1
                    class="text-3xl font-black text-gray-900 dark:text-white uppercase tracking-tight"
                  >
                    {{ currentPageTitle }}
                  </h1>

                  <!-- Description Popover -->
                  <Popover
                    v-slot="{ open }"
                    class="relative"
                  >
                    <PopoverButton
                      class="flex items-center justify-center p-2 rounded-xl transition-all outline-none"
                      :class="[
                        open
                          ? 'bg-primary-50 text-cyan-600 dark:bg-primary-500/10 dark:text-primary-400'
                          : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-200 dark:hover:bg-gray-700/50',
                      ]"
                    >
                      <InformationCircleIcon class="w-6 h-6" />
                    </PopoverButton>

                    <transition
                      enter-active-class="transition duration-200 ease-out"
                      enter-from-class="translate-y-1 opacity-0"
                      enter-to-class="translate-y-0 opacity-100"
                      leave-active-class="transition duration-150 ease-in"
                      leave-from-class="translate-y-0 opacity-100"
                      leave-to-class="translate-y-1 opacity-0"
                    >
                      <PopoverPanel
                        class="absolute left-0 z-50 mt-3 w-80 transform px-4 sm:px-0 lg:max-w-3xl"
                      >
                        <div
                          class="overflow-hidden rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10"
                        >
                          <div
                            class="relative bg-white/90 dark:bg-gray-800/95 backdrop-blur-xl p-6"
                          >
                            <div class="flex items-start gap-4">
                              <div
                                class="p-2 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 shrink-0"
                              >
                                <InformationCircleIcon class="w-5 h-5" />
                              </div>
                              <div class="space-y-1">
                                <p
                                  class="text-xs font-black uppercase tracking-widest text-primary-600 dark:text-primary-400"
                                >
                                  {{ t("admin.common.aboutPage") }}
                                </p>
                                <p
                                  class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed font-medium"
                                >
                                  {{ currentPageDescription }}
                                </p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </PopoverPanel>
                    </transition>
                  </Popover>
                </div>
              </div>
            </div>
          </div>
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import {computed, markRaw, onMounted, ref, shallowRef, watch} from "vue";
import {useRoute} from "vue-router";
import {useI18n} from "vue-i18n";
import AdminLogo from "@/assets/images/logo/logo_admin.png";
import {
  ChartBarIcon,
  Cog6ToothIcon,
  InformationCircleIcon,
  MegaphoneIcon,
  ShoppingBagIcon,
  ShoppingCartIcon,
  SignalIcon,
  Squares2X2Icon,
  TagIcon,
  TicketIcon,
  UserCircleIcon,
  UserGroupIcon,
  UsersIcon,
} from "@heroicons/vue/24/outline";
import {Popover, PopoverButton, PopoverPanel} from "@headlessui/vue";

import ProfileMenuHeader from "@/layouts/topBar/ProfileMenuHeader.vue";
import DashboardIcon from "@/components/Icon/DashboardIcon.vue";
import Notifications from "@/layouts/topBar/Notifications.vue";

const { t } = useI18n();
const route = useRoute();

const sidebarCollapsed = ref(false);
const openSubmenus = ref([]);

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};

const toggleSubmenu = (name) => {
  const index = openSubmenus.value.indexOf(name);
  index > -1
    ? openSubmenus.value.splice(index, 1)
    : openSubmenus.value.push(name);
};

const isActive = (path) =>
  route.path === path || route.path.startsWith(path + "/");

const syncSubmenus = () => {
  navItems.value.forEach((item) => {
    if (!item.children) return;
    const hasActiveChild = item.children.some((child) => isActive(child.path));
    if (hasActiveChild && !openSubmenus.value.includes(item.key)) {
      openSubmenus.value.push(item.key);
    }
  });
};

watch(() => route.path, syncSubmenus);
onMounted(syncSubmenus);

const currentPageTitle = computed(() => {
  if (route.meta?.titleKey) return t(route.meta.titleKey);
  return t("admin.dashboard.title");
});

const currentPageDescription = computed(() => {
  if (route.meta?.descriptionKey) return t(route.meta.descriptionKey);
  return t("admin.dashboard.description");
});

const navItems = shallowRef([
  {
    key: "dashboard",
    name: t("admin.nav.dashboard"),
    path: "/admin/dashboard",
    icon: markRaw(Squares2X2Icon),
  },
  {
    key: "products",
    name: t("admin.nav.products"),
    path: "/admin/products",
    icon: markRaw(ShoppingBagIcon),
  },
  {
    key: "orders",
    name: t("admin.nav.orders"),
    path: "/admin/orders",
    icon: markRaw(ShoppingCartIcon),
  },
  {
    key: "clients",
    name: t("admin.nav.clients"),
    path: "/admin/users",
    icon: markRaw(UsersIcon),
  },
  {
    key: "billing",
    name: t("admin.nav.sales"),
    icon: markRaw(SignalIcon),
    children: [
      { name: t("admin.nav.billingOverview"), path: "/admin/billing" },
      { name: t("admin.nav.accountingInvoices"), path: "/admin/accounting/invoices" },
      { name: t("admin.nav.accountingLedger"), path: "/admin/accounting/ledger" },
    ],
  },
  {
    key: "affiliates",
    name: "Партнери",
    path: "/admin/affiliates",
    icon: markRaw(UserCircleIcon),
  },
  {
    key: "marketing",
    name: t("admin.nav.marketing"),
    icon: markRaw(TagIcon),
    children: [
      { name: t("admin.nav.coupons"), path: "/admin/coupons" },
      { name: t("admin.nav.promotions"), path: "/admin/promotions" },
      { name: t("admin.nav.emailTemplates"), path: "/admin/emails" },
    ],
  },
  {
    key: "support",
    name: t("admin.nav.support"),
    path: "/admin/support",
    icon: markRaw(TicketIcon),
  },
  {
    key: "notifications",
    name: t("admin.nav.notifications"),
    path: "/admin/notifications",
    icon: markRaw(MegaphoneIcon),
  },
  {
    key: "analytics",
    name: t("admin.nav.analytics"),
    path: "/admin/analytics",
    icon: markRaw(ChartBarIcon),
  },
  {
    key: "management",
    name: t("admin.nav.management"),
    icon: markRaw(UserGroupIcon),
    children: [
      { name: t("admin.nav.team"), path: "/admin/team" },
      { name: t("admin.nav.roles"), path: "/admin/roles" },
      { name: t("admin.nav.auditLogs"), path: "/admin/logs" },
      { name: t("admin.nav.serverLogs"), path: "/admin/server-logs" },
      { name: t("admin.nav.systemStatus"), path: "/admin/system" },
    ],
  },
  {
    key: "settings",
    name: t("admin.nav.settings"),
    path: "/admin/settings",
    icon: markRaw(Cog6ToothIcon),
  },
  {
    key: "app",
    name: t("admin.nav.application"),
    path: "/dashboard",
    icon: markRaw(DashboardIcon),
  },
]);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  max-height: 400px;
  overflow: hidden;
}
.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-8px);
}

.slide-left-enter-active,
.slide-left-leave-active {
  transition: all 0.3s ease;
}
.slide-left-enter-from,
.slide-left-leave-to {
  transform: translateX(100%);
  opacity: 0;
}
</style>
