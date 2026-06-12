<template>
  <div class="space-y-6 animate-in fade-in duration-500">
    <!-- Header actions -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
      <div class="flex gap-2">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          :class="[
            'px-4 py-2 rounded-xl text-sm font-semibold transition-all',
            activeTab === tab.key
              ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20'
              : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700'
          ]"
          @click="activeTab = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>
      <button
        class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all shadow-lg shadow-emerald-500/20"
        @click="openNewPost"
      >
        <PlusIcon class="w-4 h-4" />
        Новий пост
      </button>
    </div>

    <!-- POSTS tab -->
    <div
      v-if="activeTab === 'posts'"
      class="space-y-6"
    >
      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Пошук постів..."
          class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >
        <select
          v-model="statusFilter"
          class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >
          <option value="">
            Всі статуси
          </option>
          <option value="published">
            Опубліковані
          </option>
          <option value="draft">
            Чернетки
          </option>
          <option value="archived">
            Архів
          </option>
        </select>
        <select
          v-model="categoryFilter"
          class="px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"
        >
          <option value="">
            Всі категорії
          </option>
          <option
            v-for="cat in categories"
            :key="cat.id"
            :value="cat.id"
          >
            {{ cat.nameUk || cat.nameEn }}
          </option>
        </select>
      </div>

      <!-- Posts grid -->
      <div
        v-if="loading"
        class="flex items-center justify-center py-20"
      >
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-b-4 border-emerald-500" />
      </div>

      <div
        v-else-if="posts.length === 0"
        class="text-center py-20 text-gray-400"
      >
        <DocumentTextIcon class="w-12 h-12 mx-auto mb-3 opacity-40" />
        <p class="text-sm">
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
          class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg hover:shadow-black/5 transition-all duration-300"
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
              <button
                class="flex-1 text-center py-2 rounded-lg text-xs font-semibold bg-gray-50 dark:bg-gray-700/50 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors"
                @click="openEditPost(post)"
              >
                Редагувати
              </button>
              <button
                class="py-2 px-3 rounded-lg text-xs font-semibold bg-gray-50 dark:bg-gray-700/50 hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition-colors"
                @click="deletePost(post)"
              >
                <TrashIcon class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div
        v-if="pagination.last_page > 1"
        class="flex justify-center gap-2"
      >
        <button
          v-for="page in pagination.last_page"
          :key="page"
          :class="[
            'w-8 h-8 rounded-lg text-sm font-semibold transition-colors',
            pagination.current_page === page
              ? 'bg-emerald-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 hover:border-emerald-500'
          ]"
          @click="fetchPosts(page)"
        >
          {{ page }}
        </button>
      </div>
    </div>

    <!-- CATEGORIES tab -->
    <div
      v-if="activeTab === 'categories'"
      class="space-y-4"
    >
      <div class="flex justify-end">
        <button
          class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all"
          @click="openCategoryModal(null)"
        >
          <PlusIcon class="w-4 h-4" />
          Нова категорія
        </button>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Назва (УК)
              </th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Назва (EN)
              </th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Slug
              </th>
              <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Пости
              </th>
              <th class="px-5 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Дії
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-if="categories.length === 0">
              <td
                colspan="5"
                class="px-5 py-10 text-center text-gray-400 text-sm"
              >
                Категорій немає
              </td>
            </tr>
            <tr
              v-for="cat in categories"
              :key="cat.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
            >
              <td class="px-5 py-3.5 font-medium text-gray-800 dark:text-gray-200">
                {{ cat.nameUk }}
              </td>
              <td class="px-5 py-3.5 text-gray-600 dark:text-gray-400">
                {{ cat.nameEn }}
              </td>
              <td class="px-5 py-3.5 text-gray-500 dark:text-gray-500 font-mono text-xs">
                {{ cat.slug }}
              </td>
              <td class="px-5 py-3.5 text-center">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold">
                  {{ cat.postsCount }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 hover:text-emerald-600 transition-colors"
                    @click="openCategoryModal(cat)"
                  >
                    <PencilIcon class="w-3.5 h-3.5" />
                  </button>
                  <button
                    class="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition-colors"
                    @click="deleteCategory(cat)"
                  >
                    <TrashIcon class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAGS tab -->
    <div
      v-if="activeTab === 'tags'"
      class="space-y-4"
    >
      <div class="flex justify-end">
        <button
          class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all"
          @click="openTagModal(null)"
        >
          <PlusIcon class="w-4 h-4" />
          Новий тег
        </button>
      </div>
      <div class="flex flex-wrap gap-3">
        <div
          v-for="tag in tags"
          :key="tag.id"
          class="flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 shadow-sm"
        >
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ tag.nameUk || tag.nameEn }}</span>
          <span class="text-xs text-gray-400">({{ tag.postsCount }})</span>
          <div class="flex items-center gap-1 ml-1">
            <button
              class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400 hover:text-emerald-600 transition-colors"
              @click="openTagModal(tag)"
            >
              <PencilIcon class="w-3 h-3" />
            </button>
            <button
              class="p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition-colors"
              @click="deleteTag(tag)"
            >
              <TrashIcon class="w-3 h-3" />
            </button>
          </div>
        </div>
        <div
          v-if="tags.length === 0"
          class="text-sm text-gray-400 py-4"
        >
          Тегів немає
        </div>
      </div>
    </div>

    <!-- POST EDITOR MODAL -->
    <Teleport to="body">
      <Transition name="modal">
        <div
          v-if="showPostModal"
          class="fixed inset-0 z-[9999] flex"
        >
          <!-- Backdrop -->
          <div
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            @click="closePostModal"
          />

          <!-- Editor panel (full-page slide) -->
          <div class="relative ml-auto w-full max-w-6xl h-full bg-gray-50 dark:bg-gray-900 overflow-y-auto shadow-2xl">
            <!-- Editor header -->
            <div class="sticky top-0 z-10 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <button
                  class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                  @click="closePostModal"
                >
                  <XMarkIcon class="w-5 h-5 text-gray-500" />
                </button>
                <div>
                  <h2 class="font-bold text-gray-800 dark:text-white text-base">
                    {{ editingPost ? 'Редагувати пост' : 'Новий пост' }}
                  </h2>
                  <p class="text-xs text-gray-400">
                    {{ postForm.status === 'published' ? 'Опублікований' : postForm.status === 'draft' ? 'Чернетка' : 'Архів' }}
                  </p>
                </div>
              </div>

              <div class="flex items-center gap-3">
                <select
                  v-model="postForm.status"
                  class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
                  <option value="draft">
                    Чернетка
                  </option>
                  <option value="published">
                    Опублікувати
                  </option>
                  <option value="archived">
                    Архів
                  </option>
                </select>
                <button
                  :disabled="saving"
                  class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="savePost"
                >
                  <span v-if="saving">Збереження...</span>
                  <span v-else>Зберегти</span>
                </button>
              </div>
            </div>

            <!-- Editor body -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 h-[calc(100%-73px)]">
              <!-- Main content area -->
              <div class="lg:col-span-2 p-6 space-y-5 overflow-y-auto border-r border-gray-200 dark:border-gray-700">
                <!-- Tabs for UK/EN -->
                <div class="flex gap-1 bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-fit">
                  <button
                    :class="['px-4 py-1.5 rounded-lg text-sm font-semibold transition-all', langTab === 'uk' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']"
                    @click="langTab = 'uk'"
                  >
                    🇺🇦 Українська
                  </button>
                  <button
                    :class="['px-4 py-1.5 rounded-lg text-sm font-semibold transition-all', langTab === 'en' ? 'bg-white dark:bg-gray-700 text-gray-800 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']"
                    @click="langTab = 'en'"
                  >
                    🇬🇧 English
                  </button>
                </div>

                <!-- Title -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                    {{ langTab === 'uk' ? 'Заголовок (УК)' : 'Title (EN)' }}
                  </label>
                  <input
                    v-if="langTab === 'uk'"
                    v-model="postForm.titleUk"
                    type="text"
                    placeholder="Введіть заголовок..."
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-base font-bold text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                  >
                  <input
                    v-else
                    v-model="postForm.titleEn"
                    type="text"
                    placeholder="Enter title..."
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-base font-bold text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                  >
                </div>

                <!-- Excerpt -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                    {{ langTab === 'uk' ? 'Анонс (УК)' : 'Excerpt (EN)' }}
                  </label>
                  <textarea
                    v-if="langTab === 'uk'"
                    v-model="postForm.excerptUk"
                    rows="2"
                    placeholder="Короткий опис..."
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                  />
                  <textarea
                    v-else
                    v-model="postForm.excerptEn"
                    rows="2"
                    placeholder="Short description..."
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                  />
                </div>

                <!-- Rich text editor -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                    {{ langTab === 'uk' ? 'Контент (УК)' : 'Content (EN)' }}
                  </label>
                  <RichEditor
                    v-if="langTab === 'uk'"
                    :key="`uk-${editingPost?.id || 'new'}`"
                    v-model="postForm.contentUk"
                    @upload-image="handleImageUpload"
                  />
                  <RichEditor
                    v-else
                    :key="`en-${editingPost?.id || 'new'}`"
                    v-model="postForm.contentEn"
                    @upload-image="handleImageUpload"
                  />
                </div>
              </div>

              <!-- Sidebar -->
              <div class="p-6 space-y-5 overflow-y-auto">
                <!-- Cover image -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Обкладинка</label>
                  <div class="relative">
                    <div
                      v-if="postForm.coverImage"
                      class="relative rounded-xl overflow-hidden aspect-video mb-2"
                    >
                      <img
                        :src="postForm.coverImage"
                        class="w-full h-full object-cover"
                      >
                      <button
                        class="absolute top-2 right-2 p-1.5 bg-black/50 hover:bg-black/70 rounded-lg text-white transition-colors"
                        @click="postForm.coverImage = ''"
                      >
                        <XMarkIcon class="w-4 h-4" />
                      </button>
                    </div>
                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-colors">
                      <PhotoIcon class="w-6 h-6 text-gray-300 dark:text-gray-600 mb-1" />
                      <span class="text-xs text-gray-400">Завантажити фото</span>
                      <input
                        type="file"
                        accept="image/*"
                        class="sr-only"
                        @change="uploadCover"
                      >
                    </label>
                  </div>
                </div>

                <!-- Category -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Категорія</label>
                  <select
                    v-model="postForm.categoryId"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                  >
                    <option :value="null">
                      Без категорії
                    </option>
                    <option
                      v-for="cat in categories"
                      :key="cat.id"
                      :value="cat.id"
                    >
                      {{ cat.nameUk || cat.nameEn }}
                    </option>
                  </select>
                </div>

                <!-- Tags -->
                <div>
                  <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Теги</label>
                    <button
                      class="flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 hover:underline"
                      @click="openInlineTagCreate"
                    >
                      <PlusIcon class="w-3 h-3" />
                      Новий тег
                    </button>
                  </div>
                  <!-- Quick create tag inline -->
                  <div
                    v-if="showInlineTagForm"
                    class="mb-3 flex gap-2"
                  >
                    <input
                      v-model="inlineTagNameUk"
                      type="text"
                      placeholder="Назва (УК)"
                      class="flex-1 min-w-0 px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-xs text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                      @keyup.enter="saveInlineTag"
                    >
                    <input
                      v-model="inlineTagNameEn"
                      type="text"
                      placeholder="Name (EN)"
                      class="flex-1 min-w-0 px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-xs text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                      @keyup.enter="saveInlineTag"
                    >
                    <button
                      :disabled="savingInlineTag"
                      class="px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold disabled:opacity-50"
                      @click="saveInlineTag"
                    >
                      {{ savingInlineTag ? '...' : '✓' }}
                    </button>
                    <button
                      class="px-2 py-1.5 rounded-lg text-gray-400 hover:text-gray-600 text-xs"
                      @click="showInlineTagForm = false"
                    >
                      ✕
                    </button>
                  </div>
                  <div class="flex flex-wrap gap-2">
                    <button
                      v-for="tag in tags"
                      :key="tag.id"
                      :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all',
                        postForm.tagIds.includes(tag.id)
                          ? 'bg-emerald-600 text-white'
                          : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600'
                      ]"
                      @click="toggleTag(tag.id)"
                    >
                      {{ tag.nameUk || tag.nameEn }}
                    </button>
                    <span
                      v-if="tags.length === 0"
                      class="text-xs text-gray-400 italic"
                    >Тегів ще немає — створіть перший</span>
                  </div>
                </div>

                <!-- Published at -->
                <div>
                  <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Дата публікації</label>
                  <input
                    v-model="postForm.publishedAt"
                    type="datetime-local"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- CATEGORY MODAL -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showCategoryModal"
          class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        >
          <div
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            @click="showCategoryModal = false"
          />
          <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-5">
            <h3 class="font-bold text-gray-800 dark:text-white text-lg">
              {{ editingCategory ? 'Редагувати категорію' : 'Нова категорія' }}
            </h3>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Назва (УК) *</label>
                <input
                  v-model="categoryForm.nameUk"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Name (EN) *</label>
                <input
                  v-model="categoryForm.nameEn"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Опис (УК)</label>
                <textarea
                  v-model="categoryForm.descriptionUk"
                  rows="2"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Description (EN)</label>
                <textarea
                  v-model="categoryForm.descriptionEn"
                  rows="2"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 resize-none"
                />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Порядок</label>
                <input
                  v-model.number="categoryForm.order"
                  type="number"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
              </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button
                class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                @click="showCategoryModal = false"
              >
                Скасувати
              </button>
              <button
                :disabled="savingCategory"
                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors disabled:opacity-50"
                @click="saveCategory"
              >
                {{ savingCategory ? 'Збереження...' : 'Зберегти' }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- TAG MODAL -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showTagModal"
          class="fixed inset-0 z-[9999] flex items-center justify-center p-4"
        >
          <div
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            @click="showTagModal = false"
          />
          <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-5">
            <h3 class="font-bold text-gray-800 dark:text-white text-lg">
              {{ editingTag ? 'Редагувати тег' : 'Новий тег' }}
            </h3>
            <div class="space-y-4">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Назва (УК) *</label>
                <input
                  v-model="tagForm.nameUk"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Name (EN) *</label>
                <input
                  v-model="tagForm.nameEn"
                  type="text"
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >
              </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button
                class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                @click="showTagModal = false"
              >
                Скасувати
              </button>
              <button
                :disabled="savingTag"
                class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors disabled:opacity-50"
                @click="saveTag"
              >
                {{ savingTag ? 'Збереження...' : 'Зберегти' }}
              </button>
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
  TagIcon,
  EyeIcon,
  PhotoIcon,
} from '@heroicons/vue/24/outline';

