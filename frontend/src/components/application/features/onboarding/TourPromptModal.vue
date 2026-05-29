<template>
  <TransitionRoot
    :show="isOpen"
    as="template"
  >
    <Dialog
      as="div"
      class="relative z-[10000]"
      @close="handleLater"
    >
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel
              class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all"
            >
              <div class="flex items-center justify-center mb-4">
                <div
                  class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center"
                >
                  <svg
                    class="w-8 h-8 text-primary-600 dark:text-primary-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z"
                    />
                  </svg>
                </div>
              </div>

              <DialogTitle
                as="h3"
                class="text-xl font-bold text-center text-gray-900 dark:text-white mb-2"
              >
                {{ $t("tour.prompt.title") }}
              </DialogTitle>

              <p
                class="text-sm text-center text-gray-600 dark:text-gray-300 mb-6"
              >
                {{ $t("tour.prompt.description") }}
              </p>

              <div class="flex flex-col gap-3">
                <button
                  class="w-full px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all active:scale-95"
                  @click="handleStartTour"
                >
                  {{ $t("tour.prompt.startNow") }}
                </button>
                <button
                  class="w-full px-4 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg transition-all"
                  @click="handleLater"
                >
                  {{ $t("tour.prompt.later") }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, onMounted } from "vue";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionRoot,
  TransitionChild,
} from "@headlessui/vue";

const emit = defineEmits(["start-tour", "dismiss"]);

const isOpen = ref(false);

const handleStartTour = () => {
  isOpen.value = false;
  localStorage.setItem("tour_prompt_seen", "true");
  emit("start-tour");
};

const handleLater = () => {
  isOpen.value = false;
  localStorage.setItem("tour_prompt_seen", "true");
  // Dispatch custom event so banner can react
  window.dispatchEvent(new Event("tour-state-changed"));
  emit("dismiss");
};

onMounted(() => {
  // Check if user has seen the prompt
  const hasSeenPrompt = localStorage.getItem("tour_prompt_seen");
  const tourCompleted = localStorage.getItem("tour_completed");

  if (!hasSeenPrompt && !tourCompleted) {
    // Show prompt after a short delay
    setTimeout(() => {
      isOpen.value = true;
    }, 1000);
  }
});
</script>
