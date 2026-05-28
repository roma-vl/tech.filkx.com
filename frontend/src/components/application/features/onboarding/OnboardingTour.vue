<template>
  <div
    v-if="isVisible"
    class="fixed inset-0 z-[9999] pointer-events-none"
  >
    <div
      class="absolute inset-0 bg-black/60 transition-all duration-300 ease-in-out pointer-events-auto"
      :style="clipPathStyle"
    />

    <button
      class="absolute top-6 right-6 p-3 bg-white/10 hover:bg-white/20 text-white rounded-full backdrop-blur-md border border-white/30 transition-all pointer-events-auto z-[10001]"
      :title="t('tour.skip')"
      @click="closeOverlay"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M6 18L18 6M6 6l12 12"
        />
      </svg>
    </button>

    <div
      v-if="isTargetReady && targetRect"
      class="absolute border-4 border-primary-500 rounded-lg transition-all duration-300 ease-in-out pointer-events-none shadow-[0_0_20px_rgba(var(--primary-500),0.5)]"
      :style="{
        top: targetRect.top - 4 + 'px',
        left: targetRect.left - 4 + 'px',
        width: targetRect.width + 8 + 'px',
        height: targetRect.height + 8 + 'px',
      }"
    />

    <div
      v-if="isTargetReady && targetRect"
      ref="tooltipRef"
      class="absolute w-[320px] bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 pointer-events-auto transition-all duration-300"
      :style="tooltipStyle"
    >
      <div class="flex items-center justify-between mb-3">
        <span
          class="text-xs font-semibold text-primary-600 uppercase tracking-wider"
        >
          {{ $t("tour.step", { step: currentStep + 1, total: steps.length }) }}
        </span>
        <button
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
          :aria-label="$t('tour.skip')"
          @click="closeOverlay"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-4 w-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">
        {{ currentStepData.title }}
      </h3>
      <p class="text-sm text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
        {{ currentStepData.content }}
      </p>

      <div class="flex items-center justify-between">
        <button
          v-if="currentStep > 0"
          class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
          @click="prevStep"
        >
          {{ $t("tour.back") }}
        </button>
        <div v-else />

        <button
          class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all active:scale-95"
          @click="nextStep"
        >
          {{ isLastStep ? $t("tour.finish") : $t("tour.next") }}
        </button>
      </div>

      <div
        class="absolute w-4 h-4 bg-white dark:bg-gray-800 transform rotate-45"
        :style="arrowStyle"
      />
    </div>
  </div>
</template>

<script setup>
import {computed, nextTick, onBeforeUnmount, onMounted, ref} from "vue";
import {useRoute, useRouter} from "vue-router";
import {useI18n} from "vue-i18n";

const router = useRouter();
const route = useRoute();
const { t } = useI18n();

const isVisible = ref(false);
const isTargetReady = ref(false);
const currentStep = ref(
  parseInt(localStorage.getItem("tour_current_step") || "0"),
);
const targetRect = ref(null);
let resizeObserver = null;
let cleanupStepListeners = null;

