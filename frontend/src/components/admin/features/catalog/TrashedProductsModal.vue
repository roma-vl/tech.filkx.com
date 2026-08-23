<template>
  <AppModal
    :model-value="modelValue"
    :title="t('admin.products.list.trash.title')"
    max-width="2xl"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div class="space-y-4">
      <div
        v-if="isLoading"
        class="flex items-center justify-center py-12 text-gray-400"
      >
        <div
          class="animate-spin rounded-full h-8 w-8 border-t-4 border-b-4 border-primary-500"
        />
      </div>

      <p
        v-else-if="products.length === 0"
        class="py-12 text-center text-gray-500 dark:text-gray-400"
      >
        {{ t("admin.products.list.trash.empty") }}
      </p>

      <div v-else class="space-y-3">
        <div class="flex items-center justify-between gap-3">
          <label
            class="flex items-center gap-2 text-sm font-bold text-gray-500 dark:text-gray-400 cursor-pointer select-none"
          >
            <input
              type="checkbox"
              class="w-4 h-4 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-750 dark:border-gray-650"
              :checked="allSelected"
              :indeterminate="someSelected && !allSelected"
              @change="toggleSelectAll"
            />
            {{
              t("admin.products.list.trash.selectedCount", {
                count: selectedIds.length,
              })
            }}
          </label>

          <AppButton
            variant="secondary"
            size="sm"
            :disabled="selectedIds.length === 0 || isBulkRestoring"
            @click="bulkRestore"
          >
            {{ t("admin.products.list.trash.restoreSelected") }}
          </AppButton>
        </div>

        <div class="space-y-2 max-h-[60vh] overflow-y-auto">
          <div
            v-for="product in products"
            :key="product.id"
            class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-700"
          >
            <input
              type="checkbox"
              class="w-4 h-4 shrink-0 text-primary bg-gray-100 border border-gray-300 rounded focus:ring-primary dark:bg-gray-750 dark:border-gray-650"
              :checked="selectedIds.includes(product.id)"
              @change="toggleSelected(product.id)"
            />
            <img
              :src="product.image"
              alt=""
              class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700 bg-gray-100 shrink-0"
            />
            <div class="flex-1 min-w-0">
              <div class="font-bold text-gray-900 dark:text-white truncate">
                {{ product.nameUk }}
              </div>
              <div class="text-xs text-gray-400">
                {{
                  t("admin.products.list.trash.deletedAt", {
                    date: formatDate(product.deletedAt),
                  })
                }}
              </div>
            </div>
            <AppButton
              variant="secondary"
              size="sm"
              :disabled="restoringId === product.id"
              @click="restoreProduct(product)"
            >
              {{ t("admin.products.list.trash.restore") }}
            </AppButton>
          </div>
        </div>
      </div>
    </div>
  </AppModal>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";
import { productApi } from "@/shared/services/api/productApi";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";

const { t, locale } = useI18n();
const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue", "restored"]);

const isLoading = ref(false);
const products = ref([]);
const restoringId = ref(null);
const selectedIds = ref([]);
const isBulkRestoring = ref(false);

const allSelected = computed(
  () =>
    products.value.length > 0 &&
    products.value.every((p) => selectedIds.value.includes(p.id)),
);

const someSelected = computed(() =>
  products.value.some((p) => selectedIds.value.includes(p.id)),
);

const toggleSelected = (id) => {
  const index = selectedIds.value.indexOf(id);
  if (index === -1) {
    selectedIds.value.push(id);
  } else {
    selectedIds.value.splice(index, 1);
  }
};

const toggleSelectAll = () => {
  selectedIds.value = allSelected.value ? [] : products.value.map((p) => p.id);
};

const fetchTrashed = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/admin/products/trashed");
    products.value = response.data.data;
    selectedIds.value = [];
  } catch (error) {
    console.error("Failed to load trashed products:", error);
    toast.error(t("admin.products.list.trash.loadError"));
  } finally {
    isLoading.value = false;
  }
};

const restoreProduct = async (product) => {
  restoringId.value = product.id;
  try {
    await api.post(`/admin/products/${product.id}/restore`);
    products.value = products.value.filter((p) => p.id !== product.id);
    selectedIds.value = selectedIds.value.filter((id) => id !== product.id);
    toast.success(
      t("admin.products.list.trash.restoreSuccess", { name: product.nameUk }),
    );
    emit("restored");
  } catch (error) {
    console.error("Failed to restore product:", error);
    const message = error.response?.data?.message;
    toast.error(message || t("admin.products.list.trash.restoreError"));
  } finally {
    restoringId.value = null;
  }
};

const bulkRestore = async () => {
  isBulkRestoring.value = true;
  try {
    const response = await productApi.adminBulkRestoreProducts(
      selectedIds.value,
    );
    const { restored, failed } = response.data.data;
    products.value = products.value.filter(
      (p) => !selectedIds.value.includes(p.id) || failed.includes(p.id),
    );
    selectedIds.value = [];
    if (failed.length > 0) {
      toast.error(
        t("admin.products.list.trash.bulkRestorePartial", {
          restored,
          failed: failed.length,
        }),
      );
    } else {
      toast.success(
        t("admin.products.list.trash.bulkRestoreSuccess", { count: restored }),
      );
    }
    if (restored > 0) emit("restored");
  } catch (error) {
    console.error("Failed to bulk restore products:", error);
    toast.error(t("admin.products.list.trash.bulkRestoreError"));
  } finally {
    isBulkRestoring.value = false;
  }
};

const formatDate = (isoString) => {
  if (!isoString) return "";
  return new Intl.DateTimeFormat(locale.value, {
    dateStyle: "medium",
    timeStyle: "short",
  }).format(new Date(isoString));
};

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) fetchTrashed();
  },
);
</script>
