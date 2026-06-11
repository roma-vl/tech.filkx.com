<template>
  <router-link
    :to="to"
    class="group inline-flex items-center gap-1.5 text-sm font-bold text-[#00a046] hover:text-[#00b050] transition-colors shrink-0"
  >
    <slot />
    <span class="material-symbols-outlined text-[17px] transition-transform duration-200 group-hover:translate-x-0.5">arrow_forward</span>
  </router-link>
</template>

<script setup lang="ts">
defineProps<{
  to: string | object;
}>();
</script>
