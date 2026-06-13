<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col md:flex-row gap-4 justify-between items-stretch md:items-center">
      <!-- Search bar & filters toggle button -->
      <div class="flex items-center gap-3 flex-1 max-w-md">
        <AppInput
          v-model="search"
          placeholder="Пошук постів..."
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

      <!-- Add Post Button -->
      <AppButton
        variant="primary"
        class="flex items-center gap-2 shrink-0 h-[38px] !py-0 !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046] transition-all duration-200 active:scale-[0.98]"
        @click="$emit('add-post')"
      >
        <PlusIcon class="w-4 h-4" />
        Новий пост
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
              { id: 'draft', name: 'Чернетки' },
              { id: 'archived', name: 'Архів' }
            ]"
            option-value="id"
            option-label="name"
          />
          <AppSelect
            v-model="categoryFilter"
            label="Категорія"
            placeholder="Всі категорії"
            :options="[{ id: '', nameUk: 'Всі категорії', nameEn: 'All categories' }, ...categories]"
            option-value="id"
            option-label="nameUk"
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

    <!-- Posts grid -->
    <div
      v-if="loading"
      class="flex items-center justify-center py-20"
    >
      <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-b-4 border-[#00a046]" />
    </div>

    <div
      v-else-if="posts.length === 0"
      class="text-center py-20 text-gray-400 bg-white dark:bg-gray-800 rounded-2xl border border-gray-150 dark:border-gray-700 shadow-sm"
    >
      <DocumentTextIcon class="w-12 h-12 mx-auto mb-3 opacity-40" />
      <p class="text-sm font-medium">
        Постів не знайдено
      </p>
    </div>

    <div
      v-else
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5"
    >
      <div
        v-for="post in posts"
        :key="post.id"
        class="group bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:shadow-black/5 transition-all duration-300"
      >
        <!-- Cover -->
        <div class="relative h-44 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 overflow-hidden">
          <img
            v-if="post.coverImage"
            :src="post.coverImage"
            :alt="post.titleUk"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
          >
          <div
            v-else
            class="absolute inset-0 flex items-center justify-center"
          >
            <DocumentTextIcon class="w-12 h-12 text-emerald-300" />
          </div>
          <!-- Status badge -->
          <span
            :class="statusBadgeClass(post.status)"
            class="absolute top-3 left-3 text-xs font-bold px-2.5 py-1 rounded-lg"
          >
            {{ statusLabel(post.status) }}
          </span>
        </div>

        <!-- Content -->
        <div class="p-4 space-y-3">
          <div class="flex items-start justify-between gap-2">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100 text-sm leading-snug line-clamp-2">
              {{ post.titleUk || post.titleEn }}
            </h3>
          </div>

          <p
            v-if="post.excerptUk || post.excerptEn"
            class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2"
          >
            {{ post.excerptUk || post.excerptEn }}
          </p>

          <div class="flex items-center gap-3 text-xs text-gray-400">
            <span
              v-if="post.categoryName"
              class="flex items-center gap-1"
            >
              <TagIcon class="w-3 h-3" />
              {{ post.categoryName }}
            </span>
            <span class="flex items-center gap-1">
              <EyeIcon class="w-3 h-3" />
              {{ post.views }}
            </span>
            <span>{{ formatDate(post.publishedAt || post.createdAt) }}</span>
          </div>

          <div class="flex items-center gap-2 pt-1">
            <AppButton
              variant="secondary"
              size="sm"
              class="flex-1 font-semibold text-xs bg-gray-50 dark:bg-gray-700/50 hover:bg-[#00a046]/10 text-gray-600 dark:text-gray-400 hover:text-[#00a046] dark:hover:text-[#00b050] transition-colors"
              @click="$emit('edit-post', post)"
            >
              Редагувати
            </AppButton>
            <AppButton
              variant="ghost"
              size="sm"
              class="!px-3 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
              @click="$emit('delete-post', post)"
            >
              <TrashIcon class="w-3.5 h-3.5" />
            </AppButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="pagination.last_page > 1"
      class="px-6 py-4 border-t border-gray-150 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30"
    >
      <AppPagination
        :pagination="pagination"
        @page-change="fetchPosts"
      />
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
  DocumentTextIcon,
  TagIcon,
  EyeIcon,
  TrashIcon,
  PlusIcon
} from "@heroicons/vue/24/outline";

defineProps({
  categories: {
    type: Array,
    default: () => []
  }
});

defineEmits(["add-post", "edit-post", "delete-post"]);

const toast = useToast();
const posts = ref([]);
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const search = ref("");
const statusFilter = ref("");
const categoryFilter = ref("");

const showFilters = ref(false);
const activeFiltersCount = computed(() => {
  let count = 0;
  if (statusFilter.value) count++;
  if (categoryFilter.value) count++;
  return count;
});

const resetFilters = () => {
  search.value = "";
  statusFilter.value = "";
  categoryFilter.value = "";
};

const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/blog/posts", {
      params: {
        page,
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        category_id: categoryFilter.value || undefined
      }
    });
    posts.value = data.data.data;
    pagination.value = data.data.meta;
  } catch (e) {
    toast.error("Помилка завантаження постів");
  } finally {
    loading.value = false;
  }
};

const statusLabel = (s) => ({ published: "Опубліковано", draft: "Чернетка", archived: "Архів" }[s] || s);
const statusBadgeClass = (s) => ({
  published: "bg-emerald-500/90 text-white",
  draft: "bg-amber-400/90 text-white",
  archived: "bg-gray-400/80 text-white"
}[s] || "bg-gray-400 text-white");

const formatDate = (d) => {
  if (!d) return "";
  return new Date(d).toLocaleDateString("uk-UA", { day: "2-digit", month: "short", year: "numeric" });
};

let debounce;
watch([search, statusFilter, categoryFilter], () => {
  clearTimeout(debounce);
  debounce = setTimeout(() => fetchPosts(1), 300);
});

onMounted(() => {
  fetchPosts();
});

defineExpose({
  fetchPosts
});
</script>
