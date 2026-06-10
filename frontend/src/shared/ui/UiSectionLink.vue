<template>
  <router-link
    :to="to"
    class="inline-flex items-center gap-1.5 text-sm font-bold text-[#00a046] hover:text-[#00b050] hover:underline underline-offset-2 transition-colors shrink-0"
  >
    <slot />
    <span class="material-symbols-outlined text-[17px]">arrow_forward</span>
  </router-link>
</template>

<script setup lang="ts">
defineProps<{
  to: string | object;
}>();
</script>
