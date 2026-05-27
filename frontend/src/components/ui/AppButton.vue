<script setup>
defineProps({
  to:      { type: [String, Object], default: null },
  type:    { type: String, default: 'button' },
  variant: { type: String, default: 'primary' }, // primary | secondary | danger | ghost
  size:    { type: String, default: 'md' },       // sm | md | lg
  loading: { type: Boolean, default: false },
  disabled:{ type: Boolean, default: false },
});
</script>

<template>
  <component
    :is="to ? 'router-link' : 'button'"
    :to="to"
    :type="to ? null : type"
    :disabled="to ? null : (disabled || loading)"
    class="relative inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]"
    :class="[
      // Size
      size === 'sm' ? 'px-4 py-2 text-sm' : size === 'lg' ? 'px-6 py-3.5 text-base' : 'px-5 py-2.5 text-sm',
      // Variant
      variant === 'primary'   ? 'bg-primary-600 hover:bg-primary-500 text-white focus:ring-primary-500 shadow-lg shadow-primary-500/20 hover:shadow-primary-500/40' :
      variant === 'secondary' ? 'bg-white/10 hover:bg-white/20 text-white border border-white/10 focus:ring-white/20' :
      variant === 'danger'    ? 'bg-red-600 hover:bg-red-500 text-white focus:ring-red-500' :
                                'bg-transparent hover:bg-white/10 text-slate-300 focus:ring-white/20',
    ]"
  >
    <!-- Spinner -->
    <svg
      v-if="loading"
      class="animate-spin h-4 w-4"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
    </svg>
    <slot />
  </component>
</template>
