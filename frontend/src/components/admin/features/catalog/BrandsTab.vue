<template>
  <div class="space-y-6">
    <div
      class="flex justify-between items-center bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm"
    >
      <h2 class="text-lg font-bold text-gray-900 dark:text-white">
        Управління брендами
      </h2>
      <AppButton
        variant="primary"
        class="flex items-center gap-2"
        @click="openAddBrandModal"
      >
        Додати бренд
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
                ID
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Назва бренду
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Slug
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Логотип URL
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Опис
              </th>
              <th
                class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
              >
                Дії
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="brand in brands"
              :key="brand.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
              <td
                class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white"
              >
                {{ brand.id }}
              </td>
              <td
                class="px-6 py-4 text-sm text-gray-900 dark:text-white font-bold"
              >
                {{ brand.name }}
              </td>
              <td
                class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono"
              >
                {{ brand.slug }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                <span
                  v-if="brand.logoPath"
                  class="truncate max-w-[150px] inline-block font-mono text-xs"
                >{{ brand.logoPath }}</span>
                <span
                  v-else
                  class="text-gray-300 dark:text-gray-600"
                >немає</span>
              </td>
              <td
                class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 truncate max-w-[200px]"
              >
                {{ brand.description || "—" }}
              </td>
              <td
                class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
              >
                <div class="flex justify-end gap-2">
                  <AppButton
                    variant="ghost"
                    size="sm"
                    class="!p-2 text-blue-600 dark:text-blue-400"
                    @click="openEditBrandModal(brand)"
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
                    @click="deleteBrand(brand.id)"
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
            <tr v-if="brands.length === 0">
              <td
                colspan="6"
                class="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
              >
                Брендів не створено.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Brand Modal -->
    <AppModal
      v-model="showBrandModal"
      :title="isEditing ? 'Редагувати бренд' : 'Додати бренд'"
      max-width="md"
    >
      <form
        class="space-y-4"
        @submit.prevent="saveBrand"
      >
        <AppInput
          v-model="brandForm.name"
          required
          label="Назва бренду"
          placeholder="напр. Apple чи Samsung"
        />

        <AppInput
          v-model="brandForm.logoPath"
          label="Логотип (URL)"
          placeholder="https://logo-url.com"
        />

        <AppTextarea
          v-model="brandForm.description"
          rows="3"
          label="Опис бренду"
          placeholder="Короткий опис..."
        />
      </form>

      <template #footer>
        <AppButton
          variant="secondary"
          class="mr-2"
          @click="showBrandModal = false"
        >
          Скасувати
        </AppButton>
        <AppButton
          variant="primary"
          @click="saveBrand"
        >
          Зберегти
        </AppButton>
      </template>
    </AppModal>
  </div>
</template>

<script setup>
import { ref } from "vue";
import api from "@/shared/services/api/apiClient";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";
import AppModal from "@/components/admin/ui/Feedback/AppModal.vue";

const props = defineProps({
  brands: { type: Array, required: true },
});

const emit = defineEmits(["refresh"]);

const showBrandModal = ref(false);
const isEditing = ref(false);

const brandForm = ref({
  id: null,
  name: "",
  logoPath: "",
  description: "",
});

const openAddBrandModal = () => {
  isEditing.value = false;
  brandForm.value = { id: null, name: "", logoPath: "", description: "" };
  showBrandModal.value = true;
};

const openEditBrandModal = (brand) => {
  isEditing.value = true;
  brandForm.value = {
    id: brand.id,
    name: brand.name,
    logoPath: brand.logoPath,
    description: brand.description,
  };
  showBrandModal.value = true;
};

const saveBrand = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/admin/brands/${brandForm.value.id}`, brandForm.value);
    } else {
      await api.post("/admin/brands", brandForm.value);
    }
    showBrandModal.value = false;
    emit("refresh");
  } catch (error) {
    console.error("Failed to save brand:", error);
  }
};

const deleteBrand = async (id) => {
  if (confirm("Ви впевнені, що хочете видалити цей бренд?")) {
    try {
      await api.delete(`/admin/brands/${id}`);
      emit("refresh");
    } catch (error) {
      console.error("Failed to delete brand:", error);
    }
  }
};
</script>
