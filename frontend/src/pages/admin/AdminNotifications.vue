<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import { useToast } from "vue-toastification";

import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";
import AppRadio from "@/components/admin/ui/Form/AppRadio.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();
const toast = useToast();

const form = ref({
  title: "",
  message: "",
  type: "info",
  recipients: "all",
  user_ids: "", // comma separated for manual input
  action_url: "",
});

const isSending = ref(false);
const error = ref(null);

const typeOptions = [
  { id: "info", name: "Info" },
  { id: "success", name: "Success" },
  { id: "warning", name: "Warning" },
  { id: "error", name: "Error" },
];

const recipientOptions = [
  { value: "all", label: "admin.notifications.recipients.all" },
  { value: "selected", label: "admin.notifications.recipients.selected" },
];

const handleSubmit = async () => {
  error.value = null;
  isSending.value = true;

  try {
    const payload = {
      ...form.value,
      user_ids:
        form.value.recipients === "selected"
          ? form.value.user_ids
              .split(",")
              .map((id) => parseInt(id.trim()))
              .filter((id) => !isNaN(id))
          : [],
    };

    await api.post("/admin/notifications/broadcast", payload);

    toast.success(t("admin.notifications.success_queued"));
    form.value.title = "";
    form.value.message = "";
    form.value.user_ids = "";
    form.value.action_url = "";
  } catch (err) {
    console.error(err);
    error.value = err.response?.data?.message || "Failed to send notification";
    toast.error(error.value);
  } finally {
    isSending.value = false;
  }
};
</script>

<template>
  <div class="space-y-8 animate-in fade-in duration-500">
    <div
      class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm relative z-10"
    >
      <div class="p-8">
        <form
          class="space-y-8 max-w-3xl"
          @submit.prevent="handleSubmit"
        >
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <AppInput
              v-model="form.title"
              :label="t('admin.notifications.form.title')"
              required
              :placeholder="t('admin.notifications.form.placeholders.title')"
            />

            <AppSelect
              v-model="form.type"
              :label="t('admin.notifications.form.type')"
              :options="typeOptions"
            />
          </div>

          <AppTextarea
            v-model="form.message"
            :label="t('admin.notifications.form.message')"
            :rows="5"
            required
            :placeholder="t('admin.notifications.form.placeholders.message')"
          />

          <div class="space-y-4">
            <label
              class="block text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-widest"
            >
              {{ t("admin.notifications.form.recipients") }}
            </label>
            <div class="flex flex-wrap gap-6">
              <AppRadio
                v-for="opt in recipientOptions"
                :key="opt.value"
                v-model="form.recipients"
                :value="opt.value"
                name="recipients"
                :label="t(opt.label)"
              />
            </div>
          </div>

          <div
            v-if="form.recipients === 'selected'"
            class="animate-in slide-in-from-top-4 fade-in duration-300"
          >
            <AppInput
              v-model="form.user_ids"
              :label="t('admin.notifications.form.user_ids')"
              :placeholder="t('admin.notifications.form.placeholders.user_ids')"
            />
          </div>

          <AppInput
            v-model="form.action_url"
            :label="t('admin.notifications.form.action_url')"
            type="url"
            :placeholder="t('admin.notifications.form.placeholders.action_url')"
          />

          <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50">
            <AppButton
              type="submit"
              :loading="isSending"
            >
              {{ t("admin.notifications.form.submit") }}
            </AppButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
