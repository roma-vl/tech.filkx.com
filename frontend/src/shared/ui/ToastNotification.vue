<script setup lang="ts">
import { useCartStore } from "@/entities/order/model/cartStore";

const cartStore = useCartStore();

const getIcon = (type: string) => {
  switch (type) {
    case "success":
      return "check_circle";
    case "error":
      return "error";
    case "warning":
      return "warning";
    case "info":
    default:
      return "info";
  }
};

const getIconColor = (type: string) => {
  switch (type) {
    case "success":
      return "bg-emerald-50 dark:bg-emerald-900/20 text-[#00a046]";
    case "error":
      return "bg-red-50 dark:bg-red-900/20 text-red-500";
    case "warning":
      return "bg-amber-50 dark:bg-amber-900/20 text-amber-500";
    case "info":
    default:
      return "bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400";
  }
};
</script>

<template>
  <div
    class="fixed top-6 right-6 z-[120] flex flex-col gap-3 w-80 max-w-[calc(100vw-48px)]"
  >
    <TransitionGroup
      name="toast"
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-y-2 opacity-0 scale-95"
      enter-to-class="transform translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in absolute w-full"
      leave-from-class="transform translate-x-0 opacity-100"
      leave-to-class="transform translate-x-12 opacity-0"
    >
      <div
        v-for="toast in cartStore.toasts"
        :key="toast.id"
        class="flex items-start gap-3 p-4 rounded-xl shadow-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 transition-all duration-300 pointer-events-auto"
      >
        <span
          class="w-9 h-9 rounded-full flex items-center justify-center shrink-0"
          :class="getIconColor(toast.type)"
        >
          <span class="material-symbols-outlined text-[18px]">
            {{ getIcon(toast.type) }}
          </span>
        </span>
        <div
          class="flex-grow text-sm font-semibold leading-normal text-zinc-900 dark:text-white pt-1.5"
        >
          {{ toast.message }}
        </div>
        <button
          class="shrink-0 text-zinc-400 dark:text-zinc-500 hover:text-zinc-600 dark:hover:text-zinc-300 transition-colors mt-1.5"
          @click="cartStore.removeToast(toast.id)"
        >
          <span class="material-symbols-outlined text-[16px]">close</span>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-leave-active {
  position: absolute;
}
</style>