const steps = [
  {
    route: "/media/videos",
    target: "#tour-video-upload",
    titleKey: "tour.videos.title",
    contentKey: "tour.videos.content",
    placement: "bottom-start",
    onEnter: () => {
      // Ensure modal is closed
      const closeBtn = document.querySelector(".modal-close-button");
      if (closeBtn) closeBtn.click();
    },
  },
  {
    route: "/media/videos",
    target: "#tour-video-dropzone",
    titleKey: "tour.video_upload.title",
    contentKey: "tour.video_upload.content",
    placement: "right",
    onEnter: () => {
      const btn = document.querySelector("#tour-video-upload");
      if (btn) btn.click();
    },
  },
  {
    route: "/media/playlists",
    target: "#tour-playlist-create",
    titleKey: "tour.playlists.title",
    contentKey: "tour.playlists.content",
    placement: "bottom-start",
    onEnter: () => {
      // Ensure modal is closed
      const closeBtn = document.querySelector(".modal-close-button");
      if (closeBtn) closeBtn.click();
    },
  },
  {
    route: "/media/playlists",
    target: "#tour-playlist-video-select",
    titleKey: "tour.playlist_select.title",
    contentKey: "tour.playlist_select.content",
    placement: "right",
    onEnter: () => {
      const btn = document.querySelector("#tour-playlist-create");
      if (btn) btn.click();
    },
  },
  {
    route: "/media/playlists",
    target: "#tour-playlist-sort",
    titleKey: "tour.playlist_sort.title",
    contentKey: "tour.playlist_sort.content",
    placement: "left",
  },
  {
    route: "/streams",
    target: "#tour-stream-start",
    titleKey: "tour.streams.title",
    contentKey: "tour.streams.content",
    placement: "bottom-end",
    onEnter: () => {
      // Ensure modal is closed
      const closeBtn = document.querySelector(".modal-close-button");
      if (closeBtn) closeBtn.click();
    },
  },
  {
    route: "/streams",
    target: "#tour-stream-platforms",
    titleKey: "tour.stream_platforms.title",
    contentKey: "tour.stream_platforms.content",
    placement: "right",
    onEnter: () => {
      const wrapper = document.querySelector("#tour-stream-start");
      if (wrapper) {
        const btn = wrapper.querySelector("button");
        if (btn) btn.click();
      }
    },
  },
  {
    route: "/streams",
    target: "#tour-stream-config",
    titleKey: "tour.stream_config.title",
    contentKey: "tour.stream_config.content",
    placement: "top",
    onEnter: () => {
      setTimeout(() => {
        const platform = document.querySelector(
          "#tour-stream-platforms .group",
        );
        if (platform) platform.click();
      }, 500);
    },
  },
  {
    route: "/scheduler",
    target: "#tour-calendar-container",
    titleKey: "tour.scheduler.title",
    contentKey: "tour.scheduler.content",
    placement: "right",
  },
];

const currentStepData = computed(() => {
  const step = steps[currentStep.value];
  return {
    ...step,
    title: t(step.titleKey),
    content: t(step.contentKey),
  };
});

const isLastStep = computed(() => currentStep.value === steps.length - 1);

const startTour = () => {
  currentStep.value = 0;
  localStorage.removeItem("tour_completed");
  localStorage.setItem("tour_current_step", "0");
  localStorage.setItem("tour_prompt_seen", "true");
  localStorage.setItem("tour_in_progress", "true");

  isVisible.value = true;

  setTimeout(() => {
    updateStep();
  }, 100);
};

const resumeTour = () => {
  const savedStep = parseInt(localStorage.getItem("tour_current_step") || "0");
  currentStep.value = savedStep;
  // Don't remove tour_completed here so we can re-watch finished tours
  localStorage.setItem("tour_in_progress", "true");

  isVisible.value = true;

  setTimeout(() => {
    updateStep();
  }, 100);
};

const initTour = () => {
  const tourInProgress = localStorage.getItem("tour_in_progress") === "true";
  const tourCompleted = localStorage.getItem("tour_completed") === "true";

  if (tourInProgress && !tourCompleted) {
    isVisible.value = true;
    setTimeout(() => {
      updateStep();
    }, 100);
  }
};

defineExpose({ startTour, resumeTour });

const updateStep = async () => {
  if (cleanupStepListeners) cleanupStepListeners();
  targetRect.value = null;
  isTargetReady.value = false;

  const step = steps[currentStep.value];

  if (route.path !== step.route) {
    await router.push(step.route);
    await nextTick();
    await new Promise((resolve) => setTimeout(resolve, 500));
  }

  if (step.onEnter) {
    step.onEnter();
    await new Promise((resolve) => setTimeout(resolve, 300));
  }

  const el = await waitForElement(step.target);

  if (!el) {
    console.warn(`[Tour] Target ${step.target} not found`);
    // If not found, hide overlay but keep tour in progress
    isVisible.value = false;
    return;
  }

  if (el) {
    isVisible.value = true;
    el.scrollIntoView({ behavior: "smooth", block: "center" });
    setupTargetObserver(el);
  }
};

const setupTargetObserver = (el) => {
  updatePosition(el);

  resizeObserver = new ResizeObserver(() => {
    updatePosition(el);
  });
  resizeObserver.observe(el);
  resizeObserver.observe(document.body);

  cleanupStepListeners = () => {
    if (resizeObserver) {
      resizeObserver.disconnect();
      resizeObserver = null;
    }
  };
};

