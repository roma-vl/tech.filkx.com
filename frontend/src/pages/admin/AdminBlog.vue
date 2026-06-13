<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header actions -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
      <div class="flex gap-2">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          type="button"
          :class="[
            'px-4 py-2 rounded-lg text-sm font-semibold transition-all cursor-pointer',
            activeTab === tab.key
              ? 'bg-[#00a046] text-white shadow-lg shadow-emerald-500/20'
              : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700'
          ]"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- Active tabs sections -->
    <div v-show="activeTab === 'posts'">
      <PostsTab
        ref="postsTabRef"
        :categories="categories"
        @add-post="openNewPost"
        @edit-post="openEditPost"
        @delete-post="deletePost"
      />
    </div>

    <div v-if="activeTab === 'categories'">
      <BlogCategoriesTab
        :categories="categories"
        @add-category="openCategoryModal(null)"
        @edit-category="openCategoryModal"
        @delete-category="deleteCategory"
      />
    </div>

    <div v-if="activeTab === 'tags'">
      <BlogTagsTab
        :tags="tags"
        @add-tag="openTagModal(null)"
        @edit-tag="openTagModal"
        @delete-tag="deleteTag"
      />
    </div>

    <!-- MODALS SECTION -->

    <!-- Post Form Editor Modal -->
    <PostFormModal
      v-model="showPostModal"
      :post="editingPost"
      :categories="categories"
      :tags="tags"
      @refresh="fetchPostsList"
      @tag-created="onTagCreated"
    />

    <!-- Category Modal -->
    <BlogCategoryModal
      v-model="showCategoryModal"
      :category="editingCategory"
      @refresh="fetchCategoriesList"
    />

    <!-- Tag Modal -->
    <BlogTagModal
      v-model="showTagModal"
      :tag="editingTag"
      @refresh="fetchTagsList"
    />

    <!-- Global Delete Confirmation Modal -->
    <AppConfirmModal
      v-model="showDeleteConfirmModal"
      :title="deleteConfirmTitle"
      :message="deleteConfirmMessage"
      confirm-text="Видалити"
      cancel-text="Скасувати"
      :loading="deletingItem"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";
import AppConfirmModal from "@/components/admin/ui/AppConfirmModal.vue";

// Modular tab components
import PostsTab from "@/components/admin/features/blog/PostsTab.vue";
import BlogCategoriesTab from "@/components/admin/features/blog/BlogCategoriesTab.vue";
import BlogTagsTab from "@/components/admin/features/blog/BlogTagsTab.vue";

// Modular form modals
import PostFormModal from "@/components/admin/features/blog/PostFormModal.vue";
import BlogCategoryModal from "@/components/admin/features/blog/BlogCategoryModal.vue";
import BlogTagModal from "@/components/admin/features/blog/BlogTagModal.vue";

const toast = useToast();
const activeTab = ref("posts");

const tabs = [
  { key: "posts", label: "Пости" },
  { key: "categories", label: "Категорії" },
  { key: "tags", label: "Теги" }
];

// Shared states
const categories = ref([]);
const tags = ref([]);

// Modals display flags & active form targets
const showPostModal = ref(false);
const editingPost = ref(null);

const showCategoryModal = ref(false);
const editingCategory = ref(null);

const showTagModal = ref(false);
const editingTag = ref(null);

// Tab reference for calling internal methods
const postsTabRef = ref(null);

const fetchPostsList = () => {
  if (postsTabRef.value) {
    postsTabRef.value.fetchPosts(1);
  }
};

const fetchCategoriesList = async () => {
  try {
    const { data } = await api.get("/admin/blog/categories");
    categories.value = data.data;
  } catch (e) {
    toast.error("Помилка завантаження категорій");
  }
};

const fetchTagsList = async () => {
  try {
    const { data } = await api.get("/admin/blog/tags");
    tags.value = data.data;
  } catch (e) {
    toast.error("Помилка завантаження тегів");
  }
};

const onTagCreated = (newTag) => {
  tags.value.unshift(newTag);
};

// ─── Post Editor modal triggers ───
const openNewPost = () => {
  editingPost.value = null;
  showPostModal.value = true;
};

const openEditPost = async (post) => {
  try {
    const { data } = await api.get(`/admin/blog/posts/${post.id}`);
    editingPost.value = data.data;
    showPostModal.value = true;
  } catch (e) {
    toast.error("Помилка завантаження детальної інформації про пост");
  }
};

// ─── Categories modal triggers ───
const openCategoryModal = (cat) => {
  editingCategory.value = cat;
  showCategoryModal.value = true;
};

// ─── Tags modal triggers ───
const openTagModal = (tag) => {
  editingTag.value = tag;
  showTagModal.value = true;
};

// ─── Unified Deletion flow ───
const showDeleteConfirmModal = ref(false);
const deleteType = ref(""); // 'post' | 'category' | 'tag'
const itemToDelete = ref(null);
const deletingItem = ref(false);

const deletePost = (post) => {
  itemToDelete.value = post;
  deleteType.value = "post";
  showDeleteConfirmModal.value = true;
};

const deleteCategory = (cat) => {
  itemToDelete.value = cat;
  deleteType.value = "category";
  showDeleteConfirmModal.value = true;
};

const deleteTag = (tag) => {
  itemToDelete.value = tag;
  deleteType.value = "tag";
  showDeleteConfirmModal.value = true;
};

const confirmDelete = async () => {
  if (!itemToDelete.value) return;
  deletingItem.value = true;
  try {
    if (deleteType.value === "post") {
      await api.delete(`/admin/blog/posts/${itemToDelete.value.id}`);
      toast.success("Пост видалено");
      fetchPostsList();
    } else if (deleteType.value === "category") {
      await api.delete(`/admin/blog/categories/${itemToDelete.value.id}`);
      toast.success("Категорію видалено");
      fetchCategoriesList();
    } else if (deleteType.value === "tag") {
      await api.delete(`/admin/blog/tags/${itemToDelete.value.id}`);
      toast.success("Тег видалено");
      fetchTagsList();
    }
    showDeleteConfirmModal.value = false;
  } catch (e) {
    toast.error("Помилка видалення");
  } finally {
    deletingItem.value = false;
  }
};

const deleteConfirmMessage = computed(() => {
  if (!itemToDelete.value) return "";
  if (deleteType.value === "post") {
    return `Ви впевнені, що хочете видалити пост "${itemToDelete.value.titleUk || itemToDelete.value.titleEn}"?`;
  }
  if (deleteType.value === "category") {
    return `Ви впевнені, що хочете видалити категорію "${itemToDelete.value.nameUk}"?`;
  }
  if (deleteType.value === "tag") {
    return `Ви впевнені, що хочете видалити тег "${itemToDelete.value.nameUk || itemToDelete.value.nameEn}"?`;
  }
  return "";
});

const deleteConfirmTitle = computed(() => {
  if (deleteType.value === "post") return "Видалення поста";
  if (deleteType.value === "category") return "Видалення категорії";
  if (deleteType.value === "tag") return "Видалення тегу";
  return "Видалення";
});

onMounted(async () => {
  await Promise.all([fetchCategoriesList(), fetchTagsList()]);
});
</script>

<style scoped></style>
