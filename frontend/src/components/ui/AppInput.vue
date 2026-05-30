<script setup>
defineProps({
  modelValue: { type: String, default: "" },
  label: { type: String, default: "" },
  type: { type: String, default: "text" },
  placeholder: { type: String, default: "" },
  error: { type: String, default: null },
  disabled: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
});
defineEmits(["update:modelValue"]);
</script>

<template>
  <div>
    <label
      v-if="label"
      class="block text-sm font-medium text-slate-300 mb-1.5"
    >
      {{ label }}
      <span
        v-if="required"
        class="text-red-400 ml-0.5"
      >*</span>
    </label>
    <input
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      class="w-full px-4 py-3 rounded-xl bg-white/10 border text-white placeholder-slate-400 text-sm transition-all outline-none focus:ring-2 disabled:opacity-50 disabled:cursor-not-allowed"
      :class="
        error
          ? 'border-red-500/60 focus:ring-red-500/30'
          : 'border-white/10 focus:border-primary-500/60 focus:ring-primary-500/20'
      "
      @input="$emit('update:modelValue', $event.target.value)"
    >
    <p
      v-if="error"
      class="mt-1.5 text-xs text-red-400 flex items-center gap-1"
    >
      <svg
        class="w-3 h-3 shrink-0"
        fill="currentColor"
        viewBox="0 0 20 20"
      >
        <path
          fill-rule="evenodd"
          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
          clip-rule="evenodd"
        />
      </svg>
      {{ error }}
    </p>
  </div>
</template>
