<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Action Bar -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="flex flex-col sm:flex-row flex-1 gap-3 w-full">
        <!-- Search input -->
        <div class="relative flex-1 max-w-md">
          <input
            v-model="search"
            type="text"
            placeholder="Пошук за назвою або slug..."
            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition-all"
          >
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg
              class="w-4 h-4 text-gray-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
          </div>
        </div>
        <!-- Status filter -->
        <select
          v-model="statusFilter"
          class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer"
        >
          <option value="">
            Усі статуси
          </option>
          <option value="published">
            Опубліковано
          </option>
          <option value="draft">
            Чернетки
          </option>
        </select>
      </div>

      <button
        class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all shadow-lg shadow-emerald-500/20 w-full sm:w-auto justify-center shrink-0"
        @click="openNewPage"
      >
        <PlusIcon class="w-4 h-4" />
        Створити сторінку
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
      <div
        v-if="loading"
        class="flex items-center justify-center py-20"
      >
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-b-4 border-emerald-500" />
      </div>

      <div
        v-else-if="pages.length === 0"
        class="text-center py-20 text-gray-450 dark:text-gray-500"
      >
        <DocumentTextIcon class="w-16 h-16 mx-auto mb-4 opacity-30 text-emerald-500" />
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
          <thead class="bg-gray-50/70 dark:bg-gray-700/30 border-b border-gray-250 dark:border-gray-750">
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
                    class="p-1 text-gray-400 hover:text-emerald-600 transition-colors"
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
                    class="p-2 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-gray-500 hover:text-emerald-600 transition-colors"
                    title="Переглянути на сайті"
                  >
                    <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                  </a>
                  <button
                    class="p-2 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-gray-500 hover:text-emerald-600 transition-colors"
                    title="Редагувати"
                    @click="openEditPage(page)"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </button>
                  <button
                    class="p-2 rounded-xl bg-gray-50 dark:bg-gray-700/50 hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition-colors"
                    title="Видалити"
                    @click="deletePage(page)"
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
        class="flex items-center justify-between px-6 py-4 border-t border-gray-150 dark:border-gray-700"
      >
        <span class="text-xs text-gray-550 dark:text-gray-400">
          Всього: <strong>{{ pagination.total }}</strong> сторінок
        </span>
        <div class="flex gap-1.5">
          <button
            v-for="page in pagination.last_page"
            :key="page"
            :class="[
              'w-8 h-8 rounded-lg text-xs font-semibold transition-all',
              pagination.current_page === page
                ? 'bg-emerald-600 text-white shadow-md shadow-emerald-500/20'
                : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:border-emerald-500'
            ]"
            @click="fetchPages(page)"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL EDITOR -->
    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="showModal"
          class="fixed inset-0 z-[9999] flex"
        >
          <!-- Backdrop -->
          <div
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            @click="closeModal"
          />

          <!-- Editor panel -->
          <div class="relative ml-auto w-full max-w-5xl h-full bg-gray-50 dark:bg-gray-900 flex flex-col shadow-2xl">
            <!-- Header -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between shrink-0">
              <div class="flex items-center gap-3">
                <button
                  class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                  @click="closeModal"
                >
                  <XMarkIcon class="w-5 h-5 text-gray-500" />
                </button>
                <div>
                  <h2 class="font-bold text-gray-800 dark:text-white text-lg">
                    {{ editingPage ? 'Редагувати сторінку' : 'Нова сторінка' }}
                  </h2>
                  <p class="text-xs text-gray-400 dark:text-gray-500">
                    Заповніть контент українською та англійською мовами
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <select
                  v-model="pageForm.status"
                  class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-750 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer"
                >
                  <option value="draft">
                    Чернетка
                  </option>
                  <option value="published">
                    Опублікувати
                  </option>
                </select>
                <button
                  :disabled="saving"
                  class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-emerald-500/10"
                  @click="savePage"
                >
                  <span v-if="saving">Збереження...</span>
                  <span v-else>Зберегти</span>
                </button>
              </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
              <!-- Grid for settings (Slug) -->
              <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-4">
                <h3 class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-450">
                  Налаштування URL
                </h3>
                <div>
                  <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Адреса сторінки (Slug)</label>
                  <div class="relative rounded-xl shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                      <span class="text-gray-400 text-sm font-mono">/pages/</span>
                    </div>
                    <input
                      v-model="pageForm.slug"
                      type="text"
                      placeholder="наприклад: shipping-and-payment"
                      class="block w-full rounded-xl border border-gray-250 dark:border-gray-700 bg-white dark:bg-gray-900 py-3 pl-16 pr-3 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none text-gray-800 dark:text-gray-100 font-mono"
                    >
                  </div>
                  <p class="text-xs text-gray-400 mt-1.5 leading-relaxed">
                    Лише латинські літери, цифри та дефіси. Якщо залишити порожнім, буде згенеровано автоматично з англійського заголовка.
                  </p>
                </div>
              </div>

              <!-- Tabs and Content Fields -->
              <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-5">
                <!-- Language tabs switcher -->
                <div class="flex items-center justify-between border-b border-gray-155 dark:border-gray-700 pb-3">
                  <h3 class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-450">
                    Вміст сторінки
                  </h3>
                  <div class="flex gap-1 bg-gray-100 dark:bg-gray-700 rounded-xl p-1 shrink-0">
                    <button
                      type="button"
                      :class="[
                        'px-4 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5',
                        langTab === 'uk'
                          ? 'bg-white dark:bg-gray-600 text-gray-850 dark:text-white shadow-sm'
                          : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                      ]"
                      @click="langTab = 'uk'"
                    >
                      <span>🇺🇦</span> Українська
                    </button>
                    <button
                      type="button"
                      :class="[
                        'px-4 py-1.5 rounded-lg text-xs font-semibold transition-all flex items-center gap-1.5',
                        langTab === 'en'
                          ? 'bg-white dark:bg-gray-600 text-gray-855 dark:text-white shadow-sm'
                          : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                      ]"
                      @click="langTab = 'en'"
                    >
                      <span>🇬🇧</span> English
                    </button>
                  </div>
                </div>

                <!-- UK Tab Content -->
                <div
                  v-show="langTab === 'uk'"
                  class="space-y-5"
                >
                  <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Заголовок сторінки (Українська)</label>
                    <input
                      v-model="pageForm.titleUk"
                      type="text"
                      placeholder="Введіть заголовок..."
                      class="w-full px-4 py-3 rounded-xl border border-gray-250 dark:border-gray-700 bg-white dark:bg-gray-900 text-base font-bold text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Контент сторінки (Українська)</label>
                    <RichEditor
                      v-model="pageForm.contentUk"
                      @upload-image="handleImageUpload"
                    />
                  </div>
                </div>

                <!-- EN Tab Content -->
                <div
                  v-show="langTab === 'en'"
                  class="space-y-5"
                >
                  <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Page Title (English)</label>
                    <input
                      v-model="pageForm.titleEn"
                      type="text"
                      placeholder="Enter page title..."
                      class="w-full px-4 py-3 rounded-xl border border-gray-255 dark:border-gray-700 bg-white dark:bg-gray-900 text-base font-bold text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    >
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Page Content (English)</label>
                    <RichEditor
                      v-model="pageForm.contentEn"
                      @upload-image="handleImageUpload"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import api from "@/shared/services/api/apiClient";
