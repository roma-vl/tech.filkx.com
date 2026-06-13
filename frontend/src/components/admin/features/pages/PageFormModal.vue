<template>
  <AppModal
    :model-value="modelValue"
    :title="page ? 'Редагувати сторінку' : 'Нова сторінка'"
    max-width="3xl"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div class="space-y-6">
      <!-- Settings Panel (Slug) -->
      <div class="bg-gray-50/50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-200/50 dark:border-gray-700/50 space-y-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-[#00a046]">
          Налаштування URL
        </h3>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-305 mb-1">
            Адреса сторінки (Slug)
          </label>
          <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <span class="text-gray-400 text-sm font-mono">/pages/</span>
            </div>
            <input
              v-model="pageForm.slug"
              type="text"
              placeholder="наприклад: shipping-and-payment"
              class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-2.5 pl-16 pr-3 text-sm focus:border-[#00a046] focus:ring-1 focus:ring-[#00a046] focus:outline-none text-gray-800 dark:text-gray-100 font-mono shadow-sm"
            >
          </div>
          <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5 leading-relaxed">
            Лише латинські літери, цифри та дефіси. Якщо залишити порожнім, буде згенеровано автоматично з англійського заголовка.
          </p>
        </div>
      </div>

      <!-- Content Fields -->
      <div class="bg-white dark:bg-gray-800 rounded-xl space-y-5">
        <!-- Language tabs switcher -->
        <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-700 pb-3">
          <h3 class="text-xs font-bold uppercase tracking-wider text-[#00a046]">
            Вміст сторінки
          </h3>
          <div class="flex gap-1 bg-gray-105 dark:bg-gray-700 rounded-lg p-1 shrink-0">
            <AppButton
              variant="text"
              class="!px-3.5 !py-1 rounded-md text-xs font-semibold !no-underline"
              :class="langTab === 'uk' ? 'bg-white dark:bg-gray-600 text-gray-800 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
              @click="langTab = 'uk'"
            >
              🇺🇦 Українська
            </AppButton>
            <AppButton
              variant="text"
              class="!px-3.5 !py-1 rounded-md text-xs font-semibold !no-underline"
              :class="langTab === 'en' ? 'bg-white dark:bg-gray-600 text-gray-800 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
              @click="langTab = 'en'"
            >
              🇬🇧 English
            </AppButton>
          </div>
        </div>

        <!-- UK Tab Content -->
        <div
          v-show="langTab === 'uk'"
          class="space-y-4"
        >
          <AppInput
            v-model="pageForm.titleUk"
            label="Заголовок сторінки (Українська) *"
            placeholder="Введіть заголовок..."
            class="font-bold text-base"
          />
          <div>
            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
              Контент сторінки (Українська) *
            </label>
            <RichEditor
              :key="`uk-${page?.id || 'new'}`"
              v-model="pageForm.contentUk"
              @upload-image="handleImageUpload"
            />
          </div>
        </div>

        <!-- EN Tab Content -->
        <div
          v-show="langTab === 'en'"
          class="space-y-4"
        >
          <AppInput
            v-model="pageForm.titleEn"
            label="Page Title (English) *"
            placeholder="Enter page title..."
            class="font-bold text-base"
          />
          <div>
            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
              Page Content (English) *
            </label>
            <RichEditor
              :key="`en-${page?.id || 'new'}`"
              v-model="pageForm.contentEn"
              @upload-image="handleImageUpload"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Footer actions -->
    <template #footer>
      <div class="flex flex-col sm:flex-row items-center justify-between w-full gap-4">
        <div class="flex items-center gap-3 w-full sm:w-auto">
          <AppSelect
            v-model="pageForm.status"
            :options="[
              { id: 'draft', name: 'Чернетка' },
              { id: 'published', name: 'Опубліковано' }
            ]"
            option-value="id"
            option-label="name"
            class="w-full sm:w-40"
          />
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
          <AppButton
            variant="white"
            class="w-full sm:w-auto text-sm"
            @click="$emit('update:modelValue', false)"
          >
            Скасувати
          </AppButton>
          <AppButton
            variant="primary"
            :loading="saving"
            class="w-full sm:w-auto text-sm !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046]"
            @click="savePage"
          >
            Зберегти
          </AppButton>
        </div>
      </div>
    </template>
  </AppModal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import RichEditor from "@/components/admin/features/blog/RichEditor.vue";

const props = defineProps({
  modelValue: Boolean,
  page: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(["update:modelValue", "refresh"]);

const toast = useToast();
const langTab = ref("uk");
const saving = ref(false);

const defaultPageForm = () => ({
  titleUk: "",
  titleEn: "",
  contentUk: "",
  contentEn: "",
  slug: "",
  status: "published"
});

const pageForm = ref(defaultPageForm());

watch(
  () => props.page,
  (newPage) => {
    langTab.value = "uk";
    if (newPage) {
      pageForm.value = {
        titleUk: newPage.titleUk || "",
        titleEn: newPage.titleEn || "",
        contentUk: newPage.contentUk || "",
        contentEn: newPage.contentEn || "",
        slug: newPage.slug || "",
        status: newPage.status || "published"
      };
    } else {
      pageForm.value = defaultPageForm();
    }
  },
  { immediate: true }
);

const handleImageUpload = async (file, callback) => {
  const form = new FormData();
  form.append("image", file);
  try {
    const { data } = await api.post("/admin/blog/upload", form, {
      headers: { "Content-Type": "multipart/form-data" }
    });
    callback(data.data.url);
  } catch (e) {
    toast.error("Помилка завантаження зображення");
    console.error(e);
  }
};

const savePage = async () => {
  if (!pageForm.value.titleUk || !pageForm.value.titleEn) {
    toast.warning("Заголовок є обов'язковим для обох мов");
    return;
  }
  if (!pageForm.value.contentUk || !pageForm.value.contentEn) {
    toast.warning("Вміст сторінки є обов'язковим для обох мов");
    return;
  }
  saving.value = true;
  try {
    const payload = {
      titleUk: pageForm.value.titleUk,
      titleEn: pageForm.value.titleEn,
      contentUk: pageForm.value.contentUk,
      contentEn: pageForm.value.contentEn,
      slug: pageForm.value.slug || undefined,
      status: pageForm.value.status
    };

    if (props.page) {
      await api.put(`/admin/pages/${props.page.id}`, payload);
      toast.success("Сторінку оновлено успішно");
    } else {
      await api.post("/admin/pages", payload);
      toast.success("Сторінку створено успішно");
    }
    emit("update:modelValue", false);
    emit("refresh");
  } catch (e) {
    toast.error(e.response?.data?.message || "Помилка збереження сторінки");
    console.error(e);
  } finally {
    saving.value = false;
  }
};
</script>
