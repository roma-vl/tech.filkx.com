<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm"
    >
      <div>
        <h3 class="font-bold text-gray-900 dark:text-white">
          {{ t("admin.promoPages.heading") }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ t("admin.promoPages.headingDescription") }}
        </p>
      </div>

      <AppButton
        variant="primary"
        class="flex items-center gap-2 shrink-0 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
        @click="openAddModal"
      >
        <svg
          class="w-5 h-5"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"
          />
        </svg>
        {{ t("admin.promoPages.addPromoPage") }}
      </AppButton>
    </div>

    <div
      class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden"
    >
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.promoPages.table.preview") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.promoPages.table.title") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.promoPages.table.slug") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.promoPages.table.products") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.promoPages.table.order") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.promoPages.table.status") }}
              </th>
              <th
                class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.promoPages.table.actions") }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="promoPage in sortedPromoPages"
              :key="promoPage.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <td class="px-6 py-4">
                <img
                  :src="promoPage.imageUrl"
                  :alt="promoPage.title"
                  class="w-20 h-12 object-cover rounded-lg border border-gray-200 dark:border-gray-700"
                />
              </td>
              <td
                class="px-6 py-4 text-sm text-gray-900 dark:text-white font-bold max-w-[240px] truncate"
              >
                {{ promoPage.title }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                <span class="font-mono text-xs">{{ promoPage.slug }}</span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ promoPage.productsCount }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ promoPage.sortOrder }}
              </td>
              <td class="px-6 py-4">
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-bold"
                  :class="
                    promoPage.isActive
                      ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                      : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                  "
                >
                  {{
                    promoPage.isActive
                      ? t("admin.promoPages.statusLabels.active")
                      : t("admin.promoPages.statusLabels.inactive")
                  }}
                </span>
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <div class="flex justify-end gap-2">
                  <AppButton
                    variant="ghost"
                    size="sm"
                    class="!p-2 text-blue-600 dark:text-blue-400"
                    @click="openEditModal(promoPage)"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                      />
                    </svg>
                  </AppButton>
                  <AppButton
                    variant="ghost"
                    size="sm"
                    class="!p-2 text-red-600 dark:text-red-400"
                    @click="deletePromoPage(promoPage)"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </AppButton>
                </div>
              </td>
            </tr>
            <tr v-if="sortedPromoPages.length === 0">
              <td
                colspan="7"
                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
              >
                {{ t("admin.promoPages.empty") }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Promo Page Modal -->
    <AppModal
      v-model="showModal"
      :title="
        isEditing
          ? t('admin.promoPages.form.editTitle')
          : t('admin.promoPages.addPromoPage')
      "
      max-width="lg"
    >
      <form class="space-y-4" @submit.prevent="savePromoPage">
        <div>
          <label
            class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
          >
            {{ t("admin.promoPages.form.imageLabel") }}
          </label>
          <div class="relative">
            <div
              v-if="form.imageUrl"
              class="relative rounded-xl overflow-hidden aspect-[21/9] mb-2 border border-gray-200 dark:border-gray-700"
            >
              <img :src="form.imageUrl" class="w-full h-full object-cover" />
              <AppButton
                variant="ghost"
                size="sm"
                class="absolute top-2 right-2 !p-1.5 bg-black/50 hover:bg-black/70 rounded-lg !text-white border-none shadow-none"
                @click="
                  form.imageUrl = '';
                  form.imagePath = '';
                "
              >
                ✕
              </AppButton>
            </div>
            <label
              class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-250 dark:border-gray-700 rounded-xl cursor-pointer hover:border-[#00a046] hover:bg-emerald-50/10 dark:hover:bg-emerald-950/10 transition-colors"
            >
              <span class="text-xs text-gray-400">{{
                uploading
                  ? t("admin.promoPages.form.uploading")
                  : t("admin.promoPages.form.uploadPrompt")
              }}</span>
              <input
                type="file"
                accept="image/*"
                class="sr-only"
                @change="uploadImage"
              />
            </label>
          </div>
        </div>

        <AppInput
          v-model="form.badge"
          :label="t('admin.promoPages.form.badgeLabel')"
          :placeholder="t('admin.promoPages.form.badgePlaceholder')"
        />
        <AppInput
          v-model="form.title"
          required
          :label="t('admin.promoPages.form.title')"
        />
        <AppInput
          v-model="form.subtitle"
          :label="t('admin.promoPages.form.subtitleLabel')"
        />
        <AppTextarea
          v-model="form.description"
          rows="3"
          :label="t('admin.promoPages.form.descriptionLabel')"
        />

        <div>
          <label
            class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
          >
            {{ t("admin.promoPages.form.productsLabel") }}
          </label>
          <div class="flex gap-2">
            <AppSelect
              v-model="productToAdd"
              searchable
              :placeholder="t('admin.promoPages.form.addProductPlaceholder')"
              :search-placeholder="
                t('admin.promoPages.form.addProductPlaceholder')
              "
              :options="availableProducts"
              option-value="id"
              option-label="nameUk"
              class="flex-1"
            />
            <AppButton
              type="button"
              variant="secondary"
              class="whitespace-nowrap"
              :disabled="!productToAdd"
              @click="addProduct"
            >
              {{ t("admin.promoPages.form.addButton") }}
            </AppButton>
          </div>
          <div v-if="selectedProducts.length" class="flex flex-wrap gap-2 mt-3">
            <span
              v-for="product in selectedProducts"
              :key="product.id"
              class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 rounded-full text-xs font-medium bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400"
            >
              {{ product.nameUk }}
              <button
                type="button"
                class="hover:text-red-500"
                @click="removeProduct(product.id)"
              >
                &times;
              </button>
            </span>
          </div>
          <p v-else class="text-xs text-gray-400 italic mt-2">
            {{ t("admin.promoPages.form.noProductsSelected") }}
          </p>
        </div>

        <AppInput
          v-model.number="form.sortOrder"
          type="number"
          :label="t('admin.promoPages.form.sortOrderLabel')"
        />

        <AppToggle
          v-model="form.isActive"
          :label="t('admin.promoPages.statusLabels.active')"
          :description="t('admin.promoPages.form.activeDescription')"
        />
      </form>

      <template #footer>
        <AppButton variant="secondary" class="mr-2" @click="showModal = false">
          {{ t("admin.promoPages.form.cancel") }}
        </AppButton>
        <AppButton
          variant="primary"
          class="!bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
          :disabled="!form.title || !form.imagePath"
          @click="savePromoPage"
        >
          {{ t("admin.promoPages.form.save") }}
        </AppButton>
      </template>
    </AppModal>

    <!-- Delete Confirmation Modal -->
    <AppConfirmModal
      v-model="showDeleteModal"
      :title="t('admin.promoPages.deleteModal.title')"
      :message="
        t('admin.promoPages.deleteModal.message', {
          title: promoPageToDelete?.title || '',
        })
      "
      :confirm-text="t('admin.promoPages.deleteModal.confirm')"
      :cancel-text="t('admin.promoPages.form.cancel')"
      :loading="deletingPromoPage"
      @confirm="confirmDeletePromoPage"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppTextarea from "@/components/admin/ui/AppTextarea.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppToggle from "@/components/admin/ui/AppToggle.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppConfirmModal from "@/components/admin/ui/AppConfirmModal.vue";

const { t } = useI18n();
const toast = useToast();

const promoPages = ref([]);
const sortedPromoPages = computed(() =>
  [...promoPages.value].sort((a, b) => a.sortOrder - b.sortOrder),
);

const fetchPromoPages = async () => {
  try {
    const { data } = await api.get("/admin/promo-pages");
    promoPages.value = data.data;
  } catch (error) {
    console.error("Failed to load promo pages:", error);
    toast.error(t("admin.promoPages.alerts.loadError"));
  }
};

const allProducts = ref([]);
const fetchProducts = async () => {
  try {
    const { data } = await api.get("/admin/products");
    allProducts.value = data.data;
  } catch (error) {
    console.error("Failed to load products:", error);
  }
};

onMounted(() => {
  fetchPromoPages();
  fetchProducts();
});

const showModal = ref(false);
const isEditing = ref(false);
const uploading = ref(false);

const defaultForm = () => ({
  id: null,
  badge: "",
  title: "",
  subtitle: "",
  description: "",
  imagePath: "",
  imageUrl: "",
  isActive: true,
  sortOrder: promoPages.value.length,
  productIds: [],
});

const form = ref(defaultForm());
const productToAdd = ref("");

const selectedProducts = computed(() =>
  allProducts.value.filter((p) => form.value.productIds.includes(p.id)),
);
const availableProducts = computed(() =>
  allProducts.value.filter((p) => !form.value.productIds.includes(p.id)),
);

const addProduct = () => {
  if (!productToAdd.value) return;
  form.value.productIds = [
    ...form.value.productIds,
    Number(productToAdd.value),
  ];
  productToAdd.value = "";
};

const removeProduct = (id) => {
  form.value.productIds = form.value.productIds.filter((pid) => pid !== id);
};

const openAddModal = () => {
  isEditing.value = false;
  form.value = defaultForm();
  showModal.value = true;
};

const openEditModal = (promoPage) => {
  isEditing.value = true;
  form.value = {
    ...promoPage,
    productIds: (promoPage.products || []).map((p) => p.id),
  };
  showModal.value = true;
};

const uploadImage = async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const formData = new FormData();
  formData.append("image", file);
  uploading.value = true;
  try {
    const { data } = await api.post("/admin/promo-pages/upload", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    form.value.imagePath = data.data.path;
    form.value.imageUrl = data.data.url;
  } catch (error) {
    console.error("Failed to upload promo page image:", error);
    toast.error(t("admin.promoPages.alerts.uploadError"));
  } finally {
    uploading.value = false;
  }
};

const savePromoPage = async () => {
  if (!form.value.title || !form.value.imagePath) return;
  const payload = {
    badge: form.value.badge || null,
    title: form.value.title,
    subtitle: form.value.subtitle || null,
    description: form.value.description || null,
    imagePath: form.value.imagePath,
    isActive: form.value.isActive,
    sortOrder: form.value.sortOrder,
    productIds: form.value.productIds,
  };

  try {
    if (isEditing.value) {
      await api.put(`/admin/promo-pages/${form.value.id}`, payload);
    } else {
      await api.post("/admin/promo-pages", payload);
    }
    showModal.value = false;
    toast.success(t("admin.promoPages.alerts.saveSuccess"));
    fetchPromoPages();
  } catch (error) {
    console.error("Failed to save promo page:", error);
    toast.error(t("admin.promoPages.alerts.saveError"));
  }
};

const showDeleteModal = ref(false);
const promoPageToDelete = ref(null);
const deletingPromoPage = ref(false);

const deletePromoPage = (promoPage) => {
  promoPageToDelete.value = promoPage;
  showDeleteModal.value = true;
};

const confirmDeletePromoPage = async () => {
  if (!promoPageToDelete.value) return;
  deletingPromoPage.value = true;
  try {
    await api.delete(`/admin/promo-pages/${promoPageToDelete.value.id}`);
    toast.success(t("admin.promoPages.alerts.deleteSuccess"));
    showDeleteModal.value = false;
    fetchPromoPages();
  } catch (error) {
    console.error("Failed to delete promo page:", error);
    toast.error(t("admin.promoPages.alerts.deleteError"));
  } finally {
    deletingPromoPage.value = false;
  }
};
</script>