import RichEditor from '@/components/admin/features/blog/RichEditor.vue';
import {
  PlusIcon,
  TrashIcon,
  PencilIcon,
  XMarkIcon,
  DocumentTextIcon,
  ArrowTopRightOnSquareIcon,
  DocumentDuplicateIcon,
} from '@heroicons/vue/24/outline';

const toast = useToast();

// ─── Data ───────────────────────────────────────────────────────────────────
const pages = ref([]);
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

// Filters
const search = ref('');
const statusFilter = ref('');

// ─── Modal state ─────────────────────────────────────────────────────────────
const showModal = ref(false);
const editingPage = ref(null);
const saving = ref(false);
const langTab = ref('uk');

const defaultPageForm = () => ({
  titleUk: '',
  titleEn: '',
  contentUk: '',
  contentEn: '',
  slug: '',
  status: 'published',
});

const pageForm = ref(defaultPageForm());

// ─── Methods ─────────────────────────────────────────────────────────────────
const fetchPages = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/pages', {
      params: {
        page,
        search: search.value || undefined,
        status: statusFilter.value || undefined,
      },
    });
    pages.value = data.data.data;
    pagination.value = data.data.meta;
  } catch (e) {
    toast.error('Помилка завантаження сторінок');
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const openNewPage = () => {
  editingPage.value = null;
  langTab.value = 'uk';
  pageForm.value = defaultPageForm();
  showModal.value = true;
};

const openEditPage = async (page) => {
  langTab.value = 'uk';
  try {
    const { data } = await api.get(`/admin/pages/${page.id}`);
    const p = data.data;
    editingPage.value = p;
    pageForm.value = {
      titleUk: p.titleUk,
      titleEn: p.titleEn,
      contentUk: p.contentUk,
      contentEn: p.contentEn,
      slug: p.slug,
      status: p.status,
    };
    showModal.value = true;
  } catch (e) {
    toast.error('Помилка завантаження сторінки');
    console.error(e);
  }
};

const closeModal = () => {
  showModal.value = false;
  editingPage.value = null;
};

const savePage = async () => {
  if (!pageForm.value.titleUk || !pageForm.value.titleEn) {
    toast.warning('Заголовок є обов\'язковим для обох мов');
    return;
  }
  if (!pageForm.value.contentUk || !pageForm.value.contentEn) {
    toast.warning('Вміст сторінки є обов\'язковим для обох мов');
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
      status: pageForm.value.status,
    };

    if (editingPage.value) {
      await api.put(`/admin/pages/${editingPage.value.id}`, payload);
      toast.success('Сторінку оновлено успішно');
    } else {
      await api.post('/admin/pages', payload);
      toast.success('Сторінку створено успішно');
    }
    closeModal();
    fetchPages(pagination.value.current_page);
  } catch (e) {
    toast.error(e.response?.data?.message || 'Помилка збереження сторінки');
    console.error(e);
  } finally {
    saving.value = false;
  }
};

