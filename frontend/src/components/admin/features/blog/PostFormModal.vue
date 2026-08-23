<template>
  <AppModal
    :model-value="modelValue"
    :title="
      post
        ? t('admin.blog.posts.form.editTitle')
        : t('admin.blog.posts.form.newTitle')
    "
    max-width="3xl"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div class="space-y-6">
      <!-- Top Action Controls inside Modal Header / Body boundary -->
      <div
        class="flex items-center justify-between pb-4 border-b border-gray-150 dark:border-gray-700"
      >
        <div>
          <p class="text-xs text-gray-400">
            {{ t("admin.blog.posts.form.statusDateHint") }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <AppSelect
            v-model="postForm.status"
            :options="[
              { id: 'draft', name: t('admin.blog.posts.form.status.draft') },
              {
                id: 'published',
                name: t('admin.blog.posts.form.status.published'),
              },
              {
                id: 'archived',
                name: t('admin.blog.posts.form.status.archived'),
              },
            ]"
            option-value="id"
            option-label="name"
            class="w-40"
          />
          <AppButton
            variant="primary"
            :loading="saving"
            class="!bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-sm hover:shadow-lg focus:ring-[#00a046]"
            @click="savePost"
          >
            {{ t("admin.blog.posts.form.save") }}
          </AppButton>
        </div>
      </div>

      <!-- Editor body split layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main content area (Left columns) -->
        <div class="lg:col-span-2 space-y-5">
          <!-- Tabs for UK/EN -->
          <div
            class="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-fit"
          >
            <AppButton
              variant="text"
              class="px-4 py-1.5 rounded-lg text-sm font-semibold !no-underline"
              :class="
                langTab === 'uk'
                  ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-white shadow-sm'
                  : 'text-gray-500'
              "
              @click="langTab = 'uk'"
            >
              {{ t("admin.blog.posts.form.langTabUk") }}
            </AppButton>
            <AppButton
              variant="text"
              class="px-4 py-1.5 rounded-lg text-sm font-semibold !no-underline"
              :class="
                langTab === 'en'
                  ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-white shadow-sm'
                  : 'text-gray-500'
              "
              @click="langTab = 'en'"
            >
              {{ t("admin.blog.posts.form.langTabEn") }}
            </AppButton>
          </div>

          <!-- Title -->
          <div>
            <AppInput
              v-if="langTab === 'uk'"
              v-model="postForm.titleUk"
              :label="t('admin.blog.posts.form.titleLabelUk')"
              :placeholder="t('admin.blog.posts.form.titlePlaceholderUk')"
              class="font-bold text-base"
            />
            <AppInput
              v-else
              v-model="postForm.titleEn"
              :label="t('admin.blog.posts.form.titleLabelEn')"
              :placeholder="t('admin.blog.posts.form.titlePlaceholderEn')"
              class="font-bold text-base"
            />
          </div>

          <!-- Excerpt -->
          <div>
            <AppTextarea
              v-if="langTab === 'uk'"
              v-model="postForm.excerptUk"
              :label="t('admin.blog.posts.form.excerptLabelUk')"
              :placeholder="t('admin.blog.posts.form.excerptPlaceholderUk')"
              rows="2"
            />
            <AppTextarea
              v-else
              v-model="postForm.excerptEn"
              :label="t('admin.blog.posts.form.excerptLabelEn')"
              :placeholder="t('admin.blog.posts.form.excerptPlaceholderEn')"
              rows="2"
            />
          </div>

          <!-- Rich text editor -->
          <div class="rich-editor-container">
            <label
              class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
            >
              {{
                langTab === "uk"
                  ? t("admin.blog.posts.form.contentLabelUk")
                  : t("admin.blog.posts.form.contentLabelEn")
              }}
            </label>
            <RichEditor
              v-if="langTab === 'uk'"
              :key="`uk-${post?.id || 'new'}`"
              v-model="postForm.contentUk"
              @upload-image="handleImageUpload"
            />
            <RichEditor
              v-else
              :key="`en-${post?.id || 'new'}`"
              v-model="postForm.contentEn"
              @upload-image="handleImageUpload"
            />
          </div>
        </div>

        <!-- Sidebar options (Right column) -->
        <div
          class="space-y-5 bg-gray-50/50 dark:bg-gray-900/30 p-4 rounded-xl border border-gray-200/50 dark:border-gray-700/50"
        >
          <!-- Cover image -->
          <div>
            <label
              class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2"
              >{{ t("admin.blog.posts.form.coverLabel") }}</label
            >
            <div class="relative">
              <div
                v-if="postForm.coverImage"
                class="relative rounded-xl overflow-hidden aspect-video mb-2 border border-gray-200 dark:border-gray-700"
              >
                <img
                  :src="postForm.coverImage"
                  class="w-full h-full object-cover"
                />
                <AppButton
                  variant="ghost"
                  size="sm"
                  class="absolute top-2 right-2 !p-1.5 bg-black/50 hover:bg-black/70 rounded-lg !text-white border-none shadow-none"
                  @click="postForm.coverImage = ''"
                >
                  <XMarkIcon class="w-4 h-4" />
                </AppButton>
              </div>
              <label
                class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-250 dark:border-gray-700 rounded-xl cursor-pointer hover:border-[#00a046] hover:bg-emerald-50/10 dark:hover:bg-emerald-950/10 transition-colors"
              >
                <PhotoIcon
                  class="w-6 h-6 text-gray-300 dark:text-gray-600 mb-1"
                />
                <span class="text-xs text-gray-400">{{
                  t("admin.blog.posts.form.coverUpload")
                }}</span>
                <input
                  type="file"
                  accept="image/*"
                  class="sr-only"
                  @change="uploadCover"
                />
              </label>
            </div>
          </div>

          <!-- Category -->
          <AppSelect
            v-model="postForm.categoryId"
            :label="t('admin.blog.posts.form.categoryLabel')"
            :placeholder="t('admin.blog.posts.form.categoryPlaceholder')"
            :options="[
              {
                id: null,
                nameUk: t('admin.blog.posts.form.categoryPlaceholder'),
              },
              ...categories,
            ]"
            option-value="id"
            option-label="nameUk"
          />

          <!-- Tags -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label
                class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                >{{ t("admin.blog.posts.form.tagsLabel") }}</label
              >
              <AppButton
                variant="text"
                class="flex items-center gap-1 !text-xs !text-[#00a046] dark:!text-[#00b050] !no-underline"
                @click="openInlineTagCreate"
              >
                <PlusIcon class="w-3 h-3" />
                {{ t("admin.blog.posts.form.newTagLabel") }}
              </AppButton>
            </div>

            <!-- Quick create tag inline -->
            <div
              v-if="showInlineTagForm"
              class="mb-3 flex gap-2 animate-in slide-in-from-top-1 duration-200"
            >
              <AppInput
                v-model="inlineTagNameUk"
                :placeholder="t('admin.blog.posts.form.tagNameUkPlaceholder')"
                class="flex-1 !py-1 !text-xs"
                @keyup.enter="saveInlineTag"
              />
              <AppInput
                v-model="inlineTagNameEn"
                :placeholder="t('admin.blog.posts.form.tagNameEnPlaceholder')"
                class="flex-1 !py-1 !text-xs"
                @keyup.enter="saveInlineTag"
              />
              <div class="flex gap-1">
                <AppButton
                  variant="primary"
                  size="sm"
                  :loading="savingInlineTag"
                  class="!px-2.5 !py-1 !text-xs !bg-[#00a046] hover:!bg-[#00b050] text-white border-none shadow-none"
                  @click="saveInlineTag"
                >
                  ✓
                </AppButton>
                <AppButton
                  variant="ghost"
                  size="sm"
                  class="!px-2.5 !py-1 !text-xs text-gray-400 hover:text-gray-600 shadow-none"
                  @click="showInlineTagForm = false"
                >
                  ✕
                </AppButton>
              </div>
            </div>

            <!-- Tags List Pills -->
            <div
              class="flex flex-wrap gap-1.5 max-h-40 overflow-y-auto p-1 border border-gray-100 dark:border-gray-800 rounded-lg"
            >
              <button
                v-for="tag in tags"
                :key="tag.id"
                type="button"
                :class="[
                  'px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer',
                  postForm.tagIds.includes(tag.id)
                    ? 'bg-[#00a046] text-white'
                    : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-250 dark:hover:bg-gray-650',
                ]"
                @click="toggleTag(tag.id)"
              >
                {{ tag.nameUk || tag.nameEn }}
              </button>
              <span
                v-if="tags.length === 0"
                class="text-xs text-gray-400 italic p-1"
                >{{ t("admin.blog.posts.form.noTagsYet") }}</span
              >
            </div>
          </div>

          <!-- Published at -->
          <div>
            <AppInput
              v-model="postForm.publishedAt"
              type="datetime-local"
              :label="t('admin.blog.posts.form.publishedAtLabel')"
            />
          </div>
        </div>
      </div>
    </div>
  </AppModal>
