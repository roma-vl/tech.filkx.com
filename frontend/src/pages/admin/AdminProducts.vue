<template>
  <div class="space-y-6">
    <!-- Tabs Header -->
    <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="activeTab === tab.id 
          ? 'bg-gradient-to-r from-primary-500 to-purple-600 text-white font-bold' 
          : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
        class="px-5 py-2.5 rounded-xl text-sm transition-all duration-300 flex items-center gap-2"
      >
        <span v-html="tab.icon"></span>
        {{ tab.name }}
      </button>
    </div>

    <!-- LOADING OVERLAY -->
    <div v-if="isLoading" class="relative min-h-[400px] flex items-center justify-center bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
      <div class="flex flex-col items-center gap-3">
        <div class="animate-spin rounded-full h-10 w-10 border-t-4 border-b-4 border-primary-500"></div>
        <p class="text-gray-500 dark:text-gray-400 font-medium">Завантаження даних...</p>
      </div>
    </div>

    <div v-else>
      <!-- TAB 1: PRODUCTS -->
      <div v-if="activeTab === 'products'" class="space-y-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
          <div class="flex-1 w-full sm:w-auto relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <input
              v-model="productSearch"
              type="text"
              placeholder="Пошук товарів за назвою чи SKU..."
              class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
            />
          </div>

          <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <select
              v-model="productCategoryFilter"
              class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm px-4 py-2.5 focus:ring-primary-500 focus:border-primary-500 transition-colors"
            >
              <option value="">Усі категорії</option>
              <option v-for="cat in dbCategories" :key="cat.id" :value="cat.id">{{ cat.nameUk }}</option>
            </select>

            <button
              @click="openAddProductModal"
              class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-purple-600 hover:from-primary-600 hover:to-purple-700 text-white font-bold rounded-xl text-sm shadow-md transition-all shrink-0"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Додати товар
            </button>
          </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Товар</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Категорія</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Бренд</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Варіанти (Ціна / Залишок)</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
                  <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дії</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-3">
                      <img :src="product.image" alt="" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 bg-gray-100" />
                      <div>
                        <div class="flex items-center gap-1.5">
                          <div class="font-bold text-gray-900 dark:text-white">{{ product.nameUk }}</div>
                          <span v-if="product.isHot" title="Гаряча пропозиція" class="text-xs">🔥</span>
                          <span v-if="product.isRecommended" title="Рекомендовано" class="text-xs">👍</span>
                        </div>
                        <div class="text-xs text-gray-400">{{ product.nameEn }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                    {{ product.categoryName || 'Без категорії' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                    {{ product.brandName || 'Без бренду' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="space-y-1">
                      <div v-for="v in product.variants" :key="v.id" class="text-xs text-gray-700 dark:text-gray-300">
                        <span class="font-mono bg-gray-100 dark:bg-gray-950 px-1 py-0.5 rounded text-[10px]">{{ v.sku }}</span>:
                        <span class="font-bold text-gray-900 dark:text-white">{{ formatPrice(v.price) }}</span>
                        <span class="text-gray-400"> ({{ v.stock }} шт)</span>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{
                      'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400': product.status === 'active',
                      'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': product.status === 'draft',
                      'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400': product.status === 'hidden'
                    }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider">
                      {{ product.status === 'active' ? 'Активний' : product.status === 'draft' ? 'Чернетка' : 'Прихований' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end gap-2">
                      <button @click="openEditProductModal(product)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button @click="deleteProduct(product.id)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredProducts.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    Товарів не знайдено за вашим запитом.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 2: CATEGORIES -->
      <div v-if="activeTab === 'categories'" class="space-y-6">
        <div class="flex justify-between items-center bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">Управління категоріями</h2>
          <button
            @click="openAddCategoryModal"
            class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-purple-600 hover:from-primary-600 hover:to-purple-700 text-white font-bold rounded-xl text-sm transition-all"
          >
            Додати категорію
          </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Назва (UK)</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Назва (EN)</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Батьківська категорія</th>
                  <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дії</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="cat in dbCategories" :key="cat.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                  <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ cat.id }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-bold">{{ cat.nameUk }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ cat.nameEn }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono">{{ cat.slug }}</td>
                  <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ cat.parentName || '—' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end gap-2">
                      <button @click="openEditCategoryModal(cat)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button @click="deleteCategory(cat.id)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="dbCategories.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                    Категорій не створено.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 3: BRANDS -->
      <div v-if="activeTab === 'brands'" class="space-y-6">
        <div class="flex justify-between items-center bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">Управління брендами</h2>
          <button
            @click="openAddBrandModal"
            class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-purple-600 hover:from-primary-600 hover:to-purple-700 text-white font-bold rounded-xl text-sm transition-all"
          >
            Додати бренд
          </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Назва бренду</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Slug</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Логотип URL</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Опис</th>
                  <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дії</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="brand in dbBrands" :key="brand.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                  <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ brand.id }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-bold">{{ brand.name }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono">{{ brand.slug }}</td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                    <span v-if="brand.logoPath" class="truncate max-w-[150px] inline-block font-mono text-xs">{{ brand.logoPath }}</span>
                    <span v-else class="text-gray-300 dark:text-gray-600">немає</span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 truncate max-w-[200px]">
                    {{ brand.description || '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end gap-2">
                      <button @click="openEditBrandModal(brand)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button @click="deleteBrand(brand.id)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 4: ATTRIBUTES -->
      <div v-if="activeTab === 'attributes'" class="space-y-6">
        <div class="flex justify-between items-center bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">Характеристики та атрибути</h2>
          <button
            @click="openAddAttributeModal"
            class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-purple-600 hover:from-primary-600 hover:to-purple-700 text-white font-bold rounded-xl text-sm transition-all"
          >
            Додати атрибут
          </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Код атрибуту</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Назва (UK)</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Тип поля</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Варіанти значень</th>
                  <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дії</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="attr in dbAttributes" :key="attr.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                  <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-white">{{ attr.id }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-mono font-bold">{{ attr.code }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ attr.nameUk }}</td>
                  <td class="px-6 py-4 text-sm font-semibold uppercase tracking-wider text-xs">
                    <span :class="{
                      'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400': attr.type === 'select',
                      'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': attr.type === 'color',
                      'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': attr.type === 'text'
                    }" class="px-2 py-0.5 rounded">
                      {{ attr.type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="val in attr.values" :key="val.id" class="bg-gray-100 dark:bg-gray-900 px-2 py-0.5 rounded text-xs">
                        {{ val.valueUk || val.value }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end gap-2">
                      <button @click="openEditAttributeModal(attr)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button @click="deleteAttribute(attr.id)" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 1: PRODUCT EDIT/CREATE (DOUBLY RICH MULTI-VARIANT AND MULTILANGUAL FORM) -->
    <div v-if="showProductModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-4xl w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden transition-all my-8">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
          <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-wider">
            {{ isEditing ? 'Редагувати товар' : 'Створити товар із варіантами' }}
          </h3>
          <button @click="showProductModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <form @submit.prevent="saveProduct" class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
          <!-- General Details section -->
          <div class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 space-y-4">
            <h4 class="font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">1. Загальна інформація про товар</h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <AppInput
                v-model="productForm.nameUk"
                required
                label="Назва товару (UK)"
                placeholder="напр. iPhone 15 Pro Max"
              />
              <AppInput
                v-model="productForm.nameEn"
                required
                label="Назва товару (EN)"
                placeholder="e.g. iPhone 15 Pro Max"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <AppTextarea
                v-model="productForm.descriptionUk"
                rows="3"
                label="Опис (UK)"
                placeholder="Опис українською..."
              />
              <AppTextarea
                v-model="productForm.descriptionEn"
                rows="3"
                label="Опис (EN)"
                placeholder="Description in English..."
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <AppSelect
                v-model="productForm.categoryId"
                required
                label="Категорія"
                placeholder="Оберіть категорію"
                :options="dbCategories"
                option-value="id"
                option-label="nameUk"
              />
              <AppSelect
                v-model="productForm.brandId"
                label="Бренд"
                placeholder="Без бренду"
                :options="[{ id: null, name: 'Без бренду' }, ...dbBrands]"
                option-value="id"
                option-label="name"
              />
              <AppSelect
                v-model="productForm.status"
                required
                label="Статус"
                :options="[
                  { id: 'active', name: 'Активний' },
                  { id: 'draft', name: 'Чернетка' },
                  { id: 'hidden', name: 'Прихований' }
                ]"
                option-value="id"
                option-label="name"
              />
            </div>

            <!-- Promotion Tags -->
            <div class="flex flex-wrap items-center gap-6 pt-2">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  v-model="productForm.isHot"
                  type="checkbox"
                  class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                />
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">🔥 Гаряча пропозиція (Hot Offer)</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                  v-model="productForm.isRecommended"
                  type="checkbox"
                  class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"
                />
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">👍 Рекомендовано (Recommended)</span>
              </label>
            </div>
          </div>

          <!-- Variants section -->
          <div class="bg-gray-50 dark:bg-gray-900/40 p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 space-y-6">
            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-2">
              <h4 class="font-bold text-gray-900 dark:text-white">2. Варіанти товару та наявність</h4>
              <button type="button" @click="addProductVariant" class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1">
                + Додати варіант
              </button>
            </div>

            <div v-for="(v, index) in productForm.variants" :key="index" class="bg-white dark:bg-gray-850 p-5 rounded-xl border border-gray-100 dark:border-gray-800 space-y-4 shadow-sm relative">
              <button v-if="productForm.variants.length > 1" type="button" @click="removeProductVariant(index)" class="absolute top-4 right-4 text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>

              <h5 class="text-base font-bold text-primary-500">Варіант #{{ index + 1 }}</h5>

              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                <AppInput
                  v-model="v.sku"
                  required
                  label="SKU артикул"
                  placeholder="SKU"
                />
                <AppInput
                  v-model.number="v.price"
                  required
                  type="number"
                  step="0.01"
                  label="Ціна (₴)"
                />
                <AppInput
                  v-model.number="v.oldPrice"
                  type="number"
                  step="0.01"
                  label="Стара ціна (₴)"
                />
                <AppInput
                  v-model.number="v.stock"
                  required
                  type="number"
                  label="Склад (шт)"
                />
                <AppInput
                  v-model.number="v.weight"
                  type="number"
                  step="0.01"
                  label="Вага (кг)"
                />
              </div>

              <!-- Images Section with Drag-and-Drop and Server Upload -->
              <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                <div class="flex justify-between items-center">
                  <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Фотогалерея варіанту (Перше фото автоматично стає головним)
                  </label>
                  <span class="text-xs text-gray-400">Перетягуйте фото мишкою для зміни порядку</span>
                </div>

                <!-- Image Grid with Drag and Drop -->
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
                  <div
                    v-for="(img, imgIdx) in v.images"
                    :key="imgIdx"
                    draggable="true"
                    @dragstart="onDragStart($event, imgIdx, v)"
                    @dragover.prevent
                    @drop="onDrop($event, imgIdx, v)"
                    @dragend="onDragEnd"
                    :class="img.isPrimary 
                      ? 'border-2 border-emerald-500 ring-2 ring-emerald-500/20 shadow-md scale-102' 
                      : 'border border-gray-200 dark:border-gray-700 hover:border-primary-500'"
                    class="relative aspect-square rounded-2xl overflow-hidden bg-gray-50 dark:bg-gray-900 group cursor-move transition-all duration-200"
                  >
                    <img :src="img.url" alt="" class="w-full h-full object-cover" />
                    
                    <!-- Badges -->
                    <span v-if="img.isPrimary" class="absolute top-2 left-2 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full shadow">
                      ★ Головне
                    </span>
                    <span v-else class="absolute top-2 left-2 bg-black/60 backdrop-blur-xs text-white text-[8px] font-bold px-2 py-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                      #{{ imgIdx + 1 }}
                    </span>

                    <!-- Action buttons -->
                    <button
                      type="button"
                      @click="removeVariantImage(v, imgIdx)"
                      class="absolute top-2 right-2 p-1.5 bg-rose-500 text-white rounded-lg opacity-0 group-hover:opacity-100 hover:bg-rose-600 transition-all shadow-md"
                    >
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>

                  <!-- Upload Button / Area -->
                  <label class="border-2 border-dashed border-gray-300 dark:border-gray-700 hover:border-primary-500 rounded-2xl flex flex-col items-center justify-center aspect-square cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/30 group">
                    <svg class="w-6 h-6 text-gray-400 group-hover:text-primary-500 mb-1 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 group-hover:text-primary-500 transition-colors">Завантажити</span>
                    <input
                      type="file"
                      multiple
                      accept="image/*"
                      class="hidden"
                      @change="onFileChange($event, v)"
                    />
                  </label>
                </div>
              </div>

              <!-- Attributes for Variant section -->
              <div class="space-y-3 border-t border-gray-100 dark:border-gray-800 pt-4">
                <div class="flex justify-between items-center">
                  <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Характеристики варіанту (Атрибути)</label>
                  <button type="button" @click="addVariantAttribute(v)" class="text-xs font-bold text-primary-500 hover:text-primary-600 transition-colors">+ Додати характеристику</button>
                </div>

                <div v-for="(attr, aIdx) in v.attributes" :key="aIdx" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end bg-gray-50 dark:bg-gray-900/60 p-4 rounded-xl border border-gray-200/40">
                  <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Атрибут</label>
                    <select v-model="attr.attributeId" @change="onAttributeSelected(attr)" required class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-primary-500">
                      <option value="" disabled>Оберіть характеристику</option>
                      <option v-for="dbAttr in dbAttributes" :key="dbAttr.id" :value="dbAttr.id">{{ dbAttr.nameUk }}</option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Значення</label>
                    <!-- Select option list if it's dynamic select or color attribute type -->
                    <select v-if="getAttributeType(attr.attributeId) === 'select' || getAttributeType(attr.attributeId) === 'color'" v-model="attr.valueId" required class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-primary-500">
                      <option v-for="val in getAttributeValues(attr.attributeId)" :key="val.id" :value="val.id">
                        {{ val.valueUk || val.value }}
                      </option>
                    </select>
                    <!-- Free text input for custom attribute values -->
                    <input v-else v-model="attr.value" required type="text" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:ring-primary-500" placeholder="напр. 8GB чи M2" />
                  </div>

                  <div class="flex justify-end">
                    <button type="button" @click="removeVariantAttribute(v, aIdx)" class="text-red-500 hover:text-red-700 text-sm font-bold py-2 transition-colors">
                      Видалити
                    </button>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Bottom controls -->
          <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-4">
            <AppButton
              variant="secondary"
              @click="showProductModal = false"
            >
              Скасувати
            </AppButton>
            <AppButton
              type="submit"
              variant="primary"
            >
              Зберегти товар
            </AppButton>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: CATEGORY MODAL -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden transition-all">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
          <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-wider">
            {{ isEditing ? 'Редагувати категорію' : 'Додати категорію' }}
          </h3>
          <button @click="showCategoryModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <form @submit.prevent="saveCategory" class="p-6 space-y-4">
          <AppInput
            v-model="categoryForm.nameUk"
            required
            label="Назва категорії (UK)"
            placeholder="напр. Планшети"
          />

          <AppInput
            v-model="categoryForm.nameEn"
            required
            label="Назва категорії (EN)"
            placeholder="e.g. Tablets"
          />

          <AppSelect
            v-model="categoryForm.parentId"
            label="Батьківська категорія"
            placeholder="Немає (Головна категорія)"
            :options="[{ id: null, nameUk: 'Немає (Головна категорія)' }, ...dbCategories.filter(c => c.id !== categoryForm.id)]"
            option-value="id"
            option-label="nameUk"
          />

          <AppInput
            v-model.number="categoryForm.order"
            type="number"
            label="Порядок сортування"
          />

          <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-4 mt-6">
            <AppButton variant="secondary" @click="showCategoryModal = false">
              Скасувати
            </AppButton>
            <AppButton type="submit" variant="primary">
              Зберегти
            </AppButton>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 3: BRAND MODAL -->
    <div v-if="showBrandModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden transition-all">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
          <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-wider">
            {{ isEditing ? 'Редагувати бренд' : 'Додати бренд' }}
          </h3>
          <button @click="showBrandModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <form @submit.prevent="saveBrand" class="p-6 space-y-4">
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

          <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-4 mt-6">
            <AppButton variant="secondary" @click="showBrandModal = false">
              Скасувати
            </AppButton>
            <AppButton type="submit" variant="primary">
              Зберегти
            </AppButton>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 4: ATTRIBUTE MODAL -->
    <div v-if="showAttributeModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden transition-all">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
          <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-wider">
            {{ isEditing ? 'Редагувати атрибут' : 'Додати атрибут' }}
          </h3>
          <button @click="showAttributeModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <form @submit.prevent="saveAttribute" class="p-6 space-y-4">
          <AppInput
            v-model="attributeForm.code"
            required
            label="Код атрибуту (системний)"
            placeholder="напр. color, ram, screen_size"
          />

          <AppInput
            v-model="attributeForm.nameUk"
            required
            label="Назва атрибуту (UK)"
            placeholder="напр. Колір чи ОЗП"
          />

          <AppInput
            v-model="attributeForm.nameEn"
            required
            label="Назва атрибуту (EN)"
            placeholder="e.g. Color or RAM"
          />

          <AppSelect
            v-model="attributeForm.type"
            required
            label="Тип поля"
            :options="[
              { id: 'text', name: 'Текст (Вільне введення)' },
              { id: 'select', name: 'Випадаючий список варіантів' },
              { id: 'color', name: 'Кольоровий вибір' },
              { id: 'number', name: 'Число' },
              { id: 'boolean', name: 'Так / Ні (Булеве)' }
            ]"
            option-value="id"
            option-label="name"
          />

          <!-- Attributes preset values list -->
          <div v-if="attributeForm.type === 'select' || attributeForm.type === 'color'" class="space-y-2 mt-4 pt-4 border-t border-gray-150 dark:border-gray-700">
            <div class="flex justify-between items-center">
              <label class="block text-xs font-bold text-gray-500 uppercase">Список можливих значень</label>
              <button type="button" @click="addAttributeValue" class="text-xs font-bold text-primary-500 hover:text-primary-600">+ Додати значення</button>
            </div>

            <div v-for="(val, vIdx) in attributeForm.values" :key="vIdx" class="flex gap-2 items-center bg-gray-50 dark:bg-gray-900/50 p-2 rounded-xl border">
              <div v-if="attributeForm.type === 'color'" class="flex-1 flex gap-2">
                <input v-model="val.value" required type="text" placeholder="#FF0000" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs" />
                <input type="color" v-model="val.value" class="w-8 h-8 rounded border cursor-pointer bg-transparent" />
              </div>
              <div v-else class="flex-1 flex gap-2">
                <input v-model="val.valueUk" required type="text" placeholder="Значення (UK)" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs" />
                <input v-model="val.valueEn" required type="text" placeholder="Value (EN)" class="w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs" />
              </div>

              <button type="button" @click="removeAttributeValue(vIdx)" class="text-red-500 hover:text-red-700 text-xs font-bold px-1">Х</button>
            </div>
          </div>

          <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-4 mt-6">
            <AppButton variant="secondary" @click="showAttributeModal = false">
              Скасувати
            </AppButton>
            <AppButton type="submit" variant="primary">
              Зберегти
            </AppButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';
import AppInput from '@/components/admin/ui/Form/AppInput.vue';
import AppTextarea from '@/components/admin/ui/Form/AppTextarea.vue';
import AppSelect from '@/components/admin/ui/Form/AppSelect.vue';
import AppButton from '@/components/admin/ui/Button/AppButton.vue';

const tabs = [
  { id: 'products', name: 'Товари', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>' },
  { id: 'categories', name: 'Категорії', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>' },
  { id: 'brands', name: 'Бренди', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>' },
  { id: 'attributes', name: 'Характеристики', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>' }
];

const activeTab = ref('products');
const isLoading = ref(false);

const dbProducts = ref([]);
const dbCategories = ref([]);
const dbBrands = ref([]);
const dbAttributes = ref([]);

// Filter refs
const productSearch = ref('');
const productCategoryFilter = ref('');

// Modals display refs
const showProductModal = ref(false);
const showCategoryModal = ref(false);
const showBrandModal = ref(false);
const showAttributeModal = ref(false);
const isEditing = ref(false);

// Forms templates
const productForm = ref({
  id: null,
  nameUk: '',
  nameEn: '',
  descriptionUk: '',
  descriptionEn: '',
  categoryId: '',
  brandId: null,
  status: 'active',
  isHot: false,
  isRecommended: false,
  variants: []
});

const categoryForm = ref({
  id: null,
  nameUk: '',
  nameEn: '',
  parentId: null,
  order: 0
});

const brandForm = ref({
  id: null,
  name: '',
  logoPath: '',
  description: ''
});

const attributeForm = ref({
  id: null,
  code: '',
  nameUk: '',
  nameEn: '',
  type: 'text',
  values: []
});

// Load catalog metadata
const fetchAllData = async () => {
  isLoading.value = true;
  try {
    const productsRes = await api.get('/admin/products');
    dbProducts.value = productsRes.data.data;

    const catsRes = await api.get('/admin/categories');
    dbCategories.value = catsRes.data.data;

    const brandsRes = await api.get('/admin/brands');
    dbBrands.value = brandsRes.data.data;

    const attrsRes = await api.get('/admin/attributes');
    dbAttributes.value = attrsRes.data.data;
  } catch (error) {
    console.error('Failed to load catalog data:', error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchAllData();
});

// Filtered Products Computed
const filteredProducts = computed(() => {
  return dbProducts.value.filter(product => {
    const nameMatch = (product.nameUk || '').toLowerCase().includes(productSearch.value.toLowerCase()) ||
                      (product.nameEn || '').toLowerCase().includes(productSearch.value.toLowerCase()) ||
                      (product.variants || []).some(v => (v.sku || '').toLowerCase().includes(productSearch.value.toLowerCase()));
    
    const catMatch = !productCategoryFilter.value || product.categoryId === parseInt(productCategoryFilter.value);
    
    return nameMatch && catMatch;
  });
});

// PRODUCTS OPERATIONS
const openAddProductModal = () => {
  isEditing.value = false;
  productForm.value = {
    id: null,
    nameUk: '',
    nameEn: '',
    descriptionUk: '',
    descriptionEn: '',
    categoryId: dbCategories.value[0]?.id || '',
    brandId: null,
    status: 'active',
    isHot: false,
    isRecommended: false,
    variants: [
      {
        id: null,
        sku: '',
        price: 0,
        oldPrice: null,
        stock: 10,
        weight: null,
        images: [{ url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop', isPrimary: true }],
        attributes: []
      }
    ]
  };
  showProductModal.value = true;
};

const openEditProductModal = (product) => {
  isEditing.value = true;
  
  // Clone product and variants to avoid side-effects
  const variantsCloned = (product.variants || []).map(v => {
    const imagesMapped = (v.images || []).map(img => ({ url: img.url, isPrimary: !!img.isPrimary }));
    if (imagesMapped.length === 0) {
      imagesMapped.push({ url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop', isPrimary: true });
    }
    
    const attributesMapped = (v.attributes || []).map(a => ({
      attributeId: a.attributeId,
      valueId: a.valueId,
      value: a.value
    }));

    return {
      id: v.id,
      sku: v.sku,
      price: v.price,
      oldPrice: v.oldPrice,
      stock: v.stock,
      weight: v.weight,
      images: imagesMapped,
      attributes: attributesMapped
    };
  });

  productForm.value = {
    id: product.id,
    nameUk: product.nameUk,
    nameEn: product.nameEn,
    descriptionUk: product.descriptionUk,
    descriptionEn: product.descriptionEn,
    categoryId: product.categoryId || '',
    brandId: product.brandId,
    status: product.status,
    isHot: !!product.isHot,
    isRecommended: !!product.isRecommended,
    variants: variantsCloned.length > 0 ? variantsCloned : [
      {
        id: null,
        sku: '',
        price: 0,
        oldPrice: null,
        stock: 10,
        weight: null,
        images: [{ url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop', isPrimary: true }],
        attributes: []
      }
    ]
  };
  showProductModal.value = true;
};

const saveProduct = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/admin/products/${productForm.value.id}`, productForm.value);
    } else {
      await api.post('/admin/products', productForm.value);
    }
    showProductModal.value = false;
    fetchAllData();
  } catch (error) {
    console.error('Failed to save product:', error);
    alert('Помилка при збереженні товару. Перевірте правильність заповнення полів.');
  }
};

const deleteProduct = async (id) => {
  if (confirm('Ви впевнені, що хочете видалити цей товар?')) {
    try {
      await api.delete(`/admin/products/${id}`);
      fetchAllData();
    } catch (error) {
      console.error('Failed to delete product:', error);
    }
  }
};

// DYNAMIC VARIANT FORM HELPERS
const addProductVariant = () => {
  productForm.value.variants.push({
    id: null,
    sku: '',
    price: 0,
    oldPrice: null,
    stock: 10,
    weight: null,
    images: [{ url: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop', isPrimary: true }],
    attributes: []
  });
};

const removeProductVariant = (index) => {
  productForm.value.variants.splice(index, 1);
};

const draggedIndex = ref(null);
const draggedVariant = ref(null);

const onDragStart = (event, index, variant) => {
  draggedIndex.value = index;
  draggedVariant.value = variant;
  event.dataTransfer.effectAllowed = 'move';
};

const onDrop = (event, index, variant) => {
  if (draggedVariant.value === variant && draggedIndex.value !== null && draggedIndex.value !== index) {
    const images = variant.images;
    const draggedItem = images[draggedIndex.value];
    
    // Remove and reinsert
    images.splice(draggedIndex.value, 1);
    images.splice(index, 0, draggedItem);

    // Re-evaluate primary image: first image in array becomes primary
    images.forEach((img, idx) => {
      img.isPrimary = idx === 0;
    });
  }
  draggedIndex.value = null;
  draggedVariant.value = null;
};

const onDragEnd = () => {
  draggedIndex.value = null;
  draggedVariant.value = null;
};

const onFileChange = async (event, variant) => {
  const files = event.target.files;
  if (!files || files.length === 0) return;

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const formData = new FormData();
    formData.append('image', file);

    try {
      const response = await api.post('/admin/products/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      if (response.data && response.data.data && response.data.data.url) {
        const imageUrl = response.data.data.url;
        const isPrimary = variant.images.length === 0;
        variant.images.push({ url: imageUrl, isPrimary });
      }
    } catch (error) {
      console.error('Failed to upload image:', error);
      alert('Помилка при завантаженні зображення: ' + (error.response?.data?.message || error.message));
    }
  }
  event.target.value = '';
};

const removeVariantImage = (variant, index) => {
  variant.images.splice(index, 1);
  // Ensure at least one image remains primary
  if (variant.images.length > 0) {
    variant.images.forEach((img, idx) => {
      img.isPrimary = idx === 0;
    });
  }
};

const addVariantAttribute = (variant) => {
  variant.attributes.push({
    attributeId: '',
    valueId: null,
    value: ''
  });
};

const removeVariantAttribute = (variant, index) => {
  variant.attributes.splice(index, 1);
};

const onAttributeSelected = (attr) => {
  const selected = dbAttributes.value.find(a => a.id === attr.attributeId);
  if (selected) {
    attr.valueId = null;
    attr.value = '';
    // Set default value if select/color type
    if ((selected.type === 'select' || selected.type === 'color') && selected.values.length > 0) {
      attr.valueId = selected.values[0].id;
    }
  }
};

const getAttributeType = (attrId) => {
  const attr = dbAttributes.value.find(a => a.id === attrId);
  return attr ? attr.type : 'text';
};

const getAttributeValues = (attrId) => {
  const attr = dbAttributes.value.find(a => a.id === attrId);
  return attr ? attr.values : [];
};

// CATEGORIES CRUD
const openAddCategoryModal = () => {
  isEditing.value = false;
  categoryForm.value = { id: null, nameUk: '', nameEn: '', parentId: null, order: 0 };
  showCategoryModal.value = true;
};

const openEditCategoryModal = (cat) => {
  isEditing.value = true;
  categoryForm.value = {
    id: cat.id,
    nameUk: cat.nameUk,
    nameEn: cat.nameEn,
    parentId: cat.parentId,
    order: cat.order
  };
  showCategoryModal.value = true;
};

const saveCategory = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/admin/categories/${categoryForm.value.id}`, categoryForm.value);
    } else {
      await api.post('/admin/categories', categoryForm.value);
    }
    showCategoryModal.value = false;
    fetchAllData();
  } catch (error) {
    console.error('Failed to save category:', error);
  }
};

const deleteCategory = async (id) => {
  if (confirm('Ви впевнені, що хочете видалити цю категорію?')) {
    try {
      await api.delete(`/admin/categories/${id}`);
      fetchAllData();
    } catch (error) {
      console.error('Failed to delete category:', error);
    }
  }
};

// BRANDS CRUD
const openAddBrandModal = () => {
  isEditing.value = false;
  brandForm.value = { id: null, name: '', logoPath: '', description: '' };
  showBrandModal.value = true;
};

const openEditBrandModal = (brand) => {
  isEditing.value = true;
  brandForm.value = {
    id: brand.id,
    name: brand.name,
    logoPath: brand.logoPath,
    description: brand.description
  };
  showBrandModal.value = true;
};

const saveBrand = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/admin/brands/${brandForm.value.id}`, brandForm.value);
    } else {
      await api.post('/admin/brands', brandForm.value);
    }
    showBrandModal.value = false;
    fetchAllData();
  } catch (error) {
    console.error('Failed to save brand:', error);
  }
};

const deleteBrand = async (id) => {
  if (confirm('Ви впевнені, що хочете видалити цей бренд?')) {
    try {
      await api.delete(`/admin/brands/${id}`);
      fetchAllData();
    } catch (error) {
      console.error('Failed to delete brand:', error);
    }
  }
};

// ATTRIBUTES CRUD
const openAddAttributeModal = () => {
  isEditing.value = false;
  attributeForm.value = { id: null, code: '', nameUk: '', nameEn: '', type: 'text', values: [] };
  showAttributeModal.value = true;
};

const openEditAttributeModal = (attr) => {
  isEditing.value = true;
  
  const valuesCloned = (attr.values || []).map(v => ({
    id: v.id,
    value: v.value || '',
    valueUk: v.valueUk || '',
    valueEn: v.valueEn || ''
  }));

  attributeForm.value = {
    id: attr.id,
    code: attr.code,
    nameUk: attr.nameUk,
    nameEn: attr.nameEn,
    type: attr.type,
    values: valuesCloned
  };
  showAttributeModal.value = true;
};

const saveAttribute = async () => {
  try {
    if (isEditing.value) {
      await api.put(`/admin/attributes/${attributeForm.value.id}`, attributeForm.value);
    } else {
      await api.post('/admin/attributes', attributeForm.value);
    }
    showAttributeModal.value = false;
    fetchAllData();
  } catch (error) {
    console.error('Failed to save attribute:', error);
  }
};

const deleteAttribute = async (id) => {
  if (confirm('Ви впевнені, що хочете видалити цей атрибут?')) {
    try {
      await api.delete(`/admin/attributes/${id}`);
      fetchAllData();
    } catch (error) {
      console.error('Failed to delete attribute:', error);
    }
  }
};

const addAttributeValue = () => {
  attributeForm.value.values.push({
    id: null,
    value: '',
    valueUk: '',
    valueEn: ''
  });
};

const removeAttributeValue = (index) => {
  attributeForm.value.values.splice(index, 1);
};

// Formatting helpers
const formatPrice = (val) => {
  return new Intl.NumberFormat('uk-UA', { style: 'currency', currency: 'UAH', maximumFractionDigits: 0 }).format(val);
};
</script>
