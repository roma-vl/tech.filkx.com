<template>
  <AppModal
    :model-value="modelValue"
    :title="
      $t('admin.accounting.invoices.details_title', {
        number: invoice?.invoiceNumber,
      })
    "
    max-width="lg"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div
      v-if="invoice"
      class="space-y-4"
    >
      <!-- Header Info -->
      <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
          <p class="text-gray-500 dark:text-gray-400">
            {{ $t("admin.accounting.invoices.columns.user") }}
          </p>
          <p class="font-medium text-gray-900 dark:text-white">
            {{ invoice.user?.name }}
          </p>
          <p class="text-xs text-gray-500">
            {{ invoice.user?.email }}
          </p>
        </div>
        <div class="text-right">
          <p class="text-gray-500 dark:text-gray-400">
            {{ $t("admin.accounting.invoices.columns.status") }}
          </p>
          <AdminBadge :color="getStatusColor(invoice.status)">
            {{ invoice.status }}
          </AdminBadge>
        </div>
      </div>

      <!-- Dates -->
      <div
        class="grid grid-cols-2 gap-4 text-sm border-t border-gray-100 dark:border-gray-700 pt-4"
      >
        <div>
          <p class="text-gray-500 dark:text-gray-400">
            {{ $t("admin.accounting.invoices.columns.issued") }}
          </p>
          <p class="font-medium text-gray-900 dark:text-white">
            {{
              invoice.issuedAt || invoice.createdAt
                ? formatDate(invoice.issuedAt || invoice.createdAt)
                : "-"
            }}
          </p>
        </div>
        <div class="text-right">
          <p class="text-gray-500 dark:text-gray-400">
            {{ $t("admin.accounting.invoices.due_date") || "Due Date" }}
          </p>
          <p class="font-medium text-gray-900 dark:text-white">
            {{ invoice.dueDate ? formatDate(invoice.dueDate) : "-" }}
          </p>
        </div>
      </div>

      <!-- Line Items -->
      <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
          {{ $t("admin.accounting.invoices.items") || "Items" }}
        </h4>
        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 space-y-2">
          <div
            v-for="item in invoice.items"
            :key="item.id"
            class="flex justify-between text-sm"
          >
            <div>
              <p class="text-gray-900 dark:text-white">
                {{ item.description }}
              </p>
              <p
                v-if="item.quantity > 1"
                class="text-xs text-gray-500"
              >
                x{{ item.quantity }}
              </p>
            </div>
            <p class="font-medium text-gray-900 dark:text-white">
              {{ formatMoney(item.totalMinor, invoice.currency) }}
            </p>
          </div>
          <div
            v-if="!invoice.items || invoice.items.length === 0"
            class="text-center text-gray-500 text-sm italic"
          >
            {{ $t("common.no_data") }}
          </div>
        </div>
      </div>

      <!-- Total -->
      <div
        class="flex justify-between items-center border-t border-gray-100 dark:border-gray-700 pt-4"
      >
        <p class="text-base font-bold text-gray-900 dark:text-white">
          {{ $t("admin.accounting.invoices.columns.total") }}
        </p>
        <p class="text-xl font-bold text-gray-900 dark:text-white">
          {{ formatMoney(invoice.totalMinor, invoice.currency) }}
        </p>
      </div>
    </div>

    <template #footer>
      <AppButton
        variant="secondary"
        @click="$emit('update:modelValue', false)"
      >
        {{ $t("common.close") || "Close" }}
      </AppButton>
    </template>
  </AppModal>
</template>

<script setup>
import AppModal from "@/components/admin/ui/AppModal.vue";
import AdminBadge from "@/components/admin/ui/AdminBadge.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

defineProps({
  modelValue: Boolean,
  invoice: Object,
});

defineEmits(["update:modelValue"]);

const formatMoney = (amountMinor, currency = "USD") => {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: currency,
  }).format(amountMinor / 100);
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

const getStatusColor = (status) => {
  switch (status) {
    case "paid":
      return "green";
    case "issued":
      return "blue";
    case "draft":
      return "gray";
    case "cancelled":
      return "red";
    default:
      return "gray";
  }
};
</script>
