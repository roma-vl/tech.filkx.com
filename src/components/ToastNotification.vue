<script setup>
import { store } from '../store.js';

const getIcon = (type) => {
  switch (type) {
    case 'success': return 'check_circle';
    case 'error': return 'error';
    case 'warning': return 'warning';
    case 'info':
    default:
      return 'info';
  }
};

const getBgColor = (type) => {
  switch (type) {
    case 'success': return 'bg-emerald-600 text-white';
    case 'error': return 'bg-error text-white';
    case 'warning': return 'bg-amber-500 text-black';
    case 'info':
    default:
      return 'bg-secondary text-white';
  }
};
</script>

<template>
  <div class="fixed top-6 right-6 z-[120] flex flex-col gap-3 w-80 max-w-[calc(100vw-48px)]">
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
        v-for="toast in store.toasts" 
        :key="toast.id"
        class="flex items-start gap-3 p-4 rounded-xl shadow-lg border border-white/10 backdrop-blur-md transition-all duration-300 pointer-events-auto"
        :class="getBgColor(toast.type)"
      >
        <span class="material-symbols-outlined text-[20px] shrink-0 mt-0.5">
          {{ getIcon(toast.type) }}
        </span>
        <div class="flex-grow text-xs font-semibold leading-normal">
          {{ toast.message }}
        </div>
        <button 
          @click="store.removeToast(toast.id)"
          class="shrink-0 opacity-70 hover:opacity-100 transition-opacity"
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
