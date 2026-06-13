<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center">
      <!-- Search bar & filters toggle button -->
      <div class="flex items-center gap-3 flex-1 max-w-md">
        <AppInput
          v-model="search"
          placeholder="Пошук за назвою або slug..."
          class="flex-1"
        />
        <AppButton
          variant="secondary"
          class="!p-2.5 relative"
          :class="{
            'ring-2 ring-primary-500 !bg-primary-50 dark:!bg-primary-900/20 !border-primary-200 dark:!border-primary-800':
              showFilters,
          }"
          title="Фільтри"
          @click="showFilters = !showFilters"
        >
          <svg
            class="w-5 h-5 transition-colors"
            :class="showFilters ? 'text-[#00a046]' : 'text-gray-500'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
            />
          </svg>
          <span
            v-if="activeFiltersCount > 0"
            class="absolute -top-1 -right-1 w-5 h-5 bg-[#00a046] text-white text-[10px] flex items-center justify-center rounded-full font-black shadow-lg shadow-emerald-500/30 ring-2 ring-white dark:ring-gray-800"
          >
            {{ activeFiltersCount }}
          </span>
        </AppButton>
      </div>

      <!-- Add Page Button -->
      <AppButton
        variant="primary"
        class="flex items-center gap-2 shrink-0 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
        @click="$emit('add-page')"
      >
        <PlusIcon class="w-4 h-4" />
        Створити сторінку
      </AppButton>
    </div>

    <!-- Toggleable Filters Panel -->
    <transition name="expand">
      <div
        v-if="showFilters"
        class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xl space-y-6 animate-in slide-in-from-top-2 duration-300"
      >
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <AppSelect
            v-model="statusFilter"
            label="Статус"
            placeholder="Всі статуси"
            :options="[
              { id: '', name: 'Всі статуси' },
              { id: 'published', name: 'Опубліковані' },
              { id: 'draft', name: 'Чернетки' }
            ]"
            option-value="id"
            option-label="name"
          />
        </div>
        <div class="flex items-center justify-end pt-4 border-t border-gray-150 dark:border-gray-700">
          <AppButton
            variant="text"
            class="!text-red-500 hover:!text-red-600 hover:!bg-red-50 dark:hover:!bg-red-900/20 !px-4 !py-2 !rounded-xl font-bold"
            @click="resetFilters"
          >
            Скинути фільтри
          </AppButton>
        </div>
      </div>
    </transition>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
      <div
        v-if="loading"
        class="flex items-center justify-center py-20"
      >
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-b-4 border-[#00a046]" />
      </div>

      <div
        v-else-if="pages.length === 0"
        class="text-center py-20 text-gray-400 dark:text-gray-500"
      >
        <DocumentTextIcon class="w-16 h-16 mx-auto mb-4 opacity-30 text-[#00a046]" />
        <p class="text-base font-semibold text-gray-700 dark:text-gray-300">
          Сторінок не знайдено
        </p>
        <p class="text-xs mt-1 text-gray-400 dark:text-gray-500">
          Спробуйте змінити пошуковий запит або створіть нову сторінку
        </p>
      </div>

      <div
        v-else
        class="overflow-x-auto"
      >
        <table class="w-full text-sm">
          <thead class="bg-gray-50/70 dark:bg-gray-700/30 border-b border-gray-200 dark:border-gray-700">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Назва сторінки (UK / EN)
              </th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Адреса (Slug)
              </th>
              <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Статус
              </th>
              <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Оновлено
              </th>
              <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Дії
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr
              v-for="page in pages"
              :key="page.id"
              class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-colors"
            >
              <td class="px-6 py-4">
                <div class="font-semibold text-gray-800 dark:text-gray-100">
                  {{ page.titleUk || 'Без назви' }}
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                  {{ page.titleEn || 'No title' }}
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-1.5">
                  <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-650 dark:text-gray-300 rounded font-mono text-xs">
                    /pages/{{ page.slug }}
                  </span>
                  <button
                    class="p-1 text-gray-400 hover:text-[#00a046] transition-colors"
                    title="Копіювати посилання"
                    @click="copyLink(page.slug)"
                  >
                    <DocumentDuplicateIcon class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
              <td class="px-6 py-4 text-center">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
                    page.status === 'published'
                      ? 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
                      : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-650 dark:text-zinc-400'
                  ]"
                >
                  {{ page.status === 'published' ? 'Опубліковано' : 'Чернетка' }}
                </span>
              </td>
              <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-xs">
                {{ formatDate(page.updatedAt || page.createdAt) }}
              </td>
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <a
                    :href="`/pages/${page.slug}`"
                    target="_blank"
                    class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-[#00a046]/10 text-gray-500 hover:text-[#00a046] transition-colors"
                    title="Переглянути на сайті"
                  >
                    <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                  </a>
                  <button
                    class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-[#00a046]/10 text-gray-500 hover:text-[#00a046] transition-colors"
                    title="Редагувати"
                    @click="$emit('edit-page', page)"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700/50 hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition-colors"
                    title="Видалити"
                    @click="$emit('delete-page', page)"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div
        v-if="pagination.last_page > 1"
        class="px-6 py-4 border-t border-gray-150 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30"
      >
        <AppPagination
          :pagination="pagination"
          @page-change="fetchPages"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import AppPagination from "@/components/admin/ui/AppPagination.vue";
import {
  PlusIcon,
  TrashIcon,
  PencilIcon,
  DocumentTextIcon,
  ArrowTopRightOnSquareIcon,
  DocumentDuplicateIcon
} from "@heroicons/vue/24/outline";

defineEmits(["add-page", "edit-page", "delete-page"]);

const toast = useToast();

const pages = ref([]);
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const search = ref("");
const statusFilter = ref("");
const showFilters = ref(false);

const activeFiltersCount = computed(() => {
  let count = 0;
  if (statusFilter.value) count++;
  return count;
});

const resetFilters = () => {
  search.value = "";
  statusFilter.value = "";
};

const fetchPages = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/pages", {
      params: {
        page,
        search: search.value || undefined,
        status: statusFilter.value || undefined
      }
    });
    pages.value = data.data.data;
    pagination.value = data.data.meta;
  } catch (e) {
    toast.error("Помилка завантаження сторінок");
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const copyLink = (slug) => {
  const url = `${window.location.origin}/pages/${slug}`;
  navigator.clipboard.writeText(url)
    .then(() => toast.success("Посилання скопійовано в буфер обміну"))
    .catch(() => toast.error("Не вдалося скопіювати посилання"));
};

const formatDate = (d) => {
  if (!d) return "";
  return new Date(d).toLocaleDateString("uk-UA", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit"
  });
};

let debounce;
watch([search, statusFilter], () => {
  clearTimeout(debounce);
  debounce = setTimeout(() => fetchPages(1), 300);
});

onMounted(() => {
  fetchPages();
});

defineExpose({
  fetchPages,
  pagination
});
</script>
