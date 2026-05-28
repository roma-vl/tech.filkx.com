<template>
  <AppModal
    :model-value="true"
    :title="event.title"
    max-width="md"
    :show-close="true"
    @update:model-value="$emit('close')"
  >
    <div class="space-y-4">
      <div class="flex items-center">
        <CalendarIcon class="h-5 w-5 text-gray-400 mr-2" />
        <span class="text-gray-600 dark:text-gray-400">
          {{ formattedDateTime }}
        </span>
      </div>

      <div class="flex items-center">
        <ComputerIcon class="h-5 w-5 text-gray-400 mr-2" />
        <span class="text-gray-600 dark:text-gray-400">
          {{ platformName }}
        </span>
      </div>

      <div class="text-gray-600 dark:text-gray-400">
        {{ event.description || $t("calendar.no_description") }}
      </div>
    </div>

    <template #footer>
      <div class="flex space-x-3 w-full">
        <AppButton
          variant="primary"
          class="flex-1"
          @click="editEvent"
        >
          {{ $t("calendar.edit") }}
        </AppButton>
        <AppButton
          variant="danger"
          class="flex-1"
          @click="deleteEvent"
        >
          {{ $t("calendar.delete") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";
import CalendarIcon from "@/components/Icon/CalendarIcon.vue";
import ComputerIcon from "@/components/Icon/ComputerIcon.vue";

const { t } = useI18n();

const props = defineProps({
  event: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["close", "edit", "delete"]);

const formattedDateTime = computed(() => {
  const date = new Date(props.event.date);
  const day = date.getDate();
  const monthKeys = [
    "january",
    "february",
    "march",
    "april",
    "may",
    "june",
    "july",
    "august",
    "september",
    "october",
    "november",
    "december",
  ];
  const month = t(`calendar.monthsGenitive.${monthKeys[date.getMonth()]}`);
  const year = date.getFullYear();
  return `${day} ${month} ${year}, ${props.event.time}`;
});

const platformName = computed(() => {
  return props.event.platform === "youtube" ? "YouTube" : "Twitch";
});

const editEvent = () => {
  emit("edit", props.event);
};

const deleteEvent = () => {
  // Confirmation is handled by DeleteStreamModal in parent
  emit("delete", props.event.id);
};
</script>