const toast = useToast();

const activeTab = ref('posts');
const tabs = [
  { key: 'posts', label: 'Пости' },
  { key: 'categories', label: 'Категорії' },
  { key: 'tags', label: 'Теги' },
];

// ─── Data ───────────────────────────────────────────────────────────────────
const posts = ref([]);
const categories = ref([]);
const tags = ref([]);
const loading = ref(false);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

// Filters
const search = ref('');
const statusFilter = ref('');
const categoryFilter = ref('');

// ─── Post editor ────────────────────────────────────────────────────────────
const showPostModal = ref(false);
const editingPost = ref(null);
const saving = ref(false);
const langTab = ref('uk');

const defaultPostForm = () => ({
  titleUk: '',
  titleEn: '',
  excerptUk: '',
  excerptEn: '',
  contentUk: '',
  contentEn: '',
  coverImage: '',
  status: 'draft',
  categoryId: null,
  tagIds: [],
  publishedAt: '',
});

const postForm = ref(defaultPostForm());

// ─── Category modal ──────────────────────────────────────────────────────────
const showCategoryModal = ref(false);
const editingCategory = ref(null);
const savingCategory = ref(false);
const categoryForm = ref({ nameUk: '', nameEn: '', descriptionUk: '', descriptionEn: '', order: 0 });