const deletePage = async (page) => {
  if (!confirm(`Ви дійсно бажаєте видалити сторінку "${page.titleUk || page.titleEn}"?`)) return;
  try {
    await api.delete(`/admin/pages/${page.id}`);
    toast.success('Сторінку видалено успішно');
    fetchPages(pagination.value.current_page);
  } catch (e) {
    toast.error('Помилка видалення сторінки');
    console.error(e);
  }
};

const copyLink = (slug) => {
  const url = `${window.location.origin}/pages/${slug}`;
  navigator.clipboard.writeText(url)
    .then(() => toast.success('Посилання скопійовано в буфер обміну'))
    .catch(() => toast.error('Не вдалося скопіювати посилання'));
};

const handleImageUpload = async (file, callback) => {
  const form = new FormData();
  form.append('image', file);
  try {
    const { data } = await api.post('/admin/blog/upload', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    callback(data.data.url);
  } catch (e) {
    toast.error('Помилка завантаження зображення');
    console.error(e);
  }
};

const formatDate = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uk-UA', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

// ─── Watchers ─────────────────────────────────────────────────────────────────
let debounce;
watch([search, statusFilter], () => {
  clearTimeout(debounce);
  debounce = setTimeout(() => fetchPages(1), 300);
});

// ─── Init ────────────────────────────────────────────────────────────────────
onMounted(() => {
  fetchPages();
});
</script>

<style scoped>
.modal-enter-active {
  animation: slide-in 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.modal-leave-active {
  animation: slide-in 0.2s cubic-bezier(0.4, 0, 0.2, 1) reverse;
}
@keyframes slide-in {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
