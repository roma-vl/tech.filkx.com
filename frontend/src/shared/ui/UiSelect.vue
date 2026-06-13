<template>
  <div
    ref="containerRef"
    class="w-full relative font-sans"
  >
    <label
      v-if="label"
      class="block text-sm font-semibold text-zinc-700 dark:text-zinc-350 mb-1.5"
    >
      {{ label }}
      <span
        v-if="required"
        class="text-red-500"
      >*</span>
    </label>

    <div class="relative">
      <div
        v-if="$slots.prepend"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10 text-zinc-400"
      >
        <slot name="prepend" />
      </div>

      <button
        type="button"
        :disabled="disabled"
        class="relative w-full bg-white dark:bg-zinc-900 border rounded-lg pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-[#00a046]/20 focus:border-[#00a046] text-sm transition-all duration-200"
        :class="[
          disabled
            ? 'opacity-50 bg-zinc-100 dark:bg-zinc-950 cursor-not-allowed text-zinc-400'
            : 'cursor-pointer text-zinc-800 dark:text-zinc-200',
          error
            ? 'border-red-300 dark:border-red-900 ring-red-500'
            : 'border-zinc-300 dark:border-zinc-800',
          isOpen ? 'ring-2 ring-[#00a046]/20 border-[#00a046]' : '',
          $slots.prepend ? 'pl-10' : 'pl-4',
        ]"
        v-bind="$attrs"
        @click="toggleDropdown"
      >
        <span class="block truncate">
          {{ selectedLabel || placeholder || "Оберіть варіант" }}
        </span>
        <span
          class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
        >
          <span
            class="material-symbols-outlined text-[18px] text-zinc-450 transition-transform duration-200"
            :class="{ 'rotate-180': isOpen }"
          >
            expand_more
          </span>
        </span>
      </button>

      <Transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="isOpen"
          class="absolute z-50 w-full bg-white dark:bg-zinc-900 shadow-xl max-h-[225px] rounded-lg py-1 text-sm ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none border border-zinc-200 dark:border-zinc-800"
          :class="[dropdownPlacement === 'top' ? 'bottom-full mb-1' : 'mt-1']"
        >
          <div
            v-if="searchable"
            class="p-2 sticky top-0 bg-white dark:bg-zinc-900 z-10 border-b border-zinc-200 dark:border-zinc-800"
          >
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              class="w-full px-3 py-1.5 text-sm border border-zinc-300 dark:border-zinc-800 rounded-md bg-zinc-50 dark:bg-zinc-950 text-zinc-800 dark:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-[#00a046]/20 focus:border-[#00a046]"
              :placeholder="searchPlaceholder || 'Пошук...'"
              @click.stop
            >
          </div>

          <div
            v-for="option in filteredOptions"
            :key="option[optionValue]"
            class="cursor-pointer select-none relative py-2.5 pl-3 pr-9 border-l-4 transition-colors"
            :class="[
              option[optionValue] === modelValue
                ? 'bg-emerald-50 dark:bg-emerald-950/20 border-[#00a046] text-[#00a046] font-semibold'
                : 'border-transparent text-zinc-750 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800',
              option.disabled
                ? 'cursor-not-allowed bg-zinc-50/50 dark:bg-zinc-950/50 opacity-60'
                : 'cursor-pointer',
            ]"
            @click="!option.disabled && selectOption(option)"
          >
            <slot
              name="option"
              :option="option"
            >
              <span
                class="block truncate"
                :class="{ 'opacity-50': option.disabled }"
              >
                {{ option[optionLabel] }}
              </span>
            </slot>

            <span
              v-if="option[optionValue] === modelValue"
              class="absolute inset-y-0 right-0 flex items-center pr-4 text-[#00a046]"
            >
              <span class="material-symbols-outlined text-[16px]">check</span>
            </span>
          </div>

          <div
            v-if="filteredOptions.length === 0"
            class="py-4 text-center text-zinc-500 dark:text-zinc-400"
          >
            Нічого не знайдено
          </div>
        </div>
      </Transition>
    </div>

    <p
      v-if="error"
      class="mt-1.5 text-xs text-red-500 font-medium"
    >
      {{ error }}
    </p>
    <p
      v-if="hint"
      class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400"
    >
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: "",
  },
  options: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: "",
  },
  placeholder: {
    type: String,
    default: "",
  },
  error: {
    type: String,
    default: "",
  },
  hint: {
    type: String,
    default: "",
  },
  disabled: Boolean,
  required: Boolean,
  optionValue: {
    type: String,
    default: "value",
  },
  optionLabel: {
    type: String,
    default: "label",
  },
  searchable: {
    type: Boolean,
    default: false,
  },
  searchPlaceholder: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const containerRef = ref(null);
const dropdownPlacement = ref("bottom");
const searchQuery = ref("");
const searchInput = ref(null);

const normalizedOptions = computed(() => {
  return props.options.map((o) => {
    if (typeof o === "string") {
      return { [props.optionValue]: o, [props.optionLabel]: o };
    }
    return o;
  });
});

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return normalizedOptions.value;
  }
  const query = searchQuery.value.toLowerCase();
  return normalizedOptions.value.filter((option) =>
    String(option[props.optionLabel]).toLowerCase().includes(query),
  );
});

const toggleDropdown = () => {
  if (props.disabled) return;
  if (!isOpen.value) {
    const rect = containerRef.value.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < 250 && spaceAbove > spaceBelow) {
      dropdownPlacement.value = "top";
    } else {
      dropdownPlacement.value = "bottom";
    }

    if (props.searchable) {
      searchQuery.value = "";
      nextTick(() => {
        searchInput.value?.focus();
      });
    }
  }
  isOpen.value = !isOpen.value;
};

const selectedLabel = computed(() => {
  const selected = normalizedOptions.value.find(
    (opt) => opt[props.optionValue] === props.modelValue,
  );
  return selected ? selected[props.optionLabel] : null;
});

const selectOption = (option) => {
  emit("update:modelValue", option[props.optionValue]);
  isOpen.value = false;
};

const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener("mousedown", handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener("mousedown", handleClickOutside);
});
</script>