// ─── Tag modal ───────────────────────────────────────────────────────────────
const showTagModal = ref(false);
const editingTag = ref(null);
const savingTag = ref(false);
const tagForm = ref({ nameUk: '', nameEn: '' });

// ─── Inline tag creation (inside post editor sidebar) ────────────────────────
const showInlineTagForm = ref(false);
const inlineTagNameUk = ref('');
const inlineTagNameEn = ref('');
const savingInlineTag = ref(false);

const openInlineTagCreate = () => {
  inlineTagNameUk.value = '';
  inlineTagNameEn.value = '';
  showInlineTagForm.value = true;
};

const saveInlineTag = async () => {
  if (!inlineTagNameUk.value.trim() || !inlineTagNameEn.value.trim()) {
    toast.warning('Вкажіть назву для обох мов');
    return;
  }
  savingInlineTag.value = true;
  try {
    const { data } = await api.post('/admin/blog/tags', {
      nameUk: inlineTagNameUk.value.trim(),
      nameEn: inlineTagNameEn.value.trim(),
    });
    // Add to tags list and auto-select it
    const newTag = data.data;
    tags.value.unshift(newTag);
    postForm.value.tagIds.push(newTag.id);
    showInlineTagForm.value = false;
    toast.success('Тег створено та додано');
  } catch (e) {
    toast.error('Помилка створення тегу');
  } finally {
    savingInlineTag.value = false;
  }
};

