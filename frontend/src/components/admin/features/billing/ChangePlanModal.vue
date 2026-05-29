<template>
  <AppModal
    :model-value="true"
    :title="t('admin.billing.subscriptions.changePlan.title')"
    @update:model-value="(val) => !val && $emit('close')"
  >
    <div class="px-2 py-2 space-y-6">
      <div
        class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-100 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 space-y-1"
      >
        <p>
          <strong class="text-gray-900 dark:text-gray-200">{{
              t("admin.billing.subscriptions.changePlan.currentUser")
            }}:</strong>
          {{ subscription.user?.name }} ({{ subscription.user?.email }})
        </p>
        <p>
          <strong class="text-gray-900 dark:text-gray-200">{{
              t("admin.billing.subscriptions.changePlan.currentPlan")
            }}:</strong>
          {{ subscription.plan?.name }}
        </p>
      </div>

      <div class="space-y-4">
        <AppSelect
          v-model="form.planId"
          :label="t('admin.billing.subscriptions.changePlan.selectPlan')"
          :options="plans"
          label-key="name"
          value-key="id"
        />

        <AppSelect
          v-model="form.billingCycle"
          :label="t('admin.billing.subscriptions.changePlan.cycleLabel')"
          :options="cycleOptions"
        />

        <AppTextarea
          v-model="form.reason"
          :label="t('admin.billing.subscriptions.changePlan.reasonLabel')"
          rows="3"
        />
      </div>
    </div>

    <template #footer>
      <div class="flex flex-col sm:flex-row-reverse gap-3 w-full">
        <AppButton
          variant="primary"
          size="lg"
          class="w-full sm:w-auto"
          :loading="loading"
          @click="submit"
        >
          {{ t("admin.billing.subscriptions.changePlan.save") }}
        </AppButton>
        <AppButton
          variant="secondary"
          size="lg"
          class="w-full sm:w-auto"
          @click="$emit('close')"
        >
          {{ t("admin.billing.subscriptions.changePlan.cancel") }}
        </AppButton>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import {computed, ref} from "vue";
import api from "@/services/api";
import {useI18n} from "vue-i18n";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";
import AppSelect from "@/components/admin/ui/Form/AppSelect.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";
import AppButton from "@/components/application/ui/Button/AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  subscription: Object,
  plans: Array,
});

const emit = defineEmits(["close", "updated"]);

const loading = ref(false);

const form = ref({
  planId: props.subscription.plan?.id,
  billingCycle: props.subscription.billingCycle || "monthly",
  reason: "",
});

const cycleOptions = computed(() => [
  {
    id: "monthly",
    name: t("admin.billing.subscriptions.changePlan.cycles.monthly"),
  },
  {
    id: "quarterly",
    name: t("admin.billing.subscriptions.changePlan.cycles.quarterly"),
  },
  {
    id: "semi_annual",
    name: t("admin.billing.subscriptions.changePlan.cycles.semi_annual"),
  },
  {
    id: "annual",
    name: t("admin.billing.subscriptions.changePlan.cycles.annual"),
  },
]);

const submit = async () => {
  loading.value = true;
  try {
    await api.post(
      `/admin/billing/subscriptions/${props.subscription.id}/change-plan`,
      form.value,
    );

    emit("updated");
  } catch (e) {
    alert(e.response?.data?.message ?? "Failed to change plan");
  } finally {
    loading.value = false;
  }
};
</script>
