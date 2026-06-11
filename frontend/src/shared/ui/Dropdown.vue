<script setup>
import { computed, onMounted, onUnmounted, ref, nextTick } from "vue";

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
      "bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-100 dark:border-gray-700",
  },
});

const open = ref(false);
const triggerRef = ref(null);
const contentRef = ref(null);
const coords = ref({ top: 0, left: 0 });
const verticalAlign = ref("bottom"); // 'bottom' or 'top'

const closeOnEscape = (e) => {
  if (open.value && e.key === "Escape") {
    open.value = false;
  }
};

onMounted(() => {
  document.addEventListener("keydown", closeOnEscape);
  window.addEventListener("scroll", updateCoords, true);
  window.addEventListener("resize", updateCoords);
});

onUnmounted(() => {
  document.removeEventListener("keydown", closeOnEscape);
  window.removeEventListener("scroll", updateCoords, true);
  window.removeEventListener("resize", updateCoords);
});

const widthClass = computed(() => {
  const widths = {
    24: "w-24",
    48: "w-48",
    60: "w-60",
    72: "w-72",
    80: "w-80",
    96: "w-96",
  };

  if (props.width.toString().includes("[")) return `w-${props.width}`;
  return widths[props.width.toString()] || "w-48";
});

const calculatePixelWidth = () => {
  const el = contentRef.value;
  if (!el) return 192; // Default w-48
  return el.offsetWidth;
};

const updateCoords = () => {
  if (!open.value || !triggerRef.value) return;

  const rect = triggerRef.value.getBoundingClientRect();
  const menuWidth = calculatePixelWidth();
  const menuHeight = contentRef.value?.offsetHeight || 200;

  // Calculate top/bottom
  if (rect.bottom + menuHeight > window.innerHeight && rect.top > menuHeight) {
    coords.value.top = rect.top - menuHeight - 8;
    verticalAlign.value = "top";
  } else {
    coords.value.top = rect.bottom + 8;
    verticalAlign.value = "bottom";
  }

  // Calculate left/right
  if (props.align === "right") {
    coords.value.left = rect.right - menuWidth;
  } else if (props.align === "left") {
    coords.value.left = rect.left;
  } else {
    coords.value.left = rect.left + rect.width / 2 - menuWidth / 2;
  }

  // Ensure it doesn't go off screen left/right
  if (coords.value.left < 8) coords.value.left = 8;
  if (coords.value.left + menuWidth > window.innerWidth - 8) {
    coords.value.left = window.innerWidth - menuWidth - 8;
  }
};

const toggle = async () => {
  open.value = !open.value;
  if (open.value) {
    await nextTick();
    updateCoords();
  }
};

const transitionClasses = computed(() => {
  if (verticalAlign.value === "top") {
    return {
      enter: "transition ease-out duration-200",
      from: "opacity-0 translate-y-2 scale-95",
      to: "opacity-100 translate-y-0 scale-100",
      leave: "transition ease-in duration-75",
      lFrom: "opacity-100 translate-y-0 scale-100",
      lTo: "opacity-0 translate-y-2 scale-95",
    };
  }
  return {
    enter: "transition ease-out duration-200",
    from: "opacity-0 -translate-y-2 scale-95",
    to: "opacity-100 translate-y-0 scale-100",
    leave: "transition ease-in duration-75",
    lFrom: "opacity-100 translate-y-0 scale-100",
    lTo: "opacity-0 -translate-y-2 scale-95",
  };
});
</script>

<template>
  <div class="relative inline-block">
    <div
      ref="triggerRef"
      @click.stop="toggle"
    >
      <slot name="trigger" />
    </div>

    <Teleport to="body">
      <div
        v-if="open"
        class="fixed inset-0 z-[60]"
        @click="open = false"
      />

      <Transition
        :enter-active-class="transitionClasses.enter"
        :enter-from-class="transitionClasses.from"
        :enter-to-class="transitionClasses.to"
        :leave-active-class="transitionClasses.leave"
        :leave-from-class="transitionClasses.lFrom"
        :leave-to-class="transitionClasses.lTo"
      >
        <div
          v-if="open"
          ref="contentRef"
          class="fixed z-[61] pointer-events-auto"
          :class="[widthClass]"
          :style="{
            top: `${coords.top}px`,
            left: `${coords.left}px`,
          }"
          @click="open = false"
        >
          <div
            class="overflow-hidden"
            :class="contentClasses"
          >
            <slot name="content" />
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>
