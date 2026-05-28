<template>
  <div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm">
      <div class="flex-1 w-full sm:w-auto relative">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </span>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Пошук товарів за назвою, SKU чи описом..."
          class="block w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
        />
      </div>

      <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
        <select
          v-model="categoryFilter"
          class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm px-4 py-2.5 focus:ring-primary-500 focus:border-primary-500 transition-colors"
        >
          <option value="">Усі категорії</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <select
          v-model="statusFilter"
          class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm px-4 py-2.5 focus:ring-primary-500 focus:border-primary-500 transition-colors"
        >
          <option value="">Усі статуси</option>
          <option value="active">Активні</option>
          <option value="draft">Чернетки</option>
          <option value="out_of_stock">Немає в наявності</option>
        </select>

        <button
          @click="openAddModal"
          class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-purple-600 hover:from-primary-600 hover:to-purple-700 text-white font-bold rounded-xl text-sm shadow-md transition-all shrink-0"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Додати товар
        </button>
      </div>
    </div>

    <!-- Products Table Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
              <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Товар</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Категорія</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ціна</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Наявність</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Статус</th>
              <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Дії</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <img :src="product.image" alt="" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 bg-gray-100" />
                  <div>
                    <div class="font-bold text-gray-900 dark:text-white">{{ product.name }}</div>
                    <div class="text-xs text-gray-400">SKU: {{ product.sku }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                {{ product.category }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-bold text-gray-900 dark:text-white">
                  {{ formatPrice(product.price) }}
                </div>
                <div v-if="product.discountPrice" class="text-xs text-red-500 line-through">
                  {{ formatPrice(product.discountPrice) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <span
                  v-if="product.stock > 10"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"
                >
                  У наявності ({{ product.stock }})
                </span>
                <span
                  v-else-if="product.stock > 0"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400"
                >
                  Закінчується ({{ product.stock }})
                </span>
                <span
                  v-else
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400"
                >
                  Немає
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  v-if="product.status === 'active'"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400"
                >
                  Активний
                </span>
                <span
                  v-else-if="product.status === 'draft'"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                >
                  Чернетка
                </span>
                <span
                  v-else
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400"
                >
                  Прихований
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button
                    @click="openEditModal(product)"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-blue-600 dark:text-blue-400 transition-colors"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="deleteProduct(product.id)"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 transition-colors"
                  >
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

    <!-- Product Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-2xl w-full border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden transition-all">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
          <h3 class="text-lg font-black text-gray-900 dark:text-white uppercase tracking-wider">
            {{ isEditing ? 'Редагувати товар' : 'Додати новий товар' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveProduct" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="col-span-2">
              <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Назва товару</label>
              <input
                v-model="form.name"
                required
                type="text"
                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
                placeholder="напр. Навушники Bose QuietComfort"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">SKU артикул</label>
              <input
                v-model="form.sku"
                required
                type="text"
                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
                placeholder="напр. HEAD-BOS-QC"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Категорія</label>
              <select
                v-model="form.category"
                required
                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
              >
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Ціна (₴)</label>
              <input
                v-model.number="form.price"
                required
                type="number"
                min="0"
                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Ціна зі знижкою (₴)</label>
              <input
                v-model.number="form.discountPrice"
                type="number"
                min="0"
                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
                placeholder="Немає знижки"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Кількість на складі</label>
              <input
                v-model.number="form.stock"
                required
                type="number"
                min="0"
                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Статус</label>
              <select
                v-model="form.status"
                required
                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
              >
                <option value="active">Активний</option>
                <option value="draft">Чернетка</option>
                <option value="hidden">Прихований</option>
              </select>
            </div>

            <div class="col-span-2">
              <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Зображення товару (URL)</label>
              <input
                v-model="form.image"
                required
                type="text"
                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
                placeholder="Введіть посилання на зображення..."
              />
            </div>

            <div class="col-span-2">
              <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Опис товару</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm focus:ring-primary-500 focus:border-primary-500 transition-colors"
                placeholder="Введіть детальний опис товару..."
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button
              type="button"
              @click="closeModal"
              class="px-5 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors text-gray-700 dark:text-gray-300"
            >
              Скасувати
            </button>
            <button
              type="submit"
              class="px-6 py-2.5 bg-gradient-to-r from-primary-500 to-purple-600 text-white font-bold rounded-xl text-sm shadow-md transition-colors"
            >
              Зберегти
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const categories = ['Ноутбуки', 'Смартфони', 'Планшети', 'Навушники', 'Розумні годинники', 'Аксесуари'];

const products = ref([
  {
    id: 1,
    name: 'MacBook Air M2 13" 8/256GB Midnight',
    sku: 'MBA-M2-MID-256',
    category: 'Ноутбуки',
    price: 45999,
    discountPrice: 48999,
    stock: 14,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=200&fit=crop',
    description: 'Легкий та потужний ноутбук на чіпі M2 від Apple.'
  },
  {
    id: 2,
    name: 'iPhone 15 Pro 128GB Natural Titanium',
    sku: 'IPH15P-NAT-128',
    category: 'Смартфони',
    price: 49999,
    discountPrice: null,
    stock: 5,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?w=200&fit=crop',
    description: 'Флагманський смартфон з титановим корпусом та камерою 48 МП.'
  },
  {
    id: 3,
    name: 'AirPods Max Sky Blue',
    sku: 'APM-BLUE-01',
    category: 'Навушники',
    price: 24999,
    discountPrice: 26999,
    stock: 2,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=200&fit=crop',
    description: 'Повнорозмірні бездротові навушники з активним шумопоглинанням.'
  },
  {
    id: 4,
    name: 'Sony WH-1000XM5 Black',
    sku: 'SNY-XM5-BLK',
    category: 'Навушники',
    price: 15499,
    discountPrice: null,
    stock: 0,
    status: 'out_of_stock',
    image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=200&fit=crop',
    description: 'Найкращі у своєму класі навушники з шумопоглинанням.'
  },
  {
    id: 5,
    name: 'Apple Watch Series 9 45mm GPS Graphite',
    sku: 'AW9-45-GRA-GPS',
    category: 'Розумні годинники',
    price: 18999,
    discountPrice: null,
    stock: 22,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?w=200&fit=crop',
    description: 'Розумні годинники з функцією Gesture Double Tap.'
  }
]);

const searchQuery = ref('');
const categoryFilter = ref('');
const statusFilter = ref('');

const filteredProducts = computed(() => {
  return products.value.filter(product => {
    const matchesSearch = product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                          product.sku.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesCategory = !categoryFilter.value || product.category === categoryFilter.value;
    const matchesStatus = !statusFilter.value || product.status === statusFilter.value || 
                          (statusFilter.value === 'out_of_stock' && product.stock === 0);

    return matchesSearch && matchesCategory && matchesStatus;
  });
});

const showModal = ref(false);
const isEditing = ref(false);
const form = ref({
  id: null,
  name: '',
  sku: '',
  category: '',
  price: 0,
  discountPrice: null,
  stock: 0,
  status: 'active',
  image: '',
  description: ''
});

const openAddModal = () => {
  isEditing.value = false;
  form.value = {
    id: null,
    name: '',
    sku: '',
    category: categories[0],
    price: 0,
    discountPrice: null,
    stock: 10,
    status: 'active',
    image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop',
    description: ''
  };
  showModal.value = true;
};

const openEditModal = (product) => {
  isEditing.value = true;
  form.value = { ...product };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const saveProduct = () => {
  if (isEditing.value) {
    const idx = products.value.findIndex(p => p.id === form.value.id);
    if (idx !== -1) {
      products.value[idx] = { ...form.value };
    }
  } else {
    products.value.push({
      ...form.value,
      id: Date.now()
    });
  }
  closeModal();
};

const deleteProduct = (id) => {
  if (confirm('Ви впевнені, що хочете видалити цей товар?')) {
    products.value = products.value.filter(p => p.id !== id);
  }
};

const formatPrice = (val) => {
  return new Intl.NumberFormat('uk-UA', { style: 'currency', currency: 'UAH', maximumFractionDigits: 0 }).format(val);
};
</script>
