<template>
  <div v-if="shouldShow">
    <div
      class="mx-4 mb-4 p-4 bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg shadow-lg cursor-pointer hover:shadow-xl transition-all"
      @click="showChoiceModal = true"
    >
      <div class="flex items-center gap-3">
        <div class="flex-shrink-0">
          <svg
            class="w-6 h-6 text-white"
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
        <div class="flex-1">
          <p class="text-sm font-semibold text-white">
            {{ $t("tour.reminder.title") }}
          </p>
          <p class="text-xs text-white/90">
            {{ $t("tour.reminder.subtitle") }}
          </p>
        </div>
        <button
          class="flex-shrink-0 text-white/80 hover:text-white transition-colors"
          @click.stop="handleDismiss"
        >
          <svg
            class="w-5 h-5"
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
    </div>

    <!-- Choice Modal -->
    <AppModal
      v-model="showChoiceModal"
      :title="$t('tour.reminder.modalTitle')"
      max-width="md"
    >
      <div class="space-y-4">
        <AppButton
          variant="primary"
          class="w-full justify-center py-3"
          @click="confirmChoice('continue')"
        >
          {{ $t("tour.reminder.continue") }}
        </AppButton>
        <AppButton
          variant="secondary"
          class="w-full justify-center py-3"
          @click="confirmChoice('restart')"
        >
          {{ $t("tour.reminder.restart") }}
        </AppButton>
      </div>
    </AppModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const emit = defineEmits(["start-tour"]);

const shouldShow = ref(false);
const showChoiceModal = ref(false);

const checkShouldShow = () => {
  const promptSeen = localStorage.getItem("tour_prompt_seen");
  const reminderDismissed = localStorage.getItem("tour_reminder_dismissed");

  // Decoupled from tour_completed as per user request
  shouldShow.value = promptSeen && !reminderDismissed;
};

const confirmChoice = (choice) => {
  showChoiceModal.value = false;
  shouldShow.value = false;
  emit("start-tour", choice);
};

const handleDismiss = () => {
  shouldShow.value = false;
  localStorage.setItem("tour_reminder_dismissed", "true");
};

onMounted(() => {
  checkShouldShow();
  window.addEventListener("storage", checkShouldShow);
  window.addEventListener("tour-state-changed", checkShouldShow);
});

defineExpose({ refresh: checkShouldShow });
</script>
