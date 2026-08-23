<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div
      class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm"
    >
      <div>
        <h3 class="font-bold text-gray-900 dark:text-white">
          {{ t("admin.homeBanners.heading") }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ t("admin.homeBanners.headingDescription") }}
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
        {{ t("admin.homeBanners.addBanner") }}
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
                {{ t("admin.homeBanners.table.preview") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.homeBanners.table.title") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.homeBanners.table.link") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.homeBanners.table.order") }}
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.homeBanners.table.status") }}
              </th>
              <th
                class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ t("admin.homeBanners.table.actions") }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="banner in sortedBanners"
              :key="banner.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <td class="px-6 py-4">
                <img
                  :src="banner.imageUrl"
                  :alt="banner.title"
                  class="w-20 h-12 object-cover rounded-lg border border-gray-200 dark:border-gray-700"
                />
              </td>
              <td
                class="px-6 py-4 text-sm text-gray-900 dark:text-white font-bold max-w-[240px] truncate"
              >
                {{ banner.title || t("admin.homeBanners.table.noTitle") }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                <span class="font-mono text-xs">{{
                  linkTypeLabels[banner.linkType]
                }}</span>
                <span
                  v-if="banner.linkValue"
                  class="block text-xs text-gray-400 truncate max-w-[160px]"
                  >{{ banner.linkValue }}</span
                >
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                {{ banner.sortOrder }}
              </td>
              <td class="px-6 py-4">
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-bold"
                  :class="
                    banner.isActive
                      ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                      : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'
                  "
                >
                  {{
                    banner.isActive
                      ? t("admin.homeBanners.statusLabels.active")
                      : t("admin.homeBanners.statusLabels.inactive")
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
                    @click="openEditModal(banner)"
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
                    @click="deleteBanner(banner)"
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
            <tr v-if="sortedBanners.length === 0">
              <td
                colspan="6"
                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
              >
                {{ t("admin.homeBanners.empty") }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Banner Modal -->
    <AppModal
      v-model="showModal"
      :title="
        isEditing
          ? t('admin.homeBanners.form.editTitle')
          : t('admin.homeBanners.addBanner')
      "
      max-width="lg"
    >
      <form class="space-y-4" @submit.prevent="saveBanner">
        <div>
          <label
            class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
          >
            {{ t("admin.homeBanners.form.imageLabel") }}
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
                  ? t("admin.homeBanners.form.uploading")
                  : t("admin.homeBanners.form.uploadPrompt")
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

        <p class="text-xs text-gray-400 dark:text-gray-500 italic">
          {{ t("admin.homeBanners.form.overlayHint") }}
        </p>
        <AppInput
          v-model="form.badge"
          :label="t('admin.homeBanners.form.badgeLabel')"
          :placeholder="t('admin.homeBanners.form.badgePlaceholder')"
        />
        <AppInput
          v-model="form.subtitle"
          :label="t('admin.homeBanners.form.subtitleLabel')"
        />
        <AppInput
          v-model="form.title"
          :label="t('admin.homeBanners.table.title')"
        />
        <AppTextarea
          v-model="form.description"
          rows="3"
          :label="t('admin.homeBanners.form.descriptionLabel')"
        />
        <AppInput
          v-model="form.buttonLabel"
          :label="t('admin.homeBanners.form.buttonLabelLabel')"
          :placeholder="t('admin.homeBanners.form.buttonLabelPlaceholder')"
        />

        <AppSelect
          v-model="form.linkType"
          :label="t('admin.homeBanners.form.linkTypeLabel')"
          :options="linkTypeOptions"
          option-value="value"
          option-label="label"
        />
        <AppSelect
          v-if="form.linkType === 'category'"
          v-model="form.linkValue"
          searchable
          required
          :label="linkValueLabel"
          :search-placeholder="linkValuePlaceholder"
          :options="categories"
          option-value="slug"
          option-label="nameUk"
        />
        <AppSelect
          v-else-if="form.linkType === 'product'"
          v-model="form.linkValue"
          searchable
          required
          :label="linkValueLabel"
          :search-placeholder="linkValuePlaceholder"
          :options="products"
          option-value="id"
          option-label="nameUk"
        />
        <AppSelect
          v-else-if="form.linkType === 'promo'"
          v-model="form.linkValue"
          searchable
          required
          :label="linkValueLabel"
          :search-placeholder="linkValuePlaceholder"
          :options="promoPages"
          option-value="slug"
          option-label="title"
        />
        <AppInput
          v-else-if="form.linkType === 'url'"
          v-model="form.linkValue"
          required
          :label="linkValueLabel"
          :placeholder="linkValuePlaceholder"
        />

        <AppInput
          v-model.number="form.sortOrder"
          type="number"
          :label="t('admin.homeBanners.form.sortOrderLabel')"
        />

        <AppToggle
          v-model="form.isActive"
          :label="t('admin.homeBanners.statusLabels.active')"
          :description="t('admin.homeBanners.form.activeDescription')"
        />
      </form>

      <template #footer>
        <AppButton variant="secondary" class="mr-2" @click="showModal = false">
          {{ t("admin.homeBanners.form.cancel") }}
        </AppButton>
        <AppButton
          variant="primary"
          class="!bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
          :disabled="
            !form.imagePath || (form.linkType !== 'catalog' && !form.linkValue)
          "
          @click="saveBanner"
        >
          {{ t("admin.homeBanners.form.save") }}
        </AppButton>
      </template>
    </AppModal>

    <!-- Delete Confirmation Modal -->
    <AppConfirmModal
      v-model="showDeleteModal"
      :title="t('admin.homeBanners.deleteModal.title')"
      :message="
        t('admin.homeBanners.deleteModal.message', {
          title: bannerToDelete?.title || '',
        })
      "
      :confirm-text="t('admin.homeBanners.deleteModal.confirm')"
      :cancel-text="t('admin.homeBanners.form.cancel')"
      :loading="deletingBanner"
      @confirm="confirmDeleteBanner"
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

const linkTypeOptions = computed(() => [
  { value: "catalog", label: t("admin.homeBanners.linkTypes.catalog") },
  { value: "category", label: t("admin.homeBanners.linkTypes.category") },
  { value: "product", label: t("admin.homeBanners.linkTypes.product") },
  { value: "promo", label: t("admin.homeBanners.linkTypes.promo") },
  { value: "url", label: t("admin.homeBanners.linkTypes.url") },
]);
const linkTypeLabels = computed(() =>
  Object.fromEntries(linkTypeOptions.value.map((o) => [o.value, o.label])),
);

const linkValueLabel = computed(() => {
  switch (form.value.linkType) {
    case "category":
      return t("admin.homeBanners.linkValueLabels.category");
    case "product":
      return t("admin.homeBanners.linkValueLabels.product");
    case "promo":
      return t("admin.homeBanners.linkValueLabels.promo");
    case "url":
      return t("admin.homeBanners.linkValueLabels.url");
    default:
      return t("admin.homeBanners.linkValueLabels.default");
  }
});
const linkValuePlaceholder = computed(() => {
  switch (form.value.linkType) {
    case "category":
      return t("admin.homeBanners.linkValuePlaceholders.category");
    case "product":
      return t("admin.homeBanners.linkValuePlaceholders.product");
    case "promo":
      return t("admin.homeBanners.linkValuePlaceholders.promo");
    case "url":
      return t("admin.homeBanners.linkValuePlaceholders.url");
    default:
      return t("admin.homeBanners.linkValuePlaceholders.default");
  }
});

const categories = ref([]);
const products = ref([]);
const promoPages = ref([]);

const fetchPickerData = async () => {
  try {
    const [categoriesRes, productsRes, promoPagesRes] = await Promise.all([
      api.get("/admin/categories"),
      api.get("/admin/products"),
      api.get("/admin/promo-pages"),
    ]);
    categories.value = categoriesRes.data.data;
    products.value = productsRes.data.data;
    promoPages.value = promoPagesRes.data.data;
  } catch (error) {
    console.error("Failed to load categories/products/promo pages:", error);
  }
};

const banners = ref([]);
const sortedBanners = computed(() =>
  [...banners.value].sort((a, b) => a.sortOrder - b.sortOrder),
);

const fetchBanners = async () => {
  try {
    const { data } = await api.get("/admin/home-banners");
    banners.value = data.data;
  } catch (error) {
    console.error("Failed to load home banners:", error);
    toast.error(t("admin.homeBanners.alerts.loadError"));
  }
};

onMounted(() => {
  fetchBanners();
  fetchPickerData();
});

const showModal = ref(false);
const isEditing = ref(false);
const uploading = ref(false);

const defaultForm = () => ({
  id: null,
  badge: "",
  subtitle: "",
  title: "",
  description: "",
  imagePath: "",
  imageUrl: "",
  buttonLabel: "",
  linkType: "catalog",
  linkValue: "",
  isActive: true,
  sortOrder: banners.value.length,
});

const form = ref(defaultForm());

const openAddModal = () => {
  isEditing.value = false;
  form.value = defaultForm();
  showModal.value = true;
};

const openEditModal = (banner) => {
  isEditing.value = true;
  form.value = { ...banner };
  showModal.value = true;
};

const uploadImage = async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const formData = new FormData();
  formData.append("image", file);
  uploading.value = true;
  try {
    const { data } = await api.post("/admin/home-banners/upload", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    form.value.imagePath = data.data.path;
    form.value.imageUrl = data.data.url;
  } catch (error) {
    console.error("Failed to upload banner image:", error);
    toast.error(t("admin.homeBanners.alerts.uploadError"));
  } finally {
    uploading.value = false;
  }
};

const saveBanner = async () => {
  if (!form.value.imagePath) return;
  if (form.value.linkType !== "catalog" && !form.value.linkValue) {
    toast.warning(t("admin.homeBanners.alerts.linkValueRequired"));
    return;
  }
  const payload = {
    badge: form.value.badge || null,
    subtitle: form.value.subtitle || null,
    title: form.value.title,
    description: form.value.description || null,
    imagePath: form.value.imagePath,
    buttonLabel: form.value.buttonLabel || null,
    linkType: form.value.linkType,
    // The "product" picker's option-value is a numeric id (AppSelect emits it
    // as a number), but linkValue is validated as a string on the backend.
    linkValue:
      form.value.linkType === "catalog" ? null : String(form.value.linkValue),
    isActive: form.value.isActive,
    sortOrder: form.value.sortOrder,
  };

  try {
    if (isEditing.value) {
      await api.put(`/admin/home-banners/${form.value.id}`, payload);
    } else {
      await api.post("/admin/home-banners", payload);
    }
    showModal.value = false;
    toast.success(t("admin.homeBanners.alerts.saveSuccess"));
    fetchBanners();
  } catch (error) {
    console.error("Failed to save banner:", error);
    toast.error(t("admin.homeBanners.alerts.saveError"));
  }
};

const showDeleteModal = ref(false);
const bannerToDelete = ref(null);
const deletingBanner = ref(false);

const deleteBanner = (banner) => {
  bannerToDelete.value = banner;
  showDeleteModal.value = true;
};

const confirmDeleteBanner = async () => {
  if (!bannerToDelete.value) return;
  deletingBanner.value = true;
  try {
    await api.delete(`/admin/home-banners/${bannerToDelete.value.id}`);
    toast.success(t("admin.homeBanners.alerts.deleteSuccess"));
    showDeleteModal.value = false;
    fetchBanners();
  } catch (error) {
    console.error("Failed to delete banner:", error);
    toast.error(t("admin.homeBanners.alerts.deleteError"));
  } finally {
    deletingBanner.value = false;
  }
};
</script>
