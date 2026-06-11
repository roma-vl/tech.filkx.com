<template>
  <div ref="containerRef" class="relative">
    <!-- Trigger -->
    <button
      type="button"
      class="inline-flex items-center justify-between gap-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 dark:text-zinc-200 hover:border-zinc-300 dark:hover:border-zinc-600 focus:outline-none focus:border-[#00a046] focus:ring-2 focus:ring-[#00a046]/15 transition-all"
      :class="[open ? 'border-[#00a046] ring-2 ring-[#00a046]/15' : '', triggerClass]"
      @click="open = !open"
    >
      <span class="truncate">{{ selectedLabel || placeholder }}</span>
      <span
        class="material-symbols-outlined text-[18px] text-zinc-400 transition-transform duration-200 shrink-0"
        :class="open ? 'rotate-180' : ''"
      >expand_more</span>
    </button>

    <!-- Dropdown panel -->
    <Transition
      enter-active-class="transition-all duration-150 ease-out"
      leave-active-class="transition-all duration-100 ease-in"
      enter-from-class="opacity-0 translate-y-1 scale-[0.98]"
      enter-to-class="opacity-100 translate-y-0 scale-100"
      leave-from-class="opacity-100 translate-y-0 scale-100"
      leave-to-class="opacity-0 translate-y-1 scale-[0.98]"
    >
      <div
        v-if="open"
        class="absolute z-50 mt-1.5 min-w-full bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-lg overflow-hidden"
        :class="alignRight ? 'right-0' : 'left-0'"
      >
        <ul class="py-1 max-h-64 overflow-y-auto">
          <li
            v-for="opt in normalizedOptions"
            :key="opt.value"
          >
            <button
              type="button"
              class="w-full flex items-center justify-between gap-3 px-3.5 py-2.5 text-sm font-medium transition-colors text-left"
              :class="opt.value === modelValue
                ? 'bg-emerald-50 dark:bg-emerald-900/20 text-[#00a046] font-bold'
                : 'text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800'"
              @click="select(opt.value)"
            >
              <span>{{ opt.label }}</span>
              <span
                v-if="opt.value === modelValue"
                class="material-symbols-outlined text-[16px] text-[#00a046] shrink-0"
                style="font-variation-settings: 'FILL' 1"
              >check_circle</span>
            </button>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from "vue";

const props = defineProps<{
  modelValue: string | number;
  options: Array<string | { value: string | number; label: string }>;
  placeholder?: string;
  alignRight?: boolean;
  triggerClass?: string;
}>();

const emit = defineEmits<{
  (e: "update:modelValue", val: string | number): void;
}>();

const open = ref(false);
const containerRef = ref<HTMLElement | null>(null);

const normalizedOptions = computed(() =>
  props.options.map((o) =>
    typeof o === "string" ? { value: o, label: o } : o,
  ),
);

const selectedLabel = computed(
  () => normalizedOptions.value.find((o) => o.value === props.modelValue)?.label ?? "",
);

const select = (value: string | number) => {
  emit("update:modelValue", value);
  open.value = false;
};

const onClickOutside = (e: MouseEvent) => {
  if (containerRef.value && !containerRef.value.contains(e.target as Node)) {
    open.value = false;
  }
};

const onEscape = (e: KeyboardEvent) => {
  if (e.key === "Escape") open.value = false;
};

onMounted(() => {
  document.addEventListener("mousedown", onClickOutside);
  document.addEventListener("keydown", onEscape);
});

onUnmounted(() => {
  document.removeEventListener("mousedown", onClickOutside);
  document.removeEventListener("keydown", onEscape);
});
</script>
