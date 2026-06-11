<template>
  <div
    class="bg-white dark:bg-gray-800 rounded-3xl p-8 border border-gray-100 dark:border-gray-700 shadow-sm"
  >
    <div class="flex items-center gap-4 mb-8">
      <div
        class="h-12 w-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400"
      >
        <VideoIcon class="h-6 w-6" />
      </div>
      <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
          {{ $t("admin.settings.transcoding.title") }}
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ $t("admin.settings.transcoding.subtitle") }}
        </p>
      </div>
    </div>

    <div class="space-y-6">
      <div class="flex flex-col gap-2">
        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">
          {{ $t("admin.settings.transcoding.watermark_label") }}
        </label>

        <div
          v-if="previewUrl"
          class="relative w-full max-w-sm aspect-video rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 group"
        >
          <img
            :src="previewUrl"
            class="w-full h-full object-contain"
            alt="Watermark preview"
          >
          <div
            class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"
          >
            <AppButton
              variant="danger"
              size="sm"
              @click="removeWatermark"
            >
              <Trash2Icon class="w-4 h-4 mr-2" />
              {{ $t("common.delete") }}
            </AppButton>
          </div>
        </div>

        <AppFileUpload
          v-else
          accept="image/png,image/jpeg"
          :title="$t('admin.settings.transcoding.upload_title')"
          :subtitle="$t('admin.settings.transcoding.upload_subtitle')"
          :disabled="isUploading"
          @change="handleFileUpload"
        >
          <template #icon>
            <div
              v-if="isUploading"
              class="w-10 h-10 border-4 border-primary-500 border-t-transparent rounded-full animate-spin mb-3"
            />
            <ImageIcon
              v-else
              class="w-10 h-10 text-gray-400 mb-3"
            />
          </template>
        </AppFileUpload>

        <p class="text-xs text-gray-500 dark:text-gray-400">
          {{ $t("admin.settings.transcoding.watermark_hint") }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { VideoIcon, ImageIcon, Trash2Icon } from "lucide-vue-next";
import AppFileUpload from "@/components/admin/ui/AppFileUpload.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import api from "@/shared/services/api/apiClient";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue"]);

const toast = useToast();
const { t } = useI18n();
const isUploading = ref(false);

const previewUrl = computed(() => {
  return (
    props.modelValue.platformWatermarkPreview ||
    props.modelValue.platformWatermarkUrl
  );
});

const handleFileUpload = async (file) => {
  if (!file) return;

  const formData = new FormData();
  formData.append("watermark", file);

  isUploading.value = true;
  try {
    const response = await api.post("/admin/settings/watermark", formData, {
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });

    const { url, key } = response.data.data;

    emit("update:modelValue", {
      ...props.modelValue,
      platformWatermarkUrl: key,
      platformWatermarkPreview: url,
    });

    toast.success(t("admin.settings.transcoding.upload_success"));
  } catch (error) {
    console.error(error);
    toast.error(t("admin.settings.transcoding.upload_error"));
  } finally {
    isUploading.value = false;
  }
};

const removeWatermark = () => {
  emit("update:modelValue", {
    ...props.modelValue,
    platformWatermarkUrl: "",
    platformWatermarkPreview: "",
  });
};
</script>