const waitForElement = (selector) => {
  return new Promise((resolve) => {
    const el = document.querySelector(selector);
    if (el && el.offsetParent !== null) {
      return resolve(el);
    }

    const observer = new MutationObserver(() => {
      const element = document.querySelector(selector);
      if (element && element.offsetParent !== null) {
        observer.disconnect();
        resolve(element);
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true,
      attributes: true,
    });

    setTimeout(() => {
      observer.disconnect();
      resolve(document.querySelector(selector));
    }, 2000); // Reduce timeout to 2 seconds for better UX
  });
};

const updatePosition = (el) => {
  if (!el) return;
  const rect = el.getBoundingClientRect();

  if (rect.width === 0 && rect.height === 0) {
    console.warn("[Tour] Element has 0 dimensions:", el);
    return;
  }

  targetRect.value = {
    top: rect.top,
    left: rect.left,
    width: rect.width,
    height: rect.height,
    bottom: rect.bottom,
    right: rect.right,
  };

  isTargetReady.value = true;
};

const clipPathStyle = computed(() => {
  if (!targetRect.value) return {};
  const { top, left, width, height } = targetRect.value;

  const px = (val) => `${val}px`;
  const t = top - 4;
  const l = left - 4;
  const r = left + width + 4;
  const b = top + height + 4;
  const w = window.innerWidth;
  const h = window.innerHeight;

  return {
    "clip-path": `polygon(
      0% 0%,
      0% 100%,
      ${px(l)} 100%,
      ${px(l)} ${px(t)},
      ${px(r)} ${px(t)},
      ${px(r)} ${px(b)},
      ${px(l)} ${px(b)},
      ${px(l)} 100%,
      100% 100%,
      100% 0%
    )`,
  };
});

const tooltipStyle = computed(() => {
  if (!targetRect.value || !currentStepData.value) return {};

  const { top, left, width, height } = targetRect.value;
  const placement = currentStepData.value.placement || "bottom";
  const gap = 12;

  let x = 0;
  let y = 0;

  if (placement.startsWith("bottom")) {
    y = top + height + gap;
    x = left + width / 2 - 160;
    if (placement === "bottom-start") x = left;
    if (placement === "bottom-end") x = left + width - 320;
  } else if (placement.startsWith("top")) {
    y = top - gap - 200;
    x = left + width / 2 - 160;
  } else if (placement.startsWith("right")) {
    x = left + width + gap;
    y = top;
  } else if (placement.startsWith("left")) {
    x = left - 320 - gap;
    y = top;
  }

  x = Math.max(16, Math.min(x, window.innerWidth - 320 - 16));

  return {
    top: `${y}px`,
    left: `${x}px`,
  };
});

const arrowStyle = computed(() => {
  if (!targetRect.value) return {};
  return { display: "none" };
});

const nextStep = () => {
  if (isLastStep.value) {
    finishTour();
  } else {
    currentStep.value++;
    localStorage.setItem("tour_current_step", currentStep.value.toString());
    updateStep();
  }
};

const prevStep = () => {
  if (currentStep.value > 0) {
    currentStep.value--;
    localStorage.setItem("tour_current_step", currentStep.value.toString());
    updateStep();
  }
};

const finishTour = () => {
  localStorage.setItem("tour_completed", "true");
  localStorage.setItem("tour_in_progress", "false");
  isVisible.value = false;
  isTargetReady.value = false;
  if (cleanupStepListeners) cleanupStepListeners();
};

const skipTour = () => {
  // skipTour is now same as closeOverlay, but keeps the possibility to mark as completed if needed
  localStorage.setItem("tour_in_progress", "false");
  isVisible.value = false;
  isTargetReady.value = false;
  if (cleanupStepListeners) cleanupStepListeners();
};

const closeOverlay = () => {
  localStorage.setItem("tour_in_progress", "false");
  isVisible.value = false;
  isTargetReady.value = false;
  if (cleanupStepListeners) cleanupStepListeners();
};

onMounted(() => {
  // Tour will no longer auto-start or resume on refresh
  // It only starts via startTour or resumeTour calls
});

onBeforeUnmount(() => {
  if (cleanupStepListeners) cleanupStepListeners();
});
</script>
```
