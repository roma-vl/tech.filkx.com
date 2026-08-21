<template>
  <div class="w-full">
    <div
      class="border-2 border-dashed rounded-xl p-8 transition-colors duration-200 cursor-pointer text-center"
      :class="[
        isDragging
          ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/10'
          : 'border-gray-300 dark:border-gray-600 hover:border-primary-500',
        wrapperClass,
      ]"
      @dragover.prevent="isDragging = true"
      @dragleave="isDragging = false"
      @drop="handleDrop"
      @click="triggerSelect"
    >
      <slot name="content">
        <div class="flex flex-col items-center">
          <slot name="icon">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-10 h-10 text-gray-400 mb-3"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
              />
            </svg>
          </slot>
          <span
            class="text-lg font-medium mb-1 text-gray-700 dark:text-gray-200"
          >{{ title }}</span>
          <span class="text-sm text-gray-500 dark:text-gray-400">{{
            subtitle
          }}</span>
        </div>
      </slot>

      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        class="hidden"
        :multiple="multiple"
        :disabled="disabled"
        @change="handleFileSelect"
      >

      <div
        v-if="$slots.action"
        class="mt-4"
      >
        <slot
          name="action"
          :trigger="triggerSelect"
        />
      </div>
    </div>

    <p
      v-if="error"
      class="mt-2 text-sm text-red-600"
    >
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
  accept: {
    type: String,
    default: "*",
  },
  multiple: Boolean,
  disabled: Boolean,
  title: {
    type: String,
    default: "Drop files here",
  },
  subtitle: {
    type: String,
    default: "or click to browse",
  },
  error: String,
  wrapperClass: String,
});

const emit = defineEmits(["update:modelValue", "change"]);

const fileInput = ref(null);
const isDragging = ref(false);

const triggerSelect = () => {
  if (!props.disabled) fileInput.value?.click();
};

const handleFileSelect = (event) => {
  const files = event.target.files;
  emitChange(files);
};

const handleDrop = (event) => {
  event.preventDefault();
  isDragging.value = false;
  if (props.disabled) return;

  const files = event.dataTransfer.files;
  emitChange(files);
};

const emitChange = (files) => {
  if (!files || files.length === 0) return;

  if (props.multiple) {
    emit("update:modelValue", files);
    emit("change", files);
  } else {
    emit("update:modelValue", files[0]);
    emit("change", files[0]);
  }
};
</script>