</template>

<script setup>
import { ref, watch } from "vue";
import { useToast } from "vue-toastification";
import { useI18n } from "vue-i18n";
import api from "@/shared/services/api/apiClient";
import AppModal from "@/components/admin/ui/AppModal.vue";
import AppInput from "@/components/admin/ui/AppInput.vue";
import AppSelect from "@/components/admin/ui/AppSelect.vue";
import AppTextarea from "@/components/admin/ui/AppTextarea.vue";
import AppButton from "@/components/admin/ui/AppButton.vue";
import RichEditor from "@/components/admin/features/blog/RichEditor.vue";
import { XMarkIcon, PhotoIcon, PlusIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  modelValue: Boolean,
  post: Object,
  categories: {
    type: Array,
    default: () => [],
  },
  tags: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["update:modelValue", "refresh", "tag-created"]);

const toast = useToast();
const { t } = useI18n();
const langTab = ref("uk");
const saving = ref(false);

const defaultPostForm = () => ({
  titleUk: "",
  titleEn: "",
  excerptUk: "",
  excerptEn: "",
  contentUk: "",
  contentEn: "",
  coverImage: "",
  status: "draft",
  categoryId: null,
  tagIds: [],
  publishedAt: "",
});

const postForm = ref(defaultPostForm());

// Watch for post editing initialization
watch(
  () => props.post,
  (newPost) => {
    langTab.value = "uk";
    if (newPost) {
      postForm.value = {
        titleUk: newPost.titleUk || "",
        titleEn: newPost.titleEn || "",
        excerptUk: newPost.excerptUk || "",
        excerptEn: newPost.excerptEn || "",
        contentUk: newPost.contentUk || "",
        contentEn: newPost.contentEn || "",
        coverImage: newPost.coverImage || "",
        status: newPost.status || "draft",
        categoryId: newPost.categoryId || null,
        tagIds: newPost.tags ? newPost.tags.map((t) => t.id) : [],
        publishedAt: newPost.publishedAt
          ? newPost.publishedAt.slice(0, 16)
          : "",
      };
    } else {
      postForm.value = defaultPostForm();
    }
  },
  { immediate: true },
);

