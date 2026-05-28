<template>
  <TransitionRoot
    as="template"
    :show="show"
  >
    <Dialog
      as="div"
      class="relative z-50"
      @close="$emit('close')"
    >
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
        />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div
          class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
        >
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel
              class="relative transform border border-gray-200 dark:border-gray-700 overflow-hidden rounded-3xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
            >
              <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                  <div class="flex items-center gap-4">
                    <div
                      class="w-14 h-14 rounded-2xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-600"
                    >
                      <CommandLineIcon class="w-7 h-7" />
                    </div>
                    <div>
                      <DialogTitle
                        as="h3"
                        class="text-2xl font-black text-gray-900 dark:text-white tracking-tight"
                      >
                        {{ t("admin.support.snippets.manage") }}
                      </DialogTitle>
                    </div>
                  </div>
                  <AppButton
                    variant="ghost"
                    class="!p-2 hover:!bg-gray-100 dark:hover:!bg-gray-700 !rounded-xl !transition-colors"
                    @click="$emit('close')"
                  >
                    <XMarkIcon class="w-5 h-5 text-gray-400" />
                  </AppButton>
                </div>

                <div class="space-y-4">
                  <!-- Add New Snippet Form -->
                  <div
                    class="p-4 bg-primary-50 dark:bg-primary-900/10 rounded-xl border border-primary-100 dark:border-primary-900/30"
                  >
                    <h3
                      class="text-xs font-black uppercase tracking-widest text-primary-600 dark:text-primary-400 mb-3"
                    >
                      {{ t("admin.support.snippets.add_new") }}
                    </h3>
                    <div class="space-y-3">
                      <AppInput
                        v-model="newSnippet.title"
                        type="text"
                        :placeholder="
                          t('admin.support.snippets.title_placeholder')
                        "
                      />
                      <AppTextarea
                        v-model="newSnippet.content"
                        :placeholder="
                          t('admin.support.snippets.content_placeholder')
                        "
                        rows="3"
                      />
                      <AppButton
                        :disabled="!newSnippet.title || !newSnippet.content"
                        variant="success"
                        class="!w-full !px-4 !py-2 !rounded-lg !text-xs !font-black !uppercase !tracking-widest disabled:!opacity-30 disabled:!cursor-not-allowed !transition-all"
                        @click="addSnippet"
                      >
                        {{ t("admin.support.snippets.add") }}
                      </AppButton>
                    </div>
                  </div>

                  <!-- Snippets List -->
                  <div class="space-y-2 max-h-96 overflow-y-auto">
                    <h3
                      class="text-xs font-black uppercase tracking-widest text-gray-400 mb-2"
                    >
                      {{ t("admin.support.snippets.existing") }}
                    </h3>
                    <div
                      v-if="snippets.length === 0"
                      class="p-8 text-center text-sm text-gray-400"
                    >
                      {{ t("admin.support.snippets.no_snippets") }}
                    </div>
                    <div
                      v-for="snippet in snippets"
                      :key="snippet.id"
                      class="p-4 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl group hover:border-primary-200 dark:hover:border-primary-900/50 transition-all"
                    >
                      <div
                        v-if="editingId === snippet.id"
                        class="space-y-2"
                      >
                        <AppInput
                          v-model="editForm.title"
                          type="text"
                        />
                        <AppTextarea
                          v-model="editForm.content"
                          rows="3"
                        />
                        <div class="flex gap-2">
                          <AppButton
                            variant="success"
                            class="!flex-1 !px-3 !py-1.5 !rounded-lg !text-xs !font-black !uppercase !transition-all active:!scale-95"
                            @click="saveEdit(snippet.id)"
                          >
                            {{ t("admin.support.snippets.save") }}
                          </AppButton>
                          <AppButton
                            variant="secondary"
                            class="!flex-1 !px-3 !py-1.5 !rounded-lg !text-xs !font-black !uppercase"
                            @click="cancelEdit"
                          >
                            {{ t("admin.support.snippets.cancel") }}
                          </AppButton>
                        </div>
                      </div>
                      <div v-else>
                        <div class="flex items-start justify-between gap-3">
                          <div class="flex-1 min-w-0">
                            <h4
                              class="text-sm font-black text-gray-900 dark:text-white mb-1"
                            >
                              {{ snippet.title }}
                            </h4>
                            <p
                              class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed"
                            >
                              {{ snippet.content }}
                            </p>
                            <p
                              class="text-[9px] text-gray-400 mt-2 font-bold uppercase tracking-tight"
                            >
                              {{ t("admin.support.snippets.used") }}:
                              {{ snippet.usage_count || 0 }}
                            </p>
                          </div>
                          <div
                            class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity"
                          >
                            <AppButton
                              variant="ghost"
                              class="!p-2 hover:!bg-primary-50 dark:hover:!bg-primary-900/20 !rounded-lg !text-primary-600 !transition-all"
                              :title="t('admin.support.snippets.edit')"
                              @click="startEdit(snippet)"
                            >
                              <PencilIcon class="w-4 h-4" />
                            </AppButton>
                            <AppButton
                              variant="ghost"
                              class="!p-2 hover:!bg-red-50 dark:hover:!bg-red-900/20 !rounded-lg !text-red-500 !transition-all"
                              :title="t('admin.support.snippets.delete')"
                              @click="deleteSnippet(snippet.id)"
                            >
                              <TrashIcon class="w-4 h-4" />
                            </AppButton>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {ref} from "vue";
import {Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot,} from "@headlessui/vue";
import AppInput from "@/components/admin/ui/Form/AppInput.vue";
import AppTextarea from "@/components/admin/ui/Form/AppTextarea.vue";
import {CommandLineIcon, PencilIcon, TrashIcon, XMarkIcon,} from "@heroicons/vue/24/outline";
import {useI18n} from "vue-i18n";
import AppButton from "@/components/admin/ui/Button/AppButton.vue";

const { t } = useI18n();

const props = defineProps({
  show: {
    type: Boolean,
    required: true,
  },
  snippets: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["close", "add", "update", "delete"]);

const newSnippet = ref({
  title: "",
  content: "",
});

const editingId = ref(null);
const editForm = ref({
  title: "",
  content: "",
});

const addSnippet = () => {
  emit("add", { ...newSnippet.value });
  newSnippet.value = { title: "", content: "" };
};

const startEdit = (snippet) => {
  editingId.value = snippet.id;
  editForm.value = {
    title: snippet.title,
    content: snippet.content,
  };
};

const saveEdit = (id) => {
  emit("update", id, { ...editForm.value });
  cancelEdit();
};

const cancelEdit = () => {
  editingId.value = null;
  editForm.value = { title: "", content: "" };
};

const deleteSnippet = (id) => {
  if (confirm(t("admin.support.snippets.delete_confirm"))) {
    emit("delete", id);
  }
};
</script>
