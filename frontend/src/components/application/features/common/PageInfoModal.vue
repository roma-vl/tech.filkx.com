<template>
  <AppModal
    :model-value="open"
    :title="title"
    max-width="2xl"
    :show-close="true"
    @update:model-value="$emit('close')"
  >
    <slot />
  </AppModal>
</template>

<script setup>
import AppModal from "@/components/application/ui/Overlay/AppModal.vue";

defineProps({
  open: Boolean,
  title: String,
});

defineEmits(["close"]);
</script>