// Inline tag creation
const showInlineTagForm = ref(false);
const inlineTagNameUk = ref("");
const inlineTagNameEn = ref("");
const savingInlineTag = ref(false);

const openInlineTagCreate = () => {
  inlineTagNameUk.value = "";
  inlineTagNameEn.value = "";
  showInlineTagForm.value = true;
};

const saveInlineTag = async () => {
  if (!inlineTagNameUk.value.trim() || !inlineTagNameEn.value.trim()) {
    toast.warning(t("admin.blog.posts.form.alerts.nameRequiredBothLangs"));
    return;
  }
  savingInlineTag.value = true;
  try {
    const { data } = await api.post("/admin/blog/tags", {
      nameUk: inlineTagNameUk.value.trim(),
      nameEn: inlineTagNameEn.value.trim(),
    });
    const newTag = data.data;
    emit("tag-created", newTag);
    postForm.value.tagIds.push(newTag.id);
    showInlineTagForm.value = false;
    toast.success(t("admin.blog.posts.form.alerts.tagCreated"));
  } catch (e) {
    toast.error(t("admin.blog.posts.form.alerts.tagCreateError"));
  } finally {
    savingInlineTag.value = false;
  }
};

const toggleTag = (id) => {
  const idx = postForm.value.tagIds.indexOf(id);
  if (idx === -1) {
    postForm.value.tagIds.push(id);
  } else {
    postForm.value.tagIds.splice(idx, 1);
  }
};

// Cover photo uploads
const uploadCover = async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const form = new FormData();
  form.append("image", file);
  try {
    const { data } = await api.post("/admin/blog/upload", form, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    postForm.value.coverImage = data.data.url;
  } catch (e) {
    toast.error(t("admin.blog.posts.form.alerts.imageUploadError"));
  }
};

const handleImageUpload = async (file, callback) => {
  const form = new FormData();
  form.append("image", file);
  try {
    const { data } = await api.post("/admin/blog/upload", form, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    callback(data.data.url);
  } catch (e) {
    toast.error(t("admin.blog.posts.form.alerts.imageUploadError"));
  }
};

// Save post
const savePost = async () => {
  if (!postForm.value.titleUk || !postForm.value.titleEn) {
    toast.warning(t("admin.blog.posts.form.alerts.titleRequiredBothLangs"));
    return;
  }
  saving.value = true;
  try {
    if (props.post) {
      await api.put(`/admin/blog/posts/${props.post.id}`, postForm.value);
    } else {
      await api.post("/admin/blog/posts", postForm.value);
    }
    toast.success(
      props.post
        ? t("admin.blog.posts.form.alerts.postUpdated")
        : t("admin.blog.posts.form.alerts.postCreated"),
    );
    emit("update:modelValue", false);
    emit("refresh");
  } catch (e) {
    toast.error(t("admin.blog.posts.form.alerts.saveError"));
  } finally {
    saving.value = false;
  }
};
</script>
