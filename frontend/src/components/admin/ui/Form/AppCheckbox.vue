<template>
  <div class="flex items-start">
    <div class="flex items-center h-5">
      <input
        :id="id"
        type="checkbox"
        :checked="isChecked"
        :disabled="disabled"
        class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
        :class="inputClass"
        v-bind="$attrs"
        @change="handleChange"
      >
    </div>
    <div
      v-if="label || $slots.default"
      class="ml-2 text-sm"
    >
      <label
        :for="id"
        class="font-medium text-gray-900 dark:text-gray-300"
      >
        <slot>{{ label }}</slot>
      </label>
      <p
        v-if="description"
        class="text-xs font-normal text-gray-500 dark:text-gray-400"
      >
        {{ description }}
      </p>
    </div>
  </div>
</template>

<script setup>
import {computed} from "vue";

const props = defineProps({
  modelValue: {
    type: [Boolean, Array],
    default: false,
  },
  value: {
    type: [String, Number, Boolean, Object],
    default: null,
  },
  label: String,
  description: String,
  disabled: Boolean,
  id: {
    type: String,
    default: () => `checkbox-${Math.random().toString(36).substr(2, 9)}`,
  },
  inputClass: String,
});

const emit = defineEmits(["update:modelValue"]);

const isChecked = computed(() => {
  if (Array.isArray(props.modelValue)) {
    return props.modelValue.includes(props.value);
  }
  return !!props.modelValue;
});

const handleChange = (event) => {
  const checked = event.target.checked;
  if (Array.isArray(props.modelValue)) {
    const newValue = [...props.modelValue];
    if (checked) {
      if (!newValue.includes(props.value)) {
        newValue.push(props.value);
      }
    } else {
      const index = newValue.indexOf(props.value);
      if (index !== -1) {
        newValue.splice(index, 1);
      }
    }
    emit("update:modelValue", newValue);
  } else {
    emit("update:modelValue", checked);
  }
};
</script>
