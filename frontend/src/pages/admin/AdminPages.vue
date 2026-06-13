<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <PagesTab
      ref="pagesTabRef"
      @add-page="openNewPage"
      @edit-page="openEditPage"
      @delete-page="deletePage"
    />

    <!-- MODAL EDITOR -->
    <PageFormModal
      v-model="showModal"
      :page="editingPage"
      @refresh="onPageSaved"
    />

    <!-- Global Delete Confirmation Modal -->
    <AppConfirmModal
      v-model="showDeleteConfirmModal"
      title="Видалення сторінки"
      :message="deleteConfirmMessage"
      confirm-text="Видалити"
      cancel-text="Скасувати"
      :loading="deletingPage"
      @confirm="confirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { useToast } from "vue-toastification";
import api from "@/shared/services/api/apiClient";
import AppConfirmModal from "@/components/admin/ui/AppConfirmModal.vue";
import PagesTab from "@/components/admin/features/pages/PagesTab.vue";
import PageFormModal from "@/components/admin/features/pages/PageFormModal.vue";

const toast = useToast();

const showModal = ref(false);
const editingPage = ref(null);

const pagesTabRef = ref(null);

// ─── Unified Deletion flow ───
const showDeleteConfirmModal = ref(false);
const pageToDelete = ref(null);
const deletingPage = ref(false);

const deleteConfirmMessage = computed(() => {
  if (!pageToDelete.value) return "";
  return `Ви впевнені, що хочете видалити сторінку "${pageToDelete.value.titleUk || pageToDelete.value.titleEn}"?`;
});

const openNewPage = () => {
  editingPage.value = null;
  showModal.value = true;
};

const openEditPage = async (page) => {
  try {
    const { data } = await api.get(`/admin/pages/${page.id}`);
    editingPage.value = data.data;
    showModal.value = true;
  } catch (e) {
    toast.error("Помилка завантаження сторінки");
    console.error(e);
  }
};

const deletePage = (page) => {
  pageToDelete.value = page;
  showDeleteConfirmModal.value = true;
};

const confirmDelete = async () => {
  if (!pageToDelete.value) return;
  deletingPage.value = true;
  try {
    await api.delete(`/admin/pages/${pageToDelete.value.id}`);
    toast.success("Сторінку видалено успішно");
    showDeleteConfirmModal.value = false;
    refreshPagesList();
  } catch (e) {
    toast.error("Помилка видалення сторінки");
    console.error(e);
  } finally {
    deletingPage.value = false;
  }
};

const refreshPagesList = () => {
  if (pagesTabRef.value) {
    pagesTabRef.value.fetchPages(pagesTabRef.value.pagination.current_page || 1);
  }
};

const onPageSaved = () => {
  refreshPagesList();
};
</script>

<style scoped></style>