// ─── Methods ─────────────────────────────────────────────────────────────────
const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await api.get('/admin/blog/posts', {
      params: {
        page,
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        category_id: categoryFilter.value || undefined,
      },
    });
    posts.value = data.data.data;
    pagination.value = data.data.meta;
  } catch (e) {
    toast.error('Помилка завантаження постів');
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  const { data } = await api.get('/admin/blog/categories');
  categories.value = data.data;
};

const fetchTags = async () => {
  const { data } = await api.get('/admin/blog/tags');
  tags.value = data.data;
};

// ─── Post editor ─────────────────────────────────────────────────────────────
const openNewPost = () => {
  editingPost.value = null;
  langTab.value = 'uk';
  postForm.value = defaultPostForm();
  showPostModal.value = true;
};

const openEditPost = async (post) => {
  langTab.value = 'uk';
  try {
    const { data } = await api.get(`/admin/blog/posts/${post.id}`);
    const p = data.data;
    editingPost.value = p;
    postForm.value = {
      titleUk: p.titleUk,
      titleEn: p.titleEn,
      excerptUk: p.excerptUk,
      excerptEn: p.excerptEn,
      contentUk: p.contentUk,
      contentEn: p.contentEn,
      coverImage: p.coverImage || '',
      status: p.status,
      categoryId: p.categoryId,
      tagIds: p.tags.map(t => t.id),
      publishedAt: p.publishedAt ? p.publishedAt.slice(0, 16) : '',
    };
    showPostModal.value = true;
  } catch (e) {
    toast.error('Помилка завантаження поста');
  }
};

