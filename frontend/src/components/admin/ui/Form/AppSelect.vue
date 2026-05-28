<template>
  <div
    ref="containerRef"
    class="w-full relative"
  >
    <label
      v-if="label"
      class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
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
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10"
      >
        <slot name="prepend" />
      </div>

      <button
        type="button"
        :disabled="disabled"
        class="relative w-full bg-white dark:bg-gray-800 border rounded-lg shadow-sm pr-10 py-2.5 text-left cursor-default focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition-all duration-200"
        :class="[
          disabled
            ? 'opacity-50 bg-gray-100 dark:bg-gray-900 cursor-not-allowed'
            : 'cursor-pointer',
          error
            ? 'border-red-300 dark:border-red-900 ring-red-500'
            : 'border-gray-300 dark:border-gray-600',
          isOpen ? 'ring-2 ring-primary-500 border-primary-500' : '',
          $slots.prepend ? 'pl-10' : 'pl-3',
        ]"
        v-bind="$attrs"
        @click="toggleDropdown"
      >
        <span class="block truncate dark:text-gray-200">
          {{ selectedLabel || placeholder || "Select option" }}
        </span>
        <span
          class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"
        >
          <svg
            class="h-5 w-5 text-gray-400 transition-transform duration-200"
            :class="{ 'rotate-180': isOpen }"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
            fill="currentColor"
            aria-hidden="true"
          >
            <path
              fill-rule="evenodd"
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
              clip-rule="evenodd"
            />
          </svg>
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
          class="absolute z-50 w-full bg-white dark:bg-gray-800 shadow-xl max-h-[225px] rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-100 dark:border-gray-700"
          :class="[dropdownPlacement === 'top' ? 'bottom-full mb-1' : 'mt-1']"
        >
          <div
            v-if="searchable"
            class="p-2 sticky top-0 bg-white dark:bg-gray-800 z-10 border-b border-gray-100 dark:border-gray-700"
          >
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              class="w-full px-3 py-1.5 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              :placeholder="searchPlaceholder || 'Search...'"
              @click.stop
            >
          </div>

          <div
            v-for="option in filteredOptions"
            :key="option[optionValue]"
            class="cursor-pointer select-none relative py-2.5 pl-3 pr-9 border-l-4 transition-colors"
            :class="[
              option[optionValue] === modelValue
                ? 'bg-primary/5 dark:bg-primary/10 border-primary text-primary font-semibold'
                : 'border-transparent text-gray-900 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50',
              option.disabled
                ? 'cursor-not-allowed bg-gray-50/50 dark:bg-gray-900/50 opacity-60'
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
              class="absolute inset-y-0 right-0 flex items-center pr-4"
            >
              <svg
                class="h-4 w-4"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </span>
          </div>

          <div
            v-if="filteredOptions.length === 0"
            class="py-4 text-center text-gray-500 dark:text-gray-400"
          >
            No options found
          </div>
        </div>
      </Transition>
    </div>

    <p
      v-if="error"
      class="mt-1 text-sm text-red-600 dark:text-red-400"
    >
      {{ error }}
    </p>
    <p
      v-if="hint"
      class="mt-1 text-sm text-gray-500 dark:text-gray-400"
    >
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
import {computed, nextTick, onMounted, onUnmounted, ref} from "vue";

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: "",
  },
  options: {
    type: Array,
    default: () => [],
  },
  label: String,
  placeholder: String,
  error: String,
  hint: String,
  disabled: Boolean,
  required: Boolean,
  optionValue: {
    type: String,
    default: "id",
  },
  optionLabel: {
    type: String,
    default: "name",
  },
  searchable: {
    type: Boolean,
    default: false,
  },
  searchPlaceholder: String,
});

const emit = defineEmits(["update:modelValue"]);

const isOpen = ref(false);
const containerRef = ref(null);
const dropdownPlacement = ref("bottom");
const searchQuery = ref("");
const searchInput = ref(null);

const filteredOptions = computed(() => {
  if (!props.searchable || !searchQuery.value) {
    return props.options;
  }
  const query = searchQuery.value.toLowerCase();
  return props.options.filter((option) =>
    String(option[props.optionLabel]).toLowerCase().includes(query),
  );
});

const toggleDropdown = () => {
  if (!isOpen.value) {
    const rect = containerRef.value.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;

    if (spaceBelow < 250 && spaceAbove > spaceBelow) {
      dropdownPlacement.value = "top";
    } else {
      dropdownPlacement.value = "bottom";
    }

    // Focus search input on open
    if (props.searchable) {
      searchQuery.value = ""; // Reset search
      nextTick(() => {
        searchInput.value?.focus();
      });
    }
  }
  isOpen.value = !isOpen.value;
};

const selectedLabel = computed(() => {
  const selected = props.options.find(
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
