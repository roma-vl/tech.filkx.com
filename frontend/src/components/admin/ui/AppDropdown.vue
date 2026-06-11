<script setup>
import { computed, onMounted, onUnmounted, ref } from "vue";

const props = defineProps({
  align: {
    type: String,
    default: "right",
  },
  width: {
    type: String,
    default: "48",
  },
  contentClasses: {
    type: String,
    default:
      "bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700",
  },
});

const closeOnEscape = (e) => {
  if (open.value && e.key === "Escape") {
    open.value = false;
  }
};

onMounted(() => document.addEventListener("keydown", closeOnEscape));
onUnmounted(() => document.removeEventListener("keydown", closeOnEscape));

const widthClass = computed(() => {
  return (
    {
      24: "w-24",
      48: "w-48",
      60: "w-60",
      72: "w-72",
      96: "w-96",
    }[props.width.toString()] || "w-48"
  );
});

const alignmentClasses = computed(() => {
  if (props.align === "left") {
    return "ltr:origin-top-left rtl:origin-top-right start-0";
  } else if (props.align === "right") {
    return "ltr:origin-top-right rtl:origin-top-left end-0";
  } else {
    return "origin-top";
  }
});

const open = ref(false);
</script>

<template>
  <div class="relative inline-block">
    <div @click="open = !open">
      <slot name="trigger" />
    </div>

    <div
      v-show="open"
      class="fixed inset-0 z-40"
      @click="open = false"
    />

    <Transition
      enter-active-class="transition duration-500 cubic-bezier(0.34, 1.56, 0.64, 1)"
      enter-from-class="opacity-0 scale-90 blur-xl translate-y-2"
      enter-to-class="opacity-100 scale-100 blur-0 translate-y-0"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 scale-100 blur-0 translate-y-0"
      leave-to-class="opacity-0 scale-90 blur-xl translate-y-2"
    >
      <div
        v-show="open"
        class="absolute z-50 mt-2 rounded-lg shadow-lg"
        :class="[widthClass, alignmentClasses]"
        @click="open = false"
      >
        <div
          class="rounded-lg ring-1 ring-black ring-opacity-5 overflow-hidden"
          :class="contentClasses"
        >
          <slot name="content" />
        </div>
      </div>
    </Transition>
  </div>
</template>