const closePostModal = () => {
  showPostModal.value = false;
  editingPost.value = null;
  showInlineTagForm.value = false;
  inlineTagNameUk.value = '';
  inlineTagNameEn.value = '';
};

const savePost = async () => {
  if (!postForm.value.titleUk || !postForm.value.titleEn) {
    toast.warning('Заголовок обов\'язковий для обох мов');
    return;
  }
  saving.value = true;
  try {
    if (editingPost.value) {
      await api.put(`/admin/blog/posts/${editingPost.value.id}`, postForm.value);
    } else {
      await api.post('/admin/blog/posts', postForm.value);
    }
    toast.success(editingPost.value ? 'Пост оновлено!' : 'Пост створено!');
    closePostModal();
    fetchPosts(pagination.value.current_page);
  } catch (e) {
    toast.error('Помилка збереження');
  } finally {
    saving.value = false;
  }
};

const deletePost = async (post) => {
  if (!confirm(`Видалити пост "${post.titleUk || post.titleEn}"?`)) return;
  try {
    await api.delete(`/admin/blog/posts/${post.id}`);
    toast.success('Пост видалено');
    fetchPosts(pagination.value.current_page);
  } catch (e) {
    toast.error('Помилка видалення');
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

const uploadCover = async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  const form = new FormData();
  form.append('image', file);
  try {
    const { data } = await api.post('/admin/blog/upload', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    postForm.value.coverImage = data.data.url;
  } catch (e) {
    toast.error('Помилка завантаження зображення');
  }
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
  }
};

// ─── Categories ──────────────────────────────────────────────────────────────
const openCategoryModal = (cat) => {
  editingCategory.value = cat;
  categoryForm.value = cat
    ? { nameUk: cat.nameUk, nameEn: cat.nameEn, descriptionUk: cat.descriptionUk || '', descriptionEn: cat.descriptionEn || '', order: cat.order || 0 }
    : { nameUk: '', nameEn: '', descriptionUk: '', descriptionEn: '', order: 0 };
  showCategoryModal.value = true;
};

const saveCategory = async () => {
  if (!categoryForm.value.nameUk || !categoryForm.value.nameEn) {
    toast.warning('Назва обов\'язкова для обох мов');
    return;
  }
  savingCategory.value = true;
  try {
    if (editingCategory.value) {
      await api.put(`/admin/blog/categories/${editingCategory.value.id}`, categoryForm.value);
    } else {
      await api.post('/admin/blog/categories', categoryForm.value);
    }
    toast.success('Категорію збережено');
    showCategoryModal.value = false;
    fetchCategories();
  } catch (e) {
    toast.error('Помилка збереження категорії');
  } finally {
    savingCategory.value = false;
  }
};

const deleteCategory = async (cat) => {
  if (!confirm(`Видалити категорію "${cat.nameUk}"?`)) return;
  try {
    await api.delete(`/admin/blog/categories/${cat.id}`);
    toast.success('Категорію видалено');
    fetchCategories();
  } catch (e) {
    toast.error('Помилка видалення');
  }
};

// ─── Tags ─────────────────────────────────────────────────────────────────────
const openTagModal = (tag) => {
  editingTag.value = tag;
  tagForm.value = tag ? { nameUk: tag.nameUk, nameEn: tag.nameEn } : { nameUk: '', nameEn: '' };
  showTagModal.value = true;
};

const saveTag = async () => {
  if (!tagForm.value.nameUk || !tagForm.value.nameEn) {
    toast.warning('Назва обов\'язкова');
    return;
  }
  savingTag.value = true;
  try {
    if (editingTag.value) {
      await api.put(`/admin/blog/tags/${editingTag.value.id}`, tagForm.value);
    } else {
      await api.post('/admin/blog/tags', tagForm.value);
    }
    toast.success('Тег збережено');
    showTagModal.value = false;
    fetchTags();
  } catch (e) {
    toast.error('Помилка збереження тегу');
  } finally {
    savingTag.value = false;
  }
};

const deleteTag = async (tag) => {
  if (!confirm(`Видалити тег "${tag.nameUk}"?`)) return;
  try {
    await api.delete(`/admin/blog/tags/${tag.id}`);
    toast.success('Тег видалено');
    fetchTags();
  } catch (e) {
    toast.error('Помилка видалення');
  }
};

// ─── Helpers ──────────────────────────────────────────────────────────────────
const statusLabel = (s) => ({ published: 'Опубліковано', draft: 'Чернетка', archived: 'Архів' }[s] || s);
const statusBadgeClass = (s) => ({
  published: 'bg-emerald-500/90 text-white',
  draft: 'bg-amber-400/90 text-white',
  archived: 'bg-gray-400/80 text-white',
}[s] || 'bg-gray-400 text-white');

const formatDate = (d) => {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uk-UA', { day: '2-digit', month: 'short', year: 'numeric' });
};

// ─── Watchers ─────────────────────────────────────────────────────────────────
let debounce;
watch([search, statusFilter, categoryFilter], () => {
  clearTimeout(debounce);
  debounce = setTimeout(() => fetchPosts(1), 300);
});

// ─── Init ────────────────────────────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([fetchPosts(), fetchCategories(), fetchTags()]);
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
